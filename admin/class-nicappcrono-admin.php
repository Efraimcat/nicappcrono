<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://efraim.cat
 * @since      1.0.0
 *
 * @package    Nicappcrono
 * @subpackage Nicappcrono/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Nicappcrono
 * @subpackage Nicappcrono/admin
 * @author Efraim Bayarri <efraim@efraim.cat>
 */
class Nicappcrono_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @access public
     * @param string $plugin_name
     *            The name of this plugin.
     * @param string $version
     *            The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('init', array(
            $this,
            'register_custom_post_types'
        ));
        add_action('admin_menu', array(
            $this,
            'addPluginAdminMenu'
        ), 9);
        add_action('admin_init', array(
            $this,
            'registerAndBuildFields'
        ));
        add_action('add_meta_boxes_nicappcronocalendars', array(
            $this,
            'setupCustomPostTypeMetaboxes'
        ));
        add_action('save_post_nicappcronocalendars', array(
            $this,
            'saveCustomPostTypeMetaBoxData'
        ));
        add_filter('manage_nicappcronocalendars_posts_columns', array(
            $this,
            'custom_post_type_columns'
        ));
        add_action('manage_nicappcronocalendars_posts_custom_column', array(
            $this,
            'fill_custom_post_type_columns'
        ), 10, 2);
        add_action('admin_init', array(
            $this,
            'CheckAuthPage'
        ));
        add_filter('plugin_action_links_' . $this->plugin_name, array(
            $this,
            'nicappcrono_add_plugin_page_settings_link'
        ), 10, 1);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/nicappcrono-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/nicappcrono-admin.js', array(
            'jquery'
        ), $this->version, false);
    }

    /**
     * Register the Cron Job.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function nicappcronoCron()
    {
        $this->nicappcronoMaintenance();
        $this->UpdateMasterCalendar();
    }

    /**
     * Register custom post type.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function register_custom_post_types()
    {
        $customPostTypeArgs = array(
            'label' => __('Nic-app Crono Calendars', $this->plugin_name),
            'labels' => array(
                'name' => __('Calendars', $this->plugin_name),
                'singular_name' => __('Calendar', $this->plugin_name),
                'add_new' => __('Add Calendar', $this->plugin_name),
                'add_new_item' => __('Add New Calendar', $this->plugin_name),
                'edit_item' => __('Edit Calendar', $this->plugin_name),
                'new_item' => __('New Calendar', $this->plugin_name),
                'view_item' => __('View Calendar', $this->plugin_name),
                'search_items' => __('Search Calendar', $this->plugin_name),
                'not_found' => __('No Calendars Found', $this->plugin_name),
                'not_found_in_trash' => __('No Calendarss Found in Trash', $this->plugin_name),
                'menu_name' => __('Calendars', $this->plugin_name),
                'name_admin_bar' => __('Calendars', $this->plugin_name)
            ),
            'public' => false,
            'description' => __('Nic-app Crono Calendars', $this->plugin_name),
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => $this->plugin_name,
            'supports' => array(
                'title',
                'custom_fields'
            ),
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => false
            ),
            'map_meta_cap' => true,
            'taxonomies' => array()
        );
        register_post_type('nicappcronocalendars', $customPostTypeArgs);
    }

    /**
     * Admin menu.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function addPluginAdminMenu()
    {
        // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page('Nic-app Crono', 'Nic-app Crono', 'administrator', $this->plugin_name, array(
            $this,
            'display_plugin_admin_dashboard'
        ), plugin_dir_url(dirname(__FILE__)) . 'admin/img/nic-app-logo.png', 26);
        // add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page($this->plugin_name, __('Nic-app Crono Settings', $this->plugin_name), __('Settings', $this->plugin_name), 'administrator', $this->plugin_name . '-settings', array(
            $this,
            'displayPluginAdminSettings'
        ));
        add_submenu_page($this->plugin_name, __('Nic-app Crono Scheduling', $this->plugin_name), __('Scheduling', $this->plugin_name), 'administrator', $this->plugin_name . '-scheduling', array(
            $this,
            'displayPluginAdminScheduling'
        ));
        add_submenu_page($this->plugin_name, __('Nic-app Crono Support', $this->plugin_name), __('Support', $this->plugin_name), 'administrator', $this->plugin_name . '-support', array(
            $this,
            'displayPluginAdminSupport'
        ));
    }

    /**
     * Admin menu display.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function display_plugin_admin_dashboard()
    {
        require_once 'partials/nicappcrono-admin-display.php';
    }

    /**
     * Admin Dashboard.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function displayPluginAdminDashboard()
    {
        require_once 'partials' . $this->plugin_name . '-admin-display.php';
    }

    /**
     * Custom Post Type Metabox.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function setupCustomPostTypeMetaboxes()
    {
        add_meta_box('custom_post_type_data_meta_box', __('Calendar information', $this->plugin_name), array(
            $this,
            'custom_post_type_data_meta_box'
        ), 'nicappcronocalendars', 'normal', 'high');
        remove_meta_box('wpseo_meta', 'nicappcronocalendars', 'normal');
    }

    /**
     * Custom Post Type Metabox.
     *
     * @since 1.0.0
     * @access public
     * @param object $post
     *
     */
    public function custom_post_type_data_meta_box($post)
    {
        wp_nonce_field($this->plugin_name . '_affiliate_meta_box', $this->plugin_name . '_affiliates_meta_box_nonce');

        ?><div class="nicappcronocalendars_containers"><?php
        ?><ul class="nicappcrono_calendar_data_metabox"><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_calendarID' ); ?>"> <?php
        _e('Calendar ID', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_calendarID',
            'name' => $this->plugin_name . '_calendarID',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_calendarName' ); ?>"> <?php
        _e('Calendar Name', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_calendarName',
            'name' => $this->plugin_name . '_calendarName',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_AccessToken' ); ?>"> <?php
        _e('Access Token', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_AccessToken',
            'name' => $this->plugin_name . '_AccessToken',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_RefreshToken' ); ?>"> <?php
        _e('Refresh Token', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_RefreshToken',
            'name' => $this->plugin_name . '_RefreshToken',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_ProfileName' ); ?>"> <?php
        _e('Profile Name', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_ProfileName',
            'name' => $this->plugin_name . '_ProfileName',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_ProfileID' ); ?>"> <?php
        _e('Profile ID', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_ProfileID',
            'name' => $this->plugin_name . '_ProfileID',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_ProviderID' ); ?>"> <?php
        _e('Provider ID', $this->plugin_name);
        ?></label><?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_ProviderID',
            'name' => $this->plugin_name . '_ProviderID',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'disabled' => ''
        ));
        ?></li><?php
        //
        ?><li><hr /><?php
        _e('Check if you want product number to be displayed in calendar instead of content', $this->plugin_name);
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_Product_Display' ); ?>"> <?php
        _e('Product Display', $this->plugin_name);
        ?></label> <?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'checkbox',
            'id' => $this->plugin_name . '_Product_Display',
            'name' => $this->plugin_name . '_Product_Display',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID
        ));
        ?></li><?php
        //
        ?><li><label
			for=" <?php esc_html_e($this->plugin_name . '_Product_Id' ); ?>"> <?php
        _e('Product ID', $this->plugin_name);
        ?></label> <?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_Product_Id',
            'name' => $this->plugin_name . '_Product_Id',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID,
            'size' => '6'
        ));
        ?></li>
		<hr /><?php
        //
        ?><li><?php
        _e('Check if you want two way synchronization when new order is added to calendar. (Requires Pluginhive WooCommerce Bookings and Appointments Premium plugin).', $this->plugin_name);
        ?>
		
		<li><label
			for=" <?php esc_html_e($this->plugin_name . '_TwoWay' ); ?>"> <?php
        _e('Two Way Synchronization', $this->plugin_name);
        ?></label> <?php
        $this->nicappcrono_render_settings_field(array(
            'type' => 'input',
            'subtype' => 'checkbox',
            'id' => $this->plugin_name . '_TwoWay',
            'name' => $this->plugin_name . '_TwoWay',
            'required' => '',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'post_meta',
            'post_id' => $post->ID
        ));
        ?></li>
	</ul>
	<hr />
</div><?php
    }

    /**
     * Custom Post Type Metabox Render fields.
     *
     * @since 1.0.0
     * @access public
     * @param array $args
     *
     */
    public function nicappcrono_render_settings_field($args)
    {
        if ($args['wp_data'] == 'option') {
            $wp_data_value = get_option($args['name']);
        } elseif ($args['wp_data'] == 'post_meta') {
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
        }

        switch ($args['type']) {
            case 'input':
                $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
                if ($args['subtype'] != 'checkbox') {
                    $prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>' : '';
                    $prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
                    if ($args['id'] == 'nicappcrono_AuthorizationPageId')
                        $prependEnd = ' ' . get_the_title($value) . '</div>';
                    $step = (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '';
                    $min = (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '';
                    $max = (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '';
                    $size = (isset($args['size'])) ? 'size="' . $args['size'] . '"' : 'size="40"';
                    if (isset($args['disabled'])) {
                        // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" ' . $size . ' disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
                    } else {
                        echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" ' . $size . ' value="' . esc_attr($value) . '" />' . $prependEnd;
                    }
                    /* <input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" /> */
                } else {
                    $checked = ($value) ? 'checked' : '';
                    ?><input type="<?php esc_html_e( $args['subtype'] ); ?>"
	id="<?php esc_html_e( $args['id'] ); ?>"
	<?php esc_html_e( $args['required'] ); ?>
	name="<?php esc_html_e( $args['name'] ); ?>" size="40" value="1"
	<?php esc_html_e( $checked ); ?> /><?php
                }
                break;
            default:
                break;
        }
    }

    /**
     * Custom Post Type Metabox Save fields.
     *
     * @since 1.0.0
     * @access public
     * @param string $post_id
     *
     */
    public function saveCustomPostTypeMetaBoxData($post_id)
    {
        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */
        // Check if our nonce is set.
        if (! isset($_POST[$this->plugin_name . '_affiliates_meta_box_nonce']))
            return;
        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST[$this->plugin_name . '_affiliates_meta_box_nonce'], $this->plugin_name . '_affiliate_meta_box'))
            return;
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        // Check the user's permissions.
        if (! current_user_can('manage_options'))
            return;
        // Make sure that it is set.
        if (! isset($_POST[$this->plugin_name . '_clientId']) && ! isset($_POST[$this->plugin_name . '_clientSecret']) && ! isset($_POST[$this->plugin_name . '_masterCalendar']) && ! isset($_POST[$this->plugin_name . '_masterAccessToken']) && ! isset($_POST[$this->plugin_name . '_masterRefreshToken']) && ! isset($_POST[$this->plugin_name . '_notes'])) {
            return;
        }
        /* OK, it's safe for us to save the data now. */
        // Sanitize user input.
        $calendarID = sanitize_text_field($_POST[$this->plugin_name . "_calendarID"]);
        $calendarName = sanitize_text_field($_POST[$this->plugin_name . "_calendarName"]);
        $AccessToken = sanitize_text_field($_POST[$this->plugin_name . "_AccessToken"]);
        $RefreshToken = sanitize_text_field($_POST[$this->plugin_name . "_RefreshToken"]);
        $ProfileName = sanitize_text_field($_POST[$this->plugin_name . "_ProfileName"]);
        $ProfileID = sanitize_text_field($_POST[$this->plugin_name . "_ProfileID"]);
        $ProviderID = sanitize_text_field($_POST[$this->plugin_name . "_ProviderID"]);
        $Product_Display = sanitize_text_field($_POST[$this->plugin_name . "_Product_Display"]);
        $Product_Id = sanitize_text_field($_POST[$this->plugin_name . "_Product_Id"]);
        $TwoWay = sanitize_text_field($_POST[$this->plugin_name . "_TwoWay"]);

        update_post_meta($post_id, $this->plugin_name . '_calendarID', $calendarID);
        update_post_meta($post_id, $this->plugin_name . '_calendarName', $calendarName);
        update_post_meta($post_id, $this->plugin_name . '_AccessToken', $AccessToken);
        update_post_meta($post_id, $this->plugin_name . '_RefreshToken', $RefreshToken);
        update_post_meta($post_id, $this->plugin_name . '_ProfileName', $ProfileName);
        update_post_meta($post_id, $this->plugin_name . '_ProfileID', $ProfileID);
        update_post_meta($post_id, $this->plugin_name . '_ProviderID', $ProviderID);
        update_post_meta($post_id, $this->plugin_name . '_Product_Display', $Product_Display);
        update_post_meta($post_id, $this->plugin_name . '_Product_Id', $Product_Id);
        update_post_meta($post_id, $this->plugin_name . '_TwoWay', $TwoWay);
    }

    /**
     * Display Admin settings.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function displayPluginAdminSettings()
    {
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array(
                $this,
                'pluginNameSettingsMessages'
            ));
            do_action('admin_notices', sanitize_text_field($_GET['error_message']));
        }
        require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
    }

    /**
     * Display Calendar Scheduling.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function displayPluginAdminScheduling()
    {
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array(
                $this,
                'pluginNameSettingsMessages'
            ));
            do_action('admin_notices', sanitize_text_field($_GET['error_message']));
        }
        require_once 'partials/' . $this->plugin_name . '-admin-scheduling-display.php';
    }

    /**
     * Display Calendar Support.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function displayPluginAdminSupport()
    {
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array(
                $this,
                'pluginNameSettingsMessages'
            ));
            do_action('admin_notices', sanitize_text_field($_GET['error_message']));
        }
        require_once 'partials/' . $this->plugin_name . '-admin-support-display.php';
    }

    /**
     * Display Admin settings error messages.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            $error_message
     *            
     */
    public function pluginNameSettingsMessages($error_message)
    {
        switch ($error_message) {
            case '1':
                $message = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', $this->plugin_name);
                $err_code = esc_attr('nicappcrono_setting');
                $setting_field = 'nicappcrono_setting';
                break;
        }
        $type = 'error';
        add_settings_error($setting_field, $err_code, $message, $type);
    }

    /**
     * Display Admin settings register fields.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function registerAndBuildFields()
    {
        add_settings_section('nicappcrono_general_section', // ID used to identify this section and with which to register options
        '', // Title to be displayed on the administration page
        array(
            $this,
            'nicappcrono_display_general_account'
        ), // Callback used to render the description of the section
        'nicappcrono_general_settings' // Page on which to add this section of options
        );
        // Data Center
        add_settings_field($this->plugin_name . '_DataCenter', __('Use European Data Center', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'checkbox',
            'id' => $this->plugin_name . '_DataCenter',
            'name' => $this->plugin_name . '_DataCenter',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // clientId
        add_settings_field($this->plugin_name . '_clientId', __('Client ID', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_clientId',
            'name' => $this->plugin_name . '_clientId',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // clientSecret
        add_settings_field($this->plugin_name . '_clientSecret', __('Client Secret', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'password',
            'id' => $this->plugin_name . '_clientSecret',
            'name' => $this->plugin_name . '_clientSecret',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // masterCalendar
        add_settings_field($this->plugin_name . '_masterCalendar', __('Master Calendar', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_masterCalendar',
            'name' => $this->plugin_name . '_masterCalendar',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // masterRefreshToken
        add_settings_field($this->plugin_name . '_masterRefreshToken', __('Master Refresh Token', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_masterRefreshToken',
            'name' => $this->plugin_name . '_masterRefreshToken',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // masterAccessToken
        add_settings_field($this->plugin_name . '_masterAccessToken', __('Master Access Token', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_masterAccessToken',
            'name' => $this->plugin_name . '_masterAccessToken',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));
        // Authorization page
        add_settings_field($this->plugin_name . '_AuthorizationPageId', __('Authorization Page ID', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'text',
            'id' => $this->plugin_name . '_AuthorizationPageId',
            'name' => $this->plugin_name . '_AuthorizationPageId',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option',
            'size' => '6'
        ));
        // Create page
        add_settings_field($this->plugin_name . '_CreateAuthPage', __('Create new Authorization Page', $this->plugin_name), array(
            $this,
            'nicappcrono_render_settings_field'
        ), 'nicappcrono_general_settings', 'nicappcrono_general_section', array(
            'type' => 'input',
            'subtype' => 'checkbox',
            'id' => $this->plugin_name . '_CreateAuthPage',
            'name' => $this->plugin_name . '_CreateAuthPage',
            'required' => 'true',
            'get_options_list' => '',
            'value_type' => 'normal',
            'wp_data' => 'option'
        ));

        register_setting('nicappcrono_general_settings', $this->plugin_name . '_DataCenter');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_clientId');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_clientSecret');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_masterCalendar');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_masterAccessToken');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_masterRefreshToken');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_AuthorizationPageId');
        register_setting('nicappcrono_general_settings', $this->plugin_name . '_CreateAuthPage');
    }

    /**
     * Display Admin settings display name.
     *
     * @since 1.0.0
     * @access public
     * @param
     *            void
     *            
     */
    public function nicappcrono_display_general_account()
    {
        ?><p><?php
        _e('These settings refer to your Cronofy account for master calendar an apply to all Nic-app Crono functionality.', $this->plugin_name);
        ?></p>
<hr />
<p><?php
        _e('Cronofy currently provides two data centers one in the USA, the default, and one in Germany. They are run as completely separate instances with no data flow between. This allows you to ensure data is kept within jurisdictional boundaries, eg. the EEA.', $this->plugin_name);
        ?></p>
<p><?php
        _e('Because there is no data flow then separate developer accounts need to be created on the instance that suits your requirements. Functionally the APIs are identical.', $this->plugin_name);
        ?></p>
<hr /><?php
    }

    /**
     * Display Columns in post type page.
     *
     * @since 1.0.0
     * @access public
     * @param array $columns
     *
     */
    public function custom_post_type_columns($columns)
    {
        unset($columns['wpseo-score'], $columns['wpseo-score-readability'], $columns['wpseo-title'], $columns['wpseo-links'], $columns['wpseo-metadesc'], $columns['wpseo-focuskw']);
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', $this->plugin_name),
            'calendarID' => __('Calendar ID', $this->plugin_name),
            'Product_Display' => __('Product display', $this->plugin_name),
            'TwoWay' => __('Two Way', $this->plugin_name),
            'Product_Id' => __('Product ID', $this->plugin_name),
            'date' => __('Date', $this->plugin_name)
        );
    }

    /**
     * Fill Columns in post type page.
     *
     * @since 1.0.0
     * @access public
     * @param string $column
     *
     * @param string $postID
     *
     */
    public function fill_custom_post_type_columns($column, $postID)
    {
        switch ($column) {
            case 'TwoWay':
                (get_post_meta($postID, $this->plugin_name . '_TwoWay', true)) ? _e('Yes', $this->plugin_name) : _e('No', $this->plugin_name);
                break;
            case 'Product_Display':
                (get_post_meta($postID, $this->plugin_name . '_Product_Display', true)) ? _e('Yes', $this->plugin_name) : _e('No', $this->plugin_name);
                break;
            case 'calendarID':
                esc_html_e(get_post_meta($postID, $this->plugin_name . '_calendarID', true));
                break;
            case 'Product_Id':
                esc_html_e((get_post_meta($postID, $this->plugin_name . '_Product_Display', true)) ? get_post_meta($postID, $this->plugin_name . '_Product_Id', true) : '');
                break;
        }
    }

    /**
     * Check defined authorization page.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     *            
     */
    public function CheckAuthPage()
    {
        if (get_option('nicappcrono_CreateAuthPage')) {
            $auth_page = array(
                'post_type' => 'page',
                'post_title' => __('Authorization', $this->plugin_name),
                'post_content' => '[NicappAuth]',
                'post_status' => 'publish',
                'post_author' => wp_get_current_user()
            );
            $auth_page_id = wp_insert_post($auth_page);
            if (! is_wp_error($auth_page_id)) {
                update_option($this->plugin_name . '_AuthorizationPageId', $auth_page_id);
                update_option($this->plugin_name . '_CreateAuthPage', false);
            }
        }
    }

    /**
     * Cron job fill mastercalendar with calendars entries.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     *            
     */
    private function UpdateMasterCalendar()
    {
        if (strlen(get_option('nicappcrono_clientId')) < 25)
            return false;
        $this->custom_logs('UpdateMasterCalendar Start Cron Session');
        $fechaFrom = new DateTime();
        $fechaTo = new DateTime();
        $fechaTo->add(new DateInterval('P180D'));
        $MasterEvents = $this->ReadCalendar(0, $fechaFrom, $fechaTo, true);
        $loop = new WP_Query(array(
            'post_type' => 'nicappcronocalendars',
            'posts_per_page' => 5000,
            'orderby' => 'rand'
        ));
        while ($loop->have_posts()) :
            $loop->the_post();
            $this->custom_logs('Calendar $postID: ' . $loop->post->ID);
            $CalendarEvents = $this->ReadCalendar($loop->post->ID, $fechaFrom, $fechaTo);
            if ($CalendarEvents) {
                $this->CreateMasterEvents($loop->post->ID, $MasterEvents, $CalendarEvents);
                $this->UpdateExistingEvents($loop->post->ID, $MasterEvents, $CalendarEvents);
                $this->DeleteExistingEvents($loop->post->ID, $MasterEvents, $CalendarEvents);
                $this->UpdateExternalEvents($loop->post->ID, $MasterEvents, $CalendarEvents);
            }
        endwhile
        ;
        wp_reset_query();
        $this->custom_logs('UpdateMasterCalendar End Cron Session');
        $this->custom_logs('---');
    }

    /**
     * Read calendar content.
     *
     * @since 1.0.0
     * @access private
     * @param string $postID
     *            Post Type Calendar ID
     * @param DateTime $fechaFrom
     *            Date for the begining of the search.
     * @param DateTime $fecha_to
     *            Date for the end of the search.
     * @param boolean $master
     *            Set to true to read master calendar.
     * @return mixed false|array $eventos
     *         Array of events if calendar exists. Otherwise false.
     */
    private function ReadCalendar($postID, $fechaFrom, $fechaTo, $master = false)
    {
        if (strlen(get_option($this->plugin_name . '_clientId')) < 25)
            return false;
        if (! $master)
            if (strlen(get_post_meta($postID, $this->plugin_name . '_calendarID', true)) < 5)
                return false;
        $params = array(
            "client_id" => get_option($this->plugin_name . '_clientId'),
            "client_secret" => get_option($this->plugin_name . '_clientSecret')
        );
        if ($master) {
            $params["access_token"] = get_option($this->plugin_name . '_masterAccessToken');
            $params["refresh_token"] = get_option($this->plugin_name . '_masterRefreshToken');
        } else {
            $params["access_token"] = get_post_meta($postID, $this->plugin_name . '_AccessToken', true);
            $params["refresh_token"] = get_post_meta($postID, $this->plugin_name . '_RefreshToken', true);
        }
        if (get_option($this->plugin_name . '_DataCenter'))
            $params["data_center"] = 'de';
        $cronofy = new Cronofy\Cronofy($params);
        $cronofy->refreshToken();
        $params = array(
            "from" => $fechaFrom->format('Y-m-d'),
            "to" => $fechaTo->format('Y-m-d'),
            "tzid" => "Etc/UTC",
            "include_managed" => true
        );
        ($master) ? $params["calendar_ids"] = get_option($this->plugin_name . '_masterCalendar') : $params["calendar_ids"] = get_post_meta($postID, $this->plugin_name . '_calendarID', true);
        $events = $cronofy->readEvents($params);
        $eventos = [];
        foreach ($events as $event) {
            $eventos[] = $event;
        }
        return $eventos;
    }

    /**
     * Create Master calendar event if it does not exist.
     *
     * @since 1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function CreateMasterEvents($postID, $MasterEvents, $CalendarEvents)
    {
        foreach ($CalendarEvents as $event) {
            if ($event["transparency"] == "opaque") {
                $eventExists = false;
                foreach ($MasterEvents as $masterevent) {
                    $eventInfo = explode('.', $masterevent['event_id']);
                    if ($masterevent['start'] == $event['start'] && $masterevent['end'] == $event['end'] && $eventInfo['4'] == $event['event_uid'])
                        $eventExists = true;
                }
                if (! $eventExists) {
                    $this->UpdateEvent(array(
                        "start" => $event['start'],
                        "end" => $event['end'],
                        "event_uid" => $event['event_uid'],
                        "postID" => $postID,
                        "summary" => $event['summary'],
                        "description" => $event['description'],
                        "action" => 'create'
                    ));
                }
            }
        }
    }

    /**
     * Update master calendar event content.
     *
     * @since 1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function UpdateExistingEvents($postID, $MasterEvents, $CalendarEvents)
    {
        /*
         * If calendar is defined to be linked to a woocommerce product, dont update master event.
         *
         */
        if (get_post_meta($postID, $this->plugin_name . '_Product_Display', true))
            return;
        foreach ($MasterEvents as $masterevent) {
            $eventInfo = explode('.', $masterevent['event_id']);
            if ((isset($eventInfo[0]) && $eventInfo[0] == $this->plugin_name) && (isset($eventInfo[1]) && $eventInfo[1] == $postID)) {
                foreach ($CalendarEvents as $calendarevent) {
                    if ($calendarevent['start'] == $masterevent['start'] && $calendarevent['end'] == $masterevent['end'] && $calendarevent['event_uid'] == $eventInfo[4]) {
                        $this->UpdateEvent(array(
                            "start" => $calendarevent['start'],
                            "end" => $calendarevent['end'],
                            "event_uid" => $calendarevent['event_uid'],
                            "postID" => $postID,
                            "summary" => $calendarevent['summary'],
                            "description" => $calendarevent['description'],
                            "action" => 'update'
                        ));
                    }
                }
            }
        }
    }

    /**
     * Delete master calendar event if no longer exists.
     *
     * @since 1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function DeleteExistingEvents($postID, $MasterEvents, $CalendarEvents)
    {
        foreach ($MasterEvents as $event) {
            $eventInfo = explode('.', $event['event_id']);
            if ((isset($eventInfo[0]) && $eventInfo[0] == $this->plugin_name) && (isset($eventInfo[1]) && $eventInfo[1] == $postID)) {
                $eventExists = false;
                foreach ($CalendarEvents as $calendarevent) {
                    if (($calendarevent['start'] == $event['start']) && ($calendarevent['end'] == $event['end']) && ($calendarevent['event_uid'] == $eventInfo['4']))
                        $eventExists = true;
                }
                if (! $eventExists) {
                    $this->DeleteEvent($event['event_id']);
                }
            }
        }
    }

    /**
     * Update external calendar entry
     *
     * @since 1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function UpdateExternalEvents($postID, $MasterEvents, $CalendarEvents)
    {
        if (! get_post_meta($postID, $this->plugin_name . '_Product_Display', true))
            return;
        if (! get_post_meta($postID, $this->plugin_name . '_TwoWay', true))
            return;
        if (! CheckPhive())
            return;
        foreach ($MasterEvents as $masterevent) {
            if (strpos($masterevent['summary'], 'Order:') !== false) {
                $FinPedido = strpos($masterevent['summary'], ',');
                $pedido = substr($masterevent['summary'], 8, ($FinPedido - 8));
                $order = wc_get_order($pedido);
                if ($order !== false) {
                    foreach ($order->get_items() as $item) {
                        if (get_post_meta($postID, $this->plugin_name . '_Product_Id', true) == $item->get_product_id()) {}
                    }
                }
            }
        }
    }

    /**
     * Update Master calendar entry
     *
     * @since 1.0.0
     * @access private
     * @param array $args
     * @return bool success
     *        
     */
    private function UpdateEvent($args)
    {
        if (empty($args["start"]))
            return false;
        if (empty($args["end"]))
            return false;
        if (empty($args["event_uid"]))
            return false;
        if (empty($args["postID"]))
            return false;
        $summary = $args["summary"];
        if ($args["action"] == 'create') {
            /*
             * If calendar is defined to be linked to a woocommerce product, change summary to product id on event creation.
             *
             * Compatible with PluginHive "Bookings and Appointments For WooCommerce".
             *
             */
            get_post_meta($args["postID"], $this->plugin_name . '_Product_Display', true) ? $summary = get_post_meta($args["postID"], $this->plugin_name . '_Product_Id', true) : $summary = $args["summary"];
        }
        /*
         * Event identifier.
         */
        $eventID = $this->plugin_name . '.' . $args['postID'] . '.' . $args['start'] . '.' . $args['end'] . '.' . $args['event_uid'];

        $params = array(
            "client_id" => get_option($this->plugin_name . '_clientId'),
            "client_secret" => get_option($this->plugin_name . '_clientSecret'),
            "access_token" => get_option($this->plugin_name . '_masterAccessToken'),
            "refresh_token" => get_option($this->plugin_name . '_masterRefreshToken')
        );
        if (get_option($this->plugin_name . '_DataCenter'))
            $params["data_center"] = 'de';
        $mastercronofy = new Cronofy\Cronofy($params);
        $mastercronofy->refreshToken();
        $mastercronofy->upsertEvent(array(
            "calendar_id" => get_option($this->plugin_name . '_masterCalendar'),
            "event_id" => $eventID,
            "summary" => $summary,
            "description" => $args['description'],
            "start" => $args['start'],
            "end" => $args['end'],
            "tzid" => "Etc/UTC"
        ));
        ($args["action"] == 'create') ? $this->custom_logs('UpdateEvent event created ' . $eventID) : $this->custom_logs('UpdateEvent event updated ' . $eventID);
        return true;
    }

    /**
     * Delete Master calendar entry
     *
     * @since 1.0.0
     * @access private
     * @param string $eventID
     * @return bool success
     *        
     */
    private function DeleteEvent($eventID)
    {
        $params = array(
            "client_id" => get_option($this->plugin_name . '_clientId'),
            "client_secret" => get_option($this->plugin_name . '_clientSecret'),
            "access_token" => get_option($this->plugin_name . '_masterAccessToken'),
            "refresh_token" => get_option($this->plugin_name . '_masterRefreshToken')
        );
        if (get_option($this->plugin_name . '_DataCenter'))
            $params["data_center"] = 'de';
        $mastercronofy = new Cronofy\Cronofy($params);
        $mastercronofy->refreshToken();
        $mastercronofy->deleteEvent(array(
            "calendar_id" => get_option($this->plugin_name . '_masterCalendar'),
            "event_id" => $eventID
        ));
        $this->custom_logs('DeleteEvent event deleted ' . $eventID);
        return true;
    }

    /**
     * Cron job maintenance tasks.
     *
     * @since 1.0.0
     * @access protected
     * @param
     *            void
     *            
     */
    protected function nicappcronoMaintenance()
    {
        $this->custom_logs('nicappcronoMaintenance begins');
        $files = scandir(plugin_dir_path(dirname(__FILE__)) . 'logs/');
        foreach ($files as $file) {
            if (substr($file, - 4) == '.log') {
                $this->custom_logs('Logfile: ' . plugin_dir_path(dirname(__FILE__)) . 'logs/' . $file . ' -> ' . date("d-m-Y H:i:s", filemtime(plugin_dir_path(dirname(__FILE__)) . "logs/" . $file)));
                if (time() > strtotime('+1 week', filemtime(plugin_dir_path(dirname(__FILE__)) . "logs/" . $file))) {
                    $this->custom_logs('Old logfile');
                    unlink(plugin_dir_path(dirname(__FILE__)) . "logs/" . $file);
                }
            }
        }
        $this->custom_logs('nicappcronoMaintenance ends');
        $this->custom_logs('---');
        return;
    }

    /**
     * Utility: scheduled job timestamp.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     *            
     */
    private function scheduledJob()
    {
        if (wp_next_scheduled('nicappcronoCronJob')) {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            esc_html_e(wp_date("{$date_format} {$time_format}", wp_next_scheduled('nicappcronoCronJob'), get_option('timezone_string')));
        } else {
            _e('No scheduled jobs. No calendar entries will be checked.', $this->plugin_name);
        }
    }

    /**
     * Utility: log files.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     *            
     */
    private function logFiles()
    {
        $files = scandir(plugin_dir_path(dirname(__FILE__)) . 'logs/');
        ?>
<form action="" method="post">
	<ul>	
				<?php foreach ( $files as $file ) { ?>
					<?php if( substr( $file , -4) == '.log'){?>
						<li><input type="radio" id="age[]" name="logfile"
			value="<?php esc_html_e( $file ); ?>">
							<?php esc_html_e( $file . ' -> ' . date("d-m-Y H:i:s", filemtime( plugin_dir_path(dirname(__FILE__)) . "logs/" . $file  ) ) ); ?>
						</li>
					<?php }?>
				<?php }?>
			</ul>
	<div class="nicappcrono-send-logfile">
		<input type="submit"
			value="<?php _e( 'View log file', $this->plugin_name ); ?>">
	</div>
</form>
<?php
    }

    /**
     * Utility: show log file.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     *            
     */
    private function ShowLogFile()
    {
        if (isset($_POST['logfile'])) {
            ?>
<hr />
<h3><?php esc_html_e( $_POST['logfile'] ); ?> </h3>
<textarea id="nicappcronologfile" name="nicappcronologfile" rows="30"
	cols="180" readonly>
				<?php esc_html_e( ( file_get_contents( plugin_dir_path(dirname(__FILE__)) . "logs/" . $_POST['logfile'] ) ) ); ?>
			</textarea>
<?php
        }
    }

    /**
     * Plugin Add Settings Link.
     *
     * @since 1.0.0
     * @access private
     * @param array $links
     *
     */
    public function nicappcrono_add_plugin_page_settings_link($links)
    {
        $links[] = '<a href="' . admin_url('admin.php?page=nicappcrono') . '">' . _e('Settings', $this->plugin_name) . '</a>';
        return $links;
    }

    /**
     * Check if pluginhive is active.
     *
     * @since 1.0.0
     * @access private
     * @param
     *            void
     * @return bool success
     *        
     */
    private function CheckPhive()
    {
        if (is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('ph-bookings-appointments-woocommerce-premium/ph-bookings-appointments-woocommerce-premium.php')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Utility: create entry in the log file.
     *
     * @since 1.0.0
     * @access private
     * @param string|array $message
     *
     */
    private function custom_logs($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $time = date("Y-m-d H:i:s");
        $ban = "#$time: $message\r\n";
        $file = plugin_dir_path(dirname(__FILE__)) . 'logs/nicappcrono-log-' . date("Y-m-d") . '.log';
        $open = fopen($file, "a");
        $write = fputs($open, $ban);
        fclose( $open );
    }
}