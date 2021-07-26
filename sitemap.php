<?php

/** 
 * The sitemap is a dictonary for search engines to easier find relevant content of your page.
 * For each important URL of a website a sitemap entry can be generated with a priority and change frequency. 
 * The priority tells how important the URL is compared to other URLs of the website:  0.0 - 1.0
 * The change frequency tells how often the content of the page is about to change:  always, hourly, daily, weekly, monthly, yearly, never
 * Array defining for each path inside the views directory an array with a priority(0.0 - 1.0) and 
 * a change frequency (always, hourly, daily, weekly, monthly, yearly, never)
 */
define('SITEMAP_URLS', array(
	'home/' => array('1.0', 'monthly'),
	'about/' => array('0.8', 'yearly')
));


/** define here custom '<url>...</url>' entries */
define('CUSTOM_URL_ENTRIES', '');


/** Duration in seconds how long a generated sitemap should be cached before it gets newly generated */
define('SITEMAP_CACHE_SECONDS', 3600); // 1 hour


// ---------------------------------------------------------------------------------------
// Generating Sitemap
// ---------------------------------------------------------------------------------------

// check if sitemap file already generated and inside cache periode
$fmt = filemtime(SITEMAP_FILE); // last modified time of generated sitemap file
if($fmt && (time() + SITEMAP_CACHE_SECONDS) < $fmt){ echo file_get_contents(SITEMAP_FILE); exit(); }

// newly generate sitemap
$timeFormat = "Y-m-dTH:i:sP";
$today = date($timeFormat);
$langs = getSupportedLanguages();
$domain = $_SERVER['SERVER_NAME'];
$c = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
';
foreach(SITEMAP_URLS as $path => $info){
    foreach($langs as $lang){
        $m = filemtime(_VIEWS.$path);
        $c .= 
'   <url>
        <loc>https://'.$domain.'/'.$kang.'/'.$path.'</loc>
        <lastmod>'.($m ? date($timeFormat, $m) : $today).'</lastmod>
        <priority>'.$info[0].'</priority>
        <changefreq>'.$info[1].'</changefreq>
    </url>
';
    }
    $c .= '
';
}
$c .= CUSTOM_URL_ENTRIES.'
</urlset>';
file_put_contents('sitemap.xml', $c);
echo $c;
exit();
?>