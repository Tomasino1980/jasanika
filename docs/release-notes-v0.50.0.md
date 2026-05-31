# Release Notes – v0.50.0

## M50 – Release Candidate & Stabilization

**Date:** 2026-05

**Type:** Release Candidate

---

## Project Summary

Jasanika is a custom WordPress 7 theme built for presenting and selling handmade decorations, wicker products and crocheted items. The theme uses a dark Elegant Handmade Boutique aesthetic with glassmorphism panels, purple brand color and Inter / Playfair Display typography.

The framework is built entirely with PHP, HTML5, CSS3 and Vanilla JavaScript — no external frameworks, no jQuery, no page builders.

---

## Implemented Modules

| Milestone | Module                          | Version  |
|-----------|----------------------------------|----------|
| M1        | Theme Skeleton                  | 0.1.0    |
| M2        | Layout Foundation               | 0.2.0    |
| M3        | Administration Foundation       | 0.3.0    |
| M4        | Menu System                     | 0.4.0    |
| M5        | Hero Section                    | 0.5.0    |
| M6        | Homepage Sections               | 0.6.0    |
| M7        | WooCommerce Foundation          | 0.7.0    |
| M8        | Blog                            | 0.8.0    |
| M9        | Gallery                         | 0.9.0    |
| M10       | Simple Slider                   | 0.10.0   |
| M11       | Custom Modules                  | 0.11.0   |
| M12       | Production Release              | 0.12.0   |
| M13       | Footer Builder                  | 0.13.0   |
| M14       | Homepage Builder Foundation     | 0.14.0   |
| M15       | CTA Section                     | 0.15.0   |
| M16       | Categories Section              | 0.16.0   |
| M17       | Latest Posts Section            | 0.17.0   |
| M18       | Feature Blocks                  | 0.18.0   |
| M19       | Hero Slider                     | 0.19.0   |
| M20       | WooCommerce Foundation          | 0.20.0   |
| M21       | WooCommerce Cart                | 0.21.0   |
| M22       | My Account                      | 0.22.0   |
| M23       | Comments System                 | 0.23.0   |
| M24       | Blog Foundation                 | 0.24.0   |
| M25       | Single Post                     | 0.25.0   |
| M26       | 404 Page                        | 0.26.0   |
| M27       | Search Results                  | 0.27.0   |
| M28       | Archive Templates               | 0.28.0   |
| M29       | Category Archive                | 0.29.0   |
| M30       | Tag Archive                     | 0.30.0   |
| M31       | Author Archive                  | 0.31.0   |
| M32       | Contact Page                    | 0.32.0   |
| M33       | WooCommerce Enhancements        | 0.33.0   |
| M34       | Login Branding                  | 0.34.0   |
| M35       | Menu Manager                    | 0.35.0   |
| M36       | Theme Settings                  | 0.36.0   |
| M37       | Featured Products               | 0.37.0   |
| M38       | Homepage Builder                | 0.38.0   |
| M39       | Slider Manager                  | 0.39.0   |
| M40       | Testimonials Manager            | 0.40.0   |
| M41       | Newsletter Manager              | 0.41.0   |
| M42       | SEO Manager                     | 0.42.0   |
| M43       | Cookie Manager                  | 0.43.0   |
| M44       | Diagnostics Manager             | 0.44.0   |
| M45       | Backup Manager                  | 0.45.0   |
| M46       | Profile Manager                 | 0.46.0   |
| M47       | Maintenance Mode                | 0.47.0   |
| M48       | Update Center                   | 0.48.0   |
| M49       | Theme Presets                   | 0.49.0   |
| M50       | Release Candidate & Stabilization | 0.50.0 |

---

## What Changed in M50

### Code Quality Fixes

- Fixed hardcoded CSS/JS asset version strings in all admin pages — they now use `wp_get_theme()->get('Version')` dynamically.
  - Affected: `backup-manager.php`, `cookie-manager.php`, `dashboard.php`, `diagnostics-manager.php`, `menu-manager.php`, `profile-manager.php`, `seo-manager.php`, `theme-settings.php`

### Update Center Fixes

- Added M49 (Theme Presets) and M50 (Release Candidate & Stabilization) to the changelog.
- Added missing modules (Diagnostics Manager, Theme Presets, Update Center) to the module list.

### Accessibility Improvements

- Added skip-to-content link in `header.php` for keyboard navigation.
- Added global `:focus-visible` outline styles in `reset.css`.
- Added skip-to-content reveal animation (hidden until focused).
- Added `:focus-visible` styles on all `.btn` components.
- Improved form element focus accessibility with `:focus-visible` outline in `forms.css`.

### Documentation

- Updated `README.md` to reflect the current project state (all 50 milestones listed).
- Created `docs/release-notes-v0.50.0.md` (this file).

### Version

- Updated `style.css` version from `0.49.0` to `0.50.0`.

---

## Known Issues

- Mobile hamburger/drawer navigation is not yet implemented. Desktop navigation is fully functional. Mobile navigation falls back to standard browser behavior.
- The `template-parts/navigation/`, `template-parts/cards/` and `template-parts/hero/` directories exist but are reserved for future use.

---

## Future Roadmap

The following areas are candidates for future milestones:

- **M51** – Mobile Navigation System (hamburger menu, off-canvas drawer)
- **M52** – Performance Optimization (image lazy loading, CSS delivery, caching)
- **M53** – Advanced SEO (structured data / JSON-LD, breadcrumbs)
- **M54** – Multilingual Support (WPML / Polylang compatibility)
- **M55** – Accessibility Audit Pass 2 (full WCAG 2.1 AA review)

---

## Compatibility

| Component      | Requirement | Status          |
|----------------|-------------|-----------------|
| WordPress      | ≥ 7.0       | ✓ Required      |
| PHP            | ≥ 8.2       | ✓ Required      |
| WooCommerce    | ≥ 8.0       | ✓ Supported     |
