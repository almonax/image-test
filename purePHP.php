<?php

require_once 'config.php';

$image = (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name']))
    ? $_FILES['image']
    : null;

if (!$image) {
    $_SESSION['errors'] = ['Image not uploaded'];
    redirect();
}

// validate image
try {

    $imageInfo = getimagesize($image['tmp_name']);
    $origImgWidth = $imageInfo[0];
    $origImgHeight = $imageInfo[1];

    if (!isset($availableFormats[$imageInfo['mime']])) {
        $errors[] = 'Image with this format not supported';
    }

    if ($image['size'] > ($maxSize * 10e5)) {
        $errors[] = "The size of the image should be no more that $maxSize Mb";
    }

    if ($origImgWidth > $maxWidth) {
        $errors[] = "The width of the image should be no more than $maxWidth px";
    }
    if ($origImgHeight > $maxHeight) {
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

// save original
try {

    $originalImageFile = $rootPath
        . $pathOriginalImages
        . $newImageName
        .'.'
        . $availableFormats[$imageInfo['mime']];

    $originalImage = file_get_contents($image['tmp_name']);
    move_uploaded_file($image['tmp_name'], $originalImageFile);
    $originalImage = imagecreatefromstring($originalImage);

} catch (Exception $e) {
    $_SESSION['errors'] = [$e->getMessage()];
    redirect();
}


// Image processing
try {

    $cropImages = [];
    $pathToThumbImage = $rootPath . $pathCropImages . $newImageName;

    $newImageResource = crop(
        $originalImage,
        $origImgWidth,
        $origImgHeight,
        $cropWidth,
        $cropHeight
        );
    imagedestroy($originalImage);

    // impose image on thumb image
    $imposedImageResource = imagecreatefrompng($pathToOverlayImage);
    $newImageResource = imposeImageOnImage($newImageResource, $imposedImageResource);
    imagedestroy($imposedImageResource);

    // impose data on thumb image
    $newImageResource = imposeDataOnImage($newImageResource, date('Y-m-d H:i:s'), $textSize);

    // save thumb image
    imagejpeg($newImageResource, $pathToThumbImage .'.jpeg', 100); // jpg
    imagepng($newImageResource, $pathToThumbImage . '.png', 0);
    imagegif($newImageResource, $pathToThumbImage . '.gif');
    imagebmp($newImageResource, $pathToThumbImage. '.bmp');
    imagedestroy($newImageResource);

    $cropImages = [
        'jpeg' => $pathCropImages.$newImageName.'.jpeg',
        'png'  => $pathCropImages.$newImageName.'.png',
        'gif'  => $pathCropImages.$newImageName.'.gif',
        'bmp'  => $pathCropImages.$newImageName.'.bmp',
    ];

} catch (Exception $e) {
    $_SESSION['errors'] = [$e->getMessage()];
    redirect();
}


// output images
if (!empty($cropImages)) {
    $_SESSION['uploadedImages'] = $cropImages;
}

redirect();




/**
 * @param  resource $imageResource
 * @param  integer $originalWidth
 * @param  integer $originalHeight
 * @param  integer $cropWidth
 * @param  integer $cropHeight
 * @return resource|false
 */
function crop(&$imageResource, $originalWidth, $originalHeight, $cropWidth, $cropHeight) {

    if (!is_resource($imageResource)) {
        return false;
    }

    // define pixel rates
    $originalRatio = $originalWidth / $originalHeight;
    $cropRatio = $cropWidth / $cropHeight;

    if ($originalRatio > $cropRatio) {
        $tempHeight = $cropHeight;
        $tempWidth = (int) ($cropHeight * $originalRatio);
    } else {
        $tempWidth = $cropWidth;
        $tempHeight = (int) ($cropWidth / $originalRatio);
    }

     // resize image
    $newImageResource = imagecreatetruecolor($tempWidth, $tempHeight);
    imagecopyresampled(
        $newImageResource,
        $imageResource,
        0, 0,
        0, 0,
        $tempWidth, $tempHeight,
        $originalWidth, $originalHeight
    );

    // copy cropped region from original image
    $x0 = ($tempWidth - $cropWidth) / 2;
    $y0 = ($tempHeight - $cropHeight) / 2;
    $cropResource = imagecreatetruecolor($cropWidth, $cropHeight);
    imagecopy(
        $cropResource,
        $newImageResource,
        0, 0,
        $x0, $y0,
        $cropWidth, $cropHeight
    );
    imagedestroy($newImageResource);

    return $cropResource;
}

/**
 * @param  resource $imageResource
 * @param  resource $imposedImageResource
 * @return bool|resource
 */
function imposeImageOnImage(&$imageResource, &$imposedImageResource) {

    if (!is_resource($imageResource) || !is_resource($imposedImageResource)) {
        return false;
    }

    // create overlay
    $w = imagesx($imageResource);
    $h = imagesy($imageResource);
    $overlay = imagecreatetruecolor($w, $h);
    imagealphablending($overlay, true);
    imagesavealpha($overlay, true);

    // Set the tile image for filling
    imagesettile($overlay, $imposedImageResource);
    // Flood image fill
    imagefill($overlay, 0, 0, IMG_COLOR_TILED);

    // get image with overlay
    imagecopy(
        $imageResource,
        $overlay,
        0, 0,
        0, 0,
        $w, $h);

    return $imageResource;

}

/**
 * @param  resource $imageResource
 * @param  string $date
 * @param  int $textSize
 * @return resource|bool
 */
function imposeDataOnImage(&$imageResource, $date, $textSize = 14) {

    if (!is_resource($imageResource)) {
        return false;
    }

    // set text color (red)
    $textColor = imagecolorallocate($imageResource, 255, 0, 0);

    $xW = 50;
    $yH = imagesy($imageResource) - 50;
    // print text on image
    imagefttext($imageResource, (POINT_RATIO * $textSize), 0, $xW, $yH, $textColor, realpath(__DIR__) . '/Arial.TTF', $date);

    return $imageResource;

}
