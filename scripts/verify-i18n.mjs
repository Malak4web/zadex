import assert from 'node:assert/strict';
import fs from 'node:fs';
import {createServer} from 'vite';
import {createSSRApp} from 'vue';
import {renderToString} from 'vue/server-renderer';
const locales=['en','ar','es','de','ja','fr','pt-BR','it','nl','pl','ru'];
const source=JSON.parse(fs.readFileSync('src/locales/en.json','utf8'));
const catalog=JSON.parse(fs.readFileSync('src/data/features.json','utf8')).catalog;
for(const locale of locales){
 const messages=JSON.parse(fs.readFileSync(`src/locales/${locale}.json`,'utf8'));
 for(const key of Object.keys(source)){
  assert.ok(messages[key]?.trim(),`${locale}: missing ${key}`);
  assert.deepEqual((messages[key].match(/\{\w+\}/g)||[]).sort(),(key.match(/\{\w+\}/g)||[]).sort(),`${locale}: parameters in ${key}`);
  assert.ok(!/\[ZX\d+\]|ZXPH\d+ZX|⏎/.test(messages[key]),`${locale}: untranslated marker in ${key}`);
  assert.ok(key.includes('\n')||!messages[key].includes('\n'),`${locale}: unrelated text in ${key}`);
  assert.ok(/[\p{L}\p{N}]/u.test(messages[key]),`${locale}: empty translated meaning in ${key}`);
 }
 for(const feature of catalog)for(const key of [feature.title,feature.description,feature.requirement].filter(Boolean))assert.ok(messages[key],`${locale}: ${feature.id}`);
 for(const name of ['Zadex','WordPress','WooCommerce','Stripe','PayPal'])if(messages[name])assert.equal(messages[name],name,`${locale}: brand ${name}`);
}
const server=await createServer({server:{middlewareMode:true},appType:'custom'});
try{
 const {setLocale,t}=await server.ssrLoadModule('/src/i18n.js');
 const {default:Simulation}=await server.ssrLoadModule('/src/components/FeatureSimulation.vue');
 const {default:App}=await server.ssrLoadModule('/src/App.vue');
 for(const locale of locales){
  await setLocale(locale,{persist:false});
  const page=await renderToString(createSSRApp(App));
  assert.ok(page.includes(t('Your ideas.')),`${locale}: hero`);
  assert.ok(page.includes('value="'+locale+'" selected'),`${locale}: language selection`);
  for(const feature of catalog){const html=await renderToString(createSSRApp(Simulation,{feature,replay:0}));assert.ok(html.includes('sim-walkthrough'),`${locale}: ${feature.id}`);assert.ok(!html.includes('undefined'),`${locale}: ${feature.id} unresolved content`);}
  const sample=catalog.find(x=>x.kind==='conditions');const html=await renderToString(createSSRApp(Simulation,{feature:sample,replay:0}));assert.ok(html.includes('value="Visitor"'),`${locale}: demo enums must remain stable`);
 }
 console.log(`Verified ${locales.length} locales, ${Object.keys(source).length} messages each, parameters, brand names, language selection and ${catalog.length*locales.length} rendered simulations.`);
}finally{await server.close();}
