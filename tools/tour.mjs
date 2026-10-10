import { chromium } from 'playwright';
const [,, url, prefix, w='1440', h='900', step='800', introShot='0'] = process.argv;
const b = await chromium.launch({executablePath:'/opt/pw-browsers/chromium'});
const p = await b.newPage({viewport:{width:+w,height:+h}, deviceScaleFactor:1});
p.on('console',m=>{ if(['error','warning'].includes(m.type())) console.log('['+m.type()+']',m.text().slice(0,200)); });
p.on('pageerror',e=>console.log('[pageerror]',e.message.slice(0,200)));
p.on('requestfailed',r=>console.log('[failed]',r.url().slice(0,120)));
await p.goto(url,{waitUntil:'load'});
if(introShot==='1'){ await p.waitForTimeout(900); await p.screenshot({path:prefix+'-intro.png'}); }
await p.waitForTimeout(5200);
const H = await p.evaluate(()=>document.documentElement.scrollHeight);
const ov = await p.evaluate(()=>document.documentElement.scrollWidth - window.innerWidth);
console.log('height',H,'overflowX',ov);
if(process.env.PRE){ for(let y=0;y<H;y+=500){ await p.evaluate(y=>window.scrollTo({top:y,behavior:'instant'}),y); await p.waitForTimeout(350);} await p.waitForTimeout(1500); await p.evaluate(()=>window.scrollTo({top:0,behavior:'instant'})); await p.waitForTimeout(600);}
let i=0;
for(let y=0;y<H;y+=+step){ await p.evaluate(y=>window.scrollTo({top:y,behavior:'instant'}),y); await p.waitForTimeout(1300); await p.screenshot({path:`${prefix}-${String(i).padStart(2,'0')}.png`}); i++; }
await b.close();
