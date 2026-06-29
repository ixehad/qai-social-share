=== Qai Social Share ===
Contributors: ixehad
Tags: social share, ai summarize, share buttons, social buttons, content sharing
Requires at least: 5.6
Tested up to: 6.7
Stable tag: 1.2.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add AI Summarize buttons (ChatGPT, Claude, Perplexity, Grok, Google) and Social Share buttons to your WordPress blog posts.

== Description ==

**Qai Social Share** adds two powerful rows of buttons to your blog posts — fully configurable from the WordPress dashboard with no coding required.

**🤖 AI Summarize Row**
Let readers instantly summarize your post in their preferred AI tool:

* ChatGPT
* Claude
* Perplexity
* Grok
* Google AI Mode

**📣 Social Share Row**
One-click sharing to all major platforms:

* Facebook
* X (Twitter)
* WhatsApp
* LinkedIn
* Telegram
* Pinterest
* Copy Link button (copies post URL to clipboard)

**Key Features:**

* **Two separate settings tabs** — AI Summarize and Social Share each have their own dedicated settings page
* **Independent placement** — set AI buttons at the top of posts, social buttons at the bottom — independently
* **Three button styles** — Text only, Icon only, or Icon + Text per row
* **Three button shapes** — Pill, Rounded, or Square
* **Enable/disable per row** — turn either row on or off independently
* **Toggle individual networks** — show only the platforms relevant to your audience
* **Live preview** — see exactly how buttons look before saving
* **Smart shortcode detection** — using a shortcode in a post automatically suppresses auto-insertion for that row only, preventing duplicates
* **No tracking, no data collection** — this plugin sends no data anywhere. Buttons simply open the selected platform in a new tab

**Shortcodes**

Place buttons manually anywhere in a post:

* `[qai_ai_buttons]` — AI Summarize row only
* `[qai_social_buttons]` — Social Share row only
* `[qai_buttons]` — Both rows together

**External Services**

This plugin provides links to the following third-party services. When a reader clicks a button, they are redirected to that service's website. No data is sent automatically — the redirect only happens on explicit user click.

* ChatGPT (OpenAI) — https://chatgpt.com — [Terms of Use](https://openai.com/policies/terms-of-use)
* Claude (Anthropic) — https://claude.ai — [Terms of Use](https://www.anthropic.com/legal/consumer-terms)
* Perplexity — https://www.perplexity.ai — [Terms of Use](https://www.perplexity.ai/hub/legal/terms-of-service)
* Grok (xAI / X) — https://x.com/i/grok — [Terms of Service](https://x.com/en/tos)
* Google — https://www.google.com — [Terms of Service](https://policies.google.com/terms)
* Facebook — https://www.facebook.com — [Terms of Service](https://www.facebook.com/terms.php)
* X (Twitter) — https://twitter.com — [Terms of Service](https://twitter.com/tos)
* WhatsApp — https://api.whatsapp.com — [Terms of Service](https://www.whatsapp.com/legal/terms-of-service)
* LinkedIn — https://www.linkedin.com — [Terms of Service](https://www.linkedin.com/legal/user-agreement)
* Telegram — https://t.me — [Terms of Service](https://telegram.org/tos)
* Pinterest — https://pinterest.com — [Terms of Service](https://policy.pinterest.com/terms-of-service)

No personal data is transmitted to any of these services by this plugin. Redirection happens entirely in the reader's browser upon clicking.

== Installation ==

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin** in your WordPress admin
3. Upload the zip and click **Install Now → Activate**
4. Go to **Qai Social Share** in the left sidebar to configure

**Configuration:**

* Click **AI Summarize** tab to configure AI buttons (position, style, which tools to show)
* Click **Social Share** tab to configure share buttons (position, style, which networks to show)
* Both rows auto-insert on posts based on your position setting
* To place buttons manually, set position to "Shortcode only" and paste `[qai_ai_buttons]` or `[qai_social_buttons]` into post content

**⚠️ Avoid duplicates:** Do not use both a position setting and a shortcode for the same row in the same post. Pick one method per post per row.

== Frequently Asked Questions ==

= Will this slow down my site? =

No. All button styles use inline SVG icons — no external image requests. CSS and JS are minimal and only load on singular post/page views.

= Does this plugin collect any user data? =

No. This plugin collects and stores no user data whatsoever. It does not make any external requests on its own. Buttons are plain links that redirect the reader's browser to the selected platform only when clicked.

= Can I show only some AI tools or social networks? =

Yes. Under each settings tab there are individual checkboxes for every network and AI tool. Uncheck any you don't want to display.

= What does the AI prompt send to the AI tool? =

When a reader clicks an AI button, their browser opens the AI tool's website with a pre-filled prompt containing your post's URL. The AI tool then fetches and summarizes your post independently. No data passes through this plugin's server.

= Can I customize the text above the buttons? =

Yes. You can edit the row label ("Summarize with" and "Share this article") in each settings tab.

= Can I use shortcodes on Pages, not just Posts? =

Yes. Shortcodes work on Pages and custom post types. Auto-insertion is limited to Posts by default.

= What happens if I use both a shortcode and the auto-insert position on the same post? =

The plugin is smart about this — if it detects a shortcode for a row already in the post content, it skips auto-inserting that specific row. So you won't get duplicates if you use `[qai_social_buttons]` in a post while auto-insert is enabled; the social row will come from the shortcode and the AI row will still auto-insert normally.

== Screenshots ==

1. AI Summarize buttons displayed on a live blog post (Icon + Text style, Pill shape)
2. AI Summarize settings page — placement, button style, prompt text, and tool toggles
3. Social Share settings page — placement, button style, network toggles, and Copy Link option

== Changelog ==

= 1.2.0 =
* Added two separate admin tabs: AI Summarize and Social Share
* Added independent position selector per row (AI defaults to top, Social defaults to bottom)
* Added three button display styles: Text only, Icon only, Icon + Text
* Added inline SVG icons for all AI tools and social networks
* Fixed shortcode duplication bug — shortcode detection is now per-row, not all-or-nothing
* Renamed shortcodes from [kas_*] to [qai_*]
* Added enable/disable toggle per row
* Added live preview on each settings tab
* Added duplicate-warning notice on position selectors
* Improved help text throughout settings

= 1.1.0 =
* Renamed plugin to Qai Social Share
* Moved settings to top-level dashboard menu
* Added live preview block on settings page
* Fixed fatal error in preview rendering
* Fixed unstyled buttons on Pages
* Added type-safety guards against corrupted option data

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
Shortcodes renamed from [kas_*] to [qai_*]. Update any shortcodes in existing posts before upgrading.
