<script setup>
import {t, locale} from '../i18n';
import {ref, watch, nextTick, onMounted, onBeforeUnmount} from 'vue';
import {ArrowUpRight} from 'lucide-vue-next';
// The hero doubles as a small Zadex canvas: a guided cursor edits it once, then visitors can select, restyle and rewrite the copy.
defineProps({download:String});
const root=ref(null),hoverBox=ref(null),selection=ref(null),toolbar=ref(null),ghost=ref(null);
const selected=ref(null),hovering=ref(false),toolsOpen=ref(false),dims=ref(''),bold=ref(false),italic=ref(false),handedOver=ref(false),guided=ref(false);
let demo=true,motion;
const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const nodes=()=>[...(root.value?.querySelectorAll('[data-canvas]')||[])];
function frame(el){const r=el.getBoundingClientRect(),h=root.value.getBoundingClientRect();return{x:r.left-h.left,y:r.top-h.top,w:r.width,h:r.height};}
function place(node,el,pad){const b=frame(el);Object.assign(node.style,{left:b.x-pad+'px',top:b.y-pad+'px',width:b.w+pad*2+'px',height:b.h+pad*2+'px'});return b;}
function select(el,tools=true){
 nodes().forEach(node=>{if(node!==el)node.contentEditable='false';});
 selected.value=el;const b=place(selection.value,el,8);
 dims.value=`${el.dataset.label} · ${Math.round(b.w)} × ${Math.round(b.h)}`;
 const style=getComputedStyle(el);bold.value=Number(style.fontWeight)>=700;italic.value=style.fontStyle==='italic';
 toolsOpen.value=tools;
 nextTick(()=>{const bar=toolbar.value;if(!bar)return;const max=root.value.offsetWidth-bar.offsetWidth;bar.style.left=Math.max(0,Math.min(b.x-8,max))+'px';const below=b.y+b.h+16;bar.style.top=(below+bar.offsetHeight<=root.value.offsetHeight?below:Math.max(0,b.y-58))+'px';});
}
function clear(){const el=selected.value;if(el)el.contentEditable='false';selected.value=null;toolsOpen.value=false;}
function apply(kind,value){
 const el=selected.value;if(!el)return;
 if(kind==='color'){if(el.dataset.kind==='button')el.style.background=value;else el.style.color=value;}
 if(kind==='bold')el.style.fontWeight=bold.value?'':'700';
 if(kind==='italic')el.style.fontStyle=italic.value?'':'italic';
 if(kind==='edit'&&el.dataset.kind!=='button'){el.contentEditable='true';el.focus();document.getSelection()?.selectAllChildren(el);}
 requestAnimationFrame(()=>select(el));
}
function handOver(){if(!demo)return;demo=false;guided.value=false;handedOver.value=true;}
function onEnter(event){const el=event.currentTarget;if(el===selected.value)return;place(hoverBox.value,el,6);hovering.value=true;}
function onPick(event){handOver();hovering.value=false;select(event.currentTarget);}
function onEdit(event){handOver();select(event.currentTarget);apply('edit');}
function onKey(event){
 const el=event.currentTarget;
 if(event.key==='Escape'){el.contentEditable='false';clear();el.blur?.();return;}
 if(event.key==='Enter'&&el.contentEditable!=='true'){event.preventDefault();handOver();select(el);apply('edit');}
}
function onTool(kind,value){handOver();apply(kind,value);}
function outside(event){if(!root.value?.contains(event.target)||!event.target.closest('[data-canvas],.canvas-toolbar'))clear();}
function refit(){if(selected.value?.isConnected)select(selected.value,toolsOpen.value);}

function point(el,dx=.5,dy=.6){const b=frame(el);ghost.value.style.transform=`translate(${b.x+b.w*dx}px,${b.y+b.h*dy}px)`;}
async function press(){ghost.value.classList.add('pressing');await wait(160);ghost.value?.classList.remove('pressing');}
async function guide(){
 // Each step re-checks `demo`, so any visitor input ends the walkthrough immediately.
 const step=async(ms,action)=>{await wait(ms);if(!demo||!root.value)throw 0;await action?.();};
 const swatch=color=>toolbar.value.querySelector(`[data-color="${color}"]`),tool=name=>toolbar.value.querySelector(`[data-tool="${name}"]`);
 try{
  const [,line,,,cta]=nodes();
  guided.value=true;ghost.value.style.transform=`translate(${root.value.offsetWidth*.5}px,0px)`;
  await step(700,()=>point(line,.85));
  await step(1000,press);select(line);
  await step(700,()=>point(swatch('#1a1d23')));
  await step(950,press);line.style.color='#1a1d23';
  await step(500,()=>point(tool('italic')));
  await step(900,press);line.style.fontStyle='italic';select(line);
  await step(700,()=>point(swatch('#3565eb')));
  await step(900,press);line.style.color='';line.style.fontStyle='';select(line);
  await step(600,()=>point(cta,.75));
  await step(1100,press);select(cta,false);
  await step(1500,handOver);
 }catch{}
}
watch(locale,async()=>{handOver();clear();await nextTick();const line=nodes()[1];if(line)select(line,false);});
let observer;
onMounted(async()=>{
 motion=window.matchMedia('(prefers-reduced-motion: reduce)');
 document.addEventListener('pointerdown',outside);window.addEventListener('resize',refit);
 await document.fonts?.ready;await nextTick();
 const line=nodes()[1];if(line)select(line,false);
 if(motion.matches){handOver();return;}
 observer=new IntersectionObserver(([entry])=>{if(entry.isIntersecting){observer.disconnect();guide();}},{threshold:.6});
 observer.observe(root.value);
});
onBeforeUnmount(()=>{demo=false;observer?.disconnect();document.removeEventListener('pointerdown',outside);window.removeEventListener('resize',refit);});
</script>

<template>
 <div ref="root" class="hero-canvas">
  <div :key="locale" class="hero-intro">
   <h1 id="hero-title"><span data-canvas :data-label='t("Heading")' tabindex="0" @mouseenter="onEnter" @mouseleave="hovering=false" @click="onPick" @focus="onPick" @dblclick="onEdit" @keydown="onKey" @input="refit">{{t("Your ideas.")}}</span><br><span class="selected-word" data-canvas :data-label='t("Heading")' tabindex="0" @mouseenter="onEnter" @mouseleave="hovering=false" @click="onPick" @focus="onPick" @dblclick="onEdit" @keydown="onKey" @input="refit">{{t("Made real.")}}</span></h1>
   <div class="hero-aside">
    <p data-canvas :data-label='t("Text")' tabindex="0" @mouseenter="onEnter" @mouseleave="hovering=false" @click="onPick" @focus="onPick" @dblclick="onEdit" @keydown="onKey" @input="refit">{{t("That website in your head?")}}<br>{{t("Bring it to life, right on the page.")}}</p>
    <p class="hero-description" data-canvas :data-label='t("Text")' tabindex="0" @mouseenter="onEnter" @mouseleave="hovering=false" @click="onPick" @focus="onPick" @dblclick="onEdit" @keydown="onKey" @input="refit">{{t("Meet Zadex. A free visual builder for WordPress, with the freedom to make every detail yours.")}}</p>
    <a class="button button-primary hero-download" data-canvas data-kind="button" :data-label='t("Button")' :href="download" target="_blank" rel="noopener">{{t("Start creating, for free")}}<ArrowUpRight :size="17"/></a>
    <span class="download-note">{{t("All the features. No premium upgrade.")}}</span>
   </div>
  </div>
  <p class="canvas-hint" aria-live="polite"><span class="canvas-pulse"></span><span v-if="handedOver">{{t("Your turn. Select any text, restyle it, or double-click to rewrite it.")}}</span><span v-else>{{t("This hero is a live Zadex canvas. Watch, then try it yourself.")}}</span></p>
  <div class="canvas-layer" aria-hidden="true">
   <div ref="hoverBox" class="canvas-hover" :class="{on:hovering}"></div>
   <div ref="selection" class="canvas-selection" :class="{on:selected}"><b></b><b></b><b></b><b></b><span>{{dims}}</span></div>
  </div>
  <div ref="toolbar" class="canvas-toolbar" :class="{on:toolsOpen}" role="toolbar" :aria-label='t("Element tools")' :inert="!toolsOpen">
   <span class="canvas-tag">{{selected?.dataset.label}}</span>
   <button data-color="#3565eb" :aria-label='t("Zadex blue")' @click="onTool('color','#3565eb')"><span style="background:#3565eb"></span></button>
   <button data-color="#1a1d23" :aria-label='t("Ink")' @click="onTool('color','#1a1d23')"><span style="background:#1a1d23"></span></button>
   <button data-color="#5b6472" :aria-label='t("Soft gray")' @click="onTool('color','#5b6472')"><span style="background:#5b6472"></span></button>
   <i></i>
   <button data-tool="bold" :class="{active:bold}" :aria-pressed="bold" :aria-label='t("Bold")' @click="onTool('bold')"><b>B</b></button>
   <button data-tool="italic" :class="{active:italic}" :aria-pressed="italic" :aria-label='t("Italic")' @click="onTool('italic')"><em>I</em></button>
   <button v-if="selected?.dataset.kind!=='button'" class="canvas-edit" @click="onTool('edit')">{{t("Edit")}}</button>
  </div>
  <div ref="ghost" class="canvas-ghost" :class="{on:guided}" aria-hidden="true"><svg width="18" height="22" viewBox="0 0 18 22"><path d="M1 1l15 11.5-6.6.9 3.9 7.4-3 1.4-3.8-7.5L1 19z" fill="#1a1d23" stroke="#fff" stroke-width="1.4" stroke-linejoin="round"/></svg><span>Zadex</span></div>
 </div>
</template>

<style>
.hero-canvas{position:relative}
.hero{position:relative;background-image:radial-gradient(#c9d0de 1px,transparent 1px);background-size:22px 22px;background-position:-11px -11px}
.hero:before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,#f4f5f8 78%);pointer-events:none}
.hero>*{position:relative}
[data-canvas]{outline:none;cursor:default;border-radius:2px}
[data-canvas][contenteditable=true]{cursor:text;caret-color:var(--blue)}
.hero-download[data-canvas]{cursor:pointer}
.canvas-layer{position:absolute;inset:0;pointer-events:none;z-index:4}
.canvas-hover,.canvas-selection{position:absolute;opacity:0;transition:left .28s cubic-bezier(.3,.7,.2,1),top .28s cubic-bezier(.3,.7,.2,1),width .28s cubic-bezier(.3,.7,.2,1),height .28s cubic-bezier(.3,.7,.2,1),opacity .2s}
.canvas-hover{border:1px dashed #3565eb99}
.canvas-selection{border:1px solid var(--blue)}
.canvas-hover.on,.canvas-selection.on{opacity:1}
.canvas-selection b{position:absolute;width:7px;height:7px;background:#fff;border:1px solid var(--blue)}
.canvas-selection b:nth-child(1){left:-4px;top:-4px}.canvas-selection b:nth-child(2){right:-4px;top:-4px}.canvas-selection b:nth-child(3){left:-4px;bottom:-4px}.canvas-selection b:nth-child(4){right:-4px;bottom:-4px}
.canvas-selection span{position:absolute;left:-1px;top:-24px;font:500 10px/1 'Public Sans',sans-serif;color:#fff;background:var(--blue);padding:5px 7px;border-radius:4px;white-space:nowrap;direction:ltr;unicode-bidi:isolate}
.canvas-toolbar{position:absolute;left:0;top:0;z-index:6;display:flex;align-items:center;gap:2px;padding:4px;background:var(--ink);border-radius:10px;box-shadow:0 12px 30px #1a1d2333;opacity:0;visibility:hidden;transform:translateY(6px);transition:opacity .2s,transform .2s,visibility .2s,left .28s cubic-bezier(.3,.7,.2,1),top .28s cubic-bezier(.3,.7,.2,1)}
.canvas-toolbar.on{opacity:1;visibility:visible;transform:none}
.canvas-tag{font-size:10px;font-weight:500;color:#fff;background:var(--blue);padding:7px 9px;border-radius:7px;margin-inline-end:4px;white-space:nowrap}
.canvas-toolbar button{display:grid;place-items:center;min-width:32px;height:32px;border:0;border-radius:7px;background:transparent;color:#c9d0de;font-size:12px;padding:0 6px}
.canvas-toolbar button:hover,.canvas-toolbar button.active{background:#ffffff1f;color:#fff}
.canvas-toolbar button:focus-visible{outline-offset:1px}
.canvas-toolbar button>span{width:14px;height:14px;border-radius:50%;border:2px solid #ffffff40}
.canvas-toolbar>i{width:1px;height:18px;background:#ffffff26;margin:0 4px}
.canvas-ghost{position:absolute;left:0;top:0;z-index:7;pointer-events:none;opacity:0;transition:transform .9s cubic-bezier(.5,0,.2,1),opacity .3s}
.canvas-ghost.on{opacity:1}
.canvas-ghost svg{display:block;filter:drop-shadow(0 2px 3px #0003);transition:transform .12s}
.canvas-ghost.pressing svg{transform:scale(.82)}
.canvas-ghost span{position:absolute;left:16px;top:18px;font:500 10px 'Public Sans',sans-serif;color:#fff;background:var(--blue);padding:3px 7px;border-radius:5px 5px 5px 1px}
.canvas-hint{display:flex;align-items:center;gap:10px;margin-top:34px;font-size:11px;color:var(--soft)}
.canvas-pulse{width:6px;height:6px;border-radius:50%;background:var(--blue);flex-shrink:0;box-shadow:0 0 0 4px #3565eb22;animation:canvas-pulse 2s infinite}
@keyframes canvas-pulse{50%{box-shadow:0 0 0 7px #3565eb00}}
@media (max-width:760px){.canvas-hint{margin-top:26px}.canvas-tag{display:none}}
@media (prefers-reduced-motion:reduce){.canvas-pulse{animation:none}.canvas-ghost{display:none}}
</style>
