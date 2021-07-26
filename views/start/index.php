<?php

require_once(SCRIPTS.'caching.php');
noCache();

?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo TEXT['pageTitleStart']; ?></title>
        <?php include(SCRIPTS.'metatags.php'); ?>

        <?php
            foreach(SUPPORTED_LANGUAGES as $lang)
                echo '<meta name="keywords" lang="'.$lang.'" content="'.TEXT['pageKeywordsStart'.strtoupper($lang)].'">';
            
            echo '<meta name="description" content="'.TEXT['pageDescriptionStart'].'">';
        ?>

        <!-- 
            'echo CSS' is the relative path to the 'static/css/' directory. 
            'start.css' gets automatically generated as defined in 'css-components/css-config.php'
        -->
        <link rel="stylesheet" href="<?php echo CSS; ?>start.css">

        <!-- 
            'echo JS' is the relative path to the 'static/js/' directory. 
            'start.js' gets automatically generated as defined in 'js-components/js-config.php'
        -->
        <script type="text/javascript" src="<?php echo JS; ?>start.js"></script>

    </head>
    <body>
        <div class="content">
            <header>
                <!-- 'echo IMAGES' is the relative path to the 'static/images/' directory -->
                <a class="nodrag noselect" target="_blank" href="https://php.net">
                    <img src="<?php echo IMAGES; ?>favicons/favicon.svg" dragable="false" width="35" height="35" alt="PHP"></img>
                </a>
                <h1 class="noselect nodrag"><?php echo TEXT['pageTitleStart']; ?></h1>
                <div></div>
            </header>
            <div class="main">
                <p><?php echo TEXT['pageDescriptionStart']; ?></p>
                <a class="nodrag" target="_blank" href="https://github.com/LupCode/php-framework">GitHub Repository</a>
            </div>
            <footer>
                <a class="noselect nodrag" target="_blank" href="https://lupcode.com">LupCode</a>
                <div class="language-selector"><?php
                    foreach(SUPPORTED_LANGUAGES as $lang){
                        // ROOT.$lang.'/'.REQUEST is URL to switch language
                        //  - ROOT is relative path to root of this framework
                        //  - $lang is the language code of the new language
                        //  - REQUEST is the currently requested page but without the language code
                        echo '<a class="noselect nodrag" href="'.ROOT.$lang.'/'.REQUEST.'">'.TEXT['languageName'.strtoupper($lang)].'</a>';
                    }
                ?></div>
            </footer>
        </div>
    </body>
</html>