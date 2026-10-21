import assert from 'node:assert/strict';
import fs from 'node:fs';
import {createServer} from 'vite';
import {createSSRApp} from 'vue';
import {renderToString} from 'vue/server-renderer';
import {guides} from '../src/docs/guides.js';
const reference=JSON.parse(fs.readFileSync('src/docs/reference.json','utf8'));
const source=JSON.parse(fs.readFileSync('src/docs/locales/en.json','utf8'));
const ids=new Set([...guides,...reference.widgets].map(x=>x.id));
assert.equal(guides.length,30);assert.equal(reference.widgets.length,102);assert.equal(ids.size,132);
for(const guide of guides){assert.ok(guide.sections.length,guide.id);assert.equal(new Set(guide.sections.map(x=>x.id)).size,guide.sections.length,guide.id+' section IDs');for(const related of guide.related)assert.ok(ids.has(related),guide.id+' related link '+related);}
for(const widget of reference.widgets){assert.equal(new Set(widget.controls.map(x=>x.id)).size,widget.controls.length,widget.id+' control IDs');for(const control of widget.controls)assert.ok(['content','style'].includes(control.tab),widget.id+' control tab');}
for(const locale of ['en','ar']){const translations=JSON.parse(fs.readFileSync('src/docs/locales/'+locale+'.json','utf8'));for(const key of Object.keys(source))assert.ok(translations[key]?.trim(),`${locale}: missing ${key}`);}
const server=await createServer({server:{middlewareMode:true},appType:'custom'});
const originalLocation=globalThis.location;
try{
 const globalI18n=await server.ssrLoadModule('/src/i18n.js');
 const docsI18n=await server.ssrLoadModule('/src/docs/i18n.js');
 const {default:Docs}=await server.ssrLoadModule('/src/docs/Docs.vue');
 for(const locale of ['en','ar']){
  await globalI18n.setLocale(locale,{persist:false});await docsI18n.loadDocsLocale();
  for(const page of [...guides,...reference.widgets]){
   globalThis.location=new URL('http://localhost/docs/?lang='+locale+'#'+page.id);
   const html=await renderToString(createSSRApp(Docs));
   assert.ok(html.includes('docs-article-heading'),`${locale}: ${page.id} article`);
   assert.ok(!html.includes('undefined'),`${locale}: ${page.id} unresolved text`);
   if(page.controls){const rows=[...html.matchAll(/<th scope="row">/g)];assert.equal(rows.length,page.controls.length,`${locale}: ${page.id} complete controls`);}
   else for(const section of page.sections)assert.ok(html.includes('id="doc-'+section.id+'"'),`${locale}: ${page.id}/${section.id}`);
  }
  globalThis.location=new URL('http://localhost/docs/#unknown-guide');const missing=await renderToString(createSSRApp(Docs));assert.ok(missing.includes(docsI18n.d('This documentation page was not found.')));
 }
 console.log(`Verified 30 guides, 102 elements, ${reference.widgets.reduce((n,w)=>n+w.controls.length,0)} controls, ${Object.keys(source).length} bilingual messages, related/deep links, not-found state and 264 rendered documentation pages.`);
}finally{globalThis.location=originalLocation;await server.close();}
