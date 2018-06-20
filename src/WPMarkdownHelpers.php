<?php


class WPMarkdownHelpers 
{


    /**
     * Starts With Helper
     *
     */
    static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        
        return (substr($haystack, 0, $length) === $needle);
    }


    /**
     * Ends With Helper
     *
     */
    static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }


    /**
     * Yaml Array Helper
     *
     */
    static function yamlArray($arr) 
    {
        if (empty($arr)) {
            return;
        }

        // Content
        $results = '[ "';
        $last    = end($arr);

        foreach ($arr as $value) {

            if (is_array($value)) {
                $results .= self::yamlArray($value) . ($value != $last ? ', ' : '');
                continue;
            }

            $results .= $value . ($value != $last ? '", "' : '');
        }

        $results .= '" ]';

        return stripcslashes($results);
    }
}
