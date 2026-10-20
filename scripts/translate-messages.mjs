// Optional build-time bootstrap for public source copy. Never imported by the app.
// Individual requests keep unrelated source messages from contaminating each other.
import fs from 'node:fs';
const source=JSON.parse(fs.readFileSync('src/locales/en.json','utf8'));
const languages=['ar','es','de','ja','fr','pt-BR','it','nl','pl','ru'];
const delay=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const params=text=>(text.match(/\{\w+\}/g)||[]).sort().join();
const invalid=(key,value)=>!value?.trim()||params(key)!==params(value)||(!key.includes('\n')&&value.includes('\n'))||!/[\p{L}\p{N}]/u.test(value);
async function translate(key,locale){
 const tokens=[];const input=key.replace(/\{\w+\}/g,value=>'ZXPH'+(tokens.push(value)-1)+'ZX');
 for(let attempt=0;attempt<4;attempt++)try{
  const response=await fetch('https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl='+locale+'&dt=t&q='+encodeURIComponent(input),{signal:AbortSignal.timeout(30000)});
  if(!response.ok)throw new Error('Translation HTTP '+response.status);
  const result=(await response.json())[0].map(x=>x[0]).join('').replace(/ZXPH\s*(\d+)\s*ZX/g,(_,index)=>tokens[Number(index)]).trim();
  if(invalid(key,result))throw new Error('Translation integrity failed: '+locale+' / '+key);
  return result;
 }catch(error){if(attempt===3)throw error;await delay(1000*(attempt+1));}
}
for(const locale of languages){
 const path=`src/locales/${locale}.json`,messages=fs.existsSync(path)?JSON.parse(fs.readFileSync(path,'utf8')):{};
 const keys=Object.keys(source).filter(key=>invalid(key,messages[key]));let next=0;
 await Promise.all(Array.from({length:3},async()=>{while(next<keys.length){const key=keys[next++];messages[key]=await translate(key,locale);fs.writeFileSync(path,JSON.stringify(messages,null,2)+'\n');}}));
 console.log(locale+': '+keys.length+' source messages translated or repaired.');
}
console.log('Reapply reviewed overrides with: node scripts/i18n-editorial.mjs');
