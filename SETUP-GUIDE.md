# The Cloud Pod Theme - Setup Guide

## Initial Setup Checklist

### 1. Install Required Plugin
- Install and activate **Seriously Simple Podcasting** plugin
- Go to Podcasting → Settings and configure your podcast details

### 2. Configure WordPress Settings

#### Homepage Display
- Go to **Settings → Reading**
- Set "Your homepage displays" to **Your latest posts**
- This will show the hero section and episodes grid

#### Permalink Structure
- Go to **Settings → Permalinks**
- Choose **Post name** for clean URLs
- Save changes

### 3. Add Your Logo
- Go to **Appearance → Customize → Site Identity**
- Upload your logo (recommended size: 240x60px)
- This replaces the text title "The Cloud Pod"

### 4. Set Up Menus
- Go to **Appearance → Menus**
- Create a new menu called "Primary Menu"
- Add pages: About Us, Episodes, Subscribe, Contact
- Assign to "Primary Menu" location
- Create another menu for "Footer Menu"

### 5. Configure Episodes

#### For Each Episode:
1. **Featured Image** - Upload episode artwork (recommended: 1200x1200px)
   - Without this, you'll see gradient placeholders
2. **Audio File** - Upload or link to MP3 file via SSP
3. **Transcript** - Add transcript URL in the "Episode Transcript" meta box
4. **Excerpt** - Write a brief description (will show on cards)

### 6. Customize Colors (Optional)
If you want different colors:
1. Go to **Appearance → Customize → Additional CSS**
2. Override variables:
```css
:root {
    --cloud-blue: #0A7AFF;
    --cloud-purple: #6B5CE7;
    /* Change these to your preferred colors */
}
```

## Troubleshooting

### Issue: No Episodes Showing
**Solution:**
- Make sure you have published podcast episodes (not drafts)
- Check that Seriously Simple Podcasting is active
- Verify episodes are set to "podcast" post type

### Issue: Cards Show Gradient Instead of Images
**Solution:**
- Each episode needs a **Featured Image** set
- Go to episode edit page → scroll to "Featured Image" box on right
- Upload your episode artwork

### Issue: Hero Section Not Showing
**Solution:**
- Go to Settings → Reading
- Make sure homepage is set to "Your latest posts" NOT a static page
- If you want a custom homepage, you can use Elementor to build it

### Issue: Stats Section Shows Wrong Numbers
**Solution:**
- The episode count auto-updates based on published podcasts
- If it's wrong, you may have draft episodes that need publishing

### Issue: Platform Links Not Working
**Solution:**
- Edit `index.php` in the theme
- Update the URLs in the platform-links section to your actual links:
  - Apple Podcasts URL
  - Spotify URL
  - RSS Feed URL
  - YouTube URL

## Using Elementor (Optional)

This theme is fully Elementor compatible!

### Creating Custom Pages:
1. Create a new page
2. Click "Edit with Elementor"
3. Build your custom layout
4. Template options available:
   - **Default** - Includes header and footer
   - **Elementor Full Width** - Full width with header/footer
   - **Elementor Canvas** - Blank canvas (no header/footer)

### Homepage with Elementor:
1. Create a page called "Home"
2. Edit with Elementor and design your homepage
3. Go to Settings → Reading
4. Set "A static page" and choose your "Home" page
5. This overrides the default theme homepage

## Quick Fixes

### Make Episode Cards Look Better Right Now:
1. Go to each episode
2. Set a Featured Image (episode artwork)
3. Save/Update the episode

### Get Your Logo Showing:
1. Appearance → Customize → Site Identity
2. Upload logo → Save

### Update Platform URLs:
Edit your theme's `index.php` file around line 55-70 and replace the `#` with your actual URLs.

## Need Help?

### Common File Locations:
- Theme files: `/wp-content/themes/tcptheme/`
- Uploads: `/wp-content/uploads/`
- Plugins: `/wp-content/plugins/seriously-simple-podcasting/`

### Verify SSP is Working:
1. Go to Podcasting → Episodes in WordPress admin
2. You should see your episodes listed
3. Click one and verify audio file is attached

### Force Refresh:
- Clear browser cache (Cmd+Shift+R on Mac)
- Clear WordPress cache if using a caching plugin
- Deactivate and reactivate the theme

## Next Steps

1. ✅ Upload episode artwork for all episodes
2. ✅ Set homepage to show latest posts
3. ✅ Upload your logo
4. ✅ Configure menus
5. ✅ Test the audio player on a single episode
6. ✅ Update platform URLs to your actual links

Once these are done, your site will look exactly like the preview!
