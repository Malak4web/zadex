import { createApp } from 'vue';
import App from './App.vue';
import './style.css';
import './i18n.css';
import {initializeLocale} from './i18n';
initializeLocale().catch(()=>{}).finally(()=>createApp(App).mount('#app'));
