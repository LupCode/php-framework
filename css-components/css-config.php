<?php


/**
 * Define files that should be built as key and as value an array of source files the content should be read from. 
 * The built file will be placed in 'static/css/' and the source files will be read from this directory 'js-components/'
 */
define('BUILD_FILES', array(

    "start.css" => array("default.css", "start.css"),

));



// ---------------------------------------------------------------------------------------
// INTERAL PROCESSING OF REQUESTS
// ---------------------------------------------------------------------------------------

$file = substr(FULL_REQUEST, strlen(_CSS)+1);
if(isset(BUILD_FILES[$file])){
    $fullFile = STATICS.FULL_REQUEST;
    $lastUpdate = file_exists($fullFile) ? filemtime($fullFile) : false;
    if($lastUpdate)
        foreach(BUILD_FILES[$file] as $srcFile){
            $fullSrcFile = CSS_COMPONENTS.$srcFile;
            $lu = file_exists($fullSrcFile) ? filemtime($fullSrcFile) : false;
            if(!$lu || $lu > $lastUpdate){ $lastUpdate = false; break; }
        }
    if($lastUpdate) return; // no update needed

    // build new file
    $contents = '';
    foreach(BUILD_FILES[$file] as $srcFile){
        $fullSrcFile = CSS_COMPONENTS.$srcFile;
        $cnt = file_exists($fullSrcFile) ? file_get_contents($fullSrcFile) : false;
        $contents .= $cnt ? $cnt.'
' : '';
    }
    file_put_contents($fullFile, $contents);
}
?>