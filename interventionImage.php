<?php

require 'vendor/autoload.php';

require_once 'config.php';




$Image = new \Intervention\Image\ImageManager([
    'driver' => 'imagick'
]);

// upload
$img = (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name']))
    ? $_FILES['image']
    : null;

if (!$img) {
    $_SESSION['errors'] = ['Image not uploaded'];
    redirect();
}

// validation
try {
    $img = $Image->make($img['tmp_name']);

    if (!isset($availableFormats[$img->mime()])) {
        $errors[] = 'Image with this format not supported';
    }

    if ($img->filesize() > ($maxSize * 10e5)) {
        $errors[] = "The size of the image should be no more that $maxSize Mb";
    }

    if ($img->width() > $maxWidth) {
        $errors[] = "The width of the image should be no more than $maxWidth px";
    }
    if ($img->height() > $maxHeight) {
        $errors[] = "The height of the image should be no more than $maxHeight px";
    }

} catch (Exception $e) {
    $_SESSION['errors'] = [$e->getMessage()];
    redirect();
}

// Break if validate failed
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    redirect();
}

// Save original
$img->save($rootPath . $pathOriginalImages . $newImageName . '.' . $availableFormats[$img->mime()]);

// Crop and resize combined
$img->fit($cropWidth, $cropHeight);

// Print on image
$img->fill($rootPath . "/$pathToOverlayImage");

// Print datetime on image
$img->text(date('Y-m-d H:i:s'), 50, ($img->height()-50), function ($font) use ($rootPath, $textSize) {
    $font->file($rootPath . '/Arial.TTF');
    $font->size($textSize);
    $font->color('#FF0000');
});

// save
$pathForCrop = $rootPath . $pathCropImages . $newImageName . ".";
$cropImages = [];
foreach ($availableFormats as $format) {
    $img->save($pathForCrop . $format, 100);
    $cropImages[$format] = $pathCropImages . $newImageName . ".$format";
}

// output images
if (!empty($cropImages)) {
    $_SESSION['uploadedImages'] = $cropImages;
}

redirect();