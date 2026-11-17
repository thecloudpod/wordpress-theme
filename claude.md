# Claude Development Notes - The Cloud Pod Theme

## Theme Release Process

When creating a new theme release:

1. **Update version numbers** in two locations:
   - `style.css` - Theme header comment (Version: X.X.X)
   - `functions.php` - wp_enqueue_style version parameter

2. **Create zip file** from parent directory:
   ```bash
   cd /Users/jbrodley/git/SaaS11
   rm -f tcptheme-vX.X.X.zip
   zip -r tcptheme-vX.X.X.zip tcptheme \
     -x "tcptheme/*.git*" \
     -x "tcptheme/node_modules/*" \
     -x "tcptheme/*.DS_Store" \
     -x "tcptheme/debug-*.php" \
     -x "tcptheme/clear-cache.php" \
     -x "tcptheme/investigate-*.php" \
     -x "tcptheme/tcptheme-*.zip"
   ```

3. **Verify zip structure**:
   - Must include `tcptheme/` folder at root level
   - When extracted, creates `/wp-content/themes/tcptheme/`
   - Check with: `unzip -l tcptheme-vX.X.X.zip | head -20`

4. **Final location**: `/Users/jbrodley/git/SaaS11/tcptheme-vX.X.X.zip`

## Important Notes

- **DO NOT** create zip from inside tcptheme directory (creates wrong structure)
- **ALWAYS** create zip from parent `/SaaS11` directory
- **EXCLUDE** debug files, git files, node_modules, and old zip files
- **VERIFY** folder structure before uploading to production

## Recent Fixes

### v1.3.3 - SEO headline field + heading cleanup (2026-09-17)
- **Field**: ACF text field `seo_headline` on the `podcast` post type (Show in REST API: yes). Also registered via `register_post_meta` in `functions.php` so bolt-bot can write it through the REST `meta` object.
- **Render**: `single-podcast.php` uses `seo_headline` as the H1 when set and shows the episode title as `<p class="entry-subtitle">`; empty field = unchanged legacy behaviour. The player's repeated title is now `<p class="player-title">` instead of a second heading.
- **Not changed**: post title (feeds RSS `<title>`, which Pocket Casts reads), `itunes_title`, Rank Math title template (the "- date" suffix is a Rank Math setting).

### v1.3.1 / v1.3.2 - Static podcast feeds + robots.txt (2026-09-16/17)
- **v1.3.2**: include every `series` term (the default series `the-cloud-pod` has 0 assigned episodes but renders the full feed and is the redirect target of `/feed/podcast/`); a well-formed feed with zero items is skipped without logging a failure; `If-None-Match` is unslashed before comparison (WP slashes `$_SERVER`), verified in prod that 1.3.1 returned 200 instead of 304 on ETag matches.
- **Issue**: The SSP podcast feed re-rendered all 398 episodes on every request (2.5 MB, 14+ s, ~1,200 DB queries). ~180 full renders/hour from Googlebot, Podbean, Amazon Music and Spotify made it the top source of DB load.
- **Fix**: `inc/feed-cache.php`. Any podcast post/meta/series change schedules one WP-Cron regeneration (60 s debounce, hourly safety net). Regeneration fetches each feed via loopback with a bypass header and writes `wp-content/uploads/cloudpod-feeds/<key>.xml`. Feed requests are served from that file at `init` with ETag/Last-Modified and 304 support (`X-CloudPod-Feed: static` response header). Missing or >3 h old file falls through to the dynamic render. After 3 all-failed regenerations pollers stop scheduling retries (hourly cron still tries). Editor heartbeat meta (`_edit_lock` etc.) and Rank Math meta are ignored; only published episodes invalidate.
- **Manual regen**: admin bar "Regenerate podcast feeds", or `/wp-admin/?cloudpod_regen_feeds=1` with nonce. Last result stored in option `cloudpod_feeds_last_regen`.
- **robots.txt**: appends `Disallow: /feed/` and `Disallow: /*/feed/` inside the `User-agent: *` group. Podcast apps ignore robots.txt; general crawlers stop polling RSS.
- **Files Changed**: added `inc/feed-cache.php`; updated `functions.php`, `style.css`, `CLAUDE.md`.

### v1.3.0 - Inline transcripts for SEO (2026-09-16)
- **Issue**: Transcripts were only linked as a bare `.txt` on `tcp-media.s3.us-west-2.amazonaws.com`, so Google credited none of that text to thecloudpod.net.
- **Fix**: New `inc/transcript.php` registers a REST-writable `transcript_text` post meta on the `podcast` post type, renders it inline in a collapsed native `<details>` on `single-podcast.php` (in the HTML at load, no JS), and emits `PodcastEpisode` / `AudioObject` JSON-LD with the transcript in the `transcript` property.
- **Contract with bolt-bot**: `POST /wp-json/wp/v2/podcast/{id}` with `{"meta": {"transcript_text": "..."}}`. Plain text only, one utterance per line, `[MM:SS] text` or `[MM:SS] Speaker Name: text`, no header/chapters. Minutes may exceed 59.
- **Legacy episodes**: no backfill. Posts without `transcript_text` render exactly as before (S3 link only, labelled "View Transcript"). When the text exists the box shows "Read Transcript" (jumps to `#transcript`) and "Download Transcript" (S3).
- **Deploy order matters**: WP REST silently drops unregistered meta keys and still returns 200. The theme must be live before bolt-bot starts sending `transcript_text`.
- **Files Changed**: added `inc/transcript.php`; updated `functions.php`, `single-podcast.php`, `front-page.php`, `style.css`, `CLAUDE.md`.

### v1.2.7 - Blog Post Display Fix (2026-01-14)
- **Issue**: Blog posts showing "Latest Episodes" archive instead of post content
- **Cause**: Missing `single.php` template file for regular posts
- **Fix**: Created `single.php` template with proper single post layout
- **Files Changed**:
  - Added: `single.php` (new template for blog posts)
  - Updated: `style.css` (version 1.2.6 → 1.2.7)
  - Updated: `functions.php` (version 1.2.6 → 1.2.7)

**WordPress Template Hierarchy**:
- `single-podcast.php` → handles podcast posts (custom post type)
- `single.php` → handles regular blog posts ← THIS WAS MISSING
- `index.php` → fallback for archives/listings

**Post-Installation Steps**:
1. Upload theme via WordPress admin
2. Clear all caches (APCu, WordPress object cache, transients)
3. Flush rewrite rules (Settings → Permalinks → Save)
4. Test blog post URLs

## Theme Architecture

### Template Files
- `single-podcast.php` - Single podcast episode pages
- `single.php` - Single blog post pages (regular posts)
- `archive-podcast.php` - Podcast archive
- `front-page.php` - Homepage with two-column layout
- `index.php` - General archive fallback
- `page.php` - Static pages
- `inc/transcript.php` - Transcript meta registration, inline rendering, episode JSON-LD
- `inc/feed-cache.php` - Static podcast feed generation/serving, robots.txt feed disallow

### Post Types
- `podcast` - Custom post type from Seriously Simple Podcasting plugin
- `post` - Standard WordPress blog posts

### Common Issues
1. **Cache not clearing**: Use `clear-cache.php` utility script
2. **Permalinks broken**: Flush rewrite rules in Settings → Permalinks
3. **Wrong template loading**: Check WordPress template hierarchy
4. **Debug files in production**: Always exclude debug-*.php files from zip
