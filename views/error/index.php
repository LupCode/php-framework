<?php

require_once(SCRIPTS.'caching.php');
noCache();

?><!DOCTYPE html>
<html>
    <head>
        <title>Example error page</title>
        <?php include(SCRIPTS.'metatags.php'); ?>

        <?php
            echo '<meta name="description" content="'.TEXT['pageDescriptionError'].'">';
        ?>
        
    </head>
    <body>
        <h1>Example error page</h1>
        <p>This example page has been loaded from 'views/error/index.php'</p>
        <p>The text on this page should have been loaded from the translations directory, so be careful to don't forget it!</p>
    </body>
</html>