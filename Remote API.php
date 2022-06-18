<?php

namespace WPPluginPublisher;

class Publisher {

    private static $key = "1qazxsw23edcvfr4";
    private static $JSONFile = "info.json";
    private static $dir;
    public $plugins;
    public static $authors = ['siatex', 'apon', 'zakiul islam', 'apon ahmed'];
    public static $PluginHeaders = ['Plugin Name', 'Plugin URI', 'Version', 'Description', 'Author', 'Author URI', 'License', 'Requires PHP', 'Requires'];
    public static $ThemeHeaders = [];

    public function __construct() {
        self::auth();
        //sleep(3);
        self::$dir = dirname(__FILE__);
        $this->FindAllPlugins();
        $this->response();
    }

    public static function auth() {
        $error = "";
        if (isset($_GET['key'])) {
            if (empty($_GET['key'])) {
                $error = "Key Empty";
            } else {
                if ($_GET['key'] == self::$key) {
                    return true;
                } else {
                    $error = "Incorrect KEY";
                }
            }
        } else {
            $error = "Key Not Set";
        }

        header("HTTP/1.1 403 Access Denied");
        echo json_encode([
            'error' => ['message' => "Access Denied, $error"]
        ]);
        exit;
    }

    /**
     * Find All Plugins
     */
    public function FindAllPlugins() {
        $scanned_directory = array_diff(scandir(self::$dir), array('..', '.'));
        foreach ($scanned_directory as $files) {
            //var_dump(self::$dir ."/". $files);
            if (is_dir(self::$dir . "/" . $files)) {
                //var_dump($files);
                $phpFiles = glob(self::$dir . "/" . $files . "/*.php");
                if (count($phpFiles) > 0) {
                    foreach ($phpFiles as $phpfile) {
                        $info = $this->readFileContent($phpfile);
                        if ($info) {
                            $fInfo = pathinfo($phpfile);
                            //Plugin Slug
                            $pluginSlug = $files . "/" . $fInfo['basename'];
                            $info['slug'] = $pluginSlug;
                            //Plugin Sourc File
                            $info['sourceFile'] = $files . ".zip";
                            $this->plugins[$pluginSlug] = $info; //['name' => '', 'varsion' => 1];
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
    private function readFileContent($phpfile) {
        if (file_exists($phpfile)) {
            $fp = @fopen($phpfile, 'r');
            // Pull only the first 8kiB of the file in.
            $file_data = @fread($fp, 1024);
            // PHP will close file handle
            @fclose($fp);
            // Make sure we catch CR-only line endings.
            $file_data = str_replace("\r", "\n", $file_data);
            $info = $this->parsePluginData($file_data);

            if ($info) {
                return $info;
            }
            return false;
            //var_dump($file_data);
        }
    }

    /**
     * Plugin Information Parse
     */
    public function parsePluginData($string) {
        $all_headers = [];
        foreach (self::$PluginHeaders as $field) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($field, '/') . ':(.*)$/mi', $string, $match) && $match[1])
                if ($field == "Version") {
                    $all_headers[$field] = trim($match[1]);
                } else {
                    $all_headers[$field] = trim($match[1]);
                } else
                $all_headers[$field] = '';
        }

        if (isset($all_headers['Plugin Name']) && !empty($all_headers['Plugin Name']) && in_array(strtolower(trim($all_headers['Author'])), self::$authors)) {
            return $all_headers;
        }
        return false;
    }

    /**
     * Render Response
     */
    public function response() {
        echo json_encode($this->plugins);
    }

    /**
     * init Publisher Object
     */
    public static function init() {
        return new Publisher;
    }

}

header('Content-Type: application/json; charset=utf-8');
Publisher::init();
