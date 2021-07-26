# PHP Framework
A PHP framework for easy creation of high-performance, multi-lingual websites.  

This framework does not contain pre-defined HTML code or something like that, 
the purpose of it is to organize your code and eliminate code duplication while 
providing maximum performance. 

## How to use
In the following is described how to build a website with this framework:

### Structure
#### `config.php`
The config file gets included before the execution of any PHP file inside the `views` directory. 
The idea is to define your custom constants/variables inside of it. 
However credentials should be placed in the `.env` file for security reasons.
The global constants can already be used inside of the `config.php`.

#### `views/`
Inside of the `views` folder you put your PHP or HTML files that define the layout for each page view of your website. 
The structure inside the `views` folder is important because it also defines how the URLs, that the user calls, will look like later on.  
For example if you place a file the following `views/login/index.php` then the resulting URL to request the file 
will be  `https://<yourDomain>/<languageCode>/login/`.

#### `translations/`
The `translations` directory contains JSON files that contain the actual text in different languages.  
Inside the `translations/globals.json` you can define text variables that should be available in every language  
e.g. all the native language names.  
Important is that the name of the JSON files should be the language code the file represents e.g. `en.json`, `zh-Hans.json`, etc  
Longer texts like legal notices you can but into separate files inside the `translations` directory and include 
the content of a fiel using the following `<?php include(TRANSLATIONS.<pathInsideTranslationsDir>); ?>`

#### `css-components/`
You can put CSS files into the `css-components` directory, however they can not directly be called from an URL!  
Instead you can define in `css-components/css-config.php` CSS file names that should be generated as keys 
and as values an array of CSS file names you've created inside `css-components`. Calling a file name defined as key will 
automatically generate the appropriate file inside `static/css/` consisting of the contents of the file names defined in the value array. 
The `static/css/` folder in which the generated files are placed is publicly available under `https://<yourDomain>/css/`. 
If a file inside the `css-components` folder has changed the generated files inside the `static/css/` folder will be automatically updated. 

#### `js-components/`
You can put JavaScript files into the `js-components` directory, however they can not directly be called from an URL!  
Instead you can define in `js-components/js-config.php` JavaScript file names that should be generated as keys 
and as values an array of JavaScript file names you've created inside `js-components`. Calling a file name defined as key will 
automatically generate the appropriate file inside `static/js/` consisting of the contents of the file names defined in the value array. 
The `static/js/` folder in which the generated files are placed is publicly available under `https://<yourDomain>/js/`. 
If a file inside the `js-components` folder has changed the generated files inside the `js` folder will be automatically updated. 

#### `scripts/`
Inside the `scripts` directory you can put PHP scripts that you can call from the views files either with 
`include(SCRIPTS.'<fileInsideScriptsDir>');` or with `require_once(SCRIPTS.'<fileInsideScriptsDir>');`.  
It also makes sense to put recurring parts of the views files like header and footer in there e.g. `scripts/header.php` 
and then inside a view file e.g. `views/start/index.php` you can include it with `include(SCRIPTS.'header.php');`. 

#### `scripts/caching.php` 
Script that can be included and defines some functions to easily set HTTP headers for caching. 
It is also used by the framework to set caching headers for CSS, JavaScript, images and downloads.

#### `static/`
Everything inside the `static` directory is directly publicly accessible without the word `static` in the URL e.g. 
`static/css/start.css` --> `https://<yourDomain>/css/start.css`.  
Every direct subdirectory of the `static` directory e.g. `static/images/` gets a constant with the same name in uppercase defined e.g. `IMAGES` 
with a relative path the named subdirectory such that the constant can be used as prefix in links e.g. `<img src="<?php echo IMAGES; ?>favicons/favicon.ico">`

#### `static/css/`
Files automatically generated out of the `css-components` will be placed here. 
However you can also directly place static CSS files inside of this folder. 
All files of the `static/css/` folder are publicly accessible over the URL `https://<yourDomain>/css/`.

#### `static/js/`
Files automatically generated out of the `js-components` will be placed here. 
However you can also directly place static JavaScript files inside of this folder. 
All files of the `static/js/` folder are publicly accessible over the URL `https://<yourDomain>/js/`.

#### `static/images/`
The idea of the `images` folder is that it contains all the images, however it is not required and can be deleted if wanted. 
Referencing images inside your code looks like the following `<?php echo IMAGES.'<pathInsideImagesDir>'; ?>` 
where the constant `IMAGES` will get replaced with the relative path to the `images/` directory. So you need just to 
append your path as if you were inside the `images/` directory.
For  example `<?php echo IMAGES.'favicons/favicon.ico'; ?>`

#### `sitemap.php`
The `sitemap.php` file automatically generates and updates a `sitemap.xml` file. 
Simply define the individual URLs of your website at the top of the `sitemap.php`. 
Language code prefixes will be automatically added to the URLs.

#### `.env`
Contains environment variables that get loaded for every request and can be accessed either by referecing with `$_ENV`
or by calling `getenv()`. 
Secrets that should never be exposed must be defined as environment variables such as credentials for database for example.

#### `.htaccess`
The `.htaccess` files tells the apache server that all HTTP requests inside of this repository should be handled by the `index.php` file.

### Global Constants
* `CSS` Relative path to the CSS directory `static/css/` that can be used for links
* `JS` Relative path to the JavaScript directory `static/js/` that can be used for links
* For each subdirectory of `static/` an equaly named constant with an relative path to the subdirectory gets created e.g. `static/images/` --> `IMAGES` 
that can be used for links
* `SCRIPTS` Path to the internal `scripts/` directory, can only be used with `include()` or `require_once()`
* `TEXT` Array loaded with the translation variables from the `translations/globals.json` and the language specific translation file inside `translations/`
* `LANGUAGE_CODE` Language code of the language in which the page should be displayed e.g. `en` for English, `de` for German, etc
* `REQUEST` Requested URI without the language prefix in front of it e.g. `users/login/index.php` or `css/start.css`
* `FULL_REQUEST` Requested URI with the language prefix in front of it if given e.g. `en/users/login/index.php` or `css/start.css`
* `BASE` Relative path to the language specific root that can be used to send user back to root URL without droping the language prefix
* `ROOT` Relative path to absolute root which means that also the language prefix will be dropped
* `BASE_DEPTH` Integer how deep the requested file is without the language prefix taken into account e.g. `en/users/login/index.php` --> `2`
* `ROOT_DEPTH` Integer how deep the requested file is including the language prefix e.g. `en/users/login/index.php` --> `3`
* `SUPPORTED_LANGUAGES` Array with language codes that were found in the `translations/` directory
* `$_ENV` Similar to `$_GET` but instead to retrieve to environment variables defined in the `.env` file

### Example files that can be deleted
The framework also comes with some example files that can be deleted if wanted:
* `css-components/default.css` and `css-components/start.css` are example CSS component files for the example start page
* `js-components/default.js` and `js-components/start.js` are example JavaScript component files for the example start page
* `scripts/metatags.php` Script containing generic HTML metatags that should be included in every page
* `static/css/start.css` Example CSS file that was generated by the start page as defined in `css-components/css-config.php`
* `static/images/favicons` Idea is to place the favicon in different formats and resolutions for different browser in here
* `static/js/start.js` Example JavaScript file that was generated by the start page as defined in `js-components/js-config.php`
* `translations/en.json` and `translations/de.json` are two example translation files
* `views/error/` Contains index file that shows an example error page
* `views/language-editor/` primitive editor to easily edit the translation files, however it is **not secure** and should not be deployed!
* `views/start/` Contains index file that shows an example start page
* `views/browserconfig.xml` File used by browsers to get metadata about the website
* `views/index.php` Redirects to the example start page
* `views/manifest.json` File used by browsers to get metadata about the website
* `views/robots.txt` Inside of this file you can declare URLs that should not be crawled and indexed by search engines
