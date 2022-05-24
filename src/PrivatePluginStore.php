<?php

namespace PrivatePluginUpdater\src;

/**
 * Description of PrivatePluginStor
 *
 * @author Mahabub
 */
class PrivatePluginStore {

    use RemoteResource;

    //put your code here
    public function __construct() {
        $this->hookReg();
    }

    function hookReg() {
        add_filter('views_plugin-install', array($this, 'privatePluginModule'), 10, 1);
        add_filter('plugin_install_action_links', array($this, 'action_links_wpse_119218'), 10, 2);
        //Browse Private Plugins Ajax Callback
        add_action("wp_ajax_BrowsePrivatePlugins", [$this, 'BrowsePrivatePlugins']);
        add_action("wp_ajax_clearCache", [$this, 'cleanCache']);
        add_action("wp_ajax_downloadPrivatePlugin", [$this, 'downloadPrivatePlugin']);

        add_filter('views_plugins', array($this, 'pluginsHook'), 10, 1);
    }

    function privatePluginModule($views) {
        //Do your stuff
        $views['private-plugin'] = "<a href='javascript:void(0)' onclick='BrowsePrivatePlugins(this)'>Private Plugins</a>";
        return $views;
    }

    function pluginsHook($views) {
        $views['cleare-cache'] = "<a href='javascript:void(0)' onclick='clearCache(this)' title='Cleare Cache of Plugin Update-Data'>Check Update</a>";
        return $views;
    }

    function BrowsePrivatePlugins() {
        $this->getPrivatePlugins();

        $data = self::get("plugins", true);
        $htm = "<ul class='private-plugin-list'>";
        //echo "<pre>";
        foreach ($data as $plugin) {
            $pluginArr = (array) $plugin;
            $cardClass = "";
            $install = "Install";
            $disabled = "";
            if (array_key_exists($plugin->slug, $this->plugins)) {
                $cardClass .= " plugin-exist";
                $install = "Installed";
                $disabled = "disabled='true'";
            }
            //var_dump($pluginArr);
            $htm .= "<li class='private-plugin-card $cardClass' data-description='" . $pluginArr['Plugin Name'] . " " . $pluginArr['Description'] . "'>";
            $htm .= "   <div class='plugin-card-head'>";
            $htm .= "       <div class='plugin-name'>" . $pluginArr['Plugin Name'] . "</div>";
            $htm .= "       <div class='plugin-control'><button class='plugin-download-btn $install' onclick='downloadPrivatePlugin(\"$plugin->slug\",this)' $disabled>$install</button></div>";
            $htm .= "   </div>";
            $htm .= "   <div class='plugin-description'>" . $pluginArr['Description'] . "</div>";
            $htm .= "</li>";
        }
        $htm .= "</ul>";
        echo $htm;
        wp_die();
    }

    function downloadPrivatePlugin() {
        //PLUGINDIR;
        $tempDir = get_temp_dir();
        //var_dump(PLUGINDIR);
        //return;
        $res = ['error' => false, 'message' => ''];

        if (isset($_POST['slug']) && !empty($_POST['slug'])) {
            $this->RemotePluginData = self::get('plugins', true);
            $reqSlug = $_POST['slug'];
            //echo "<pre>";
            $fileName = $this->RemotePluginData->$reqSlug->sourceFile;
            $remoteFile = self::$rootPath . "plugins/" . $fileName;
            //var_dump($remoteFile);
            //echo "</pre>";
            $tempFile = $tempDir . $fileName;
            $down = file_put_contents($tempFile, @fopen($remoteFile, 'r'));
            if (!$down) {
                $res['error'] = "Download Error";
                $res['message'] = "Plugin Unable to download, ";
            } else {
                $zip = New \ZipArchive();
                if ($zip->open($tempFile)) {
                    $extr = $zip->extractTo(trailingslashit(WP_PLUGIN_DIR));
                    //var_dump(trailingslashit(WP_PLUGIN_DIR));
                    $zip->close();
                    unlink($tempFile);
                    $res['error'] = false;
                    $res['message'] = "Plugin Installed";
                } else {
                    $res['error'] = "Zip Archive Open Error";
                    $res['message'] = "Plugin file contains error with Zip Archive";
                }
            }
            echo json_encode($res);
        }
        wp_die();
    }

    /**
     * Individual Plugin Action Link
     */
    function action_links_wpse_119218($links, $plugin) {
        //var_dump($links);
        if (isset($_GET['tab'])) {
            switch ($_GET['tab']) {
                case 'featured':
                    $links['my-action'] = "Tested up to <a href='#'>{$plugin['tested']}</a>";
                    break;
                case 'popular':
                    $links['my-action'] = "Requires <a href='#'>{$plugin['requires']}</a>";
                    break;
                case 'new':
                    $links['my-action'] = "Slug <a href='#'>{$plugin['slug']}</a>";
                    break;
            }
        }
        return $links;
    }

}
