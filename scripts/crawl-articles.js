#!/usr/bin/env node
/**
 * Pulls every article off the current hasnurgroup.com so it can be converted
 * to MDX afterwards. Run on its own:
 *
 *     node scripts/crawl-articles.js            # resume / fill in what is missing
 *     node scripts/crawl-articles.js --force    # re-scrape even what exists
 *     node scripts/crawl-articles.js --limit 5  # smoke test on a few articles
 *
 * The selectors below are not guesses. They come from reading the live markup:
 *
 *   <h3 class="title-post">Aktifitas CSR</h3>   <- section label, NOT the title
 *   <hr>
 *   <div class="... about-section">
 *     <h3><strong>Real article title</strong></h3>
 *     <p><strong><img src="../../uploads/page/x.jpg"></strong></p>
 *     <p><strong>Martapura, 2 Oktober</strong> <strong>2025</strong> – body…</p>
 *
 * Three things that catch you out here:
 *   1. The page <h1> is the category banner ("Tanggung Jawab Sosial
 *      Perusahaan"), identical on every article in a category. The title lives
 *      in the first <h3><strong> inside .about-section.
 *   2. Image src values are relative ("../../uploads/…"), so they must be
 *      resolved against the article URL, not the site root.
 *   3. The dateline is split across sibling <strong> tags, so it only reads
 *      back correctly from the paragraph's text, never from its HTML.
 *
 * Output shape (one file per article, content-migration/raw/{category}/{slug}.json).
 * There was no existing Article interface in this repo, so this is the shape
 * the MDX step should expect:
 *
 *   {
 *     slug, title, category, publishedAt|null, excerpt,
 *     coverImage|null, images[], content, rawHtml,
 *     seo: { title }, sourceUrl, scrapedAt
 *   }
 */

import path from "node:path";
import { fileURLToPath } from "node:url";
import axios from "axios";
import * as cheerio from "cheerio";
import fs from "fs-extra";
import pLimit from "p-limit";

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const OUT = path.join(ROOT, "content-migration");
const DATA = path.join(ROOT, "data");
const ORIGIN = "https://www.hasnurgroup.com";

const LISTINGS = [
  "/page/csr/csr-activity",
  "/page/she/she-activity",
  "/page/publication/latest-update",
  "/page/publication/news",
  "/page/publication/press-release",
  "/page/publication/multimedia",
  "/page/publication/gallery",
];

const CONCURRENCY = 4;
const DELAY_MS = 600;
const RETRIES = 2;
const TIMEOUT_MS = 30000;
const UA = "Mozilla/5.0 (compatible; hasnur-content-migration/1.0; +internal audit)";

const args = process.argv.slice(2);
const FORCE = args.includes("--force");
const LIMIT = Number(args[args.indexOf("--limit") + 1]) || Infinity;

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function fetchText(url, { retries = RETRIES } = {}) {
  for (let attempt = 0; ; attempt++) {
    try {
      const res = await axios.get(url, {
        timeout: TIMEOUT_MS,
        responseType: "text",
        headers: { "User-Agent": UA, Accept: "text/html,application/xhtml+xml,*/*" },
        validateStatus: (s) => s === 200,
      });
      return res.data;
    } catch (err) {
      const status = err.response?.status;
      // 4xx other than 429 will not get better by asking again
      const worthRetrying = !status || status === 429 || status >= 500;
      if (attempt >= retries || !worthRetrying) throw err;
      await sleep(1000 * (attempt + 1));
    }
  }
}

/** Empty `Disallow:` means everything is allowed; anything else we honour. */
async function readRobots() {
  try {
    const txt = await fetchText(`${ORIGIN}/robots.txt`, { retries: 0 });
    const rules = [];
    let appliesToUs = false;
    for (const line of txt.split("\n")) {
      const [rawKey, ...rest] = line.split(":");
      const key = rawKey.trim().toLowerCase();
      const value = rest.join(":").trim();
      if (key === "user-agent") appliesToUs = value === "*";
      else if (key === "disallow" && appliesToUs && value) rules.push(value);
      else if (key === "sitemap" && value) rules.sitemap = value;
    }
    return { disallow: rules, raw: txt };
  } catch {
    return { disallow: [], raw: null };
  }
}

const blockedBy = (disallow, url) => {
  const p = new URL(url).pathname;
  return disallow.find((rule) => p.startsWith(rule));
};

// ---------------------------------------------------------------- discovery
async function discover(disallow) {
  const found = new Map(); // url -> category

  const add = (href, from) => {
    let u;
    try {
      u = new URL(href, ORIGIN);
    } catch {
      return;
    }
    if (u.origin !== ORIGIN) return;
    const m = u.pathname.match(/^\/post\/([^/]+)\/([^/]+)\/?$/);
    if (!m) return;
    u.hash = "";
    u.search = "";
    const clean = u.toString();
    const blocked = blockedBy(disallow, clean);
    if (blocked) {
      console.warn(`  ! dilewati (robots.txt Disallow: ${blocked}): ${u.pathname}`);
      return;
    }
    // Category always comes from the article's own URL. Listing pages carry a
    // "Recent Post" sidebar linking to other categories, so trusting the page
    // you found a link on would mislabel those.
    if (!found.has(clean)) found.set(clean, { url: clean, category: m[1], foundOn: from });
  };

  // Sitemap first if the site has one. It is cheaper and more complete.
  try {
    const xml = await fetchText(`${ORIGIN}/sitemap.xml`, { retries: 0 });
    const locs = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]);
    const posts = locs.filter((l) => l.includes("/post/"));
    if (posts.length) {
      console.log(`sitemap.xml: ${posts.length} URL /post/`);
      posts.forEach((l) => add(l, "sitemap.xml"));
      return [...found.values()];
    }
    console.log("sitemap.xml ada tapi tidak memuat /post/. Beralih ke listing");
  } catch {
    console.log("sitemap.xml tidak tersedia. Memakai 7 halaman listing");
  }

  for (const listing of LISTINGS) {
    const url = ORIGIN + listing;
    let html;
    try {
      html = await fetchText(url);
    } catch (err) {
      console.warn(`  ! listing gagal ${listing}: ${err.message}`);
      continue;
    }
    const $ = cheerio.load(html);
    const before = found.size;

    // Restrict to the content column; the footer's "RECENT POST" block links
    // to unrelated articles.
    const scope = $(".about-section, .content-block").length
      ? $(".about-section, .content-block")
      : $.root();
    scope.find('a[href*="/post/"]').each((_, el) => add($(el).attr("href"), listing));

    // Follow pagination when it exists. The site currently renders every card
    // on one page, so this usually adds nothing. It is here so the script
    // does not silently truncate if that changes.
    const seenPages = new Set([url]);
    let next = $('a[rel="next"], .pagination a, a[href*="page="]')
      .map((_, el) => new URL($(el).attr("href"), url).toString())
      .get()
      .find((h) => h.includes(listing) && !seenPages.has(h));
    while (next) {
      seenPages.add(next);
      await sleep(DELAY_MS);
      const page = cheerio.load(await fetchText(next));
      page('a[href*="/post/"]').each((_, el) => add(page(el).attr("href"), listing));
      next = page('a[rel="next"], .pagination a')
        .map((_, el) => new URL(page(el).attr("href"), url).toString())
        .get()
        .find((h) => h.includes(listing) && !seenPages.has(h));
    }

    console.log(`  ${listing.padEnd(34)} +${found.size - before}`);
    await sleep(DELAY_MS);
  }

  return [...found.values()];
}

// ---------------------------------------------------------------- extraction
const MONTHS = "Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember";
const MONTH_NUM = Object.fromEntries(
  MONTHS.split("|").map((m, i) => [m.toLowerCase(), String(i + 1).padStart(2, "0")])
);

function parseDate(text) {
  const iso = (y, m, d) => `${y}-${String(m).padStart(2, "0")}-${String(d).padStart(2, "0")}`;

  // "Martapura, 2 Oktober 2025 –"
  let m = text.match(new RegExp(`(\\d{1,2})\\s+(${MONTHS})\\s+(\\d{4})`, "i"));
  if (m) return iso(m[3], MONTH_NUM[m[2].toLowerCase()], m[1]);

  // "(2/10/2025)" or "02/10/2025"
  m = text.match(/\(?\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b\)?/);
  if (m) return iso(m[3], m[2], m[1]);

  return null;
}

/**
 * The source pages ship an unterminated attribute. `<hr class="2px/>`, which
 * makes the parser swallow everything after it into the attribute value, so
 * the content container never materialises as an element. Repairing that one
 * tag before parsing is what makes .about-section reachable at all.
 */
function repairMarkup(html) {
  return html.replace(/<hr([^>]*?)class="([^"]*?)\/>/gi, '<hr$1class="$2" />');
}

function extract(html, url) {
  const $ = cheerio.load(repairMarkup(html));
  const [, category, slug] = new URL(url).pathname.match(/^\/post\/([^/]+)\/([^/]+)/);

  let $body = $(".about-section").first();
  if (!$body.length) $body = $("section.content-block").eq(1);
  if (!$body.length) throw new Error("container konten tidak ditemukan");

  $body.find("script, style, nav, header, footer").remove();

  // <title> is the only title present on every article. Some posts repeat it
  // as an <h3><strong> at the top of the body and some have no heading at all,
  // so an in-body heading cannot be the primary source. The page <h1> is worse
  // still: it is the category banner, identical for every post in a category.
  const seoTitle = $("title").first().text().trim();
  const title = seoTitle.replace(/\s*[-–]\s*Hasnur Group\s*$/i, "").trim();

  // Drop a leading heading only when it is that same title, so the body does
  // not repeat it. A heading that says something else is real content.
  const norm = (s) => s.replace(/\s+/g, " ").trim().toLowerCase();
  const $firstHeading = $body.find("h1, h2, h3, h4").first();
  if ($firstHeading.length && norm($firstHeading.text()) === norm(title)) $firstHeading.remove();

  const images = [];
  $body.find("img[src]").each((_, el) => {
    const src = $(el).attr("src");
    if (!src) return;
    try {
      images.push(new URL(src, url).toString()); // src values are relative
    } catch {}
  });

  // Date sits in the first paragraph that actually carries text; the opening
  // paragraph is often just the cover image.
  const paras = $body
    .find("p")
    .map((_, el) => $(el).text().replace(/\s+/g, " ").trim())
    .get()
    .filter(Boolean);
  const publishedAt = parseDate(paras.slice(0, 3).join(" "));

  const bodyText = paras.join(" ");
  const excerpt = bodyText
    .replace(new RegExp(`^.{0,40}?\\d{1,2}\\s+(${MONTHS})\\s+\\d{4}\\s*[–--]\\s*`, "i"), "")
    .slice(0, 150)
    .trim();

  return {
    slug,
    title,
    category,
    publishedAt,
    excerpt,
    coverImage: images[0] ?? null,
    images,
    content: $body.html()?.trim() ?? "",
    seo: { title: seoTitle },
    sourceUrl: url,
    scrapedAt: new Date().toISOString(),
  };
}

// ------------------------------------------------------------------- images
async function downloadImages(article) {
  const dir = path.join(OUT, "images", article.category, article.slug);
  const map = [];
  let i = 0;

  for (const src of article.images) {
    i += 1;
    const ext = (path.extname(new URL(src).pathname) || ".jpg").split("?")[0];
    const localAbs = path.join(dir, `${i}${ext}`);
    const localRel = path
      .relative(OUT, localAbs)
      .split(path.sep)
      .join("/");

    if (!(await fs.pathExists(localAbs))) {
      try {
        const res = await axios.get(src, {
          timeout: TIMEOUT_MS,
          responseType: "arraybuffer",
          headers: { "User-Agent": UA },
          validateStatus: (s) => s === 200,
        });
        await fs.outputFile(localAbs, res.data);
      } catch (err) {
        map.push({ originalImageUrl: src, localPath: null, error: err.message });
        continue;
      }
      await sleep(200);
    }
    map.push({ originalImageUrl: src, localPath: localRel });
  }

  // Point the content at the downloaded copies, but keep the mapping so the
  // rewrite can be audited (and reversed) later.
  let content = article.content;
  for (const entry of map) {
    if (!entry.localPath) continue;
    const original = article.images.find((s) => s === entry.originalImageUrl);
    content = content.split(original).join(entry.localPath);
  }
  // relative srcs in the source markup still need rewriting
  const $ = cheerio.load(content, null, false);
  $("img[src]").each((_, el) => {
    const src = $(el).attr("src");
    const abs = (() => {
      try {
        return new URL(src, article.sourceUrl).toString();
      } catch {
        return null;
      }
    })();
    const hit = map.find((m) => m.originalImageUrl === abs && m.localPath);
    if (hit) $(el).attr("src", hit.localPath);
  });

  return { content: $.html(), imageMap: map };
}

// --------------------------------------------------------------------- main
async function main() {
  console.log(`Crawl ${ORIGIN}\n`);

  const robots = await readRobots();
  console.log(
    robots.raw === null
      ? "robots.txt tidak terbaca. Melanjutkan dengan hati-hati"
      : robots.disallow.length
      ? `robots.txt melarang: ${robots.disallow.join(", ")}`
      : "robots.txt: tidak ada larangan"
  );

  console.log("\nDiscovery,");
  const discovered = await discover(robots.disallow);
  await fs.outputJson(path.join(DATA, "discovered-urls.json"), discovered, { spaces: 1 });
  console.log(`\n${discovered.length} URL unik → data/discovered-urls.json`);

  const queue = discovered.slice(0, LIMIT);
  const limit = pLimit(CONCURRENCY);
  const failed = [];
  const needsManualDate = [];
  const imageMapAll = [];
  let scraped = 0;
  let skipped = 0;

  console.log(`\nScrape (${queue.length} artikel, ${CONCURRENCY} paralel),`);
  await Promise.all(
    queue.map((item) =>
      limit(async () => {
        const slug = item.url.split("/").pop();
        const dest = path.join(OUT, "raw", item.category, `${slug}.json`);
        if (!FORCE && (await fs.pathExists(dest))) {
          skipped += 1;
          return;
        }
        try {
          await sleep(DELAY_MS);
          const html = await fetchText(item.url);
          const article = extract(html, item.url);
          const { content, imageMap } = await downloadImages(article);

          article.content = content;
          // point the cover at the downloaded copy too, so every image path in
          // the record is local and the MDX step never reaches back to the old site
          article.coverImage = imageMap.find((m) => m.localPath)?.localPath ?? null;
          article.rawHtml = html;
          await fs.outputJson(dest, article, { spaces: 1 });

          imageMapAll.push({ slug: article.slug, category: article.category, images: imageMap });
          if (!article.publishedAt) needsManualDate.push(`${article.category}/${article.slug}`);
          scraped += 1;
          process.stdout.write(".");
        } catch (err) {
          failed.push({ url: item.url, category: item.category, error: err.message });
          process.stdout.write("x");
        }
      })
    )
  );
  console.log("");

  await fs.outputJson(path.join(OUT, "image-map.json"), imageMapAll, { spaces: 1 });
  if (failed.length) await fs.outputJson(path.join(OUT, "failed.json"), failed, { spaces: 1 });

  const perCategory = {};
  for (const f of await fs.readdir(path.join(OUT, "raw")).catch(() => [])) {
    perCategory[f] = (await fs.readdir(path.join(OUT, "raw", f))).length;
  }

  console.log("\n=========== RINGKASAN ===========");
  for (const [cat, n] of Object.entries(perCategory).sort()) {
    console.log(`  ${cat.padEnd(18)} ${n}`);
  }
  console.log(`  ${"".padEnd(18)} ${"-".repeat(4)}`);
  console.log(`  ${"tersimpan".padEnd(18)} ${Object.values(perCategory).reduce((a, b) => a + b, 0)}`);
  console.log(`\n  baru di-scrape    ${scraped}`);
  console.log(`  dilewati (ada)    ${skipped}`);
  console.log(`  gagal             ${failed.length}${failed.length ? "  → content-migration/failed.json" : ""}`);
  console.log(`  tanggal manual    ${needsManualDate.length}`);
  if (needsManualDate.length) {
    needsManualDate.slice(0, 15).forEach((s) => console.log(`      - ${s}`));
    if (needsManualDate.length > 15) console.log(`      … dan ${needsManualDate.length - 15} lagi`);
  }
  console.log("\nLangkah berikutnya: konversi content-migration/raw/*.json (HTML) → MDX.");
}

main().catch((err) => {
  console.error("\nCrawl berhenti:", err);
  process.exit(1);
});
