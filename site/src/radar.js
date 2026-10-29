/* Friburgo no Mapa: mapa de demandas e envio de novas demandas */
(function () {
  'use strict';
  var doc = document, BASE = doc.documentElement.getAttribute('data-base') || '';
  var $ = function (s, r) { return (r || doc).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || doc).querySelectorAll(s)); };
  var CENTER = [-22.2819, -42.5311];
  var ITEMS = window.RD_ITEMS || [];
  var icons = {};
  var tpl = $('#rd-icons');
  if (tpl) $$('span', tpl.content || tpl).forEach(function (s) { icons[s.getAttribute('data-i')] = s.innerHTML; });
  var esc = function (t) { var d = doc.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
  var fmtDate = function (iso) { var d = new Date(iso); return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear(); };
  var span = function (from) {
    var s = Math.max(0, (Date.now() - new Date(from).getTime()) / 1000), d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
    if (d > 0) return d + (d === 1 ? ' dia' : ' dias') + (h ? ', ' + h + ' h' : '');
    if (h > 0) return h + ' h ' + m + ' min';
    return Math.max(1, m) + ' min';
  };
  var pinIcon = function (st) {
    var c = st === 'resolvido' ? '#16A34A' : '#FE6500';
    return L.divIcon({ className: 'rd-pin', iconSize: [30, 40], iconAnchor: [15, 38], tooltipAnchor: [0, -34],
      html: '<svg viewBox="0 0 34 44" width="30" height="40"><path d="M17 1C8.2 1 1 8 1 16.7 1 28.4 17 43 17 43s16-14.6 16-26.3C33 8 25.8 1 17 1z" fill="' + c + '" stroke="#fff" stroke-width="2.5"/><circle cx="17" cy="16.5" r="5.5" fill="#fff"/></svg>' });
  };

  /* ---------------- lightbox ---------------- */
  var lb = $('#rd-lb');
  // galeria: setas, teclado e arrastar para o lado
  var gallery = [], gi = 0;
  var lbShow = function () {
    var img = $('img', lb), many = gallery.length > 1;
    img.src = gallery[gi]; img.alt = 'Foto ' + (gi + 1) + ' de ' + gallery.length;
    $('.lb-p', lb).hidden = $('.lb-n', lb).hidden = $('.lb-c', lb).hidden = !many;
    $('.lb-c', lb).textContent = (gi + 1) + ' / ' + gallery.length;
  };
  var lbGo = function (d) { if (gallery.length < 2) return; gi = (gi + d + gallery.length) % gallery.length; lbShow(); };
  var zoom = function (src) {
    if (!lb || !lb.showModal) { window.open(src, '_blank'); return; }
    gi = gallery.indexOf(src); if (gi < 0) { gallery = [src]; gi = 0; }
    lbShow(); if (!lb.open) lb.showModal();
  };
  if (lb) {
    $('.lb-x', lb).addEventListener('click', function () { lb.close(); });
    $('.lb-p', lb).addEventListener('click', function (e) { e.stopPropagation(); lbGo(-1); });
    $('.lb-n', lb).addEventListener('click', function (e) { e.stopPropagation(); lbGo(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.classList.contains('lb-in')) lb.close(); });
    lb.addEventListener('keydown', function (e) { if (e.key === 'ArrowLeft') lbGo(-1); else if (e.key === 'ArrowRight') lbGo(1); });
    var sx = null, sy = 0;
    lb.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      if (sx === null) return;
      var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy; sx = null;
      if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) lbGo(dx < 0 ? 1 : -1);
    }, { passive: true });
  }

  /* ---------------- mapa público ---------------- */
  var mapEl = $('#rd-map'), card = $('#rd-card');
  var openById = null; // abre o card de uma demanda no mapa (usado pelo aviso de demandas próximas)
  if (mapEl && window.L) {
    var map = L.map(mapEl, { scrollWheelZoom: false, zoomControl: true, tap: true }).setView(CENTER, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    map.on('focus click', function () { map.scrollWheelZoom.enable(); });
    var cluster = L.markerClusterGroup ? L.markerClusterGroup({
      showCoverageOnHover: false, maxClusterRadius: 44,
      iconCreateFunction: function (c) {
        var ms = c.getAllChildMarkers(), wait = ms.filter(function (m) { return m.item.status !== 'resolvido'; }).length;
        return L.divIcon({ className: 'rd-cl' + (wait ? '' : ' rd-cl--ok'), iconSize: [40, 40], html: '<span>' + ms.length + '</span>' });
      }
    }) : L.layerGroup();
    map.addLayer(cluster);
    var markers = [], active = null, st = '', cat = '';
    var canHover = matchMedia('(hover: hover) and (pointer: fine)').matches;
    var mobile = function () { return matchMedia('(max-width: 860px)').matches; };

    var renderCard = function (t) {
      var ph = t.photos || [], vd = t.video, ig = t.insta;
      if (vd && ph.length && ph[0].url === vd.poster) ph = ph.slice(1); // a capa do vídeo não se repete como foto
      var top = ig ? 'ig' : vd ? 'video' : ph.length ? 'photo' : '';
      gallery = ph.map(function (p) { return p.url; }).concat(t.after ? [t.after.url] : []);
      var timer = t.status === 'resolvido'
        ? '<div class="rd-timer rd-timer--ok">' + icons.check + '<div><small>Resolvido em</small><b>' + t.days + (t.days === 1 ? ' dia' : ' dias') + '</b><small>Solução em ' + fmtDate(t.resolvedAt) + '</small></div></div>'
        : '<div class="rd-timer">' + icons.clock + '<div><small>Aguardando a Prefeitura há</small><b data-since="' + esc(t.publishedAt) + '">' + span(t.publishedAt) + '</b></div></div>';
      card.innerHTML =
        '<button type="button" class="rd-card-x" aria-label="Fechar">' + icons.x + '</button>' +
        (ig ? '<a class="rd-ig" href="' + esc(ig.url) + '" target="_blank" rel="noopener" aria-label="Assistir ao vídeo no Instagram">' + (ig.thumb ? '<img src="' + esc(ig.thumb) + '" alt="" loading="lazy">' : '') +
          '<span class="rd-ig-play">' + icons.play + '</span><span class="rd-ig-tag">' + icons.ig + 'Assistir no Instagram</span></a>' : '') +
        (vd ? '<div class="rd-card-vid"><video controls playsinline preload="none"' + (vd.poster ? ' poster="' + esc(vd.poster) + '"' : '') + '><source src="' + esc(vd.url) + '" type="' + esc(vd.mime) + '"></video></div>' : '') +
        (top === 'photo' ? '<button type="button" class="rd-card-ph" data-zoom="' + esc(ph[0].url) + '"><img src="' + esc(ph[0].url) + '" alt="Foto do problema: ' + esc(t.catLabel) + '">' + (ph.length > 1 ? '<span>' + ph.length + ' fotos ' + icons.next + '</span>' : '') + '</button>' : '') +
        (ph.length > (top === 'photo' ? 1 : 0) ? '<div class="rd-thumbs">' + ph.map(function (p, i) { return '<button type="button" data-zoom="' + esc(p.url) + '" aria-label="Ver foto ' + (i + 1) + '"><img src="' + esc(p.url) + '" alt=""></button>'; }).join('') + '</div>' : '') +
        '<div class="rd-card-b">' +
          '<div class="rd-card-tags"><span class="tag">' + esc(t.catLabel) + '</span><span class="rd-code">' + esc(t.code) + '</span></div>' +
          (t.place ? '<h3>' + esc(t.place) + '</h3>' : '') +
          (t.text ? '<p>' + esc(t.text) + '</p>' : '') + timer +
          '<ul class="rd-meta">' +
            '<li>' + icons.cal + 'Registrado em ' + fmtDate(t.createdAt) + (t.by ? ' por ' + esc(t.by) : '') + '</li>' +
            '<li>' + icons.bank + 'Levado à Prefeitura em ' + fmtDate(t.publishedAt) + (t.department ? ' · ' + esc(t.department) : '') + '</li>' +
            (t.oficio ? '<li>' + icons.doc + esc(t.oficio) + '</li>' : '') +
          '</ul>' +
          (t.after ? '<div class="rd-after"><small>Como ficou</small><button type="button" data-zoom="' + esc(t.after.url) + '"><img src="' + esc(t.after.url) + '" alt="Foto depois da solução"></button></div>' : '') +
        '</div>';
      card.hidden = false;
      doc.documentElement.classList.toggle('rd-sheet-open', mobile());
      card.classList.toggle('is-ok', t.status === 'resolvido');
      card.scrollTop = 0;
    };
    var closeCard = function () { var v = $('video', card); if (v) v.pause(); card.hidden = true; doc.documentElement.classList.remove('rd-sheet-open'); if (active) active.getElement() && active.getElement().classList.remove('is-on'); active = null; };
    card.addEventListener('click', function (e) {
      var z = e.target.closest('[data-zoom]'); if (z) { zoom(z.getAttribute('data-zoom')); return; }
      if (e.target.closest('.rd-card-x')) closeCard();
    });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !card.hidden) closeCard(); });
    map.on('click', function () { if (!card.hidden) closeCard(); });
    // no celular o card é uma gaveta fixa: fecha quando o mapa sai da tela
    if ('IntersectionObserver' in window) new IntersectionObserver(function (es) { if (!es[0].isIntersecting && mobile() && !card.hidden) closeCard(); }, { threshold: 0.05 }).observe(mapEl);

    ITEMS.forEach(function (t) {
      var m = L.marker([t.lat, t.lng], { icon: pinIcon(t.status), keyboard: true, alt: t.catLabel + (t.place ? ' – ' + t.place : '') });
      m.item = t;
      var thumb = t.photos && t.photos[0] ? '<img src="' + esc(t.photos[0].url) + '" alt="">' : '';
      if (canHover) m.bindTooltip('<div class="rd-tip">' + thumb + '<div><b>' + esc(t.catLabel) + '</b><span>' + (t.status === 'resolvido' ? 'Resolvido em ' + t.days + (t.days === 1 ? ' dia' : ' dias') : 'Aguardando há <em data-since="' + esc(t.publishedAt) + '">' + span(t.publishedAt) + '</em>') + '</span></div></div>', { direction: 'top', opacity: 1, className: 'rd-tipbox' });
      m.on('click', function () {
        m.closeTooltip && m.closeTooltip();
        if (active && active.getElement()) active.getElement().classList.remove('is-on');
        active = m; if (m.getElement()) m.getElement().classList.add('is-on');
        renderCard(t);
        if (mobile()) map.panTo([t.lat, t.lng]);
      });
      markers.push(m);
    });
    var apply = function () {
      cluster.clearLayers();
      var shown = markers.filter(function (m) { return (!st || m.item.status === st) && (!cat || m.item.cat === cat); });
      shown.forEach(function (m) { cluster.addLayer(m); });
      $('#rd-empty').hidden = shown.length > 0;
      if (shown.length && cluster.getBounds) { var b = cluster.getBounds(); if (b.isValid()) map.fitBounds(b.pad(0.25), { maxZoom: 16 }); }
      if (active && shown.indexOf(active) < 0) closeCard();
    };
    $$('.rd-tools .fl').forEach(function (b) {
      b.addEventListener('click', function () {
        st = b.getAttribute('data-st'); $$('.rd-tools .fl').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); }); apply();
      });
    });
    $('#rd-cat').addEventListener('change', function () { cat = this.value; apply(); });
    apply();
    openById = function (id) {
      var m = markers.filter(function (x) { return x.item.id === id; })[0]; if (!m) return;
      if (st || cat) { st = ''; cat = ''; $('#rd-cat').value = ''; $$('.rd-tools .fl').forEach(function (x) { x.setAttribute('aria-pressed', x.getAttribute('data-st') === '' ? 'true' : 'false'); }); apply(); }
      mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(function () { cluster.zoomToShowLayer(m, function () { map.setView(m.getLatLng(), Math.max(map.getZoom(), 17)); m.fire('click'); }); }, 350);
    };
    // link direto para uma demanda (?d=123), usado nos e-mails de acompanhamento
    var want = parseInt(new URLSearchParams(location.search).get('d'), 10);
    var target = want && markers.filter(function (m) { return m.item.id === want; })[0];
    if (target) {
      setTimeout(function () {
        mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        cluster.zoomToShowLayer(target, function () { map.setView(target.getLatLng(), Math.max(map.getZoom(), 16)); target.fire('click'); });
      }, 400);
    }
    // contadores ao vivo
    setInterval(function () { $$('[data-since]').forEach(function (el) { el.textContent = span(el.getAttribute('data-since')); }); }, 30000);
  }

  /* ---------------- formulário ---------------- */
  var form = $('#rd-form');
  if (!form) return;
  var msg = $('#rd-msg'), send = $('#rd-send'), fileIn = $('#rd-fotos'), ups = $('#rd-ups'), drop = $('#rd-drop');
  var photos = [], MAXF = 3, vid = null, MAXV = 25 * 1024 * 1024;
  $('#rd-t').value = Date.now();
  var say = function (t, ok) { msg.hidden = false; msg.textContent = t; msg.classList.toggle('is-ok', !!ok); };
  var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim(); };
  var toast = function (t) { var el = $('#toast'); if (!el) return alert(t); el.textContent = t; el.classList.add('is-on'); setTimeout(function () { el.classList.remove('is-on'); }, 3000); };

  $$('[data-mask="phone"]', form).forEach(function (inp) {
    inp.addEventListener('input', function () {
      var d = inp.value.replace(/\D/g, '').slice(0, 11);
      inp.value = d.length > 10 ? '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7) : d.length > 6 ? '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6) : d.length > 2 ? '(' + d.slice(0, 2) + ') ' + d.slice(2) : d.length ? '(' + d : '';
    });
  });
  $$('input,select,textarea', form).forEach(function (f) { f.addEventListener('input', function () { f.classList.remove('is-bad'); }); f.addEventListener('change', function () { f.classList.remove('is-bad'); var fs = f.closest('.rd-cats'); if (fs) fs.classList.remove('is-bad'); }); });

  /* fotos e vídeo */
  var mmss = function (s) { s = Math.round(s || 0); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); };
  var draw = function () {
    ups.innerHTML = '';
    photos.forEach(function (p, i) {
      var d = doc.createElement('div'); d.className = 'up';
      d.innerHTML = '<img alt="Foto ' + (i + 1) + '"><button type="button" aria-label="Remover foto ' + (i + 1) + '">' + icons.x + '</button>';
      $('img', d).src = p.url;
      $('button', d).addEventListener('click', function () { URL.revokeObjectURL(p.url); photos.splice(i, 1); draw(); });
      ups.appendChild(d);
    });
    if (vid) {
      var v = doc.createElement('div'); v.className = 'up up--video' + (vid.state === 'busy' ? ' is-busy' : '');
      v.innerHTML = vid.state === 'busy'
        ? '<div class="up-prog"><b>' + Math.round(vid.pct * 100) + '%</b><span>Preparando o vídeo</span><i style="width:' + Math.round(vid.pct * 100) + '%"></i></div>'
        : (vid.poster ? '<img alt="Capa do vídeo">' : '<div class="up-vph">' + icons.video + '</div>') + '<span class="up-play">' + icons.play + '</span><span class="up-dur">' + mmss(vid.duration) + '</span>';
      v.insertAdjacentHTML('beforeend', '<button type="button" aria-label="Remover vídeo">' + icons.x + '</button>');
      if (vid.poster && $('img', v)) $('img', v).src = vid.posterUrl;
      $('button', v).addEventListener('click', function () { if (vid && vid.cancel) vid.cancel(); if (vid && vid.posterUrl) URL.revokeObjectURL(vid.posterUrl); vid = null; draw(); });
      ups.appendChild(v);
    }
    var parts = [];
    if (photos.length) parts.push(photos.length + '/' + MAXF + ' fotos');
    if (vid) parts.push('1 vídeo');
    $('span', drop).textContent = parts.length ? 'Adicionar mais (' + parts.join(', ') + ')' : 'Toque para tirar ou escolher fotos ou vídeo';
  };
  var isVideo = function (f) { return /^video\//.test(f.type) || /\.(mp4|mov|m4v|webm|3gp|mkv)$/i.test(f.name || ''); };
  var VIDEO_JS = (doc.documentElement.getAttribute('data-base') || '') + '/js/video.js?v=__VIDEO_V__';
  var addVideo = function (f) {
    var me = { state: 'busy', pct: 0, duration: 0 }, cancelled = false;
    me.cancel = function () { cancelled = true; if (me.conv) me.conv.cancel(); };
    vid = me; draw();
    var fail = function (t) { if (vid === me) { vid = null; draw(); } toast(t); };
    import(VIDEO_JS).then(function (m) {
      return m.supported().then(function (ok) {
        if (!ok) {
          // navegador sem compressão: aceita o arquivo original se já for pequeno
          if (f.size > MAXV) throw new Error('Este navegador não consegue diminuir o vídeo. Grave um vídeo mais curto (até 25 MB) ou envie pelo WhatsApp.');
          return { blob: f, duration: 0, original: true };
        }
        return m.compress(f, function (p) { if (cancelled) return; me.pct = p; draw(); });
      }).then(function (r) {
        if (cancelled) return;
        if (r.blob.size > MAXV) throw new Error('O vídeo ainda ficou grande demais. Envie um trecho mais curto.');
        return m.poster(r.blob).then(function (pb) {
          if (cancelled) return;
          me.state = 'ready'; me.blob = r.blob; me.duration = r.duration; me.poster = pb; me.posterUrl = pb ? URL.createObjectURL(pb) : '';
          draw();
          if (r.trimmed) toast('O vídeo foi cortado para o primeiro minuto.');
          if (r.audio === false) toast('O vídeo foi enviado sem som neste navegador.');
        });
      });
    }).catch(function (e) { if (!cancelled) fail(e && e.message ? e.message : 'Não foi possível preparar o vídeo.'); });
  };
  fileIn.addEventListener('change', function () {
    Array.prototype.forEach.call(fileIn.files, function (f) {
      if (isVideo(f)) {
        if (vid) { toast('Só dá para enviar 1 vídeo por demanda.'); return; }
        addVideo(f); return;
      }
      if (photos.length >= MAXF) { toast('Máximo de ' + MAXF + ' fotos.'); return; }
      photos.push({ file: f, url: URL.createObjectURL(f) });
    });
    fileIn.value = ''; draw(); drop.classList.remove('is-bad');
  });
  var shrink = function (f) {
    return new Promise(function (res) {
      var img = new Image(), u = URL.createObjectURL(f);
      img.onload = function () {
        var k = Math.min(1, 1920 / Math.max(img.naturalWidth, img.naturalHeight)), c = doc.createElement('canvas');
        c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height); URL.revokeObjectURL(u);
        c.toBlob(function (b) { res(b ? new File([b], 'foto.jpg', { type: 'image/jpeg' }) : f); }, 'image/jpeg', 0.84);
      };
      img.onerror = function () { URL.revokeObjectURL(u); res(f); };
      img.src = u;
    });
  };

  /* local: mapa para marcar + GPS */
  var pickEl = $('#rd-pick'), pickSt = $('#rd-pick-st'), rua = $('#rd-rua'), bairro = $('#rd-bairro'), geoBtn = $('#rd-geo');
  var pick, pin, ring, watch, best, timer, ruaAuto = false;
  /* aviso de demandas parecidas num raio de 200 m */
  var NEAR = 200, nearEl = $('#rd-near'), nearT, nearLayer;
  var dist = function (a, b, c, d) { var R = 6371000, r = Math.PI / 180, x = Math.sin((c - a) * r / 2), y = Math.sin((d - b) * r / 2); return 2 * R * Math.asin(Math.min(1, Math.sqrt(x * x + Math.cos(a * r) * Math.cos(c * r) * y * y))); };
  var near = function (lat, lng) {
    clearTimeout(nearT);
    nearT = setTimeout(function () {
      var pub = (window.RD_ITEMS || []).map(function (t) { return { t: t, d: dist(lat, lng, t.lat, t.lng) }; })
        .filter(function (x) { return x.d <= NEAR && x.t.status !== 'resolvido'; }).sort(function (a, b) { return a.d - b.d; });
      fetch(BASE + '/api/demandas.php?near=' + lat.toFixed(6) + ',' + lng.toFixed(6), { credentials: 'same-origin' })
        .then(function (r) { return r.json(); }).catch(function () { return { items: [] }; })
        .then(function (j) { drawNear(lat, lng, pub, (j && j.items) || []); });
    }, 350);
  };
  var drawNear = function (lat, lng, pub, pend) {
    if (pick) { if (nearLayer) pick.removeLayer(nearLayer); nearLayer = null; }
    if (!pub.length && !pend.length) { nearEl.hidden = true; nearEl.innerHTML = ''; return; }
    var chosen = ($('#rd-tipo') || {}).value || '';
    var same = function (c) { return chosen && c === chosen ? ' <em>mesmo tipo</em>' : ''; };
    var n = pub.length + pend.length;
    var html = '<div class="rd-near-hd">' + icons.pin + '<div><b>' + (n === 1 ? 'Já existe 1 demanda' : 'Já existem ' + n + ' demandas') + ' a até ' + NEAR + ' m daqui</b>' +
      '<span>Confira se não é o mesmo problema. Se for, não precisa enviar de novo: nossa equipe já está acompanhando. Se for outro, pode continuar.</span></div></div><ul>';
    pub.forEach(function (x) {
      var t = x.t, th = t.photos && t.photos[0] ? '<img src="' + esc(t.photos[0].url) + '" alt="">' : '<span class="rd-near-i">' + icons.pin + '</span>';
      html += '<li>' + th + '<div><b>' + esc(t.catLabel) + same(t.cat) + '</b><span>' + Math.round(x.d) + ' m · ' +
        'aguardando a Prefeitura há ' + span(t.publishedAt) + (t.place ? ' · ' + esc(t.place) : '') + '</span></div>' +
        (openById ? '<button type="button" class="rd-near-go" data-id="' + t.id + '">Ver</button>' : '') + '</li>';
    });
    pend.forEach(function (x) {
      html += '<li><span class="rd-near-i rd-near-i--wait">' + icons.clock + '</span><div><b>' + esc(x.catLabel) + same(x.cat) + '</b><span>' + x.dist + ' m · em análise pela nossa equipe' +
        (x.days ? ' (enviada há ' + x.days + (x.days === 1 ? ' dia' : ' dias') + ')' : ' (enviada hoje)') + '</span></div></li>';
    });
    nearEl.innerHTML = html + '</ul>';
    nearEl.hidden = false;
    if (pick && window.L) {
      nearLayer = L.layerGroup().addTo(pick);
      L.circle([lat, lng], { radius: NEAR, color: '#FE6500', weight: 1.5, dashArray: '5 6', fillOpacity: .05, interactive: false }).addTo(nearLayer);
      pub.forEach(function (x) { L.circleMarker([x.t.lat, x.t.lng], { radius: 7, color: '#fff', weight: 2, fillColor: '#FE6500', fillOpacity: 1, interactive: false }).addTo(nearLayer); });
    }
  };
  nearEl && nearEl.addEventListener('click', function (e) { var b = e.target.closest('.rd-near-go'); if (b && openById) openById(parseInt(b.getAttribute('data-id'), 10)); });
  $('#rd-tipo') && $('#rd-tipo').addEventListener('change', function () { var la = parseFloat($('#rd-lat').value), lo = parseFloat($('#rd-lng').value); if (!isNaN(la)) near(la, lo); });

  var setLoc = function (lat, lng, acc, manual) {
    $('#rd-lat').value = lat.toFixed(7); $('#rd-lng').value = lng.toFixed(7);
    if (nearEl) near(lat, lng);
    $('#rd-acc').value = acc ? Math.round(acc) : ''; $('#rd-manual').value = manual ? '1' : '';
    pickEl.classList.remove('is-bad');
    if (!pick) return;
    if (!pin) {
      pin = L.marker([lat, lng], { draggable: true, icon: pinIcon('publicado'), title: 'Arraste para ajustar' }).addTo(pick);
      pin.on('dragend', function () { var p = pin.getLatLng(); stop(); if (ring) ring.setRadius(0); setLoc(p.lat, p.lng, 0, true); reverse(p.lat, p.lng, 0, true); });
      ring = L.circle([lat, lng], { radius: acc || 0, color: '#0144C9', weight: 1, fillOpacity: .08 }).addTo(pick);
    } else { pin.setLatLng([lat, lng]); ring.setLatLng([lat, lng]).setRadius(manual ? 0 : (acc || 0)); }
  };
  var describe = function (label, acc, manual) {
    pickSt.innerHTML = (label ? '<b>' + esc(label) + '</b><br>' : '') + (manual ? 'Ponto marcado por você. Arraste o pino se precisar.' : acc > 60 ? '<span class="warn">Precisão baixa (±' + Math.round(acc) + ' m). Arraste o pino até o local exato.</span>' : 'Precisão de ±' + Math.round(acc) + ' m. Arraste o pino se precisar.');
  };
  var reverse = function (lat, lng, acc, manual) {
    fetch(BASE + '/api/geocode.php?lat=' + lat.toFixed(6) + '&lng=' + lng.toFixed(6)).then(function (r) { return r.json(); }).then(function (j) {
      if (!j.ok) { $('#rd-addr').value = ''; describe('', acc, manual); return; }
      $('#rd-addr').value = j.label;
      if (j.line && (ruaAuto || !rua.value.trim())) { rua.value = j.line; ruaAuto = true; }
      var cands = (j.candidates || []).map(norm);
      var hit = Array.prototype.filter.call(bairro.options, function (o) { return o.value && cands.indexOf(norm(o.value)) > -1; })[0];
      if (hit && (!bairro.value || bairro.getAttribute('data-auto'))) { bairro.value = hit.value; bairro.setAttribute('data-auto', '1'); }
      describe(j.label, acc, manual);
    }).catch(function () { describe('', acc, manual); });
  };
  if (pickEl && window.L) {
    pick = L.map(pickEl, { scrollWheelZoom: false }).setView(CENTER, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(pick);
    pick.on('click', function (e) { stop(); setLoc(e.latlng.lat, e.latlng.lng, 0, true); reverse(e.latlng.lat, e.latlng.lng, 0, true); });
  }
  var stop = function () { if (watch != null) { navigator.geolocation.clearWatch(watch); watch = null; } clearTimeout(timer); geoBtn.disabled = false; geoBtn.classList.remove('is-busy'); };
  var finish = function () { if (!geoBtn.classList.contains('is-busy')) return; stop(); if (best) reverse(best.lat, best.lng, best.acc, false); };
  geoBtn.addEventListener('click', function () {
    if (!navigator.geolocation || !window.isSecureContext) { toast('Não foi possível usar a localização. Toque no mapa para marcar o local.'); return; }
    geoBtn.disabled = true; geoBtn.classList.add('is-busy'); best = null;
    pickSt.innerHTML = 'Buscando sua localização… Se o navegador perguntar, toque em <b>Permitir</b>.';
    watch = navigator.geolocation.watchPosition(function (pos) {
      var c = pos.coords;
      if (!best || c.accuracy < best.acc) {
        best = { lat: c.latitude, lng: c.longitude, acc: c.accuracy };
        setLoc(best.lat, best.lng, best.acc, false);
        if (pick) pick.setView([best.lat, best.lng], best.acc > 150 ? 16 : 18);
        pickSt.textContent = 'Melhorando a precisão… ±' + Math.round(best.acc) + ' m';
      }
      if (best.acc <= 15) finish();
    }, function (err) {
      if (best) { finish(); return; }
      stop();
      pickSt.textContent = err.code === 1 ? 'Localização bloqueada. Toque no mapa para marcar o local.' : 'Não foi possível pegar a localização. Toque no mapa para marcar o local.';
    }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
    timer = setTimeout(finish, 9000);
  });
  rua.addEventListener('input', function () { ruaAuto = false; });
  bairro.addEventListener('change', function () { bairro.removeAttribute('data-auto'); });

  /* envio */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var bad = [];
    if (vid && vid.state === 'busy') { say('Aguarde o vídeo terminar de ser preparado (' + Math.round(vid.pct * 100) + '%).'); return; }
    if (!photos.length && !vid) { drop.classList.add('is-bad'); bad.push([drop, 'uma foto ou vídeo']); }
    if (!$('#rd-lat').value) { pickEl.classList.add('is-bad'); bad.push([pickEl, 'o local no mapa']); }
    if (!$('#rd-tipo').value) { $('#rd-tipo').classList.add('is-bad'); bad.push([$('#rd-tipo'), 'o tipo da demanda']); }
    if (!$('#rd-desc').value.trim()) { $('#rd-desc').classList.add('is-bad'); bad.push([$('#rd-desc'), 'a descrição']); }
    if (!$('#rd-anon').checked && !$('#rd-nome').value.trim()) { $('#rd-nome').classList.add('is-bad'); bad.push([$('#rd-nome'), 'seu nome']); }
    if ($('#rd-zap').value.replace(/\D/g, '').length < 10) { $('#rd-zap').classList.add('is-bad'); bad.push([$('#rd-zap'), 'seu WhatsApp com DDD']); }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test($('#rd-mail').value.trim())) { $('#rd-mail').classList.add('is-bad'); bad.push([$('#rd-mail'), 'seu e-mail']); }
    if (!$('#rd-ok').checked) bad.push([$('#rd-ok'), 'a concordância com o uso dos dados']);
    if (bad.length) { say('Falta: ' + bad.map(function (b) { return b[1]; }).join(', ') + '.'); bad[0][0].scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
    send.classList.add('is-busy'); $('span', send).textContent = 'ENVIANDO…'; msg.hidden = true;
    Promise.all(photos.map(function (p) { return shrink(p.file); })).then(function (files) {
      var fd = new FormData(form);
      files.forEach(function (f, i) { fd.append('fotos[]', f, 'foto-' + (i + 1) + '.jpg'); });
      if (vid && vid.blob) {
        fd.append('video', vid.blob, 'video.' + (/webm/.test(vid.blob.type) ? 'webm' : /quicktime/.test(vid.blob.type) ? 'mov' : 'mp4'));
        if (vid.poster) fd.append('video_poster', vid.poster, 'capa.jpg');
        if (vid.duration) fd.append('video_dur', String(vid.duration));
      }
      // envio com porcentagem (o vídeo pode levar alguns segundos no 4G)
      return new Promise(function (res) {
        var x = new XMLHttpRequest();
        x.open('POST', form.getAttribute('action'));
        x.upload.onprogress = function (ev) { if (ev.lengthComputable && vid) $('span', send).textContent = 'ENVIANDO ' + Math.round(ev.loaded / ev.total * 100) + '%'; };
        x.onload = function () { var j; try { j = JSON.parse(x.responseText); } catch (e) { j = { ok: false, error: x.status === 413 ? 'Os arquivos ficaram pesados demais. Tente um vídeo mais curto.' : 'Erro no servidor (' + x.status + ').' }; } res(j); };
        x.onerror = function () { res({ ok: false, error: 'Sem conexão no momento. Verifique a internet e tente de novo.' }); };
        x.send(fd);
      });
    }).then(function (j) {
      if (!j.ok) { say(j.error || 'Não foi possível enviar.'); if (j.field) { var f = $('[name="' + j.field + '"]', form); if (f) f.classList.add('is-bad'); } return; }
      $('#rd-code').textContent = j.code;
      form.hidden = true; $('#rd-done').hidden = false;
      $('#rd-done').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }).catch(function () { say('Sem conexão no momento. Verifique a internet e tente de novo.'); })
      .then(function () { send.classList.remove('is-busy'); $('span', send).textContent = 'ENVIAR DEMANDA'; });
  });
  $('#rd-again').addEventListener('click', function () {
    form.reset(); photos = []; if (vid && vid.posterUrl) URL.revokeObjectURL(vid.posterUrl); vid = null; draw(); msg.hidden = true; ruaAuto = false; $('#rd-t').value = Date.now();
    $$('#rd-lat,#rd-lng,#rd-acc,#rd-addr,#rd-manual', form).forEach(function (i) { i.value = ''; });
    if (pin) { pick.removeLayer(pin); pick.removeLayer(ring); pin = ring = null; }
    pickSt.textContent = 'Use sua localização ou toque no mapa para marcar o local exato.';
    if (nearEl) { nearEl.hidden = true; nearEl.innerHTML = ''; } if (nearLayer && pick) { pick.removeLayer(nearLayer); nearLayer = null; }
    $('#rd-done').hidden = true; form.hidden = false; form.scrollIntoView({ behavior: 'smooth' });
  });
  // vídeo "Como funciona": o player do Instagram só carrega no toque (mais rápido e sem cookies antes da hora)
  var vid = $('#rd-vid');
  if (vid) vid.addEventListener('click', function () {
    if (vid.querySelector('iframe')) return;
    var f = doc.createElement('iframe');
    f.src = vid.getAttribute('data-src'); f.title = 'Vídeo: como funciona o Friburgo no Mapa'; f.allow = 'autoplay; encrypted-media; picture-in-picture'; f.allowFullscreen = true; f.setAttribute('scrolling', 'no'); f.loading = 'lazy';
    vid.classList.add('is-on'); vid.innerHTML = ''; vid.appendChild(f);
  });
})();
