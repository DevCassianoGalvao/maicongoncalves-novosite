(function () {
  'use strict';
  var doc = document, html = doc.documentElement;
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var G = window.gsap, ST = window.ScrollTrigger;
  var anim = !!G && !reduce;
  if (G && ST) G.registerPlugin(ST);
  var $ = function (s, r) { return (r || doc).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || doc).querySelectorAll(s)); };

  /* aviso curto */
  var toastEl = $('#toast'), toastT;
  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg; toastEl.classList.add('is-on');
    clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('is-on'); }, 2600);
  }

  var BASE = html.getAttribute('data-base') || '';

  /* links internos: pular a abertura na próxima página */
  doc.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a');
    if (!a || a.target === '_blank' || a.host !== location.host) return;
    try { sessionStorage.setItem('mgNav', '1'); } catch (err) {}
  });

  /* copiar */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-copy') || location.href;
      var ok = function () { toast('Copiado.'); };
      try { navigator.clipboard.writeText(t).then(ok, function () { toast('Selecione e copie o texto.'); }); } catch (err) { toast('Selecione e copie o texto.'); }
    });
  });

  /* menu do celular */
  var mm = $('#mm'), burger = $('#burger'), mmx = $('#mmx');
  function menu(open) {
    if (!mm) return;
    mm.classList.toggle('is-open', open); mm.setAttribute('aria-hidden', open ? 'false' : 'true');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    doc.body.style.overflow = open ? 'hidden' : '';
    if (open) mmx.focus(); else burger.focus();
  }
  if (burger) {
    burger.addEventListener('click', function () { menu(true); });
    mmx.addEventListener('click', function () { menu(false); });
    $$('a', mm).forEach(function (a) { a.addEventListener('click', function () { menu(false); }); });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape' && mm.classList.contains('is-open')) menu(false); });
  }

  /* cabeçalho: fixo sempre, só muda o fundo ao descer */
  var hd = $('#hd');
  addEventListener('scroll', function () {
    var y = scrollY;
    if (hd) hd.classList.toggle('is-solid', y > 40);
    var p = $('#progress');
    if (p) { var h = html.scrollHeight - innerHeight; p.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')'; }
  }, { passive: true });

  /* pausa as ilustrações fora da tela */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) { es.forEach(function (e) { e.target.classList.toggle('paused', !e.isIntersecting); }); }, { rootMargin: '80px' });
    $$('.pauta-art,.hub,.prog-vis,.ft-top').forEach(function (el) { io.observe(el); });
  }

  /* divide títulos em palavras */
  function split(el) {
    var out = [];
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          var frag = doc.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(doc.createTextNode(' ')); return; }
            var w = doc.createElement('span'); w.className = 'w';
            var i = doc.createElement('span'); i.className = 'wi'; i.textContent = part;
            w.appendChild(i); frag.appendChild(w); out.push(i);
          });
          node.replaceChild(frag, n);
        } else if (n.nodeType === 1 && n.tagName.toLowerCase() !== 'svg') walk(n);
      });
    })(el);
    return out;
  }

  /* fita dos bairros */
  function ribbon() {
    var tp = $('.rb-text textPath'); if (!tp) return;
    var unit = tp.getAttribute('data-unit'); var L = 0;
    try { tp.textContent = unit; L = tp.getComputedTextLength(); } catch (e) {}
    tp.textContent = unit + unit + unit + unit;
    if (anim && L > 0) G.fromTo(tp, { attr: { startOffset: 0 } }, { attr: { startOffset: -L }, duration: L / 46, ease: 'none', repeat: -1 });
  }

  function heroIn(delay) {
    var h1 = $('.hero .h1, .ph-hero .h1, .art-hd h1'); if (!h1 || !anim) return;
    var words = h1.__words || [];
    var tl = G.timeline({ delay: delay || 0, defaults: { ease: 'expo.out' } });
    tl.from(words, { yPercent: 115, duration: 1.1, stagger: .07 }, 0)
      .from('[data-h]', { y: 28, autoAlpha: 0, duration: .9, stagger: .09 }, .25);
    if ($('.scr')) tl.fromTo('.scr svg:not(.spark)', { clipPath: 'inset(-40% 100% -40% 0)' }, { clipPath: 'inset(-40% 0% -40% 0)', duration: .8, ease: 'power2.inOut' }, .85)
      .from('.scr .spark path', { scale: 0, transformOrigin: '50% 100%', duration: .5, stagger: .08, ease: 'back.out(3)' }, 1.45);
    var dr = $$('.draw path');
    if (dr.length) tl.fromTo(dr, { strokeDasharray: 1, strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 1.2, stagger: .12, ease: 'power2.inOut' }, .7);
    var band = $('.rb-band');
    if (band) {
      var len = 2200; try { len = $('#rb').getTotalLength(); } catch (e) {}
      tl.fromTo(band, { strokeDasharray: len, strokeDashoffset: len }, { strokeDashoffset: 0, duration: 1.5, ease: 'power3.inOut' }, .3)
        .from('.rb-text', { autoAlpha: 0, duration: .6 }, 1.3);
    }
    var ph = $$('.stage .ph');
    if (ph.length) tl.from(ph, { y: 120, autoAlpha: 0, rotation: function (i) { return [8, -16, 18][i] || 0; }, scale: .8, duration: 1.3, stagger: .12 }, .35)
      .from('.stk', { scale: 0, autoAlpha: 0, duration: .7, stagger: .1, ease: 'back.out(2.2)' }, 1.2);
    tl.from('#hd .hd-bar', { y: -90, autoAlpha: 0, duration: 1 }, .2);
  }

  function boot() {
    /* só os títulos da primeira seção são divididos (animação de abertura) */
    $$('[data-split]').forEach(function (el) { if (el.closest('.hero,.ph-hero,.art-hd')) el.__words = split(el); });
    ribbon();

    /* abertura com o logotipo */
    var intro = $('#intro');
    if (html.classList.contains('has-intro') && intro && anim) {
      var done = function () { html.classList.remove('has-intro'); if (ST) ST.refresh(); };
      G.timeline({ defaults: { ease: 'expo.inOut' } })
        .fromTo('.intro-logo', { clipPath: 'inset(0 100% 0 0)', x: -30 }, { clipPath: 'inset(0 0% 0 0)', x: 0, duration: 1 })
        .fromTo('.intro-bar i', { scaleX: 0 }, { scaleX: 1, duration: 1.1 }, .15)
        .to('.intro-in', { y: -40, autoAlpha: 0, duration: .6, ease: 'power3.in' }, 1.45)
        .to(intro, { yPercent: -100, duration: 1 }, 1.65)
        .add(done)
        .add(function () { heroIn(0); }, 1.85);
    } else { html.classList.remove('has-intro'); heroIn(.1); }

    if (!anim) return;

    if (ST) {
      /* fotos do hero se afastam ao rolar */
      var stage = $('#stage');
      if (stage) {
        $$('.ph', stage).forEach(function (p, i) {
          G.to(p, { yPercent: [6, -14, -22][i], ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: .6 } });
        });
      }
      /* parallax das imagens */
      $$('[data-plx]').forEach(function (img) {
        G.fromTo(img, { yPercent: -9 }, { yPercent: 0, ease: 'none', scrollTrigger: { trigger: img.parentNode, start: 'top bottom', end: 'bottom top', scrub: true } });
      });
      /* linha do tempo */
      var tl = $('#tl');
      if (tl) {
        G.fromTo('.tl-line i', { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: tl, start: 'top 70%', end: 'bottom 75%', scrub: .4 } });
        $$('.tl-dot', tl).forEach(function (d) { ST.create({ trigger: d, start: 'top 70%', onEnter: function () { d.classList.add('is-on'); }, onLeaveBack: function () { d.classList.remove('is-on'); } }); });
      }
      /* números que contam */
      $$('[data-count]').forEach(function (el) {
        var end = +el.getAttribute('data-count'), pre = el.getAttribute('data-prefix') || '', o = { v: 0 };
        G.to(o, { v: end, duration: 1.6, ease: 'power2.out', scrollTrigger: { trigger: el, start: 'top 90%', once: true }, onUpdate: function () { el.textContent = pre + Math.round(o.v); } });
      });
      addEventListener('load', function () { ST.refresh(); });
    }

    /* microinterações de ponteiro */
    if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
      $$('[data-mag]').forEach(function (b) {
        var qx = G.quickTo(b, 'x', { duration: .5, ease: 'power3.out' }), qy = G.quickTo(b, 'y', { duration: .5, ease: 'power3.out' });
        b.addEventListener('pointermove', function (e) { var r = b.getBoundingClientRect(); qx((e.clientX - r.left - r.width / 2) * .22); qy((e.clientY - r.top - r.height / 2) * .3); });
        b.addEventListener('pointerleave', function () { qx(0); qy(0); });
      });
      $$('[data-spot]').forEach(function (c) {
        c.addEventListener('pointermove', function (e) { var r = c.getBoundingClientRect(); c.style.setProperty('--mx', (e.clientX - r.left) + 'px'); c.style.setProperty('--my', (e.clientY - r.top) + 'px'); });
      });
      var st = $('#stage');
      if (st) {
        var movers = $$('.ph-in img', st).map(function (el) { return { d: +el.closest('.ph').getAttribute('data-depth') || 10, x: G.quickTo(el, 'x', { duration: .9, ease: 'power3.out' }), y: G.quickTo(el, 'y', { duration: .9, ease: 'power3.out' }) }; });
        $('.hero').addEventListener('pointermove', function (e) {
          var nx = e.clientX / innerWidth - .5, ny = e.clientY / innerHeight - .5;
          movers.forEach(function (m) { m.x(-nx * m.d); m.y(-ny * m.d); });
        });
      }
      $$('.pauta').forEach(function (c) {
        var art = $('.pauta-art', c); if (!art) return;
        var rx = G.quickTo(art, 'x', { duration: .6 }), ry = G.quickTo(art, 'y', { duration: .6 });
        c.addEventListener('pointermove', function (e) { var r = c.getBoundingClientRect(); rx((e.clientX - r.left - r.width / 2) * -.03); ry((e.clientY - r.top - r.height / 2) * -.04); });
        c.addEventListener('pointerleave', function () { rx(0); ry(0); });
      });
    }
  }

  /* formulário de contato: fotos, localização e envio */
  var form = $('#form-contato');
  if (form) {
    var msg = $('#form-msg'), send = $('#f-send'), fileIn = $('#f-fotos'), ups = $('#ups'), drop = $('#up-drop');
    var photos = [], MAXF = 6;
    var X = '<svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208.49 191.51a12 12 0 0 1-17 17L128 145l-63.51 63.49a12 12 0 0 1-17-17L111 128 47.51 64.49a12 12 0 0 1 17-17L128 111l63.51-63.52a12 12 0 0 1 17 17L145 128Z"/></svg>';
    $('#f-t').value = Date.now();
    var say = function (text, ok) { msg.hidden = false; msg.textContent = text; msg.classList.toggle('is-ok', !!ok); };
    var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim(); };

    /* máscaras */
    $$('[data-mask]', form).forEach(function (inp) {
      inp.addEventListener('input', function () {
        var d = inp.value.replace(/\D/g, '');
        if (inp.getAttribute('data-mask') === 'date') {
          d = d.slice(0, 8);
          inp.value = d.length > 4 ? d.slice(0, 2) + '/' + d.slice(2, 4) + '/' + d.slice(4) : d.length > 2 ? d.slice(0, 2) + '/' + d.slice(2) : d;
        } else {
          d = d.slice(0, 11);
          inp.value = d.length > 10 ? '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7)
            : d.length > 6 ? '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6)
            : d.length > 2 ? '(' + d.slice(0, 2) + ') ' + d.slice(2) : d.length ? '(' + d : '';
        }
      });
    });
    $$('input,select,textarea', form).forEach(function (f) { f.addEventListener('input', function () { f.classList.remove('is-bad'); }); });

    /* fotos: miniaturas, remover, reduzir antes de enviar */
    var drawUps = function () {
      ups.innerHTML = '';
      photos.forEach(function (p, i) {
        var d = doc.createElement('div'); d.className = 'up';
        d.innerHTML = '<img alt="Foto ' + (i + 1) + '"><button type="button" aria-label="Remover foto ' + (i + 1) + '">' + X + '</button>';
        $('img', d).src = p.url;
        $('button', d).addEventListener('click', function () { URL.revokeObjectURL(p.url); photos.splice(i, 1); drawUps(); });
        ups.appendChild(d);
      });
      $('span', drop).textContent = photos.length ? (photos.length >= MAXF ? 'Limite de ' + MAXF + ' fotos' : 'Adicionar mais fotos (' + photos.length + '/' + MAXF + ')') : 'Toque para tirar ou escolher fotos';
    };
    fileIn.addEventListener('change', function () {
      Array.prototype.forEach.call(fileIn.files, function (f) {
        if (photos.length >= MAXF) { toast('Máximo de ' + MAXF + ' fotos.'); return; }
        if (!/^image\//.test(f.type) && !/\.(jpe?g|png|webp|heic|heif|gif)$/i.test(f.name)) { toast(f.name + ' não é uma foto.'); return; }
        photos.push({ file: f, url: URL.createObjectURL(f) });
      });
      fileIn.value = ''; drawUps();
    });
    ['dragenter', 'dragover'].forEach(function (t) { drop.addEventListener(t, function () { drop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (t) { drop.addEventListener(t, function () { drop.classList.remove('is-over'); }); });
    var shrink = function (f) {
      return new Promise(function (res) {
        if (f.type === 'image/gif') return res(f);
        var img = new Image(), u = URL.createObjectURL(f);
        img.onload = function () {
          var k = Math.min(1, 1920 / Math.max(img.naturalWidth, img.naturalHeight));
          var c = doc.createElement('canvas'); c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
          c.getContext('2d').drawImage(img, 0, 0, c.width, c.height); URL.revokeObjectURL(u);
          c.toBlob(function (b) { res(b ? new File([b], (f.name || 'foto').replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }) : f); }, 'image/jpeg', 0.82);
        };
        img.onerror = function () { URL.revokeObjectURL(u); res(f); };
        img.src = u;
      });
    };

    /* localização: GPS do aparelho + mapa com pino ajustável + endereço pelo OpenStreetMap */
    var geoBtn = $('#geo'), geoBox = $('#geo-box'), geoSt = $('#geo-st'), rua = $('#f-rua'), bairro = $('#f-bairro');
    var map, pin, ring, watch, best, geoTimer, ruaAuto = false, lastAddr = '';
    var setHidden = function (lat, lng, acc, manual) {
      $('#geo-lat').value = lat ? lat.toFixed(6) : ''; $('#geo-lng').value = lng ? lng.toFixed(6) : '';
      $('#geo-acc').value = acc ? Math.round(acc) : ''; $('#geo-manual').value = manual ? '1' : '';
    };
    var status = function (h) { geoSt.innerHTML = h; };
    var esc = function (t) { var d = doc.createElement('div'); d.textContent = t; return d.innerHTML; };
    var loadLeaflet = function () {
      if (window.L) return Promise.resolve();
      return new Promise(function (res, rej) {
        var css = doc.createElement('link'); css.rel = 'stylesheet'; css.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css'; doc.head.appendChild(css);
        var js = doc.createElement('script'); js.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js'; js.onload = res; js.onerror = rej; doc.head.appendChild(js);
      });
    };
    var describe = function (acc, manual) {
      var where = lastAddr ? '<b>' + esc(lastAddr) + '</b><br>' : '';
      var how = manual ? 'Ponto ajustado por você.' : acc > 60 ? '<span class="warn">Precisão baixa (±' + Math.round(acc) + ' m). Arraste o pino até o local exato.</span>' : 'Precisão de ±' + Math.round(acc) + ' m. Se precisar, arraste o pino para ajustar.';
      status(where + how);
    };
    var reverse = function (lat, lng, acc, manual) {
      fetch(BASE + '/api/geocode.php?lat=' + lat.toFixed(6) + '&lng=' + lng.toFixed(6))
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!j.ok) { lastAddr = ''; $('#geo-addr').value = ''; describe(acc, manual); return; }
          lastAddr = j.label; $('#geo-addr').value = j.label;
          if (j.line && (ruaAuto || !rua.value.trim())) { rua.value = j.line; ruaAuto = true; }
          var cands = (j.candidates || []).map(norm);
          if (cands.length) {
            var hit = Array.prototype.filter.call(bairro.options, function (o) { return o.value && cands.indexOf(norm(o.value)) > -1; })[0];
            if (hit && (!bairro.value || bairro.getAttribute('data-auto'))) { bairro.value = hit.value; bairro.setAttribute('data-auto', '1'); bairro.classList.remove('is-bad'); }
          }
          describe(acc, manual);
        })
        .catch(function () { describe(acc, manual); });
    };
    var place = function (lat, lng, acc, manual) {
      setHidden(lat, lng, acc, manual);
      if (window.L && map) {
        var ll = [lat, lng];
        if (!pin) {
          var icon = L.divIcon({ className: '', iconSize: [34, 44], iconAnchor: [17, 42], html: '<svg class="geo-pin" viewBox="0 0 34 44"><path d="M17 1C8.2 1 1 8 1 16.7 1 28.4 17 43 17 43s16-14.6 16-26.3C33 8 25.8 1 17 1z" fill="#FE6500" stroke="#fff" stroke-width="2"/><circle cx="17" cy="16.5" r="6" fill="#fff"/></svg>' });
          pin = L.marker(ll, { draggable: true, icon: icon, keyboard: true, title: 'Arraste para ajustar' }).addTo(map);
          pin.on('dragend', function () { var p = pin.getLatLng(); if (ring) ring.setRadius(0); stopWatch(); place(p.lat, p.lng, 0, true); reverse(p.lat, p.lng, 0, true); });
          map.on('click', function (ev) { pin.setLatLng(ev.latlng); if (ring) ring.setRadius(0); stopWatch(); place(ev.latlng.lat, ev.latlng.lng, 0, true); reverse(ev.latlng.lat, ev.latlng.lng, 0, true); });
          ring = L.circle(ll, { radius: acc || 0, color: '#0144C9', weight: 1, fillOpacity: .08 }).addTo(map);
        } else { pin.setLatLng(ll); if (!manual) ring.setLatLng(ll).setRadius(acc || 0); }
        if (!manual) map.setView(ll, acc > 150 ? 16 : 18);
      }
    };
    var stopWatch = function () { if (watch != null) { navigator.geolocation.clearWatch(watch); watch = null; } clearTimeout(geoTimer); geoBtn.disabled = false; geoBtn.classList.remove('is-busy'); };
    var finish = function () { if (!geoBtn.classList.contains('is-busy')) return; stopWatch(); if (best) reverse(best.lat, best.lng, best.acc, false); };
    geoBtn.addEventListener('click', function () {
      if (!navigator.geolocation) { toast('Seu navegador não permite usar a localização.'); return; }
      if (!window.isSecureContext) { toast('A localização só funciona com o site em https.'); return; }
      geoBtn.disabled = true; geoBtn.classList.add('is-busy'); best = null;
      geoBox.hidden = false; status('Buscando sua localização… Se o navegador perguntar, toque em <b>Permitir</b>.');
      loadLeaflet().then(function () {
        if (!map) {
          map = L.map('geo-map', { zoomControl: true, attributionControl: true }).setView([-22.2819, -42.5311], 13);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        }
        setTimeout(function () { map.invalidateSize(); }, 50);
        if (best) place(best.lat, best.lng, best.acc, false);
      }).catch(function () { $('#geo-map').hidden = true; });
      // Lê o GPS por alguns segundos e fica com a leitura mais precisa
      watch = navigator.geolocation.watchPosition(function (pos) {
        var c = pos.coords;
        if (!best || c.accuracy < best.acc) {
          best = { lat: c.latitude, lng: c.longitude, acc: c.accuracy };
          place(best.lat, best.lng, best.acc, false);
          status('Melhorando a precisão… ±' + Math.round(best.acc) + ' m');
        }
        if (best.acc <= 15) finish();
      }, function (err) {
        if (best) { finish(); return; }
        stopWatch();
        geoBox.hidden = true;
        toast(err.code === 1 ? 'Permita o acesso à localização nas configurações do navegador.' : 'Não foi possível pegar sua localização. Digite o endereço.');
      }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
      geoTimer = setTimeout(finish, 9000);
    });
    $('#geo-x').addEventListener('click', function () {
      stopWatch(); best = null; setHidden(); $('#geo-addr').value = ''; lastAddr = '';
      if (ruaAuto) { rua.value = ''; ruaAuto = false; }
      if (pin) { map.removeLayer(pin); map.removeLayer(ring); pin = ring = null; }
      geoBox.hidden = true;
    });
    rua.addEventListener('input', function () { ruaAuto = false; });
    bairro.addEventListener('change', function () { bairro.removeAttribute('data-auto'); });

    /* envio */
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var miss = $$('[required]', form).filter(function (f) { return !f.value.trim(); });
      miss.forEach(function (f) { f.classList.add('is-bad'); });
      if (miss.length) { say('Preencha os campos que faltam: ' + miss.map(function (f) { return $('label[for="' + f.id + '"]', form).textContent; }).join(', ') + '.'); miss[0].focus(); return; }
      if ($('#f-num').value.replace(/\D/g, '').length < 10) { $('#f-num').classList.add('is-bad'); say('Confira o número de telefone/WhatsApp com DDD.'); $('#f-num').focus(); return; }
      send.classList.add('is-busy'); $('span', send).textContent = photos.length ? 'ENVIANDO FOTOS…' : 'ENVIANDO…'; msg.hidden = true;
      Promise.all(photos.map(function (p) { return shrink(p.file); })).then(function (files) {
        var fd = new FormData(form);
        files.forEach(function (f) { fd.append('fotos[]', f, f.name); });
        return fetch(form.getAttribute('action'), { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Erro no servidor (' + r.status + ').' }; }); });
      }).then(function (j) {
        if (j.ok) {
          say(j.message, true); form.reset(); $('#f-t').value = Date.now();
          photos.forEach(function (p) { URL.revokeObjectURL(p.url); }); photos = []; drawUps();
          $('#geo-x').click(); ruaAuto = false;
          msg.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
        } else {
          say(j.error || 'Não foi possível enviar. Tente de novo.');
          if (j.field) { var f = $('[name="' + j.field + '"]', form); if (f) { f.classList.add('is-bad'); f.focus(); } }
        }
      }).catch(function () {
        say('Sem conexão no momento. Verifique a internet e tente de novo.');
      }).then(function () { send.classList.remove('is-busy'); $('span', send).textContent = 'ENVIAR'; });
    });
  }

  /* lista de notícias: filtro, busca e paginação */
  var grid = $('#lista');
  if (grid) {
    var cards = $$('.card', grid), empty = $('#vazio'), q = $('#busca'), pager = $('#pager'), page = 1;
    var pressed = $('.fl[aria-pressed="true"]'), cat = pressed ? pressed.getAttribute('data-cat') || '' : '';
    var per = +grid.getAttribute('data-per') || 6;
    var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); };
    var icon = function (d) { return '<svg class="ic" viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>'; };
    var apply = function (animate) {
      var term = norm(q.value);
      var match = cards.filter(function (c) { return (!cat || c.getAttribute('data-cat') === cat) && (!term || norm(c.getAttribute('data-q')).indexOf(term) > -1); });
      var pages = Math.max(1, Math.ceil(match.length / per)); page = Math.min(Math.max(1, page), pages);
      var shown = match.slice((page - 1) * per, page * per);
      cards.forEach(function (c) { c.hidden = shown.indexOf(c) < 0; });
      empty.hidden = match.length > 0;
      pager.hidden = match.length === 0;
      var h = '<button class="pg" data-go="' + (page - 1) + '" aria-label="Página anterior"' + (page === 1 ? ' disabled' : '') + '>' + icon('M160 208L80 128l80-80') + '</button>';
      for (var i = 1; i <= pages; i++) h += '<button class="pg" data-go="' + i + '"' + (i === page ? ' aria-current="page"' : '') + ' aria-label="Página ' + i + '">' + i + '</button>';
      h += '<button class="pg" data-go="' + (page + 1) + '" aria-label="Próxima página"' + (page === pages ? ' disabled' : '') + '>' + icon('M96 48l80 80-80 80') + '</button>';
      pager.innerHTML = h;
      if (anim && animate !== false) G.fromTo(shown, { y: 24, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .5, stagger: .06, ease: 'power3.out', overwrite: true });
    };
    pager.addEventListener('click', function (e) {
      var b = e.target.closest('.pg'); if (!b || b.disabled) return;
      page = +b.getAttribute('data-go'); apply();
      var top = grid.getBoundingClientRect().top + scrollY - 150;
      if (scrollY > top) scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' });
    });
    $$('.fl').forEach(function (b) {
      b.addEventListener('click', function () {
        cat = b.getAttribute('data-cat') || ''; page = 1;
        $$('.fl').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        apply();
      });
    });
    q.addEventListener('input', function () { page = 1; apply(); });
    apply(false);
  }

  /* galeria do artigo */
  var lb = $('#lb');
  if (lb && lb.showModal) {
    var shots = $$('.gal button'), cur = 0, img = $('img', lb);
    var show = function (i) { cur = (i + shots.length) % shots.length; img.src = shots[cur].getAttribute('data-full'); img.alt = $('img', shots[cur]).alt; };
    shots.forEach(function (b, i) { b.addEventListener('click', function () { show(i); lb.showModal(); }); });
    $('.lb-x', lb).addEventListener('click', function () { lb.close(); });
    $('.lb-p', lb).addEventListener('click', function () { show(cur - 1); });
    $('.lb-n', lb).addEventListener('click', function () { show(cur + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.classList.contains('lb-in')) lb.close(); });
    lb.addEventListener('keydown', function (e) { if (e.key === 'ArrowLeft') show(cur - 1); if (e.key === 'ArrowRight') show(cur + 1); });
  }

  var start = function () { try { boot(); } catch (e) { html.classList.remove('has-intro'); if (window.console) console.error(e); } };
  if (doc.fonts && doc.fonts.ready) {
    var went = false, go = function () { if (!went) { went = true; start(); } };
    doc.fonts.ready.then(go); setTimeout(go, 1200);
  } else start();
})();
