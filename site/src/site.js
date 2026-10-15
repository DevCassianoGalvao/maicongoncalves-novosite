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

  /* aviso de cookies (LGPD) */
  var ck = $('#ck');
  if (ck) {
    var seen = false;
    try { seen = localStorage.getItem('mg_cookies') === 'ok'; } catch (e) {}
    if (!seen) { ck.hidden = false; document.documentElement.classList.add('ck-on'); }
    $('#ck-ok').addEventListener('click', function () {
      try { localStorage.setItem('mg_cookies', 'ok'); } catch (e) {}
      ck.hidden = true; document.documentElement.classList.remove('ck-on');
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
