<?php
defined('APP') or exit;

const IMG_TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];

/** Abre uma imagem enviada (JPG, PNG, GIF ou WEBP) já na orientação certa. Lança exceção se não for imagem válida. */
function img_open(string $file)
{
    if (!function_exists('imagecreatetruecolor')) throw new RuntimeException('A extensão GD do PHP não está ativa na hospedagem.');
    $info = @getimagesize($file);
    if (!$info || !isset(IMG_TYPES[$info[2]])) throw new RuntimeException('Formato não aceito. Envie fotos em JPG, PNG ou WEBP.');
    if ($info[0] * $info[1] > 50000000) throw new RuntimeException('Imagem grande demais.');
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $im = @imagecreatefromjpeg($file); break;
        case IMAGETYPE_PNG:  $im = @imagecreatefrompng($file); break;
        case IMAGETYPE_GIF:  $im = @imagecreatefromgif($file); break;
        default:             $im = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false;
    }
    if (!$im) throw new RuntimeException('Não foi possível ler a imagem.');
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (int) (@exif_read_data($file)['Orientation'] ?? 1);
        if ($o === 3) $im = imagerotate($im, 180, 0);
        elseif ($o === 6) $im = imagerotate($im, -90, 0);
        elseif ($o === 8) $im = imagerotate($im, 90, 0);
    }
    return $im;
}

/** Reduz para caber em $max x $max (não amplia). Fundo branco para transparências. */
function img_fit($im, int $max)
{
    $w = imagesx($im); $h = imagesy($im);
    $r = min(1, $max / max($w, $h));
    $nw = max(1, (int) round($w * $r)); $nh = max(1, (int) round($h * $r));
    $out = imagecreatetruecolor($nw, $nh);
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $out;
}

/** Salva uma imagem enviada no painel em uploads/AAAA/MM/ como WEBP (ou JPG). Retorna ['src','w','h']. */
function img_store_upload(string $tmp, int $max = 1800): array
{
    $im = img_fit(img_open($tmp), $max);
    $rel = 'uploads/' . date('Y/m');
    $dir = ROOT . '/' . $rel;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) throw new RuntimeException('Sem permissão para gravar na pasta uploads/.');
    $name = date('dHis') . '-' . bin2hex(random_bytes(5));
    if (function_exists('imagewebp')) { $file = "$rel/$name.webp"; $ok = imagewebp($im, ROOT . '/' . $file, 84); }
    else { $file = "$rel/$name.jpg"; $ok = imagejpeg($im, ROOT . '/' . $file, 85); }
    if (!$ok) throw new RuntimeException('Não foi possível salvar a imagem.');
    return ['src' => $file, 'w' => imagesx($im), 'h' => imagesy($im)];
}

/** Converte para JPG (o Brevo não aceita WEBP em anexo) já reduzida. Retorna os bytes. */
function img_to_jpeg(string $tmp, int $max = 1920, int $quality = 82): string
{
    $im = img_fit(img_open($tmp), $max);
    ob_start();
    imagejpeg($im, null, $quality);
    return (string) ob_get_clean();
}
