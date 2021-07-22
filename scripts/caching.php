<?php


/**
 * Signals that page should not be cached at all
 */
function noCache(){
	header('Expires: '.date("D, d M Y H:i:s").' GMT', true);
	header('Cache-Control: no-store, no-cache, must-revalidate,  post-check=0, pre-check=0', true);
	header('Pragma: no-cache', true);
}


/**
 * Signals that the page should be cached for a certain amount of seconds
 * @param int $seconds Seconds how long the page should be cached
 * @param bool $private If true only the client is allowed to cached, proxies not (default false) 
 */
function setCache($seconds=0, $private=false){
	if(!$seconds || !is_int($seconds) || $seconds<=0){ noCache(); return true; }
	header("Cache-Control: ".($private ? "private" : "public").", max-age=".$seconds, true);
}


/**
 * Signals that the page should be cached until a given UTC time in seconds
 * @param int $timeUTCSec UTC time in seconds until page should be cached
 * @param bool $private If true only the client is allowed to cached, proxies not (default false) 
 */
function setCacheUntil($timeUTCSec, $private=false){
	setCache($timeUTCSec-time(), $private);
}

/**
 * Signals that the page should be cached until the next full given unit (e.g. until the next full hour)
 * @param int $unitSeconds Seconds defining the unit interval (e.g. 3600 for hourly)
 * @param bool $private If true only the client is allowed to cached, proxies not (default false) 
 */
function setCacheUntilFullUnit($unitSeconds=3600, $private=false){
	$time = time();
	setCache((intval($time / $unitSeconds) + 1)*$unitSeconds - $time, $private);
}

?>