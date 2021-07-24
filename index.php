<?php

// ---------------------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------------------

define('DEFAULT_LANGUAGE_CODE', 'en');

define('LANGUAGE_COOKIE_NAME', 'L'); // name of cookie to store users last visited language (empty or false to disable)
define('LANGUAGE_COOKIE_EXPIRE_SEC', 5184000); // 60 days
define('LANGUAGE_COOKIE_DOMAIN', $_SERVER['SERVER_NAME']);

/**
 * Path inside views directory that will be called if a requested URL was not found instead of throwing a 404 error.
 * Can be empty or false to disable and throw 404 error instead
 */
define('NOT_FOUND_PAGE', 'error/');


/**
 * Path from this framework root to the favicon file that should be returned if browser requests the favicon.
 * If empty or false the favicon will be looked up in the views folder 'views/favicon.ico'
 */
define('FAVICON_FILE', 'images/favicons/favicon.ico');


// Constants defining paths to publicly accessible, static filse.
// Do not directly use in your code but instead without the underscore at the beginning '_'
// e.g. echo '<link rel="stylesheet" type="text/css" href="'.CSS.'about.css" />';
define('_CSS', 'css/');
define('_JS', 'js/');
define('_IMAGES', 'images/');
define('_DOWNLOADS', 'downloads/');

// Constants defining paths to private files that can only be accessed with include() or require_once()
define('SCRIPTS', 'scripts/');
define('TRANSLATIONS', 'translations/');
define('CSS_COMPONENTS', 'css-components/');
define('JS_COMPONENTS', 'js-components/');
define('VIEWS', 'views/');
define('ENVIRONMENT_FILE', '.env');


/** If the URL ends with a directory following files will be search inside the requested directory otherwise 404 will be returned */
define('DEFAULT_INDEX_FILES', ['index.php', 'index.html']);



// ---------------------------------------------------------------------------------------
// INTERAL PROCESSING OF REQUESTS
// ---------------------------------------------------------------------------------------

// Load environment variables
if(($handle = fopen(ENVIRONMENT_FILE, "r"))){
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


// detect supported languages
$arr = array();
foreach(scandir(TRANSLATIONS) as $file){
	if($file === "." || $file === ".." || $file === "globals.json" || !str_ends_with($file, '.json')) continue;
	array_push($arr, substr($file, 0, -5));
}
define('SUPPORTED_LANGUAGES', $arr);


/**
 * Converts a given language code into a supported language code or false if no matches found
 * @param String $code Language code that should be checked
 * @return String Supported language code clostest matching given language code or false if no close match
 */
function toSupportedLanguage($code){
	if(!$code || !is_string($code)) return false;
	$len = strlen($code);
	if(in_array($code, SUPPORTED_LANGUAGES)) return $code;
	if($len <= 2) return false;
	$code = substr($code, 0, 2);
	return in_array($code, SUPPORTED_LANGUAGES) ? $code : false;
}

// Trim to actual request
$base = substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));
$requestWithQuery = substr($_SERVER['REQUEST_URI'], strlen($base));
$len = strlen($_SERVER['QUERY_STRING']);
define('FULL_REQUEST', (!$len && !empty($requestWithQuery) && $requestWithQuery[-1] !== '?') ? $requestWithQuery : substr($requestWithQuery, 0, -($len+1)));
$fullRequestLen = strlen(FULL_REQUEST);

// Detect language
$lang = false;
$useLangCookie = (LANGUAGE_COOKIE_NAME && !empty(LANGUAGE_COOKIE_NAME)); 
$idx = strpos(FULL_REQUEST, '/');
if($idx > 0) $lang = toSupportedLanguage(substr(FULL_REQUEST, 0, $idx));
if(!$lang && $useLangCookie && isset($_COOKIE[LANGUAGE_COOKIE_NAME])) $lang = toSupportedLanguage($_COOKIE[LANGUAGE_COOKIE_NAME]);
if(!$lang) $lang = toSupportedLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
define('LANGUAGE_CODE', $lang ? $lang : DEFAULT_LANGUAGE_CODE);
if($useLangCookie) setcookie(LANGUAGE_COOKIE_NAME, LANGUAGE_CODE, (LANGUAGE_COOKIE_EXPIRE_SEC > 0 ? time()+LANGUAGE_COOKIE_EXPIRE_SEC : 0), '/', LANGUAGE_COOKIE_DOMAIN);
$langLen = strlen(LANGUAGE_CODE);
define('REQUEST', ($fullRequestLen < $langLen || strtolower(substr(FULL_REQUEST, 0, $langLen)) != $lang) ? FULL_REQUEST : substr(FULL_REQUEST, $langLen+1));

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
		header("HTTP/1.0 404 Not Found", true, 404); // signal search engines that page is an error page
		if($isAlreadyNotFound || !NOT_FOUND_PAGE || empty(NOT_FOUND_PAGE)) exit();
		respondWithFile(VIEWS.NOT_FOUND_PAGE, true);
	}

	// if path is directory search inside directory
	$add = 0;
	if(is_dir($file)){
		$notFound = true;
		$needSlash = (empty($file) || $file[-1] !== '/');
		$add += ($needSlash && !$isAlreadyNotFound);
		$dir = $file.($needSlash ? '/' : '');
		foreach(DEFAULT_INDEX_FILES as $indexName){
			$path = $dir.$indexName;
			if(file_exists($path)){
				$file = $path;
				$notFound = false;
				break;
			}
		}
		if($notFound) respondWithFile(null, $isAlreadyNotFound);
	}

	// execute PHP files
	if(str_ends_with($file, '.php')){
		// load language files, define constants and include config
		$globals = json_decode(file_get_contents(TRANSLATIONS.'globals.json'), true);
		$trans = json_decode(file_get_contents(TRANSLATIONS.LANGUAGE_CODE.'.json'), true);
		define('TEXT', array_merge((is_array($globals) ? $globals : array()), (is_array($trans) ? $trans : array())));
		define('ROOT_DEPTH', substr_count(FULL_REQUEST, "/")+$add);
		define('BASE_DEPTH', substr_count(REQUEST, "/")+$add);
		define('BASE', str_repeat('../', BASE_DEPTH));
		define('ROOT', BASE.(ROOT_DEPTH != BASE_DEPTH ? '../' : ''));
		define('CSS', ROOT._CSS);
		define('JS', ROOT._JS);
		define('IMAGES', ROOT._IMAGES);
		define('DOWNLOADS', ROOT._DOWNLOADS);
		require_once('config.php');
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

// TODO HANDLE favicon (if not defined views/favicon.ico)

// TODO handle if not found and NOT_FOUND_PAGE is defined


respondWithFile(VIEWS.REQUEST);

?>