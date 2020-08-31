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
    public function __construct($plugin_name, $version){
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('init', array( $this, 'register_custom_post_types' ));
        add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);
        add_action('admin_init', array( $this, 'registerAndBuildFields' ));
        add_action('add_meta_boxes_nicappcronocalendars', array( $this, 'setupCustomPostTypeMetaboxes' ));
        add_action('save_post_nicappcronocalendars', array( $this, 'saveCustomPostTypeMetaBoxData') );
        add_filter('manage_nicappcronocalendars_posts_columns' , array($this,'custom_post_type_columns'));
        add_action('manage_nicappcronocalendars_posts_custom_column' , array($this,'fill_custom_post_type_columns'), 10, 2 );
        add_action('admin_init', array( $this, 'CheckAuthPage'));
        add_filter('plugin_action_links_' . $this->plugin_name, array($this, 'nicappcrono_add_plugin_page_settings_link'), 10 , 1);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     * @access public  
     * @param void
     *
     */
    public function enqueue_styles(){
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/nicappcrono-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     * @access public  
     * @param void
     *
     */
    public function enqueue_scripts(){
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/nicappcrono-admin.js', array(
            'jquery'
        ), $this->version, false);
    }
    
    /**
     * Register the Cron Job.
     *
     * @since 1.0.0
     * @access public 
     * @param void
     *
     */
    public function nicappcronoCron(){
        $this->nicappcronoMaintenance();
        $this->UpdateMasterCalendar();
    }
 
    /**
     * Register custom post type.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    
    public function register_custom_post_types(){
        $customPostTypeArgs = array(
            'label'=>__('Nic-app Crono Calendars','nicappcrono'),
            'labels'=>
            array(
                'name'=>__('Calendars','nicappcrono'),
                'singular_name'=>__('Calendar','nicappcrono'),
                'add_new'=>__('Add Calendar','nicappcrono'),
                'add_new_item'=>__('Add New Calendar','nicappcrono'),
                'edit_item'=>__('Edit Calendar','nicappcrono'),
                'new_item'=>__('New Calendar','nicappcrono'),
                'view_item'=>__('View Calendar','nicappcrono'),
                'search_items'=>__('Search Calendar','nicappcrono'),
                'not_found'=>__('No Calendars Found','nicappcrono'),
                'not_found_in_trash'=>__('No Calendarss Found in Trash','nicappcrono'),
                'menu_name'=>__('Calendars','nicappcrono'),
                'name_admin_bar'=>__('Calendars','nicappcrono'),
            ),
            'public'=>true,
            'description'=>__('Nic-app Crono Calendars','nicappcrono'),
            'exclude_from_search'=>true,
            'show_ui'=>true,
            'show_in_menu'=>$this->plugin_name,
            'supports'=>array('title','custom_fields'),
            'capability_type'=>'post',
            'taxonomies'=>array());

            register_post_type( 'nicappcronocalendars', $customPostTypeArgs );
    }
    
    /**
     * Admin menu.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function addPluginAdminMenu() {
//		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page( 'Nic-app Crono', 'Nic-app Crono', 'administrator', $this->plugin_name, array( $this, 'display_plugin_admin_dashboard' ), plugin_dir_url( dirname(__FILE__) ) . 'admin/img/nic-app-logo.png', 26 );
//      add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page( $this->plugin_name, __('Nic-app Crono Settings','nicappcrono'), __('Settings','nicappcrono'), 'administrator', $this->plugin_name.'-settings', array( $this, 'displayPluginAdminSettings' ));
        add_submenu_page( $this->plugin_name, __('Nic-app Crono Scheduling','nicappcrono'), __('Scheduling','nicappcrono'), 'administrator', $this->plugin_name.'-scheduling', array( $this, 'displayPluginAdminScheduling' ));
        add_submenu_page( $this->plugin_name, __('Nic-app Crono Support','nicappcrono'), __('Support','nicappcrono'), 'administrator', $this->plugin_name.'-support', array( $this, 'displayPluginAdminSupport' ));
    }
	
	/**
     * Admin menu display.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function display_plugin_admin_dashboard() {
        require_once 'partials/nicappcrono-admin-display.php';
    }
    
    /**
     * Admin Dashboard.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function displayPluginAdminDashboard() {
        require_once 'partials'.$this->plugin_name.'-admin-display.php';
    }
    
    /**
     * Custom Post Type Metabox.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function setupCustomPostTypeMetaboxes(){
        add_meta_box('custom_post_type_data_meta_box', __('Calendar information','nicappcrono'), array($this,'custom_post_type_data_meta_box'), 'nicappcronocalendars', 'normal','high' );
    }
    
    /**
     * Custom Post Type Metabox.
     *
     * @since    1.0.0
     * @access public 
     * @param object $post
     * 
     */
    public function custom_post_type_data_meta_box( $post ){
        wp_nonce_field( $this->plugin_name.'_affiliate_meta_box', $this->plugin_name.'_affiliates_meta_box_nonce' );
        
        echo '<div class="nicappcronocalendars_containers">';
        echo '<ul class="nicappcrono_calendar_data_metabox">';
//
        echo '<li><label for="'.$this->plugin_name.'_calendarID">';
        _e( 'Calendar ID', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_calendarID','name'=>$this->plugin_name.'_calendarID','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_calendarName">';
        _e( 'Calendar Name', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_calendarName','name'=>$this->plugin_name.'_calendarName','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_AccessToken">';
        _e( 'Access Token', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_AccessToken','name'=>$this->plugin_name.'_AccessToken','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_RefreshToken">';
        _e( 'Refresh Token', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_RefreshToken','name'=>$this->plugin_name.'_RefreshToken','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_ProfileName">';
        _e( 'Profile Name', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_ProfileName','name'=>$this->plugin_name.'_ProfileName','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_ProfileID">';
        _e( 'Profile ID', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_ProfileID','name'=>$this->plugin_name.'_ProfileID','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_ProviderID">';
        _e( 'Provider ID', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_ProviderID','name'=>$this->plugin_name.'_ProviderID','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID, 'disabled'=>''));
        echo '</li>';
//
        echo '<li><hr/>';
        _e('Check if you want product number to be displayed in calendar instead of content','nicappcrono');
        echo '</li>';
//
        echo '<li><label for="'.$this->plugin_name.'_Product_Display">';
        _e( 'Product Display', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'checkbox','id'=>$this->plugin_name.'_Product_Display','name'=>$this->plugin_name.'_Product_Display','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID));
        echo '</li>';
//
        echo '</li><li><label for="'.$this->plugin_name.'_Product_Id">';
        _e( 'Product ID', 'nicappcrono' );
        echo '</label> ';
        $this->nicappcrono_render_settings_field(array ('type'=>'input','subtype'=>'text','id'=>$this->plugin_name.'_Product_Id','name'=>$this->plugin_name.'_Product_Id','required'=>'','get_options_list'=>'','value_type'=>'normal','wp_data'=>'post_meta','post_id'=>$post->ID,'size'=>'6'));
        echo '</li><hr/>';
// provide textarea name for $_POST variable
        $notes = get_post_meta( $post->ID, $this->plugin_name.'_notes', true );
        echo '<li><label for="'.$this->plugin_name.'_notes"><strong>';
        _e( 'Notes', 'nicappcrono' );
        echo '</strong></label> ';
        wp_editor( $notes, $this->plugin_name.'_notes_editor',array('textarea_name' => $this->plugin_name.'_notes',));
        echo '</li></ul></div>';
    }
    
    /**
     * Custom Post Type Metabox Render fields.
     *
     * @since    1.0.0
     * @access public 
     * @param array $args
     * 
     */
    public function nicappcrono_render_settings_field( $args ) {
        if($args['wp_data'] == 'option'){
            $wp_data_value = get_option($args['name']);
        } elseif($args['wp_data'] == 'post_meta'){
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
        }

        switch ($args['type']) {
            case 'input':
                $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
                if($args['subtype'] != 'checkbox'){
                    $prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
                    $prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
                    if($args['id']=='nicappcrono_AuthorizationPageId')$prependEnd=' ' .get_the_title($value).'</div>';
                    $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
                    $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
                    $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
                    $size = (isset($args['size'])) ? 'size="'.$args['size'].'"' : 'size="40"';
                    if(isset($args['disabled'])){
                        // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" '.$size.' disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
                    } else {
                        echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" '.$size.' value="' . esc_attr($value) . '" />'.$prependEnd;
                    }
                    /*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
                    
                } else {
                    $checked = ($value) ? 'checked' : '';
                    echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
                }
                break;
            default:
                break;
        }
    }
    
    /**
     * Custom Post Type Metabox Save fields.
     *
     * @since    1.0.0
     * @access public 
     * @param string $post_id
     * 
     */
    public function saveCustomPostTypeMetaBoxData( $post_id ) {
        /*
         * We need to verify this came from our screen and with proper authorization,
         * because the save_post action can be triggered at other times.
         */
        // Check if our nonce is set.
        if ( ! isset( $_POST[$this->plugin_name.'_affiliates_meta_box_nonce'] ) ) return; 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST[$this->plugin_name.'_affiliates_meta_box_nonce'], $this->plugin_name.'_affiliate_meta_box' ) ) return;
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        // Check the user's permissions.
        if ( ! current_user_can( 'manage_options' ) ) return;
        // Make sure that it is set.
        if ( !isset( $_POST[$this->plugin_name.'_clientId'] ) && !isset( $_POST[$this->plugin_name.'_clientSecret'] ) && !isset( $_POST[$this->plugin_name.'_masterCalendar'] ) && !isset( $_POST[$this->plugin_name.'_masterAccessToken'] ) && !isset( $_POST[$this->plugin_name.'_masterRefreshToken'] ) && !isset( $_POST[$this->plugin_name.'_notes'] )) {
            return;
        }
        /* OK, it's safe for us to save the data now. */
        // Sanitize user input.
        $calendarID = sanitize_text_field( $_POST[$this->plugin_name."_calendarID"]);
        $calendarName = sanitize_text_field( $_POST[$this->plugin_name."_calendarName"]);
        $AccessToken = sanitize_text_field( $_POST[$this->plugin_name."_AccessToken"]);
        $RefreshToken = sanitize_text_field( $_POST[$this->plugin_name."_RefreshToken"]);
        $ProfileName = sanitize_text_field( $_POST[$this->plugin_name."_ProfileName"]);
        $ProfileID = sanitize_text_field( $_POST[$this->plugin_name."_ProfileID"]);
        $ProviderID = sanitize_text_field( $_POST[$this->plugin_name."_ProviderID"]);
        $Product_Display = sanitize_text_field( $_POST[$this->plugin_name."_Product_Display"]);
        $Product_Id = sanitize_text_field( $_POST[$this->plugin_name."_Product_Id"]);
        $notes = wp_kses_post( $_POST[$this->plugin_name."_notes"]);
        
        update_post_meta($post_id, $this->plugin_name.'_calendarID',$calendarID);
        update_post_meta($post_id, $this->plugin_name.'_calendarName',$calendarName);
        update_post_meta($post_id, $this->plugin_name.'_AccessToken',$AccessToken);
        update_post_meta($post_id, $this->plugin_name.'_RefreshToken',$RefreshToken);
        update_post_meta($post_id, $this->plugin_name.'_ProfileName',$ProfileName);
        update_post_meta($post_id, $this->plugin_name.'_ProfileID',$ProfileID);
        update_post_meta($post_id, $this->plugin_name.'_ProviderID',$ProviderID);
        update_post_meta($post_id, $this->plugin_name.'_Product_Display',$Product_Display);
        update_post_meta($post_id, $this->plugin_name.'_Product_Id',$Product_Id);
        update_post_meta($post_id, $this->plugin_name.'_notes',$notes);
    }
    
    /**
     * Display Admin settings.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function displayPluginAdminSettings() {
        if(isset($_GET['error_message'])){
            add_action('admin_notices', array($this,'pluginNameSettingsMessages'));
            do_action( 'admin_notices', $_GET['error_message'] );
        }
        require_once 'partials/'.$this->plugin_name.'-admin-settings-display.php';
    }
	
    /**
     * Display Calendar Scheduling.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function displayPluginAdminScheduling(){
        if(isset($_GET['error_message'])){
            add_action('admin_notices', array($this,'pluginNameSettingsMessages'));
            do_action( 'admin_notices', $_GET['error_message'] );
        }
        require_once 'partials/'.$this->plugin_name.'-admin-scheduling-display.php';
    }

    /**
     * Display Calendar Support.
     *
     * @since    1.0.0
     * @access public
     * @param void
     *
     */
    public function displayPluginAdminSupport(){
        if(isset($_GET['error_message'])){
            add_action('admin_notices', array($this,'pluginNameSettingsMessages'));
            do_action( 'admin_notices', $_GET['error_message'] );
        }
        require_once 'partials/'.$this->plugin_name.'-admin-support-display.php';
    }
	
    /**
     * Display Admin settings error messages.
     *
     * @since    1.0.0
     * @access public 
     * @param $error_message
     * 
     */
    public function pluginNameSettingsMessages($error_message){
        switch ($error_message) {
            case '1':
                $message = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'nicappcrono' );
                $err_code = esc_attr( 'nicappcrono_setting' );
                $setting_field = 'nicappcrono_setting';
                break;
        }
        $type = 'error';
        add_settings_error(
            $setting_field,
            $err_code,
            $message,
            $type
        );
    }
    
    /**
     * Display Admin settings register fields.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function registerAndBuildFields() {
        add_settings_section(
            'nicappcrono_general_section',      // ID used to identify this section and with which to register options
            '',                                 // Title to be displayed on the administration page
            array( $this, 'nicappcrono_display_general_account' ),  // Callback used to render the description of the section
            'nicappcrono_general_settings'      // Page on which to add this section of options
        );
//  Data Center
        add_settings_field('nicappcrono_DataCenter',__('Use European Data Center','nicappcrono'),   array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section',
            array ('type'=>'input', 'subtype'=>'checkbox',  'id'=>'nicappcrono_DataCenter',  		'name'=>'nicappcrono_DataCenter',   'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  clientId
        add_settings_field('nicappcrono_clientId',          __('Client ID','nicappcrono'),              array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section', 
            array ('type'=>'input', 'subtype'=>'text',      'id'=>'nicappcrono_clientId',           'name'=>'nicappcrono_clientId',             'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  clientSecret
        add_settings_field('nicappcrono_clientSecret',      __('Client Secret','nicappcrono'),          array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section', 
            array ('type'=>'input', 'subtype'=>'password',  'id'=>'nicappcrono_clientSecret',       'name'=>'nicappcrono_clientSecret',         'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  masterCalendar
        add_settings_field('nicappcrono_masterCalendar',    __('Master Calendar','nicappcrono'),        array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section', 
            array ('type'=>'input', 'subtype'=>'text',      'id'=>'nicappcrono_masterCalendar',     'name'=>'nicappcrono_masterCalendar',       'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  masterRefreshToken
        add_settings_field('nicappcrono_masterRefreshToken',__('Master Refresh Token','nicappcrono'),   array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section',
            array ('type'=>'input', 'subtype'=>'text',      'id'=>'nicappcrono_masterRefreshToken', 'name'=>'nicappcrono_masterRefreshToken',   'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  masterAccessToken
        add_settings_field('nicappcrono_masterAccessToken', __('Master Access Token','nicappcrono'),    array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings','nicappcrono_general_section',
            array ('type'=>'input', 'subtype'=>'text',      'id'=>'nicappcrono_masterAccessToken',  'name'=>'nicappcrono_masterAccessToken',    'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
//  Authorization page
        add_settings_field('nicappcrono_AuthorizationPageId',__('Authorization Page ID','nicappcrono'),   array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section',
            array ('type'=>'input', 'subtype'=>'text',      'id'=>'nicappcrono_AuthorizationPageId',  'name'=>'nicappcrono_AuthorizationPageId','required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option', 'size'=>'6'));
//  Authorization page
        add_settings_field('nicappcrono_CreateAuthPage',__('Create new Authorization Page','nicappcrono'),   array( $this, 'nicappcrono_render_settings_field' ), 'nicappcrono_general_settings', 'nicappcrono_general_section',
            array ('type'=>'input', 'subtype'=>'checkbox',  'id'=>'nicappcrono_CreateAuthPage',  		'name'=>'nicappcrono_CreateAuthPage',   'required'=>'true', 'get_options_list'=>'', 'value_type'=>'normal', 'wp_data'=>'option'));
		
        register_setting('nicappcrono_general_settings', 'nicappcrono_DataCenter');
        register_setting('nicappcrono_general_settings', 'nicappcrono_clientId');
        register_setting('nicappcrono_general_settings', 'nicappcrono_clientSecret');
        register_setting('nicappcrono_general_settings', 'nicappcrono_masterCalendar');
        register_setting('nicappcrono_general_settings', 'nicappcrono_masterAccessToken');
        register_setting('nicappcrono_general_settings', 'nicappcrono_masterRefreshToken');
        register_setting('nicappcrono_general_settings', 'nicappcrono_AuthorizationPageId');
        register_setting('nicappcrono_general_settings', 'nicappcrono_CreateAuthPage');
    }
    
    /**
     * Display Admin settings display name.
     *
     * @since    1.0.0
     * @access public  
     * @param void
     *
     */
    public function nicappcrono_display_general_account() {
        echo '<p>';
        _e('These settings refer to your Cronofy account for master calendar an apply to all Nic-app Crono functionality.','nicappcrono');
        echo '</p><hr/><p>';
        _e('Cronofy currently provides two data centers one in the USA, the default, and one in Germany. They are run as completely separate instances with no data flow between. This allows you to ensure data is kept within jurisdictional boundaries, eg. the EEA.','nicappcrono');
        echo '</p><p>';
        _e('Because there is no data flow then separate developer accounts need to be created on the instance that suits your requirements. Functionally the APIs are identical.','nicappcrono');
        echo '</p><hr/>';
    } 
    
    /**
     * Display Columns in post type page.
     *
     * @since    1.0.0
     * @access public 
     * @param array $columns
     * 
     */
    public function custom_post_type_columns( $columns ){
        unset(
            $columns['wpseo-score'],
            $columns['wpseo-score-readability'],
            $columns['wpseo-title'],
            $columns['wpseo-links'],
            $columns['wpseo-metadesc'],
            $columns['wpseo-focuskw']
        );
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'nicappcrono'),
            'calendarID' => __('Calendar ID', 'nicappcrono'),
            'Product_Display' => __('Product display', 'nicappcrono'),
            'Product_Id' =>__( 'Product ID', 'nicappcrono'),
            'date' =>__( 'Date', 'nicappcrono')
        );
    }
    
    /**
     * Fill Columns in post type page.
     *
     * @since    1.0.0
     * @access public 
     * @param string $column
     * 
     * @param string $postID
     * 
     */
    public function fill_custom_post_type_columns( $column, $postID ) {
        switch ( $column ) {
            case 'Product_Display' :
                (get_post_meta( $postID , $this->plugin_name.'_Product_Display' , true )) ? _e('Yes', 'nicappcrono') : _e('No', 'nicappcrono');
                break;
            case 'calendarID' :
                echo get_post_meta( $postID , $this->plugin_name.'_calendarID' , true );
                break;
            case 'Product_Id' :
                echo (get_post_meta( $postID , $this->plugin_name.'_Product_Display' , true )) ? get_post_meta( $postID , $this->plugin_name.'_Product_Id' , true ) : '';
                break;
        }
    }

    /**
     * Check defined authorization page.
     *
     * @since    1.0.0
     * @access private 
     * @param void
     * 
     */
    public function CheckAuthPage(){
        if( get_option( 'nicappcrono_CreateAuthPage' )){
            $auth_page = array(
                'post_type'    => 'page',
                'post_title'    => __('Authorization', 'nicappcrono'),
                'post_content'  => '[NicappAuth]',
                'post_status'   => 'publish',
                'post_author'   => wp_get_current_user()
            );
            $auth_page_id = wp_insert_post( $auth_page );
            if( !is_wp_error( $auth_page_id )){
                update_option( 'nicappcrono_AuthorizationPageId', $auth_page_id );
                update_option( 'nicappcrono_CreateAuthPage', false );
            }
        }
    }
	
    /**
     * Cron job fill mastercalendar with calendars entries.
     *
     * @since    1.0.0
     * @access private 
     * @param void
     * 
     */
    private function UpdateMasterCalendar(){
        if( strlen( get_option( 'nicappcrono_clientId' ) ) < 25 ) return false;
        $this->custom_logs( 'UpdateMasterCalendar Start Cron Session' );
        $fechaFrom = new DateTime();
        $fechaTo = new DateTime();
        $fechaTo->add( new DateInterval( 'P180D' ));
        $MasterEvents = $this->ReadMasterCalendar( $fechaFrom, $fechaTo );
        $loop = new WP_Query( array( 'post_type' => 'nicappcronocalendars' , 'posts_per_page' => 5000 , 'orderby' => 'rand', ) );
        while ( $loop->have_posts() ) : $loop->the_post();
            $this->custom_logs( 'Calendar $postID: ' . $loop->post->ID );
            $CalendarEvents = $this->ReadCalendar( $loop->post->ID, $fechaFrom, $fechaTo );
            if( $CalendarEvents ){
                $this->CreateMasterEvents( $loop->post->ID, $MasterEvents, $CalendarEvents );
                $this->UpdateExistingEvents( $loop->post->ID, $MasterEvents, $CalendarEvents );
                $this->DeleteExistingEvents( $loop->post->ID, $MasterEvents, $CalendarEvents );
                $this->UpdateExternalEvents( $loop->post->ID, $MasterEvents, $CalendarEvents );
            }
        endwhile;
        wp_reset_query();
        $this->custom_logs( 'UpdateMasterCalendar End Cron Session' );
        $this->custom_logs( '---' );
    }
	
    /**
     * Read mastercalendar content.
     *
     * @since    1.0.0
     * @access private 
     * @param DateTime $fechaFrom
     *            Date for the begining of the search.
     * @param DateTime $fechaTo
     *            Date for the end of the search.
     * @return mixed false|array $eventos 
     *            Array of events if calendar exists. Otherwise false.
     */
    private function ReadMasterCalendar( $fechaFrom, $fechaTo ){
        if( strlen( get_option( 'nicappcrono_clientId' ) ) < 25 ) return false;
        $params = array(
            "client_id" => get_option( 'nicappcrono_clientId' ),
            "client_secret" => get_option( 'nicappcrono_clientSecret' ),
            "access_token" => get_option( 'nicappcrono_masterAccessToken' ),
            "refresh_token" => get_option( 'nicappcrono_masterRefreshToken' ),
        );
        if( get_option( 'nicappcrono_DataCenter' ) ) $params["data_center"] = 'de';
        $mastercronofy = new Cronofy( $params );
        $mastercronofy->refresh_token();
        $masterevents = $mastercronofy->read_events( array( 
            "from" => $fechaFrom->format('Y-m-d'), 
            "to" => $fechaTo->format('Y-m-d'), 
            "tzid" => "Europe/Paris", 
            "include_managed" => true, 
            "calendar_ids" => get_option( 'nicappcrono_masterCalendar' ) 
        ) );
// return an array, not an object.
        $eventos = [];
        foreach ($masterevents as $event){ 
            $eventos[] = $event; 
        }
        return $eventos;
	}

    /**
     * Read calendar content.
     *
     * @since    1.0.0
     * @access private
     * @param string $postID
     * 			  Post Type Calendar ID	
     * @param DateTime $fechaFrom
     *            Date for the begining of the search.
     * @param DateTime $fecha_to
     *            Date for the end of the search.
     * @return mixed false|array $eventos 
     *            Array of events if calendar exists. Otherwise false.
     */
    private function ReadCalendar( $postID, $fechaFrom, $fechaTo ){
        if( strlen( get_option( 'nicappcrono_clientId' ) ) < 25 ) return false;
        if( strlen( get_post_meta ( $postID, $this->plugin_name.'_calendarID', true ) ) < 5 ) return false;
        $params = array(
            "client_id" => get_option( 'nicappcrono_clientId' ),
            "client_secret" => get_option( 'nicappcrono_clientSecret' ),
            "access_token" => get_option( 'nicappcrono_masterAccessToken' ),
            "refresh_token" => get_option( 'nicappcrono_masterRefreshToken' ),
        );
        if( get_option( 'nicappcrono_DataCenter' ) ) $params["data_center"] = 'de';
        $cronofy = new Cronofy( $params );
        $cronofy->refresh_token();
        $events = $cronofy->read_events( array( 
            "from" => $fechaFrom->format('Y-m-d'), 
            "to" => $fechaTo->format('Y-m-d'), 
            "tzid" => "Europe/Paris", 
            "include_managed" => true, 
            "calendar_ids" => get_post_meta ( $postID, $this->plugin_name.'_calendarID', true ) 
        ) );
    // return an array, not an object.
        $eventos = [];
        foreach ($events as $event){ 
            $eventos[] = $event; 
        }
        return $eventos;
    }

    /**
     * Create Master calendar event if it does not exist.
     *
     * @since    1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function CreateMasterEvents( $postID, $MasterEvents, $CalendarEvents ){
        foreach ( $CalendarEvents as $event ){
            if ( $event["transparency"] == "opaque" ){
                $eventExists = false;
                foreach ( $MasterEvents as $masterevent ){
                    $eventInfo = explode ( '.', $masterevent['event_id'] );
                    if( $masterevent['start'] == $event['start'] && $masterevent['end'] == $event['end'] && $eventInfo['4'] == $event['event_uid'] ) $eventExists = true;
                }
                if( !$eventExists ){
                    $this->UpdateEvent( array(
                        "start" => $event['start'],
                        "end" => $event['end'],
                        "event_uid" => $event['event_uid'],
                        "postID" => $postID,
                        "summary" => $event['summary'],
                        "description" => $event['description'],
                        "action" => 'create'
                    ) );
                }
            }
        }
    }
    
    /**
     * Update master calendar event content.
     *
     * @since    1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function UpdateExistingEvents( $postID, $MasterEvents, $CalendarEvents ){
        /*
         * If calendar is defined to be linked to a woocommerce product, dont update master event.
         *
         */
        if( get_post_meta( $postID , $this->plugin_name.'_Product_Display' , true ) ) return;
        foreach ( $MasterEvents as $masterevent ){
            $eventInfo = explode ( '.', $masterevent['event_id'] );
            if( ( isset( $eventInfo[0] ) && $eventInfo[0] == 'nicappcrono' ) && ( isset( $eventInfo[1] ) && $eventInfo[1] == $postID ) ){
                foreach( $CalendarEvents as $calendarevent){
                    if( $calendarevent['start'] == $masterevent['start'] && $calendarevent['end'] == $masterevent['end'] && $calendarevent['event_uid'] == $eventInfo[4] ){
                        $this->UpdateEvent( array(
                            "start" => $calendarevent['start'],
                            "end" => $calendarevent['end'],
                            "event_uid" => $calendarevent['event_uid'],
                            "postID" => $postID,
                            "summary" => $calendarevent['summary'],
                            "description" => $calendarevent['description'],
                            "action" => 'update'
                        ) );
                    }
                }
            }
        }
    }
    
    /**
     * Delete master calendar event if no longer exists.
     *
     * @since    1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function DeleteExistingEvents( $postID, $MasterEvents, $CalendarEvents ){
        foreach ( $MasterEvents as $event ){
            $eventInfo = explode ( '.', $event['event_id'] );
            if( ( isset( $eventInfo[0] ) && $eventInfo[0] == 'nicappcrono' ) && ( isset( $eventInfo[1] ) && $eventInfo[1] == $postID ) ){
                $eventExists = false;
                foreach( $CalendarEvents as $calendarevent){
                    if( ( $calendarevent['start'] == $event['start'] ) && ( $calendarevent['end'] == $event['end'] ) && ( $calendarevent['event_uid'] == $eventInfo['4'] ) ) $eventExists = true;
                }
                if( !$eventExists ){
                    $this->DeleteEvent( $event['event_id'] );
                }
            }
        }
    }
	
    /**
     * Update external calendar entry.
     *
     * @since    1.0.0
     * @access private
     * @param string $postID
     *
     * @param array $MasterEvents
     *
     * @param array $CalendarEvents
     *
     */
    private function UpdateExternalEvents( $postID, $MasterEvents, $CalendarEvents ){
        
    }
    
    /**
     * Update Master calendar entry
     *
     * @since  1.0.0
     * @access private
     * @param array $args
     * @return bool success
     *
     */
    private function UpdateEvent( $args ){
        if ( empty( $args["start"] ) ) return false;
        if ( empty( $args["end"] ) ) return false;
        if ( empty( $args["event_uid"] ) ) return false;
        if ( empty( $args["postID"] ) ) return false;
        $summary = $args["summary"];
        if ( $args["action"] == 'create' ){
            /*
             * If calendar is defined to be linked to a woocommerce product, change summary to product id on event creation.
             *
             * Compatible with PluginHive "Bookings and Appointments For WooCommerce".
             *
             */
            get_post_meta( $args["postID"] , $this->plugin_name.'_Product_Display' , true ) ? $summary = get_post_meta( $args["postID"], $this->plugin_name.'_Product_Id', true ) : $summary = $args["summary"] ;
        }
        /*
         * Event identifier.
         */
        $eventID = 'nicappcrono.' . $args['postID'] . '.' . $args['start'] . '.' . $args['end'] . '.' . $args['event_uid'];
	    
        $params = array(
            "client_id" => get_option( 'nicappcrono_clientId' ),
            "client_secret" => get_option( 'nicappcrono_clientSecret' ),
            "access_token" => get_option( 'nicappcrono_masterAccessToken' ),
            "refresh_token" => get_option( 'nicappcrono_masterRefreshToken' ),
        );
        if( get_option( 'nicappcrono_DataCenter' ) ) $params["data_center"] = 'de';
        $mastercronofy = new Cronofy( $params );
        $mastercronofy->refresh_token();
        $mastercronofy->upsert_event( array(
            "calendar_id" => get_option( 'nicappcrono_masterCalendar' ),
            "event_id"	=> $eventID,
            "summary" => $summary,
            "description" => $args['description'],
            "start" => $args['start'],
            "end" => $args['end'],
            "tzid" => "Etc/UTC",
        ) );
        ( $args["action"] == 'create') ? $this->custom_logs( 'UpdateEvent event created ' . $eventID ) : $this->custom_logs( 'UpdateEvent event updated ' . $eventID ) ;
        return true;
    }
	
    /**
     * Delete Master calendar entry
     *
     * @since  1.0.0
     * @access private
     * @param string $eventID
     * @return bool success
     *
     */
    private function DeleteEvent( $eventID ){
        $params = array(
            "client_id" => get_option( 'nicappcrono_clientId' ),
            "client_secret" => get_option( 'nicappcrono_clientSecret' ),
            "access_token" => get_option( 'nicappcrono_masterAccessToken' ),
            "refresh_token" => get_option( 'nicappcrono_masterRefreshToken' ),
        );
        if( get_option( 'nicappcrono_DataCenter' ) ) $params["data_center"] = 'de';
        $mastercronofy = new Cronofy( $params );
        $mastercronofy->refresh_token();
        $mastercronofy->delete_event( array(
            "calendar_id" => get_option( 'nicappcrono_masterCalendar' ),
            "event_id"	=> $eventID,
        ) );
        $this->custom_logs( 'DeleteEvent event deleted ' . $eventID );
        return true;
    }
	
    /**
     * Cron job maintenance tasks.
     *
     * @since    1.0.0
     * @access protected 
     * @param void
     * 
     */
    protected function nicappcronoMaintenance(){
        $this->custom_logs( 'nicappcronoMaintenance begins' );
        $files = scandir( plugin_dir_path(dirname(__FILE__)) . 'logs/' );
        foreach ( $files as $file ) {
            if( substr( $file , -4) == '.log'){
                $this->custom_logs( 'Logfile: ' . plugin_dir_path(dirname(__FILE__)) . 'logs/' . $file . ' -> ' . date("d-m-Y H:i:s", filemtime( plugin_dir_path(dirname(__FILE__)) . "logs/" . $file  )));
                if( time() > strtotime('+1 week', filemtime( plugin_dir_path(dirname(__FILE__)) . "logs/" . $file  ))){
                    $this->custom_logs( 'Old logfile' );
                    unlink( plugin_dir_path(dirname(__FILE__)) . "logs/" . $file  );				
                } 
            }
        }
        $this->custom_logs( 'nicappcronoMaintenance ends' );
        $this->custom_logs( '---' );
        return;
    }
	
    /**
     * Utility: scheduled job timestamp.
     *
     * @since    1.0.0
     * @access private
     * @param string|array $message
     *
     */
    private function scheduledJob() {
        if ( wp_next_scheduled ( 'nicappcronoCronJob' )) {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            echo wp_date("{$date_format} {$time_format}", wp_next_scheduled( 'nicappcronoCronJob' ), get_option( 'timezone_string' ) );
        }else{
            _e( 'No scheduled jobs. No calendar entries will be checked.', 'nicappcrono' );
        }
    }

    /**
     * Utility: log files.
     *
     * @since    1.0.0
     * @access private
     * @param string|array $message
     *
     */
    private function logFiles() {
        $files = scandir( plugin_dir_path(dirname(__FILE__)) . 'logs/' );
        ?>
		<form action="" method="post">
			<ul>	
				<?php foreach ( $files as $file ) { ?>
					<?php if( substr( $file , -4) == '.log'){?>
						<li>
							<input type="radio" id="age[]" name="logfile" value="<?php echo $file; ?>">
							<?php echo $file . ' -> ' . date("d-m-Y H:i:s", filemtime( plugin_dir_path(dirname(__FILE__)) . "logs/" . $file  ) ); ?>
						</li>
					<?php }?>
				<?php }?>
			</ul>
			<div class="nicappcrono-send-logfile">
				<input type="submit" value="<?php _e('View log file', 'nicappcrono'); ?>">
			</div>
		</form>
		<?php
    }
    
    /**
     * Utility: show log file.
     *
     * @since    1.0.0
     * @access private
     * @param string|array $message
     *
     */
    private function ShowLogFile(){
        if( isset( $_POST['logfile'] ) ){
            ?>
			<hr/>
			<h3><?php echo $_POST['logfile']; ?> </h3>
			<textarea id="nicappcronologfile" name="nicappcronologfile" rows="30" cols="180" readonly>
				<?php echo file_get_contents( plugin_dir_path(dirname(__FILE__)) . "logs/" . $_POST['logfile'] ); ?>
			</textarea>
			<?php
        }
    }
   
    /**
     * Plugin Add Settings Link.
     *
     * @since    1.0.0
     * @access private
     * @param array $links 
     *
     */
    public function nicappcrono_add_plugin_page_settings_link( $links ) {
        $links[] = '<a href="' .
            admin_url( 'admin.php?page=nicappcrono' ) .
            '">' . _e('Settings', 'nicappcrono') . '</a>';
            return $links;
    }
    
    /**
     * Utility: create entry in the log file.
     *
     * @since    1.0.0
     * @access private 
     * @param string|array $message
     * 
     */
    private function custom_logs( $message ) {
        if(is_array( $message ) ) {
            $message = json_encode( $message );
        }
        date_default_timezone_set("Europe/Paris");
        $time = date("Y-m-d H:i:s");
        $ban = "#$time: $message\r\n";
        $file = plugin_dir_path(dirname(__FILE__)) . 'logs/nicappcrono-log-' . date("Y-m-d") . '.log';
        $open = fopen( $file, "a" );
        $write = fputs( $open, $ban );
        fclose( $open );
    }
}