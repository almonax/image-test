<?php
session_start();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Homework: uploading image and processing with php</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

    <style>
        * {
            font-family: 'Roboto';
        }
        .image-margin:nth-child(2n) {
            margin: 5px 5px;
        }
        img {
            max-width: 100%;
        }
    </style>

</head><body>

<div class="container">

    <div class="row mt-4">
        <div class="col-md-6 offset-md-3 text-center">
            <h1>Image upload</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col col-md-6">
            <h2>Processing images with pure PHP<br>(GD library)</h2>
        </div>
        <div class="col col-md-6">
            <h2>Processing images with <a href="http://image.intervention.io/" target="_blank">Intervention Image</a> (Imagick library)</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col col-md-6">
            <form action="/purePHP.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="image">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" role="button">
                </div>
            </form>
        </div>

        <div class="col col-md-6">
            <form action="/interventionImage.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="image">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" role="button">
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="row mt-4">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <?= "<li>$error</li>"; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['uploadedImages'])): ?>
        <div class="row mt-4">
        <?php foreach ($_SESSION['uploadedImages'] as $key => $image): ?>
            <a href="<?= $image ?>" title="<?= $key ?>" class="image-margin" target="_blank">
                <img src="<?= $image ?>" class="rounded float-left image-margin" alt="">
            </a>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['memory'])): ?>
        <div class="row mt-4">
            Memory used: <?= $_SESSION['memory'] ?>
        </div>
    <?php endif; ?>
</div>

</body></html>
<?php
session_unset();
?>