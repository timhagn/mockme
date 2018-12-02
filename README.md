# MockMe - Drupal MockUp Widget

With MockMe you're going to be able to create mock ups.  
But that's not all, you can do it directly in your Form,  
with image uploading or grabbing a website directly included!

Best of all, it's made with ReactJS, so you won't have
to wait for a mock up to be created and returned to the Form.

### MockMe Widget requirements
* Core File & Image Modules
* screen library
* html2canvas library
* html5-device-mockups 

### MockMe Widget installation instruction
Under the hood, MockMe utilizes the following libraries:

* [html5-device-mockups](https://pixelsign.github.io/html5-device-mockups/) - the mock up library
* [thml2canvas](https://html2canvas.hertzen.com/) - mock up creation to canvas
* [screen](https://github.com/microweber/screen/tree/v2.0.0) - website screenshot generator through PhantomJS

Before installing the module, be sure to add these lines to your `composer.json`:

```
...
    "scripts": {
        "post-install-cmd": [
            "PhantomInstaller\\Installer::installPhantomJS"
        ],
        "post-update-cmd": [
            "PhantomInstaller\\Installer::installPhantomJS"
        ]
    }
...    
```

As it isn't on drupal.org at the moment, the easiest way to acquire it, is by
requiring it through adding the following lines to `composer.json`:

```
    "repositories": [
        {
            "type": "git",
            "url": "git@bitbucket.org:timhagn/mockme.git"
        }
    ]
``` 

Afterwards you can use `"composer require timhagn/mockme`.

Switch to the `modules/contrib/mockme` folder and execute: 

```
git submodule update --init --force
```

This one fetches the mockme(-react) sub-repository.

Next, download the libraries into your folder of the same name:   
1. Download html2canvas library (version 1.0.0-alpha.12 or later) from 
   [https://html2canvas.hertzen.com/](https://html2canvas.hertzen.com/)
2. Unzip it into the libraries folder, so that there's a
   `libraries/html2canvas/html2canvas.min.js` file, in addition to the other 
   files included in the library.
3. Download html5-device-mockups from pixelsign's 
   [GitHub repository](https://github.com/pixelsign/html5-device-mockups).
4. Unzip it into the libraries folder and place it under 
   `libraries/html5-device-mockups`.
   
Enable the module in Drupal or use `drush en mockme`.
   
**Now you can change the ImageWidget in a Content Type to a "MockMe Image" widget!**



### Troubleshooting

Should PhantomJS not produce results, look in your server's `error.log` and 
the `.htaccess` file in `sites/default/files`. Remove `-ExecCGI` and `-Includes`
from it, so PhantomJS may be run by PHP, if necessary.  
  


#### TODO:
- add some settings to the widget
- add tests
- ...
- create Project on drupal.org...
