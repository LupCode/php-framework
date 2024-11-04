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


/**
 * If a requested URI does not exist (cannot be found in the views directory e.g. 'start/123') it will be checked 
 * if the URI starts with one of the following keys of the array. If a match is found the physical file declared as 
 * value will be executed instead of the fallback error page (e.g. 'start/123' will be executed by 'views/start/index.php').
 * The constant 'REQUEST_PREFIX' will contain the matched key and the constant 'REQUEST_SUFFIX' will contain the 
 * part of the URI that comes immediately after the prefix (language code prefixes in URI will be ignored and don't need to be added in the array keys, 
 * additionally URIs must not start with '/' e.g. not '/start/' but instead 'start/').
 */
define('PREFIX_FALLBACKS', array(
	'start/' => 'views/start/index.php' // just an example, can be removed
));


/** 
 * Every request will be redirected to HTTPs (execpt if client is on localhost)
 */
define('HTTPS_REDIRECT', true);


/**
 * If true the framework will remove the 'www.' prefix from the domain name by redirecting to the domain without 'www.'.
 */
define('WWW_REDIRECT', true);


// Cookie settings for remembering users language
define('LANGUAGE_COOKIE_NAME', 'L'); // name of cookie to store users last visited language (empty or false to disable)
define('LANGUAGE_COOKIE_EXPIRE_SEC', 5184000); // 60 days
define('LANGUAGE_COOKIE_DOMAIN', $_SERVER['SERVER_NAME']); // domain at which the cookie is readable


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

// Caching times (seconds) for specific subdirectories inside the 'static' directory (0 to cache only for session, -1 to disable caching)
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


/** Sequence that must be in front and after constants that are referenced in the JSON translation files */
define('TRANSLATION_CONSTANT_ESCAPE', '%%');


/** Path used in dev mode for sending updates to client */
define('DEV_SSE_URI', '.LUP');

/** Interval in milliseconds for checking file system for changes */
define('DEV_CHECK_FILE_CHANGES', 500);


// ---------------------------------------------------------------------------------------
// INTERAL PROCESSING OF REQUESTS
// ---------------------------------------------------------------------------------------

$IS_HTTPS = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
			(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' || 
			!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) !== 'off');

// check if www. prefix should be removed
if(WWW_REDIRECT && str_starts_with($_SERVER['SERVER_NAME'], 'www.') && empty($_POST)){
	header("Location: ".(($IS_HTTPS || HTTPS_REDIRECT) ? 'https' : 'http').
					"://".substr($_SERVER['SERVER_NAME'], 4).$_SERVER['REQUEST_URI'].(empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING']));
	exit(0);
}

// check if redirect to https is needed
if(HTTPS_REDIRECT && $_SERVER['REMOTE_ADDR'] !== "127.0.0.1" && $_SERVER['REMOTE_ADDR'] !== "::1" && !$IS_HTTPS && empty($_POST)){
	header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].(empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING']));
	exit(0);
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
define('PROJECT_ROOT', substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME']))));
$requestWithQuery = substr($_SERVER['REQUEST_URI'], strlen(PROJECT_ROOT));
$len = strlen($_SERVER['QUERY_STRING']);
define('FULL_REQUEST', urldecode((!$len && !empty($requestWithQuery) && $requestWithQuery[-1] !== '?') ? $requestWithQuery : substr($requestWithQuery, 0, -($len+1))));
$fullRequestLen = strlen(FULL_REQUEST);

// Detect language
$lang = false;
$useLangCookie = (LANGUAGE_COOKIE_NAME && !empty(LANGUAGE_COOKIE_NAME)); 
$idx = strpos(FULL_REQUEST, '/');
if($idx > 0) $lang = toSupportedLanguage(substr(FULL_REQUEST, 0, $idx));
if(!$lang && $useLangCookie && isset($_COOKIE[LANGUAGE_COOKIE_NAME])) $lang = toSupportedLanguage($_COOKIE[LANGUAGE_COOKIE_NAME]);
if(!$lang && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$al = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$idx = strpos($al, ',');
	$lang = toSupportedLanguage($idx ? substr($al, 0, $idx) : $al);
}
define('LANGUAGE_CODE', $lang ? $lang : DEFAULT_LANGUAGE_CODE);
if($useLangCookie) setcookie(LANGUAGE_COOKIE_NAME, LANGUAGE_CODE, (LANGUAGE_COOKIE_EXPIRE_SEC > 0 ? time()+LANGUAGE_COOKIE_EXPIRE_SEC : 0), '/', LANGUAGE_COOKIE_DOMAIN);
$langLen = strlen(LANGUAGE_CODE);
define('REQUEST', ($fullRequestLen < $langLen || strtolower(substr(FULL_REQUEST, 0, $langLen)) != $lang) ? FULL_REQUEST : substr(FULL_REQUEST, $langLen+1));

/** Load environment variables */
function reloadEnv(){
	unset($_ENV);
	if(($handle = fopen(ENVIRONMENT_FILE, "r"))){
		while(($line = fgets($handle)) !== false){
			$idx = strpos($line, "=");
			if($idx < 0) continue;
			$k = trim(substr($line, 0, $idx));
			if(empty($k) || $k[0] === '#') continue;
			$v = trim(substr($line, $idx+1));
			$_ENV[$k] = $v;
			putenv($k.'='.$v);
		}
		fclose($handle);
	}
}

/** Returns true if server runs in development mode, otherwise false */
function isDevMode(){
	return isset($_ENV['DEV']) && (empty($_ENV['DEV']) || $_ENV['DEV'] === '1' || strtolower($_ENV['DEV']) === 'true');
}

/**
 * Function takes the path of a file from framework root, sets appropiate headers, 
 * echos the contents of the file and exits the execution of the script. 
 * If file does not exist the NOT_FOUND_PAGE will be used if defined otherwise responds with 404 error. 
 * @param String $file Path to the file
 */
function respondWithFile($file, $isAlreadyNotFound=false, $isInsideStatics=false, $prefix=false){

	// dev mode SSE
	if($file === VIEWS.DEV_SSE_URI){
		reloadEnv();
		if(isDevMode()){
			header('Content-Type: text/event-stream');
			header('Cache-Control: no-cache');
			header('Connection: Keep-Alive');
			
			function initFiles($files=array(), $prefix=''){
				$result = array();
				foreach($files as $file){
					if($file === '.' || $file === '..') continue;
					$fullFile = $prefix.$file;
					if(is_dir($fullFile))
						array_push($result, ...initFiles(scandir($fullFile), $fullFile.'/'));
					else
						array_push($result, $fullFile.':'.filemtime($fullFile));
				}
				return $result;
			}
			$FILES = initFiles(scandir('.'));
			$FILES_LEN = count($FILES);

			/** Returns count of found files or -1 if file has changed */
			function checkFiles($FILES=array(), $files=array(), $prefix=''){
				$count = 0;
				foreach($files as $file){
					if($file === '.' || $file === '..') continue;
					$fullFile = $prefix.$file;
					if(is_dir($fullFile)){
						$found = checkFiles($FILES, scandir($fullFile), $fullFile.'/');
						if($found === -1) return -1;
						$count = $count + $found;
					} else if(in_array($fullFile.':'.filemtime($fullFile), $FILES)){
						$count = $count + 1;
					} else {
						echo $fullFile.':'.filemtime($fullFile).'<br />';
						return -1;
					}
				}
				return $count;
			}

			while(!connection_aborted()){
				usleep(DEV_CHECK_FILE_CHANGES*1000);
				if(checkFiles($FILES, scandir('.')) !== $FILES_LEN){
					echo "event: message\n";
					echo "data: {\"reload\": true}";
					echo "\n\n";
					ob_end_flush();
					flush();
					$FILES = initFiles(scandir('.'));
					$FILES_LEN = count($FILES);
				}
			}
			exit(0);
		}
	}

	// safety checks
	$realFile = ($file ? realpath($file) : ''); $realRoot = realpath(__DIR__);
	if(!$file || !file_exists($file) || strlen($realFile) < strlen($realRoot) || !str_starts_with($realFile, $realRoot)){
		if($isInsideStatics) respondWithFile(VIEWS.REQUEST, $isAlreadyNotFound, false, $prefix);

		// respond with prefixed fallbacks
		foreach(PREFIX_FALLBACKS as $pref => $file)
			if(str_starts_with(REQUEST, $pref) && file_exists($file)) respondWithFile($file, false, false, $pref);

		// respond with not found page
		header("HTTP/1.0 404 Not Found", true, 404); // signal search engines that page is an error page
		if($isAlreadyNotFound || !NOT_FOUND_PAGE || empty(NOT_FOUND_PAGE)) exit();
		respondWithFile(VIEWS.NOT_FOUND_PAGE, true, false, $prefix);
	}

	// if path is directory search inside directory
	if(is_dir($file)){
		if(!empty(FULL_REQUEST) && FULL_REQUEST[-1] !== '/'){
			header('Location: '.str_repeat('../', substr_count(FULL_REQUEST, "/")).FULL_REQUEST.'/', true);
			exit(0);
		}
		$notFound = true;
		foreach(DEFAULT_INDEX_FILES as $indexName){
			$path = $file.$indexName;
			if(file_exists($path)){
				$file = $path;
				$notFound = false;
				break;
			}
		}
		if($notFound){
			if($isInsideStatics) respondWithFile(VIEWS.REQUEST, $isAlreadyNotFound, false, $prefix);
			respondWithFile(null, $isAlreadyNotFound, false, $prefix);
		}
	}

	// execute PHP files
	if(str_ends_with($file, '.php')){
		// load language files, define constants and include config
		define('REQUEST_PREFIX', $prefix === false ? REQUEST : $prefix);
		define('REQUEST_SUFFIX', $prefix === false ? false : substr(REQUEST, strlen($prefix)));
		define('REQUEST_SUFFIX_PARTS', !empty(REQUEST_SUFFIX) ? explode('/', REQUEST_SUFFIX[0] === '/' ? substr(REQUEST_SUFFIX, 1) : REQUEST_SUFFIX) : array());
		define('REQUEST_PREFIX_BASE', str_repeat('../', max(0, count(REQUEST_SUFFIX_PARTS)-1) ));
		define('ROOT_DEPTH', substr_count(FULL_REQUEST, "/"));
		define('BASE_DEPTH', substr_count(REQUEST, "/"));
		define('BASE', str_repeat('../', BASE_DEPTH));
		define('ROOT', BASE.(ROOT_DEPTH != BASE_DEPTH ? '../' : ''));
		foreach(scandir(STATICS) as $dir) if(is_dir(STATICS.$dir)) define(strtoupper($dir), ROOT.$dir.'/');

		reloadEnv();

		include('config.php');

		$IS_DEV = isDevMode();

		function replaceVariables($str){
			$val = ""; $start = -1; $end = 0;
			do {
				$start = strpos($str, TRANSLATION_CONSTANT_ESCAPE, $end);
				if($start === false){ $val .= substr($str, $end); break; }
				$val .= substr($str, $end, $start-$end);
				$start += strlen(TRANSLATION_CONSTANT_ESCAPE);
				$end = strpos($str, TRANSLATION_CONSTANT_ESCAPE, $start);
				if($end === false){ $val .= TRANSLATION_CONSTANT_ESCAPE.substr($str, $start); break; }
				$len = $end - $start;
				if($len !== 0) $val .= constant(substr($str, $start, $len));
				$end += strlen(TRANSLATION_CONSTANT_ESCAPE);
			} while(true);
			return $val;
		}

		$arr = array();
		$globals = json_decode(file_get_contents(TRANSLATIONS.'globals.json'), true);
		foreach($globals as $key=>$value){
			$arr[$key] = replaceVariables($value);
		}

		$trans = json_decode(file_get_contents(TRANSLATIONS.LANGUAGE_CODE.'.json'), true);
		foreach($trans as $key=>$value){
			$arr[$key] = replaceVariables($value);
		}

		define('TEXT', $arr);

		include($file);
		if($IS_DEV)
			foreach(headers_list() as $header)
				if(strpos($header, 'text/html') !== false){
					echo '<script type="text/javascript">(function(){const es=new EventSource("'.ROOT.DEV_SSE_URI.'");es.addEventListener("message",function(event){console.log(event.data);if(JSON.parse(event.data).reload)window.location.reload(true);});})();</script>';
					break;
				}
		exit(0);
	}

	// return contents of file
	$mimeType = file_to_mime($file);
	header('Content-Type: '.$mimeType);
	if($mimeType === 'video/quicktime' || $mimeType === 'video/mp4' || $mimeType === 'video/x-m4v' || $mimeType === 'video/3gpp'){
		// video files
		header('Content-Length: '.filesize($file));
		header('Accept-Ranges: bytes');
	}
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
			reloadEnv();
			if($cacheSec >= 0 && !isDevMode()) setCache($cacheSec); else noCache();
		}
		if($dir === _CSS) include(CSS_COMPONENTS.'css-config.php');
		if($dir === _JS) include(JS_COMPONENTS.'js-config.php');
		respondWithFile(STATICS.FULL_REQUEST, false, true);
	}
}


// process favicon.ico
if(FULL_REQUEST === 'favicon.ico') respondWithFile((FAVICON_FILE && !empty(FAVICON_FILE)) ? FAVICON_FILE : _VIEWS.'favicon.ico');


// process sitemap.xml
if(FULL_REQUEST === 'sitemap.xml'){
	respondWithFile('sitemap.php'); // in root
	return;
}


// all other files including PHP files
respondWithFile(VIEWS.REQUEST);

?>
