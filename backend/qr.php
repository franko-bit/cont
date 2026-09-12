<?php
/**
 * backend/qr.php
 * Generates QR code PNG images locally using chillerlan/php-qrcode.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

$data = trim($_GET['data'] ?? '');
if ($data === '') {
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: text/plain');
    echo 'Missing data parameter for QR generation.';
    exit;
}

$options = new QROptions([
    'outputInterface' => QRGdImagePNG::class,
    'eccLevel'     => 'L',
    'scale'        => 5,
    'outputBase64' => false,
    'margin'       => 1,
]);

header('Content-Type: image/png');
header('Cache-Control: max-age=3600, public');

$qr = new QRCode($options);
echo $qr->render($data);
