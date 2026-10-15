<?php
defined('APP') or exit;
$meta = ($meta ?? []) + ['title' => 'Vereador Maicon Gonçalves | Nova Friburgo – RJ', 'description' => 'Site oficial do vereador Maicon Gonçalves, de Nova Friburgo (RJ): ações do mandato, projetos de lei, programas FL360 e Peneira Descobrindo Talentos, notícias e contato direto com o gabinete.', 'page' => 'home'];
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
<link rel="stylesheet" href="<?= B ?>/site.css?v=3d55af77">
<script>(function(){var d=document.documentElement,skip=false;d.classList.add('js');try{skip=sessionStorage.getItem('mgNav')==='1';sessionStorage.removeItem('mgNav')}catch(e){}
if(!skip&&true&&!matchMedia('(prefers-reduced-motion: reduce)').matches){d.classList.add('has-intro');setTimeout(function(){d.classList.remove('has-intro')},6000)}})();</script>
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
      <a href="<?= B ?>/" class="is-on"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"/></svg>Início</a>
      <a href="<?= B ?>/minha-historia/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64ZM160,88h40a8,8,0,0,1,0,16H160a8,8,0,0,1,0-16Zm48,40a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,128Zm0,32a8,8,0,0,1-8,8H160a8,8,0,0,1,0-16h40A8,8,0,0,1,208,160Z"/></svg>Minha história</a>
      <a href="<?= B ?>/fl360/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M244.8,150.4a8,8,0,0,1-11.2-1.6A51.6,51.6,0,0,0,192,128a8,8,0,0,1-7.37-4.89,8,8,0,0,1,0-6.22A8,8,0,0,1,192,112a24,24,0,1,0-23.24-30,8,8,0,1,1-15.5-4A40,40,0,1,1,219,117.51a67.94,67.94,0,0,1,27.43,21.68A8,8,0,0,1,244.8,150.4ZM190.92,212a8,8,0,1,1-13.84,8,57,57,0,0,0-98.16,0,8,8,0,1,1-13.84-8,72.06,72.06,0,0,1,33.74-29.92,48,48,0,1,1,58.36,0A72.06,72.06,0,0,1,190.92,212ZM128,176a32,32,0,1,0-32-32A32,32,0,0,0,128,176ZM72,120a8,8,0,0,0-8-8A24,24,0,1,1,87.24,82a8,8,0,1,0,15.5-4A40,40,0,1,0,37,117.51,67.94,67.94,0,0,0,9.6,139.19a8,8,0,1,0,12.8,9.61A51.6,51.6,0,0,1,64,128,8,8,0,0,0,72,120Z"/></svg>FL360</a>
      <a href="<?= B ?>/descobrindo-talentos/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm76.52,147.42H170.9l-9.26-12.76,12.63-36.78,15-4.89,26.24,20.13A87.38,87.38,0,0,1,204.52,171.42Zm-164-34.3L66.71,117l15,4.89,12.63,36.78L85.1,171.42H51.48A87.38,87.38,0,0,1,40.47,137.12Zm10-50.64,5.51,18.6L40.71,116.77A87.33,87.33,0,0,1,50.43,86.48ZM109,152,97.54,118.65,128,97.71l30.46,20.94L147,152Zm91.07-46.92,5.51-18.6a87.33,87.33,0,0,1,9.72,30.29Zm-6.2-35.38-9.51,32.08-15.07,4.89L136,83.79V68.21l29.09-20A88.58,88.58,0,0,1,193.86,69.7ZM146.07,41.87,128,54.29,109.93,41.87a88.24,88.24,0,0,1,36.14,0ZM90.91,48.21l29.09,20V83.79L86.72,106.67l-15.07-4.89L62.14,69.7A88.58,88.58,0,0,1,90.91,48.21ZM63.15,187.42H83.52l7.17,20.27A88.4,88.4,0,0,1,63.15,187.42ZM110,214.13,98.12,180.71,107.35,168h41.3l9.23,12.71-11.83,33.42a88,88,0,0,1-36.1,0Zm55.36-6.44,7.17-20.27h20.37A88.4,88.4,0,0,1,165.31,207.69Z"/></svg>Peneira</a>
      <a href="<?= B ?>/noticias/" class=""><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M88,112a8,8,0,0,1,8-8h80a8,8,0,0,1,0,16H96A8,8,0,0,1,88,112Zm8,40h80a8,8,0,0,0,0-16H96a8,8,0,0,0,0,16ZM232,64V184a24,24,0,0,1-24,24H32A24,24,0,0,1,8,184.11V88a8,8,0,0,1,16,0v96a8,8,0,0,0,16,0V64A16,16,0,0,1,56,48H216A16,16,0,0,1,232,64Zm-16,0H56V184a23.84,23.84,0,0,1-1.37,8H208a8,8,0,0,0,8-8Z"/></svg>Notícias</a>
    </nav>
    <a class="hd-cta btn btn--orange btn--sm " href="<?= B ?>/demandas/"><span>Enviar demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg></span></a>
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
  <a class="mm-cta btn btn--orange" href="<?= B ?>/demandas/"><span>Enviar demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg></span></a>
  <div class="mm-soc">
      <?= social_links() ?>
  </div>
</div>

<main>
<section class="hero" id="inicio">
  <div class="hero-bg" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span></div>
  <div class="ribbon" aria-hidden="true">
    <svg viewBox="0 0 1600 300">
      <defs><path id="rb" d="M-80 264C260 300 600 250 900 190S1400 30 1700 90"/></defs>
      <use href="#rb" class="rb-band"/>
      <text class="rb-text" dy="6"><textPath href="#rb" startOffset="0" data-unit="Olaria    •    Conselheiro Paulino    •    Lumiar    •    Mury    •    Cônego    •    Centro    •    São Pedro da Serra    •    Riograndina    •    Campo do Coelho    •    Amparo    •    Cordoeira    •    Duas Pedras    •    Catarcione    •    Varginha    •    Stucky    •    Lagoinha    •    Braunes    •    Perissê    •    Ypu    •    Vila Amélia    •    Chácara do Paraíso    •    São Geraldo    •    ">Olaria    •    Conselheiro Paulino    •    Lumiar    •    Mury    •    Cônego    •    Centro    •    São Pedro da Serra    •    Riograndina    •    Campo do Coelho    •    Amparo    •    Cordoeira    •    Duas Pedras    •    Catarcione    •    Varginha    •    Stucky    •    Lagoinha    •    Braunes    •    Perissê    •    Ypu    •    Vila Amélia    •    Chácara do Paraíso    •    São Geraldo    •    </textPath></text>
    </svg>
  </div>
  <div class="wrap hero-in">
    <div class="hero-tx">
      <span class="chip" data-h><i></i>Vereador de Nova Friburgo, RJ</span>
      <h1 class="h1" data-split>Nova Friburgo merece <span class="scr">muito mais<svg viewBox="0 0 300 26" preserveAspectRatio="none" aria-hidden="true"><path d="M4 15C62 4 124 6 182 10s86 0 114 3"/><path d="M34 22c58-6 132-6 214-2"/></svg><svg class="spark" viewBox="0 0 40 40" aria-hidden="true"><path d="M8 30L4 12"/><path d="M20 28l8-22"/><path d="M30 34l8-6"/></svg></span></h1>
      <p class="lead" data-h>Aqui você encontra informações sobre ações, projetos e atividades desenvolvidas pelo meu mandato.</p>
      <div class="hero-cta" data-h>
        <a class="btn btn--orange" href="<?= B ?>/minha-historia/" data-mag><span>Minha história</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a>
        <a class="btn btn--ghost" href="<?= B ?>/demandas/" data-mag><span>Enviar demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M230.14,25.86a20,20,0,0,0-19.57-5.11l-.22.07L18.44,79a20,20,0,0,0-3.06,37.25L99,157l40.71,83.65a19.81,19.81,0,0,0,18,11.38c.57,0,1.15,0,1.73-.07A19.82,19.82,0,0,0,177,237.56L235.18,45.65a1.42,1.42,0,0,0,.07-.22A20,20,0,0,0,230.14,25.86ZM156.91,221.07l-34.37-70.64,46-45.95a12,12,0,0,0-17-17l-46,46L34.93,99.09,210,46Z"/></svg></span></a>
      </div>
    </div>
    <div class="stage" id="stage">
      <figure class="ph ph-c" data-depth="10"><div class="ph-in"><img src="<?= B ?>/img/hero-retrato.webp" alt="Retrato do vereador Maicon Gonçalves sorrindo no centro de Nova Friburgo" width="1000" height="1339" fetchpriority="high"></div><span class="stk stk-2"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,60a44,44,0,1,0,44,44A44.05,44.05,0,0,0,128,60Zm0,64a20,20,0,1,1,20-20A20,20,0,0,1,128,124Zm0-112a92.1,92.1,0,0,0-92,92c0,77.36,81.64,135.4,85.12,137.83a12,12,0,0,0,13.76,0,259,259,0,0,0,42.18-39C205.15,170.57,220,136.37,220,104A92.1,92.1,0,0,0,128,12Zm31.3,174.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104a68,68,0,0,1,136,0C196,137.31,176,167.37,159.3,186.71Z"/></svg>Presente nas comunidades</span></figure>
      <figure class="ph ph-l" data-depth="26"><div class="ph-in"><img src="<?= B ?>/img/hero-moradora.webp" alt="Maicon Gonçalves cumprimenta uma moradora sentada em um banco de praça" width="800" height="1071"></div><span class="stk stk-1"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M220,104a12,12,0,0,1-24,0,68,68,0,0,0-136,0c0,25,7.58,32.3,16.35,40.76S96,163.71,96,188a32,32,0,0,0,32,32c9,0,16.19-3.7,22.75-11.64a12,12,0,0,1,18.5,15.28C158.09,237.15,144.21,244,128,244a56.06,56.06,0,0,1-56-56c0-14.09-4.63-18.56-12.31-26C49.13,151.86,36,139.19,36,104a92,92,0,0,1,184,0Zm-40.13,53.61a12,12,0,0,0-16.4,4.38,4,4,0,0,1-7.47-2c0-7.61,3.65-12.86,9.6-20.8C172,130.65,180,120,180,104a52,52,0,0,0-104,0,12,12,0,0,0,24,0,28,28,0,0,1,56,0c0,7.61-3.65,12.86-9.6,20.8C140,133.35,132,144,132,160a28,28,0,0,0,52.25,14A12,12,0,0,0,179.87,157.61Z"/></svg>Ouvindo as pessoas</span></figure>
      <figure class="ph ph-r" data-depth="34"><div class="ph-in"><img src="<?= B ?>/img/hero-abraco.webp" alt="Maicon Gonçalves recebe o abraço de um morador na rua" width="800" height="1058"></div><span class="stk stk-3"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M253.88,108.11l-25.53-51a20,20,0,0,0-26.83-9L178.34,59.7,131.7,44.58a12.14,12.14,0,0,0-7.4,0L77.66,59.7,54.48,48.11a20,20,0,0,0-26.83,9L2.12,108.11a20,20,0,0,0,9,26.83l26.67,13.34,51.18,37.41A12.15,12.15,0,0,0,93,187.62l62,16a12.27,12.27,0,0,0,3,.38,12,12,0,0,0,8.48-3.52l52.62-52.62,25.83-12.92a20,20,0,0,0,8.95-26.83Zm-58.12,29.15-27.52-26a12,12,0,0,0-16.76.26c-9.66,9.74-25.06,16.81-40.81,9.55l38.19-37h22.72l25.81,51.63ZM47.32,71.37,60.59,78l-22,43.9-13.27-6.63Zm107,107.3L101.23,165l-42-30.66L85.17,82.5,128,68.61l1.69.55L90,107.68l-.13.12a20,20,0,0,0,3.4,31c20.95,13.39,46,12.07,66.33-2.73l19.2,18.15Zm63-56.77-22-43.9,13.27-6.63,21.95,43.9ZM118.55,219a12,12,0,0,1-14.62,8.62l-26.6-6.87a12,12,0,0,1-4.08-1.93L48.92,201a12,12,0,0,1,14.16-19.37l22.47,16.42,24.38,6.29A12,12,0,0,1,118.55,219Z"/></svg>Soluções reais</span></figure>
    </div>
  </div>
</section>

<section class="sec pautas" id="pautas">
  <div class="wrap">
    <div class="sec-hd"><h2 class="h2" data-split>Principais pautas do mandato</h2></div>
    <div class="bento" data-rs>
      <article class="pauta pauta--navy">
        <div class="pauta-art"><svg class="il" viewBox="0 0 420 200" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
          <rect x="24" y="172" width="372" height="9" rx="4.5" fill="rgba(255,255,255,.12)"/>
          <g filter="url(#f-soft)"><path d="M52 116q30-14 60 0v54q-30-14-60 0z" fill="url(#g-wh)"/><path d="M112 116q30-14 60 0v54q-30-14-60 0z" fill="url(#g-sky)"/></g>
          <path class="il-page" d="M112 116q30-14 60 0v54q-30-14-60 0z" fill="url(#g-wh)"/>
          <path d="M64 132q18-8 36 0M64 145q18-8 36 0" stroke="#041C4B" stroke-width="3" fill="none" stroke-linecap="round" opacity=".3"/>
          <rect x="109" y="111" width="6" height="62" rx="3" fill="url(#g-or)"/>
          <ellipse class="il-shadow" cx="238" cy="176" rx="30" ry="6" fill="#020B1F"/>
          <g class="il-ball" style="transform-origin:center bottom"><g class="il-ball-in">
            <circle cx="238" cy="144" r="28" fill="url(#g-wh)"/><path d="M238 130l13 9.500-5 15.500h-16l-5-15.500z" fill="#041C4B"/>
            <path d="M238 130v-14M251 139.500l13-5M246 155l8 12M230 155l-8 12M225 139.500l-13-5" stroke="#041C4B" stroke-width="3" stroke-linecap="round"/></g></g>
          <g class="il-pawn"><circle cx="312" cy="128" r="11" fill="url(#g-sky)"/><rect x="299" y="142" width="26" height="32" rx="12" fill="url(#g-sky)"/></g>
          <g class="il-pawn" style="animation-delay:-.45s"><circle cx="346" cy="118" r="12" fill="url(#g-or)"/><rect x="332" y="133" width="28" height="41" rx="13" fill="url(#g-or)"/></g>
          <g class="il-pawn" style="animation-delay:-.9s"><circle cx="380" cy="130" r="10" fill="url(#g-wh)"/><rect x="368" y="143" width="24" height="31" rx="11" fill="url(#g-wh)"/></g>
          <g stroke="#FF8A3D" stroke-width="4" stroke-linecap="round"><path class="il-tw" d="M150 40v16M142 48h16"/><path class="il-tw" style="animation-delay:-1.2s" d="M330 62v14M323 69h14"/></g>
          <path class="il-tw" style="animation-delay:-.6s" d="M70 62v14M63 69h14" stroke="#8CC3E3" stroke-width="4" stroke-linecap="round"/>
        </svg></div>
        <div class="pauta-tx"><h3 class="h3">Educação, Esporte e Inclusão</h3><p>A educação e o esporte como ferramentas de transformação social, inclusão, disciplina e geração de oportunidades para crianças, jovens e famílias.</p></div>
        <span class="pauta-n" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm76.52,147.42H170.9l-9.26-12.76,12.63-36.78,15-4.89,26.24,20.13A87.38,87.38,0,0,1,204.52,171.42Zm-164-34.3L66.71,117l15,4.89,12.63,36.78L85.1,171.42H51.48A87.38,87.38,0,0,1,40.47,137.12Zm10-50.64,5.51,18.6L40.71,116.77A87.33,87.33,0,0,1,50.43,86.48ZM109,152,97.54,118.65,128,97.71l30.46,20.94L147,152Zm91.07-46.92,5.51-18.6a87.33,87.33,0,0,1,9.72,30.29Zm-6.2-35.38-9.51,32.08-15.07,4.89L136,83.79V68.21l29.09-20A88.58,88.58,0,0,1,193.86,69.7ZM146.07,41.87,128,54.29,109.93,41.87a88.24,88.24,0,0,1,36.14,0ZM90.91,48.21l29.09,20V83.79L86.72,106.67l-15.07-4.89L62.14,69.7A88.58,88.58,0,0,1,90.91,48.21ZM63.15,187.42H83.52l7.17,20.27A88.4,88.4,0,0,1,63.15,187.42ZM110,214.13,98.12,180.71,107.35,168h41.3l9.23,12.71-11.83,33.42a88,88,0,0,1-36.1,0Zm55.36-6.44,7.17-20.27h20.37A88.4,88.4,0,0,1,165.31,207.69Z"/></svg></span>
      </article>
      <article class="pauta">
        <div class="pauta-art"><svg class="il" viewBox="0 0 300 200" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
          <circle class="il-glow" cx="226" cy="50" r="34" fill="url(#g-or)" opacity=".22"/>
          <circle class="il-ray" cx="226" cy="50" r="22" fill="none" stroke="#FE6500" stroke-width="2"/><circle class="il-sun" cx="226" cy="50" r="19" fill="url(#g-or)"/>
          <g class="il-cloud" opacity=".95"><rect x="0" y="30" width="62" height="20" rx="10" fill="url(#g-wh)"/><rect x="14" y="18" width="34" height="22" rx="11" fill="#fff"/></g>
          <g class="il-cloud" style="animation-delay:-8s;animation-duration:20s" opacity=".9"><rect x="0" y="70" width="46" height="16" rx="8" fill="#fff"/></g>
          <g class="il-hill-a" fill="url(#g-hill-a)"><path d="M0 150v-36l60-34 50 32 60-48 70 58 60-34 60 30 60-4v36z"/><path d="M420 150v-36l60-34 50 32 60-48 70 58 60-34 60 30 60-4v36z"/></g>
          <g class="il-hill-b" fill="url(#g-hill-b)"><path d="M0 150v-24l80-22 70 32 80-38 90 40 100-12v24z"/><path d="M420 150v-24l80-22 70 32 80-38 90 40 100-12v24z"/></g>
          <rect x="-10" y="148" width="320" height="56" fill="url(#g-nv)"/>
          <line class="il-lane" x1="-40" y1="180" x2="340" y2="180" stroke="#fff" stroke-width="3" stroke-dasharray="22 18" opacity=".8"/>
          <g class="il-bus"><g filter="url(#f-soft)"><rect x="70" y="92" width="158" height="62" rx="13" fill="url(#g-or)"/></g><rect x="74" y="95" width="150" height="10" rx="5" fill="#fff" opacity=".3"/><rect x="70" y="134" width="158" height="7" fill="#031436" opacity=".22"/>
            <rect x="82" y="108" width="27" height="21" rx="5" fill="url(#g-wh)"/><rect x="115" y="108" width="27" height="21" rx="5" fill="url(#g-wh)"/><rect x="148" y="108" width="27" height="21" rx="5" fill="url(#g-wh)"/><rect x="183" y="108" width="33" height="21" rx="5" fill="url(#g-sky)"/>
            <circle cx="221" cy="142" r="4" fill="#fff"/></g>
          <g class="il-wheel"><circle cx="108" cy="156" r="13" fill="#020B1F" stroke="#fff" stroke-width="3"/><path d="M108 147v18M99 156h18" stroke="#fff" stroke-width="2.5"/></g>
          <g class="il-wheel"><circle cx="192" cy="156" r="13" fill="#020B1F" stroke="#fff" stroke-width="3"/><path d="M192 147v18M183 156h18" stroke="#fff" stroke-width="2.5"/></g>
          <g class="il-pin" style="transform-origin:center bottom" filter="url(#f-soft)"><path d="M266 132c-9-11-13-17-13-24a13 13 0 0126 0c0 7-4 13-13 24z" fill="url(#g-bl)"/><circle cx="266" cy="108" r="5" fill="#fff"/></g>
        </svg></div>
        <div class="pauta-tx"><h3 class="h3">Mobilidade</h3><p>Defesa de uma mobilidade urbana mais eficiente, segura e acessível, garantindo o direito de ir e vir com dignidade em todos os bairros e distritos.</p></div>
        <span class="pauta-n" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M184,32H72A32,32,0,0,0,40,64V208a16,16,0,0,0,16,16H80a16,16,0,0,0,16-16V192h64v16a16,16,0,0,0,16,16h24a16,16,0,0,0,16-16V64A32,32,0,0,0,184,32ZM56,176V120H200v56Zm0-96H200v24H56ZM72,48H184a16,16,0,0,1,16,16H56A16,16,0,0,1,72,48Zm8,160H56V192H80Zm96,0V192h24v16Zm-72-60a12,12,0,1,1-12-12A12,12,0,0,1,104,148Zm72,0a12,12,0,1,1-12-12A12,12,0,0,1,176,148Zm72-68v24a8,8,0,0,1-16,0V80a8,8,0,0,1,16,0ZM24,80v24a8,8,0,0,1-16,0V80a8,8,0,0,1,16,0Z"/></svg></span>
      </article>
      <article class="pauta pauta--orange">
        <div class="pauta-art"><svg class="il" viewBox="0 0 300 200" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
          <circle cx="170" cy="100" r="84" fill="url(#g-glass)" stroke="rgba(255,255,255,.3)"/>
          <path d="M68 133Q96 76 124 101 152 44 180 69 208 12 236 37" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="2.500" stroke-dasharray="2 8" stroke-linecap="round"/>
          <g filter="url(#f-soft)"><rect class="il-step" x="40" y="146" width="56" height="60" fill="url(#g-nv)"/><rect class="il-step" style="animation-delay:.9s" x="96" y="114" width="56" height="92" fill="url(#g-nv)"/><rect class="il-step" style="animation-delay:1.8s" x="152" y="82" width="56" height="124" fill="url(#g-nv)"/><rect class="il-step" style="animation-delay:2.7s" x="208" y="50" width="56" height="156" fill="url(#g-nv)"/></g>
          <g fill="#fff" opacity=".28"><rect x="40" y="146" width="56" height="3"/><rect x="96" y="114" width="56" height="3"/><rect x="152" y="82" width="56" height="3"/><rect x="208" y="50" width="56" height="3"/></g>
          <path d="M252 50V8" stroke="#fff" stroke-width="3.500" stroke-linecap="round"/><path class="il-flag" d="M254 10h28l-8 10 8 10h-28z" fill="url(#g-wh)"/>
          <circle r="11" fill="url(#g-wh)"><animateMotion dur="3.6s" repeatCount="indefinite" calcMode="linear" keyTimes="0;.08;.3;.38;.6;.68;.9;1" keyPoints="0;0;.333;.333;.667;.667;1;1" path="M68 133Q96 76 124 101 152 44 180 69 208 12 236 37"/><animate attributeName="opacity" dur="3.6s" repeatCount="indefinite" values="0;1;1;0" keyTimes="0;.06;.94;1"/></circle>
          <g stroke="#fff" stroke-width="4" stroke-linecap="round"><path class="il-tw" d="M40 50v16M32 58h16"/><path class="il-tw" style="animation-delay:-1.2s" d="M130 22v12M124 28h12"/><path class="il-tw" style="animation-delay:-.6s" d="M282 100v12M276 106h12"/></g>
        </svg></div>
        <div class="pauta-tx"><h3 class="h3">Juventude e Futuro</h3><p>Investimento em formação, capacitação e caminhos reais para o crescimento pessoal, profissional e cidadão da juventude.</p></div>
        <span class="pauta-n" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M223.85,47.12a16,16,0,0,0-15-15c-12.58-.75-44.73.4-71.41,27.07L132.69,64H74.36A15.91,15.91,0,0,0,63,68.68L28.7,103a16,16,0,0,0,9.07,27.16l38.47,5.37,44.21,44.21,5.37,38.49a15.94,15.94,0,0,0,10.78,12.92,16.11,16.11,0,0,0,5.1.83A15.91,15.91,0,0,0,153,227.3L187.32,193A15.91,15.91,0,0,0,192,181.64V123.31l4.77-4.77C223.45,91.86,224.6,59.71,223.85,47.12ZM74.36,80h42.33L77.16,119.52,40,114.34Zm74.41-9.45a76.65,76.65,0,0,1,59.11-22.47,76.46,76.46,0,0,1-22.42,59.16L128,164.68,91.32,128ZM176,181.64,141.67,216l-5.19-37.17L176,139.31Zm-74.16,9.5C97.34,201,82.29,224,40,224a8,8,0,0,1-8-8c0-42.29,23-57.34,32.86-61.85a8,8,0,0,1,6.64,14.56c-6.43,2.93-20.62,12.36-23.12,38.91,26.55-2.5,36-16.69,38.91-23.12a8,8,0,1,1,14.56,6.64Z"/></svg></span>
      </article>
      <article class="pauta pauta--blue">
        <div class="pauta-art"><svg class="il" viewBox="0 0 420 200" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
          <circle cx="221" cy="104" r="82" fill="url(#g-glass)" stroke="rgba(255,255,255,.26)"/>
          <g filter="url(#f-soft)"><rect x="146" y="20" width="150" height="170" rx="14" fill="url(#g-wh)"/></g>
          <rect x="164" y="40" width="62" height="10" rx="5" fill="#041C4B"/>
          <rect class="il-line" x="164" y="62" width="114" height="6" rx="3" fill="#C4D6F7"/><rect class="il-line" style="animation-delay:.2s" x="164" y="76" width="88" height="6" rx="3" fill="#C4D6F7"/>
          <path d="M164 172h114" stroke="#C4D6F7" stroke-width="2"/>
          <rect class="il-bar" x="168" y="132" width="20" height="40" rx="4" fill="url(#g-or)"/><rect class="il-bar" style="animation-delay:.15s" x="196" y="110" width="20" height="62" rx="4" fill="url(#g-nv)"/>
          <rect class="il-bar" style="animation-delay:.3s" x="224" y="142" width="20" height="30" rx="4" fill="url(#g-sky)"/><rect class="il-bar" style="animation-delay:.45s" x="252" y="96" width="20" height="76" rx="4" fill="url(#g-or)"/>
          <g class="il-check" filter="url(#f-soft)"><circle cx="296" cy="26" r="18" fill="url(#g-or)"/><path d="M287 26l6 7 12-13" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></g>
          <g class="il-float"><rect x="42" y="78" width="72" height="38" rx="19" fill="url(#g-glass)" stroke="rgba(255,255,255,.55)"/><text x="78" y="103" text-anchor="middle" font-family="Hanken Grotesk,sans-serif" font-weight="800" font-size="18" fill="#fff">R$</text></g>
          <g class="il-float" style="animation-delay:-1.7s"><circle cx="356" cy="138" r="21" fill="url(#g-glass)" stroke="rgba(255,255,255,.55)"/><path d="M347 138l6 7 12-13" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></g>
          <g><circle r="27" fill="rgba(140,195,227,.3)" stroke="#031436" stroke-width="7"/><path d="M-16 -8a18 18 0 0114-10" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".8"/><path d="M20 20l20 20" stroke="#031436" stroke-width="10" stroke-linecap="round"/>
            <animateMotion dur="7s" repeatCount="indefinite" path="M196 100c0-44 62-44 62 0s-62 52-62 0"/></g>
        </svg></div>
        <div class="pauta-tx"><h3 class="h3">Transparência</h3><p>Compromisso com informação clara, fiscalização responsável e respeito ao cidadão no uso dos recursos públicos.</p></div>
        <span class="pauta-n" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"/></svg></span>
      </article>
    </div>
  </div>
</section>

<section class="sec panel" id="programas">
  <div class="panel-rings" aria-hidden="true"><i></i><i></i><i></i></div>
  <div class="wrap">
    <div class="sec-hd sec-hd--c"><h2 class="h2 h2--2l" data-split><span>Ações, programas e atividades</span> <span>em desenvolvimento</span></h2></div>
    <div class="progs" data-rs>
      <article class="pcard">
        <div class="pcard-img"><img src="<?= B ?>/img/fl360-kv.webp" alt="Arte do programa FL360: formação cidadã e compreensão da gestão pública" width="1500" height="844" loading="lazy"><span class="pcard-b" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M164.38,181.1a52,52,0,1,0-72.76,0,75.89,75.89,0,0,0-30,28.89,12,12,0,0,0,20.78,12,53,53,0,0,1,91.22,0,12,12,0,1,0,20.78-12A75.89,75.89,0,0,0,164.38,181.1ZM100,144a28,28,0,1,1,28,28A28,28,0,0,1,100,144Zm147.21,9.59a12,12,0,0,1-16.81-2.39c-8.33-11.09-19.85-19.59-29.33-21.64a12,12,0,0,1-1.82-22.91,20,20,0,1,0-24.78-28.3,12,12,0,1,1-21-11.6,44,44,0,1,1,73.28,48.35,92.18,92.18,0,0,1,22.85,21.69A12,12,0,0,1,247.21,153.59Zm-192.28-24c-9.48,2.05-21,10.55-29.33,21.65A12,12,0,0,1,6.41,136.79,92.37,92.37,0,0,1,29.26,115.1a44,44,0,1,1,73.28-48.35,12,12,0,1,1-21,11.6,20,20,0,1,0-24.78,28.3,12,12,0,0,1-1.82,22.91Z"/></svg></span></div>
        <div class="pcard-tx">
          <h3 class="h3">FL360 – Friburgo Líder 360</h3>
          <p>Um programa de formação política e comunitária que prepara novas lideranças e conecta a população ao funcionamento da Câmara.</p>
          <a class="btn btn--orange" href="<?= B ?>/fl360/" data-mag><span>Conhecer o FL360</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a>
        </div>
      </article>
      <article class="pcard">
        <div class="pcard-img"><img src="<?= B ?>/img/peneira.webp" alt="Maicon Gonçalves ao lado de um jovem atleta com uma bola no estádio" width="1500" height="844" loading="lazy"><span class="pcard-b" aria-hidden="true"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,20A108,108,0,1,0,236,128,108.12,108.12,0,0,0,128,20Zm71.88,151.42h-24L166.1,158l11.3-32.94L193.15,120l18.6,14.26A83.42,83.42,0,0,1,199.88,171.42ZM44.25,134.21,62.85,120l15.75,5.11L89.9,158l-9.75,13.42h-24A83.42,83.42,0,0,1,44.25,134.21ZM111.85,148l-9.54-27.77L128,102.56l25.69,17.67L144.15,148Zm80.66-73.73-6.78,22.85-15.81,5.14L140,81.69V65.46l21.22-14.59A84.27,84.27,0,0,1,192.51,74.27ZM94.78,50.87,116,65.46V81.69L86.08,102.26,70.27,97.12,63.49,74.27A84.27,84.27,0,0,1,94.78,50.87Zm13.58,158.79-8.62-24.37L109.39,172h37.22l9.65,13.29-8.62,24.37a83.59,83.59,0,0,1-39.28,0Z"/></svg></span></div>
        <div class="pcard-tx">
          <h3 class="h3">Peneira de Futebol - Descobrindo Talentos</h3>
          <p>Projeto de avaliação esportiva com participação de jovens atletas da cidade e região.</p>
          <a class="btn btn--orange" href="<?= B ?>/descobrindo-talentos/" data-mag><span>Conhecer a Peneira</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a>
        </div>
      </article>
    </div>
  </div>
</section>

<section class="sec join" id="participe">
  <div class="wrap join-in">
    <div class="join-tx">
      <h2 class="h2" data-split>Participe do mandato</h2>
      <p class="lead" data-r>Seu contato é fundamental para que possamos agir com eficiência. Envie sua sugestão, demanda ou denúncia. Estamos prontos para ouvir você, entender o problema e buscar soluções junto aos órgãos responsáveis.</p>
      <a class="btn btn--navy" href="<?= B ?>/demandas/" data-mag data-r><span>Enviar demanda</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a>
    </div>
    <div class="hub" aria-hidden="true" data-r>
      <svg viewBox="0 0 100 100"><path class="hub-flow" vector-effect="non-scaling-stroke" d="M22 16Q42 26 50 50"/><path class="hub-flow" vector-effect="non-scaling-stroke" d="M84 32Q64 34 50 50"/><path class="hub-flow" vector-effect="non-scaling-stroke" d="M30 88Q36 66 50 50"/></svg>
      <span class="hub-c"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M248,208a8,8,0,0,1-8,8H16a8,8,0,0,1,0-16H240A8,8,0,0,1,248,208ZM16.3,98.18a8,8,0,0,1,3.51-9l104-64a8,8,0,0,1,8.38,0l104,64A8,8,0,0,1,232,104H208v64h16a8,8,0,0,1,0,16H32a8,8,0,0,1,0-16H48V104H24A8,8,0,0,1,16.3,98.18ZM144,160a8,8,0,0,0,16,0V112a8,8,0,0,0-16,0Zm-48,0a8,8,0,0,0,16,0V112a8,8,0,0,0-16,0Z"/></svg></span>
      <span class="hub-n hub-n1"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M180,232a12,12,0,0,1-12,12H88a12,12,0,0,1,0-24h80A12,12,0,0,1,180,232Zm40-128a91.51,91.51,0,0,1-35.17,72.35A12.26,12.26,0,0,0,180,186v2a20,20,0,0,1-20,20H96a20,20,0,0,1-20-20v-2a12,12,0,0,0-4.7-9.51A91.57,91.57,0,0,1,36,104.52C35.73,54.69,76,13.2,125.79,12A92,92,0,0,1,220,104Zm-24,0a68,68,0,0,0-69.65-68C89.56,36.88,59.8,67.55,60,104.38a67.71,67.71,0,0,0,26.1,53.19A35.87,35.87,0,0,1,100,184h56.1A36.13,36.13,0,0,1,170,157.49,67.68,67.68,0,0,0,196,104Zm-20.07-5.32a48.5,48.5,0,0,0-31.91-40,12,12,0,0,0-8,22.62,24.31,24.31,0,0,1,16.09,20,12,12,0,0,0,23.86-2.64Z"/></svg>Sugestão</span>
      <span class="hub-n hub-n2"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M128,60a44,44,0,1,0,44,44A44.05,44.05,0,0,0,128,60Zm0,64a20,20,0,1,1,20-20A20,20,0,0,1,128,124Zm0-112a92.1,92.1,0,0,0-92,92c0,77.36,81.64,135.4,85.12,137.83a12,12,0,0,0,13.76,0,259,259,0,0,0,42.18-39C205.15,170.57,220,136.37,220,104A92.1,92.1,0,0,0,128,12Zm31.3,174.71A249.35,249.35,0,0,1,128,216.89a249.35,249.35,0,0,1-31.3-30.18C80,167.37,60,137.31,60,104a68,68,0,0,1,136,0C196,137.31,176,167.37,159.3,186.71Z"/></svg>Demanda</span>
      <span class="hub-n hub-n3"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M252,120a52.06,52.06,0,0,0-52-52H160.32c-3.44-.21-52.6-4-99.46-43.3A20,20,0,0,0,28,40V200a19.8,19.8,0,0,0,11.54,18.12,19.86,19.86,0,0,0,21.32-2.81A192.92,192.92,0,0,1,144,174.47v26.2a20,20,0,0,0,8.9,16.64,11.35,11.35,0,0,0,1.39.8l14.44,7.06A20,20,0,0,0,198.37,213l11.09-41.82A52.07,52.07,0,0,0,252,120ZM52,191.63V48.4c36.17,28.07,72.17,38.1,92,41.66V150C124.17,153.52,88.17,163.55,52,191.63ZM176.39,202.2,168,198.1V172h16.4ZM200,148H168V92h32a28,28,0,1,1,0,56Z"/></svg>Denúncia</span>
    </div>
  </div>
</section>

<?php $news = posts_published(6); if ($news): ?>
<section class="sec news" id="noticias">
  <div class="wrap">
    <div class="sec-hd"><h2 class="h2" data-split>Últimas notícias</h2><a class="btn btn--line btn--sm" href="<?= B ?>/noticias/" data-r><span>Ver mais</span><span class="btn-i"><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224.49,136.49l-72,72a12,12,0,0,1-17-17L187,140H40a12,12,0,0,1,0-24H187L135.51,64.48a12,12,0,0,1,17-17l72,72A12,12,0,0,1,224.49,136.49Z"/></svg></span></a></div>
    <div class="news-grid" data-rs>
<?php foreach ($news as $k => $p) echo card_html($p, $k < 2), "\n"; ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>

<div class="ft-top " aria-hidden="true">
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
          <a href="<?= B ?>/">Início</a><a href="<?= B ?>/minha-historia/">Minha história</a><a href="<?= B ?>/fl360/">FL360</a><a href="<?= B ?>/descobrindo-talentos/">Peneira</a><a href="<?= B ?>/noticias/">Notícias</a><a href="<?= B ?>/demandas/">Enviar demanda</a>
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
    <div class="ft-end">© <?= date('Y') ?> Vereador Maicon Gonçalves. Todos os direitos reservados.</div>
  </div>
</footer>
<a class="wa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><b><svg class="ic" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M187.58,144.84l-32-16a8,8,0,0,0-8,.5l-14.69,9.8a40.55,40.55,0,0,1-16-16l9.8-14.69a8,8,0,0,0,.5-8l-16-32A8,8,0,0,0,104,64a40,40,0,0,0-40,40,88.1,88.1,0,0,0,88,88,40,40,0,0,0,40-40A8,8,0,0,0,187.58,144.84ZM152,176a72.08,72.08,0,0,1-72-72A24,24,0,0,1,99.29,80.46l11.48,23L101,118a8,8,0,0,0-.73,7.51,56.47,56.47,0,0,0,30.15,30.15A8,8,0,0,0,138,155l14.61-9.74,23,11.48A24,24,0,0,1,152,176ZM128,24A104,104,0,0,0,36.18,176.88L24.83,210.93a16,16,0,0,0,20.24,20.24l34.05-11.35A104,104,0,1,0,128,24Zm0,192a87.87,87.87,0,0,1-44.06-11.81,8,8,0,0,0-6.54-.67L40,216,52.47,178.6a8,8,0,0,0-.66-6.54A88,88,0,1,1,128,216Z"/></svg></b><span>Fale comigo!</span></a>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="<?= B ?>/site.js?v=a9e78a47"></script>

</body>
</html>
