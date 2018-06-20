<?php

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
# $markdown->paragraphTags       = true;
# $markdown->shortcodeConvert    = 'jekyll';
# $markdown->shortcodePreview    = true;
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
