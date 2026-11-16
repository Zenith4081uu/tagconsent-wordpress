=== TagConsent - DPDP Consent Management ===
Contributors: tagconsent, tecstaqops
Tags: dpdp, consent, cookie, privacy, gdpr, india, data protection, compliance, google consent mode
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

India's first DPDP Act 2023 compliant consent management plugin. Cookie consent banner, Google Consent Mode v2, preference centre, and full compliance dashboard.

== Description ==

**TagConsent** is India's purpose-built consent management platform for the **Digital Personal Data Protection (DPDP) Act, 2023** and **DPDP Rules, 2025**.

This WordPress plugin integrates your site with the TagConsent platform to deliver:

= Key Features =

* **DPDP-Compliant Cookie Consent Banner** — Granular, category-based consent (Essential, Analytics, Marketing, Functional) with customisable position and theme
* **Google Consent Mode v2** — Automatic GCM defaults (deny-all until consent) injected before gtag.js, fully compliant with Google's requirements
* **Auto-Block Cookies** — Non-essential cookies are blocked until the visitor gives consent
* **Preference Centre** — Shortcode `[tagconsent_preferences]` lets visitors manage their consent choices at any time
* **Cookie Declaration** — Shortcode `[tagconsent_cookie_declaration]` displays a transparent list of all cookies used
* **Cookie Scanner** — Automatically detects and categorises cookies on your site
* **Consent Logging** — Every consent action is logged with timestamp, IP (hashed), and categories — auditable proof for regulators
* **Dashboard Integration** — View analytics, compliance scores, and consent trends in your TagConsent account

= DPDP Act Coverage =

| Obligation | Section | Status |
|---|---|---|
| Informed consent before processing | §6 | ✅ Covered |
| Notice with purpose | §5 | ✅ Covered |
| Withdrawal of consent | §6(4) | ✅ Covered |
| Children's data consent | §9 | ✅ Covered |
| Cookie transparency | §5, §6 | ✅ Covered |

= Requirements =

* A free or paid [TagConsent](https://tagconsent.com) account
* PHP 7.4 or higher
* WordPress 5.8 or higher

== Installation ==

1. Upload the `tagconsent` folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins** menu
3. Go to **TagConsent → Settings**
4. Enter your **API Key** and **Domain ID** from your TagConsent dashboard
5. Configure banner position, theme, and blocking options under **TagConsent → Banner Config**

== Frequently Asked Questions ==

= What is the DPDP Act? =
The Digital Personal Data Protection Act, 2023 is India's comprehensive data protection law that regulates how personal data is collected, stored, and processed. It was enacted on 11 August 2023.

= Do I need a TagConsent account? =
Yes. The plugin connects to the TagConsent platform which handles consent storage, analytics, and compliance reporting. Sign up free at tagconsent.com.

= Does this work with Google Tag Manager? =
Yes. The plugin automatically sets Google Consent Mode v2 defaults before GTM loads, ensuring tags fire only after consent.

= Is this GDPR compatible? =
While designed for India's DPDP Act, the consent mechanisms (granular consent, withdrawal, logging) also satisfy GDPR requirements.

== Changelog ==

= 1.0.0 =
* Initial release
* Cookie consent banner with customisable position and theme
* Google Consent Mode v2 integration
* Auto-block non-essential cookies
* Preference centre shortcode
* Cookie declaration shortcode
* Admin dashboard with compliance summary
* Sync with TagConsent platform
