import {ref} from 'vue';
// English source messages are their own keys; the audit catalog stays out of the runtime bundle.
const english={};
export const languages=[['en','English'],['ar','العربية'],['es','Español'],['de','Deutsch'],['ja','日本語'],['fr','Français'],['pt-BR','Português (Brasil)'],['it','Italiano'],['nl','Nederlands'],['pl','Polski'],['ru','Русский']];
export const locale=ref('en');
export const changing=ref(false);
const dictionaries={en:english};
const loaders=import.meta.glob(['./locales/*.json','!./locales/en.json']);
let request=0;
export function t(value,params){
 if(typeof value!=='string')return value;
 const clean=value.trim(),dictionary=dictionaries[locale.value]||english;
 let text=dictionary[clean]??clean;
 if(params)text=text.replace(/\{(\w+)\}/g,(_,name)=>String(params[name]??''));
 return text;
}
export async function setLocale(next,{persist=true}={}){
 if(!languages.some(([code])=>code===next))next='en';
 const id=++request;changing.value=true;
 try{
  if(!dictionaries[next])dictionaries[next]=(await loaders[`./locales/${next}.json`]()).default;
  if(id!==request)return;
  locale.value=next;
  if(typeof document!=='undefined'){
   document.documentElement.lang=next;document.documentElement.dir=next==='ar'?'rtl':'ltr';
   document.title=t('Zadex — Your ideas, made real.');
   document.querySelector('meta[name="description"]')?.setAttribute('content',t('A free visual builder for WordPress, with the freedom to make every detail yours.'));
   if(persist){try{localStorage.setItem('zadex-language',next);}catch{}const url=new URL(location.href);url.searchParams.set('lang',next);history.replaceState(null,'',url);}
  }
 }finally{if(id===request)changing.value=false;}
}
export function detectBrowserLocale(){
 if(typeof navigator==='undefined')return 'en';
 const navLanguages=Array.isArray(navigator.languages)&&navigator.languages.length>0
  ? navigator.languages
  : [navigator.language||navigator.userLanguage||navigator.browserLanguage].filter(Boolean);
 const supported=languages.map(([code])=>code);
 for(const candidate of navLanguages){
  if(typeof candidate!=='string')continue;
  const normalized=candidate.trim().replace('_','-');
  if(!normalized)continue;
  const lower=normalized.toLowerCase();
  const exact=supported.find(c=>c.toLowerCase()===lower);
  if(exact)return exact;
  const primary=lower.split('-')[0];
  const primaryMatch=supported.find(c=>c.toLowerCase()===primary);
  if(primaryMatch)return primaryMatch;
  const regionalMatch=supported.find(c=>c.toLowerCase().startsWith(primary+'-'));
  if(regionalMatch)return regionalMatch;
 }
 return 'en';
}
export async function initializeLocale(){
 let preferred='en';
 try{
  const urlParam=new URL(location.href).searchParams.get('lang');
  const stored=localStorage.getItem('zadex-language');
  if(urlParam&&languages.some(([code])=>code===urlParam)){
   preferred=urlParam;
  }else if(stored&&languages.some(([code])=>code===stored)){
   preferred=stored;
  }else{
   preferred=detectBrowserLocale();
  }
 }catch{
  preferred=detectBrowserLocale();
 }
 await setLocale(preferred,{persist:false});
}
