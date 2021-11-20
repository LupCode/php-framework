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

    '/' => array('1.0', 'yearly'),
	'start/' => array('0.9', 'yearly'),

));


/** define here custom "<url>...</url>" entries */
define('CUSTOM_URL_ENTRIES', "");


/** Duration in seconds how long a generated sitemap should be cached before it gets newly generated */
define('SITEMAP_CACHE_SECONDS', 3600); // 1 hour


// ---------------------------------------------------------------------------------------
// Generating Sitemap
// ---------------------------------------------------------------------------------------

// check if sitemap file already generated and inside cache periode
$sitemapFile = 'sitemap.xml';
$fmt = file_exists($sitemapFile) ? filemtime($sitemapFile) : false; // last modified time of generated sitemap file
if($fmt && (time() + SITEMAP_CACHE_SECONDS) < $fmt){ echo file_get_contents($sitemapFile); exit(); }

// newly generate sitemap
$timeFormat = "Y-m-d\TH:i:sP";
$today = date($timeFormat);
$domain = $_SERVER['SERVER_NAME'];
$c = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";
foreach(SITEMAP_URLS as $path => $info){
    $absPath = VIEWS.$path; if(!file_exists($absPath)) continue; // skip non existing URLs
    $m = filemtime($absPath); $m = ($m ? date($timeFormat, $m) : $today);
    foreach(SUPPORTED_LANGUAGES as $lang){
        $c .= "\t<url>\n\t\t<loc>https://".$domain."/".$lang."/".$path."</loc>\n\t\t<lastmod>".$m."</lastmod>\n";
        $c .= "\t\t<priority>".$info[0]."</priority>\n\t\t<changefreq>".$info[1]."</changefreq>\n\t</url>\n";
    }
}
$c .= CUSTOM_URL_ENTRIES."</urlset>";
file_put_contents('sitemap.xml', $c);
header('Content-Type: text/xml', true);
echo $c;
exit();
?>