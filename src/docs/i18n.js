import {ref} from 'vue';
import {locale,t} from '../i18n';
const packs={en:{}};
const loaders=import.meta.glob(['./locales/*.json','!./locales/en.json']);
export const docsReady=ref(false),docsError=ref(false);
let token=0;
export function d(value,params){
 if(typeof value!=='string')return value;
 let text=packs[locale.value]?.[value]??t(value);
 if(params)text=text.replace(/\{(\w+)\}/g,(_,key)=>String(params[key]??''));
 return text;
}
export async function loadDocsLocale(){const request=++token;docsReady.value=false;docsError.value=false;const next=locale.value;try{if(!packs[next])packs[next]=(await loaders[`./locales/${next}.json`]()).default;if(request===token)docsReady.value=true;}catch{if(request===token)docsError.value=true;}}
