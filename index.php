<?php


// ---------------------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------------------

define('DEFAULT_LANGUAGE_CODE', 'en');

define('_CSS', 'css/'); // internal use only
define('_IMAGES', 'images/'); // internal use only
define('_JS', 'js/'); // internal use only
define('_VIEWS', 'views/'); // internal use only
define('_SCRIPTS', 'scripts/'); // internal use only
define('_TRANSLATIONS', 'translations/'); // internal use only





// ---------------------------------------------------------------------------------------
// DEFINING DEFAULT FUNCTIONS
// ---------------------------------------------------------------------------------------

function isSupportedLanguage($langCode){
	return is_string($langCode) && file_exists(_TRANSLATIONS.strtolower($langCode).'.php');
}

// ---------------------------------------------------------------------------------------
// PROCESSING REQUEST URI
// ---------------------------------------------------------------------------------------

$base = substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));

define('FULL_REQUEST', substr($_SERVER['REQUEST_URI'], strlen($base)));
$fullRequestLen = strlen(FULL_REQUEST);

// ---------------------------------------------------------------------------------------
// DETECTING LANGUAGE
// ---------------------------------------------------------------------------------------

$lang = false;
// TODO check if language codes exist as /translations/<lang>.php

// TODO based on URI

// TODO based cookie

// TODO default language

if(!isSupportedLanguage($lang)) $lang = DEFAULT_LANGUAGE_CODE;

define('LANGUAGE_CODE', $lang);

$langLen = strlen(LANGUAGE_CODE);

define('REQUEST', ($fullRequestLen < $langLen || strtolower(substr(FULL_REQUEST, 0, $langLen)) != $lang) ? FULL_REQUEST : substr(FULL_REQUEST, $langLen+1));

// ---------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------


// TODO HANDLE _CSS
// TODO HANDLE _IMAGES
// TODO HANDLE _JS

// TODO HANDLE sitemap.

?>