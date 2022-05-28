<?php

namespace PrivatePluginUpdater\src;

/**
 * Description of PrivateThemeStore
 *
 * @author Mahabub
 */
class PrivateThemeStore {

    //put your code here
    use RemoteResource;

    /**
     * Sub Directory for Theme In hosted Root
     * @var string
     */
    private string $subDir = "themes";

    public function __construct() {
        add_filter('views_theme-install', array($this, 'privateThemeModule'), 10, 1);
    }

    function privateThemeModule($views) {
        //Do your stuff
        var_dump($views);
        $views['private-plugin'] = "<a href='javascript:void(0)' onclick='BrowsePrivatePlugins(this)'>Private Plugins</a>";
        return $views;
    }

}
