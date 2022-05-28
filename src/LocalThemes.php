<?php

namespace PrivatePluginUpdater\src;

/**
 * Description of LocalThemes
 *
 * @author Mahabub
 */
class LocalThemes {

    private static $authors;
    private static $ThemeHeaders = ['Theme Name', 'Theme URI', 'Version', 'Description', 'Author', 'Author URI', 'License', 'Requires PHP', 'Requires'];
    private static $themes;
    private static $dir = WP_CONTENT_DIR . "/themes/";

    //put your code here
    public static function themes($authors) {
        self::$themes = [];
        self::$authors = $authors;
        self::FindAllThemes();
        //var_dump(self::$themes);
        return self::$themes;
    }

    /**
     * Find All Themes
     */
    private static function FindAllThemes() {
        $scanned_directory = array_diff(scandir(self::$dir), array('..', '.'));
        foreach ($scanned_directory as $files) {
            if (is_dir(self::$dir . "/" . $files)) {
                //var_dump($files);
                $cssFiles = glob(self::$dir . "/" . $files . "/*.css");
                if (count($cssFiles) > 0) {
                    foreach ($cssFiles as $cssFile) {
                        //var_dump($files,$cssFile);
                        $info = self::readFileContent($cssFile);
                        //var_dump($info);
                        if ($info) {
                            //Plugin Slug
                            $themeSlug = $files;
                            $info['slug'] = $themeSlug;
                            //Plugin Sourc File
                            self::$themes[$themeSlug] = $info; //['name' => '', 'varsion' => 1];
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Read File Content
     */
    private static function readFileContent($cssFile) {
        if (file_exists($cssFile)) {
            $fp = @fopen($cssFile, 'r');
            // Pull only the first 8kiB of the file in.
            $file_data = @fread($fp, 1024);
            // PHP will close file handle
            @fclose($fp);
            // Make sure we catch CR-only line endings.
            $file_data = str_replace("\r", "\n", $file_data);

            $info = self::parseThemeData($file_data);
            if ($info) {
                return $info;
            }
            return false;
            //var_dump($file_data);
        }
    }

    /**
     * Theme Information Parse
     */
    public static function parseThemeData($string) {
        $all_headers = [];
        foreach (self::$ThemeHeaders as $field) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($field, '/') . ':(.*)$/mi', $string, $match) && $match[1]) {
                if ($field == "Version") {
                    $all_headers[$field] = trim($match[1]);
                } else {
                    $all_headers[$field] = trim($match[1]);
                }
            } else {
                $all_headers[$field] = '';
            }
        }
        if (isset($all_headers['Theme Name']) && !empty($all_headers['Theme Name']) && in_array(strtolower($all_headers['Author']), self::$authors)) {
            return $all_headers;
        }
        return false;
    }

}
