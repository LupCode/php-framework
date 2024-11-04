<?php

require_once(SCRIPTS.'caching.php');
noCache();

?><!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>
        <title><?php echo TEXT['pageErrorTitle'] ?></title>
        <?php include(SCRIPTS.'metatags.php'); ?>

        <?php
            echo '<meta name="description" content="'.TEXT['pageErrorDescription'].'">';
        ?>
        
    </head>
    <body>
        <h1><?php echo TEXT['pageErrorTitle'] ?></h1>
        <p>This example page has been loaded from 'views/error/index.php'</p>
        <p>The text on this page should have been loaded from the translations directory, so be careful to don't forget it!</p>
    </body>
</html>