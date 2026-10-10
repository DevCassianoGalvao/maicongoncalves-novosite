import { chromium } from 'playwright';
const b = await chromium.launch({executablePath:'/opt/pw-browsers/chromium'});
const p = await b.newPage({viewport:{width:1440,height:900}});
p.on('pageerror',e=>console.log('[pageerror]',p.url(),e.message));
p.on('console',m=>{ if(m.type()==='error' && !/404/.test(m.text())) console.log('[console]',m.text().slice(0,160)); });
for (const u of ['index','minha-historia','fl360','peneira','noticias','contato','noticia-viaduto-recursos']) { await p.goto(`http://localhost:8765/${u}.html`); await p.waitForTimeout(1500); }
await p.goto('http://localhost:8765/noticias.html'); await p.waitForTimeout(2500);
const vis=()=>p.$$eval('#lista .card:not([hidden]) .card-t',e=>e.map(x=>x.textContent.slice(0,28)));
console.log('pág 1:', await vis(), 'botões:', await p.$$eval('#pager .pg',e=>e.map(x=>x.textContent||x.getAttribute('aria-label'))));
await p.click('#pager .pg[data-go="2"]:not([aria-label^="Próx"])'); await p.waitForTimeout(700);
console.log('pág 2:', await vis(), 'atual:', await p.$eval('#pager [aria-current]',e=>e.textContent));
await p.click('.fl[data-cat="Mobilidade"]'); await p.waitForTimeout(500); console.log('filtro:', await vis(), await p.$$eval('#pager .pg',e=>e.length));
await p.evaluate(()=>scrollTo(0,document.body.scrollHeight)); await p.waitForTimeout(600); await p.click('.fl[data-cat=""]'); await p.waitForTimeout(600); await p.evaluate(()=>document.querySelector('#pager').scrollIntoView({block:'center'})); await p.waitForTimeout(500); await p.screenshot({path:'/home/claude/maicon/shots7/pager.png'});
await p.goto('http://localhost:8765/minha-historia.html'); await p.waitForTimeout(2500);
console.log('bolinhas no topo:', await p.$$eval('.tl-dot.is-on',e=>e.length));
await p.evaluate(()=>scrollTo({top:1700,behavior:'instant'})); await p.waitForTimeout(900); console.log('bolinhas no meio:', await p.$$eval('.tl-dot.is-on',e=>e.length)); await p.screenshot({path:'/home/claude/maicon/shots7/tl.png'});
await b.close();
