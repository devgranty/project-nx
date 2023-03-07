<?php
/**
 * ---------------------------------------
 * LOAD UP APPLICATION
 * ---------------------------------------
 * Let us load up all helpers, classes
 * and application configuration.
 * Feels good to take a nap now!
 */
require_once __DIR__.'/bootstrap/app.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" prefix="website: http://ogp.me/ns/website#">
    <head>
        <?php include_once __DIR__.'/includes/gtm.php'; ?>
        <title>Unauthorized access</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#000000">
        <link rel="icon" href="<?=SROOT?>favicon.png" sizes="19x19" type="image/png">
		<link rel="apple-touch-icon" href="<?=SROOT?>assets/icons/icon.png" type="image/png">
        <link rel="stylesheet" type="text/css" href="<?=SROOT?>assets/css/style.css?v=20200407">
        <meta name="robots" content="noindex, nofollow">
    </head>
    <body class="s-error-body">
        <?php include_once __DIR__.'/includes/gtm-ns.php'; ?>
        <div class="s-error-container">
            <h1>403</h1>
            <p><strong>Unauthorized access</strong></p>
            <p>
                You are not authorized to access the page you were trying to view <a href="<?=SROOT?>" class="s-blue">go home</a>
            </p>
        </div>
    <body>
</html>
