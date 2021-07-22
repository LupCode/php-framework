<?php


// ---------------------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------------------

define('DEFAULT_LANGUAGE_CODE', 'en');
define('LANGUAGE_COOKIE', 'lang'); // name of cookie to store users last visited language (empty or false to disable)

/** 
 * URL of sitemap that will be generated (should not be changed as this is standard)
 * Further settings of sitemap can be found in sitemap.php
 */
define('SITEMAP_FILE', 'sitemap.xml');

/**
 * Path inside views directory that will be called if a requested URL was not found instead of throwing a 404 error.
 * Can be empty or false to disable and throw 404 error instead
 */
define('NOT_FOUND_PAGE', 'error/');





define('_CSS', 'css/'); // just defines the location, do not reference in your code
define('_CSS_COMPONENTS', 'css-components/'); // just defines the location, do not reference in your code
define('_DOWNLOADS', 'downloads/'); // just defines the location, do not reference in your code
define('_IMAGES', 'images/'); // just defines the location, do not reference in your code
define('_JS', 'js/'); // just defines the location, do not reference in your code
define('_JS_COMPONENTS', 'js-components/'); // just defines the location, do not reference in your code
define('_VIEWS', 'views/'); // just defines the location, do not reference in your code
define('_SCRIPTS', 'scripts/'); // just defines the location, do not reference in your code
define('_TRANSLATIONS', 'translations/'); // just defines the location, do not reference in your code
define('_ENVIRONMENT_FILE', '.env'); // just defines the location, do not reference in your code





// ---------------------------------------------------------------------------------------
// INTERAL PROCESSING OF REQUESTS
// ---------------------------------------------------------------------------------------

// Load environment variables
if(($handle = fopen(_ENVIRONMENT_FILE, "r"))){
	while(($line = fgets($handle)) !== false){
		$idx = strpos($line, "=");
		if($idx < 0) continue;
		$_ENV[substr($line, 0, $idx)] = substr($line, $idx+1);
		putenv($line);
	}
	fclose($handle);
}

/**
 * @return Array Array containing all supported language codes base on the translation files
 */
function getSupportedLanguages(){
	if(isset($GLOBALS['_SUPPORTED_LANGUAGES'])) return $GLOBALS['_SUPPORTED_LANGUAGES'];
	$arr = array();
	foreach(scandir(_TRANSLATIONS) as $file){
		if($file === "." || $file === ".." || $file === "globals.json") continue;
		$idx = strrpos($file, ".");
		array_push($arr, $idx < 0 ? $file : substr($file, 0, $idx));
	}
	$GLOBALS['_SUPPORTED_LANGUAGES'] = $arr;
	return $arr;
}

/**
 * Converts a given language code into a supported language code or false if no matches found
 * @param String $code Language code that should be checked
 * @return String Supported language code clostest matching given language code or false if no close match
 */
function toSupportedLanguage($code){
	if(!$code || !is_string($code)) return false;
	$supported = getSupportedLanguages();
	$len = strlen($code);
	if(in_array($code, $supported)) return $code;
	if($len <= 2) return false;
	$code = substr($code, 0, 2);
	return in_array($code, $supported) ? $code : false;
}

// Trim to actual request
$base = substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));
define('FULL_REQUEST', substr($_SERVER['REQUEST_URI'], strlen($base)));
$fullRequestLen = strlen(FULL_REQUEST);

// Detect language
$lang = false;
$idx = strpos(FULL_REQUEST, '/');
if($idx > 0) $lang = toSupportedLanguage(substr(FULL_REQUEST, 0, $idx));
if(!$lang && LANGUAGE_COOKIE && !empty(LANGUAGE_COOKIE) && isset($_COOKIE[LANGUAGE_COOKIE])) $lang = toSupportedLanguage($_COOKIE[LANGUAGE_COOKIE]);
define('LANGUAGE_CODE', $lang ? $lang : DEFAULT_LANGUAGE_CODE);
$langLen = strlen(LANGUAGE_CODE);
define('REQUEST', ($fullRequestLen < $langLen || strtolower(substr(FULL_REQUEST, 0, $langLen)) != $lang) ? FULL_REQUEST : substr(FULL_REQUEST, $langLen+1));

// ---------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------


// TODO HANDLE _CSS
// TODO HANDLE _IMAGES
// TODO HANDLE _JS

// TODO .env

// TODO HANDLE sitemap.

// TODO HANDLE favicon

// TODO handle if not found and NOT_FOUND_PAGE is defined

?>