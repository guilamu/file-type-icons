# File Type Icons

[![Latest Release](https://img.shields.io/github/v/release/guilamu/file-type-icons?color=blue)](https://github.com/guilamu/file-type-icons/releases) [![License: AGPL-3.0](https://img.shields.io/badge/license-AGPL--3.0-green.svg)](LICENSE.txt) [![WordPress: 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org) [![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net)

Automatically adds customizable SVG icons to file links (PDF, Word, Excel, PowerPoint, TXT, etc.)

## Custom SVG Icons
- Display elegant vector graphics next to document links automatically.
- Colorize icons using HSL tailored colors directly from a native HTML5 color picker.
- Choose between Solid/Filled and Outline icon designs to match your style.
- Set responsive sizing and align icons to either the left or right of link text.

## Smart Link Exclusions
- Add the `no-fti` class to any anchor tag or container element to disable icons.
- Exclude whole blocks of content easily by wrapping them inside the `[no_fti]...[/no_fti]` shortcode.
- Toggle off icon rendering completely on specific pages via the editor sidebar metadata check.

## Key Features
- **Customizable:** Adjust colors, style presets, sizes, and layout choices instantly.
- **Lightweight:** Pure CSS-based mask-image presentation with zero javascript front-end libraries.
- **Multilingual:** Works with content in any language
- **Translation-Ready:** All strings are internationalized
- **Secure:** Enforces strict server sanitization and outputs clean, escaped inline styles.
- **GitHub Updates:** Automatic updates from GitHub releases

## Requirements
- WordPress 6.0 or higher
- PHP 8.0 or higher

## Installation
1. Upload the `file-type-icons` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → File Type Icons** and configure icon sizes, styles, positions, and active types.

## FAQ

### How do I change the color of an icon?
Go to the configuration page **Settings → File Type Icons** and use the color picker next to the desired file type.

### How do I disable icons on a specific page?
Check the **Disable icons on this page** box located in the editor sidebar (Document settings) when editing your page or post.

### How do I exclude specific links in the content?
Simply add the CSS class `no-fti` to your links or wrap the text containing the links with the shortcode `[no_fti]...[/no_fti]`.

### Can I customize the icon URL via code?
Yes, use the `fti_icon_url` filter:
```php
add_filter( 'fti_icon_url', function( $url, $type ) {
    return $url;
}, 10, 2 );
```

### Can I disable the icon handler programmatically?
Yes, use the `fti_is_disabled` filter:
```php
add_filter( 'fti_is_disabled', function( $is_disabled ) {
    return $is_disabled;
} );
```

## Project Structure
```
.
├── file-type-icons.php           # Main plugin bootstrap file
├── uninstall.php                 # Database settings cleanup on uninstall
├── README.md                     # Documentation
├── assets
│   ├── css
│   │   ├── admin.css             # Styles for settings options page
│   │   └── frontend.css          # Frontend layout structures
│   ├── js
│   │   └── admin.js              # Settings real-time preview sync script
│   └── icons
│       └── ...                   # Solid and outline SVG vector assets
├── includes
│   ├── class-admin-settings.php  # Options registry and admin settings page
│   ├── class-disabler.php        # Meta box and shortcode handler
│   ├── class-github-updater.php  # GitHub automatic updater filter engine
│   ├── class-icon-handler.php    # Frontend link parser and dynamic CSS injector
│   ├── class-plugin.php          # Main bootstrap loader
│   └── Parsedown.php             # PHP Markdown parser library dependency
└── languages
    ├── file-type-icons-fr_FR.mo  # French translation compiled binary
    ├── file-type-icons-fr_FR.po  # French translation source file
    └── file-type-icons.pot       # Main translation template
```

## Changelog

### 1.0.2 - 2026-06-10
- Added Style 3 ("Rounded" round gradient badge preset) with real-time settings page preview support.

### 1.0.1 - 2026-06-10
- Added Live Link Preview in general settings panel.
- Fixed layout alignment for Left and Below position styles in settings preview.
- Fixed layout overlaps when previewing large icon size values.
- Updated POT/PO translation source files with all missing localized strings.

### 1.0.0 - 2026-06-10
- Initial stable release.

## Security

If you discover a security vulnerability in this plugin, please report it responsibly through [GitHub Security Advisories](https://github.com/guilamu/file-type-icons/security/advisories/new). Do not open a public issue for security reports.

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on [GitHub](https://github.com/guilamu/file-type-icons).

For translations, the plugin uses WordPress i18n. You can contribute translations by editing the `.po` files in the `languages/` directory and generating the corresponding `.mo` files with the `wp i18n` CLI commands.

## License
This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0) — see the [LICENSE](LICENSE.txt) file for details.

---

Made with love for the WordPress community
