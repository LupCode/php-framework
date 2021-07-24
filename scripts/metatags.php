<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=yes, minimal-ui">
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo IMAGES; ?>favicons/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo IMAGES; ?>favicons/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo IMAGES; ?>favicons/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo IMAGES; ?>favicons/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo IMAGES; ?>favicons/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo IMAGES; ?>favicons/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo IMAGES; ?>favicons/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo IMAGES; ?>favicons/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo IMAGES; ?>favicons/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo IMAGES; ?>favicons/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo IMAGES; ?>favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo IMAGES; ?>favicons/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo IMAGES; ?>favicons/favicon-16x16.png">
<link rel="manifest" href="<?php echo ROOT; ?>manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="<?php echo IMAGES; ?>favicons/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<meta name="language" content="<?php echo TEXT['languageName']; ?>">
<meta http-equiv="content-language" content="<?php echo LANGUAGE_CODE; ?>">
<?php

    foreach(SUPPORTED_LANGUAGES as $lang)
        echo '<link rel="alternate" href="https://'.$_SERVER['SERVER_NAME'].'/'.$lang.'/'.REQUEST.'" hreflang="'.$lang.'" />';

?>
