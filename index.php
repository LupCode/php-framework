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


/** Path from this framework root to the favicon file that should be returned if browser requests the favicon */
define('FAVICON_FILE', 'images/favicons/favicon.ico');


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


/** If the URL ends with a directory following files will be search inside the requested directory otherwise 404 will be returned */
define('DEFAULT_INDEX_FILES', ['index.php', 'index.html']);



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

// polyfill function str_starts_with($haystack, $needle): bool
if(!function_exists("str_starts_with")){
	function str_starts_with($haystack, $needle){
		if(!is_string($haystack) || !is_string($needle)) return false;
		return substr($haystack, 0, min(strlen($haystack), strlen($needle))) === $needle;
	}
}

// polyfill function str_ends_with($haystack, $needle): bool
if(!function_exists("str_ends_with")){
	function str_ends_with($haystack, $needle){
		if(!is_string($haystack) || !is_string($needle)) return false;
		return substr($haystack, max(0, strlen($haystack)-strlen($needle))) === $needle;
	}
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

/**
 * Function takes the path of a file from framework root, sets appropiate headers, 
 * echos the contents of the file and exits the execution of the script. 
 * If file does not exist the NOT_FOUND_PAGE will be used if defined otherwise responds with 404 error. 
 * @param String $file Path to the file
 */
function respondWithFile($file, $isAlreadyNotFound=false){
	// safety checks
	$realFile = ($file ? realpath($file) : ''); $realRoot = realpath(__DIR__);
	if(!$file || !file_exists($file) || strlen($realFile) < strlen($realRoot) || !str_starts_with($realFile, $realRoot)){
		if($isAlreadyNotFound || !NOT_FOUND_PAGE || empty(NOT_FOUND_PAGE)){
			header("HTTP/1.0 404 Not Foun", true, 404);
			exit(0);
		}
		respondWithFile(_VIEWS.NOT_FOUND_PAGE, true);
	}

	// if path is directory search inside directory
	if(is_dir($file)){
		$notFound = true;
		foreach(DEFAULT_INDEX_FILES as $indexName){
			$path = $file.($file[strlen($file)-1] !== '/' ? '/' : '').$indexName;
			if(file_exists($path)){
				$file = $path;
				$notFound = false;
				break;
			}
		}
		if($notFound) respondWithFile(null);
	}

	// execute PHP files
	if(str_ends_with($file, '.php')){
		include($file);
		exit(0);
	}

	// return contents of file
	$mimeType = mime_content_type($file);
	header('Content-Type: '.($mimeType ? $mimeType : 'text/html'), true);
	echo file_get_contents($file);
	exit(0);
}

// process CSS files
if(str_starts_with(REQUEST, _CSS)){
	$request = substr(REQUEST, strlen(_CSS));
	$routes = json_decode(file_get_contents(_CSS_COMPONENTS.'routes.json'), true);
	if(!isset($routes[$request])){ respondWithFile(_CSS.$request); }

	// TODO BUILD CSS OUT OF COMPONENTS
}




// TODO HANDLE _CSS
// TODO HANDLE _IMAGES
// TODO HANDLE _JS
// TODO HANDLE _DOWNLOADS

// TODO .env

// TODO HANDLE sitemap.

// TODO HANDLE favicon

// TODO handle if not found and NOT_FOUND_PAGE is defined


respondWithFile(REQUEST);

?>