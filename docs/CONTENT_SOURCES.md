# Documentation evidence and maintenance

This is a maintenance note for the website, not an instruction to alter the plugin.

## Read-only source

Source root: `C:/Users/DANTECH/OneDrive/Desktop/plugin zadex`.

The website documents the supplied implementation snapshot with plugin header version **0.1.1**, reviewed **21 October 2026**. `FEATURES.md` describes the product scope but still lists 0.1.0; the plugin header owns the documentation snapshot version. No claim is made that this local version is the currently published WordPress.org release.

| Documentation topic | Evidence inspected |
| --- | --- |
| Requirements and version | `zadex.php` header and requirement constants |
| Editor, layouts, theme, dynamic content, popups, reuse, assets, teams, output | Supplied `FEATURES.md` |
| Default enabled content types and breakpoint direction | `includes/Settings.php`, `includes/Admin/Admin.php` |
| Dashboard page names and settings | `includes/Admin/*.php` |
| Keyboard combinations and typing/access restrictions | `src/editor/core/shortcuts.ts` |
| Form actions and field controls | `widgets/form/manifest.json`, form integration credentials implementation |
| Stripe settings and server-side Checkout | `includes/Payments/StripeCheckout.php` |
| Off-Canvas link format and content/settings | `widgets/off-canvas/manifest.json` |
| Every public element and its controls | `widgets/*/manifest.json` |
| Arabic UI terminology | `languages/zadex-ar.json`, reviewed site glossary and authored guide prose |

The element importer excludes `accordion-item`, `tab`, `mega-menu-item` and `component`, which are helper/internal types, matching the existing public-element catalog. It retains all 102 public elements. Nested repeater controls inherit their parent tab if they do not declare a tab themselves. Metadata identifies responsive, explicitly dynamic and conditional controls, states, options and defaults. It does not infer extra capabilities from absent flags. Shared editor Advanced controls are covered in separate guides rather than fabricated into each manifest.

## Maintenance workflow

1. Read changed plugin behavior without writing into the source directory.
2. Update bilingual guide prose in `src/docs/guides.js` when procedures or requirements change.
3. Run `npm run docs:reference` to regenerate the manifest snapshot and complete bilingual control messages. The output paths are fixed under the website.
4. Review any new Arabic reference strings and preserve identifiers, code, service names and actual stored defaults.
5. Update the explicit review date after reviewing new source behavior.
6. Run `npm run verify:docs` and `npm run build`; check browser navigation and representative reference tables at desktop/mobile widths when UI changes.

Guides are informational. No WordPress installation, mail delivery, third-party credentials or financial transaction was executed to create the docs. Integration instructions stop at plugin-supported settings rather than inventing current provider onboarding steps. Playground illustrations remain distinct from actual plugin operation.
