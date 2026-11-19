import {createApp} from 'vue';
import Migrator from './migrator/Migrator.vue';
import './style.css';
import './i18n.css';
import './migrator/migrator.css';
import {initializeLocale,locale,setLocale} from './i18n';
import {loadMigratorLocale} from './migrator/i18n';
// The add-on page ships in English and Arabic, like the documentation.
initializeLocale().catch(()=>{}).then(async()=>{
 if(!['en','ar'].includes(locale.value)){
  await setLocale('en',{persist:false});
  const url=new URL(location.href);url.searchParams.set('lang','en');history.replaceState(null,'',url);
 }
 await loadMigratorLocale();
}).finally(()=>createApp(Migrator).mount('#app'));
