<?php
defined('APP') or exit;
$meta = ($meta ?? []) + ['title' => 'Friburgo no Mapa – envie sua demanda | Vereador Maicon Gonçalves', 'description' => 'Envie sua demanda ao nosso gabinete. Nós conferimos, encaminhamos oficialmente à Prefeitura de Nova Friburgo e você acompanha tudo em tempo real no mapa.', 'page' => 'demandas'];
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
<link rel="stylesheet" href="<?= B ?>/site.css?v=bb278261">
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
    <a class="hd-cta btn btn--orange btn--sm is-on" href="<?= B ?>/friburgo-no-mapa/"><span>Friburgo no Mapa</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M231.38,46.54a12,12,0,0,0-10.29-2.18L161.4,59.28l-60-30a12,12,0,0,0-8.28-.91l-64,16A12,12,0,0,0,20,56V200a12,12,0,0,0,14.91,11.64L94.6,196.72l60,30a12,12,0,0,0,8.28.91l64-16A12,12,0,0,0,236,200V56A12,12,0,0,0,231.38,46.54ZM108,59.42l40,20V196.58l-40-20Zm-64,6,40-10V174.63l-40,10ZM212,190.63l-40,10V81.37l40-10Z"/></svg></span></a>
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
$items = tickets_public();
$waiting = array_values(array_filter($items, function ($t) { return $t['status'] === 'publicado'; }));
$solved = array_values(array_filter($items, function ($t) { return $t['status'] === 'resolvido'; }));
$avg = $solved ? (int) round(array_sum(array_column($solved, 'days')) / count($solved)) : null;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.min.css">
<main class="rd">
<section class="ph-hero ph-hero--c rd-hero">
  <div class="hero-bg" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
  <div class="wrap ph-hero-in">
    <span class="chip" data-h><i></i>Fiscalização cidadã em Nova Friburgo</span>
    <h1 class="h1" data-split><span class="scr">Friburgo no Mapa<svg viewBox="0 0 300 26" preserveAspectRatio="none" aria-hidden="true"><path d="M4 15C62 4 124 6 182 10s86 0 114 3"/><path d="M34 22c58-6 132-6 214-2"/></svg><svg class="spark" viewBox="0 0 40 40" aria-hidden="true"><path d="M8 30L4 12"/><path d="M20 28l8-22"/><path d="M30 34l8-6"/></svg></span></h1>
    <p class="lead" data-h>Você envia a demanda, nosso gabinete confere e encaminha oficialmente para a Prefeitura. Depois, você acompanha tudo em&nbsp;tempo&nbsp;real!</p>
    <div class="rd-stats" data-h>
      <div><b><?= count($waiting) ?></b><span>aguardando a Prefeitura</span></div>
      <div><b><?= count($solved) ?></b><span>resolvidos</span></div>
      <div><b><?= $avg !== null ? $avg . ' d' : '—' ?></b><span>tempo médio para resolver</span></div>
    </div>
    <a class="btn btn--orange" href="#registrar" data-h data-mag><span>Enviar demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208,52H182.42L170,33.34A12,12,0,0,0,160,28H96a12,12,0,0,0-10,5.34L73.57,52H48A28,28,0,0,0,20,80V192a28,28,0,0,0,28,28H208a28,28,0,0,0,28-28V80A28,28,0,0,0,208,52Zm4,140a4,4,0,0,1-4,4H48a4,4,0,0,1-4-4V80a4,4,0,0,1,4-4H80a12,12,0,0,0,10-5.34L102.42,52h51.15L166,70.66A12,12,0,0,0,176,76h32a4,4,0,0,1,4,4ZM128,84a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,84Zm0,72a24,24,0,1,1,24-24A24,24,0,0,1,128,156Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208,52H182.42L170,33.34A12,12,0,0,0,160,28H96a12,12,0,0,0-10,5.34L73.57,52H48A28,28,0,0,0,20,80V192a28,28,0,0,0,28,28H208a28,28,0,0,0,28-28V80A28,28,0,0,0,208,52Zm4,140a4,4,0,0,1-4,4H48a4,4,0,0,1-4-4V80a4,4,0,0,1,4-4H80a12,12,0,0,0,10-5.34L102.42,52h51.15L166,70.66A12,12,0,0,0,176,76h32a4,4,0,0,1,4,4ZM128,84a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,84Zm0,72a24,24,0,1,1,24-24A24,24,0,0,1,128,156Z"/></svg></span></a>
  </div>
</section>

<section class="sec rd-map-sec" id="mapa">
  <div class="wrap">
    <div class="rd-tools">
      <div class="filters" role="group" aria-label="Filtrar por situação">
        <button class="fl" aria-pressed="true" data-st="">Todos</button>
        <button class="fl" aria-pressed="false" data-st="publicado"><i class="rd-dot rd-dot--wait"></i>Aguardando</button>
        <button class="fl" aria-pressed="false" data-st="resolvido"><i class="rd-dot rd-dot--ok"></i>Resolvidos</button>
      </div>
      <label class="rd-sel"><span class="sr">Tipo de problema</span><select id="rd-cat"><option value="">Todos os tipos</option>
<?php $used = array_unique(array_column($items, 'cat')); foreach (ticket_cats(true) as $k => $v): if (!in_array($k, $used, true)) continue; ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
      </select></label>
    </div>
    <div class="rd-mapbox">
      <div id="rd-map" class="rd-map" role="application" aria-label="Friburgo no Mapa: demandas registradas em Nova Friburgo"></div>
      <p class="rd-empty" id="rd-empty"<?= $items ? ' hidden' : '' ?>>Ainda não há demandas publicadas no mapa. <a href="#registrar">Envie a primeira</a>.</p>
      <article class="rd-card" id="rd-card" hidden aria-live="polite"></article>
    </div>
    <p class="rd-legend"><span><i class="rd-dot rd-dot--wait"></i>Aguardando a Prefeitura</span><span><i class="rd-dot rd-dot--ok"></i>Resolvido</span><span>O tempo começa a contar quando nosso gabinete leva o problema à Prefeitura.</span></p>
  </div>
</section>

<section class="sec sec--mist">
  <div class="wrap">
    <div class="sec-hd"><h2 class="h2">Como funciona</h2></div>
    <ol class="steps rd-steps">
      <li class="step"><span class="step-i" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208,56H180.28L166.65,35.56A8,8,0,0,0,160,32H96a8,8,0,0,0-6.65,3.56L75.71,56H48A24,24,0,0,0,24,80V192a24,24,0,0,0,24,24H208a24,24,0,0,0,24-24V80A24,24,0,0,0,208,56Zm8,136a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V80a8,8,0,0,1,8-8H80a8,8,0,0,0,6.66-3.56L100.28,48h55.43l13.63,20.44A8,8,0,0,0,176,72h32a8,8,0,0,1,8,8ZM128,88a44,44,0,1,0,44,44A44.05,44.05,0,0,0,128,88Zm0,72a28,28,0,1,1,28-28A28,28,0,0,1,128,160Z"/></svg></span><h3>Você envia</h3><p>Tira a foto, marca o local no mapa e conta o que está acontecendo.</p></li>
      <li class="step"><span class="step-i" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M140,128a12,12,0,1,1-12-12A12,12,0,0,1,140,128ZM84,116a12,12,0,1,0,12,12A12,12,0,0,0,84,116Zm88,0a12,12,0,1,0,12,12A12,12,0,0,0,172,116Zm60,12A104,104,0,0,1,79.12,219.82L45.07,231.17a16,16,0,0,1-20.24-20.24l11.35-34.05A104,104,0,1,1,232,128Zm-16,0A88,88,0,1,0,51.81,172.06a8,8,0,0,1,.66,6.54L40,216,77.4,203.53a7.85,7.85,0,0,1,2.53-.42,8,8,0,0,1,4,1.08A88,88,0,0,0,216,128Z"/></svg></span><h3>Nosso gabinete confere</h3><p>Nossa equipe fala com você, entende o problema e prepara o pedido.</p></li>
      <li class="step"><span class="step-i" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M24,104H48v64H32a8,8,0,0,0,0,16H224a8,8,0,0,0,0-16H208V104h24a8,8,0,0,0,4.19-14.81l-104-64a8,8,0,0,0-8.38,0l-104,64A8,8,0,0,0,24,104Zm40,0H96v64H64Zm80,0v64H112V104Zm48,64H160V104h32ZM128,41.39,203.74,88H52.26ZM248,208a8,8,0,0,1-8,8H16a8,8,0,0,1,0-16H240A8,8,0,0,1,248,208Z"/></svg></span><h3>Vai para a Prefeitura</h3><p>O pedido é enviado à secretaria responsável e o problema entra no mapa.</p></li>
      <li class="step"><span class="step-i" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z"/></svg></span><h3>Todo mundo acompanha</h3><p>O contador mostra há quantos dias a Prefeitura foi avisada, até a solução.</p></li>
    </ol>
  </div>
</section>

<section class="sec" id="registrar">
  <div class="wrap rd-form-wrap">
    <div class="stackv rd-form-hd">
      <h2 class="h2">Enviar demanda</h2>
      <p class="lead">Leva menos de dois minutos. Seu telefone e seu e-mail nunca aparecem no site.</p>
    </div>
    <form class="form rd-form" id="rd-form" action="<?= B ?>/api/demandas.php" method="post" enctype="multipart/form-data" novalidate>
      <div class="fd fd--full"><span class="rd-n">1</span><label for="rd-fotos">Fotos ou vídeo do problema <small>(até 3 fotos e 1 vídeo de até 1 minuto)</small></label>
        <label class="up-drop" id="rd-drop"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M172,136a12,12,0,0,1-12,12H140v20a12,12,0,0,1-24,0V148H96a12,12,0,0,1,0-24h20V104a12,12,0,0,1,24,0v20h20A12,12,0,0,1,172,136Zm64-56V192a28,28,0,0,1-28,28H48a28,28,0,0,1-28-28V80A28,28,0,0,1,48,52H73.58L83.64,36.91A20,20,0,0,1,100.28,28h55.44a20,20,0,0,1,16.64,8.91L182.42,52H208A28,28,0,0,1,236,80Zm-24,0a4,4,0,0,0-4-4H176a12,12,0,0,1-10-5.34L153.58,52H102.42L90,70.66A12,12,0,0,1,80,76H48a4,4,0,0,0-4,4V192a4,4,0,0,0,4,4H208a4,4,0,0,0,4-4Z"/></svg><span>Toque para tirar ou escolher fotos ou vídeo</span><input id="rd-fotos" type="file" accept="image/*,video/*" multiple></label>
        <div class="ups" id="rd-ups"></div></div>

      <div class="fd fd--full"><span class="rd-n">2</span><span class="lbl" id="rd-where">Onde fica?</span>
        <button type="button" class="geo" id="rd-geo"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M238.7,102.46,62.81,37.21l-.25-.09A20,20,0,0,0,37.12,62.56l.09.25L102.46,238.7A20,20,0,0,0,121.3,252h.35a20,20,0,0,0,18.77-14.12l.09-.29,21.23-75.85,75.85-21.23.29-.09a20,20,0,0,0,.82-38Zm-89.93,38a12,12,0,0,0-8.32,8.32l-19.68,70.29L62.8,62.8l156.26,58Z"/></svg><span>Usar minha localização atual</span></button>
        <div class="rd-pick" id="rd-pick" role="application" aria-labelledby="rd-where"></div>
        <p class="rd-pick-st" id="rd-pick-st">Use sua localização ou toque no mapa para marcar o local exato.</p>
        <div class="rd-near" id="rd-near" role="status" aria-live="polite" hidden></div></div>
      <div class="fd"><label for="rd-rua">Rua e número</label><input id="rd-rua" name="endereco" type="text" maxlength="255" placeholder="Ex.: Rua Farinha Filho, 50"></div>
      <div class="fd"><label for="rd-bairro">Bairro / Localidade</label><select id="rd-bairro" name="bairro"><option value="">Qual bairro?</option><option>Alto dos 50</option><option>Amparo</option><option>Baixada de Salinas</option><option>Barracão dos Mendes</option><option>Benfica</option><option>Boa Esperança</option><option>Bela Vista</option><option>Braunes</option><option>Caledônia</option><option>Campo do Coelho</option><option>Canto do Riacho</option><option>Cardinot</option><option>Cascatinha</option><option>Catarcione</option><option>Centenário</option><option>Centro</option><option>Chácara do Paraíso</option><option>Cônego</option><option>Conselheiro Paulino</option><option>Conquista</option><option>Cordoeira</option><option>Córrego D’Antas</option><option>Curral do Sol</option><option>Da Graça</option><option>Debossan</option><option>Duas Pedras</option><option>Fazenda Bella Vista</option><option>Fazenda da Lage</option><option>Fazenda Rio Grande</option><option>Florândia da Serra</option><option>Floresta</option><option>Furnas</option><option>Galdinópolis</option><option>Girassol</option><option>Granja do Céu</option><option>Granja Mimosa</option><option>Granja Spinelli</option><option>Janela das Andorinhas</option><option>Jardim Califórnia</option><option>Jardim Marajói</option><option>Jardim Ouro Preto</option><option>Sans Souci</option><option>Jardinlândia</option><option>Lagoinha</option><option>Lazareto</option><option>Lumiar</option><option>Macaé de Cima</option><option>Marechal Rondon</option><option>Maria Teresa</option><option>Maringá</option><option>Mury</option><option>Nossa Senhora de Fátima</option><option>Nova Suíça</option><option>Olaria</option><option>Paissandu</option><option>Parque das Flores</option><option>Parque São Clemente</option><option>Perissê</option><option>Pilões</option><option>Ponte Branca</option><option>Ponte da Saudade</option><option>Ponte dos Alemães</option><option>Ponte Preta</option><option>Prado</option><option>Rio Bonito</option><option>Rio Grande de Cima</option><option>Riograndina</option><option>Rui Sanglard</option><option>Salinas</option><option>Santa Bernadete</option><option>Santa Cruz</option><option>Santa Terezinha</option><option>Santiago</option><option>Santo André</option><option>Santo Antônio</option><option>São Cristóvão</option><option>São Geraldo</option><option>São Jorge</option><option>São Lourenço</option><option>São Pedro da Serra</option><option>São Romão</option><option>Serra Nevada</option><option>Sítio São Luiz</option><option>Solares</option><option>Stucky</option><option>Bairro Suíço</option><option>Suspiro</option><option>Theodoro de Oliveira</option><option>Tinguely (ou Tingly)</option><option>Toca da Onça</option><option>Três Cachoeiras</option><option>Três Picos</option><option>Trilha do Céu</option><option>Terra Nova</option><option>Vargem Grande</option><option>Vale das Rosas</option><option>Vale dos Pinheiros</option><option>Varginha</option><option>Venda das Pedras</option><option>Vila Amélia</option><option>Vila Guarani</option><option>Vila Nova</option><option>Vila Rica</option><option>Vilage</option><option>Vargem Alta</option><option>Vale Radiante</option><option>Ypu</option></select></div>

      <div class="fd fd--full"><span class="rd-n">3</span><label for="rd-tipo">Tipo da demanda</label><select id="rd-tipo" name="categoria" required><option value="">Escolha o tipo</option>
<?php foreach (ticket_cats() as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
      </select></div>
      <div class="fd fd--full"><label for="rd-desc">O que está acontecendo?</label><textarea id="rd-desc" name="descricao" maxlength="1500" placeholder="Ex.: Buraco grande no meio da rua há três semanas, carros desviando pela calçada." required></textarea></div>

      <div class="fd"><span class="rd-n">4</span><label for="rd-nome">Seu nome</label><input id="rd-nome" name="nome" type="text" maxlength="120" autocomplete="name" placeholder="Nome e sobrenome"></div>
      <div class="fd"><label for="rd-zap">WhatsApp</label><input id="rd-zap" name="whatsapp" type="tel" maxlength="16" autocomplete="tel" placeholder="(22) 99999-9999" data-mask="phone" required></div>
      <div class="fd fd--full"><label for="rd-mail">E-mail <small>(para avisarmos quando a demanda for à Prefeitura e quando for resolvida)</small></label><input id="rd-mail" name="email" type="email" maxlength="190" autocomplete="email" inputmode="email" placeholder="seuemail@exemplo.com" required></div>
      <label class="rd-check fd--full"><input type="checkbox" name="anonimo" value="1" id="rd-anon"><span>Não mostrar meu nome no mapa <small>(nosso gabinete continua sabendo quem enviou, para falar com você)</small></span></label>
      <label class="rd-check fd--full"><input type="checkbox" name="novidades" value="1" id="rd-news"><span>Quero receber por e-mail as novidades do mandato, como novos artigos do site <small>(opcional; dá para sair a qualquer momento)</small></span></label>
      <label class="rd-check fd--full"><input type="checkbox" name="consentimento" value="1" id="rd-ok" required><span>Concordo que o gabinete use estes dados para encaminhar a demanda à Prefeitura e falar comigo. A foto, o local e a descrição podem ser publicados no mapa; meu telefone e meu e-mail, não. <a href="<?= B ?>/privacidade/" target="_blank">Política de privacidade</a>.</span></label>

      <input type="hidden" name="lat" id="rd-lat"><input type="hidden" name="lng" id="rd-lng"><input type="hidden" name="acc" id="rd-acc"><input type="hidden" name="geo_addr" id="rd-addr"><input type="hidden" name="geo_manual" id="rd-manual"><input type="hidden" name="t" id="rd-t">
      <div class="hp" aria-hidden="true"><label for="rd-site">Não preencha</label><input id="rd-site" name="website" type="text" tabindex="-1" autocomplete="off"></div>
      <p class="form-msg" id="rd-msg" role="status" hidden></p>
      <div class="form-ft">
        <button class="btn btn--orange" type="submit" id="rd-send" data-mag><span>ENVIAR DEMANDA</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg></span></button>
        <p>Depois de enviar, você recebe um número de protocolo.</p>
      </div>
    </form>
    <div class="rd-done" id="rd-done" hidden>
      <span class="rd-done-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm45.66,85.66-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35a8,8,0,0,1,11.32,11.32Z"/></svg></span>
      <h3 class="h3">Recebemos sua demanda!</h3>
      <p>Protocolo <b id="rd-code"></b>. Nossa equipe vai analisar e falar com você pelo WhatsApp. Quando o pedido for levado à Prefeitura, ele aparece no mapa e você recebe um aviso por e-mail.</p>
      <button type="button" class="btn btn--line btn--sm" id="rd-again"><span>Enviar outra demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M228,128a12,12,0,0,1-12,12H140v76a12,12,0,0,1-24,0V140H40a12,12,0,0,1,0-24h76V40a12,12,0,0,1,24,0v76h76A12,12,0,0,1,228,128Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M228,128a12,12,0,0,1-12,12H140v76a12,12,0,0,1-24,0V140H40a12,12,0,0,1,0-24h76V40a12,12,0,0,1,24,0v76h76A12,12,0,0,1,228,128Z"/></svg></span></button>
    </div>
  </div>
</section>
</main>
<dialog class="lb" id="rd-lb" aria-label="Foto ampliada"><div class="lb-in"><img src="" alt=""></div><button class="lb-x" aria-label="Fechar"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208.49,191.51a12,12,0,0,1-17,17L128,145,64.49,208.49a12,12,0,0,1-17-17L111,128,47.51,64.49a12,12,0,0,1,17-17L128,111l63.51-63.52a12,12,0,0,1,17,17L145,128Z"/></svg></button><button class="lb-p" aria-label="Foto anterior"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M168.49,199.51a12,12,0,0,1-17,17l-80-80a12,12,0,0,1,0-17l80-80a12,12,0,0,1,17,17L97,128Z"/></svg></button><button class="lb-n" aria-label="Próxima foto"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M184.49,136.49l-80,80a12,12,0,0,1-17-17L159,128,87.51,56.49a12,12,0,1,1,17-17l80,80A12,12,0,0,1,184.49,136.49Z"/></svg></button><span class="lb-c" aria-live="polite"></span></dialog>
<template id="rd-icons"><span data-i="clock"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,20A108,108,0,1,0,236,128,108.12,108.12,0,0,0,128,20Zm0,192a84,84,0,1,1,84-84A84.09,84.09,0,0,1,128,212Zm68-84a12,12,0,0,1-12,12H128a12,12,0,0,1-12-12V72a12,12,0,0,1,24,0v44h44A12,12,0,0,1,196,128Z"/></svg></span><span data-i="check"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm45.66,85.66-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35a8,8,0,0,1,11.32,11.32Z"/></svg></span><span data-i="pin"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,60a44,44,0,1,0,44,44A44.05,44.05,0,0,0,128,60Zm0,64a20,20,0,1,1,20-20A20,20,0,0,1,128,124Zm0-112a92.1,92.1,0,0,0-92,92c0,77.36,81.64,135.4,85.12,137.83a12,12,0,0,0,13.76,0,259,259,0,0,0,42.18-39C205.15,170.57,220,136.37,220,104A92.1,92.1,0,0,0,128,12Zm31.3,174.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104a68,68,0,0,1,136,0C196,137.31,176,167.37,159.3,186.71Z"/></svg></span><span data-i="user"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M234.38,210a123.36,123.36,0,0,0-60.78-53.23,76,76,0,1,0-91.2,0A123.36,123.36,0,0,0,21.62,210a12,12,0,1,0,20.77,12c18.12-31.32,50.12-50,85.61-50s67.49,18.69,85.61,50a12,12,0,0,0,20.77-12ZM76,96a52,52,0,1,1,52,52A52.06,52.06,0,0,1,76,96Z"/></svg></span><span data-i="bank"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M24,108H44v48H32a12,12,0,0,0,0,24H224a12,12,0,0,0,0-24H212V108h20a12,12,0,0,0,6.29-22.22l-104-64a12,12,0,0,0-12.58,0l-104,64A12,12,0,0,0,24,108Zm44,0H92v48H68Zm72,0v48H116V108Zm48,48H164V108h24ZM128,46.09,189.6,84H66.4ZM252,208a12,12,0,0,1-12,12H16a12,12,0,0,1,0-24H240A12,12,0,0,1,252,208Z"/></svg></span><span data-i="x"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208.49,191.51a12,12,0,0,1-17,17L128,145,64.49,208.49a12,12,0,0,1-17-17L111,128,47.51,64.49a12,12,0,0,1,17-17L128,111l63.51-63.52a12,12,0,0,1,17,17L145,128Z"/></svg></span><span data-i="cal"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M208,28H188V24a12,12,0,0,0-24,0v4H92V24a12,12,0,0,0-24,0v4H48A20,20,0,0,0,28,48V208a20,20,0,0,0,20,20H208a20,20,0,0,0,20-20V48A20,20,0,0,0,208,28ZM68,52a12,12,0,0,0,24,0h72a12,12,0,0,0,24,0h16V76H52V52ZM52,204V100H204V204Z"/></svg></span><span data-i="play"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,128a15.74,15.74,0,0,1-7.6,13.51L88.32,229.65a16,16,0,0,1-16.2.3A15.86,15.86,0,0,1,64,216.13V39.87a15.86,15.86,0,0,1,8.12-13.82,16,16,0,0,1,16.2.3L232.4,114.49A15.74,15.74,0,0,1,240,128Z"/></svg></span><span data-i="ig"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,72a24,24,0,1,1,24-24A24,24,0,0,1,128,152ZM176,20H80A60.07,60.07,0,0,0,20,80v96a60.07,60.07,0,0,0,60,60h96a60.07,60.07,0,0,0,60-60V80A60.07,60.07,0,0,0,176,20Zm36,156a36,36,0,0,1-36,36H80a36,36,0,0,1-36-36V80A36,36,0,0,1,80,44h96a36,36,0,0,1,36,36ZM196,76a16,16,0,1,1-16-16A16,16,0,0,1,196,76Z"/></svg></span><span data-i="prev"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M168.49,199.51a12,12,0,0,1-17,17l-80-80a12,12,0,0,1,0-17l80-80a12,12,0,0,1,17,17L97,128Z"/></svg></span><span data-i="next"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M184.49,136.49l-80,80a12,12,0,0,1-17-17L159,128,87.51,56.49a12,12,0,1,1,17-17l80,80A12,12,0,0,1,184.49,136.49Z"/></svg></span><span data-i="video"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M249.45,69.31a12,12,0,0,0-12.51,1L212,88.43V72a20,20,0,0,0-20-20H32A20,20,0,0,0,12,72V184a20,20,0,0,0,20,20H192a20,20,0,0,0,20-20V167.57l24.94,18.14A12,12,0,0,0,256,176V80A12,12,0,0,0,249.45,69.31ZM188,180H36V76H188Zm44-27.57-20-14.54V118.11l20-14.54Z"/></svg></span></template>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
<script>window.RD_ITEMS = <?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="<?= B ?>/radar.js?v=e27e96cf096b" defer></script>

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
  <p>Usamos apenas cookies essenciais para o site funcionar, sem publicidade nem rastreamento. Saiba mais na <a href="<?= B ?>/privacidade/">Política de privacidade</a>.</p>
  <button type="button" class="btn btn--orange btn--sm" id="ck-ok"><span>Aceitar</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232.49,80.49l-128,128a12,12,0,0,1-17,0l-56-56a12,12,0,1,1,17-17L96,183,215.51,63.51a12,12,0,0,1,17,17Z"/></svg></span></button>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= B ?>/site.js?v=01a3b9d2"></script>

</body>
</html>
