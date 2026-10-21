---
name: Zadex website direction preview
description: An elegant editor-inspired marketing site centered on real WordPress footage.
colors:
  blue: "#3565eb"
  ink: "#1a1d23"
  soft: "#5b6472"
  line: "#dfe3ec"
  paper: "#fff"
  canvas: "#f4f5f8"
  study: "#eceef3"
  blue-hover: "#2c5ad6"
  blue-active: "#2650c0"
  docs-text: "#414b5b"
  docs-selected: "#e7edff"
  docs-note: "#eef3ff"
  docs-note-text: "#314a85"
  docs-code: "#f4f6fa"
  docs-search-border: "#d1d7e3"
typography:
  display:
    fontFamily: "Sora, sans-serif"
    fontSize: "clamp(62px, 6.7vw, 96px)"
    fontWeight: 500
    lineHeight: 1.09
    letterSpacing: "-0.04em"
  headline:
    fontFamily: "Sora, sans-serif"
    fontSize: "42px"
    fontWeight: 500
    lineHeight: 1.22
    letterSpacing: "-0.04em"
  body:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "13px"
    fontWeight: 400
    lineHeight: 1.85
  button:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "12px"
    fontWeight: 500
  docs-title:
    fontFamily: "Sora, sans-serif"
    fontSize: "clamp(29px, 3vw, 43px)"
    fontWeight: 500
    lineHeight: 1.25
    letterSpacing: "-0.035em"
  docs-body:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.95
  docs-reference:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 1.9
  arabic-body:
    fontFamily: "Tajawal, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.95
rounded:
  button: "8px"
  panel: "12px"
  film: "13px"
  docs-nav: "6px"
  docs-inset: "7px"
components:
  button-primary:
    backgroundColor: "{colors.blue}"
    textColor: "{colors.paper}"
    rounded: "{rounded.button}"
    padding: "14px 18px"
  button-primary-hover:
    backgroundColor: "{colors.blue-hover}"
  button-primary-active:
    backgroundColor: "{colors.blue-active}"
---

# Design System: Zadex website

## Overview

The built direction extends the existing editor identity: Zadex blue, white sheets on a cool-gray canvas, and a Sora wordmark ending in a blue dot. The character is innovative, elegant and contemporary, with generous marketing typography and quiet editor-like controls. Blue selection edges around “Made real.” connect the headline to the actual editing experience.

This document records the Vue 3 + Vite local preview, including the feature-library and documentation extensions, not final production approval. The review disposition is suitable to ship for local preview; no deployment is included. Sources are `PRODUCT.md`, `index.html`, `src/App.vue`, `src/style.css`, the feature-library components/styles, `README.md`, `docs/index.html` and `src/docs/`. The original plugin directory is outside this website's editing scope. The marketing website supports eleven locales; documentation supports English and Arabic. Hosting and final production approval remain open.

The separate `/docs/` entry extends this identity as a practical reading desk: compact wayfinding surrounds a quiet white article. Its task-led guides and element reference use a reading scale of their own. The docs-specific tokens below supplement the marketing scale; they do not replace it.

## Colors

The single accent is **blue**: primary download actions, the wordmark dot, selection borders, active controls, icons and film progress. **Ink** carries headings; **soft** carries supporting copy; **line** separates sections. **Canvas**, **paper** and **study** establish the desktop/sheet hierarchy. Preserve the inherited blue rather than introducing an unrelated brand palette.

Additional tonal ramps, dark editor surfaces, component markers and sculptural radii inside playground previews belong to miniature editor illustrations and sample artwork. They do not introduce new website brand colors or replace the surface and radius system above.

Documentation uses **docs-text** for sustained reading, **docs-selected** for the current guide, **docs-note** and **docs-note-text** for explanatory callouts, and **docs-code** for code blocks and table headers. The search field uses **docs-search-border**. These cool tonal surfaces support the inherited single blue accent without adding a second brand color.

## Typography

Sora supplies headings and the wordmark; Public Sans supplies prose, navigation and controls. Both load through Google Fonts with sans-serif fallbacks. Headings use medium weight and tight tracking; body copy uses relaxed leading. The logo is 29px/700 on desktop, with smaller variants in the footer and illustration.

The hero uses the frontmatter display scale, a 70px intermediate override, then `clamp(46px, 14.5vw, 62px)` on mobile. Section headings are generally 42px, with purpose-specific 32–55px variants. Supporting copy is mostly 12–13px; the hero introduction is 19px. Navigation and small controls sit at 10–12px. Small text inside the explicitly labeled miniature illustration is representational, not a general text-size standard.

Known review limitation: chapter descriptions drop from 11px to 9px at widths ≤1050px, then disappear at ≤760px. Treat that as a remaining typography refinement, not a preferred scale for new content.

Documentation uses **docs-title**, **docs-body** and **docs-reference** for article titles, reading text and control explanations. Leads use the reading size with 1.85 leading and a 68ch maximum. Section headings are 23px/1.45; article titles become 31px at ≤850px and 28px at ≤380px. Desktop guide navigation is 12px, increasing to 14px with 42px minimum rows on mobile. The final reference-table rules keep explanation text at the docs-reference size even at 320px; smaller control IDs (10px desktop, 9px mobile, 8px at ≤380px) are technical metadata, not a reading-text precedent. Code uses the system monospace stack with horizontal scrolling.

Arabic uses the user-requested Tajawal family throughout, with zero heading tracking and 1.55 heading leading. **arabic-body** captures the documentation reading role, not a replacement for the marketing site's existing Arabic sizes. English headings retain Sora and English prose retains Public Sans.

## Layout

The shell caps at 1440px, centered with 64px side padding (85px from 1400px, 36px at ≤1050px, 22px at ≤760px, 17px at ≤380px). A ruled header precedes an asymmetric 1.3fr/0.7fr headline and introduction. The full-width 16:9 editor film follows after 43px. Major sections use 110px top spacing, reduced to 72px on mobile.

Feature exploration pairs a left selector with a right design-system illustration; principles form three columns. Free-feature and FAQ sections use split layouts. At ≤760px these sections stack, navigation becomes a disclosure menu, and the hero becomes one column. Film chapters occupy their own mobile row, with visible “View larger” and play/pause labels beneath. The corrected mobile headline and enlarged-view affordance are part of the reviewed preview.

Documentation caps its shell at 1600px with 40px side padding, increasing to 50px at ≥1400px. The desktop grid has a 225px guide rail, an article column capped at 780px and a 165px section index, separated by 38px (46px at ≥1400px). A sticky compact header and independently scrollable sticky side rails preserve orientation. White articles use 36px by 42px padding. At ≤1200px the section index becomes an inline disclosure and the guide rail narrows to 200px. At ≤850px the guide rail becomes an in-flow disclosure above the article, rather than an overlay; shell padding becomes 22px and article padding 27px by 24px. At ≤380px these become 15px and 24px by 18px, and guide groups form one column. Logical spacing supports RTL. Print removes navigation and media to leave the article and reference content.

## Elevation & Depth

Depth is restrained: white sheets, cool tonal nesting, thin rules and blue selection edges do most of the work. The film uses `0 15px 40px #1b2a4510`; the illustrative sheet uses `0 9px 25px #1b2a450b`. Do not spread these shadows across every section. Hover motion is a 1px lift on primary buttons, with 150ms background/transform transitions.

Documentation stays flat: white articles, pale callouts, selected navigation fills and thin rules establish hierarchy without card shadows. Inherited visible focus rings remain blue; search uses a 2px outline with 3px offset on focus within. The inherited reduced-motion rules apply to this entry too.

## Shapes

Use soft rectangular surfaces: 8px buttons, 12px panels, a 13px desktop film window (8px mobile). Fine 1px rules organize the page. Small circular blue dots identify the brand, live-film cue and active chapter. The headline's thin blue selection rectangle and small square handle are the signature editor reference.

Documentation reuses the panel radius for articles and result/status surfaces, and the button radius for search and control tables. The smaller **docs-nav** radius belongs to guide rows, filters and recovery actions; **docs-inset** belongs to notes, code blocks and embedded film. These purposeful variants do not reset the marketing radius scale.

## Components

- **Primary action:** blue fill, white label, arrow icon, 46px minimum height on the base button. Hover darkens and lifts; active darkens again. All download links lead to the supplied WordPress.org plugin page.
- **Navigation:** restrained text links, blue hover, mobile menu with expanded state. A skip link and 2px blue focus-visible outline with 5px offset support keyboard navigation.
- **Hero film:** actual silent, looping WordPress/Zadex session in `public/media/zadex-in-action.mp4`, with WebM alternative and an actual editor poster. Approximately 23 seconds at 1280×720, it shows template insertion, heading/description edits, mobile preview and save. Screenshots sampled at roughly six frames per second were encoded at 30fps; pauses were edited out and cursor movement is absent. This is an edited demonstration, not native 30fps capture. Capture sources live in ignored `.capture/`; only website output is a deployment artifact.
- **Film controls:** chapters seek to 0, 7 and 17 seconds; active state tracks playback. Play/pause, progress, a direct larger-video link, poster and load-error link are present. Autoplay is attempted only without reduced-motion preference; enabling that preference pauses playback. Manual playback remains available. CSS also disables smooth scrolling and minimizes transitions for reduced motion.
- **Feature selector:** three button rows update descriptive content and the adjacent illustration. Blue indicates selection. The miniature site is explicitly labeled “Design system illustration”; it is not presented as product footage.
- **Free-feature manifest and FAQ:** a white checklist panel and ruled accordion rows, respectively. FAQ buttons expose expanded state; the first answer starts open. Lucide supplies the line icons throughout.

### Feature playground

The searchable library at `#feature-lab` contains 185 entries: 83 capabilities/settings and 102 public widgets, organized into 14 groups. Search, category filtering, result counts, empty-state recovery and pagination show nine items per page. A selected blue row opens a labeled interactive illustration; 38 shared simulation families provide representative scenarios rather than full copies of every editor feature. Only the selected simulation is mounted, and the simulation module loads lazily when the section approaches the viewport or is activated.

The desktop layout pairs a catalog column with the playground. At ≤650px it stacks, presents catalog choices in two columns, scrolls a selection into view and exposes “Browse features” to return to the list. The canvas repeats the established cool desk, white sheet, blue selection edges and Public Sans controls. Manual controls sit alongside finite, resettable three-step walkthroughs. Pointer, keyboard and input/change interactions cancel guided playback; selecting another entry also cancels it. Reduced-motion preference removes timed walkthrough motion. Accordion examples support independent item expansion when their multiple-open option is enabled. Responsive examples expose seven device widths, with desktop values inherited until a device-specific override is set.

Forms, payment flows and external-service shells are explicitly local illustrations: no transactions, submissions or integration requests occur. Sample exports/imports are browser-local demonstration files, distinct from real site exports. HTML/code examples display plain text and illustrate removal of unsafe snippets; this is not a full security audit or a production sanitization guarantee. The link back to the real editor film preserves the distinction between illustration and product evidence.

Feature-library review is complete for local preview after keyboard/input cancellation and independent accordion expansion were fixed; no required review fixes remain. Reported validation includes `npm run verify:features` rendering all 185 previews, browser checks reaching the final guided step across all 38 families plus focused manual interactions, and a passing build. Desktop, mobile and 320px visual checks are recorded in `.impeccable/review/feature-desktop.jpg`, `feature-mobile.jpg` and `feature-320.jpg`. This validation does not establish production approval.

### Documentation reading desk

The static multi-page `/docs/` entry contains 30 English/Arabic practical guides plus a reference for all 102 public elements and 2,275 imported controls from plugin 0.1.1. The original plugin remains read-only. The compact header retains brand, documentation identity and an English/Arabic selector; other marketing locales fall back to English on this route, while the homepage retains all eleven languages.

Search covers translated guide content and control labels alongside original element names. Results reveal in increments of 30 and provide a clear-search recovery state. The element index also offers local search and category filtering. Native hash links identify articles and sections; activating the current hash still restores its position and heading focus. The section index tracks the current section. Copy-link feedback, print, related guides and previous/next navigation support continued reading. Loading, load failure, missing-page and empty-search states provide explicit recovery actions.

Reference tables retain visible control labels, IDs, defaults and applicable metadata; long values wrap inside fixed table columns. Arabic flow mirrors the reading layout while code and technical values retain their original direction. Do not infer functionality beyond the imported reference or turn technical metadata into primary prose.

The documentation finish review resolved DOC-F01 and returned **SHIP**, with no remaining material finding at the reviewed scope. The build and source checks passed. Reviewed views are `.impeccable/review/docs-desktop.jpg`, `docs-mobile.jpg`, `docs-controls-mobile.jpg` and `docs-controls-320.jpg`. This records the reviewed local implementation, not deployment approval. The regex detector was degraded; its advisory scale differences are not authority to replace the established marketing tokens or the requested Tajawal font.

## Do's and Don'ts

The language selector displays native language names without flags. Eleven locales share the existing visual identity. Arabic uses RTL document flow and Tajawal; the Zadex wordmark, real editor film and code retain their original direction. Layouts allow translated text to wrap, and mobile headers retain the language selector and navigation toggle. Non-English hero type scales to accommodate longer copy. Dictionaries load on demand; selection persists in local storage and a shareable `lang` query parameter. The playground remounts on language changes so localized sample defaults and walkthroughs stay consistent.

- **Do** preserve the blue-dot identity and cool desk/white sheet relationship.
- **Do** keep real editor evidence central and distinguish illustrative material explicitly.
- **Do** retain playback controls, larger viewing, responsive stacking and keyboard-visible states.
- **Do** preserve the documentation reading scale, translated wayfinding and in-flow mobile guide disclosure.
- **Do** keep documentation tokens scoped to their reading roles and retain Tajawal for Arabic.
- **Do** derive product claims from supplied product evidence; all described features belong to the free plugin.
- **Don't** invent customers, testimonials, benchmarks or premium tiers.
- **Don't** treat the first-preview review as production approval or deploy disposable capture infrastructure.
