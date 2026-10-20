# Zadex official website — first direction preview

Vue 3 + Vite. An original blue and cool-gray marketing site built around real Zadex footage.

## Run

```sh
npm install
npm run dev
```

Local preview: http://127.0.0.1:5186/

```sh
npm run build
npm run preview
```

Production files are in `dist/`. No deployment has been made during this design-preview stage.

## Interactive feature playground

`#feature-lab` contains a searchable, category-filtered catalog of 185 entries: the 102 public widgets plus 83 grouped capabilities/settings from FEATURES.md. The 102 widget titles and descriptions were imported read-only from the plugin manifests; internal accordion/tab/menu helpers are excluded.

Each entry opens an interactive illustration, built from 38 reusable simulation families. These are website demonstrations, not embedded copies of the complete WordPress editor. Some related features share the same representative scenario; the catalog description gives the full documented scope. Optional remote services (maps, audio, social embeds, integrations and payment providers) use clearly labeled local shells and flow illustrations. They are not connected. Video examples can use the existing local recording. No API model is needed at runtime.

The simulator component is loaded when the section comes into view or the visitor selects a feature. Only the selected simulation is mounted. Walkthroughs are finite, resettable, cancel on manual interaction and cancel when another feature is selected. Reduced-motion mode applies the steps without timed motion. Local sample exports can be downloaded and reimported; they are explicitly distinct from real Zadex site exports.

```sh
npm run verify:features
```

This validates unique IDs, all categories and families, the complete 102-widget catalog, and actual Vue server rendering of all 185 previews. The browser pass exercised a final guided step for every family plus manual form validation, component overrides, responsive inheritance, reset, search, filtering and pagination. Browser checks include desktop, 390px and 320px layouts. `catalog:features` is an optional read-only reimport from the local plugin; it only writes the website's data file.

### Documentation coverage

| FEATURES.md area | Playground group |
| --- | --- |
| Editor, sections, navigator, history, devices, language | Editor |
| Public elements, including the free advanced elements | Elements + WooCommerce |
| Content / Style / Advanced and motion / conditions | Element settings |
| Colors, fonts, spacing, classes, site layout, maintenance | Site settings |
| Templates, header behavior and theme compatibility | Theme builder |
| Data sources, custom fields and loop behavior | Dynamic content |
| Appearance, triggers, targeting and actions | Popups |
| Fields, multistep validation, spam, actions and submissions | Forms & integrations |
| Templates, components, global widgets and exports | Templates & reuse |
| Custom fonts, Adobe Fonts and icon sets | Fonts & icons |
| Notes, roles and design locking | Team & permissions |
| Output, generated CSS, conditional scripts and privacy | Performance & privacy |
| Dashboard panels, editor entry and retained designs | WordPress admin |

## Recording

- `public/media/zadex-in-action.mp4`: approximately 23 seconds, 1280 × 720, silent, looping.
- `public/media/zadex-in-action.webm`: alternative format. MP4 is preferred because it was verified to decode in the preview browser.
- `public/media/zadex-editor-poster.jpg`: actual isolated-editor capture.
- Footage is a real WordPress session with the copied Zadex plugin: template insertion, inline heading editing, description editing, mobile preview and save.
- Browser screenshots were captured at approximately six frames per second and encoded into a 30fps video. It is an edited demonstration, not a native 30fps screen recording; mouse movement is not captured. Pauses between takes are removed. This is suitable as a lightweight first hero treatment, with a higher-frame-rate production recording an optional later improvement.
- The source frames, timing manifest and encoder are in the ignored `.capture/` directory. Frames contain only the disposable demo session.

## Source protection

The development plugin at `C:/Users/DANTECH/OneDrive/Desktop/plugin zadex` was only read. Runtime plugin files, WordPress and the SQLite database were copied under `.capture/`. The WordPress plugin junction points to `.capture/plugin`; the copied SQLite drop-in points to the copied SQLite plugin. No runtime server was pointed at the development database.

The local WordPress capture server is loopback-only on port 8896. Its auto-login helper exists only in the ignored disposable copy; it must never be deployed. Only `dist/` is a website deployment artifact.

## Content and assets

Product claims are based on the supplied `FEATURES.md`. No customers, testimonials or performance statistics were invented. The lower-page design-system illustration is explicitly labeled as an illustration.

Sora, Public Sans and Noto Sans Arabic are loaded from Google Fonts. Icons are from Lucide. The small favicon is a provisional website asset, not a replacement for the plugin wordmark.

## Languages

The website supports English, Arabic, Spanish, German, Japanese, French, Brazilian Portuguese, Italian, Dutch, Polish and Russian. The header selector saves the choice locally. A `?lang=ar` parameter takes precedence and preserves section links. Language files load on demand from this project; there are no translation-service requests during a visit. Arabic sets the document direction to RTL and uses Noto Sans Arabic. Demo option values remain stable internal identifiers while their visible labels are translated.

Coverage includes marketing content, FAQs, accessibility labels, all 185 feature titles/descriptions, 38 walkthrough families, validation messages and editable initial sample content. Search includes localized text and original English terms. Changing language resets the illustrative canvas; entered demo content is temporary. The original recorded editor video and code identifiers remain in their original language.

Translations were bootstrapped with Google machine translation, with authored editorial overrides for prominent copy, UI labels and WordPress terminology. They have not received independent native-speaker review across all ten translated languages. Structural checks establish completeness and operability, not linguistic certification.

Run `npm run i18n:extract` after changing source messages, edit `src/locales/*.json`, and run `npm run verify:i18n`. The optional build-time `scripts/translate-messages.mjs` bootstrap sends source messages to a translation endpoint; it is never imported by the app. `scripts/i18n-editorial.mjs` reapplies the curated overrides. Keep `{parameters}` and code references intact. The i18n check validates every source message and interpolation parameter in every locale, then renders all 2,035 locale/feature combinations.

