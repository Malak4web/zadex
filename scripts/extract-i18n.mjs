import fs from 'node:fs';
import {parse as parseSfc} from '@vue/compiler-sfc';
import {parse as parseTemplate} from '@vue/compiler-dom';
import {parse} from '@babel/parser';
const messages=new Set();
function add(value){if(typeof value==='string'&&/[A-Za-z]{2}/.test(value)&&!value.startsWith('<')&&!/^(https?:|\.\.?\/|#[a-z])/i.test(value))messages.add(value.trim());}
function js(node){if(!node||typeof node!=='object')return;if(node.type==='StringLiteral')add(node.value);for(const value of Object.values(node))if(Array.isArray(value))value.forEach(js);else if(value&&typeof value==='object')js(value);}
function expression(value){try{js(parse('('+value+')',{sourceType:'module'}));}catch{}}
function template(node){if(node.type===5)expression(node.content.content);node.props?.forEach(p=>{if(p.exp)expression(p.exp.content);});node.children?.forEach(template);}
for(const path of ['src/App.vue','src/components/HeroCanvas.vue','src/components/FeatureBento.vue','src/components/FeatureLibrary.vue','src/components/FeatureSimulation.vue']){const {descriptor}=parseSfc(fs.readFileSync(path,'utf8'));js(parse(descriptor.scriptSetup.content,{sourceType:'module'}));template(parseTemplate(descriptor.template.content));}
js(parse(fs.readFileSync('src/components/simulationKinds.js','utf8'),{sourceType:'module'}));
const data=JSON.parse(fs.readFileSync('src/data/features.json','utf8'));Object.values(data.groups).forEach(add);for(const x of data.catalog)[x.title,x.description,x.subcategory,x.requirement].forEach(add);
['Language','Changing language…','Unable to load this language. Please try again.','Zadex — Your ideas, made real.','A free visual builder for WordPress, with the freedom to make every detail yours.'].forEach(add);
fs.writeFileSync('src/locales/en.json',JSON.stringify(Object.fromEntries([...messages].sort().map(x=>[x,x])),null,2)+'\n');console.log(messages.size+' source messages.');

