<?php
defined('APP') or exit;
$meta = ($meta ?? []) + ['title' => 'Minha história – Vereador Maicon Gonçalves | Nova Friburgo', 'description' => 'Da escola pública à Câmara Municipal: a trajetória de Maicon Gonçalves, professor de Educação Física e vereador de Nova Friburgo (RJ) eleito em 2024.', 'page' => 'historia'];
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
<link rel="stylesheet" href="<?= B ?>/site.css?v=d7b1c765">
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
      <a href="<?= B ?>/minha-historia/" class="is-on"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64ZM160,88h40a8,8,0,0,1,0,16H160a8,8,0,0,1,0-16Zm48,40a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,128Zm0,32a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,160Z"/></svg>Minha história</a>
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

<main>
<section class="ph-hero ph-hero--split">
  <div class="hero-bg" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
  <div class="wrap ph-hero-in">
    <div class="stackv">
      <h1 class="h1" data-split>Minha <span class="scr">história<svg viewBox="0 0 300 26" preserveAspectRatio="none" aria-hidden="true"><path d="M4 15C62 4 124 6 182 10s86 0 114 3"/><path d="M34 22c58-6 132-6 214-2"/></svg><svg class="spark" viewBox="0 0 40 40" aria-hidden="true"><path d="M8 30L4 12"/><path d="M20 28l8-22"/><path d="M30 34l8-6"/></svg></span></h1>
      <p class="h3" data-h>A caminhada que me trouxe até aqui</p>
      <p class="lead" data-h>Cada etapa da minha vida ajudou a construir os valores que hoje levo para o mandato: trabalho, persistência, respeito às pessoas e compromisso com Nova Friburgo.</p>
    </div>
    <div class="il-wrap" data-h aria-hidden="true"><svg class="il" viewBox="0 0 480 380">
      <circle cx="250" cy="200" r="150" fill="url(#g-glass)" stroke="rgba(255,255,255,.22)"/>
      <path d="M40 330C92 330 92 280 142 280s50-52 100-52 50-58 100-58 50-64 100-64" fill="none" stroke="rgba(255,255,255,.16)" stroke-width="20" stroke-linecap="round"/>
      <path class="il-dash" d="M40 330C92 330 92 280 142 280s50-52 100-52 50-58 100-58 50-64 100-64" fill="none" stroke="#fff" stroke-width="2.500" stroke-dasharray="3 9" stroke-linecap="round" opacity=".8"/>
      <g><circle class="il-ray" cx="40" cy="330" r="13" fill="none" stroke="#fff" stroke-width="2"/><circle cx="40" cy="330" r="10" fill="url(#g-wh)"/></g>
      <g><circle class="il-ray" style="animation-delay:-.5s" cx="142" cy="280" r="13" fill="none" stroke="#fff" stroke-width="2"/><circle cx="142" cy="280" r="10" fill="url(#g-wh)"/></g>
      <g><circle class="il-ray" style="animation-delay:-1s" cx="242" cy="228" r="13" fill="none" stroke="#fff" stroke-width="2"/><circle cx="242" cy="228" r="10" fill="url(#g-wh)"/></g>
      <g><circle class="il-ray" style="animation-delay:-1.5s" cx="342" cy="170" r="13" fill="none" stroke="#fff" stroke-width="2"/><circle cx="342" cy="170" r="10" fill="url(#g-wh)"/></g>
      <g><circle class="il-ray" style="animation-delay:-2s" cx="442" cy="106" r="15" fill="none" stroke="#FE6500" stroke-width="2"/><circle cx="442" cy="106" r="12" fill="url(#g-or)"/></g>
      <path d="M442 104V44" stroke="#fff" stroke-width="3.500" stroke-linecap="round"/><path class="il-flag" d="M444 46h30l-9 11 9 11h-30z" fill="url(#g-or)"/>
      <g font-family="Hanken Grotesk,sans-serif" font-weight="800" font-size="13" fill="#fff" text-anchor="middle">
        <g class="il-float"><rect x="12" y="278" width="56" height="26" rx="13" fill="url(#g-glass)" stroke="rgba(255,255,255,.5)"/><text x="40" y="296">2015</text></g>
        <g class="il-float" style="animation-delay:-.7s"><rect x="114" y="228" width="56" height="26" rx="13" fill="url(#g-glass)" stroke="rgba(255,255,255,.5)"/><text x="142" y="246">2016</text></g>
        <g class="il-float" style="animation-delay:-1.4s"><rect x="214" y="176" width="56" height="26" rx="13" fill="url(#g-glass)" stroke="rgba(255,255,255,.5)"/><text x="242" y="194">2020</text></g>
        <g class="il-float" style="animation-delay:-2.1s"><rect x="314" y="118" width="56" height="26" rx="13" fill="url(#g-glass)" stroke="rgba(255,255,255,.5)"/><text x="342" y="136">2024</text></g>
        <g class="il-float" style="animation-delay:-2.8s"><rect x="372" y="130" width="56" height="26" rx="13" fill="url(#g-or)"/><text x="400" y="148">2025</text></g>
      </g>
      <g filter="url(#f-soft)"><circle r="9" fill="url(#g-or)" stroke="#fff" stroke-width="3"><animateMotion dur="7s" repeatCount="indefinite" calcMode="linear" keyTimes="0;.92;1" keyPoints="0;1;1" path="M40 330C92 330 92 280 142 280s50-52 100-52 50-58 100-58 50-64 100-64"/><animate attributeName="opacity" dur="7s" repeatCount="indefinite" values="0;1;1;0" keyTimes="0;.05;.9;1"/></circle></g>
      <g stroke="#FF8A3D" stroke-width="4" stroke-linecap="round"><path class="il-tw" d="M96 150v16M88 158h16"/><path class="il-tw" style="animation-delay:-1.2s" d="M300 320v14M293 327h14"/></g>
      <path class="il-tw" style="animation-delay:-.6s" d="M190 96v14M183 103h14" stroke="#8CC3E3" stroke-width="4" stroke-linecap="round"/>
    </svg></div>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <ol class="tl" id="tl">
      <li class="tl-line" aria-hidden="true"><i></i></li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">Onde tudo começou</span><h2 class="h3">Formação na escola pública</h2>
          <p>Minha formação começou na escola pública, base de tudo o que sou hoje. Passei por unidades que marcaram minha trajetória e meus valores: Escola Municipal Bonaventure Bardy, na Estrada de Lumiar; E.M. José Alves Ferreira, em Teresópolis; E.M. Vevey La Jolie, em Conquista; E.E. Padre França, em Mury. Concluí o ensino médio no C.E. Dr. João Bazet, encerrando uma etapa fundamental da minha vida.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-1.webp" alt="Maicon Gonçalves criança, sentado a uma mesa com livros" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2015</span><h2 class="h3">A escolha pela Educação Física</h2>
          <p>Me formei professor de Educação Física, profissão que exerço com orgulho há quase 20 anos no município, sempre acreditando no esporte, na educação e nos projetos sociais como ferramentas de transformação.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-2.webp" alt="Turma de formandos com beca na colação de grau" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2016</span><h2 class="h3">A política como missão</h2>
          <p>Em 2016, disputei minha primeira eleição e fiquei na 1ª suplência, perdendo por apenas 9 votos. Foi uma grata surpresa receber um número tão expressivo de votos, mesmo sem nunca ter tido participação direta ou familiar na política.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-3.webp" alt="Maicon Gonçalves com apoiadores durante a campanha de 2016" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2020</span><h2 class="h3">Quando o voto reconhece, mas o sistema adia</h2>
          <p>Em 2020, fui mais votado que 9 vereadores eleitos, mas, por questões de legenda partidária, fiquei novamente na 1ª suplência. Persistência, resiliência e trabalho sempre falaram mais alto.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-4.webp" alt="Maicon Gonçalves segurando um diploma" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2024</span><h2 class="h3">O mandato</h2>
          <p>Em 2024, conquistei meu primeiro mandato. Uma vitória que não foi apenas minha, mas de muita gente que acreditou, apoiou e caminhou ao meu lado. Recebi um apoio enorme, com adesões que surgiram no caminho, muitas delas inesperadas, mas profundamente significativas. Juntos, conseguimos chegar ao mandato e dar início a uma trajetória marcada pela dedicação, responsabilidade e compromisso com o serviço público.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-5.webp" alt="Maicon Gonçalves na Câmara Municipal segurando um documento ao lado de três pessoas" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2025</span><h2 class="h3">Minha primeira lei aprovada por unanimidade</h2>
          <p>Já em 2025, criei minha primeira lei municipal, garantindo transparência no uso do dinheiro público na realização de eventos, levando informação, clareza e respeito à população.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-6.webp" alt="Texto da lei municipal no Portal de Legislação da Câmara de Nova Friburgo" width="1200" height="675" loading="lazy"></figure></div>
      </li>
      <li class="tl-it">
        <span class="tl-dot" aria-hidden="true"></span>
        <div class="tl-tx" data-r><span class="tl-y">2025</span><h2 class="h3">Medalha Manoel Tubino</h2>
          <p>Em 2025, recebi a maior honraria do esporte nacional: a Medalha Manoel José Gomes Tubino.</p>
          <p>Sou professor de Educação Física, pós-graduado em Administração e Marketing Esportivo, e sigo acreditando que o esporte transforma vidas. É isso que a gente faz acontecer todos os dias, aqui na ponta.</p>
          <p>Meu agradecimento especial ao deputado Luiz Lima e ao suplente Welbert Pedro, pela confiança, parceria e compromisso com o esporte e com Nova Friburgo.</p></div>
        <div class="tl-fig" data-r><figure class="frame"><img src="<?= B ?>/img/historia-7.webp" alt="Maicon Gonçalves com a medalha ao lado de duas autoridades" width="1200" height="675" loading="lazy"></figure></div>
      </li>
    </ol>
  </div>
</section>

<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="band band--blue" data-r>
      <h2 class="h2" data-split>O futuro continua</h2>
      <p class="lead">Minha história segue sendo escrita todos os dias, ao lado da população, ouvindo as comunidades e trabalhando para gerar mais oportunidades para Nova Friburgo.</p>
    </div>
  </div>
</section>
</main>

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
