<script setup>
import {ref,computed,watch,onBeforeUnmount} from 'vue';
import {Download,ArrowLeft,ArrowRight,Check,Eye,FileText,RotateCcw,Layers,PanelsTopLeft,MousePointerClick,ShoppingBag,Palette,ShieldCheck,Lock,Zap,Copy,Plus,Minus,Info} from 'lucide-vue-next';
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
import {locale} from '../i18n';
import {m,loadMigratorLocale} from './i18n';
import {release,changelog} from './release';
const zadexPlugin='https://wordpress.org/plugins/zadex/';
const home=computed(()=>'/?lang='+locale.value);
const docs=computed(()=>'/docs/?lang='+locale.value);
const formatDate=value=>new Intl.DateTimeFormat(locale.value,{year:'numeric',month:'long',day:'numeric'}).format(new Date(value+'T12:00:00Z'));
const size=computed(()=>new Intl.NumberFormat(locale.value,{maximumFractionDigits:0}).format(release.bytes/1024)+' KB');
const released=computed(()=>formatDate(release.date));

// Hero: drag to compare the original page with the converted one.
const split=ref(40);

const capabilities=[
 {icon:Layers,title:'Everything on the page',text:'Sections, columns and containers (flexbox and grid), including the new Elementor 4 atomic elements. Every core widget and most Pro widgets.',items:['Responsive styles for tablet and mobile','Hover states','Colors, typography, spacing, borders, shadows and backgrounds']},
 {icon:PanelsTopLeft,title:'Theme Builder',text:'Headers, footers, single posts, archives, 404 pages, loop items and saved templates move over with their display conditions.',items:['Entire site','All posts','All archives']},
 {icon:MousePointerClick,title:'Popups',text:'Design, triggers and display conditions come along. Buttons and forms that open a popup keep working.',items:['On page load','On exit intent','After scrolling']},
 {icon:ShoppingBag,title:'WooCommerce',text:'Single product and shop templates, plus the cart, checkout and my account pages.',items:['Product templates','Shop archive','Cart, checkout, my account']},
 {icon:Palette,title:'Site settings',text:'Global colors, global fonts, content width and responsive breakpoints are carried into Zadex settings.',items:['Global colors','Global fonts','Breakpoints']}
];
const widgets=['Slides','Animated headline','Flip box','Call to action','Price table','Countdown','Testimonial carousel','Posts','Portfolio','Gallery','Multi-step form','Login','Nav menu','Mega menu','Table of contents','Share buttons'];

// Migration console illustration with sample data. Order mirrors the add-on: settings, templates, popups, pages.
const sampleItems=[
 {id:'kit',name:'Global colors and fonts',type:'Site settings',stage:0},
 {id:'header',name:'Header',type:'Template',stage:1,condition:'Entire site'},
 {id:'footer',name:'Footer',type:'Template',stage:1,condition:'Entire site'},
 {id:'single',name:'Single post',type:'Template',stage:1,condition:'All posts'},
 {id:'newsletter',name:'Newsletter popup',type:'Popup',stage:2,condition:'On exit intent'},
 {id:'home',name:'Home',type:'Page',stage:3},
 {id:'services',name:'Services',type:'Page',stage:3,score:97,note:'Custom CSS is not carried over. Zadex has no custom CSS field.'},
 {id:'shop',name:'Shop',type:'WooCommerce',stage:3},
 {id:'contact',name:'Contact',type:'Page',stage:3}
];
const stages=['Site settings','Templates','Popups','Pages'];
const status=ref(Object.fromEntries(sampleItems.map(x=>[x.id,'waiting'])));
const activeStage=ref(-1),running=ref(false),pickedId=ref('services');
let timers=[];
const picked=computed(()=>sampleItems.find(x=>x.id===pickedId.value));
const doneCount=computed(()=>sampleItems.filter(x=>status.value[x.id]==='converted').length);
const allDone=computed(()=>doneCount.value===sampleItems.length);
const calm=()=>window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const wait=ms=>new Promise(resolve=>timers.push(setTimeout(resolve,calm()?0:ms)));
async function convertAll(){
 if(running.value)return;
 running.value=true;
 for(let stage=0;stage<stages.length;stage++){
  activeStage.value=stage;
  for(const item of sampleItems.filter(x=>x.stage===stage)){status.value[item.id]='converting';await wait(380);status.value[item.id]='converted';}
  await wait(180);
 }
 activeStage.value=stages.length;running.value=false;
}
function rollBack(id){status.value[id]='rolled';}
function rollBackAll(){timers.forEach(clearTimeout);timers=[];running.value=false;activeStage.value=-1;for(const item of sampleItems)status.value[item.id]=status.value[item.id]==='converted'?'rolled':'waiting';}
function resetConsole(){timers.forEach(clearTimeout);timers=[];running.value=false;activeStage.value=-1;for(const item of sampleItems)status.value[item.id]='waiting';}
const statusLabel=id=>({waiting:'Waiting',converting:'Converting…',converted:'Converted',rolled:'Rolled back'})[status.value[id]];
onBeforeUnmount(()=>timers.forEach(clearTimeout));

const proof=[
 {value:'100',label:'Report score on 10 of 10 pages and Pro templates in the latest test run'},
 {value:'20/20',label:'Live checks passing after conversion: popup, menus, counter, slider, gallery filter, form'},
 {value:'900+',label:'Automated tests'}
];
const requirements=[
 {label:'WordPress',value:'6.5 or later'},
 {label:'PHP',value:'8.0 or later'},
 {label:'Zadex Page Builder',value:'Installed and active. Free on WordPress.org.'},
 {label:'Elementor',value:'Not required to convert. When active, it powers the “before” preview and keeps widgets with no Zadex equivalent as HTML.'}
];
const steps=[
 {title:'Install Zadex',text:'Install and activate Zadex from Plugins → Add New.'},
 {title:'Download the add-on',text:'Download zadex-migrator.zip from this page.'},
 {title:'Upload and activate',text:'Go to Plugins → Add New → Upload Plugin, choose the file, then click Activate.'},
 {title:'Start the import',text:'Open Zadex → Import from Elementor.'}
];
const faq=[
 {q:'Is the conversion a perfect match?',a:'Very close on most sites, and the report shows any difference. Small spacing differences can remain, depending on your theme.'},
 {q:'Does Elementor custom CSS move over?',a:'No. Zadex has no custom CSS field, so the report lists it for you to handle.'},
 {q:'What about Google Fonts?',a:'Zadex does not load fonts from external servers. The add-on tells you which fonts to add in Zadex → Custom Fonts.'},
 {q:'Is my Elementor data deleted?',a:'No. It stays exactly as it is, and you can switch back at any time.'},
 {q:'Do I need Elementor Pro?',a:'No. Pro content is converted when it exists, but Pro does not need to be installed.'},
 {q:'Is anything sent to an external server?',a:'No. Everything happens on your own site.'}
];
const open=ref(0);
const copied=ref(false);
let copyTimer;
async function copyChecksum(){try{await navigator.clipboard.writeText(release.sha256);copied.value=true;clearTimeout(copyTimer);copyTimer=setTimeout(()=>copied.value=false,2000);}catch{copied.value=false;}}
function updateMetadata(){document.title=m('Zadex Migrator — Move from Elementor to Zadex, without rebuilding.');document.querySelector('meta[name="description"]')?.setAttribute('content',m('Zadex Migrator converts Elementor pages, theme templates, popups and WooCommerce layouts into Zadex, with a side-by-side preview, a report per page and one-click rollback. Free download.'));}
watch(locale,async()=>{await loadMigratorLocale();updateMetadata();});
if(typeof document!=='undefined')updateMetadata();
</script>

<template>
 <a class="skip" href="#main">{{m('Skip to content')}}</a>
 <div class="site-shell mg">
  <header class="mg-header">
   <div class="mg-header-start"><a class="brand" :href="home" aria-label="Zadex">Zadex<span class="brand-dot"></span></a><span class="mg-badge">Migrator</span></div>
   <nav class="mg-nav" :aria-label="m('Page sections')"><a href="#what-moves">{{m('What moves')}}</a><a href="#safety">{{m('Safety')}}</a><a href="#install">{{m('Install')}}</a><a href="#faq">{{m('FAQ')}}</a></nav>
   <div class="mg-header-end"><a class="mg-home" :href="home"><ArrowLeft :size="14"/>{{m('Zadex home')}}</a><LanguageSwitcher :supported="['en','ar']"/><a class="button button-primary mg-header-download" :href="release.file" download><Download :size="15"/>{{m('Download')}}</a></div>
  </header>

  <main id="main">
   <section class="mg-hero" aria-labelledby="mg-title">
    <div class="mg-hero-copy">
     <p class="mg-kicker"><span class="mg-dot"></span>{{m('Free add-on for Zadex')}} · <bdi dir="ltr">v{{release.version}}</bdi></p>
     <h1 id="mg-title">{{m('Move from Elementor to Zadex,')}} <span>{{m('without rebuilding.')}}</span></h1>
     <p class="mg-lead">{{m('Convert pages, theme templates, popups and WooCommerce layouts in one click. Preview side by side, read a report for every page, and roll back anytime. Your Elementor data is never touched.')}}</p>
     <div class="mg-cta"><a class="button button-primary mg-download" :href="release.file" download><Download :size="17"/>{{m('Download free')}}<small><bdi dir="ltr">.zip · {{size}}</bdi></small></a><a class="mg-secondary" href="#how-it-works">{{m('See how it works')}}<ArrowRight :size="15"/></a></div>
     <p class="mg-requires"><Info :size="14"/>{{m('Requires')}} <a :href="zadexPlugin" target="_blank" rel="noopener">Zadex Page Builder</a> · <bdi dir="ltr">WordPress 6.5+ · PHP 8.0+</bdi></p>
    </div>
    <figure class="mg-compare" :aria-label="m('Illustration: the original page beside the converted Zadex page')">
     <div class="mg-compare-bar"><span>{{m('Side-by-side preview')}}</span><span class="mg-score"><Check :size="12"/>{{m('Score')}} 100</span></div>
     <div class="mg-compare-stage" :style="{'--split':split+'%'}">
      <div class="mg-sheet mg-sheet-before" aria-hidden="true"><span class="mg-sheet-tag">{{m('Original')}}</span><div class="mg-wire"><i class="nav"></i><b></b><b class="short"></b><i class="btn"></i><div class="cards"><i></i><i></i><i></i></div></div></div>
      <div class="mg-sheet mg-sheet-after" aria-hidden="true"><span class="mg-sheet-tag">Zadex</span><div class="mg-wire"><i class="nav"></i><b></b><b class="short"></b><i class="btn"></i><div class="cards"><i></i><i></i><i></i></div></div></div>
      <span class="mg-handle" aria-hidden="true"><ArrowLeft :size="12"/><ArrowRight :size="12"/></span>
      <input class="mg-range" type="range" min="8" max="92" v-model.number="split" :aria-label="m('Compare original and converted page')">
     </div>
     <figcaption>{{m('Illustration. Drag to compare. Visitors keep seeing the original until you apply the conversion.')}}</figcaption>
    </figure>
   </section>

   <section class="mg-proof" :aria-label="m('Tested results')">
    <p class="mg-proof-intro"><ShieldCheck :size="18"/><span>{{m('Tested on real installs of')}} <bdi dir="ltr">Elementor 4.4.1 + Elementor Pro 3.33.2</bdi></span></p>
    <div class="mg-proof-grid"><div v-for="item in proof" :key="item.value"><strong><bdi dir="ltr">{{item.value}}</bdi></strong><span>{{m(item.label)}}</span></div></div>
   </section>

   <section class="mg-section" id="what-moves" aria-labelledby="moves-title">
    <div class="mg-section-intro"><h2 id="moves-title">{{m('Your whole site moves over.')}} <span>{{m('Not just the pages.')}}</span></h2><p>{{m('Built for site owners who want a faster site with cleaner code, and for agencies with many client sites on Elementor.')}}</p></div>
    <div class="mg-capabilities">
     <article v-for="(item,index) in capabilities" :key="item.title" :class="{wide:index===0}">
      <component :is="item.icon" :size="22"/>
      <h3>{{m(item.title)}}</h3><p>{{m(item.text)}}</p>
      <ul><li v-for="point in item.items" :key="point"><Check :size="13"/>{{m(point)}}</li></ul>
      <div v-if="index===0" class="mg-widgets"><span v-for="name in widgets" :key="name">{{m(name)}}</span><span class="more">{{m('and more')}}</span></div>
     </article>
    </div>
   </section>

   <section class="mg-section" id="how-it-works" aria-labelledby="console-title">
    <div class="mg-section-intro"><h2 id="console-title">{{m('Convert everything')}} <span>{{m('with one button.')}}</span></h2><p>{{m('Site settings first, then templates, popups and pages, in the right order. Try it on this sample site.')}}</p></div>
    <div class="mg-console">
     <div class="mg-console-top">
      <div class="mg-console-title"><span class="mg-window"><i></i><i></i><i></i></span>{{m('Zadex → Import from Elementor')}}</div>
      <div class="mg-console-actions">
       <button v-if="!allDone" class="button button-primary" :disabled="running" @click="convertAll"><Zap :size="15"/>{{m(running?'Converting…':'Convert everything')}}</button>
       <button v-else class="mg-ghost" @click="rollBackAll"><RotateCcw :size="14"/>{{m('Roll back everything')}}</button>
       <button class="mg-icon" :aria-label="m('Reset the sample')" @click="resetConsole"><RotateCcw :size="14"/></button>
      </div>
     </div>
     <ol class="mg-stages" :aria-label="m('Conversion order')"><li v-for="(stage,index) in stages" :key="stage" :class="{active:activeStage===index,done:activeStage>index}"><span><Check v-if="activeStage>index" :size="11"/><template v-else>{{index+1}}</template></span>{{m(stage)}}</li></ol>
     <div class="mg-console-body">
      <ul class="mg-items" :aria-label="m('Sample site content')">
       <li v-for="item in sampleItems" :key="item.id"><button :class="['mg-item',status[item.id],{picked:pickedId===item.id}]" :aria-pressed="pickedId===item.id" @click="pickedId=item.id">
        <span class="mg-item-name"><strong>{{m(item.name)}}</strong><small>{{m(item.type)}}<template v-if="item.condition"> · {{m(item.condition)}}</template></small></span>
        <span class="mg-item-status"><template v-if="status[item.id]==='converted'"><bdi dir="ltr">{{item.score||100}}</bdi></template>{{m(statusLabel(item.id))}}</span>
       </button></li>
      </ul>
      <aside class="mg-report" aria-live="polite">
       <p class="mg-report-kicker"><FileText :size="14"/>{{m('Report')}} · {{m(picked.type)}}</p>
       <h3>{{m(picked.name)}}</h3>
       <template v-if="status[picked.id]==='converted'">
        <div class="mg-meter"><span :style="{width:(picked.score||100)+'%'}"></span></div>
        <p class="mg-report-score"><strong><bdi dir="ltr">{{picked.score||100}}</bdi></strong> / 100</p>
        <p v-if="picked.note" class="mg-note"><Info :size="14"/>{{m(picked.note)}}</p>
        <p v-else class="mg-note ok"><Check :size="14"/>{{m('Everything carried over.')}}</p>
        <div class="mg-mini-compare" aria-hidden="true"><div><span>{{m('Original')}}</span><i></i><i></i><i class="s"></i></div><div class="after"><span>Zadex</span><i></i><i></i><i class="s"></i></div></div>
        <button class="mg-ghost" @click="rollBack(picked.id)"><RotateCcw :size="14"/>{{m('Roll back this item')}}</button>
       </template>
       <p v-else-if="status[picked.id]==='rolled'" class="mg-note"><RotateCcw :size="14"/>{{m('Rolled back. Elementor shows this item exactly as before.')}}</p>
       <p v-else class="mg-empty">{{m('Not converted yet. Press Convert everything to see its report.')}}</p>
      </aside>
     </div>
     <p class="mg-console-foot"><span><bdi dir="ltr">{{doneCount}} / {{sampleItems.length}}</bdi> {{m('converted')}}</span><span>{{m('Illustration with sample data. Nothing here touches a real site.')}}</span></p>
    </div>
   </section>

   <section class="mg-section" id="safety" aria-labelledby="safety-title">
    <div class="mg-section-intro"><h2 id="safety-title">{{m('Nothing changes')}} <span>{{m('until you say so.')}}</span></h2><p>{{m('Your original Elementor data is never modified. Every step can be undone.')}}</p></div>
    <div class="mg-safety">
     <article><Eye :size="22"/><h3>{{m('Side-by-side preview')}}</h3><p>{{m('See the original page next to the converted one. Visitors don’t see any change yet.')}}</p></article>
     <article><FileText :size="22"/><h3>{{m('A report for every page')}}</h3><p>{{m('A conversion score, plus a list of anything that didn’t carry over completely.')}}</p></article>
     <article><RotateCcw :size="22"/><h3>{{m('One-click rollback')}}</h3><p>{{m('For every page, every template and the global settings. Elementor shows your site exactly as it was.')}}</p></article>
     <article class="dark"><Lock :size="22"/><h3>{{m('Private by design')}}</h3><p>{{m('Everything happens on your site. No data is ever sent to an external server.')}}</p></article>
    </div>
   </section>

   <section class="mg-section mg-install" id="install" aria-labelledby="install-title">
    <div><h2 id="install-title">{{m('Up and running')}} <span>{{m('in four steps.')}}</span></h2>
     <ol class="mg-steps"><li v-for="(step,index) in steps" :key="step.title"><span>{{index+1}}</span><div><h3>{{m(step.title)}}</h3><p>{{m(step.text)}}</p></div></li></ol>
    </div>
    <div class="mg-requirements"><h3>{{m('Requirements')}}</h3><dl><div v-for="row in requirements" :key="row.label"><dt>{{m(row.label)}}</dt><dd>{{m(row.value)}}</dd></div></dl><a class="mg-secondary" :href="zadexPlugin" target="_blank" rel="noopener">{{m('Get Zadex on WordPress.org')}}<ArrowRight :size="15"/></a></div>
   </section>

   <section class="mg-section mg-faq" id="faq" aria-labelledby="faq-title">
    <div><h2 id="faq-title">{{m('Straight answers.')}}</h2><p>{{m('What the add-on does, and what it doesn’t.')}}</p></div>
    <div class="mg-faq-list"><article v-for="(item,index) in faq" :key="item.q"><h3><button :aria-expanded="open===index" :aria-controls="'mg-answer-'+index" @click="open=open===index?null:index">{{m(item.q)}}<Minus v-if="open===index" :size="17"/><Plus v-else :size="17"/></button></h3><p v-show="open===index" :id="'mg-answer-'+index">{{m(item.a)}}</p></article></div>
   </section>

   <section class="mg-section mg-download-panel" id="download" aria-labelledby="download-title">
    <div class="mg-download-main">
     <p class="mg-kicker light"><span class="mg-dot"></span>{{m('Free. No sign-up, no email.')}}</p>
     <h2 id="download-title">{{m('Download Zadex Migrator')}}</h2>
     <a class="button mg-download-light" :href="release.file" download><Download :size="17"/>{{m('Download free')}}<small>zadex-migrator.zip</small></a>
     <dl class="mg-file"><div><dt>{{m('Version')}}</dt><dd><bdi dir="ltr">{{release.version}}</bdi></dd></div><div><dt>{{m('Released')}}</dt><dd>{{released}}</dd></div><div><dt>{{m('Size')}}</dt><dd><bdi dir="ltr">{{size}}</bdi></dd></div><div><dt>{{m('License')}}</dt><dd>{{m(release.license)}}</dd></div><div><dt>{{m('Tested up to')}}</dt><dd><bdi dir="ltr">WordPress {{release.testedUpTo}}</bdi></dd></div></dl>
     <div class="mg-checksum"><span>SHA-256</span><code>{{release.sha256}}</code><button @click="copyChecksum" :aria-label="m('Copy checksum')"><Check v-if="copied" :size="14"/><Copy v-else :size="14"/></button></div>
    </div>
    <div class="mg-changelog"><h3>{{m('Changelog')}}</h3><article v-for="entry in changelog" :key="entry.version"><header><strong><bdi dir="ltr">{{entry.version}}</bdi></strong><span>{{formatDate(entry.date)}}</span></header><ul><li v-for="note in entry.notes" :key="note">{{m(note)}}</li></ul></article></div>
   </section>
  </main>

  <footer class="mg-footer">
   <div><a class="brand footer-brand" :href="home">Zadex<span class="brand-dot"></span></a><p>{{m('Zadex Migrator is free software, licensed GPLv2 or later.')}}</p></div>
   <nav :aria-label="m('Footer')"><a :href="home">{{m('Zadex home')}}</a><a :href="docs">{{m('Documentation')}}</a><a :href="zadexPlugin" target="_blank" rel="noopener">WordPress.org</a></nav>
   <p class="mg-legal" lang="en" dir="ltr">Elementor is a trademark of its owner. Zadex Migrator is not affiliated with or endorsed by Elementor.</p>
  </footer>
 </div>
</template>
