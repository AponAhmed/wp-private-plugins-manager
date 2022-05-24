<?php

namespace PrivatePluginUpdater\src;

/**
 *
 * @author apon
 */
trait RemoteResource {

    /**
     * Private Authors
     * @var array
     */
    public static array $authors = ['siatex', 'apon ahmed', 'apon', 'Zakiul Islam'];

    /**
     * Private Plugins
     * @var array
     */
    public array $plugins;
    public $RemotePluginData;
    private static string $key = "1qazxsw23edcvfr4";
    private static string $PluginCache_key = "remote_private_plugins";
    public static string $rootPath = "https://siatexltd.com/wp-update-path/"; //"http://localhost/WPPlugins/"; //;

    public static function get($subDir, $cache = false) {
        if (!$cache) {
            delete_transient(self::$PluginCache_key);
        }
        $remote = get_transient(self::$PluginCache_key);
        //var_dump($remote);
        if (false === $remote || !$cache) {
            //echo "not Cache";
            $remote = wp_remote_get(
                    self::$rootPath . "$subDir" . "/index.php?key=" . self::$key,
                    array(
                        'timeout' => 10,
                        'headers' => array(
                            'Accept' => 'application/json'
                        )
                    )
            );
            if (
                    is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))
            ) {
                return false;
            }
            set_transient(self::$PluginCache_key, $remote, DAY_IN_SECONDS);
        }

        $remoteDataBody = json_decode(wp_remote_retrieve_body($remote));

        return $remoteDataBody;
    }

    /**
     *  Have to make option to clear remote data cache 
     */
    public function cleanCache() {
        delete_transient(self::$PluginCache_key);
        //echo "Cache Cleaned";
    }

    /**
     * To Filter Private Plugins by authors
     */
    public function getPrivatePlugins() {
        $allPlugins = \get_plugins();
        $privatePlugins = array_filter($allPlugins, function ($plugin) {
            if (in_array(strtolower($plugin['Author']), self::$authors)) {
                return $plugin;
            }
        });

        $this->plugins = $privatePlugins;
    }

}
