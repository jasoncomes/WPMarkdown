<?php

class WPMarkdown 
{

    /**
     * Post Status to Query
     * @var strs
     */
    public $status = 'publish';


    /**
     * Amount of Posts to Query
     * @var strs
     */
    public $limit = 0;


    /**
     * Post Offset to Query
     * @var strs, arrays
     */
    public $offset = 0;


    /**
     * Default Post Types to Query
     * @var array
     */
    public $postTypes = array();


    /**
     * Extension type for files.
     * @var str
     */
    public $fileType = '.md';


    /**
     * Build - Directory
     * @var str
     */
    public $buildDir = 'build';


    /**
     * Build - Collections Directory
     * @var str
     */
    public $buildCollectionsDir = '_collections';


    /**
     * Build - Asset Uploads
     * @var str
     */
    public $buildUploads = 'assets/uploads';


    /**
     * Build Directory Exists
     * @var array
     */
    private $buildBase = '';


    /**
     * Image - Setup
     * @var str
     * Options: default, flat, custom url
     */
    public $imagePrefix = 'default';


    /**
     * Image Prefix Replacements
     * @var array
     */
    public $imagePrefixReplace = array();



    /**
     * Image Prefix Replacements Reg
     * @var array
     */
    private $imagePrefixReplaceReg = array();


    /**
     * Meta Defaults
     * @var array
     */
    public $metaDefaults = array();   


    /**
     * Meta Mapping - User Defined 
     * @var array
     */
    public $metaMapping = array();


    /**
     * Meta Mapping - Defaults
     * @var array
     */
    private $metaMappingDefaults = array(
        '_yoast_wpseo_metadesc' => 'description', 
        '_wp_page_template'     => 'layout', 
        '_yoast_wpseo_title'    => 'seo_title'
    );


    /**
     * Meta Exclusions - User Defined
     * @var array
     */
    public $metaExclude = array();


    /**
     * Meta to Exclude
     * @var array
     */
    private $metaExcludeDefaults = array(
        '_edit_lock', 
        '_edit_last',
        'collection_rewrite',
        'collection_public'
    );


    /**
     * Front Matter if Meta empty, don't show.
     * @var bool
     */
    public $fmClean = true;


    /**
     * Add WP Paragraph Tags
     * @var bool
     */
    public $paragraphTags = false;


    /**
     * Shortcodes Conversion
     * @var string
     * jekyll, etc.
     */
    public $shortcodeConvert = false;


    /**
     * Shortcode Preview
     * @var bool
     */
    public $shortcodePreview = false;


    /**
     * Shortcode File
     * @var bool
     */
    public $shortcodeFile = false;


    /**
     * Shortcodes - Globals
     * @var array
     */
    private $shortcodes = array();

    
    /**
     * Shortcodes - Post
     * @var array
     */
    private $postShortcodes = array();


    /**
     * URLs 
     * @var string
     */
    public $urlPrefix = '/';


    /**
     * URLs Prefix Replacements
     * @var array
     */
    public $urlPrefixReplace = array();


    /**
     * Home URL
     * @var str
     */
    public $homeUrl = '';


    /**
     * Post Collected
     * @var array
     */
    private $posts = array();


    /**
     * Collections
     * @var array
     */
    private $collections = array();


    /**
     * Taxonomies Collected
     * @var array
     */
    private $taxonomies = array();


    /**
     * Tag(Shortcodes) Patterns
     * @var str
     */
    private $shortcodePatterns = '';


    /**
     * Uploads Directory
     * @var array
     */
    private $wpUploadDir = array();


    /**
     * Construct ~ Setup Globals
     *
     */
    public function __construct()
    {
        add_action('init', array($this, 'initConstruct'), 999);
    }


    /**
     * Construct Init
     *
     */
    public function initConstruct()
    {
        $this->shortcodePatterns = get_shortcode_regex();
        $this->homeUrl           = home_url();
        $this->wpUploadDir       = wp_upload_dir();
    }


    /**
     * Build Process
     *
     */
    public function build() 
    {
        // Set Build Base Dir.
        $this->buildBase = realpath(dirname(__FILE__) . '/..') . '/' . $this->buildDir . '/';

        // Admin & Build Exists Check.
        if (!is_admin() || file_exists($this->buildBase)) {
            return;
        }

        // Build Directory.
        mkdir($this->buildBase, 0755, true);

        // Build.
        add_action('init', array($this, 'initBuild'), 1000);
    }


    /**
     * Build Init
     *
     */
    public function initBuild() 
    {
        // Meta Mapping
        $this->metaMapping = array_merge($this->metaMappingDefaults, $this->metaMapping);
        $this->metaExclude = array_merge(array('body', 'shortcodes', 'url'), $this->metaExcludeDefaults, $this->metaExclude);

        // URL Prefix Setup.
        if (!empty($this->urlPrefix) && !WPMarkdownHelpers::endsWith($this->urlPrefix, '/')) {
           $this->urlPrefix = $this->urlPrefix . '/';
        }

        // Image Prefix Setup.
        if (!empty($this->imagePrefix) && !in_array($this->imagePrefix, array('default', 'flat')) && !WPMarkdownHelpers::endsWith($this->imagePrefix, '/')) {
            $this->imagePrefix = $this->imagePrefix . '/';    
        }

        // Image Prefix Replace.
        $this->imagePrefixReplace[] = $this->homeUrl;
        $this->imagePrefixReplace[] = $this->wpUploadDir['baseurl'];
        $this->imagePrefixReplace[] = '/wp-content/uploads';

        foreach ($this->imagePrefixReplace as &$url) {
            $this->imagePrefixReplaceReg[] = preg_quote($url, '/');
        }

        // Setup Posts
        $this->setupPosts();

        // Build Collections
        $this->buildCollections();

        // Build Shortcodes
        $this->buildShortcodes(); 

        // Build Config
        $this->buildConfg();
    }


    /**
     * Setup Posts
     *
     */
    private function setupPosts()
    {
        // Query Posts
        $posts = $this->queryPosts();
        
        if (empty($posts)) {
            return;
        }

        // Build Post Object
        foreach ($posts as $post) {
            if ($this->paragraphTags) {
                $post['body'] = wpautop($post['body']); // Paragraph Tags    
            }
            $post['body']       = $this->getImages($post['body']); // Images
            $post['body']       = $this->replaceUrls($post['body']); // URLs
            $post['shortcodes'] = $this->getShortcodes($post['body']); // Shortcodes - Find
            $post['body']       = $this->replaceShortcodes($post['body'], $post['shortcodes']); // Shortcodes - Replace
            $post               = $this->getMeta($post); // Metadata
            $post               = $this->getTaxonomies($post); // Taxonomies & Terms
            $post               = $this->getCollections($post); // Collections
            $post               = $this->getPermalink($post); // Permalinks
            $post               = $this->getFeaturedImage($post); // Featured Image

            // Add to Posts
            $this->posts[$post['id']] = $post;

            // Post Shortcodes
            if (!empty($post['shortcodes'])) {
                $this->postShortcodes[] = array(
                    'id'         => $post['id'],
                    'title'      => $post['title'],
                    'url'        => $post['url'],
                    'shortcodes' => $post['shortcodes']
                );
            }
        }
    }


    /**
     * SQL Query Posts
     *
     */
    private function queryPosts() 
    {
        global $wpdb;

        $session = 'SET SESSION group_concat_max_len = 10000000;';
        $wpdb->query($session);

        $andPostTypes = !empty($this->postTypes) ? "AND posts.post_type IN ('" . implode("', '", $this->postTypes) . "')" : "";
        $limitOffset  = !empty($this->limit) ? "LIMIT {$this->offset}, {$this->limit}" : "";

        $sql = " 
            SELECT 
                posts.post_title title,
                posts.post_name slug,
                posts.ID id,
                posts.post_type collection,
                posts.post_date date,
                posts.post_excerpt description,
                posts.post_content body,
                GROUP_CONCAT(meta.meta_key ORDER BY meta.meta_key DESC SEPARATOR '||') as meta_keys, 
                GROUP_CONCAT(meta.meta_value ORDER BY meta.meta_key DESC SEPARATOR '||') as meta_values 
            FROM 
                $wpdb->posts posts 
            LEFT JOIN 
                $wpdb->postmeta meta on meta.post_id = posts.id 
            WHERE 
                posts.post_status = '{$this->status}'
            {$andPostTypes}
            GROUP BY 
                posts.ID
            {$limitOffset}
        ";

        return $wpdb->get_results($sql, ARRAY_A);
    }


    /** 
     * Get Metadata & Format
     *
     */
    private function getMeta($post) 
    {
        // Variables.
        $meta        = $this->metaDefaults;
        $keys        = explode('||', $post['meta_keys']);
        $values      = explode('||', $post['meta_values']);

        unset($post['meta_keys']);
        unset($post['meta_values']);

        if (!empty($keys) && !empty($values) && count($keys) === count($values)) {

            // Combine Array + Filter
            $filtered = array();

            foreach (array_combine($keys, $values) as $k => $v) {
                if (empty($v) || in_array($k, $this->metaExcludeDefaults)) {
                    continue;
                }
                $filtered[$k] = $v;
            }

            $meta = array_merge($meta, $filtered);


            // MetaData Mapping
            foreach ($meta as $k => $v) {
                // Unserialize
                $v = maybe_unserialize(maybe_unserialize($v)); // Not sure why but had to do this.

                if (is_string($v)) {
                    // Decode
                    $v = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $v);

                    // Strip Slashes
                    $v = stripcslashes($v);
                }

                if (!empty($this->metaMapping[$k])) {
                    $meta[$this->metaMapping[$k]] = $v;
                    unset($meta[$k]);
                } else {
                    $meta[$k] = $v;
                }

                // Check ACF
                if (!empty($v) && is_string($v) && substr($v, 0, 6) === 'field_') {
                    unset($meta[$k]);
                }
            }
        }

        return array_merge($post, $meta);
    }


    /**
     * Get Collections
     *
     */
    private function getCollections($post) 
    {
        // Variables.
        $collection = in_array($post['collection'], array('post', 'page')) ? $post['collection'] . 's' : $post['collection'];

        // Create Collection, if doesn't exist
        if (empty($this->collections[$collection])) {
            $postObject = get_post_type_object($post['collection']);
            $this->collections[$collection]['public'] = !empty($postObject->public) ? $postObject->public : '';
            $this->collections[$collection]['rewrite'] = !empty($postObject->rewrite) ? $postObject->rewrite['slug'] : '';
            $this->collections[$collection]['has_archive'] = !empty($postObject->has_archive) ? $postObject->has_archive : '';
        }

        // Add Post Redirects
        if (!empty($this->collections[$collection])) {
            $post['collection_rewrite'] = $this->collections[$collection]['rewrite'];
            $post['collection_public']  = $this->collections[$collection]['public'];  
        }

        // Set Post Collection
        $post['collection'] = $collection;
        
        return $post;       
    }


    /**
     * Get Taxonomies & Terms
     *
     */
    private function getTaxonomies($post) 
    {
        // Taxonomies & Terms
        if (empty($this->taxonomies[$post['collection']])) {
            $this->taxonomies[$post['collection']] = get_object_taxonomies($post['collection'], 'names');
        }

        if (!empty($this->taxonomies[$post['collection']])) {
            foreach ($this->taxonomies[$post['collection']] as $taxonomy) {
                $terms           = get_the_terms($post['id'], $taxonomy);
                $post[$taxonomy] = (!empty($terms) && !is_wp_error($terms)) ? wp_list_pluck($terms, 'name', 'slug') : '';
            }
        }

        return $post;
    }


    /** 
     * Get Permalink
     */
    private function getPermalink($post)
    {
        $permalink   = get_permalink($post['id']);
        $post['url'] = $permalink;
        $path        = str_replace($this->homeUrl, '', $permalink);
        $pos         = strrpos($path, $post['slug']);
        $collection  = !empty($post['collection_rewrite']) ? $post['collection_rewrite'] : $post['collection'];

        // Home Page
        if ($path === '/') {
            $post['slug'] = 'index';
            return $post;
        }

        // Default Setup
        if (
            strpos($path, $collection . '/' . $post['slug']) !== false ||
            $collection === 'pages' && $pos === 1
        ) {
            return $post;
        }

        // Single Directory Permalinks minus Pages
        if ($post['collection'] !== 'pages' && $pos === 1) {
            $post['permalink'] = '/:slug';
            return $post;
        }

        // Multi-Directory Pages
        if ($pos !== -1) {
            $len          = strlen($collection);
            $slug         = str_replace('/', '-', trim($path, '/'));
            $post['slug'] = substr($slug, 0, $len) === $collection ? substr($slug, $len + 1) : $slug;
        }

        // Everything else
        $post['permalink'] = $path;
        return $post;
    }


    /**
     * Get Featured Image
     *
     */
    private function getFeaturedImage($post) 
    {
        if (empty($post['_thumbnail_id'])) {
            return $post;
        }

        $post['featured_image'] = $this->getImages(wp_get_attachment_url($post['_thumbnail_id']));
        unset($post['_thumbnail_id']);

        return $post;
    }


    /**
     * Tags - Find
     *
     */
    private function getShortcodes($content) 
    {
        $shortcodes = array();

        preg_match_all('/'. $this->shortcodePatterns .'/s', $content, $matches);

        if (!empty($matches[2])) {

            foreach ($matches[2] as $key => $shortcode) {
                $properties                     = array();
                $properties['shortcode']        = $shortcode;
                $properties['markup']           = $matches[0][$key];
                $properties['content']          = $matches[5][$key];
                $properties['child_shortcodes'] = $this->getShortcodes($matches[5][$key]);
                $attributes                     = shortcode_parse_atts($matches[3][$key]);

                // Add to Global Shortcodes
                if (!in_array($shortcode, $this->shortcodes)) {
                    $this->shortcodes[] = $shortcode;
                }

                // Add Attributes
                if (!empty($attributes)) {
                    $properties['attributes'] = $attributes;
                }

                $shortcodes[] = $properties;
            }

        }

        return $shortcodes;
    }



    /**
     * Tags - Replace
     *
     */
    private function replaceShortcodes($content, $shortcodes) 
    {
        if (empty($this->shortcodeConvert) || empty($shortcodes)) {
            return $content;
        }

        foreach ($shortcodes as $shortcode) {
            switch ($this->shortcodeConvert) {
                case 'jekyll':
                    $content = str_replace($shortcode['markup'], $this->convertToJekyll($shortcode), $content);    
                    break;
                case 'gatsby':
                    break;
                case 'hugo':
                    break;
                case 'hexo':
                    break;
                default:
                    break;
            }
            

            if ($shortcode['child_shortcodes']) {
                $content = $this->replaceShortcodes($content, $shortcode['child_shortcodes']);
            }
        }

        return $content;
    }


    /**
     * Shortcodes - Convert to Jekyll
     *
     */
    private function convertToJekyll($shortcode) 
    {
        if (empty($shortcode['shortcode'])) {
            return;
        }

        $attributes = '';

        if (!empty($shortcode['attributes'])) {
            foreach ($shortcode['attributes'] as $attribute => $value) {
                if (
                    empty($value) ||
                    $value == 'false' ||
                    $value === false ||
                    $value == 'none' ||
                    $value == 'default' ||
                    $value === 0 ||
                    $value == '0'
                ) {
                    continue;
                }

                // Add parentheses for certain Jekyll values
                if (strpos($value, ' ') !== false || strpos($value, ',') !== false || strpos($value, ':') !== false) {
                    $value = '"' . $value . '"';
                }

                $attributes .= ' ' . $attribute . ':' . $value; 
            }
        }

        $markup = "{% " . $shortcode['shortcode'] . $attributes . " %}";
        
        // Content - Make Liquid Block
        if (!empty($shortcode['content'])) {
            $markup .= ' ' . $shortcode['content'];
            $markup .= "{% end" . $shortcode['shortcode'] . " %}";
        }
        
        return $markup;
    }


    /**
     * Images - Find & Replace
     *
     */
    private function getImages($content) 
    {
        if (empty($content) || !is_string($content)) {
            return;
        }

        // Base Uploads Directory.
        $baseUploads = $this->buildBase . $this->buildUploads;

        // Match Images Found Locally.
        preg_match_all("/(^|\"|\')(" . implode('|', $this->imagePrefixReplaceReg) . ")(\S*\.png|\S*\.jpg|\S*\.jpeg|\S*\.gif|\S*\.pdf|\S*\.svg)($|\"|\')/i", $content, $images);

        // Loop Matches
        if (!empty($images[0])) {

            foreach ($images[0] as $key => $image) {

                // Variables
                $image      = !empty($images[1][$key]) ? trim($image, $images[1][$key]) : $image;
                $path       = dirname(str_replace($this->imagePrefixReplace, '', $image));
                $path       = $path !== '/' ? $path . '/' : '/';
                $file       = basename($image);
                $source     = $this->wpUploadDir['basedir'] . $path . $file;
                $output     = $baseUploads . ($this->imagePrefix === 'default' ? $path : '/');

                // If file doesn't exists, skip
                if (!file_exists($source)) {
                    continue;
                }

                // Create Dir
                if (!file_exists($output)) {
                    mkdir($output, 0755, true);
                }

                // Existing Images
                if ($this->imagePrefix !== 'default' && file_exists($output . $file)) {
                    $file = ($path !== '/' ? str_replace('/', '', $path) . '-' : '') . $file;
                }

                // Copy Image
                copy($source, $output . $file);

                // Image Replacements Vars.
                $urlReplace = $this->imagePrefix . $file;

                if ($this->imagePrefix === 'default') {
                    $urlReplace = '/' . $this->buildUploads . $path . $file;
                } elseif ($this->imagePrefix === 'flat') {
                    $urlReplace = '/' . $this->buildUploads . '/' . $file;
                }

                // Replace
                $content = str_replace($image, $urlReplace, $content);
            }
        }

        return $content;
    }


    /**
     * URLS - Replacement
     *
     */
    private function replaceUrls($content)
    {
        // Return conditions.
        if (empty($content) || !$this->urlPrefix) {
            return $content;
        }

        // Variable - Replacement URLs
        if (!empty($this->urlPrefixReplace)) {
            $urlPrefixReplace = array_map(function($url) {
                return WPMarkdownHelpers::endsWith($url, '/') ? $url : $url . '/';
            }, 
                $this->urlPrefixReplace
            );
        }
        $urlPrefixReplace[] = $this->homeUrl . '/';

        // Replace
        $content = str_replace($urlPrefixReplace, $this->urlPrefix, $content);

        // Return
        return $content;
    }
    

    /**
     * Build Collections
     *
     */
    private function buildCollections() 
    {
        if (
            empty($this->posts) || 
            empty($this->buildDir) ||
            empty($this->buildCollectionsDir)
        ) {
            return;
        }

        // Variables
        $baseCollections = $this->buildBase . $this->buildCollectionsDir;

        // Build
        foreach ($this->posts as $post) {

            // Directory Setup
            $collectionDir = $baseCollections . '/_' . $post['collection'] . '/';

            if (!file_exists($collectionDir)) {
                mkdir($collectionDir, 0755, true);
            }

            $file   = $collectionDir . sanitize_title($post['slug']) . $this->fileType;
            $handle = fopen($file, 'w') or die('Cannot open file:  ' . $file);

            // Content - Front Matter
            $content = "---" . PHP_EOL;

            foreach ($post as $property => $value) {

                // Skip
                if (
                    ($this->fmClean && empty($value)) || 
                    in_array($property, $this->metaExclude)
                ) {
                    continue;
                }

                // Metadata/Front Matter Re-Mapping
                if (!empty($this->metaMapping) && !empty($this->metaMapping[$property])) {
                    $property = $this->metaMapping[$property];
                }

                // Get Decode/Strip Slashes/Replace Tags/Images/Replace URLs
                if (!empty($value)) {

                    // Setup Arrays
                    if (is_array($value)) {
                        $value = WPMarkdownHelpers::yamlArray($value);
                    }

                    // Setup Object
                    if (is_object($value)) {
                        $value = PHP_EOL . 'OBJECT ~~~ ' . PHP_EOL . print_r($value, true) . PHP_EOL;
                    }

                    $value = $this->replaceShortcodes($value, $this->getShortcodes($value));
                    $value = $this->getImages($value);
                    $value = $this->replaceUrls($value);
                    $value = trim(preg_replace('/\s\s+/', ' ', $value));
                }

                // Content Values
                if ($value != strip_tags($value) && !in_array(substr($value, 0, 1), array('[', '{')))  {
                    $content .= $property . ': >- ' . PHP_EOL;
                    $content .= '   ' . $value . PHP_EOL;
                } else {
                    $content .= $property . ': ' . $value . PHP_EOL;
                }
            }

            $content .= "---";

            // Content - Shortcodes Preview
            if ($this->shortcodePreview && !empty($post['shortcodes'])) {

                $content .= PHP_EOL . PHP_EOL . '-------------- SHORTCODES --------------' . PHP_EOL;

                foreach ($post['shortcodes'] as $shortcode) {
                    $content .= PHP_EOL . '------- ' . strtoupper($shortcode['shortcode']) . (!empty($shortcode['child_shortcodes']) ? ' & CHILDREN' : '') . ' SHORTCODE -------' . PHP_EOL;
                    $content .= $shortcode['markup'] . PHP_EOL . PHP_EOL;
                }

                $content .= '-------------- END OF SHORTCODES --------------';    
            }
            
            // Content - Body
            $content .= PHP_EOL . PHP_EOL . $post['body'];

            fwrite($handle, $content);
        }
    }


    /**
     * Build Shortcodes
     *
     */
    private function buildShortcodes() 
    {
        if (!$this->shortcodeFile || empty($this->shortcodes)) {
            return;
        }

        // Variables
        $filename = 'shortcodes.txt';
        $file     = $this->buildBase . $filename;
        $handle   = fopen($file, 'w') or die('Cannot open file:  ' . $file);

        // Shortcodes - Global
        $content  = '### Shortcodes - Globals' . PHP_EOL . PHP_EOL;

        foreach ($this->shortcodes as $shortcode) {
            $content .= $shortcode . PHP_EOL;    
        }
    
        // Shortcodes - Posts
        $content .= PHP_EOL . PHP_EOL . '#### Shortcodes - Posts' . PHP_EOL . PHP_EOL;

        foreach ($this->postShortcodes as $post) {
            $content .= '/** ' . PHP_EOL;
            $content .= ' * ' . $post['id'] . ': ' . $post['title'] . PHP_EOL;
            $content .= ' * ' . $post['url'] . PHP_EOL;
            $content .= ' * ' . PHP_EOL;
            $content .= ' */ ' . PHP_EOL . PHP_EOL;
            foreach ($post['shortcodes'] as $shortcode) {
                $content .= $shortcode['markup'] . PHP_EOL . PHP_EOL;    
            }
            $content .= PHP_EOL . PHP_EOL . PHP_EOL;  
        }
        
        // Write content to file
        fwrite($handle, $content);
    }


    /**
     * Build Config
     *
     */
    private function buildConfg() 
    {
        // Variables
        $filename = !empty($this->limit) ? '_config-' . $this->offset . '_' . ($this->offset + $this->limit) . '.yml' : '_config.yml';
        $file     = $this->buildBase . $filename;
        $handle   = fopen($file, 'w') or die('Cannot open file:  ' . $file);

        // Content
        $content  = '# Settings' . PHP_EOL;
        $content .= 'title: ' . get_bloginfo('name') . PHP_EOL;
        $content .= 'description: ' . get_bloginfo('description') . PHP_EOL;
        $content .= 'url: ' . get_bloginfo('url') . PHP_EOL;
        $content .= 'email: ' . get_bloginfo('admin_email') . PHP_EOL . PHP_EOL . PHP_EOL;
        $content .= '# Collections' . PHP_EOL;
        $content .= 'collections_dir: ' . $this->buildCollectionsDir . PHP_EOL;
        $content .= 'collections:' . PHP_EOL;

        foreach ($this->collections as $collection => $settings) {
            $content .= '  ' . $collection . ': ' . PHP_EOL;
            $content .= $collection === 'pages' ? '    permalink: :path' . PHP_EOL : '';    
            $content .= (!empty($settings['rewrite']) && $settings['rewrite'] !== $collection) ? '    permalink: ' . $settings['rewrite'] . '/:path' . PHP_EOL : '';    
            $content .= '    output: ' . (!empty($settings['public']) ? 'true' : 'false')  . PHP_EOL;
            if (!empty($settings['has_archive'])) {
                $content .= '    # has_archive: true' . PHP_EOL;        
            }  
        }
        
        // Write content to file
        fwrite($handle, $content);
    }
}
