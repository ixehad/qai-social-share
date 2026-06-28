# Qai Social Share

A WordPress plugin that adds **AI Summarize** buttons and **Social Share** buttons to your blog posts — fully configurable from the WordPress dashboard, no theme editing required.

![Plugin Version](https://img.shields.io/badge/version-1.1.0-blue) ![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b) ![PHP](https://img.shields.io/badge/PHP-7.0%2B-777bb4) ![License](https://img.shields.io/badge/license-GPL--2.0-green)

---

## Features

### 🤖 AI Summarize Row
Lets readers instantly summarize your post in their preferred AI tool:
- ChatGPT
- Claude
- Perplexity
- Grok
- Google AI Mode

### 📣 Social Share Row
One-click sharing to all major platforms:
- Facebook
- X (Twitter)
- WhatsApp
- LinkedIn
- Telegram
- Pinterest
- Copy Link (clipboard button with "Copied!" feedback)

### ⚙️ Full Admin Settings Page
Found at **Qai Social Share** in your WordPress dashboard sidebar:
- Toggle any network on/off individually
- Edit the AI prompt template (e.g. "Summarize the key insights from {url}")
- Edit row labels ("Summarize with", "Share this article")
- Choose button shape: Pill / Rounded / Square
- Choose placement: Top of post, Before content, After content, or Shortcode only
- Toggle Copy Link button
- Toggle open-in-new-tab
- **Live Preview** — see exactly what your buttons look like before saving, with instant toggles for layout changes

### 🔁 Smart Auto-Inject
- Buttons auto-appear on blog posts based on your placement setting
- If you drop a shortcode into a post manually, auto-inject skips that post — **no duplicates ever**
- Safe against AMP plugins, page builders, and related-post widgets that re-run the content filter

---

## Installation

1. Download the latest `.zip` from the [Releases](https://github.com/ixehad/qai-social-share/releases) page
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Upload the zip and click **Install Now → Activate**
4. Go to **Qai Social Share** in the left sidebar to configure

---

## Shortcodes

Use these anywhere in post or page content for manual placement:

| Shortcode | Output |
|---|---|
| `[kas_ai_buttons]` | AI Summarize row only |
| `[kas_social_buttons]` | Social Share row only |
| `[kas_buttons]` | Both rows together |

> **Tip:** If a post contains any of these shortcodes, the plugin automatically skips auto-injecting on that post — so buttons never appear twice.

---

## Configuration

### AI Prompt Template
The default prompt sent to each AI tool is:

```
Analyze and summarize the key insights from {url}
```

You can edit this under **Qai Social Share → Labels & Prompt Text**. The `{url}` placeholder is required and gets replaced with the post's permalink automatically.

### Placement Options
| Option | Description |
|---|---|
| Top of post (after meta) | Appears right below the post title/date — like PlayPlay.com |
| Before content | Just above the post body |
| After content | Below the post body |
| Shortcode only | Turns off auto-inject entirely; use shortcodes for manual placement |

### Button Shapes
- **Pill** — Fully rounded (default, matches the PlayPlay style)
- **Rounded** — Subtle rounded corners
- **Square** — Sharp corners

---

## File Structure

```
qai-social-share/
├── qai-social-share.php          # Main plugin bootstrap
├── includes/
│   ├── class-kas-settings.php    # Admin settings page, option schema, sanitization
│   ├── class-kas-render.php      # HTML output for AI and social button rows
│   └── class-kas-loader.php      # Shortcodes, content hook, asset enqueuing
└── assets/
    ├── front.css                 # Front-end button styles (pill/rounded/square variants)
    ├── front.js                  # Copy-link clipboard handler
    ├── admin.css                 # Settings page styles
    └── admin.js                  # Live preview instant toggles
```

---

## Security

- All output goes through `esc_url()`, `esc_html()`, `esc_attr()` — no raw user input ever reaches the DOM
- Settings save uses WordPress's standard `register_setting()` + `settings_fields()` CSRF protection
- Settings page gated behind `manage_options` capability (Administrators only)
- Stored option data type-checked on read — malformed/corrupted data falls back to safe defaults
- No external API calls, no tracking, no data sent anywhere except the share/summarize links the reader clicks

---

## Requirements

- WordPress 5.8+
- PHP 7.0+
- No additional plugins required

---

## Changelog

### 1.1.0
- Renamed to **Qai Social Share**
- Settings moved to top-level dashboard menu (was under Settings submenu)
- Fixed shortcode duplication bug — auto-inject now skips posts that already contain a shortcode
- Added run-once guard against repeated `the_content` filter firing in same request
- Added Live Preview block on settings page with instant JS toggles
- Fixed fatal error in preview rendering (wrong class reference for `get_network_definitions`)
- Fixed unstyled buttons on Pages — CSS/JS now loads on all singular content, not just Posts
- Added type-safety guards in `get_settings()` against corrupted stored data
- Hardened `$_GET` access per WordPress coding standards

### 1.0.0
- Initial release

---

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

Built by [KahfKids](https://kahfkids.com)
