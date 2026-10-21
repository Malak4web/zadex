<script setup>
import {ref,computed,watch,onMounted,onBeforeUnmount,nextTick} from 'vue';
import {Search,X,Menu,ArrowUpRight,ArrowLeft,ArrowRight,BookOpen,Link,Check,ChevronDown,Info,Printer,SlidersHorizontal} from 'lucide-vue-next';
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
import {locale} from '../i18n';
import {guides,groups,ui} from './guides';
import reference from './reference.json';
import {d,docsReady,docsError,loadDocsLocale} from './i18n';
const query=ref(''),menuOpen=ref(false),hash=ref(typeof location==='undefined'?'installation':decodeHash(location.hash)),copied=ref(false),copyError=ref(false),articleHeading=ref(null),currentSection=ref('');
const shownResults=ref(30);
let copyTimer,observer;
function decodeHash(value){try{return decodeURIComponent(value.replace(/^#/,''))||'installation';}catch{return 'installation';}}
const pageId=computed(()=>hash.value.split('/')[0]);
const guide=computed(()=>guides.find(x=>x.id===pageId.value));
const widget=computed(()=>reference.widgets.find(x=>x.id===pageId.value));
const page=computed(()=>guide.value||widget.value);
const sourceGroup=computed(()=>guide.value?.group||'reference');
const normalized=value=>String(value||'').toLocaleLowerCase(locale.value).normalize('NFKD').replace(/[\u0300-\u036f\u064B-\u065F\u0670]/g,'');
const pages=computed(()=>[...guides.map(x=>({...x,kind:'guide',searchText:x.sections.map(s=>[d(s.title),...s.paragraphs.map(value=>d(value)),...s.steps.map(value=>d(value))].join(' ')).join(' ')})),...reference.widgets.map(x=>({...x,kind:'element',searchText:x.controls.map(c=>d(c.label)+' '+c.label).join(' ')}))]);
const matches=computed(()=>{const words=normalized(query.value).trim().split(/\s+/).filter(Boolean);if(!words.length)return [];return pages.value.map(item=>({item,score:words.every(word=>normalized(d(item.title)).includes(word))?1:0})).filter(({item})=>words.every(word=>normalized([d(item.title),item.title,d(item.summary||item.description),item.searchText].join(' ')).includes(word))).sort((a,b)=>b.score-a.score).map(x=>x.item);});
const categories=computed(()=>[...new Set(reference.widgets.map(x=>x.category))]);
const elementCategory=ref('all'),elementQuery=ref('');
const listedWidgets=computed(()=>reference.widgets.filter(w=>(elementCategory.value==='all'||w.category===elementCategory.value)&&normalized(d(w.title)+' '+w.title+' '+d(w.description)).includes(normalized(elementQuery.value))));
const sections=computed(()=>guide.value?.sections||[{id:'purpose',title:ui.purpose},{id:'use',title:ui.use},...['content','style'].filter(tab=>widget.value?.controls.some(c=>c.tab===tab)).map(tab=>({id:tab,title:ui[tab]})),{id:'advanced',title:ui.shared}]);
const index=computed(()=>guides.findIndex(x=>x.id===pageId.value));
const adjacent=computed(()=>({previous:index.value>0?guides[index.value-1]:null,next:index.value>=0&&index.value<guides.length-1?guides[index.value+1]:null}));
const related=computed(()=>(guide.value?.related||['elements','element-settings','responsive']).map(id=>guides.find(x=>x.id===id)).filter(Boolean));
const href=(id,section)=>'#'+id+(section?'/'+section:'');
function handleRouteClick(event){
 if(event.defaultPrevented||event.button!==0||event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
 const anchor=event.target.closest?.('a[href^="#"]');
 if(anchor&&anchor.hash===location.hash){event.preventDefault();navigate();}
}
function skipToMain(){const main=document.getElementById('docs-main');main?.focus();main?.scrollIntoView({block:'start',behavior:'instant'});}
const home=computed(()=>'/?lang='+locale.value);
const reviewed=computed(()=>new Intl.DateTimeFormat(locale.value,{year:'numeric',month:'long',day:'numeric'}).format(new Date(reference.reviewed+'T12:00:00Z')));
function defaultValue(value){if(value==null)return d(ui.noDefault);if(typeof value==='boolean')return d(ui[String(value)]);if(Array.isArray(value))return d(ui.repeater);if(typeof value==='object')return Object.entries(value).map(([key,v])=>`${key}: ${typeof v==='object'?JSON.stringify(v):v}`).join(' · ');return String(value);}
function typeLabel(type){return d(({text:'Text',textarea:'Text',number:'Number',slider:'Number',select:'Select',choose:'Choose',switch:'Switch',color:'Color',typography:'Typography',image:'Image',media:'Media',gallery:'Gallery',url:'URL',repeater:'Repeater',dimensions:'Dimensions',background:'Background',border:'Border',boxShadow:'Shadow',code:'Code',icon:'Icon'})[type]||type);}
function clearSearch(){query.value='';}
async function copyLink(){copyError.value=false;try{await navigator.clipboard.writeText(location.href);copied.value=true;clearTimeout(copyTimer);copyTimer=setTimeout(()=>copied.value=false,2000);}catch{copyError.value=true;}}
function printGuide(){window.print();}
function updateMetadata(){if(typeof document==='undefined')return;document.title=(page.value?d(page.value.title)+' — ':'')+d(ui.docs)+' · Zadex';document.querySelector('meta[name="description"]')?.setAttribute('content',page.value?d(page.value.summary||page.value.description):d(ui.description));}
async function trackSections(){await nextTick();observer?.disconnect();if(typeof IntersectionObserver==='undefined')return;observer=new IntersectionObserver(entries=>{for(const entry of entries)if(entry.isIntersecting)currentSection.value=entry.target.dataset.section;},{rootMargin:'-100px 0px -65% 0px'});document.querySelectorAll('[data-section]').forEach(el=>observer.observe(el));}
async function navigate(){hash.value=decodeHash(location.hash);query.value='';menuOpen.value=false;copied.value=false;await nextTick();const target=hash.value.split('/')[1];if(target){const heading=document.getElementById('doc-'+target);heading?.scrollIntoView({block:'start',behavior:'instant'});heading?.focus({preventScroll:true});currentSection.value=target;}else{window.scrollTo({top:0,behavior:'instant'});articleHeading.value?.focus({preventScroll:true});}updateMetadata();trackSections();}
watch(locale,async()=>{query.value='';await loadDocsLocale();updateMetadata();trackSections();});
watch(query,()=>{shownResults.value=30;});
watch(docsReady,value=>{if(value){updateMetadata();trackSections();}});
onMounted(()=>{window.addEventListener('hashchange',navigate);updateMetadata();trackSections();if(hash.value.includes('/'))navigate();});
onBeforeUnmount(()=>{window.removeEventListener('hashchange',navigate);clearTimeout(copyTimer);observer?.disconnect();});
</script>
<template>
<a class="skip" href="#docs-main" @click.prevent="skipToMain">{{d(ui.skip)}}</a>
<div class="docs-shell" @click="handleRouteClick">
 <header class="docs-topbar"><a class="brand" :href="home" aria-label="Zadex">Zadex<span class="brand-dot"></span></a><a class="docs-brand-label" href="#installation"><BookOpen :size="17"/>{{d(ui.docs)}}</a><div class="docs-topbar-end"><a class="docs-home" :href="home"><ArrowLeft :size="14"/>{{d(ui.home)}}</a><LanguageSwitcher :supported="['en','ar']"/><a class="docs-download" href="https://wordpress.org/plugins/zadex/" target="_blank" rel="noopener">WordPress.org<ArrowUpRight :size="14"/></a></div></header>
 <div class="docs-mobile-tools"><button :aria-expanded="menuOpen" aria-controls="docs-sidebar" @click="menuOpen=!menuOpen"><X v-if="menuOpen" :size="18"/><Menu v-else :size="18"/>{{d(ui.browse)}}</button><span>v{{reference.version}}</span></div>
 <div class="docs-layout">
  <aside id="docs-sidebar" class="docs-sidebar" :class="{'is-open':menuOpen}"><nav :aria-label="d(ui.browse)"><section v-for="(label,key) in groups" :key="key" class="docs-nav-group"><h2>{{d(label)}}</h2><a v-for="entry in guides.filter(x=>x.group===key)" :key="entry.id" :href="href(entry.id)" :class="{active:entry.id===pageId||(entry.id==='elements'&&widget)}" :aria-current="entry.id===pageId?'page':undefined">{{d(entry.title)}}</a></section></nav><p class="docs-version">{{d(ui.version)}} <bdi>v{{reference.version}}</bdi></p></aside>
  <main id="docs-main" class="docs-main" tabindex="-1" :aria-busy="!docsReady">
   <form class="docs-search" role="search" @submit.prevent><Search :size="19" aria-hidden="true"/><label class="sr-only" for="docs-search">{{d(ui.search)}}</label><input id="docs-search" v-model="query" type="search" :placeholder="d(ui.placeholder)" autocomplete="off"><button v-if="query" type="button" :aria-label="d(ui.clear)" @click="clearSearch"><X :size="18"/></button></form>
   <div v-if="docsError" class="docs-status" role="alert"><p>{{d(ui.loadError)}}</p><button @click="loadDocsLocale">{{d(ui.retry)}}</button></div>
   <p v-else-if="!docsReady" class="docs-status" role="status">{{d(ui.loading)}}</p>
   <section v-else-if="query.trim()" class="docs-results" :aria-label="d(ui.results)"><h1>{{d(ui.results)}}</h1><p role="status">{{matches.length}} {{d(ui.results)}}</p><a v-for="result in matches.slice(0,shownResults)" :key="result.id" :href="href(result.id)" class="docs-result"><span>{{d(result.kind==='element'?ui.elements:ui.guides)}}</span><h2>{{d(result.title)}}</h2><p>{{d(result.summary||result.description)}}</p><ArrowRight :size="17"/></a><button v-if="matches.length>shownResults" class="docs-more" @click="shownResults+=30">{{d(ui.more)}}</button><div v-if="!matches.length" class="docs-empty"><Search :size="28"/><h2>{{d(ui.noResults)}}</h2><p>{{d(ui.tryAgain)}}</p><button @click="clearSearch">{{d(ui.clear)}}</button></div></section>
   <article v-else-if="page" class="docs-article">
    <div class="docs-article-path"><a href="#installation">{{d(ui.docs)}}</a><span>/</span><span>{{d(groups[sourceGroup])}}</span></div>
    <header class="docs-article-heading"><h1 ref="articleHeading" tabindex="-1">{{d(page.title)}}</h1><p class="docs-lead">{{d(guide?guide.summary:widget.description)}}</p><div class="docs-article-meta"><span>{{d(ui.updated)}} {{reviewed}}</span><div><button @click="copyLink" :aria-label="d(copied?ui.copied:ui.copy)"><Check v-if="copied" :size="15"/><Link v-else :size="15"/>{{d(copied?ui.copied:ui.copy)}}</button><button @click="printGuide" :aria-label="d(ui.print)"><Printer :size="15"/></button></div></div><p v-if="copyError" class="docs-copy-error" role="status">{{d(ui.copyFailed)}}</p></header>
    <details class="docs-inline-toc"><summary>{{d(ui.contents)}}<ChevronDown :size="15"/></summary><a v-for="part in sections" :key="part.id" :href="href(pageId,part.id)">{{d(part.title)}}</a></details>
    <template v-if="guide"><section v-for="part in guide.sections" :key="part.id" class="docs-section"><h2 :id="'doc-'+part.id" :data-section="part.id" tabindex="-1"><a :href="href(pageId,part.id)">{{d(part.title)}}<Link :size="15"/></a></h2><p v-for="(paragraph,i) in part.paragraphs" :key="i">{{d(paragraph)}}</p><ol v-if="part.steps.length" class="docs-steps"><li v-for="(step,i) in part.steps" :key="i">{{d(step)}}</li></ol><aside v-if="part.note" class="docs-note"><Info :size="19"/><p>{{d(part.note)}}</p></aside><pre v-if="part.code" dir="ltr"><code>{{part.code}}</code></pre></section>
     <div v-if="guide.id==='installation'" class="docs-film"><video controls muted playsinline preload="none" poster="/media/zadex-editor-poster.jpg" src="/media/zadex-in-action.mp4" :aria-label="d(ui.localVideo)"></video><a :href="home+'#in-action'">{{d(ui.localVideo)}}<ArrowUpRight :size="14"/></a></div>
     <section v-if="guide.id==='elements'" class="docs-element-library"><div class="docs-element-filters"><label><Search :size="16"/><span class="sr-only">{{d(ui.search)}}</span><input v-model="elementQuery" type="search" :placeholder="d(ui.placeholder)"></label><label><SlidersHorizontal :size="16"/><span class="sr-only">{{d(ui.all)}}</span><select v-model="elementCategory"><option value="all">{{d(ui.all)}}</option><option v-for="category in categories" :key="category" :value="category">{{d(category)}}</option></select></label></div><p class="docs-element-count" role="status">{{listedWidgets.length}} / {{reference.widgets.length}}</p><a v-for="entry in listedWidgets" :key="entry.id" :href="href(entry.id)" class="docs-element-row"><strong>{{d(entry.title)}}</strong><span>{{d(entry.description)}}</span><ArrowRight :size="16"/></a><p v-if="!listedWidgets.length">{{d(ui.noResults)}}</p></section>
    </template>
    <template v-else-if="widget"><section class="docs-section"><h2 id="doc-purpose" data-section="purpose" tabindex="-1">{{d(ui.purpose)}}</h2><p>{{d(widget.description)}}</p><aside v-if="widget.category==='woocommerce'" class="docs-note"><Info :size="19"/><p>{{d(ui.woo)}}</p></aside></section><section class="docs-section"><h2 id="doc-use" data-section="use" tabindex="-1">{{d(ui.use)}}</h2><ol class="docs-steps"><li>{{d(ui.addElement)}}</li><li>{{d(ui.preview)}}</li></ol></section>
     <section v-for="tab in ['content','style'].filter(tab=>widget.controls.some(c=>c.tab===tab))" :key="tab" class="docs-section"><h2 :id="'doc-'+tab" :data-section="tab" tabindex="-1">{{d(ui[tab])}}</h2><p class="docs-control-intro">{{d(ui.source)}}</p><div class="docs-control-table" role="region" :aria-label="d(ui[tab])" tabindex="0"><table><thead><tr><th>{{d(ui.control)}}</th><th>{{d(ui.details)}}</th></tr></thead><tbody><tr v-for="control in widget.controls.filter(c=>c.tab===tab)" :key="control.id"><th scope="row"><strong>{{d(control.label)}}</strong><code dir="ltr">{{control.id}}</code><small>{{typeLabel(control.type)}}</small></th><td><p v-if="control.description">{{d(control.description)}}</p><p v-if="control.options.length" class="docs-options"><b>{{d(ui.options)}}:</b> {{control.options.map(x=>d(x.label)).join(' · ')}}</p><p class="docs-default"><b>{{d(ui.default)}}:</b> <bdi>{{defaultValue(control.default)}}</bdi></p><div class="docs-control-tags"><span v-if="control.responsive">{{d(ui.responsive)}}</span><span v-if="control.dynamic">{{d(ui.dynamic)}}</span><span v-if="control.conditional">{{d(ui.conditional)}}</span><span v-if="control.states.length">{{d(ui.states)}}: {{control.states.map(value=>d(value)).join(' · ')}}</span></div></td></tr></tbody></table></div></section>
     <section class="docs-section"><h2 id="doc-advanced" data-section="advanced" tabindex="-1">{{d(ui.shared)}}</h2><p>{{d(ui.sharedText)}}</p><a class="docs-text-link" href="#element-settings">{{d(guides.find(x=>x.id==='element-settings').title)}}<ArrowRight :size="15"/></a><a class="docs-text-link" href="#elements">{{d(ui.backElements)}}<ArrowRight :size="15"/></a></section>
    </template>
    <section class="docs-related"><h2>{{d(ui.related)}}</h2><a v-for="entry in related" :key="entry.id" :href="href(entry.id)">{{d(entry.title)}}<ArrowRight :size="16"/></a></section>
    <nav v-if="guide" class="docs-pagination" :aria-label="d(ui.browse)"><a v-if="adjacent.previous" :href="href(adjacent.previous.id)"><small>{{d(ui.previous)}}</small><span><ArrowLeft :size="16"/>{{d(adjacent.previous.title)}}</span></a><a v-if="adjacent.next" class="docs-next" :href="href(adjacent.next.id)"><small>{{d(ui.next)}}</small><span>{{d(adjacent.next.title)}}<ArrowRight :size="16"/></span></a></nav>
   </article>
   <section v-else class="docs-empty"><BookOpen :size="32"/><h1>{{d(ui.notFound)}}</h1><a href="#installation">{{d(ui.start)}}<ArrowRight :size="16"/></a></section>
   <footer class="docs-footer"><a :href="home">Zadex<span class="brand-dot"></span></a><span>{{d(ui.version)}} <bdi>v{{reference.version}}</bdi></span></footer>
  </main>
  <aside v-if="page&&!query.trim()&&docsReady" class="docs-toc"><h2>{{d(ui.contents)}}</h2><a v-for="part in sections" :key="part.id" :href="href(pageId,part.id)" :class="{active:currentSection===part.id}">{{d(part.title)}}</a><a class="docs-toc-reference" href="#elements">{{d(ui.reference)}}<ArrowUpRight :size="14"/></a></aside>
 </div>
</div>
</template>
