<script setup>
import {t, locale} from "../i18n";
import {ref,computed,defineAsyncComponent,onMounted,onBeforeUnmount,watch,nextTick,h} from 'vue';
import {Search,ArrowUpRight,ArrowLeft,ArrowRight,X,SlidersHorizontal,Play,RotateCcw} from 'lucide-vue-next';
import data from '../data/features.json';
const Simulation=defineAsyncComponent({loader:()=>import('./FeatureSimulation.vue'),loadingComponent:{render:()=>h('p',{class:'lab-loading',role:'status'},t('Opening the canvas…'))},errorComponent:{render:()=>h('p',{role:'alert'},t('The canvas couldn’t load. Refresh the page to try again.'))},timeout:15000});
const query=ref(''),group=ref('all'),page=ref(0),selectedId=ref('live-editing'),activated=ref(false),replay=ref(0),playing=ref(false),stage=ref(null),library=ref(null),viewCount=9;
let observer;
const selected=computed(()=>data.catalog.find(x=>x.id===selectedId.value)||data.catalog[0]);
const normalize=value=>value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f\u064B-\u065F\u0670]/g,'');
const filtered=computed(()=>{const words=normalize(query.value).split(/\s+/).filter(Boolean);return data.catalog.filter(item=>(group.value==='all'||item.group===group.value)&&words.every(word=>normalize([t(item.title),t(item.description),t(item.subcategory),t(data.groups[item.group]),item.title,item.description,item.subcategory,...item.keywords].join(' ')).includes(word)));});
const pages=computed(()=>Math.max(1,Math.ceil(filtered.value.length/viewCount)));
const visible=computed(()=>filtered.value.slice(page.value*viewCount,(page.value+1)*viewCount));
const range=computed(()=>filtered.value.length?t('{start}–{end} of {total}',{start:page.value*viewCount+1,end:Math.min((page.value+1)*viewCount,filtered.value.length),total:filtered.value.length}):'0 results');
watch([query,group,locale],()=>{page.value=0;});
async function choose(item){selectedId.value=item.id;activated.value=true;playing.value=false;replay.value=0;await nextTick();if(window.innerWidth<=650)library.value.querySelector('.lab-demonstration')?.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});}
function browse(){library.value.querySelector('.lab-tools')?.scrollIntoView({behavior:'smooth',block:'start'});}
async function play(){activated.value=true;await nextTick();replay.value++;}
function reset(){playing.value=false;selectedId.value=selectedId.value;replay.value=0;stage.value?.reset?.();}
onMounted(()=>{observer=new IntersectionObserver(entries=>{if(entries.some(x=>x.isIntersecting)){activated.value=true;observer.disconnect();}},{rootMargin:'180px'});observer.observe(library.value);});
onBeforeUnmount(()=>observer?.disconnect());
</script>
<template>
<section ref="library" id="feature-lab" class="feature-lab section-space" aria-labelledby="feature-lab-title">
 <div class="section-intro"><h2 id="feature-lab-title">{{t("Don’t just read about it.")}}<br><span>{{t("Get a feel for it.")}}</span></h2><p>{{t("Explore every feature. Try the controls.")}}<br>{{t("See what your next website could do.")}}</p></div>
 <div class="lab-tools"><label class="lab-search"><Search :size="17"/><span class="sr-only">{{t("Search all features")}}</span><input v-model="query" type="search" :aria-label='t("Search all features")' :placeholder='t("Search features, elements, integrations…")' autocomplete="off"><button v-if="query" :aria-label='t("Clear feature search")' @click="query=''"><X :size="15"/></button></label><label class="lab-category"><SlidersHorizontal :size="16"/><span class="sr-only">{{t("Feature category")}}</span><select v-model="group"><option value="all">{{t("All features ·")}} {{t(data.catalog.length)}}</option><option v-for="(label,key) in data.groups" :key="key" :value="key">{{t(label)}} · {{t(data.catalog.filter(x=>x.group===key).length)}}</option></select></label></div>
 <div class="lab-layout">
  <div class="lab-browser"><div class="lab-resultline"><span aria-live="polite">{{t(range)}}</span><span>{{t("All included. All free.")}}</span></div><div class="lab-feature-list" :aria-label='t("Feature list")'><button v-for="item in visible" :key="item.id" class="lab-feature" :class="{selected:item.id===selectedId}" :aria-pressed="item.id===selectedId" @click="choose(item)" :data-feature-id="item.id"><span><strong>{{t(item.title)}}</strong><small>{{t(data.groups[item.group])}}{{item.widget?' / '+t(item.subcategory):''}}</small></span><ArrowUpRight :size="15"/></button><div v-if="!filtered.length" class="lab-empty"><Search :size="26"/><strong>{{t("No features found.")}}</strong><p>{{t("Try “popup”, “gallery” or a different category.")}}</p><button class="lab-secondary" @click="query='';group='all'">{{t("Clear filters")}}</button></div></div><div class="lab-pagination"><button :disabled="page===0" @click="page--" :aria-label='t("Previous feature page")'><ArrowLeft :size="16"/></button><span>{{t("Page")}} <bdi dir="ltr">{{page+1}} / {{pages}}</bdi></span><button :disabled="page>=pages-1" @click="page++" :aria-label='t("Next feature page")'><ArrowRight :size="16"/></button></div></div>
  <div class="lab-demonstration" role="region" :aria-label="t('Simulation: {feature}',{feature:t(selected.title)})">
   <button class="lab-mobile-back" @click="browse"><ArrowLeft :size="13"/>{{t("Browse features")}}</button>
   <div class="lab-demo-title"><span class="lab-demo-category">{{t(data.groups[selected.group])}}<span v-if="selected.widget">{{t("/ Element")}}</span></span><span class="lab-simulation-badge">{{t("Interactive simulation")}}</span></div>
   <h3>{{t(selected.title)}}</h3><p class="lab-demo-description">{{t(selected.description)}}</p><p v-if="selected.requirement" class="lab-requirement">{{t(selected.requirement)}}</p>
   <div class="lab-canvas-toolbar"><span><span class="brand-dot"></span>{{t("Zadex playground")}}</span><div><button @click="reset" class="lab-reset" :aria-label='t("Reset simulation")'><RotateCcw :size="14"/></button><button @click="play" class="lab-play" :disabled="playing"><Play :size="13"/>{{t(playing?'Playing…':'Play walkthrough')}}</button></div></div>
   <Simulation v-if="activated" ref="stage" :key="selected.id+locale" :feature="selected" :replay="replay" @playing="playing=$event"/>
   <button v-else class="lab-wake" @click="activated=true">{{t("Open interactive canvas")}}<ArrowRight :size="16"/></button>
   <p class="lab-honesty">{{t("Illustrative interactions, built for this website. Sample content stays in your browser; forms, payments and integrations make no external requests.")}}<a href="#in-action">{{t("Watch the real editor")}}<ArrowUpRight :size="12"/></a></p>
  </div>
 </div>
</section>
</template>



