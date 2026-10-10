import { chromium } from 'playwright';
const b = await chromium.launch({executablePath:'/opt/pw-browsers/chromium'});
const p = await b.newPage({viewport:{width:1440,height:900}});
p.on('pageerror',e=>console.log('[pageerror]',e.message));
await p.goto('http://localhost:8765/contato.html'); await p.waitForTimeout(2500);
await p.click('#form-contato button[type=submit]'); await p.waitForTimeout(300); console.log('1:', await p.$eval('#form-msg',e=>e.textContent));
await p.fill('#f-nome','Teste'); await p.fill('#f-num','22999'); await p.selectOption('#f-bairro',{index:5}); await p.selectOption('#f-assunto',{index:2}); await p.fill('#f-msg','oi');
await p.click('#form-contato button[type=submit]'); await p.waitForTimeout(300); console.log('2:', await p.$eval('#form-msg',e=>e.textContent));
console.log('bairros:', await p.$$eval('#f-bairro option',e=>e.length));
for (const u of ['index','minha-historia','fl360','peneira','noticias','contato']) { await p.goto(`http://localhost:8765/${u}.html`); await p.waitForTimeout(600); const bad = await p.$$eval('a[href]',as=>as.map(a=>a.getAttribute('href')).filter(h=>h==='#'||h==='')); if(bad.length) console.log(u,'links vazios',bad.length); }
await b.close();
