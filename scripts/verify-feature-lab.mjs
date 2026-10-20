import assert from 'node:assert/strict';
import fs from 'node:fs';
import {createServer} from 'vite';
import {createSSRApp} from 'vue';
import {renderToString} from 'vue/server-renderer';
import {simulationKinds,sceneLabels} from '../src/components/simulationKinds.js';
const data=JSON.parse(fs.readFileSync('src/data/features.json','utf8'));
assert.equal(data.catalog.filter(x=>x.widget).length,102,'Every public widget must be represented');
assert.equal(new Set(data.catalog.map(x=>x.id)).size,data.catalog.length,'Feature IDs must be unique');
for(const feature of data.catalog){assert.ok(data.groups[feature.group],feature.id+' category');assert.ok(simulationKinds.includes(feature.kind),feature.id+' simulation family');assert.ok(feature.title&&feature.description,feature.id+' description');}
for(const kind of simulationKinds){assert.equal(sceneLabels[kind].length,3,kind+' walkthrough');assert.ok(data.catalog.some(x=>x.kind===kind),kind+' coverage');}
const server=await createServer({server:{middlewareMode:true},appType:'custom'});
try{
 const {default:Simulation}=await server.ssrLoadModule('/src/components/FeatureSimulation.vue');
 for(const feature of data.catalog){const html=await renderToString(createSSRApp(Simulation,{feature,replay:0}));assert.ok(html.includes('sim-sheet')||html.includes('sim-device-sheet'),feature.id+' must render an actual preview');assert.ok(html.includes('sim-walkthrough'),feature.id+' needs an operable walkthrough');assert.ok(!html.includes('undefined'),feature.id+' has unresolved demo content');}
 console.log(`Verified ${data.catalog.length} rendered simulations, ${simulationKinds.length} families, 102 widgets, unique IDs, categories and guided steps.`);
}finally{await server.close();}
