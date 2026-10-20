import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
// Read-only import. This script only writes inside this website's src/data directory.
const plugin=process.argv[2]||'C:/Users/DANTECH/OneDrive/Desktop/plugin zadex';
const groups={editor:'Editor',elements:'Elements',design:'Element settings',site:'Site settings',theme:'Theme builder',dynamic:'Dynamic content',popup:'Popups',forms:'Forms & integrations',reuse:'Templates & reuse',assets:'Fonts & icons',team:'Team & permissions',performance:'Performance & privacy',admin:'WordPress admin',commerce:'WooCommerce'};
const mapping={
 edit:['heading','text','animated-headline','text-path'], layout:['container','grid','divider','spacer'],
 tabs:['tabs','product-data-tabs'],accordion:['accordion','toggle'],popup:['off-canvas'],
 media:['image','image-box','gallery','featured-image','product-images'],carousel:['image-carousel','slides','media-carousel','testimonial-carousel','reviews','loop-carousel'],
 links:['button','icon','icon-box','icon-list','social-icons','bio-links','share-buttons'],notice:['alert','testimonial','blockquote','price-table','price-list','call-to-action'],
 metric:['counter','progress-bar','star-rating','reading-progress','product-rating'],countdown:['countdown'],flip:['flip-box'],code:['code-highlight','html','svg','shortcode'],
 collection:['posts','portfolio','gallery-pro','archive-posts','loop-grid','loop-filter','product-categories','products','product-related','product-upsells'],
 navigation:['nav-menu','mega-menu','post-navigation','breadcrumbs','sitemap','table-of-contents','sidebar'],
 search:['search','search-form'],form:['form','form-basic','login','comments'],
 dynamic:['site-logo','site-title','post-title','post-content','post-excerpt','post-info','author-box','archive-title','product-title','product-meta','product-short-description'],
 commerce:['add-to-cart','cart','checkout','my-account','menu-cart','purchase-summary','product-price','product-stock'],
 payment:['paypal-button','stripe-button'],embed:['google-maps','soundcloud','facebook','video','video-playlist'],motion:['lottie'],hotspot:['hotspot'],anchor:['menu-anchor','read-more'],reuse:['template']
};
const kindByType=Object.fromEntries(Object.entries(mapping).flatMap(([kind,types])=>types.map(type=>[type,kind])));
const skipped=['accordion-item','mega-menu-item','tab','component'];
const widgets=fs.readdirSync(path.join(plugin,'widgets')).filter(type=>!skipped.includes(type)&&fs.existsSync(path.join(plugin,'widgets',type,'manifest.json'))).map(type=>{
 const manifest=JSON.parse(fs.readFileSync(path.join(plugin,'widgets',type,'manifest.json'),'utf8'));
 if(!kindByType[type])throw new Error('Missing simulation: '+type);
 return {id:'widget-'+type,title:manifest.title,description:manifest.description,group:manifest.category==='woocommerce'?'commerce':'elements',subcategory:manifest.category==='pro'?'Advanced · free':manifest.category,kind:kindByType[type],type,widget:true,keywords:manifest.keywords||[],requirement:manifest.category==='woocommerce'?'Requires WooCommerce':(['google-maps','facebook','soundcloud','paypal-button','stripe-button'].includes(type)?'Optional external service':null)};
});
const rows=`
live-editing|editor|edit|Live page editing|Edit on the real WordPress page, with your actual theme around it.
inline-formatting|editor|edit|Inline text & formatting|Write headings, text and button labels in place; format text with the inline toolbar.
drag-and-drop|editor|organize|Drag & drop|Move widgets between containers and reorder them on the canvas.
element-toolbar|editor|organize|Element toolbar|Select a parent, split columns, duplicate or delete from the element toolbar.
context-menu|editor|organize|Copy, paste & style transfer|Copy elements or their styles, duplicate them and save templates or components.
section-picker|editor|layout|12 column structures|Insert a section between existing sections with a ready-made column structure.
resplit|editor|layout|Re-split without losing content|Change a section's columns and keep its contents. Undo the split in one step.
empty-page|editor|layout|Start from a blank page|Choose the first section's structure directly from the empty canvas.
navigator|editor|organize|Navigator|Rename, reorder, hide and lock elements in the page tree.
quick-search|editor|search|Quick search & shortcuts|Find widgets and settings with Ctrl+K and discover the keyboard shortcuts.
undo|editor|history|Undo, redo & history|Step backward and forward through your design changes using the history panel.
revisions|editor|history|Saved revisions|Compare and restore a previously saved version of the page.
save-publish|editor|workflow|Draft, save & publish|Keep a draft or publish your page; preview without editing controls.
breakpoints|editor|responsive|Seven responsive breakpoints|Enable and customize mobile, tablet, laptop, desktop and widescreen widths.
responsive-values|editor|responsive|Responsive & inherited values|Set values per screen and hover state. See which values inherit from a larger device.
dark-mode|editor|appearance|Dark mode|Switch the editor chrome to a dark appearance while keeping the page in view.
arabic|editor|appearance|Arabic & right-to-left|Use the translated Arabic editor with a fully mirrored right-to-left interface.
content-style-advanced|design|style|Content, Style & Advanced|Keep content, appearance and advanced element controls in separate tabs.
box-model|design|style|Spacing, size & alignment|Adjust margins, padding, width, height, alignment and order.
position|design|style|Position & layers|Place elements with fixed or absolute positioning and control their stacking.
visibility|design|responsive|Device visibility|Hide an element on selected screen sizes.
attributes|design|code|IDs, classes & safe attributes|Add identifiers, classes and allowed custom attributes. Script-capable attributes are rejected.
entrance|design|motion|Entrance animations|Choose how an element appears when it enters view.
motion-effects|design|motion|Scroll & mouse effects|Explore parallax, opacity, blur, rotation, scale, mouse tracking and 3D tilt.
keyframes|design|motion|Scroll-linked keyframes|Connect an animation's keyframes to progress through the page.
interactions|design|motion|Interaction timelines|Use page load, visibility, hover or click to animate, toggle classes, show elements or open popups.
sticky|design|motion|Sticky elements|Keep an element at the top or bottom and change its appearance after scrolling.
display-conditions|design|conditions|Display conditions|Show content by role, login, time, page, query parameter or dynamic field.
global-colors|site|style|Global colors|Change a shared color and update the elements that reference it.
global-fonts|site|style|Global fonts|Define reusable typography and change linked text consistently.
spacing-variables|site|style|Spacing variables|Use shared spacing values across your design.
global-classes|site|style|Global classes|Apply a saved style to multiple elements, with class import and export.
theme-style|site|style|Site-wide theme styles|Set consistent headings, buttons, links and form appearance.
site-layout|site|layout|Content width & default gaps|Set the content width and default container spacing for the site.
lightbox|site|media|Image lightbox|Open images in an enlarged view with a consistent site-wide style.
transitions|site|motion|Page transitions & loading|Choose a transition between pages and a loading screen.
floating-buttons|site|links|Floating contact buttons|Add floating WhatsApp, phone, email and back-to-top actions.
maintenance|site|conditions|Maintenance & coming soon|Choose a visitor-facing page and allowed roles. Maintenance returns HTTP 503.
theme-templates|theme|theme|Headers, footers & page templates|Design single, archive, search, 404, loop item and popup templates, plus product and shop templates.
theme-conditions|theme|conditions|Template display rules|Assign templates to the site, home, post types, terms, pages and archives with exclusions.
header-behavior|theme|theme|Sticky & transparent headers|Keep a header visible or place a transparent header above the content.
theme-compatibility|theme|theme|Classic & block themes|Work with block and classic themes, including dedicated compatibility for common WordPress themes.
post-data|dynamic|dynamic|Post data bindings|Connect titles, excerpts, content, dates, authors, URLs, terms, featured images and comment counts.
site-data|dynamic|dynamic|Site, user & archive data|Bind site identity, user information, archive titles, the current date and URL parameters.
custom-fields|dynamic|dynamic|Custom fields & integrations|Read custom fields, including ACF, Pods, Toolset and Meta Box when installed.
loop-builder|dynamic|collection|Visual loop builder|Design one item and repeat it for any post type in a grid or carousel.
loop-pagination|dynamic|collection|Pagination & load more|Choose numbered pages, load more or infinite scrolling for a loop.
loop-filters|dynamic|collection|Live taxonomy filters|Let visitors filter by category or tag without reloading the page.
alternate-loop|dynamic|collection|Alternate loop templates|Use another item template for selected positions, such as every third item.
popup-layouts|popup|popup|Popup layouts & appearance|Choose a centered, slide-in, fullscreen, top-bar or bottom-bar layout; control overlay and closing behavior.
popup-triggers|popup|popup|Popup triggers|Trigger by delay, scroll, element, click, inactivity, exit intent or ad-block detection.
popup-targeting|popup|conditions|Popup targeting & frequency|Limit by visits, pages, referrer, login or device, with a view cap and cooldown after closing.
popup-actions|popup|popup|Open popups from anywhere|Open from buttons, links, form submissions or countdown completion.
form-fields|forms|form|15 form field types|Use text, email, phone, number, date, time, select, radio, checkbox, acceptance, upload, password, hidden and HTML fields.
multi-step|forms|form|Multi-step forms|Guide visitors through steps with progress and validation at each stage.
spam|forms|workflow|Spam protection|Use a honeypot, submission limits and optional reCAPTCHA v2 or v3.
email-actions|forms|workflow|Email & follow-up actions|Send two independent emails, redirect visitors or open a popup after submission.
webhooks|forms|workflow|Webhooks & automatic retries|Send to Zapier, Make or n8n endpoints and retry when delivery fails.
email-marketing|forms|workflow|Email marketing integrations|Connect Mailchimp, ActiveCampaign, GetResponse, Kit, MailerLite or Drip.
notifications|forms|workflow|Slack & Discord notifications|Route submission notifications to an optional Slack or Discord integration.
submissions|forms|admin|Submission inbox & CSV|Review stored form messages, mark them read, delete them or export a CSV.
starter-library|reuse|reuse|Starter section library|Start with hero, features, testimonials, pricing, CTA, FAQ, contact or footer sections and a Link in bio page.
save-template|reuse|reuse|Save sections & pages|Save a section or whole page as a template and use it again.
components|reuse|reuse|Connected components|Edit a shared component once; keep all instances linked, with selected properties overridable per instance.
global-widgets|reuse|reuse|Global widgets|Keep a single shared widget synchronized wherever it appears.
page-json|reuse|transfer|Page JSON import & export|Move a page between sites using a JSON file.
site-kits|reuse|transfer|Complete site kits|Move pages, templates, settings and images in a ZIP, with links adjusted for the destination.
custom-fonts|assets|assets|Custom font uploads|Use WOFF2, WOFF or TTF families with multiple weights and styles.
adobe-fonts|assets|assets|Adobe Fonts|Connect a project ID to use your Adobe Fonts collection.
font-awesome|assets|assets|Included Font Awesome icons|Choose from the bundled Font Awesome Free library.
custom-icons|assets|assets|Custom icon sets|Import icon sets from Fontello, IcoMoon or Fontastic.
notes|team|team|Notes, replies & mentions|Pin comments to elements, reply, mention teammates by email and resolve or reopen threads.
roles|team|team|Role Manager|Choose no access, content-only or full access per role. Content-only restrictions are also enforced on the server.
design-lock|team|team|Lock design elements|Protect selected elements from editing.
clean-html|performance|performance|Clean HTML output|Render each widget without unnecessary wrapper elements.
generated-css|performance|performance|CSS generated at save time|Generate styling when saving and load it in the page head.
conditional-js|performance|performance|Only the scripts you need|Load widget runtimes such as sliders and popups only when a page needs them.
local-assets|performance|performance|Local assets & optional services|Load bundled assets locally, with external requests only for services you choose to use.
privacy|performance|performance|No developer tracking|Zadex does not track visitors or send their data to its developers.
dashboard|admin|admin|WordPress dashboard pages|Manage Zadex settings, theme templates, components, submissions, fonts, icons, site kits and roles from WordPress.
edit-entry|admin|admin|Edit with Zadex|Open the builder from the pages list, WordPress editor or admin bar.
safe-html|performance|code|Safe HTML & no custom scripts|HTML elements reject scripts. Custom CSS or JavaScript entry is not available.
deactivation|admin|workflow|Designs retained on deactivation|Deactivation shows standard WordPress content; the saved Zadex design returns when the plugin is reactivated.
`;
const capabilities=rows.trim().split('\n').map(row=>{const[id,group,kind,title,description]=row.split('|');return{id,title,description,group,kind,type:id,widget:false,keywords:[]};});
const catalog=[...capabilities,...widgets];
if(widgets.length!==102)throw new Error('Expected 102 public widgets, got '+widgets.length);
const outputDirectory=fileURLToPath(new URL('../src/data/',import.meta.url));
if(outputDirectory.startsWith(path.resolve(plugin)+path.sep))throw new Error('Refusing to write inside the source plugin');
fs.mkdirSync(outputDirectory,{recursive:true});
fs.writeFileSync(path.join(outputDirectory,'features.json'),JSON.stringify({groups,catalog},null,2)+'\n');
console.log(`${capabilities.length} capability demos + ${widgets.length} widget demos = ${catalog.length} entries`);
