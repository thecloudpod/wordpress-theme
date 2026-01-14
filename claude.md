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

### Post Types
- `podcast` - Custom post type from Seriously Simple Podcasting plugin
- `post` - Standard WordPress blog posts

### Common Issues
1. **Cache not clearing**: Use `clear-cache.php` utility script
2. **Permalinks broken**: Flush rewrite rules in Settings → Permalinks
3. **Wrong template loading**: Check WordPress template hierarchy
4. **Debug files in production**: Always exclude debug-*.php files from zip
