<?php

/**
 * Plugin Name: Plugin Updater
 * Plugin URI: https://siatexltd.com/wp/plugins/plugin-updater
 * Description: To Update Personal Hosted Plugins 
 * Author: SiATEX
 * Author URI: https://www.siatex.com
 * Version: 1.0
 * Text Domain: update-plugin-stex;
 */
/**
 * Description of Updater
 *
 * @author Apon
 */

namespace PrivatePluginUpdater;

use PrivatePluginUpdater\src\PrivatePluginStore;

define('__UPD_DIR', dirname(__FILE__));
define('__UPD_ASSETS', plugin_dir_url(__FILE__) . "assets/");

//Autoloader 
require 'vendor/autoload.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

class Updater {

    use src\RemoteResource;
   
    /**
     * Remote Host Server URL 
     * @var string
     */
    //public static string $rootPath = "https://siatexltd.com/wp-update-path/"; //"http://localhost/WPPlugins/"; //;
    //public $cache_key = "plugin-update-data";
    //$cache_allowed = false;
    public $PluginStore;

    //put your code here
    public function __construct() {
        if (is_admin()) {
            $this->getPrivatePlugins();
            add_action('admin_enqueue_scripts', [$this, 'adminScript']);

            $this->PluginStore = new PrivatePluginStore();

            if ($this->isPluginManager()) {
                //$this->RemoteData = $this->getRemotePluginData();
            }
        }
        $this->RemotePluginData = self::get('plugins', true);
        add_filter('site_transient_update_plugins', array($this, 'updateTest'));
    }

    /**
     * Get the base URL of the current admin page, with query params.
     *
     * @return string
     */
    function isPluginManager() {
        $pInfo = pathinfo($_SERVER['SCRIPT_NAME']);
        if ($pInfo['basename'] == 'plugins.php') {
            return true;
        }
        return false;
    }

    /**
     * Admin Script Init
     */
    function adminScript($hook) {
        //var_dump(strpos($hook, 'plugin'));
        if (strpos($hook, 'plugin') !== false) {
            wp_enqueue_style('upd-admin-style', __UPD_ASSETS . 'admin-style.css');
            wp_enqueue_script('upd-admin-script', __UPD_ASSETS . 'admin-script.js', array('jquery'), '1.0');
            wp_localize_script('upd-admin-script', 'updobj', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

//    function getRemotePluginData() {
//        $remote = get_transient($this->cache_key);
//        if (false === $remote || !$this->cache_allowed) {
//            $remote = wp_remote_get(
//                    self::$rootPath . "plugins/",
//                    array(
//                        'timeout' => 10,
//                        'headers' => array(
//                            'Accept' => 'application/json'
//                        )
//                    )
//            );
//            if (
//                    is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))
//            ) {
//                return false;
//            }
//            set_transient($this->cache_key, $remote, DAY_IN_SECONDS);
//        }
//        $remote = json_decode(wp_remote_retrieve_body($remote));
//        return $remote;
//    }

    public function updateTest($transient) {
        //echo '<pre>';
        //var_dump($transient);
        //echo "-----------------------------------------------------------------------------------------";
        //exit();        
        if (!$this->RemotePluginData) {
            return $transient;
        }
        if (empty($transient->checked)) {
            // return $transient;
        }
        //var_dump($this->plugins);
        //echo "</pre>";

        foreach ($this->RemotePluginData as $remotePlugin) {
            /* if($remotePlugin->slug=="seoSearch/seoSearch.php"){ 
              $this->currentPlugin = (object) $this->plugins[$remotePlugin->slug];
              var_dump(version_compare($remotePlugin->Requires, get_bloginfo('version'), '<'));
              } */

            if (array_key_exists($remotePlugin->slug, $this->plugins)) {
                $this->currentPlugin = (object) $this->plugins[$remotePlugin->slug];
                if (
                        version_compare($this->currentPlugin->Version, $remotePlugin->Version, '<') &&
                        version_compare($remotePlugin->Requires, get_bloginfo('version'), '<')
                ) {
                    // var_dump($remotePlugin->Version);

                    $res = new \stdClass();
                    $res->slug = $remotePlugin->slug;
                    $res->plugin = $remotePlugin->slug; //plugin_basename(__FILE__); // misha-update-plugin/misha-update-plugin.php
                    $res->new_version = $remotePlugin->Version;
                    $res->tested = true;
                    $res->package = self::$rootPath . "plugins/" . $remotePlugin->sourceFile;
                    $transient->response[$res->plugin] = $res;
                }
            }
        }

        //var_dump($transient);
        return $transient;
    }

    function purgeCache() {
        //delete_transient($this->cache_key);
    }

}

new Updater();
