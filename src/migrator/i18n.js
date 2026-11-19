import {ref} from 'vue';
import {locale,t} from '../i18n';
// English copy is its own key; Arabic lives in locales/ar.json. Anything missing falls back to the main site dictionary.
const packs={en:{}};
const loaders=import.meta.glob('./locales/*.json');
export const migratorReady=ref(false);
const loaded=ref(0);
let token=0;
export function m(value,params){
 if(typeof value!=='string')return value;
 loaded.value;
 let text=packs[locale.value]?.[value]??t(value);
 if(params)text=text.replace(/\{(\w+)\}/g,(_,key)=>String(params[key]??''));
 return text;
}
export async function loadMigratorLocale(){const request=++token;const next=locale.value;try{if(!packs[next]&&loaders[`./locales/${next}.json`])packs[next]=(await loaders[`./locales/${next}.json`]()).default;}catch{}if(request===token){migratorReady.value=true;loaded.value++;}}
