<?php


// Gets included before every PHP file and can be used to define custom constants or functions.
// All variables of the framework are already available here e.g.:

define('DOMAIN', implode(".", array_slice(explode(".", $_SERVER['SERVER_NAME']), -2, 2)));

define('NAME', 'LupCode'); // can be removed, just for example

?>