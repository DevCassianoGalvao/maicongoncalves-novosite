<?php
defined('APP') or exit;
$__path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (B !== '' && strpos($__path, B) === 0) $__path = substr($__path, strlen(B));
$meta += [
    'canonical' => abs_url($__path ?: '/'),
    'image' => abs_url('/img/hero-retrato.webp'),
    'type' => 'website',
    'robots' => '',
];
if (!$meta['image']) $meta['image'] = abs_url('/img/hero-retrato.webp');
?>
<title><?= e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?><meta name="description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<?php if ($meta['robots'] !== ''): ?><meta name="robots" content="<?= e($meta['robots']) ?>">
<?php endif; ?>
<link rel="canonical" href="<?= e($meta['canonical']) ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Vereador Maicon Gonçalves">
<meta property="og:type" content="<?= e($meta['type']) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<?php if ($meta['description'] !== ''): ?><meta property="og:description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:image" content="<?= e($meta['image']) ?>">
<meta name="twitter:card" content="summary_large_image">
