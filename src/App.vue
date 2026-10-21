<script setup>
import {t, locale} from "./i18n";
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { ArrowUpRight, Play, Pause, Plus, Minus, Monitor, MousePointer2, Check, Menu, X, Undo2, Maximize2 } from 'lucide-vue-next';
import LanguageSwitcher from './components/LanguageSwitcher.vue';
import FeatureLibrary from './components/FeatureLibrary.vue';
import HeroCanvas from './components/HeroCanvas.vue';
import FeatureBento from './components/FeatureBento.vue';
import SupportSection from './components/SupportSection.vue';
import './components/feature-lab.css';
const download='https://wordpress.org/plugins/zadex/';
const docsHref=computed(()=>'/docs/?lang='+locale.value);
const video=ref(null),playing=ref(false),current=ref(0),duration=ref(22),mobileMenu=ref(false),openQuestion=ref(0),videoError=ref(false);
const chapters=[{name:'A starting point',detail:'Insert a section',time:0},{name:'Make it yours',detail:'Edit directly on the page',time:7},{name:'Every screen',detail:'Preview. Adjust. Save.',time:17}];
const activeChapter=computed(()=>current.value>=17?2:current.value>=7?1:0);
const progress=computed(()=>Math.min(100,current.value/duration.value*100));
const faq=[
 {q:'Is Zadex really free?',a:'Yes. All features described here are included in the free plugin. There is no paid version or premium upgrade.'},
 {q:'Will it work with my WordPress theme?',a:'Zadex edits the real page inside your theme and supports both classic and block themes. You can also use its Canvas or Full Width page templates, and design theme parts with the Theme Builder.'},
 {q:'Can I build a site in Arabic?',a:'Yes. The editor includes an Arabic translation and right-to-left support. It follows the language selected for your WordPress user.'},
 {q:'What do I need to get started?',a:'WordPress 6.5 or later and PHP 8.0 or later. Install Zadex, open a page, and select “Edit with Zadex”. WooCommerce features appear when WooCommerce is active.'},
 {q:'What happens if I deactivate the plugin?',a:'Zadex designs remain saved, but they stop rendering while the plugin is inactive. Your pages show their standard WordPress content, which may be empty. Reactivating Zadex restores the designs.'}
];
async function toggleVideo(){if(!video.value)return;if(video.value.paused){try{await video.value.play();videoError.value=false;}catch{playing.value=false;}}else video.value.pause();}
async function seekChapter(time){if(!video.value)return;video.value.currentTime=time;try{await video.value.play();}catch{playing.value=false;}}
function closeMenu(){mobileMenu.value=false;}
let motionQuery;
const respectMotion=()=>{if(motionQuery?.matches)video.value?.pause();};
onMounted(async()=>{motionQuery=window.matchMedia('(prefers-reduced-motion: reduce)');motionQuery.addEventListener('change',respectMotion);if(!motionQuery.matches){try{await video.value?.play();}catch{playing.value=false;}}});
onBeforeUnmount(()=>motionQuery?.removeEventListener('change',respectMotion));
</script>

<template>
 <a class="skip" href="#main">{{t("Skip to content")}}</a>
 <div class="site-shell">
 <header class="site-header">
  <a class="brand" href="#" :aria-label='t("Zadex home")'>{{t("Zadex")}}<span class="brand-dot"></span></a>
  <nav class="desktop-nav" :aria-label='t("Main navigation")'><a href="#in-action">{{t("In action")}}</a><a href="#feature-lab">{{t("Feature playground")}}</a><a href="#free">{{t("Why free?")}}</a><a href="#questions">{{t("FAQs")}}</a><a href="#tickets">{{t("Support")}}</a><a :href="docsHref">{{t("Documentation")}}</a></nav>
  <div class="header-end"><LanguageSwitcher/><a class="button button-primary header-download" :href="download" target="_blank" rel="noopener">{{t("Get Zadex free")}}<ArrowUpRight :size="15" /></a><button class="menu-toggle" :aria-expanded="mobileMenu" aria-controls="mobile-navigation" :aria-label='t("Toggle navigation")' @click="mobileMenu=!mobileMenu"><X v-if="mobileMenu"/><Menu v-else/></button></div>
 </header>
 <nav v-if="mobileMenu" id="mobile-navigation" class="mobile-nav" :aria-label='t("Mobile navigation")'><a href="#in-action" @click="closeMenu">{{t("In action")}}</a><a href="#feature-lab" @click="closeMenu">{{t("Feature playground")}}</a><a href="#free" @click="closeMenu">{{t("Why free?")}}</a><a href="#questions" @click="closeMenu">{{t("FAQs")}}</a><a href="#tickets" @click="closeMenu">{{t("Support")}}</a><a :href="docsHref" @click="closeMenu">{{t("Documentation")}}</a></nav>
 <main id="main">
  <section class="hero" aria-labelledby="hero-title">
   <HeroCanvas :download="download"/>
   <div class="film-area" id="in-action">
    <div class="film-prelude"><span><span class="live-dot"></span>{{t("This is Zadex. Actually building.")}}</span><span class="film-label">{{t("Real editor / Recorded in WordPress")}}</span></div>
    <div class="film-window"><video ref="video" class="editor-film" muted loop playsinline preload="metadata" poster="/media/zadex-editor-poster.jpg" :aria-label='t("Zadex demonstration: insert a template, edit text, preview mobile and save")' @play="playing=true" @pause="playing=false" @timeupdate="current=$event.target.currentTime" @loadedmetadata="duration=$event.target.duration;videoError=false" @playing="videoError=false" @error="videoError=true"><source src="/media/zadex-in-action.mp4" type="video/mp4"><source src="/media/zadex-in-action.webm" type="video/webm">{{t("Your browser does not support embedded video.")}}<a href="/media/zadex-in-action.mp4">{{t("Watch the demonstration.")}}</a></video><div v-if="videoError" class="film-error">{{t("The video couldn’t load.")}}<a href="/media/zadex-in-action.mp4">{{t("Open the recording")}}</a></div><div class="film-progress" role="progressbar" :aria-label='t("Demo playback progress")' :aria-valuenow="Math.round(progress)" aria-valuemin="0" aria-valuemax="100"><span :style="{width:progress+'%'}"></span></div></div>
    <div class="film-controls"><div class="chapters"><button v-for="(chapter,index) in chapters" :key="chapter.name" class="chapter" :class="{active:activeChapter===index}" :aria-pressed="activeChapter===index" @click="seekChapter(chapter.time)"><span class="chapter-dot"></span><span><strong>{{t(chapter.name)}}</strong><small>{{t(chapter.detail)}}</small></span></button></div><div class="playback-actions"><a class="expand-demo" href="/media/zadex-in-action.mp4" target="_blank" rel="noopener" :aria-label='t("View demonstration larger")'><Maximize2 :size="15"/><span>{{t("View larger")}}</span></a><button class="play-toggle" :aria-label="t(playing?'Pause demonstration':'Play demonstration')" @click="toggleVideo"><Pause v-if="playing" :size="16"/><Play v-else :size="16"/><span>{{t(playing?'Pause demo':'Play demo')}}</span></button></div></div>
   </div>
  </section>
  <div class="capability-line" :aria-label='t("Included capabilities")'><span>{{t("One plugin.")}}<br><strong>{{t("So much possibility.")}}</strong></span><span>{{t("Live visual editing")}}</span><span>{{t("102 elements")}}</span><span>{{t("Theme builder")}}</span><span>{{t("WooCommerce")}}</span><span>{{t("Arabic & RTL")}}</span></div>
  <FeatureLibrary />
  <FeatureBento />
  <section class="principles section-space" aria-labelledby="principles-title"><div class="section-intro"><h2 id="principles-title">{{t("A little less friction.")}}<br><span>{{t("A lot more creating.")}}</span></h2></div><div class="principle-list"><article><MousePointer2/><h3>{{t("The real page is your canvas.")}}</h3><p>{{t("Edit text where it lives. Move things where they belong. See every change with your actual theme around it.")}}</p></article><article><Monitor/><h3>{{t("Every screen gets its moment.")}}</h3><p>{{t("Fine-tune your layout across up to seven breakpoints. Your design, considered from desktop to mobile.")}}</p></article><article><Undo2/><h3>{{t("Go on. Try that idea.")}}</h3><p>{{t("Undo, redo and saved revisions give you room to explore. Keep the good ideas. Revisit the others.")}}</p></article></div></section>
  <section class="free-section section-space" id="free" aria-labelledby="free-title"><div><h2 id="free-title">{{t("Your creativity")}}<br><span>{{t("has no upgrade button.")}}</span></h2><p>{{t("Neither does Zadex. Every element, every builder, every feature described here is included in the same free plugin.")}}</p><a class="button button-primary" :href="download" target="_blank" rel="noopener">{{t("All yours. Get Zadex.")}}<ArrowUpRight :size="17"/></a></div><div class="free-manifest"><div><span>{{t("Visual editor")}}</span><Check/></div><div><span>{{t("Theme & loop builders")}}</span><Check/></div><div><span>{{t("Forms & popups")}}</span><Check/></div><div><span>{{t("Global styles & components")}}</span><Check/></div><div><span>{{t("WooCommerce integration")}}</span><Check/></div><footer>{{t("One plugin.")}}<strong>{{t("Completely free.")}}</strong></footer></div></section>
  <section class="questions section-space" id="questions" aria-labelledby="questions-title"><div><h2 id="questions-title">{{t("A few things you might be wondering.")}}</h2><p>{{t("Clear answers, before you create.")}}</p></div><div class="faq-list"><article v-for="(question,index) in faq" :key="question.q"><h3><button :aria-expanded="openQuestion===index" :aria-controls="'answer-'+index" @click="openQuestion=openQuestion===index?null:index">{{t(question.q)}}<Minus v-if="openQuestion===index" :size="17"/><Plus v-else :size="17"/></button></h3><div v-show="openQuestion===index" :id="'answer-'+index" class="faq-answer">{{t(question.a)}}</div></article></div></section>
  <SupportSection />
  <section class="closing"><div class="brand closing-brand">{{t("Zadex")}}<span class="brand-dot"></span></div><h2>{{t("Make the thing you’re imagining.")}}</h2><a class="button button-primary" :href="download" target="_blank" rel="noopener">{{t("Your next website starts here")}}<ArrowUpRight :size="17"/></a><p>{{t("WordPress 6.5+ · PHP 8.0+ · Free to create.")}}</p></section>
 </main>
 <footer class="site-footer"><a class="brand footer-brand" href="#">{{t("Zadex")}}<span class="brand-dot"></span></a><span>{{t("A little imagination goes a long way.")}}</span><a href="#tickets">{{t("Support")}}</a><a :href="docsHref">{{t("Documentation")}}</a><a :href="download" target="_blank" rel="noopener">{{t("Find us on WordPress.org")}}<ArrowUpRight :size="14"/></a></footer>
 </div>
</template>
