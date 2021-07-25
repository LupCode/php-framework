<?php

// ---------------------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------------------

/** Default language code used if requested language not supported */
define('DEFAULT_LANGUAGE_CODE', 'en');


/**
 * Path inside views directory that will be called if a requested URL was not found instead of throwing a 404 error.
 * Can be empty or false to disable and throw 404 error instead
 */
define('NOT_FOUND_PAGE', 'error/');


/**
 * Path from this framework root to the favicon file that should be returned if browser requests the favicon.
 * If empty or false the favicon will be looked up in the views folder 'views/favicon.ico'
 */
define('FAVICON_FILE', 'static/images/favicons/favicon.ico');


// Cookie settings for remembering users language
define('LANGUAGE_COOKIE_NAME', 'L'); // name of cookie to store users last visited language (empty or false to disable)
define('LANGUAGE_COOKIE_EXPIRE_SEC', 5184000); // 60 days
define('LANGUAGE_COOKIE_DOMAIN', $_SERVER['SERVER_NAME']); // domain at which the cookie is readable


// Caching times (seconds) for specific files (0 to cache only for session, -1 to disable caching)
define('IMAGES_CACHE_SECONDS', 604800); // 1 week caching of image files by the browser
define('STATIC_CACHE_SECONDS', 604800); // 1 week caching of files inside the static directory by the browser

// Constants defining paths to private files that can only be accessed with include() or require_once()
define('CSS_COMPONENTS', 'css-components/');
define('JS_COMPONENTS', 'js-components/');
define('SCRIPTS', 'scripts/');
define('STATICS', 'static/');
define('TRANSLATIONS', 'translations/');
define('VIEWS', 'views/');
define('ENVIRONMENT_FILE', '.env');

// Name of CSS and JavaScript directory inside the 'static/' directory. Canno publicly be referenced. No trailing '/'
define('_CSS', 'css');
define('_JS', 'js');


define('STATICS_CACHE_SECONDS', array(
	'css' => 86400, // 1 day
	'images' => 604800, // 1 week
	'js' => 86400, // 1 day
));


/** If the URL ends with a directory following files will be search inside the requested directory otherwise 404 will be returned */
define('DEFAULT_INDEX_FILES', array('index.php', 'index.html'));


/** File extensions to mime type needed for HTTP headers. If needed file extension not found then fallback to function mime_content_type() */
define('FILE_EXTENSION_TO_MIME', array(
	'css' => 'text/css',
	'csv' => 'text/comma-separated-values',
	'gif' => 'image/gif',
	'htm' => 'text/html',
	'html' => 'text/html',
	'ico' => 'image/x-icon',
	'jpeg' => 'image/jpeg',
	'jpg' => 'image/jpeg',
	'jpe' => 'image/jpeg',
	'js' => 'text/javascript',
	'json' => 'application/json',
	'pdf' => 'application/pdf',
	'png' => 'image/png',
	'shtml' => 'text/html',
	'svg' => 'image/svg+xml',
	'txt' => 'text/plain',
	'xhtml' => 'application/xhtml+xml',
	'xml' => 'text/xml'
));



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

/**
 * Takes a path to a file and returns the MIME type based on the file extension.
 * Uses 'FILE_EXTENSION_TO_MIME' array but falls back to mime_content_type() if extenion not defined.
 * @param String $file Path to file the MIME type should be returned for
 * @return String MIME type of file
 */
function file_to_mime($file){
	$idx = strrpos($file, '.');
	if($idx !== false){
		$ext = strtolower(substr($file, $idx+1));
		if(isset(FILE_EXTENSION_TO_MIME[$ext])) return FILE_EXTENSION_TO_MIME[$ext];
	}
	$type = mime_content_type($file);
	return $type ? $type : 'text/plain';
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
		foreach(scandir(STATICS) as $dir) if(is_dir($dir)) define(strtoupper($dir), ROOT.$dir.'/');
		require_once('config.php');
		include($file);
		exit(0);
	}

	// return contents of file
	header('Content-Type: '.file_to_mime($file));
	echo file_get_contents($file);
	exit(0);
}


// process requests starting with a subdirectory inside of statics/
foreach(scandir(STATICS) as $dir){
	if($dir === '.' || $dir === '..') continue;
	if(str_starts_with(FULL_REQUEST, $dir)){
		if(isset(STATICS_CACHE_SECONDS[$dir])){
			require_once(SCRIPTS.'caching.php');
			$cacheSec = STATICS_CACHE_SECONDS[$dir];
			if($cacheSec >= 0) setCache($cacheSec); else noCache();
		}
		if($dir === _CSS) include(CSS_COMPONENTS.'css-config.php');
		if($dir === _JS) include(JS_COMPONENTS.'js-config.php');
		respondWithFile(STATICS.FULL_REQUEST);
	}
}


// process favicon.ico
if(FULL_REQUEST === 'favicon.ico') respondWithFile((FAVICON_FILE && !empty(FAVICON_FILE)) ? FAVICON_FILE : _VIEWS.'favicon.ico');


// process sitemap.xml
if(FULL_REQUEST === 'sitemap.xml') include('sitemap.php');


// all other files including PHP files
respondWithFile(VIEWS.REQUEST);

?>