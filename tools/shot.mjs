import { chromium } from 'playwright';
const [,, url, out, w='1440', h='900', full='1', scrollWait='1'] = process.argv;
const b = await chromium.launch({executablePath: process.env.PW_CHROME || undefined});
const p = await b.newPage({viewport:{width:+w,height:+h}});
try { await p.goto(url,{waitUntil:'networkidle',timeout:45000}); } catch(e){ console.log('goto:',e.message.slice(0,80)); }
await p.waitForTimeout(2500);
if (scrollWait==='1'){ const H = await p.evaluate(()=>document.documentElement.scrollHeight);
  for(let y=0;y<H;y+=600){ await p.evaluate(y=>window.scrollTo(0,y),y); await p.waitForTimeout(250);} await p.evaluate(()=>window.scrollTo(0,0)); await p.waitForTimeout(800);}
await p.screenshot({path:out, fullPage: full==='1'});
console.log(out, await p.evaluate(()=>document.documentElement.scrollHeight));
await b.close();
