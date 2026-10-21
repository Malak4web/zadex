import {createApp} from 'vue';
import Docs from './docs/Docs.vue';
import './style.css';
import './i18n.css';
import './docs/docs.css';
import {initializeLocale,locale,setLocale} from './i18n';
import {loadDocsLocale} from './docs/i18n';
initializeLocale().catch(()=>{}).then(async()=>{
 if(!['en','ar'].includes(locale.value)){
  await setLocale('en',{persist:false});
  const url=new URL(location.href);url.searchParams.set('lang','en');history.replaceState(null,'',url);
 }
 await loadDocsLocale();
}).finally(()=>createApp(Docs).mount('#app'));
