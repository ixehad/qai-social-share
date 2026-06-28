# Qai Social Share

A WordPress plugin that adds **AI Summarize** buttons and **Social Share** buttons to your blog posts — fully configurable from the WordPress dashboard, no theme editing required.

![Plugin Version](https://img.shields.io/badge/version-1.1.0-blue) ![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b) ![PHP](https://img.shields.io/badge/PHP-7.0%2B-777bb4) ![License](https://img.shields.io/badge/license-GPL--2.0-green)

---

## Screenshots

### Live Post — Buttons on a Real WordPress Blog Post
![Buttons on live post](screenshots/live-post.jpg)

### Admin Settings — Live Preview & Display Options
![Settings page](screenshots/settings-page.jpg)

### Admin Settings — Network Toggles
![Settings options](screenshots/settings-options.jpg)

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
- **Live Preview** — see exactly what your buttons look like before saving

### 🔁 Smart Auto-Inject
- Buttons auto-appear on blog posts based on your placement setting
- If you drop a shortcode into a post manually, auto-inject skips that post — **no duplicates ever**

---

## Installation

1. Download the latest `.zip` from the [Releases](https://github.com/ixehad/qai-social-share/releases) page
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Upload the zip and click **Install Now → Activate**
4. Go to **Qai Social Share** in the left sidebar to configure

---

## Shortcodes

| Shortcode | Output |
|---|---|
| `[kas_ai_buttons]` | AI Summarize row only |
| `[kas_social_buttons]` | Social Share row only |
| `[kas_buttons]` | Both rows together |

> If a post contains any of these shortcodes, the plugin automatically skips auto-injecting — buttons never appear twice.

---

## Configuration

### Placement Options
| Option | Description |
|---|---|
| Top of post (after meta) | Appears right below the post title/date |
| Before content | Just above the post body |
| After content | Below the post body |
| Shortcode only | Turns off auto-inject; use shortcodes for manual placement |

### Button Shapes
- **Pill** — Fully rounded (default)
- **Rounded** — Subtle rounded corners
- **Square** — Sharp corners

---

## File Structure

```
qai-social-share/
├── qai-social-share.php
├── includes/
│   ├── class-kas-settings.php
│   ├── class-kas-render.php
│   └── class-kas-loader.php
└── assets/
    ├── front.css
    ├── front.js
    ├── admin.css
    └── admin.js
```

---

## Security

- All output escaped via `esc_url()`, `esc_html()`, `esc_attr()`
- CSRF protection via WordPress `register_setting()` + `settings_fields()`
- Settings page gated behind `manage_options` capability
- Stored option data type-checked against corruption

---

## Requirements

- WordPress 5.8+
- PHP 7.0+

---

## Changelog

### 1.1.0
- Renamed to **Qai Social Share**
- Settings moved to top-level dashboard menu
- Fixed shortcode duplication bug
- Added Live Preview on settings page
- Fixed fatal error in preview rendering
- Fixed unstyled buttons on Pages
- Added type-safety guards in `get_settings()`

### 1.0.0
- Initial release

---

## License

GPL v2 or later.

---

Built by Jehadul Islam