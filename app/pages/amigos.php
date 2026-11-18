<?php
defined('APP') or exit;
$meta = ($meta ?? []) + ['title' => 'Amigos do Bem – onde doar em Nova Friburgo | Vereador Maicon Gonçalves', 'description' => 'Instituições de Nova Friburgo que recebem doações.', 'page' => 'amigos'];
?><!doctype html>
<html lang="pt-BR" data-base="<?= e(B) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php require ROOT . '/app/views/meta.php'; ?>
<meta name="theme-color" content="#031436">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="<?= B ?>/site.css?v=cfe18850">
<script>(function(){var d=document.documentElement,skip=false;d.classList.add('js');try{skip=sessionStorage.getItem('mgNav')==='1';sessionStorage.removeItem('mgNav')}catch(e){}
if(!skip&&false&&!matchMedia('(prefers-reduced-motion: reduce)').matches){d.classList.add('has-intro');setTimeout(function(){d.classList.remove('has-intro')},6000)}})();</script>
</head>
<body>
<svg class="defs" aria-hidden="true" focusable="false"><defs>
  <linearGradient id="g-or" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFB070"/><stop offset=".55" stop-color="#FE6500"/><stop offset="1" stop-color="#D94A00"/></linearGradient>
  <linearGradient id="g-rb" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="1600" y2="0"><stop offset="0" stop-color="#FF8A3D"/><stop offset=".5" stop-color="#FE6500"/><stop offset="1" stop-color="#FF9A52"/></linearGradient>
  <linearGradient id="g-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D2ECFB"/><stop offset="1" stop-color="#6BAEDB"/></linearGradient>
  <linearGradient id="g-wh" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#CFDCF2"/></linearGradient>
  <linearGradient id="g-nv" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1348B3"/><stop offset="1" stop-color="#020B1F"/></linearGradient>
  <linearGradient id="g-bl" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6FA0FF"/><stop offset="1" stop-color="#0137A8"/></linearGradient>
  <linearGradient id="g-glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".42"/><stop offset="1" stop-color="#fff" stop-opacity=".05"/></linearGradient>
  <linearGradient id="g-hill-a" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E4ECF9"/><stop offset="1" stop-color="#B3C6E8"/></linearGradient>
  <linearGradient id="g-hill-b" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#B9CCEC"/><stop offset="1" stop-color="#7E9DD6"/></linearGradient>
  <linearGradient id="g-m1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C9D7F0"/><stop offset="1" stop-color="#9DB5E0"/></linearGradient>
  <linearGradient id="g-m2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3D68C4"/><stop offset="1" stop-color="#143A8C"/></linearGradient>
  <linearGradient id="g-m3" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0A2C73"/><stop offset="1" stop-color="#041C4B"/></linearGradient>
  <filter id="f-soft" x="-40%" y="-40%" width="180%" height="200%"><feDropShadow dx="0" dy="7" stdDeviation="7" flood-color="#020B1F" flood-opacity=".3"/></filter>
</defs></svg>
<div class="intro" id="intro" aria-hidden="true"><div class="intro-in"><img class="intro-logo" src="<?= B ?>/img/logo.webp" alt="" width="640" height="293"><span class="intro-bar"><i></i></span></div></div>
<header class="hd" id="hd">
  <div class="hd-bar">
    <a class="hd-logo" href="<?= B ?>/" aria-label="Vereador Maicon Gonçalves, início"><img src="<?= B ?>/img/logo.webp" alt="Vereador Maicon Gonçalves" width="640" height="293"></a>
    <nav class="hd-nav" aria-label="Principal">
      <a href="<?= B ?>/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"/></svg>Início</a>
      <a href="<?= B ?>/minha-historia/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64ZM160,88h40a8,8,0,0,1,0,16H160a8,8,0,0,1,0-16Zm48,40a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,128Zm0,32a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,160Z"/></svg>Minha história</a>
      <a href="<?= B ?>/fl360/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M244.8,150.4a8,8,0,0,1-11.2-1.6A51.6,51.6,0,0,0,192,128a8,8,0,0,1-7.37-4.89,8,8,0,0,1,0-6.22A8,8,0,0,1,192,112a24,24,0,1,0-23.24-30,8,8,0,1,1-15.5-4A40,40,0,1,1,219,117.51a67.94,67.94,0,0,1,27.43,21.68A8,8,0,0,1,244.8,150.4ZM190.92,212a8,8,0,1,1-13.84,8,57,57,0,0,0-98.16,0,8,8,0,1,1-13.84-8,72.06,72.06,0,0,1,33.74-29.92,48,48,0,1,1,58.36,0A72.06,72.06,0,0,1,190.92,212ZM128,176a32,32,0,1,0-32-32A32,32,0,0,0,128,176ZM72,120a8,8,0,0,0-8-8A24,24,0,1,1,87.24,82a8,8,0,1,0,15.5-4A40,40,0,1,0,37,117.51,67.94,67.94,0,0,0,9.6,139.19a8,8,0,1,0,12.8,9.61A51.6,51.6,0,0,1,64,128,8,8,0,0,0,72,120Z"/></svg>FL360</a>
      <a href="<?= B ?>/descobrindo-talentos/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm76.52,147.42H170.9l-9.26-12.76,12.63-36.78,15-4.89,26.24,20.13A87.38,87.38,0,0,1,204.52,171.42Zm-164-34.3L66.71,117l15,4.89,12.63,36.78L85.1,171.42H51.48A87.38,87.38,0,0,1,40.47,137.12Zm10-50.64,5.51,18.6L40.71,116.77A87.33,87.33,0,0,1,50.43,86.48ZM109,152,97.54,118.65,128,97.71l30.46,20.94L147,152Zm91.07-46.92,5.51-18.6a87.33,87.33,0,0,1,9.72,30.29Zm-6.2-35.38-9.51,32.08-15.07,4.89L136,83.79V68.21l29.09-20A88.58,88.58,0,0,1,193.86,69.7ZM146.07,41.87,128,54.29,109.93,41.87a88.24,88.24,0,0,1,36.14,0ZM90.91,48.21l29.09,20V83.79L86.72,106.67l-15.07-4.89L62.14,69.7A88.58,88.58,0,0,1,90.91,48.21ZM63.15,187.42H83.52l7.17,20.27A88.4,88.4,0,0,1,63.15,187.42ZM110,214.13,98.12,180.71,107.35,168h41.3l9.23,12.71-11.83,33.42a88,88,0,0,1-36.1,0Zm55.36-6.44,7.17-20.27h20.37A88.4,88.4,0,0,1,165.31,207.69Z"/></svg>Peneira</a>
      <a href="<?= B ?>/noticias/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M88,112a8,8,0,0,1,8-8h80a8,8,0,0,1,0,16H96A8,8,0,0,1,88,112Zm8,40h80a8,8,0,0,0,0-16H96a8,8,0,0,0,0,16ZM232,64V184a24,24,0,0,1-24,24H32A24,24,0,0,1,8,184.11V88a8,8,0,0,1,16,0v96a8,8,0,0,0,16,0V64A16,16,0,0,1,56,48H216A16,16,0,0,1,232,64Zm-16,0H56V184a23.84,23.84,0,0,1-1.37,8H208a8,8,0,0,0,8-8Z"/></svg>Notícias</a>
    </nav>
    <a class="hd-cta btn btn--orange btn--sm " href="<?= B ?>/friburgo-no-mapa/"><span>Friburgo no Mapa</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg></span></a>
    <div class="hd-soc">
      <?= social_links() ?>
    </div>
    <button class="hd-burger" id="burger" aria-label="Abrir menu" aria-expanded="false" aria-controls="mm"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128ZM40,72H216a8,8,0,0,0,0-16H40a8,8,0,0,0,0,16ZM216,184H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Z"/></svg></button>
  </div>
</header>
<div class="mm" id="mm" aria-hidden="true">
  <button class="mm-x" id="mmx" aria-label="Fechar menu"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg></button>
  <nav aria-label="Menu">
    <a href="<?= B ?>/"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"/></svg>Início</a>
    <a href="<?= B ?>/minha-historia/"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64ZM160,88h40a8,8,0,0,1,0,16H160a8,8,0,0,1,0-16Zm48,40a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,128Zm0,32a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,160Z"/></svg>Minha história</a>
    <a href="<?= B ?>/fl360/"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M244.8,150.4a8,8,0,0,1-11.2-1.6A51.6,51.6,0,0,0,192,128a8,8,0,0,1-7.37-4.89,8,8,0,0,1,0-6.22A8,8,0,0,1,192,112a24,24,0,1,0-23.24-30,8,8,0,1,1-15.5-4A40,40,0,1,1,219,117.51a67.94,67.94,0,0,1,27.43,21.68A8,8,0,0,1,244.8,150.4ZM190.92,212a8,8,0,1,1-13.84,8,57,57,0,0,0-98.16,0,8,8,0,1,1-13.84-8,72.06,72.06,0,0,1,33.74-29.92,48,48,0,1,1,58.36,0A72.06,72.06,0,0,1,190.92,212ZM128,176a32,32,0,1,0-32-32A32,32,0,0,0,128,176ZM72,120a8,8,0,0,0-8-8A24,24,0,1,1,87.24,82a8,8,0,1,0,15.5-4A40,40,0,1,0,37,117.51,67.94,67.94,0,0,0,9.6,139.19a8,8,0,1,0,12.8,9.61A51.6,51.6,0,0,1,64,128,8,8,0,0,0,72,120Z"/></svg>FL360</a>
    <a href="<?= B ?>/descobrindo-talentos/"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm76.52,147.42H170.9l-9.26-12.76,12.63-36.78,15-4.89,26.24,20.13A87.38,87.38,0,0,1,204.52,171.42Zm-164-34.3L66.71,117l15,4.89,12.63,36.78L85.1,171.42H51.48A87.38,87.38,0,0,1,40.47,137.12Zm10-50.64,5.51,18.6L40.71,116.77A87.33,87.33,0,0,1,50.43,86.48ZM109,152,97.54,118.65,128,97.71l30.46,20.94L147,152Zm91.07-46.92,5.51-18.6a87.33,87.33,0,0,1,9.72,30.29Zm-6.2-35.38-9.51,32.08-15.07,4.89L136,83.79V68.21l29.09-20A88.58,88.58,0,0,1,193.86,69.7ZM146.07,41.87,128,54.29,109.93,41.87a88.24,88.24,0,0,1,36.14,0ZM90.91,48.21l29.09,20V83.79L86.72,106.67l-15.07-4.89L62.14,69.7A88.58,88.58,0,0,1,90.91,48.21ZM63.15,187.42H83.52l7.17,20.27A88.4,88.4,0,0,1,63.15,187.42ZM110,214.13,98.12,180.71,107.35,168h41.3l9.23,12.71-11.83,33.42a88,88,0,0,1-36.1,0Zm55.36-6.44,7.17-20.27h20.37A88.4,88.4,0,0,1,165.31,207.69Z"/></svg>Peneira</a>
    <a href="<?= B ?>/noticias/"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M88,112a8,8,0,0,1,8-8h80a8,8,0,0,1,0,16H96A8,8,0,0,1,88,112Zm8,40h80a8,8,0,0,0,0-16H96a8,8,0,0,0,0,16ZM232,64V184a24,24,0,0,1-24,24H32A24,24,0,0,1,8,184.11V88a8,8,0,0,1,16,0v96a8,8,0,0,0,16,0V64A16,16,0,0,1,56,48H216A16,16,0,0,1,232,64Zm-16,0H56V184a23.84,23.84,0,0,1-1.37,8H208a8,8,0,0,0,8-8Z"/></svg>Notícias</a>
  </nav>
  <a class="mm-cta btn btn--orange" href="<?= B ?>/friburgo-no-mapa/"><span>Friburgo no Mapa</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg></span></a>
  <div class="mm-soc">
      <?= social_links() ?>
  </div>
</div>

<?php
// Amigos do Bem: instituições publicadas, filtros no próprio navegador (são poucas dezenas).
$abInsts = ab_list('publicada');
$abTipos = ab_tipos();
$abCount = []; $abBairros = []; $abUrg = []; $abDemo = false;
foreach ($abInsts as $i) {
    foreach ($i['tipos'] as $t) $abCount[(int) $t['id']] = ($abCount[(int) $t['id']] ?? 0) + 1;
    if ($i['bairro']) $abBairros[$i['bairro']] = true;
    foreach ($i['necessidades'] as $n) $abUrg[] = $n + ['inst' => $i];
    if ($i['demo']) $abDemo = true;
}
ksort($abBairros);
usort($abUrg, function ($a, $b) { return [$a['prazo'] === null, $a['prazo']] <=> [$b['prazo'] === null, $b['prazo']]; });
$abPins = array_values(array_filter(array_map(function ($i) {
    return $i['lat'] !== null ? ['n' => $i['nome'], 's' => $i['slug'], 'lat' => (float) $i['lat'], 'lng' => (float) $i['lng'], 'c' => ab_causa($i['causa']), 'b' => $i['bairro'], 'l' => ab_img($i['logo'])] : null;
}, $abInsts)));
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<main class="ab">
<section class="ph-hero ab-hero">
  <div class="hero-bg" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
  <div class="wrap ph-hero-in">
    <span class="chip" data-h><i></i>Corrente do bem em Nova Friburgo</span>
    <h1 class="h1" data-split><span class="scr">Amigos do Bem<svg viewBox="0 0 300 26" preserveAspectRatio="none" aria-hidden="true"><path d="M4 15C62 4 124 6 182 10s86 0 114 3"/><path d="M34 22c58-6 132-6 214-2"/></svg><svg class="spark" viewBox="0 0 40 40" aria-hidden="true"><path d="M8 30L4 12"/><path d="M20 28l8-22"/><path d="M30 34l8-6"/></svg></span></h1>
    <p class="lead" data-h><?= e(ab_cfg('intro')) ?></p>
    <div class="ab-stats" data-h>
      <span><b><?= count($abInsts) ?></b> <?= count($abInsts) === 1 ? 'instituição' : 'instituições' ?></span>
      <span><b><?= count($abCount) ?></b> tipos de doação</span>
<?php if ($abUrg): ?>      <span class="ab-stats-urg"><b><?= count($abUrg) ?></b> <?= count($abUrg) === 1 ? 'pedido urgente' : 'pedidos urgentes' ?></span><?php endif; ?>
    </div>
  </div>
</section>

<section class="sec ab-pick-sec" id="doar">
  <div class="wrap">
<?php if ($abDemo): ?>
    <p class="ab-demo-note" role="note"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M108,84a16,16,0,1,1,16,16A16,16,0,0,1,108,84Zm128,44A108,108,0,1,1,128,20,108.12,108.12,0,0,1,236,128Zm-24,0a84,84,0,1,0-84,84A84.09,84.09,0,0,0,212,128Zm-72,36.68V132a20,20,0,0,0-20-20,12,12,0,0,0-4,23.32V168a20,20,0,0,0,20,20,12,12,0,0,0,4-23.32Z"/></svg><span>As instituições marcadas como <b>exemplo</b> são fictícias, criadas só para testar a página. Os contatos delas não funcionam.</span></p>
<?php endif; ?>
    <h2 class="h2">O que você quer doar?</h2>
    <div class="ab-tipos" role="group" aria-label="Filtrar por tipo de doação">
      <button type="button" class="ab-tipo is-on" data-tipo=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,72H180.92c.39-.33.79-.65,1.17-1A29.53,29.53,0,0,0,192,49.57,32.62,32.62,0,0,0,158.44,16,29.53,29.53,0,0,0,137,25.91a54.94,54.94,0,0,0-9,14.48,54.94,54.94,0,0,0-9-14.48A29.53,29.53,0,0,0,97.56,16,32.62,32.62,0,0,0,64,49.57,29.53,29.53,0,0,0,73.91,71c.38.33.78.65,1.17,1H40A16,16,0,0,0,24,88v32a16,16,0,0,0,16,16v64a16,16,0,0,0,16,16h60a4,4,0,0,0,4-4V120H40V88h80v32h16V88h80v32H136v92a4,4,0,0,0,4,4h60a16,16,0,0,0,16-16V136a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72ZM84.51,59a13.69,13.69,0,0,1-4.5-10A16.62,16.62,0,0,1,96.59,32h.49a13.69,13.69,0,0,1,10,4.5c8.39,9.48,11.35,25.2,12.39,34.92C109.71,70.39,94,67.43,84.51,59Zm87,0c-9.49,8.4-25.24,11.36-35,12.4C137.7,60.89,141,45.5,149,36.51a13.69,13.69,0,0,1,10-4.5h.49A16.62,16.62,0,0,1,176,49.08,13.69,13.69,0,0,1,171.49,59Z"/></svg><span>Tudo</span><small><?= count($abInsts) ?></small></button>
<?php foreach ($abTipos as $tid => $t): if (empty($abCount[$tid])) continue; ?>
      <button type="button" class="ab-tipo" data-tipo="<?= (int) $tid ?>"><?= icon($t['icone'], 'fill') ?><span><?= e($t['nome']) ?></span><small><?= (int) $abCount[$tid] ?></small></button>
<?php endforeach; ?>
    </div>
    <div class="ab-filters">
      <label class="ab-search"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232.49,215.51,185,168a92.12,92.12,0,1,0-17,17l47.53,47.54a12,12,0,0,0,17-17ZM44,112a68,68,0,1,1,68,68A68.07,68.07,0,0,1,44,112Z"/></svg><input type="search" id="ab-q" placeholder="Buscar por nome ou causa" aria-label="Buscar instituição"></label>
      <select id="ab-bairro" aria-label="Bairro"><option value="">Todos os bairros</option><?php foreach (array_keys($abBairros) as $b): ?><option><?= e($b) ?></option><?php endforeach; ?></select>
    </div>
  </div>
</section>

<?php if ($abUrg): ?>
<section class="ab-urg-sec" aria-labelledby="ab-urg-t">
  <div class="wrap">
    <div class="ab-urg-hd"><h2 class="ab-urg-t" id="ab-urg-t"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm-8,56a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm8,104a12,12,0,1,1,12-12A12,12,0,0,1,128,184Z"/></svg>Precisam agora</h2><p>Pedidos com prazo. Fale direto com a instituição.</p></div>
    <ul class="ab-urg" tabindex="0" aria-label="Pedidos urgentes">
<?php foreach (array_slice($abUrg, 0, 12) as $n): ?>
      <li><a href="<?= e(url('/amigos-do-bem/' . $n['inst']['slug'] . '/')) ?>">
        <b><?= e($n['item']) ?></b><?php if ($n['quantidade']): ?><span class="ab-urg-q"><?= e($n['quantidade']) ?></span><?php endif; ?>
        <small><?= e($n['inst']['nome']) ?><?= $n['prazo'] ? ' · <em>' . e(ab_prazo($n['prazo'])) . '</em>' : '' ?></small></a></li>
<?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="sec ab-list-sec" id="instituicoes">
  <div class="wrap">
    <p class="ab-count" id="ab-count" aria-live="polite"></p>
<?php if (!$abInsts): ?>
    <p class="ab-empty">Nenhuma instituição publicada ainda.</p>
<?php else: ?>
    <ul class="ab-cards">
<?php foreach ($abInsts as $i): $tids = array_map(function ($t) { return (int) $t['id']; }, $i['tipos']); $wa = ab_wa($i['whatsapp']); ?>
      <li class="ab-card" data-tipos=",<?= implode(',', $tids) ?>," data-bairro="<?= e($i['bairro']) ?>" data-q="<?= e(mb_strtolower($i['nome'] . ' ' . ab_causa($i['causa']) . ' ' . $i['resumo'], 'UTF-8')) ?>">
        <a class="ab-card-img" href="<?= e(url('/amigos-do-bem/' . $i['slug'] . '/')) ?>" tabindex="-1" aria-hidden="true">
          <?php if ($i['capa']): ?><img src="<?= e(ab_img($i['capa'])) ?>" alt="" loading="lazy" width="800" height="450"><?php endif; ?>
          <?php if ($i['demo']): ?><span class="ab-demo">Exemplo</span><?php endif; ?>
        </a>
        <div class="ab-card-bd">
          <div class="ab-card-hd">
            <?php if ($i['logo']): ?><img class="ab-logo" src="<?= e(ab_img($i['logo'])) ?>" alt="" width="56" height="56" loading="lazy"><?php endif; ?>
            <div><span class="ab-causa"><?= e(ab_causa($i['causa'])) ?></span><h3><a href="<?= e(url('/amigos-do-bem/' . $i['slug'] . '/')) ?>"><?= e($i['nome']) ?></a></h3></div>
          </div>
          <p class="ab-resumo"><?= e($i['resumo']) ?></p>
          <p class="ab-local"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,60a44,44,0,1,0,44,44A44.05,44.05,0,0,0,128,60Zm0,64a20,20,0,1,1,20-20A20,20,0,0,1,128,124Zm0-112a92.1,92.1,0,0,0-92,92c0,77.36,81.64,135.4,85.12,137.83a12,12,0,0,0,13.76,0,259,259,0,0,0,42.18-39C205.15,170.57,220,136.37,220,104A92.1,92.1,0,0,0,128,12Zm31.3,174.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104a68,68,0,0,1,136,0C196,137.31,176,167.37,159.3,186.71Z"/></svg><?= e($i['bairro'] ?: 'Nova Friburgo') ?><?= $i['busca_em_casa'] ? ' · <span class="ab-busca"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M255.14,115.54l-14-35A19.89,19.89,0,0,0,222.58,68H196V64a12,12,0,0,0-12-12H32A20,20,0,0,0,12,72V184a20,20,0,0,0,20,20H46.06a36,36,0,0,0,67.88,0h44.12a36,36,0,0,0,67.88,0H236a20,20,0,0,0,20-20V120A21.7,21.7,0,0,0,255.14,115.54ZM196,92h23.88l6.4,16H196ZM80,204a12,12,0,1,1,12-12A12,12,0,0,1,80,204Zm92-41.92A36.32,36.32,0,0,0,158.06,180H113.94a36,36,0,0,0-67.88,0H36V140H172ZM172,116H36V76H172Zm20,88a12,12,0,1,1,12-12A12,12,0,0,1,192,204Zm40-24h-6.06A36.09,36.09,0,0,0,196,156.23V132h36Z"/></svg>Busca em casa</span>' : '' ?></p>
          <ul class="ab-ticons" aria-label="Aceita">
<?php foreach ($i['tipos'] as $t): ?>            <li title="<?= e($t['nome']) ?>"><?= icon($t['icone'], 'fill') ?><span class="sr"><?= e($t['nome']) ?></span></li>
<?php endforeach; ?>
          </ul>
<?php if ($i['necessidades']): ?>          <p class="ab-card-urg"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm-8,56a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm8,104a12,12,0,1,1,12-12A12,12,0,0,1,128,184Z"/></svg>Precisa agora: <?= e($i['necessidades'][0]['item']) ?><?= count($i['necessidades']) > 1 ? ' e mais ' . (count($i['necessidades']) - 1) : '' ?></p><?php endif; ?>
          <div class="ab-card-ft">
            <a class="btn btn--navy btn--sm" href="<?= e(url('/amigos-do-bem/' . $i['slug'] . '/')) ?>"><span>Como doar</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a>
<?php if ($wa && !$i['demo']): ?>            <a class="ab-wa" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Olá! Vi a ' . $i['nome'] . ' no site Amigos do Bem e quero doar.') ?>" target="_blank" rel="noopener" data-ab-wa="<?= e($i['slug']) ?>" aria-label="WhatsApp de <?= e($i['nome']) ?>"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M152.58,145.23l23,11.48A24,24,0,0,1,152,176a72.08,72.08,0,0,1-72-72A24,24,0,0,1,99.29,80.46l11.48,23L101,118a8,8,0,0,0-.73,7.51,56.47,56.47,0,0,0,30.15,30.15A8,8,0,0,0,138,155ZM232,128A104,104,0,0,1,79.12,219.82L45.07,231.17a16,16,0,0,1-20.24-20.24l11.35-34.05A104,104,0,1,1,232,128Zm-40,24a8,8,0,0,0-4.42-7.16l-32-16a8,8,0,0,0-8,.5l-14.69,9.8a40.55,40.55,0,0,1-16-16l9.8-14.69a8,8,0,0,0,.5-8l-16-32A8,8,0,0,0,104,64a40,40,0,0,0-40,40,88.1,88.1,0,0,0,88,88A40,40,0,0,0,192,152Z"/></svg></a><?php endif; ?>
          </div>
        </div>
      </li>
<?php endforeach; ?>
    </ul>
    <p class="ab-empty" id="ab-none" hidden>Nenhuma instituição com esses filtros. <button type="button" class="link" id="ab-clear">Limpar filtros</button></p>
<?php endif; ?>
  </div>
</section>

<?php if ($abPins): ?>
<section class="sec sec--mist ab-map-sec" id="mapa">
  <div class="wrap">
    <div class="sec-hd"><h2 class="h2">No mapa</h2></div>
    <div id="ab-map" class="ab-map" role="region" aria-label="Mapa das instituições"></div>
  </div>
</section>
<?php endif; ?>

<section class="sec ab-join" id="participar">
  <div class="wrap ab-join-in">
    <div>
      <h2 class="h2">Sua instituição quer participar?</h2>
      <p class="lead">A participação é gratuita. Mande os dados e nossa equipe entra em contato para conferir as informações antes de publicar.</p>
      <ul class="ab-how">
        <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M225.86,102.82c-3.77-3.94-7.67-8-9.14-11.57-1.36-3.27-1.44-8.69-1.52-13.94-.15-9.76-.31-20.82-8-28.51s-18.75-7.85-28.51-8c-5.25-.08-10.67-.16-13.94-1.52-3.56-1.47-7.63-5.37-11.57-9.14C146.28,23.51,138.44,16,128,16s-18.27,7.51-25.18,14.14c-3.94,3.77-8,7.67-11.57,9.14C88,40.64,82.56,40.72,77.31,40.8c-9.76.15-20.82.31-28.51,8S41,67.55,40.8,77.31c-.08,5.25-.16,10.67-1.52,13.94-1.47,3.56-5.37,7.63-9.14,11.57C23.51,109.72,16,117.56,16,128s7.51,18.27,14.14,25.18c3.77,3.94,7.67,8,9.14,11.57,1.36,3.27,1.44,8.69,1.52,13.94.15,9.76.31,20.82,8,28.51s18.75,7.85,28.51,8c5.25.08,10.67.16,13.94,1.52,3.56,1.47,7.63,5.37,11.57,9.14C109.72,232.49,117.56,240,128,240s18.27-7.51,25.18-14.14c3.94-3.77,8-7.67,11.57-9.14,3.27-1.36,8.69-1.44,13.94-1.52,9.76-.15,20.82-.31,28.51-8s7.85-18.75,8-28.51c.08-5.25.16-10.67,1.52-13.94,1.47-3.56,5.37-7.63,9.14-11.57C232.49,146.28,240,138.44,240,128S232.49,109.73,225.86,102.82Zm-52.2,6.84-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35a8,8,0,0,1,11.32,11.32Z"/></svg><span>Só entram instituições conferidas pelo gabinete, com autorização para divulgar.</span></li>
        <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.33,141.06a24.34,24.34,0,0,0-18.61-4.77C230.5,117.33,240,98.48,240,80c0-26.47-21.29-48-47.46-48A47.58,47.58,0,0,0,156,48.75,47.58,47.58,0,0,0,119.46,32C93.29,32,72,53.53,72,80c0,11,3.24,21.69,10.06,33a31.87,31.87,0,0,0-14.75,8.4L44.69,144H16A16,16,0,0,0,0,160v40a16,16,0,0,0,16,16H120a7.93,7.93,0,0,0,1.94-.24l64-16a6.94,6.94,0,0,0,1.19-.4L226,182.82l.44-.2a24.6,24.6,0,0,0,3.93-41.56Zm-10.9,27.15-38,16.18L119,200H56V155.31l22.63-22.62A15.86,15.86,0,0,1,89.94,128H140a12,12,0,0,1,0,24H112a8,8,0,0,0,0,16h32a8.32,8.32,0,0,0,1.79-.2l67-15.41.31-.08a8.6,8.6,0,0,1,6.3,15.9Z"/></svg><span>A doação é feita direto à instituição. Nada passa pelo gabinete.</span></li>
        <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224,48V96a8,8,0,0,1-8,8H168a8,8,0,0,1-5.66-13.66L180.65,72a79.48,79.48,0,0,0-54.72-22.09h-.45A79.52,79.52,0,0,0,69.59,72.71,8,8,0,0,1,58.41,61.27,96,96,0,0,1,192,60.7l18.36-18.36A8,8,0,0,1,224,48ZM186.41,183.29A80,80,0,0,1,75.35,184l18.31-18.31A8,8,0,0,0,88,152H40a8,8,0,0,0-8,8v48a8,8,0,0,0,13.66,5.66L64,195.3a95.42,95.42,0,0,0,66,26.76h.53a95.36,95.36,0,0,0,67.07-27.33,8,8,0,0,0-11.18-11.44Z"/></svg><span>A instituição recebe um link para atualizar o que está precisando, sem senha.</span></li>
      </ul>
    </div>
    <form class="ab-form" id="ab-join-form" novalidate>
      <input type="hidden" name="t" value="">
      <div class="fv-hp" aria-hidden="true"><label>Site <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="fd"><label for="abj-nome">Nome da instituição</label><input id="abj-nome" name="nome" maxlength="160" required></div>
      <div class="fd"><label for="abj-causa">Quem vocês atendem</label><select id="abj-causa" name="causa"><?php foreach (AB_CAUSAS as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?></select></div>
      <div class="fd"><label for="abj-bairro">Bairro</label><input id="abj-bairro" name="bairro" maxlength="120"></div>
      <div class="fd"><label for="abj-resp">Seu nome</label><input id="abj-resp" name="responsavel" maxlength="120" required autocomplete="name"></div>
      <div class="fd"><label for="abj-tel">WhatsApp</label><input id="abj-tel" name="whatsapp" maxlength="30" required inputmode="tel" autocomplete="tel" placeholder="(22) 99999-9999"></div>
      <div class="fd"><label for="abj-msg">O que vocês mais precisam receber?</label><textarea id="abj-msg" name="mensagem" maxlength="1200" rows="3"></textarea></div>
      <p class="ab-form-msg" id="ab-join-msg" role="alert" hidden></p>
      <button class="btn btn--orange" type="submit"><span>Quero participar</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg></span></button>
    </form>
  </div>
</section>

<section class="ab-disclaimer">
  <div class="wrap"><p>O gabinete do vereador Maicon Gonçalves apenas divulga as instituições. Nenhuma doação passa pelo gabinete: tudo é combinado e entregue direto à instituição. Confira sempre o nome do titular antes de fazer um PIX.</p></div>
</section>
</main>
<?php if ($abPins): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<?php endif; ?>
<script>
(function () {
  var cards = [].slice.call(document.querySelectorAll('.ab-card')), tipo = '', q = document.getElementById('ab-q'), bairro = document.getElementById('ab-bairro');
  var count = document.getElementById('ab-count'), none = document.getElementById('ab-none');
  var fold = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };
  function apply() {
    var t = fold(q ? q.value.trim() : ''), b = bairro ? bairro.value : '', n = 0;
    cards.forEach(function (c) {
      var ok = (!tipo || c.getAttribute('data-tipos').indexOf(',' + tipo + ',') > -1) && (!b || c.getAttribute('data-bairro') === b) && (!t || fold(c.getAttribute('data-q')).indexOf(t) > -1);
      c.hidden = !ok; if (ok) n++;
    });
    if (count) count.textContent = n === cards.length ? '' : n + (n === 1 ? ' instituição encontrada' : ' instituições encontradas');
    if (none) none.hidden = n > 0;
  }
  [].forEach.call(document.querySelectorAll('.ab-tipo'), function (btn) {
    btn.setAttribute('aria-pressed', btn.classList.contains('is-on') ? 'true' : 'false');
    btn.addEventListener('click', function () {
      [].forEach.call(document.querySelectorAll('.ab-tipo'), function (o) { o.classList.remove('is-on'); o.setAttribute('aria-pressed', 'false'); });
      btn.classList.add('is-on'); btn.setAttribute('aria-pressed', 'true');
      tipo = btn.getAttribute('data-tipo'); apply();
      if (tipo) document.getElementById('instituicoes').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
  if (q) q.addEventListener('input', apply);
  if (bairro) bairro.addEventListener('change', apply);
  var clr = document.getElementById('ab-clear');
  if (clr) clr.addEventListener('click', function () { q.value = ''; bairro.value = ''; document.querySelector('.ab-tipo[data-tipo=""]').click(); });

  var base = document.documentElement.getAttribute('data-base') || '';
  [].forEach.call(document.querySelectorAll('[data-ab-wa]'), function (a) {
    a.addEventListener('click', function () { try { navigator.sendBeacon(base + '/api/amigos-clique.php', new Blob(['slug=' + encodeURIComponent(a.getAttribute('data-ab-wa'))], { type: 'application/x-www-form-urlencoded' })); } catch (e) {} });
  });

  var el = document.getElementById('ab-map'), pins = <?= json_encode($abPins, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  if (el && window.L && pins.length) {
    var m = L.map(el, { scrollWheelZoom: false }).setView([-22.2819, -42.5311], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(m);
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; };
    var bounds = [];
    pins.forEach(function (p) {
      var icon = L.divIcon({ className: 'ab-pin', html: p.l ? '<img src="' + esc(p.l) + '" alt="">' : '<span></span>', iconSize: [42, 42], iconAnchor: [21, 42], popupAnchor: [0, -38] });
      L.marker([p.lat, p.lng], { icon: icon, title: p.n }).addTo(m).bindPopup('<b>' + esc(p.n) + '</b><br><small>' + esc(p.c) + (p.b ? ' · ' + esc(p.b) : '') + '</small><br><a href="' + base + '/amigos-do-bem/' + esc(p.s) + '/">Como doar →</a>');
      bounds.push([p.lat, p.lng]);
    });
    if (bounds.length > 1) m.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
  }

  var f = document.getElementById('ab-join-form'), msg = document.getElementById('ab-join-msg');
  if (f) {
    f.t.value = Date.now();
    f.addEventListener('submit', function (ev) {
      ev.preventDefault();
      if (!f.checkValidity()) { f.reportValidity(); return; }
      var b = f.querySelector('button[type=submit]'); b.disabled = true;
      fetch(base + '/api/amigos-participar.php', { method: 'POST', body: new FormData(f), credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
        msg.hidden = false; msg.className = 'ab-form-msg' + (j.ok ? ' is-ok' : '');
        msg.textContent = j.ok ? 'Recebemos! Nossa equipe vai falar com você pelo WhatsApp para conferir os dados.' : (j.error || 'Não foi possível enviar agora.');
        if (j.ok) f.reset();
      }).catch(function () { msg.hidden = false; msg.className = 'ab-form-msg'; msg.textContent = 'Sem conexão no momento. Tente de novo.'; })
        .then(function () { b.disabled = false; });
    });
  }
})();
</script>

<div class="ft-top on-white" aria-hidden="true">
  <svg viewBox="0 0 1440 200" preserveAspectRatio="xMidYMax slice">
    <circle class="mt-sun" cx="1080" cy="78" r="38" fill="url(#g-or)"/>
    <path d="M0 200V120l120-50 90 30 120-80 90 60 100-35 120 65 120-100 90 60 110-40 120 70 120-50 120 50 120-40v140z" fill="url(#g-m1)"/>
    <path d="M0 200v-56l90-30 110 30 100-60 110 50 130-40 120 55 140-65 130 55 130-45 130 50 120-35 130 30v61z" fill="url(#g-m2)"/>
    <path d="M0 200v-28l140-25 140 30 150-35 170 38 170-33 170 31 170-28 170 30 160-23v43z" fill="#041C4B"/>
  </svg>
</div>
<footer class="ft">
  <div class="wrap">
    <div class="ft-grid">
      <div>
        <img class="ft-logo" src="<?= B ?>/img/logo.webp" alt="Vereador Maicon Gonçalves" width="640" height="293" loading="lazy">
        <p>Mandato presente nas comunidades, ouvindo as pessoas e trabalhando por soluções reais.</p>
      </div>
      <div>
        <div class="ft-h">Navegação</div>
        <nav class="ft-nav" aria-label="Rodapé">
          <a href="<?= B ?>/">Início</a><a href="<?= B ?>/minha-historia/">Minha história</a><a href="<?= B ?>/fl360/">FL360</a><a href="<?= B ?>/descobrindo-talentos/">Peneira</a><a href="<?= B ?>/noticias/">Notícias</a><a href="<?= B ?>/friburgo-no-mapa/">Friburgo no Mapa</a>
        </nav>
      </div>
      <div>
        <div class="ft-h">Contato</div>
        <ul class="ft-c">
          <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,64a40,40,0,1,0,40,40A40,40,0,0,0,128,64Zm0,64a24,24,0,1,1,24-24A24,24,0,0,1,128,128Zm0-112a88.1,88.1,0,0,0-88,88c0,31.4,14.51,64.68,42,96.25a254.19,254.19,0,0,0,41.45,38.3,8,8,0,0,0,9.18,0A254.19,254.19,0,0,0,174,200.25c27.45-31.57,42-64.85,42-96.25A88.1,88.1,0,0,0,128,16Zm0,206c-16.53-13-72-60.75-72-118a72,72,0,0,1,144,0C200,161.23,144.53,209,128,222Z"/></svg><span><?= implode('<br>', array_map('e', address_lines())) ?></span></li>
          <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,216H208V40a16,16,0,0,0-16-16H64A16,16,0,0,0,48,40V216H24a8,8,0,0,0,0,16H232a8,8,0,0,0,0-16Zm-40,0H176V40h16ZM64,40h96V216H64Zm80,92a12,12,0,1,1-12-12A12,12,0,0,1,144,132Z"/></svg><span><?= e(S('office')) ?></span></li>
          <li><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224,48H32a8,8,0,0,0-8,8V192a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A8,8,0,0,0,224,48ZM203.43,64,128,133.15,52.57,64ZM216,192H40V74.19l82.59,75.71a8,8,0,0,0,10.82,0L216,74.19V192Z"/></svg><span><span class="sel"><?= e(S('email')) ?></span> <button class="copy" data-copy="<?= e(S('email')) ?>" aria-label="Copiar e-mail"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,32H88a8,8,0,0,0-8,8V80H40a8,8,0,0,0-8,8V216a8,8,0,0,0,8,8H168a8,8,0,0,0,8-8V176h40a8,8,0,0,0,8-8V40A8,8,0,0,0,216,32ZM160,208H48V96H160Zm48-48H176V88a8,8,0,0,0-8-8H96V48H208Z"/></svg></button></span></li>
        </ul>
      </div>
      <div>
        <div class="ft-h">Redes sociais</div>
        <div class="ft-soc">
          <?= social_links() ?>
        </div>
      </div>
    </div>
    <div class="ft-end"><span>© <?= date('Y') ?> Vereador Maicon Gonçalves. Todos os direitos reservados.</span><a href="<?= B ?>/privacidade/">Política de privacidade</a></div>
  </div>
</footer>
<a class="wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><b><svg class="ic" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M26.576 5.363c-2.69-2.69-6.406-4.354-10.511-4.354-8.209 0-14.865 6.655-14.865 14.865 0 2.732 0.737 5.291 2.022 7.491l-0.038-0.070-2.109 7.702 7.879-2.067c2.051 1.139 4.498 1.809 7.102 1.809h0.006c8.209-0.003 14.862-6.659 14.862-14.868 0-4.103-1.662-7.817-4.349-10.507l0 0zM16.062 28.228h-0.005c-0 0-0.001 0-0.001 0-2.319 0-4.489-0.64-6.342-1.753l0.056 0.031-0.451-0.267-4.675 1.227 1.247-4.559-0.294-0.467c-1.185-1.862-1.889-4.131-1.889-6.565 0-6.822 5.531-12.353 12.353-12.353s12.353 5.531 12.353 12.353c0 6.822-5.53 12.353-12.353 12.353h-0zM22.838 18.977c-0.371-0.186-2.197-1.083-2.537-1.208-0.341-0.124-0.589-0.185-0.837 0.187-0.246 0.371-0.958 1.207-1.175 1.455-0.216 0.249-0.434 0.279-0.805 0.094-1.15-0.466-2.138-1.087-2.997-1.852l0.010 0.009c-0.799-0.74-1.484-1.587-2.037-2.521l-0.028-0.052c-0.216-0.371-0.023-0.572 0.162-0.757 0.167-0.166 0.372-0.434 0.557-0.65 0.146-0.179 0.271-0.384 0.366-0.604l0.006-0.017c0.043-0.087 0.068-0.188 0.068-0.296 0-0.131-0.037-0.253-0.101-0.357l0.002 0.003c-0.094-0.186-0.836-2.014-1.145-2.758-0.302-0.724-0.609-0.625-0.836-0.637-0.216-0.010-0.464-0.012-0.712-0.012-0.395 0.010-0.746 0.188-0.988 0.463l-0.001 0.002c-0.802 0.761-1.3 1.834-1.3 3.023 0 0.026 0 0.053 0.001 0.079l-0-0.004c0.131 1.467 0.681 2.784 1.527 3.857l-0.012-0.015c1.604 2.379 3.742 4.282 6.251 5.564l0.094 0.043c0.548 0.248 1.25 0.513 1.968 0.74l0.149 0.041c0.442 0.14 0.951 0.221 1.479 0.221 0.303 0 0.601-0.027 0.889-0.078l-0.031 0.004c1.069-0.223 1.956-0.868 2.497-1.749l0.009-0.017c0.165-0.366 0.261-0.793 0.261-1.242 0-0.185-0.016-0.366-0.047-0.542l0.003 0.019c-0.092-0.155-0.34-0.247-0.712-0.434z"/></svg></b><span>Fale comigo!</span></a>
<div class="ck" id="ck" role="dialog" aria-live="polite" aria-label="Aviso de cookies" hidden>
  <p>Usamos cookies essenciais para o site funcionar e o Google Analytics para medir quantas pessoas visitam o site, sem publicidade. Saiba mais na <a href="<?= B ?>/privacidade/">Política de privacidade</a>.</p>
  <button type="button" class="btn btn--orange btn--sm" id="ck-ok"><span>Aceitar</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"/></svg></span></button>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= B ?>/site.js?v=01a3b9d2"></script>

</body>
</html>
