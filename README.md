# Wordpress Markdown Generator

Tool used to export all Wordpress Posts & Assets into Markdown. While exporting this has the ability to:

- Create Markdown Files in Data/Collections Directory
- Find Metadata
- Map Metadata
- Metadata to Front Matter
- Front Matter Defaults
- Find Images
- Add only Used Images to specified Assets Directory
- Convert Image URLs
- Find HREF URLs
- Replace HREF URLs
- Find Shortcodes
- Convert Shortcodes to new Static Site Generator Tags
- Shortcodes Preview / Seperate File with Shortcodes
- Config Yaml File Autmatic Setup
- Permalink Setup based on WP Setup


## Installation

This should only be added to local instance of a Wordpress site. In the sites active theme folder, add the `WPMarkdown` folder then at the bottom of the themes `functions.php` file add:

```
/**
 * WPMarkdown
 *
 */
include_once 'WPMarkdown/_bootstrap.php';
```

## Setup

In the `config.php` file, to initiate build: 

1. Instantiate an instance of the `WPMardown` class
2. Set custom properties to specify the custom build
3. Run the class build method
4. Login to the local instance of the Wordpress Admin, this will fire the build in the `WPMarkdown` folder.


````
/**
 * Configuration Settings
 */

$markdown                        = new WPMarkdown();
# $markdown->postTypes           = array('post');
# $markdown->metaExclude         = array('id', 'slug', 'collection', '_yoast_wpseo_content_score');
# $markdown->metaMapping         = array('sub_navigation' => 'sticky_navigation');
# $markdown->metaDefaults        = array('layout' => 'default', 'boss' => 'ER');
# $markdown->buildDir            = 'build_10';
# $markdown->fileType            = '.md';
# $markdown->fmClean             = false;
# $markdown->shortcodeConvert    = 'jekyll';
# $markdown->shortcodePreview    = true;
# $markdown->paragraphTags       = true;
# $markdown->shortcodeFile       = true;
# $markdown->buildUploads        = 'src';
# $markdown->status              = 'draft';
# $markdown->limit               = 50;
# $markdown->offset              = 51;
# $markdown->buildCollectionsDir = '_cpts';
# $markdown->imagePrefixReplace  = array('https://bestcolleges.com', 'http://www.bestcolleges.com');
# $markdown->imagePrefix         = 'default'; 
# $markdown->urlPrefixReplace    = array('https://bestcolleges.com', 'http://www.bestcolleges.com', 'http://uwaterloo.ca');
# $markdown->urlPrefix           = 'https://www.cloudinary.com/uploads/';
$markdown->build();
````


## Properties


##### Post Type(s)
```
$markdown->postTypes = array('post');
```
_Optional._ This will SQL query only the `Post` post type on the site, you may specific any Custom Post Type you'd like. Otherwise, if this property isn't include will query all post types on the site.


##### Status
```
$markdown->status = 'draft';
```
_Optional. Default: publish_ This will SQL query only the draft status posts.


##### Limit
```
$markdown->limit = 5;
```
_Optional._ Limit the amount of post queried, good for batch parsing if needed.


##### Offset
```
$markdown->offset = 6;
```
_Optional._ Offset the amount of post queried, good for batch parsing if needed.


##### Front Matter Cleanup
```
$markdown->fmClean = false;
```
_Optional. Default: true_ This removes any Metadata from displaying in the Front Matter if value is empty. Otherwise, it'll show all Metadata attached to Post.



##### Paragraph Tags
```
$markdown->paragraphTags = true;
```
_Optional. Default: false_ Add WPAutoP to content.



##### Metadata/Front Matter Exclusion
```
$markdown->metaExclude = array('id', 'slug', 'collection', '_yoast_wpseo_content_score');
```
_Optional._ Exclude certain Metadata from displaying in the Front Matter.


##### Metadata/Front Matter Mapping
```
$markdown->metaMapping = array('sub_navigation' => 'sticky_navigation');
```
_Optional. Defaults: yoast_wpseo_metadesc = description, wp_page_template = layout, yoast_wpseo_title = title_ Rename Metadata to display correctly/differently in Front Matter keys.


##### Metadata/Front Matter Defaults
```
$markdown->metaDefaults = array('layout' => 'default', 'boss' => 'ER');
```
_Optional._ Set default Metadata to add it to the Front Matter key values pairing.


##### Build Directory
```
$markdown->buildDir = 'build_15';
```
_Optional. Default: build_ Build folder to add the Post Type Markdown files, Config, Shortcodes & Assets.


##### Asset Uploads Directory
```
$markdown->buildUploads = 'src';
```
_Optional. Default: assets/uploads_ Build folder to add the Assets.


##### Post Type Markdown Directory
```
$markdown->buildCollectionsDir = '_data';
```
_Optional. Default: collections_ Build folder to add the Post Type Markdown files.


##### Markdown File Extension
```
$markdown->fileType = '.html';
```
_Optional. Default: .md_ Change the Markdown files extension type.


##### Shortcodes Conversion to Static Generator Tag
```
$markdown->shortcodeConvert = 'jekyll';
```
_Optional. Options: jekyll, more to come..._ Convert Wordpress Shortcodes to Static Generator Tags.


##### Shortcode File
```
$markdown->shortcodeFile = true;
```
_Optional._ This adds the Shortcodes used throughout the site in a separate file called shortcodes.txt.


##### Shortcode Preview Usage
```
$markdown->shortcodePreview = true;
```
_Optional._ This adds the Shortcodes used on post beneath the Front Matter on the Markdown file.


##### Image Prefixes to Replace
```
$markdown->imagePrefixReplace = array('https://bestcolleges.com', 'http://www.bestcolleges.com');
```
_Optional._ In addition to the WP Home URL and WP Uploads Directory, these specified URLs are also used to find and replace images that are prefixed.


##### Image Prefix to Use
```
$markdown->imagePrefix = 'default'; 
```
_Optional. Default: default_ The way the assets directory is setup with the assets in the Markdown files. Default, will be the default setting Wordpress has on its backend(usually date based). Flat, is all assets into a flat directory, Markdown Files Updated. Custom URL, used for CDN image setup, add the url and they'll update in your Markdown files. Keep in mind, all the assets are added to the Build folder for you, do as you wish.


##### URL Prefixes to Replace
```
$markdown->urlPrefixReplace = array('https://bestcolleges.com', 'http://www.bestcolleges.com', 'http://uwaterloo.ca');
```
_Optional._ URL's to find and replace throughout the site, Markdown files will reflect this.


##### URL Prefixes to Use
```
$markdown->urlPrefix = 'https://www.cloudinary.com/uploads/';
```
_Optional. Default: /_ New URL to use instead of the URL Prefix Replacements.



## Tips

If you'd like to play around with the class property settings, increment the `buildDir` property after you adjust the settings and refresh the Wordpress Admin page. This will initiate the new build in the WPMarkdown Directory.

```
$markdown->buildDir = 'build_2';
```
