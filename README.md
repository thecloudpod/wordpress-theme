# The Cloud Pod WordPress Theme

A modern, cloud-computing themed WordPress theme designed specifically for The Cloud Pod podcast. Features seamless integration with Seriously Simple Podcasting plugin, a sticky miniplayer, and mobile-friendly responsive design.

## Features

- 🎨 **Modern Cloud Theme Design** - Beautiful gradient blue/purple color scheme inspired by cloud computing
- 🎵 **Custom Audio Player** - Full-featured audio player with play/pause, skip, and progress controls
- 📱 **Sticky Miniplayer** - Appears when scrolling away from the main player, keeps playing as you browse
- ⬇️ **Download Options** - Easy access to download MP3 files and episode transcripts
- 📱 **Mobile Responsive** - Fully optimized for phones, tablets, and desktops
- ⚡ **Performance Optimized** - Clean, lightweight code with modern CSS and JavaScript
- ♿ **Accessible** - ARIA labels, keyboard shortcuts, and semantic HTML
- 🔌 **SSP Integration** - Built to work seamlessly with Seriously Simple Podcasting plugin
- 🎯 **Elementor Compatible** - Full support for Elementor page builder with custom templates
- 📊 **Stats Section** - Display episode count and key metrics
- 🎧 **Platform Links** - Pre-styled links for Apple Podcasts, Spotify, RSS, and YouTube
- 🏆 **Premium Look** - Hero section, animated cards, and modern UI elements

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- [Seriously Simple Podcasting](https://wordpress.org/plugins/seriously-simple-podcasting/) plugin

## Installation

1. **Upload the theme:**
   - Upload the `tcptheme` folder to `/wp-content/themes/`
   - Or zip the folder and upload via WordPress admin: Appearance → Themes → Add New → Upload Theme

2. **Activate the theme:**
   - Go to Appearance → Themes in your WordPress admin
   - Click "Activate" on The Cloud Pod theme

3. **Install required plugin:**
   - Install and activate [Seriously Simple Podcasting](https://wordpress.org/plugins/seriously-simple-podcasting/)

4. **Configure menus:**
   - Go to Appearance → Menus
   - Create menus for "Primary Menu" and "Footer Menu"

5. **Configure widgets:**
   - Go to Appearance → Widgets
   - Add widgets to Footer 1, Footer 2, and Footer 3 areas

## Theme Setup

### Adding Episode Transcripts

When creating or editing a podcast episode, you'll see an "Episode Transcript" meta box where you can add a URL to your transcript file (PDF, TXT, or DOC).

### Color Customization

The theme uses CSS variables for easy color customization. Edit `style.css` and modify the `:root` section:

```css
:root {
    --cloud-blue: #0A7AFF;
    --cloud-blue-light: #4DA8FF;
    --cloud-blue-dark: #0556B3;
    --cloud-purple: #6B5CE7;
    --cloud-teal: #00BFA5;
    /* ... */
}
```

### Custom Logo

- Go to Appearance → Customize → Site Identity
- Upload your logo (recommended size: 240x60px)

## Audio Player Features

### Main Player
- Play/Pause button with visual feedback
- Rewind 15 seconds (⏪)
- Forward 15 seconds (⏩)
- Progress bar with click-to-seek
- Current time and duration display
- Episode artwork and metadata

### Miniplayer
- Automatically appears when scrolling past the main player
- Synchronized playback with main player
- Compact design with essential controls
- Close button to dismiss

### Keyboard Shortcuts
- **Spacebar** - Play/Pause
- **Left Arrow** - Rewind 15 seconds
- **Right Arrow** - Forward 15 seconds

## File Structure

```
tcptheme/
├── style.css                      # Main stylesheet with cloud theme
├── functions.php                  # Theme functions and SSP integration
├── header.php                     # Header template
├── footer.php                     # Footer template with miniplayer
├── index.php                      # Main blog/homepage template
├── single-podcast.php             # Single episode template
├── archive-podcast.php            # Episode archive template
├── js/
│   ├── audio-player.js           # Audio player and miniplayer logic
│   └── navigation.js             # Mobile menu functionality
└── template-parts/
    ├── content-podcast-card.php  # Episode card component
    └── content-none.php          # No content found template
```

## Seriously Simple Podcasting Integration

The theme automatically integrates with SSP and retrieves:
- Episode audio files
- Episode duration
- Episode metadata
- RSS feed data

### Meta Fields Used
- `audio_file` - Primary audio file URL
- `duration` - Episode duration
- `_transcript_url` - Custom transcript URL (added by theme)

## Customization

### Adding Custom Styles

Add custom CSS in Appearance → Customize → Additional CSS, or create a child theme.

### Creating a Child Theme

1. Create a new folder: `tcptheme-child/`
2. Create `style.css`:

```css
/*
Theme Name: The Cloud Pod Child
Template: tcptheme
*/

@import url('../tcptheme/style.css');

/* Your custom styles here */
```

3. Create `functions.php`:

```php
<?php
function cloudpod_child_enqueue_styles() {
    wp_enqueue_style('cloudpod-child-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'cloudpod_child_enqueue_styles');
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Support

For issues related to The Cloud Pod podcast website, contact [SaaS 11, LLC](https://www.thecloudpod.net/contact).

For theme bugs or feature requests, please document and share with the development team.

## Credits

**Theme Author:** SaaS 11, LLC  
**Website:** https://www.thecloudpod.net  
**License:** GNU General Public License v2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Changelog

### Version 1.0.0
- Initial release
- Custom audio player with miniplayer
- Seriously Simple Podcasting integration
- Cloud computing themed design
- MP3 and transcript download functionality
- Mobile responsive layout
- Keyboard shortcuts for audio control
