<script setup>
import {ref} from 'vue';
import {Languages} from 'lucide-vue-next';
import {languages,locale,changing,setLocale,t} from '../i18n';
const error=ref(false);
defineProps({supported:{type:Array,default:null}});
async function change(event){error.value=false;try{await setLocale(event.target.value);}catch{event.target.value=locale.value;error.value=true;}}
</script>
<template>
 <div class="language-picker"><label><Languages :size="16" aria-hidden="true"/><span class="sr-only">{{t('Language')}}</span><select :value="locale" :disabled="changing" @change="change" :aria-label="t('Language')"><option v-for="[code,name] in languages.filter(([code])=>!supported||supported.includes(code))" :key="code" :value="code" :selected="code===locale" :lang="code">{{name}}</option></select></label><span v-if="changing" class="sr-only" role="status">{{t('Changing language…')}}</span><p v-if="error" role="alert">{{t('Unable to load this language. Please try again.')}}</p></div>
</template>

