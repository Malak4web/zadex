import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {arabic,guides,ui} from '../src/docs/guides.js';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const plugin='C:/Users/DANTECH/OneDrive/Desktop/plugin zadex';
// Source plugin is read-only: all output locations are fixed beneath this website.
const english={},ar={...arabic};
const siteAr=JSON.parse(fs.readFileSync(path.join(root,'src/locales/ar.json'),'utf8'));
const pluginAr=JSON.parse(fs.readFileSync(path.join(plugin,'languages/zadex-ar.json'),'utf8'));
Object.assign(ar,{pro:'متقدمة · مجانية',H1:'H1',H2:'H2',H3:'H3',H4:'H4',H5:'H5',H6:'H6',p:'p',N:'N',Choose:'اختيار',Switch:'تفعيل أو تعطيل',Repeater:'عناصر قابلة للتكرار',Dimensions:'الأبعاد'});
function message(value){if(typeof value==='string'&&value.trim()&&/[\p{L}]/u.test(value)){english[value]=value;ar[value]??=siteAr[value]||pluginAr[value];}return value;}
Object.keys(ar).forEach(message);
const excluded=new Set(['accordion-item','tab','mega-menu-item','component']);
function controls(items,parent='',tab='content'){
 const result=[];
 for(const control of items||[]){
  if(!control.label)continue;
  const options=Array.isArray(control.options)?control.options.filter(x=>x&&typeof x.label==='string').map(x=>({label:message(x.label),value:x.value})):[];
  const entry={id:parent+control.id,label:message(control.label),type:control.type,tab:control.tab||tab,states:control.states||[],options};
  if(control.description)entry.description=message(control.description);
  if(control.responsive)entry.responsive=true;
  if(control.dynamic===true)entry.dynamic=true;
  if(control.condition)entry.conditional=true;
  if(control.default!==undefined)entry.default=control.default;
  result.push(entry);
  if(control.fields)result.push(...controls(control.fields,parent+control.id+'.',control.tab||tab));
 }
 return result;
}
const widgets=fs.readdirSync(path.join(plugin,'widgets')).filter(name=>!excluded.has(name)&&fs.existsSync(path.join(plugin,'widgets',name,'manifest.json'))).map(name=>{
 const source=JSON.parse(fs.readFileSync(path.join(plugin,'widgets',name,'manifest.json'),'utf8'));
 return {id:'element-'+name,type:name,title:message(source.title),description:message(source.description)||'',category:message(source.category),controls:controls(source.controls),source:`widgets/${name}/manifest.json`};
}).sort((a,b)=>a.title.localeCompare(b.title));
for(const value of ['Text','Number','Choose','Switch','Color','Typography','Image','Gallery','URL','Repeater','Dimensions','Background','Border','Shadow','Code','Icon','Media','Select','Content','Style','Advanced','Layout','Basic','Site','Theme','Media','pro','woocommerce','hover','active','focus'])message(value);
const version=fs.readFileSync(path.join(plugin,'zadex.php'),'utf8').match(/Version:\s*([\d.]+)/)?.[1]||'0.1.1';
const directory=path.join(root,'src/docs/locales');fs.mkdirSync(directory,{recursive:true});
fs.writeFileSync(path.join(root,'src/docs/reference.json'),JSON.stringify({version,reviewed:'2026-10-21',widgets},null,2)+'\n');
fs.writeFileSync(path.join(directory,'en.json'),JSON.stringify(english,null,2)+'\n');
// Preserve already translated reference strings if the read-only import is run again.
const existing=fs.existsSync(path.join(directory,'ar.json'))?JSON.parse(fs.readFileSync(path.join(directory,'ar.json'),'utf8')):{};
fs.writeFileSync(path.join(directory,'ar.json'),JSON.stringify({...existing,...Object.fromEntries(Object.entries(ar).filter(([,value])=>value))},null,2)+'\n');
const labels={en:'Documentation',ar:'التوثيق',es:'Documentación',de:'Dokumentation',ja:'ドキュメント',fr:'Documentation','pt-BR':'Documentação',it:'Documentazione',nl:'Documentatie',pl:'Dokumentacja',ru:'Документация'};
for(const [locale,label] of Object.entries(labels)){const file=path.join(root,`src/locales/${locale}.json`);const messages=JSON.parse(fs.readFileSync(file,'utf8'));messages.Documentation=label;fs.writeFileSync(file,JSON.stringify(messages,null,2)+'\n');}
console.log(`${guides.length} detailed guides; ${widgets.length} public elements; ${widgets.reduce((n,w)=>n+w.controls.length,0)} control rows; ${Object.keys(english).length} documentation messages; source version ${version}.`);
