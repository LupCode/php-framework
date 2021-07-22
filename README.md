# PHP Framework (Work in progress)
A PHP framework for easy creation of high-performance, multi-lingual websites.  

This framework does not contain pre-defined HTML code or something like that, 
the purpose of it is to organize your code and eliminate code duplication while 
providing maximum performance. 

## How to use
In the following is described how to build a website with this framework:

### Structure
#### `views/`
Inside of the `views` folder you put your PHP or HTML files that define the layout of each page view of your website. 
The structure inside the `views` folder is important because it also defines how the URLs, that the user calls, will look like later on.  
For example if you place a file the following `views/login/index.php` then the resulting URL to request the file 
will be  `https://<yourDomain>/<languageCode>/login/`.

#### `translations/`
The `translations` directory contains JSON files that contain the actual text in different languages.  
Inside the `translations/globals.json` you can define text variables that should be available in every language  
e.g. all the native names 

#### `css-components/`
You can put CSS files into the `css-components` directory, however they can not directly be called from an URL!  
Instead you can define in the `css-components/routes.json` file names that should be generated as keys 
and as values an array of files names you've created inside `css-components`. Calling a file name defined as key will 
automatically generate the appropriate file consisting of the contents of the file names defined in the value array. 
Those generated files will be put in the `css` folder that is publicly accessible over the URL `https://<yourDomain>/css/`. 
If a file inside the `css-components` folder gets changed the generated files inside the `css` folder will be automatically updated. 

#### `css/`
Files automatically generated out of the `css-components` will be placed here. 
However you can also directly place static CSS files inside of here if you don't want to use the CSS components system. 
All files of the `css` folder are publicly accessible over the URL `https://<yourDomain>/css/`.

#### `js-components/`
TODO

#### `js/`
TODO

#### `scripts/`
TODO

#### `images/`
The `images` folder contains all the images. You can create subdirectories in it as you like. 
Referencing images inside your code looks like the following `<?php echo IMAGES.'<pathInsideImagesDir>'; ?>` 
where the keyword `IMAGES` will get replaced with the relative path to the `images/` directory. So you need just to 
append your path as if you were inside the `images/` directory.
For  example `<?php echo IMAGES.'backgrounds/start-background.jpg'; ?>`

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

### Global Variables
* `CSS` TODO
* `JS` TODO
* `IMAGES` TODO
* `SCRIPTS` TODO
* `DOWNLOADS` TODO
* `TEXT` Array loaded with the translation variables from the `translations/globals.json` and the language specific translation file inside `translations/`
* `$_ENV` Similar to `$_GET` but instead to retrieve to environment variables defined in the `.env` file
* `BASE` Relative path to root index file but still with the language prefix e.g. `https://<yourDomain>/en/`
* `ROOT` Relative path to absolute root index file without the language prefix e.g. `https://<yourDomain>/`
* `LANGUAGE_CODE` TODO
* `REQUEST` TODO
* `FULL_REQUEST` TODO
