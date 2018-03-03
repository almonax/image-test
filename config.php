<?php

$rootPath = realpath(__DIR__);
$pathOriginalImages = '/originals/';
$pathCropImages     = '/cropped/';
$pathToOverlayImage = 'pattern_copy.png';

$maxWidth   = 5000;
$maxHeight  = 5000;
$maxSize    = 10; // Mb

$cropWidth  = 500;
$cropHeight = 500;
$textSize   = 14; // px
define('POINT_RATIO', 0.752812499999996);

$newImageName = md5(time() . random_bytes(10));

$availableFormats = [
    'image/jpeg' => 'jpeg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/x-ms-bmp'=> 'bmp'
];


// create folders if not exist
try {

    if (!file_exists($rootPath . $pathOriginalImages)) {
        mkdir($rootPath . $pathOriginalImages, 775, true);
    }

    if (!file_exists($rootPath . $pathCropImages)) {
        mkdir($rootPath . $pathCropImages, 755, true);
    }

} catch (Exception $e) {

    redirect();

}

session_start();
// clear old session data
$_SESSION['errors'] = [];
$_SESSION['uploadedImages'] = [];



/**
 * Helper functions
 */


/**
 * @param string $url
 */
function redirect($url = '/') {
    $memory = round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB';
    $_SESSION['memory'] = $memory;
    session_write_close();
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $url);
    die();
}