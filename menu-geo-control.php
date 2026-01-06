<?php
/**
 * Plugin Name: Menu Geo Control
 * Plugin URI: https://phantosmax.github.io/wordpress-geo-menu-control/
 * Description: Show or hide menu items based on visitor's country
 * Version: 1.0.0
 * Author: Phantosmax
 * Author URI: https://phantosmax.github.io
 * License: GPL v2 or later
 * Text Domain: menu-geo-control
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Menu_Geo_Control {
    
    private static $instance = null;
    private $cache_duration = 86400; // 24 hours
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Menu_Geo_Control();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add custom fields to menu items
        add_action('wp_nav_menu_item_custom_fields', array($this, 'add_menu_item_fields'), 10, 2);
        
        // Save menu item custom fields
        add_action('wp_update_nav_menu_item', array($this, 'save_menu_item_fields'), 10, 2);
        
        // Filter menu items based on geo targeting
        add_filter('wp_nav_menu_objects', array($this, 'filter_menu_items'), 10, 2);
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add custom CSS to admin
        add_action('admin_head-nav-menus.php', array($this, 'admin_menu_css'));
    }
    
    /**
     * Add CSS to admin menu editor
     */
    public function admin_menu_css() {
        ?>
        <style>
        .mgc-field-group {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #0073aa;
        }
        .mgc-field-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #23282d;
        }
        .mgc-field-group input {
            width: 100%;
            padding: 5px;
        }
        .mgc-field-group .description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .mgc-field-group select {
            width: 100%;
            padding: 5px;
        }
        </style>
        <?php
    }
    
    /**
     * Add custom fields to menu items in the admin
     */
    public function add_menu_item_fields($item_id, $item) {
        $show_countries = get_post_meta($item_id, '_menu_item_geo_show_countries', true);
        $hide_countries = get_post_meta($item_id, '_menu_item_geo_hide_countries', true);
        $geo_mode = get_post_meta($item_id, '_menu_item_geo_mode', true);
        
        if (empty($geo_mode)) {
            $geo_mode = 'default';
        }
        ?>
        <div class="mgc-field-group">
            <p class="description" style="margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                <strong>Geo Targeting</strong> - Control menu item visibility by country
            </p>
            
            <p class="field-geo-show-countries description description-wide">
                <label for="edit-menu-item-geo-show-countries-<?php echo esc_attr($item_id); ?>">
                    Show for Countries
                </label>
                <input 
                    type="text" 
                    id="edit-menu-item-geo-show-countries-<?php echo esc_attr($item_id); ?>"
                    class="widefat"
                    name="menu-item-geo-show-countries[<?php echo esc_attr($item_id); ?>]"
                    value="<?php echo esc_attr($show_countries); ?>"
                    placeholder="e.g., AU,NZ,SG"
                />
                <span class="description">
                    Show this menu item ONLY for these countries (comma-separated codes). Leave empty to show for all.
                </span>
            </p>
            
            <p class="field-geo-hide-countries description description-wide">
                <label for="edit-menu-item-geo-hide-countries-<?php echo esc_attr($item_id); ?>">
                    Hide for Countries
                </label>
                <input 
                    type="text" 
                    id="edit-menu-item-geo-hide-countries-<?php echo esc_attr($item_id); ?>"
                    class="widefat"
                    name="menu-item-geo-hide-countries[<?php echo esc_attr($item_id); ?>]"
                    value="<?php echo esc_attr($hide_countries); ?>"
                    placeholder="e.g., US,GB"
                />
                <span class="description">
                    Hide this menu item for these countries (comma-separated codes). Leave empty to not hide.
                </span>
            </p>
            
            <p class="field-geo-mode description description-wide">
                <label for="edit-menu-item-geo-mode-<?php echo esc_attr($item_id); ?>">
                    Geo Targeting Mode
                </label>
                <select 
                    id="edit-menu-item-geo-mode-<?php echo esc_attr($item_id); ?>"
                    name="menu-item-geo-mode[<?php echo esc_attr($item_id); ?>]"
                >
                    <option value="default" <?php selected($geo_mode, 'default'); ?>>Show/Hide (Default)</option>
                    <option value="show_only" <?php selected($geo_mode, 'show_only'); ?>>Show Only</option>
                    <option value="hide_only" <?php selected($geo_mode, 'hide_only'); ?>>Hide Only</option>
                </select>
                <span class="description">
                    Default: Both fields work together. Show Only: Only use "Show" field. Hide Only: Only use "Hide" field.
                </span>
            </p>
        </div>
        <?php
    }
    
    /**
     * Save menu item custom fields
     */
    public function save_menu_item_fields($menu_id, $menu_item_db_id) {
        // Save show countries
        if (isset($_POST['menu-item-geo-show-countries'][$menu_item_db_id])) {
            $show_countries = sanitize_text_field($_POST['menu-item-geo-show-countries'][$menu_item_db_id]);
            update_post_meta($menu_item_db_id, '_menu_item_geo_show_countries', $show_countries);
        } else {
            delete_post_meta($menu_item_db_id, '_menu_item_geo_show_countries');
        }
        
        // Save hide countries
        if (isset($_POST['menu-item-geo-hide-countries'][$menu_item_db_id])) {
            $hide_countries = sanitize_text_field($_POST['menu-item-geo-hide-countries'][$menu_item_db_id]);
            update_post_meta($menu_item_db_id, '_menu_item_geo_hide_countries', $hide_countries);
        } else {
            delete_post_meta($menu_item_db_id, '_menu_item_geo_hide_countries');
        }
        
        // Save geo mode
        if (isset($_POST['menu-item-geo-mode'][$menu_item_db_id])) {
            $geo_mode = sanitize_text_field($_POST['menu-item-geo-mode'][$menu_item_db_id]);
            update_post_meta($menu_item_db_id, '_menu_item_geo_mode', $geo_mode);
        } else {
            delete_post_meta($menu_item_db_id, '_menu_item_geo_mode');
        }
    }
    
    /**
     * Filter menu items based on geo targeting
     */
    public function filter_menu_items($items, $args) {
        $visitor_country = $this->get_visitor_country();
        
        foreach ($items as $key => $item) {
            $show_countries = get_post_meta($item->ID, '_menu_item_geo_show_countries', true);
            $hide_countries = get_post_meta($item->ID, '_menu_item_geo_hide_countries', true);
            $geo_mode = get_post_meta($item->ID, '_menu_item_geo_mode', true);
            
            if (empty($geo_mode)) {
                $geo_mode = 'default';
            }
            
            // If no geo targeting is set, keep the item
            if (empty($show_countries) && empty($hide_countries)) {
                continue;
            }
            
            $should_remove = false;
            
            // Process based on mode
            switch ($geo_mode) {
                case 'show_only':
                    // Only use show countries
                    if (!empty($show_countries)) {
                        $allowed_countries = array_map('trim', array_map('strtoupper', explode(',', $show_countries)));
                        if (!in_array($visitor_country, $allowed_countries)) {
                            $should_remove = true;
                        }
                    }
                    break;
                    
                case 'hide_only':
                    // Only use hide countries
                    if (!empty($hide_countries)) {
                        $blocked_countries = array_map('trim', array_map('strtoupper', explode(',', $hide_countries)));
                        if (in_array($visitor_country, $blocked_countries)) {
                            $should_remove = true;
                        }
                    }
                    break;
                    
                case 'default':
                default:
                    // Check show countries first
                    if (!empty($show_countries)) {
                        $allowed_countries = array_map('trim', array_map('strtoupper', explode(',', $show_countries)));
                        if (!in_array($visitor_country, $allowed_countries)) {
                            $should_remove = true;
                        }
                    }
                    
                    // Then check hide countries
                    if (!$should_remove && !empty($hide_countries)) {
                        $blocked_countries = array_map('trim', array_map('strtoupper', explode(',', $hide_countries)));
                        if (in_array($visitor_country, $blocked_countries)) {
                            $should_remove = true;
                        }
                    }
                    break;
            }
            
            // Remove the item if needed
            if ($should_remove) {
                unset($items[$key]);
            }
        }
        
        return $items;
    }
    
    /**
     * Get visitor's country code
     */
    public function get_visitor_country() {
        // Check if we have a cached country code
        $transient_key = 'mgc_country_' . $this->get_visitor_ip();
        $cached_country = get_transient($transient_key);
        
        if ($cached_country !== false) {
            return $cached_country;
        }
        
        // Get IP address
        $ip = $this->get_visitor_ip();
        
        // Check for local/private IP
        if ($this->is_local_ip($ip)) {
            // Use default country from settings or 'AU'
            $country = get_option('mgc_default_country', 'AU');
            set_transient($transient_key, $country, $this->cache_duration);
            return $country;
        }
        
        // Get geolocation service
        $service = get_option('mgc_geo_service', 'ip-api');
        
        $country = false;
        
        switch ($service) {
            case 'ip-api':
                $country = $this->get_country_from_ip_api($ip);
                break;
            case 'ipapi':
                $country = $this->get_country_from_ipapi($ip);
                break;
            case 'ipinfo':
                $country = $this->get_country_from_ipinfo($ip);
                break;
        }
        
        // Fallback to default if API fails
        if (!$country) {
            $country = get_option('mgc_default_country', 'AU');
        }
        
        // Cache the result
        set_transient($transient_key, $country, $this->cache_duration);
        
        return $country;
    }
    
    /**
     * Get visitor IP address
     */
    private function get_visitor_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    /**
     * Check if IP is local/private
     */
    private function is_local_ip($ip) {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get country from ip-api.com (free, no key required)
     */
    private function get_country_from_ip_api($ip) {
        $response = wp_remote_get("http://ip-api.com/json/{$ip}?fields=countryCode", array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['countryCode']) ? $data['countryCode'] : false;
    }
    
    /**
     * Get country from ipapi.co (free tier available)
     */
    private function get_country_from_ipapi($ip) {
        $response = wp_remote_get("https://ipapi.co/{$ip}/country/", array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $country = wp_remote_retrieve_body($response);
        
        return !empty($country) ? trim($country) : false;
    }
    
    /**
     * Get country from ipinfo.io (free tier available)
     */
    private function get_country_from_ipinfo($ip) {
        $response = wp_remote_get("https://ipinfo.io/{$ip}/country", array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $country = wp_remote_retrieve_body($response);
        
        return !empty($country) ? trim($country) : false;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'Menu Geo Control Settings',
            'Menu Geo Control',
            'manage_options',
            'menu-geo-control',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('mgc_settings', 'mgc_geo_service');
        register_setting('mgc_settings', 'mgc_default_country');
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Menu Geo Control Settings</h1>
            
            <div class="notice notice-info">
                <p><strong>Current Visitor Country:</strong> <?php echo esc_html($this->get_visitor_country()); ?></p>
                <p><strong>Current IP:</strong> <?php echo esc_html($this->get_visitor_ip()); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('mgc_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Geolocation Service</th>
                        <td>
                            <?php $service = get_option('mgc_geo_service', 'ip-api'); ?>
                            <select name="mgc_geo_service">
                                <option value="ip-api" <?php selected($service, 'ip-api'); ?>>ip-api.com (Free, No Key Required)</option>
                                <option value="ipapi" <?php selected($service, 'ipapi'); ?>>ipapi.co (Free Tier)</option>
                                <option value="ipinfo" <?php selected($service, 'ipinfo'); ?>>ipinfo.io (Free Tier)</option>
                            </select>
                            <p class="description">Choose which geolocation service to use.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Country</th>
                        <td>
                            <input type="text" name="mgc_default_country" value="<?php echo esc_attr(get_option('mgc_default_country', 'AU')); ?>" maxlength="2" style="width: 60px; text-transform: uppercase;">
                            <p class="description">Default country code (2 letters, e.g., AU) used for local/development environments.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>How to Use</h2>
            
            <ol>
                <li>Go to <strong>Appearance → Menus</strong></li>
                <li>Expand any menu item to see the geo targeting options</li>
                <li>Configure the country codes for each menu item</li>
                <li>Save your menu</li>
            </ol>
            
            <h3>Menu Item Fields</h3>
            
            <h4>Show for Countries</h4>
            <p>Enter country codes (comma-separated) to show the menu item ONLY for visitors from those countries.</p>
            <p><strong>Example:</strong> <code>AU,NZ,SG</code> - Menu item only shows for Australia, New Zealand, and Singapore visitors.</p>
            
            <h4>Hide for Countries</h4>
            <p>Enter country codes (comma-separated) to hide the menu item for visitors from those countries.</p>
            <p><strong>Example:</strong> <code>US,GB</code> - Menu item is hidden for US and UK visitors.</p>
            
            <h4>Geo Targeting Mode</h4>
            <ul>
                <li><strong>Show/Hide (Default):</strong> Both "Show" and "Hide" fields work together</li>
                <li><strong>Show Only:</strong> Only "Show for Countries" is used (ignore "Hide" field)</li>
                <li><strong>Hide Only:</strong> Only "Hide for Countries" is used (ignore "Show" field)</li>
            </ul>
            
            <h3>Common Country Codes</h3>
            <ul style="columns: 3;">
                <li><strong>AU</strong> - Australia</li>
                <li><strong>NZ</strong> - New Zealand</li>
                <li><strong>SG</strong> - Singapore</li>
                <li><strong>MY</strong> - Malaysia</li>
                <li><strong>ID</strong> - Indonesia</li>
                <li><strong>TH</strong> - Thailand</li>
                <li><strong>US</strong> - United States</li>
                <li><strong>GB</strong> - United Kingdom</li>
                <li><strong>CA</strong> - Canada</li>
                <li><strong>JP</strong> - Japan</li>
                <li><strong>CN</strong> - China</li>
                <li><strong>IN</strong> - India</li>
                <li><strong>DE</strong> - Germany</li>
                <li><strong>FR</strong> - France</li>
                <li><strong>ES</strong> - Spain</li>
            </ul>
            <p><a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">See full list of country codes →</a></p>
            
            <h3>Use Cases</h3>
            
            <h4>Example 1: Regional Product Pages</h4>
            <p>Create different "Products" menu items for different regions:</p>
            <ul>
                <li>Menu Item: "Products (AU/NZ)" → Show for Countries: <code>AU,NZ</code></li>
                <li>Menu Item: "Products (Asia)" → Show for Countries: <code>SG,MY,ID,TH</code></li>
                <li>Menu Item: "Products (US)" → Show for Countries: <code>US,CA</code></li>
            </ul>
            
            <h4>Example 2: Regional Support Links</h4>
            <ul>
                <li>Menu Item: "Sydney Support" → Show for Countries: <code>AU</code></li>
                <li>Menu Item: "Singapore Support" → Show for Countries: <code>SG,MY,ID,TH</code></li>
                <li>Menu Item: "Auckland Support" → Show for Countries: <code>NZ</code></li>
            </ul>
            
            <h4>Example 3: Hide Features for Specific Markets</h4>
            <ul>
                <li>Menu Item: "Enterprise Solutions" → Hide for Countries: <code>CN</code></li>
            </ul>
            
            <h3>Nested Menu Items</h3>
            <p>If you hide a parent menu item, all its child items are also hidden automatically.</p>
            
            <h3>Clear Cache</h3>
            <p>Country detection results are cached for 24 hours. To clear the cache:</p>
            <button type="button" class="button" onclick="if(confirm('Clear all geo-location cache?')) { location.href='<?php echo admin_url('options-general.php?page=menu-geo-control&clear_cache=1'); ?>'; }">Clear Cache</button>
            
            <?php
            if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == '1') {
                global $wpdb;
                $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_mgc_country_%' OR option_name LIKE '_transient_timeout_mgc_country_%'");
                echo '<div class="notice notice-success"><p>Cache cleared successfully!</p></div>';
            }
            ?>
        </div>
        <?php
    }
}

// Initialize the plugin
Menu_Geo_Control::getInstance();
