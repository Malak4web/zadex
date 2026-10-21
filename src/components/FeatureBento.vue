<script setup>
import {t, locale} from '../i18n';
import {ref, computed, watch, onMounted, onBeforeUnmount} from 'vue';
// Each tile is a small, local illustration of one capability; nothing here talks to WordPress or a store.
const typed=ref(''),count=ref(0),part=ref(0),device=ref(0),direction=ref('ltr'),tone=ref(0),popup=ref(false),cart=ref(0),bump=ref(false),posts=ref(4),email=ref('');
const headlines=['Make it bold.','Make it yours.','Make it real.'];
const elements=['Heading','Button','Image','Gallery','Tabs','Accordion','Countdown','Form','Video','Testimonial','Progress bar','Alert'];
const parts=['Header','Single post','Footer'];
const devices=[['Desktop',190],['Tablet',120],['Mobile',66]];
const tones=['#3565eb','#1a1d23','#7c95e8'];
const samples={ltr:['Hello, world','Your story, your language.','Read more'],rtl:['أهلاً بالعالم','قصتك، بلغتك.','اقرأ المزيد']};
const postTints=['#dfe7ff','#c9d6fb','#e8ebf2','#b9c9f7','#d6defa','#eef1f8','#cdd8f8','#e3e8f5'];
const emailState=computed(()=>!email.value?'idle':/^\S+@\S+\.\S+$/.test(email.value)?'valid':'invalid');
const root=ref(null);
let timers=[],alive=true,reduce=false,observer;
const wait=ms=>new Promise(resolve=>timers.push(setTimeout(resolve,ms)));
async function typeLoop(){
 for(let index=0;alive;index++){
  const word=t(headlines[index%headlines.length]);
  if(reduce){typed.value=word;await wait(2600);continue;}
  for(let i=0;i<=word.length&&alive;i++){typed.value=word.slice(0,i);await wait(70);}
  await wait(1800);
  for(let i=word.length;i>=0&&alive;i--){typed.value=word.slice(0,i);await wait(28);}
 }
}
function countUp(){if(reduce){count.value=102;return;}const start=performance.now();const frame=now=>{count.value=Math.round(102*Math.min(1,(now-start)/1200)**.6);if(count.value<102&&alive)requestAnimationFrame(frame);};requestAnimationFrame(frame);}
function addToCart(){cart.value++;bump.value=false;requestAnimationFrame(()=>bump.value=true);}
function loadMore(){posts.value=posts.value>=8?4:posts.value+4;}
watch(locale,()=>{typed.value='';});
onMounted(()=>{
 reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
 observer=new IntersectionObserver(([entry])=>{if(!entry.isIntersecting)return;observer.disconnect();typeLoop();countUp();timers.push(setInterval(()=>part.value=(part.value+1)%3,1300));},{threshold:.15});
 observer.observe(root.value);
});
onBeforeUnmount(()=>{alive=false;observer?.disconnect();timers.forEach(id=>{clearTimeout(id);clearInterval(id);});});
</script>

<template>
 <section ref="root" class="possibilities section-space" id="possibilities" aria-labelledby="possibilities-title">
  <div class="section-intro"><div><small class="bento-kicker">{{t("Every tile is live. Go ahead, click.")}}</small><h2 id="possibilities-title">{{t("Start with a page.")}}<br><span>{{t("Think whole website.")}}</span></h2></div><p>{{t("Easy enough for your first idea.")}}<br>{{t("Flexible enough for everything after it.")}}</p></div>
  <div class="bento">
   <article class="bento-tile bento-live">
    <span class="bento-label">{{t("Live visual editing")}}</span>
    <div class="bento-stage"><div class="bento-page" aria-hidden="true">
     <div class="bento-page-nav"><b>studio.</b><span>{{t("Work / Studio / Contact")}}</span></div>
     <div class="bento-typed"><span class="bento-typed-tag">{{t("Heading")}}</span>{{typed}}</div>
     <p>{{t("Edit it where it lives.")}}</p>
     <span class="bento-page-button" :class="{alt:typed.length%2}">{{t("Let’s talk")}}</span>
     <div class="bento-page-lines"><i></i><i></i><i></i></div>
    </div></div>
    <h3>{{t("Edit right on the real page.")}}</h3><p>{{t("Click, type and see it instantly. What you see is what your visitors get.")}}</p>
   </article>

   <article class="bento-tile bento-count">
    <span class="bento-label">{{t("Elements")}}</span>
    <div class="bento-stage"><span class="bento-number">{{count}}</span></div>
    <div class="bento-marquee" aria-hidden="true"><div><span v-for="(name,index) in [...elements,...elements]" :key="index">{{t(name)}}</span></div></div>
    <p>{{t("Ready on the canvas. All free.")}}</p>
   </article>

   <article class="bento-tile">
    <span class="bento-label">{{t("Theme builder")}}</span>
    <div class="bento-stage"><div class="bento-wire" aria-hidden="true"><i v-for="(name,index) in parts" :key="name" :class="{on:part===index}">{{t(name)}}</i></div></div>
    <h3>{{t("Your whole site. One creative space.")}}</h3>
   </article>

   <article class="bento-tile">
    <span class="bento-label">{{t("Responsive")}}</span>
    <div class="bento-stage bento-stack"><div class="bento-device-frame" aria-hidden="true"><div class="bento-device" :style="{width:devices[device][1]+'px'}"><i></i><i></i><div><i></i><i></i><i></i></div></div></div>
     <div class="bento-chips"><button v-for="(item,index) in devices" :key="item[0]" :class="{on:device===index}" :aria-pressed="device===index" @click="device=index">{{t(item[0])}}</button></div></div>
    <h3>{{t("Tuned for every screen.")}}</h3>
   </article>

   <article class="bento-tile">
    <span class="bento-label">{{t("Arabic & RTL")}}</span>
    <div class="bento-stage bento-stack"><div class="bento-rtl" :dir="direction" :lang="direction==='rtl'?'ar':'en'"><b>{{samples[direction][0]}}</b><span>{{samples[direction][1]}}</span><em>{{samples[direction][2]}}</em></div>
     <div class="bento-chips"><button :class="{on:direction==='ltr'}" :aria-pressed="direction==='ltr'" lang="en" @click="direction='ltr'">English</button><button :class="{on:direction==='rtl'}" :aria-pressed="direction==='rtl'" lang="ar" @click="direction='rtl'">العربية</button></div></div>
    <h3>{{t("Right-to-left, built in.")}}</h3>
   </article>

   <article class="bento-tile bento-components" :style="{'--tone':tones[tone]}">
    <span class="bento-label">{{t("Reusable components")}}</span>
    <div class="bento-stage">
     <div><div class="bento-main"><span>{{t("Main component")}}</span><div class="bento-card"><i></i><i></i><i></i><u></u></div></div>
      <div class="bento-tones"><button v-for="(color,index) in tones" :key="color" :class="{on:tone===index}" :style="{background:color}" :aria-pressed="tone===index" :aria-label='t("Component color {number}",{number:index+1})' @click="tone=index"></button></div></div>
     <div class="bento-instances" aria-hidden="true"><div v-for="n in 3" :key="n" class="bento-card"><i></i><i></i><u></u></div></div>
    </div>
    <h3>{{t("Design it once. Make it go further.")}}</h3><p>{{t("Pick a color on the main component. Every copy follows.")}}</p>
   </article>

   <article class="bento-tile">
    <span class="bento-label">{{t("Popups")}}</span>
    <div class="bento-stage bento-popup-stage"><button class="bento-button" :aria-expanded="popup" @click="popup=true">{{t("Open popup")}}</button>
     <div class="bento-modal" :class="{on:popup}" :inert="!popup"><div role="dialog" :aria-label='t("Before you go")'><b>{{t("Before you go")}}</b><span>{{t("New posts, straight to your inbox.")}}</span><button class="bento-button ghost" @click="popup=false">{{t("Close")}}</button></div></div></div>
    <h3>{{t("Popups, ready when you are.")}}</h3>
   </article>

   <article class="bento-tile">
    <span class="bento-label">WooCommerce</span>
    <span class="bento-cart" :class="{bump}" aria-live="polite">{{t("Cart")}} · {{cart}}</span>
    <div class="bento-stage"><div class="bento-product"><i aria-hidden="true"></i><div><b>{{t("Linen tote")}}</b><span>$24.00</span><button class="bento-button" @click="addToCart">{{t("Add to cart")}}</button></div></div></div>
    <h3>{{t("Store pages, your way.")}}</h3>
   </article>

   <article class="bento-tile bento-loop">
    <span class="bento-label">{{t("Dynamic content")}}</span>
    <div class="bento-stage"><div><div class="bento-posts"><div v-for="n in posts" :key="n" :style="{animationDelay:((n-1)%4)*60+'ms'}"><i :style="{background:postTints[n-1]}"></i><b>{{t("Post {number}",{number:n})}}</b></div></div>
     <div class="bento-loop-foot"><span class="bento-query">{{t("Posts · Newest first")}}</span><button class="bento-button ghost" @click="loadMore">{{posts>=8?t("Reset"):t("Load more")}}</button></div></div></div>
    <h3>{{t("A design that grows with your content.")}}</h3><p>{{t("Loop grids, custom fields, filters and pagination.")}}</p>
   </article>

   <article class="bento-tile">
    <span class="bento-label">{{t("Forms")}}</span>
    <div class="bento-stage"><label class="bento-field" :class="emailState"><span>{{t("Email")}}</span><input v-model="email" type="email" placeholder="you@studio.com" autocomplete="off"><small aria-live="polite">{{emailState==='valid'?t("Looks good."):emailState==='invalid'?t("That email looks incomplete."):t("Try typing an email.")}}</small></label></div>
    <h3>{{t("Forms that check as you type.")}}</h3>
   </article>

   <article class="bento-tile bento-free">
    <span class="bento-label">{{t("Price")}}</span>
    <div class="bento-stage"><span class="bento-price">$0<i>.</i></span></div>
    <h3>{{t("Every tile here is free.")}}</h3><p>{{t("No premium tier. Nothing locked.")}}</p>
   </article>
  </div>
  <small class="illustration-label">{{t("Interactive illustrations. The real editor is in the film above.")}}</small>
 </section>
</template>

<style>
.bento-kicker{display:flex;align-items:center;gap:8px;font-size:11px;color:var(--blue);margin-bottom:16px}
.bento-kicker:before{content:'';width:6px;height:6px;border-radius:50%;background:var(--blue)}
.bento{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));grid-auto-rows:minmax(250px,auto);gap:14px;margin-top:47px}
.bento-live{grid-column:span 2;grid-row:span 2}.bento-components,.bento-loop{grid-column:span 2}
.bento-tile{position:relative;background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px;display:flex;flex-direction:column;overflow:hidden;min-width:0;transition:border-color .2s,transform .25s cubic-bezier(.3,.7,.2,1),box-shadow .25s}
.bento-tile:hover{border-color:#3565eb66;transform:translateY(-3px);box-shadow:0 18px 40px #1b2a4512}
.bento-label{display:flex;align-items:center;gap:6px;font-size:10px;font-weight:500;color:var(--soft)}
.bento-label:before{content:'';width:5px;height:5px;border-radius:50%;background:var(--blue)}
.bento-stage{flex:1;display:grid;place-items:center;min-height:0;padding:12px 0}
.bento-stack{gap:10px;align-content:center}
.bento-tile h3{font:500 16px/1.35 Sora,sans-serif;letter-spacing:-.3px}
.bento-tile p{font-size:12px;color:var(--soft);line-height:1.6;margin-top:4px}
.bento-chips{display:flex;gap:4px;flex-wrap:wrap;justify-content:center}
.bento-chips button{font-size:10px;font-weight:500;border:1px solid var(--line);border-radius:6px;padding:6px 9px;background:#fff;color:var(--soft)}
.bento-chips button.on{background:var(--ink);color:#fff;border-color:var(--ink)}
.bento-button{font-size:11px;font-weight:500;padding:9px 13px;border-radius:7px;border:1px solid var(--blue);background:var(--blue);color:#fff}
.bento-button:hover{background:#2c5ad6}
.bento-button.ghost{background:transparent;border-color:var(--line);color:var(--ink)}.bento-button.ghost:hover{background:#f4f5f8}

.bento-live .bento-stage{place-items:stretch}
.bento-page{background:#f4f5f8;border-radius:12px;padding:18px;display:flex;flex-direction:column;gap:10px}
.bento-page-nav{display:flex;justify-content:space-between;gap:10px;font-size:10px;color:var(--soft);padding-bottom:10px;border-bottom:1px solid var(--line)}
.bento-page-nav b{color:var(--ink);font-family:Sora,sans-serif}
.bento-typed{position:relative;align-self:flex-start;font:500 clamp(28px,3vw,42px)/1.15 Sora,sans-serif;letter-spacing:-.04em;min-height:1.4em;margin:20px 0 0;padding:2px 8px;margin-inline-start:-8px;border:1px solid var(--blue)}
.bento-typed:after{content:'';display:inline-block;width:2px;height:.9em;background:var(--blue);margin-inline-start:3px;vertical-align:-.08em;animation:bento-blink 1s steps(1) infinite}
.bento-typed-tag{position:absolute;inset-inline-start:-1px;top:-21px;font:500 9px 'Public Sans',sans-serif;letter-spacing:0;background:var(--blue);color:#fff;padding:4px 6px;border-radius:4px 4px 0 0}
@keyframes bento-blink{50%{opacity:0}}
.bento-page>p{font-size:12px;color:var(--soft);margin:0}
.bento-page-button{align-self:flex-start;font-size:11px;padding:9px 14px;border-radius:7px;background:var(--blue);color:#fff;transition:background .4s}
.bento-page-button.alt{background:var(--ink)}
.bento-page-lines{display:flex;gap:8px;margin-top:auto;padding-top:14px}.bento-page-lines i{flex:1;height:44px;border-radius:8px;background:#fff}

.bento-count{background:var(--blue);border-color:var(--blue);color:#fff}
.bento-count .bento-label{color:#d5e0ff}.bento-count .bento-label:before{background:#fff}
.bento-count p{color:#d5e0ff;margin-top:12px}
.bento-number{font:500 84px/1 Sora,sans-serif;letter-spacing:-.05em;direction:ltr}
.bento-marquee{overflow:hidden;margin:0 -18px;direction:ltr;mask:linear-gradient(90deg,transparent,#000 15%,#000 85%,transparent)}
.bento-marquee div{display:flex;gap:6px;width:max-content;animation:bento-slide 26s linear infinite}
.bento-marquee span{font-size:10px;font-weight:500;padding:5px 8px;border-radius:6px;background:#ffffff1f;white-space:nowrap}
@keyframes bento-slide{to{transform:translateX(-50%)}}

.bento-wire{width:100%;max-width:170px;display:grid;gap:5px}
.bento-wire i{display:flex;align-items:center;justify-content:center;border-radius:5px;background:#f4f5f8;font-size:9px;font-style:normal;font-weight:500;color:transparent;transition:all .35s}
.bento-wire i:nth-child(1),.bento-wire i:nth-child(3){height:24px}.bento-wire i:nth-child(2){height:70px}
.bento-wire i.on{background:#3565eb14;outline:1px solid var(--blue);color:var(--blue)}

.bento-device-frame{height:112px;width:100%;display:grid;place-items:center}
.bento-device{height:100%;border:1.5px solid var(--ink);border-radius:10px;padding:8px;display:flex;flex-direction:column;gap:5px;transition:width .45s cubic-bezier(.3,.7,.2,1)}
.bento-device i{display:block;height:8px;border-radius:3px;background:#f4f5f8}.bento-device>i:first-child{height:14px;width:60%;background:var(--blue)}
.bento-device div{display:flex;gap:4px;flex:1}.bento-device div i{flex:1;height:auto;min-width:14px}

.bento-rtl{width:100%;max-width:200px;background:#f4f5f8;border-radius:10px;padding:12px;text-align:start}
.bento-rtl b{display:block;font:500 15px Sora,sans-serif}
.bento-rtl span{display:block;font-size:10px;color:var(--soft);margin:4px 0 10px}
.bento-rtl em{font-style:normal;font-size:10px;background:var(--blue);color:#fff;padding:5px 9px;border-radius:5px}
.bento-rtl[dir=rtl] b,.bento-rtl[dir=rtl] span,.bento-rtl[dir=rtl] em{font-family:Tajawal,sans-serif}.bento-rtl[dir=rtl] b{font-size:17px;font-weight:700}

.bento-components .bento-stage{grid-template-columns:1.1fr 1.6fr;gap:24px;place-items:center stretch}
.bento-main{position:relative;border:1px solid var(--blue);border-radius:10px;padding:12px}
.bento-main>span{position:absolute;top:-8px;inset-inline-start:10px;font-size:9px;font-weight:500;background:#fff;color:var(--blue);padding:0 5px}
.bento-card{border-radius:8px;padding:10px;background:#f4f5f8;display:grid;gap:6px}
.bento-card i{display:block;height:7px;border-radius:3px;background:var(--line)}.bento-card i:first-child{width:50%;height:9px;background:var(--ink)}
.bento-card u{display:block;height:16px;width:46%;border-radius:4px;background:var(--tone);transition:background .4s}
.bento-tones{display:flex;gap:7px;margin-top:12px}
.bento-tones button{width:20px;height:20px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px var(--line);padding:0}
.bento-tones button.on{box-shadow:0 0 0 2px var(--ink)}
.bento-instances{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}

.bento-popup-stage{position:relative}
.bento-modal{position:absolute;inset:6px 0;background:#1a1d2366;border-radius:10px;display:grid;place-items:center;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
.bento-modal>div{background:#fff;border-radius:9px;padding:14px;width:82%;text-align:center;transform:scale(.9) translateY(8px);transition:transform .3s cubic-bezier(.3,.7,.2,1)}
.bento-modal.on{opacity:1;visibility:visible}.bento-modal.on>div{transform:none}
.bento-modal b{display:block;font:500 13px Sora,sans-serif}.bento-modal span{display:block;font-size:10px;color:var(--soft);margin:4px 0 10px}

.bento-cart{position:absolute;top:16px;inset-inline-end:16px;font-size:10px;font-weight:500;background:#f4f5f8;border-radius:20px;padding:5px 9px}
.bento-cart.bump{animation:bento-bump .35s}
@keyframes bento-bump{40%{transform:scale(1.2);background:var(--blue);color:#fff}}
.bento-product{display:flex;gap:12px;align-items:center;width:100%}
.bento-product>i{width:64px;height:78px;border-radius:9px;background:linear-gradient(160deg,#dfe7ff,#b9c9f7);flex-shrink:0}
.bento-product b{display:block;font:500 13px Sora,sans-serif}.bento-product span{display:block;font-size:11px;color:var(--soft);margin:2px 0 8px}

.bento-loop .bento-stage{place-items:stretch}
.bento-posts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
.bento-posts>div{border-radius:8px;background:#f4f5f8;padding:7px;animation:bento-in .45s both cubic-bezier(.3,.7,.2,1)}
.bento-posts i{display:block;height:40px;border-radius:5px}
.bento-posts b{display:block;font-size:9px;font-weight:500;color:var(--soft);margin-top:6px}
@keyframes bento-in{from{opacity:0;transform:translateY(10px)}}
.bento-loop-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:10px}
.bento-query{font-size:10px;font-weight:500;border:1px solid var(--line);border-radius:6px;padding:6px 9px;color:var(--soft)}

.bento-field{width:100%;display:grid;gap:6px}
.bento-field>span{font-size:11px;font-weight:500}
.bento-field input{font:inherit;font-size:12px;border:1px solid var(--line);border-radius:7px;padding:10px;outline:none;transition:border-color .2s;min-width:0}
.bento-field input:focus{border-color:var(--blue)}
.bento-field small{font-size:10px;min-height:13px;color:var(--soft)}
.bento-field.invalid input{border-color:#c93a3a}.bento-field.invalid small{color:#b42f2f}.bento-field.valid small{color:#1d7a50}

.bento-free{background:var(--ink);border-color:var(--ink);color:#fff}
.bento-free .bento-label,.bento-free p{color:#9aa4b8}
.bento-price{font:500 64px/1 Sora,sans-serif;letter-spacing:-.04em;direction:ltr}.bento-price i{font-style:normal;color:var(--blue)}

html[lang=ar] .bento-tile h3,html[lang=ar] .bento-typed,html[lang=ar] .bento-modal b,html[lang=ar] .bento-product b{font-family:Tajawal,sans-serif;letter-spacing:0}
@media (max-width:1050px){.bento{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:650px){.bento{grid-template-columns:minmax(0,1fr)}.bento-live,.bento-components,.bento-loop{grid-column:auto}.bento-live{grid-row:auto}.bento-components .bento-stage{grid-template-columns:1fr}.bento-posts{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (prefers-reduced-motion:reduce){.bento-marquee div,.bento-typed:after{animation:none}.bento-tile:hover{transform:none}}
</style>
