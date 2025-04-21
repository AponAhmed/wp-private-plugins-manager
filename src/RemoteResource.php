<?php

namespace PrivatePluginUpdater\src;

use PrivatePluginUpdater\src\LocalThemes;

/**
 *
 * @author apon
 */
trait RemoteResource
{

    /**
     * HOST API Root
     * @var string
     */
    public static string $rootPath = "https://siatexltd.com/wp-update-path/";

    /**
     * Key For Auth
     * @var string
     */
    private static string $key = "1qazxsw23edcvfr4";

    /**
     * Password For Auth
     * @var string
     */
    private static string $password = "";

    /**
     * Private Authors
     * @var array
     */
    public static array $authors = ['siatex', 'apon ahmed', 'apon', 'zakiul islam', 'SiATEX'];

    /**
     * Private Plugins
     * @var array
     */
    public array $plugins;

    /**
     * Local Themes by Private Authors
     * @var array
     */
    public array $themes;

    /**
     * Remote Private Plugins Data
     * @var type Object
     */
    public $RemotePluginData;

    /**
     * Remote Private Theme Data
     * @var type Object
     */
    public $RemoteThemeData;

    /**
     * remote Information cache Key
     * @var string
     */
    private static string $PluginCache_key = "remote_private_plugins";
    private static string $ThemeCache_key = "remote_private_theme";

    public static function get($subDir, $cache = false)
    {
        $cacheKey = "";
        if ($subDir == "plugins") {
            $cacheKey = self::$PluginCache_key;
        } elseif ($subDir == "themes") {
            $cacheKey = self::$ThemeCache_key;
        }

        if (!$cache) {
            delete_transient($cacheKey);
        }
        $remote = get_transient($cacheKey);
        self::$rootPath = PrivateSetup::get_option('remote_url');
        self::$key = PrivateSetup::get_option('key');
        self::$password = PrivateSetup::get_option('password');
        //var_dump($remote);
        if (false === $remote || !$cache) {
            $remote = wp_remote_get(
                self::$rootPath . "$subDir" . "/index.php?key=" . self::$key . "&password=" . self::$password,
                array(
                    'timeout' => 10,
                    'headers' => array(
                        'Accept' => 'application/json'
                    )
                )
            );
            //var_dump($remote);

            if (
                is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))
            ) {
                return false;
            }
            set_transient($cacheKey, $remote, DAY_IN_SECONDS);
        }

        $remoteDataBody = json_decode(wp_remote_retrieve_body($remote));

        return $remoteDataBody;
    }

    /**
     * 
     *  Clear remote data cache  For Plugin's and Themes Remote Information
     */
    public function cleanCache()
    {
        if (isset($_POST['module'])) {
            if ($_POST['module'] == "plugins") {
                delete_transient(self::$PluginCache_key);
            }
            if ($_POST['module'] == "themes") {
                delete_transient(self::$ThemeCache_key);
            }
        }
    }

    /**
     * To Filter Private Plugins by authors
     */
    public function getPrivatePlugins()
    {
        $allPlugins = \get_plugins();
        $privatePlugins = array_filter($allPlugins, function ($plugin) {
            if (in_array(strtolower($plugin['Author']), self::$authors)) {
                return $plugin;
            }
        });

        $this->plugins = $privatePlugins;
    }

    /**
     * To Filter Private Plugins by authors
     */
    public function getPrivateThemes()
    {
        $this->themes = LocalThemes::themes(self::$authors);

        // var_dump(self::get('themes', true));
    }

}
