<?php

/**
 * Plugin Name: Plugin Updater
 * Plugin URI: https://siatexltd.com/wp/plugins/plugin-updater
 * Description: To Update Personal Hosted Plugins 
 * Author: SiATEX
 * Author URI: https://www.siatex.com
 * Version: 1.6.3
 * PHP Version : 8.0
 * Text Domain: update-plugin-stex;
 */
/**
 * Description of Updater
 *
 * @author Apon
 */

namespace PrivatePluginUpdater;

use PrivatePluginUpdater\src\PrivatePluginStore;
use PrivatePluginUpdater\src\PrivateThemeStore;

define('__UPD_DIR', dirname(__FILE__));
define('__UPD_ASSETS', plugin_dir_url(__FILE__) . "assets/");

//Autoloader 
require 'vendor/autoload.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

class Updater {

    use src\RemoteResource;

    /**
     * Plugin Store Object 
     * @var Object
     */
    public $PluginStore;

    /**
     * Theme Store
     * @var Object
     */
    public $ThemeStore;

    //put your code here
    public function __construct() {
        $this->getPrivatePlugins();
        $this->getPrivateThemes();
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'adminScript']);

            $this->PluginStore = new PrivatePluginStore();
            $this->ThemeStore = new PrivateThemeStore();
        }
        $this->RemotePluginData = self::get('plugins', true);
        $this->RemoteThemeData = self::get('themes', true);
        //Plugin transient 
        add_filter('site_transient_update_plugins', array($this, 'set_update_for_plugin'));
        //Themes transient
        add_filter('site_transient_update_themes', array($this, 'theme_check_for_update'));
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
        //var_dump(strpos($hook, 'themes'));
        if (strpos($hook, 'plugin') !== false || strpos($hook, 'theme') !== false) {
            wp_enqueue_style('upd-admin-style', __UPD_ASSETS . 'admin-style.css');
            wp_enqueue_script('upd-admin-script', __UPD_ASSETS . 'admin-script.js', array('jquery'), '1.0');
            wp_localize_script('upd-admin-script', 'updobj', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

    public function set_update_for_plugin($transient) {
        if (!$transient) {
            $transient = new \stdClass();
            $transient->response = [];
        }
        if (!$this->RemotePluginData) {
            return $transient;
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

                    if (property_exists($transient, 'response')) {
                        if (!is_array($transient->response)) {
                            $transient->response = [];
                        }
                    }
                    $transient->response[$res->plugin] = $res;
                }
            }
        }

        //var_dump($transient);
        return $transient;
    }

    /**
     * 
     * @param object $transient
     */
    function theme_check_for_update($transient) {
        //echo "<pre>";
        if (!$transient) {
            $transient = new \stdClass();
            $transient->response = [];
        }
        if (!$this->RemoteThemeData) {
            return $transient;
        }
        //echo "<pre>";

        $remotArr = (array) $this->RemoteThemeData;
        //var_dump($remotArr);
        foreach ($this->themes as $slug => $theme) {
            //$remoteTheme
            if (array_key_exists($slug, $remotArr)) {
                $remoteThme = (array) $remotArr[$slug];
                $localThemeV = $theme['Version'];
                if (version_compare($localThemeV, $remoteThme['Version'], '<')) {
                    //var_dump($remoteThme);
                    $res = [
                        "theme" => $slug,
                        "new_version" => $remoteThme['Version'],
                        "url" => $remoteThme['Theme URI'],
                        "package" => self::$rootPath . "themes/" . $remoteThme['sourceFile'],
                        "requires_php" => $remoteThme['Requires PHP'],
                        "requires" => $remoteThme['Requires'],
                    ];
                    //var_dump($slug);
                    if (property_exists($transient, 'response')) {
                        if (!is_array($transient->response)) {
                            $transient->response = [];
                        }
                    }
                    $transient->response[$slug] = $res;
                }
            }
        }
        //var_dump($transient);
        //exit;
        return $transient;
    }

}

new Updater();
