<?php
namespace PrivatePluginUpdater\src;

class PrivateSetup
{
    // Centralized default options
    private static $default_options = [
        'remote_url' => 'https://siatexltd.com/wp-update-path/',
        'key' => '1qazxsw23edcvfr4',
        'password' => '', // Password remains empty by default
    ];

    public function __construct()
    {
        // Hook to add the admin menu
        add_action('admin_menu', [$this, 'add_menu']);
        // Hook to initialize settings
        add_action('admin_init', [$this, 'settings_init']);
        // Hook to set default options on plugin activation
        register_activation_hook(__FILE__, [$this, 'set_default_options']);
    }

    public function set_default_options()
    {
        $existing_options = get_option('private_setup_options');
        if (!$existing_options) {
            add_option('private_setup_options', self::$default_options);
        }
    }

    public static function get_option($key)
    {
        // Retrieve all options from the database, using defaults as fallback
        $options = get_option('private_setup_options', self::$default_options);
        // Return the specific option or the default value if not set
        return $options[$key] ?? self::$default_options[$key];
    }

    public function add_menu()
    {
        // Add the submenu under the Plugins menu
        add_submenu_page(
            'plugins.php',          // Parent slug
            'Private Setup',        // Page title
            'Private Setup',        // Menu title
            'manage_options',       // Capability required to access the page
            'private-setup',        // Menu slug
            [$this, 'render_page']  // Callback function to display the page
        );
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Display the settings page content
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Private Setup', 'private-setup-plugin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                // Output settings fields and sections
                settings_fields('private_setup_options_group');
                do_settings_sections('private-setup');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function settings_init()
    {
        // Register a new setting
        register_setting('private_setup_options_group', 'private_setup_options');

        // Add a settings section
        add_settings_section(
            'private_setup_section',              // Section ID
            __('', 'private-setup-plugin'), // Title
            [$this, 'section_callback'],          // Callback function
            'private-setup'                       // Page slug
        );

        // Add Remote URL field
        add_settings_field(
            'private_setup_remote_url',           // Field ID
            __('Remote URL', 'private-setup-plugin'), // Title
            [$this, 'remote_url_field_callback'], // Callback function
            'private-setup',                      // Page slug
            'private_setup_section',              // Section ID
            ['label_for' => 'private_setup_remote_url']
        );

        // Add Key field
        add_settings_field(
            'private_setup_key',                  // Field ID
            __('Key', 'private-setup-plugin'),    // Title
            [$this, 'key_field_callback'],        // Callback function
            'private-setup',                      // Page slug
            'private_setup_section',              // Section ID
            ['label_for' => 'private_setup_key']
        );

        // Add Password field
        add_settings_field(
            'private_setup_password',             // Field ID
            __('Password', 'private-setup-plugin'), // Title
            [$this, 'password_field_callback'],   // Callback function
            'private-setup',                      // Page slug
            'private_setup_section',              // Section ID
            ['label_for' => 'private_setup_password']
        );
    }

    public function section_callback()
    {
        echo '<p>' . esc_html__('Configure the Remote URL, Key, and Password for your private setup.', 'private-setup-plugin') . '</p>';
    }

    public function remote_url_field_callback()
    {
        $value = self::get_option('remote_url');
        ?>
        <input type="url" id="private_setup_remote_url" name="private_setup_options[remote_url]"
            value="<?php echo esc_url($value); ?>" class="regular-text" />
        <?php
    }

    public function key_field_callback()
    {
        $value = self::get_option('key');
        ?>
        <input type="text" id="private_setup_key" name="private_setup_options[key]" value="<?php echo esc_attr($value); ?>"
            class="regular-text" />
        <?php
    }

    public function password_field_callback()
    {
        $value = self::get_option('password');
        ?>
        <input type="password" id="private_setup_password" name="private_setup_options[password]"
            value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
}

