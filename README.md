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
Inside of the `views` folder you put your PHP or HTML files that define the layout of each page view of your website. 
The structure inside the `views` folder is important because it also defines how the URLs, that the user calls, will look like later on.  
For example if you place a file the following `views/login/index.php` then the resulting URL to request the file 
will be  `https://<yourDomain>/<languageCode>/login/`.

#### `translations/`
The `translations` directory contains JSON files that contain the actual text in different languages.  
Inside the `translations/globals.json` you can define text variables that should be available in every language  
e.g. all the native language names.  
Longer texts like legal notices you can but into separate files inside the `translations` directory and include 
the content of a fiel using the following `<?php include(TRANSLATIONS.<pathInsideTranslationsDir>); ?>`

#### `css-components/`
You can put CSS files into the `css-components` directory, however they can not directly be called from an URL!  
Instead you can define in `css-components/css-config.php` CSS file names that should be generated as keys 
and as values an array of CSS file names you've created inside `css-components`. Calling a file name defined as key will 
automatically generate the appropriate file inside `css` consisting of the contents of the file names defined in the value array. 
The `css` folder in which the generated files are placed is publicly available under `https://<yourDomain>/css/`. 
If a file inside the `css-components` folder has changed the generated files inside the `css` folder will be automatically updated. 

#### `css/`
Files automatically generated out of the `css-components` will be placed here. 
However you can also directly place static CSS files inside of this folder. 
All files of the `css` folder are publicly accessible over the URL `https://<yourDomain>/css/`.

#### `js-components/`
You can put JavaScript files into the `js-components` directory, however they can not directly be called from an URL!  
Instead you can define in `js-components/js-config.php` JavaScript file names that should be generated as keys 
and as values an array of JavaScript file names you've created inside `js-components`. Calling a file name defined as key will 
automatically generate the appropriate file inside `js` consisting of the contents of the file names defined in the value array. 
The `js` folder in which the generated files are placed is publicly available under `https://<yourDomain>/js/`. 
If a file inside the `js-components` folder has changed the generated files inside the `js` folder will be automatically updated. 

#### `js/`
Files automatically generated out of the `js-components` will be placed here. 
However you can also directly place static JavaScript files inside of this folder. 
All files of the `js` folder are publicly accessible over the URL `https://<yourDomain>/js/`.

#### `scripts/`
TODO


#### `scripts/caching.php` 
Script that can be included and defines some functions to easily set HTTP headers for caching. 
It is also used by the framework to set caching headers for CSS, JavaScript, images and downloads.

#### `images/`
The `images` folder contains all the images. You can create subdirectories in it as you like. 
Referencing images inside your code looks like the following `<?php echo IMAGES.'<pathInsideImagesDir>'; ?>` 
where the keyword `IMAGES` will get replaced with the relative path to the `images/` directory. So you need just to 
append your path as if you were inside the `images/` directory.
For  example `<?php echo IMAGES.'favicons/favicon.ico'; ?>`

#### `downloads/`
Put here other static files that are not CSS nor Javascript files. You can 
reference files in your code the following `<?php echo DOWNLOADS.'<pathInsideDownloadsDir>'; ?>`.
For  example `<?php echo DOWNLOADS.'manuals/de.pdf'; ?>`

#### `sitemap.php`
The `sitemap.php` file automatically generates and updates a `sitemap.xml` file. 
Simply define the individual URLs of your website at the top of the `sitemap.php`. 
Language code prefixes will be automatically added to the URLs.

#### `.env`
Contains environment variables that get loaded for every request and can be accessed either by referecing with `$_ENV`
or by calling `getenv()`. 
Secrets that should never be exposed must be defined as environment variables such as credentials for database for example.

### Global Constants
* `CSS` TODO
* `JS` TODO
* `IMAGES` TODO
* `SCRIPTS` TODO
* `DOWNLOADS` TODO
* `TEXT` Array loaded with the translation variables from the `translations/globals.json` and the language specific translation file inside `translations/`
* `LANGUAGE_CODE` TODO
* `REQUEST` TODO
* `FULL_REQUEST` TODO
* `BASE` TODO
* `ROOT` TODO
* `BASE_DEPTH` TODO
* `ROOT_DEPTH` TODO
* `SUPPORTED_LANGUAGES` TODO
* `$_ENV` Similar to `$_GET` but instead to retrieve to environment variables defined in the `.env` file

### Example files that can be deleted
The framework also comes with some example files that can be deleted if wanted:
* `css-components/default.css` and `css-components/start.css` are example CSS component files for the example start page
* `images/favicons` Idea is to place the favicon in different formats and resolutions for different browser in here
* `js-components/default.js` and `js-components/start.js` are example JavaScript component files for the example start page
* `scripts/metatags.php` Script containing generic HTML metatags that should be included in every page
* `translations/en.json` and `translations/de.json` are two example translation files
* `views/browserconfig.xml` File used by browsers to get metadata about the website
* `views/index.php`, `views/start/` and `views/error/` are two example pages
* `views/language-editor/` primitive editor to easily edit the translation files, however it is **not secure** and should not be deployed!
* `views/manifest.json` File used by browsers to get metadata about the website
* `views/robots.txt` Inside of this file you can declare URLs that should not be crawled and indexed by search engines
