<?php

require_once(SCRIPTS.'caching.php');
noCache();

?><!DOCTYPE html>
<html>
    <head>
        <title>PHP Framework by LupCode</title>
        <?php include(SCRIPTS.'metatags.php'); ?>

        <?php
            foreach(SUPPORTED_LANGUAGES as $lang)
                echo '<meta name="keywords" lang="'.$lang.'" content="'.TEXT['pageKeywordsStart'.strtoupper($lang)].'">';
            
            echo '<meta name="description" content="'.TEXT['pageDescriptionStart'].'">';
        ?>

        

    </head>
    <body>
        <h1>Test</h1>
        <?php echo TEXT['languageName']; ?>
    </body>
</html>