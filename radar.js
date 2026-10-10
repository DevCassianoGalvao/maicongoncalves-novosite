/* Radar Friburgo: mapa de demandas e registro de problemas */
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
  var zoom = function (src, alt) { if (!lb || !lb.showModal) { window.open(src, '_blank'); return; } $('img', lb).src = src; $('img', lb).alt = alt || ''; lb.showModal(); };
  if (lb) {
    $('.lb-x', lb).addEventListener('click', function () { lb.close(); });
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.classList.contains('lb-in')) lb.close(); });
  }

  /* ---------------- mapa público ---------------- */
  var mapEl = $('#rd-map'), card = $('#rd-card');
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
      var ph = t.photos || [];
      var timer = t.status === 'resolvido'
        ? '<div class="rd-timer rd-timer--ok">' + icons.check + '<div><small>Resolvido em</small><b>' + t.days + (t.days === 1 ? ' dia' : ' dias') + '</b><small>Solução em ' + fmtDate(t.resolvedAt) + '</small></div></div>'
        : '<div class="rd-timer">' + icons.clock + '<div><small>Aguardando a Prefeitura há</small><b data-since="' + esc(t.publishedAt) + '">' + span(t.publishedAt) + '</b></div></div>';
      card.innerHTML =
        '<button type="button" class="rd-card-x" aria-label="Fechar">' + icons.x + '</button>' +
        (ph.length ? '<button type="button" class="rd-card-ph" data-zoom="' + esc(ph[0].url) + '"><img src="' + esc(ph[0].url) + '" alt="Foto do problema: ' + esc(t.catLabel) + '">' + (ph.length > 1 ? '<span>' + ph.length + ' fotos</span>' : '') + '</button>' : '') +
        (ph.length > 1 ? '<div class="rd-thumbs">' + ph.map(function (p, i) { return '<button type="button" data-zoom="' + esc(p.url) + '" aria-label="Ver foto ' + (i + 1) + '"><img src="' + esc(p.url) + '" alt=""></button>'; }).join('') + '</div>' : '') +
        '<div class="rd-card-b">' +
          '<div class="rd-card-tags"><span class="tag">' + esc(t.catLabel) + '</span><span class="rd-code">' + esc(t.code) + '</span></div>' +
          (t.place ? '<h3>' + esc(t.place) + '</h3>' : '') +
          (t.text ? '<p>' + esc(t.text) + '</p>' : '') + timer +
          '<ul class="rd-meta">' +
            '<li>' + icons.cal + 'Registrado em ' + fmtDate(t.createdAt) + (t.by ? ' por ' + esc(t.by) : '') + '</li>' +
            '<li>' + icons.bank + 'Levado à Prefeitura em ' + fmtDate(t.publishedAt) + (t.department ? ' · ' + esc(t.department) : '') + '</li>' +
          '</ul>' +
          (t.after ? '<div class="rd-after"><small>Como ficou</small><button type="button" data-zoom="' + esc(t.after.url) + '"><img src="' + esc(t.after.url) + '" alt="Foto depois da solução"></button></div>' : '') +
        '</div>';
      card.hidden = false;
      doc.documentElement.classList.toggle('rd-sheet-open', mobile());
      card.classList.toggle('is-ok', t.status === 'resolvido');
      card.scrollTop = 0;
    };
    var closeCard = function () { card.hidden = true; doc.documentElement.classList.remove('rd-sheet-open'); if (active) active.getElement() && active.getElement().classList.remove('is-on'); active = null; };
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
    // contadores ao vivo
    setInterval(function () { $$('[data-since]').forEach(function (el) { el.textContent = span(el.getAttribute('data-since')); }); }, 30000);
  }

  /* ---------------- formulário ---------------- */
  var form = $('#rd-form');
  if (!form) return;
  var msg = $('#rd-msg'), send = $('#rd-send'), fileIn = $('#rd-fotos'), ups = $('#rd-ups'), drop = $('#rd-drop');
  var photos = [], MAXF = 3;
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

  /* fotos */
  var draw = function () {
    ups.innerHTML = '';
    photos.forEach(function (p, i) {
      var d = doc.createElement('div'); d.className = 'up';
      d.innerHTML = '<img alt="Foto ' + (i + 1) + '"><button type="button" aria-label="Remover foto ' + (i + 1) + '">' + icons.x + '</button>';
      $('img', d).src = p.url;
      $('button', d).addEventListener('click', function () { URL.revokeObjectURL(p.url); photos.splice(i, 1); draw(); });
      ups.appendChild(d);
    });
    $('span', drop).textContent = photos.length ? (photos.length >= MAXF ? 'Limite de ' + MAXF + ' fotos' : 'Adicionar outra foto (' + photos.length + '/' + MAXF + ')') : 'Toque para tirar ou escolher a foto';
  };
  fileIn.addEventListener('change', function () {
    Array.prototype.forEach.call(fileIn.files, function (f) {
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
  var setLoc = function (lat, lng, acc, manual) {
    $('#rd-lat').value = lat.toFixed(7); $('#rd-lng').value = lng.toFixed(7);
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
    if (!photos.length) { drop.classList.add('is-bad'); bad.push([drop, 'a foto']); }
    if (!$('#rd-lat').value) { pickEl.classList.add('is-bad'); bad.push([pickEl, 'o local no mapa']); }
    if (!$('input[name="categoria"]:checked', form)) { $('.rd-cats', form).classList.add('is-bad'); bad.push([$('.rd-cats', form), 'o tipo do problema']); }
    if (!$('#rd-desc').value.trim()) { $('#rd-desc').classList.add('is-bad'); bad.push([$('#rd-desc'), 'a descrição']); }
    if (!$('#rd-anon').checked && !$('#rd-nome').value.trim()) { $('#rd-nome').classList.add('is-bad'); bad.push([$('#rd-nome'), 'seu nome']); }
    if ($('#rd-zap').value.replace(/\D/g, '').length < 10) { $('#rd-zap').classList.add('is-bad'); bad.push([$('#rd-zap'), 'seu WhatsApp com DDD']); }
    if (!$('#rd-ok').checked) bad.push([$('#rd-ok'), 'a concordância com o uso dos dados']);
    if (bad.length) { say('Falta: ' + bad.map(function (b) { return b[1]; }).join(', ') + '.'); bad[0][0].scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
    send.classList.add('is-busy'); $('span', send).textContent = 'ENVIANDO…'; msg.hidden = true;
    Promise.all(photos.map(function (p) { return shrink(p.file); })).then(function (files) {
      var fd = new FormData(form);
      files.forEach(function (f, i) { fd.append('fotos[]', f, 'foto-' + (i + 1) + '.jpg'); });
      return fetch(form.getAttribute('action'), { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Erro no servidor (' + r.status + ').' }; }); });
    }).then(function (j) {
      if (!j.ok) { say(j.error || 'Não foi possível enviar.'); if (j.field) { var f = $('[name="' + j.field + '"]', form); if (f) f.classList.add('is-bad'); } return; }
      $('#rd-code').textContent = j.code;
      form.hidden = true; $('#rd-done').hidden = false;
      $('#rd-done').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }).catch(function () { say('Sem conexão no momento. Verifique a internet e tente de novo.'); })
      .then(function () { send.classList.remove('is-busy'); $('span', send).textContent = 'ENVIAR'; });
  });
  $('#rd-again').addEventListener('click', function () {
    form.reset(); photos = []; draw(); msg.hidden = true; ruaAuto = false; $('#rd-t').value = Date.now();
    $$('#rd-lat,#rd-lng,#rd-acc,#rd-addr,#rd-manual', form).forEach(function (i) { i.value = ''; });
    if (pin) { pick.removeLayer(pin); pick.removeLayer(ring); pin = ring = null; }
    pickSt.textContent = 'Use sua localização ou toque no mapa para marcar o local exato.';
    $('#rd-done').hidden = true; form.hidden = false; form.scrollIntoView({ behavior: 'smooth' });
  });
})();
