<?php

namespace PrivatePluginUpdater\src;

/**
 * Description of PrivatePluginStor
 *
 * @author Mahabub
 */
class PrivatePluginStore
{

    use RemoteResource;

    //put your code here
    public function __construct()
    {
        $this->hookReg();
    }

    function hookReg()
    {
        add_filter('views_plugin-install', array($this, 'privatePluginModule'), 10, 1);
        add_filter('views_plugin-install-network', array($this, 'privatePluginModule'), 10, 1);

        add_filter('plugin_install_action_links', array($this, 'action_links_wpse_119218'), 10, 2);
        //Browse Private Plugins Ajax Callback
        add_action("wp_ajax_BrowsePrivatePlugins", [$this, 'BrowsePrivatePlugins']);
        add_action("wp_ajax_clearCache", [$this, 'cleanCache']);
        add_action("wp_ajax_downloadPrivatePlugin", [$this, 'downloadPrivatePlugin']);
        add_action("wp_ajax_ActivePrivatePlugin", [$this, 'ActivePrivatePlugin']);

        add_filter('views_plugins', array($this, 'pluginsHook'), 10, 1);
        add_filter('views_plugins-network', array($this, 'pluginsHook'), 10, 1);
    }

    function privatePluginModule($views)
    {
        //Do your stuff
        $views['private-plugin'] = "<a href='javascript:void(0)' onclick='BrowsePrivatePlugins(this)'>Private Plugins</a>";
        return $views;
    }

    function pluginsHook($views)
    {
        $views['cleare-cache'] = "<a href='javascript:void(0)' onclick='clearCache(this,\"plugins\")' title='Cleare Cache of Plugin Update-Data'>Check Update</a>";
        return $views;
    }

    function BrowsePrivatePlugins()
    {
        $this->getPrivatePlugins();

        $data = self::get("plugins", true);
        $htm = "<ul class='private-plugin-list'>";
        //Active Plugins
        $activePlugins = get_option('active_plugins');
        //var_dump($activePlugins);
        //echo "<pre>";
        foreach ($data as $plugin) {
            $pluginArr = (array) $plugin;
            $cardClass = "";
            $install = "Install";
            $disabled = "";
            $activeButton = '<button class="plugin-download-btn active-plugin-btn" onclick="activePlugin(\'' . $plugin->slug . '\',this)">Active</button>';
            if (array_key_exists($plugin->slug, $this->plugins)) {
                $cardClass .= " plugin-exist";
                $install = "Installed";
                $disabled = "disabled='true'";
                if (in_array($plugin->slug, $activePlugins)) {
                    $activeButton = "";
                }
            } else {
                $activeButton = "";
            }
            //var_dump($pluginArr);
            $htm .= "<li class='private-plugin-card $cardClass' data-description='" . $pluginArr['Plugin Name'] . " " . $pluginArr['Description'] . "'>";
            $htm .= "   <div class='plugin-card-head'>";
            $htm .= "       <div class='plugin-name'>" . $pluginArr['Plugin Name'] . "</div>";
            $htm .= "       <div class='plugin-control'><button class='plugin-download-btn $install' onclick='downloadPrivatePlugin(\"$plugin->slug\",this)' $disabled>$install</button> $activeButton</div>";
            $htm .= "   </div>";
            $htm .= "   <div class='plugin-description'>" . $pluginArr['Description'] . "</div>";
            $htm .= "</li>";
        }
        $htm .= "</ul>";
        echo $htm;
        wp_die();
    }

    function ActivePrivatePlugin()
    {
        $resp = [];
        if (isset($_POST['slug']) && !empty($_POST['slug'])) {
            $activePlugins = get_option('active_plugins');
            $rqSlug = $_POST['slug'];
            $activePlugins[] = $rqSlug;
            if (update_option('active_plugins', $activePlugins)) {
                $resp = ['error' => false, 'message' => 'Plugin Activated'];
            }
        } else {
            $resp = ['error' => true, 'message' => 'Invalid Request, Try again'];
        }

        echo json_encode($resp);
        wp_die();
    }

    function downloadPrivatePlugin()
    {
        global $wp_filesystem;
        if (!$wp_filesystem) {
            WP_Filesystem();
        }
        //PLUGINDIR;
        require_once(ABSPATH . '/wp-admin/includes/file.php');

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
                $destFile = WP_PLUGIN_DIR;
                $uz = unzip_file($tempFile, $destFile);
                if ($uz === true) {
                    //$this->rcopy($destFile, WP_PLUGIN_DIR);
                    $res['error'] = false;
                    $res['message'] = "Plugin Installed";
                    unlink($tempFile);
                } else {
                    $res['error'] = "Zip Archive Open Error";
                    $res['message'] = "Plugin file contains error with Zip Archive";
                }
                //                $zip = New \ZipArchive();
//                if ($zip->open($tempFile)) {
//                    $extr = $zip->extractTo(trailingslashit(WP_PLUGIN_DIR));
//                    //var_dump(trailingslashit(WP_PLUGIN_DIR));
//                    $zip->close();
//                    unlink($tempFile);
//                    $res['error'] = false;
//                    $res['message'] = "Plugin Installed";
//                } else {
//                    $res['error'] = "Zip Archive Open Error";
//                    $res['message'] = "Plugin file contains error with Zip Archive";
//                }
            }
            echo json_encode($res);
        }
        wp_die();
    }

    // Function to remove folders and files 
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
                if ($file != "." && $file != "..")
                    $this->rrmdir("$dir/$file");
            rmdir($dir);
        } else if (file_exists($dir))
            unlink($dir);
    }

    // Function to Copy folders and files       
    function rcopy($src, $dst)
    {
        if (file_exists($dst))
            $this->rrmdir($dst);
        if (is_dir($src)) {
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file)
                if ($file != "." && $file != "..")
                    $this->rcopy("$src/$file", "$dst/$file");
        } else if (file_exists($src))
            \copy($src, $dst);
    }

    /**
     * Individual Plugin Action Link
     */
    function action_links_wpse_119218($links, $plugin)
    {
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
