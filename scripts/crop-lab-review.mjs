import fs from 'node:fs';
import {spawnSync} from 'node:child_process';
for(const size of ['desktop','mobile']){
 const box=JSON.parse(fs.readFileSync(`.impeccable/review/feature-${size}-box.json`,'utf8'));
 const result=spawnSync('ffmpeg',['-hide_banner','-loglevel','error','-y','-i',`.impeccable/review/feature-full-${size}.jpg`,'-vf',`crop=iw:${box.height}:0:${box.y}`,'-frames:v','1',`.impeccable/review/feature-${size}.jpg`],{stdio:'inherit'});
 if(result.status!==0)process.exit(result.status);
}
