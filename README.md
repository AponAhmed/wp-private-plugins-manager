# WP Private Plugin Manager

WP Private Plugin Manager is a WordPress plugin that facilitates the management and updating of personally hosted plugins. This plugin is designed for WordPress administrators who maintain their private plugin repository on a dedicated server.

## Features

- **Plugin Management:** Easily manage and update plugins hosted on your private server.
- **User-Friendly Interface:** Intuitive interface for seamless plugin activation, deactivation, and updates.
- **Automatic Updates:** Keep your plugins up-to-date with automatic update functionality.

## ScreenShot
![ScreenShot](https://github.com/AponAhmed/wp-private-plugins-manager/blob/main/ss.png?raw=true)

## Installation

1. Upload the entire `wp-private-plugin-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in the WordPress admin dashboard.

## Configuration

- Requires PHP version 8.0.

## Usage

1. Navigate to the WordPress admin dashboard.
2. Find the 'Private Plugins' section for managing your private plugins.
3. Install, update, and activate plugins directly from your private repository.

# Remote Host

The WP Private Plugin Manager communicates with a remote host server to retrieve information about the hosted plugins. This server-side component is responsible for serving plugin details to the WordPress admin interface.

### Remote Host API

The remote host API file, located in the `WPPluginPublisher` directory, acts as the endpoint for plugin information. It responds to requests from the WP Private Plugin Manager and provides crucial data, such as plugin names, versions, and other relevant details.

#### Authentication

Access to the remote host API is protected by a key to ensure security. Requests must include a valid key for successful communication.

### How to Set Up Your Remote Host

1. **Authentication Key:**
   - Ensure the key used in the remote host API matches the key expected by the WP Private Plugin Manager. Adjust the key in both components if necessary.

2. **Server Configuration:**
   - Host the remote host API on a server with PHP support, ensuring it's accessible to your WordPress instance.

3. **API Endpoint:**
   - Configure the WP Private Plugin Manager to point to the correct API endpoint. Verify that the URL in the plugin code matches the location of your hosted API file.

### Example Code for Remote Host API

An Example file [Remote API.php](https://github.com/AponAhmed/wp-private-plugins-manager/blob/main/Remote%20API.php) for details.

```php
// Example authentication in the remote host API
namespace WPPluginPublisher;

class Publisher {
    private static $key = "your-key";

    public static function auth() {
        // Authentication logic here
        // ...

        // If authentication fails, send a 403 Access Denied response
        header("HTTP/1.1 403 Access Denied");
        echo json_encode([
            'error' => ['message' => "Access Denied, $error"]
        ]);
        exit;
    }

    // ... Rest of the code
}
```

## Author

- Author: APON
- Email : [apon2041@gmail.com](mailto:apon2041@gmail.com)

## Version

- Current Version: 1.6.3

## License

This project is licensed under the [Your License] License - see the [LICENSE.md](LICENSE.md) file for details.

For more information, visit [https://siatexltd.com/wp/plugins/plugin-updater](https://siatexltd.com/wp/plugins/plugin-updater).
