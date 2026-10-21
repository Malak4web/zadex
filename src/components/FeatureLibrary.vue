<script setup>
import {t, locale} from "../i18n";
import {ref,computed,defineAsyncComponent,onMounted,onBeforeUnmount,watch,nextTick,h} from 'vue';
import {Search,ArrowUpRight,ArrowRight,X,Play,RotateCcw} from 'lucide-vue-next';
import data from '../data/features.json';
const Simulation=defineAsyncComponent({loader:()=>import('./FeatureSimulation.vue'),loadingComponent:{render:()=>h('p',{class:'lab-loading',role:'status'},t('Opening the canvas…'))},errorComponent:{render:()=>h('p',{role:'alert'},t('The canvas couldn’t load. Refresh the page to try again.'))},timeout:15000});
// One path through 185 features: choose a category, choose a feature, try it in a single card.
const query=ref(''),group=ref('editor'),expanded=ref(false),searching=ref(false),selectedId=ref('live-editing'),activated=ref(false),replay=ref(0),playing=ref(false),stage=ref(null),library=ref(null),searchInput=ref(null),collapsedCount=12;
let observer,stageObserver;
const calm=()=>window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const selected=computed(()=>data.catalog.find(x=>x.id===selectedId.value)||data.catalog[0]);
const groups=computed(()=>Object.entries(data.groups).map(([key,label])=>({key,label,count:data.catalog.filter(x=>x.group===key).length})));
const normalize=value=>value.toLowerCase().normalize('NFKD').replace(/[̀-ًͯ-ٰٟ]/g,'');
const filtered=computed(()=>{const words=normalize(query.value).split(/\s+/).filter(Boolean);if(!words.length)return data.catalog.filter(item=>item.group===group.value);return data.catalog.filter(item=>words.every(word=>normalize([t(item.title),t(item.description),t(item.subcategory),t(data.groups[item.group]),item.title,item.description,item.subcategory,...item.keywords].join(' ')).includes(word)));});
const visible=computed(()=>{if(expanded.value||filtered.value.length<=collapsedCount)return filtered.value;const head=filtered.value.slice(0,collapsedCount-1);return head.some(x=>x.id===selectedId.value)||!filtered.value.some(x=>x.id===selectedId.value)?filtered.value.slice(0,collapsedCount):[...head,selected.value];});
const hidden=computed(()=>filtered.value.length-visible.value.length);
watch([query,locale],()=>{expanded.value=false;});
async function choose(item,{scroll=true}={}){selectedId.value=item.id;activated.value=true;playing.value=false;replay.value=calm()?0:1;await nextTick();if(scroll&&window.innerWidth<=650)library.value.querySelector('.lab-stage')?.scrollIntoView({behavior:calm()?'auto':'smooth',block:'start'});}
function pickGroup(key){query.value='';searching.value=false;group.value=key;expanded.value=false;const first=data.catalog.find(x=>x.group===key);if(first)choose(first,{scroll:false});}
async function openSearch(){searching.value=true;await nextTick();searchInput.value?.focus();}
function closeSearch(){query.value='';searching.value=false;}
async function play(){activated.value=true;await nextTick();replay.value++;}
function reset(){playing.value=false;replay.value=0;stage.value?.reset?.();}
onMounted(()=>{
 observer=new IntersectionObserver(entries=>{if(entries.some(x=>x.isIntersecting)){activated.value=true;observer.disconnect();}},{rootMargin:'180px'});observer.observe(library.value);
 stageObserver=new IntersectionObserver(entries=>{if(entries.some(x=>x.isIntersecting)){stageObserver.disconnect();if(!calm()&&replay.value===0)play();}},{threshold:.55});stageObserver.observe(library.value.querySelector('.lab-stage'));
});
onBeforeUnmount(()=>{observer?.disconnect();stageObserver?.disconnect();});
</script>
<template>
<section ref="library" id="feature-lab" class="feature-lab section-space" aria-labelledby="feature-lab-title">
 <div class="section-intro"><h2 id="feature-lab-title">{{t("Don’t just read about it.")}}<br><span>{{t("Get a feel for it.")}}</span></h2><p>{{t("Explore every feature. Try the controls.")}}<br>{{t("See what your next website could do.")}}</p></div>

 <div class="lab-picker">
  <div class="lab-tabs-row">
   <div v-if="!searching" class="lab-tabs" role="tablist" :aria-label='t("Feature category")'>
    <button v-for="item in groups" :key="item.key" role="tab" :aria-selected="group===item.key" :class="{active:group===item.key}" @click="pickGroup(item.key)">{{t(item.label)}}<small>{{t(item.count)}}</small></button>
   </div>
   <label v-else class="lab-find"><Search :size="16"/><span class="sr-only">{{t("Search all features")}}</span><input ref="searchInput" v-model="query" type="search" :placeholder='t("Search features, elements, integrations…")' autocomplete="off" @keydown.esc="closeSearch"></label>
   <button class="lab-find-toggle" :aria-label='t(searching?"Clear feature search":"Search all features")' :aria-expanded="searching" @click="searching?closeSearch():openSearch()"><X v-if="searching" :size="16"/><Search v-else :size="16"/></button>
  </div>
  <div class="lab-chips" :aria-label='t("Feature list")'>
   <button v-for="item in visible" :key="item.id" :class="{active:item.id===selectedId}" :aria-pressed="item.id===selectedId" :data-feature-id="item.id" @click="choose(item)">{{t(item.title)}}</button>
   <button v-if="hidden>0" class="lab-chips-more" @click="expanded=true">{{t("+{count} more",{count:hidden})}}</button>
   <button v-else-if="expanded&&filtered.length>collapsedCount" class="lab-chips-more" @click="expanded=false">{{t("Show fewer")}}</button>
   <div v-if="!filtered.length" class="lab-empty"><strong>{{t("No features found.")}}</strong><p>{{t("Try “popup”, “gallery” or a different category.")}}</p><button class="lab-chips-more" @click="closeSearch">{{t("Clear filters")}}</button></div>
  </div>
 </div>

 <div class="lab-stage" role="region" :aria-label="t('Simulation: {feature}',{feature:t(selected.title)})">
  <header class="lab-stage-head">
   <div><span class="lab-stage-kicker">{{t(data.groups[selected.group])}}<span v-if="selected.widget" class="lab-stage-sub"> · {{t(selected.subcategory)}}</span></span><h3>{{t(selected.title)}}</h3><p>{{t(selected.description)}}</p><p v-if="selected.requirement" class="lab-requirement">{{t(selected.requirement)}}</p></div>
   <div class="lab-stage-actions"><button @click="reset" class="lab-reset" :aria-label='t("Reset simulation")'><RotateCcw :size="15"/></button><button @click="play" class="lab-play" :disabled="playing"><Play :size="13"/>{{t(playing?'Playing…':'Watch how it works')}}</button></div>
  </header>
  <Simulation v-if="activated" ref="stage" :key="selected.id+locale" :feature="selected" :replay="replay" @playing="playing=$event"/>
  <button v-else class="lab-wake" @click="activated=true">{{t("Open interactive canvas")}}<ArrowRight :size="16"/></button>
 </div>
 <p class="lab-honesty">{{t("Illustrative interactions, built for this website. Sample content stays in your browser; forms, payments and integrations make no external requests.")}}<a href="#in-action">{{t("Watch the real editor")}}<ArrowUpRight :size="12"/></a></p>
</section>
</template>
