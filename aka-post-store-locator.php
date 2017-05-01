<?php
/**
 * Plugin Name: WP Post's Store Locator
 * Plugin URI:
 * Description: A full-featured map maker & location management interface for creating any post store locator for a post type.
 * Version: 1.0.0
 * Author: Alok Shrestha
 * Author URI:
 * Text Domain: aka_stores
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


/**
* Initialize Aka_Stores class
*/
if ( !class_exists('Aka_Stores') ) {

    class Aka_Stores {


        /*
        * Class constructor
        */
        function __construct() {
            $this->aka_define_constants();
            $this->aka_includes();
            $this->aka_plugin_setting();

            add_action( 'admin_init', array( $this, 'aka_register_settings' ) );

            add_action( 'admin_menu', array( $this, 'aka_register_menu_page' ) );
            global $aka_store_setting;
            $aka_store_setting = get_option('aka_store_options');

            add_action( 'wp_enqueue_scripts', array( $this, 'aka_stores_add_scripts_style' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'aka_stores_admin_scripts_style' ) );
            add_action( 'add_meta_boxes', array( $this, 'aka_stores_meta_boxes' ) );
            add_action( 'wp_ajax_return_address_latlng', array( $this, 'aka_stores_return_address_latlng' ) );
            add_action( 'wp_ajax_nopriv_return_address_latlng', array( $this, 'aka_stores_return_address_latlng' ) );

            add_action( 'save_post', array( $this, 'aka_stores_save_posts' ) );
            add_shortcode( 'aka-stores', array( $this, 'aka_list_stores') );

            add_action( 'wp_ajax_aka_store_search', array( $this, 'aka_store_search' ) );
            add_action( 'wp_ajax_nopriv_aka_store_search', array( $this, 'aka_store_search' ) );
        }


        /*
        * Define Plugin Constants.
        */
        public function aka_define_constants() {

            if ( !defined( 'AKA_STORE_VERSION' ) )
                define( 'AKA_STORE_VERSION', '1.0.0' );

            if ( !defined( 'AKA_STORE_URL' ) )
                define( 'AKA_STORE_URL', plugin_dir_url( __FILE__ ) );

            if ( !defined( 'AKA_STORE_BASENAME' ) )
                define( 'AKA_STORE_BASENAME', plugin_basename( __FILE__ ) );

            if ( ! defined( 'AKA_STORE_PLUGIN_DIR' ) )
                define( 'AKA_STORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        /*
        * Set admin form default value.
        */
        public function aka_plugin_setting() {

            global $aka_store_default_setting;
            $aka_store_default_setting = aka_stores_default_settings();
        }

        /*
        * Enqueue required admin styles and scripts.
        */
        public function aka_stores_admin_scripts_style() {

            global $aka_store_setting;

            wp_enqueue_style( 'admin-style', AKA_STORE_URL.'assets/css/admin-style.css' );

            wp_enqueue_script('jquery-ui-tabs');

            //deregister other google map scripts if enqueued
            aka_stores_deregister_other_gmaps();

            wp_enqueue_script( 'aka-gmap', '//maps.google.com/maps/api/js' . aka_stores_gmap_api_params('browser_key'), false, AKA_STORE_VERSION, true );
            wp_enqueue_script( 'admin-script', AKA_STORE_URL.'assets/js/admin-script.js', array('jquery'), AKA_STORE_VERSION, true );
            wp_localize_script( 'admin-script', 'aka_stores', array(
                'aka_settings' =>   $aka_store_setting,
                'ajaxurl' => admin_url( 'admin-ajax.php' )
                )

            );
        }


        public function aka_stores_add_scripts_style() {

            global $aka_store_setting;

            aka_stores_deregister_other_gmaps();

            wp_enqueue_style( 'aka-front-style', AKA_STORE_URL.'assets/css/aka-front-style.css');

            wp_enqueue_script( 'aka-gmap', '//maps.google.com/maps/api/js' . aka_stores_gmap_api_params('browser_key'), false, AKA_STORE_VERSION, true );

            wp_enqueue_script( 'aka-front-js', AKA_STORE_URL.'assets/js/aka-maps.js', array('jquery'), AKA_STORE_VERSION, true );
            wp_localize_script( 'aka-front-js', 'aka_stores', array(
                'aka_settings' =>   $aka_store_setting,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'marker_dir_url'    => AKA_STORE_URL.'markers/',
                )

            );

        }

        /*
        * Add menu page.
        */
        public function aka_register_menu_page() {
            add_menu_page( 'Store Settings', 'Store Settings', 'manage_options', 'aka_stores.php', array($this, 'aka_add_setting_page' ), '', 20 );
        }

        /*
        * Callback function of add_menu_page. Displays the page's content.
        */
        public function aka_add_setting_page() {

            require AKA_STORE_PLUGIN_DIR.'store-settings-form-new.php';

        }

        public function aka_register_settings() {

            register_setting( 'aka_store_options', 'aka_store_options', array( $this, 'aka_sanitize_settings' ) );

        }

        /*
        * Save admin form settings value to aka_store_option option.
        */
        public function aka_sanitize_settings() {

            if ( !isset( $_POST['validate_submit'] ) && !wp_verify_nonce( $_POST['validate_submit'], 'aka_nonce_stores' ) )
                return false;


            $input_options = array();
            global $aka_store_setting;

            //Map Api Section
            $input_options['server_key'] = sanitize_text_field( $_POST['aka_store_setting']['server_key']);

            $input_options['browser_key'] = sanitize_text_field( $_POST['aka_store_setting']['browser_key']);

            $input_options['language'] = wp_filter_nohtml_kses( $_POST['aka_store_setting']['language'] );
            $input_options['region'] = wp_filter_nohtml_kses( $_POST['aka_store_setting']['region'] );
            //End of Map Api Section

            //General Map Setting
            $input_options['autolocate_users'] = isset( $_POST['aka_store_setting']['autolocate_users'] ) ? 1 : 0;

            $input_options['no_of_locations'] = isset( $_POST['aka_store_setting']['no_of_locations'] ) ? 1 : 0;

            $input_options['start_point'] = sanitize_text_field( $_POST['aka_store_setting']['start_point'] );


            // If no location name is then we also empty the latlng values from the hidden input field.
            if ( empty( $input_options['start_point'] ) ) {
                $this->settings_error( 'start_point' );
                $input_options['start_latlng'] = '';
            } else {

                    /*
                     * If the start latlng is empty, but a start location name is provided,
                     * then make a request to the Geocode API to get it.
                     *
                     * This can only happen if there is a JS error in the admin area that breaks the
                     * Google Maps Autocomplete. So this code is only used as fallback to make sure
                     * the provided start location is always geocoded.
                     */
                    if ( $aka_store_setting['start_point'] != $_POST['aka_store_setting']['start_point'] && $aka_store_setting['start_latlng'] == $_POST['aka_store_setting']['start_latlng'] || empty( $_POST['aka_store_setting']['start_latlng'] ) ) {
                        $start_latlng = aka_stores_get_address_latlng( $_POST['aka_store_setting']['start_point'] );
                    } else {
                        $start_latlng = sanitize_text_field( $_POST['aka_store_setting']['start_latlng'] );
                    }

                    $input_options['start_latlng'] = $start_latlng;
                }

                $input_options['zoom_level'] = $_POST['aka_store_setting']['zoom_level'];

                $input_options['max_zoom_level'] = $_POST['aka_store_setting']['max_zoom_level'];

                $input_options['direction_view_control'] = isset ($_POST['aka_store_setting']['direction_view_control'] ) ? 1 : 0;

                $input_options['map_type_control'] = isset( $_POST['aka_store_setting']['map_type_control'] ) ? 1 : 0;

                $input_options['scrollwheel_zoom'] = isset( $_POST['aka_store_setting']['scrollwheel_zoom'] ) ? 1 : 0;

                $input_options['map_type'] = $_POST['aka_store_setting']['map_type'];
            //End of General Map Setting

            //Start of Search
                $input_options['autocomplete'] = isset( $_POST['aka_store_setting']['autocomplete'] ) ? 1 : 0;

                $input_options['radius_dropdown'] = isset( $_POST['aka_store_setting']['radius_dropdown'] ) ? 1 : 0;

                $input_options['max_results_dropdown'] = isset( $_POST['aka_store_setting']['max_results_dropdown'] ) ? 1 : 0;

                $input_options['distance_unit'] = ( $_POST['aka_store_setting']['distance_unit'] == 'km' ) ? 'km' : 'mi';


            // Check for a valid max results value, otherwise we use the default.
                if ( !empty( $_POST['aka_store_setting']['max_results'] ) ) {
                    $input_options['max_results'] = sanitize_text_field( $_POST['aka_store_setting']['max_results'] );
                } else {
                    $this->settings_error( 'max_results' );
                    $input_options['max_results'] = aka_stores_get_default_setting( 'max_results' );
                }


            // See if a search radius value exist, otherwise we use the default.
                if ( !empty( $_POST['aka_store_setting']['radius_options'] ) ) {
                    $input_options['radius_options'] = sanitize_text_field( $_POST['aka_store_setting']['radius_options'] );
                } else {
                    $this->settings_error( 'radius_options' );
                    $input_options['radius_options'] = aka_stores_get_default_setting( 'radius_options' );
                }
            //End of Search Section

            //Role Manager Section
                if ( isset( $_POST['aka_store_setting']['post_type'] ) && !empty( $_POST['aka_store_setting']['post_type'] ) ) {

                    $input_options['post_type'] = $_POST['aka_store_setting']['post_type'];

                }

                $input_options['show_url_field'] = isset( $_POST['aka_store_setting']['show_url_field'] ) ? 1 : 0;
                $input_options['show_phone_field'] = isset( $_POST['aka_store_setting']['show_phone_field'] ) ? 1 : 0;
                $input_options['show_description_field'] = isset( $_POST['aka_store_setting']['show_description_field'] ) ? 1 : 0;

                return $input_options;

            }

        /*
        * Includes certain functions from aka-functions.php.
        */
        public function aka_includes() {

            require_once( AKA_STORE_PLUGIN_DIR. 'inc/aka-functions.php' );

        }

        /*
        * Add meta box to selected post types.
        */
        public function aka_stores_meta_boxes() {

            global $aka_store_setting;

            if ( !empty( $aka_store_setting['post_type'] ) && isset( $aka_store_setting['post_type'] ) ) {
                $option_postTypes = $aka_store_setting['post_type'];

                foreach ($option_postTypes as $type_value) {

                    add_meta_box( 'meta-box-id', __( 'AKA Stores Box', 'aka_stores' ), array( $this, 'aka_display_metabox' ), $type_value );
                }
            }

        }

        /*
        * Callback function displaying form elements to add_meta_box.
        */
        public function aka_display_metabox() {

            require AKA_STORE_PLUGIN_DIR.'metabox-form.php';

        }

        public function aka_stores_return_address_latlng() {

            if ( isset( $_POST['location'] ) && !empty( $_POST['location'] ) ) {
                $location = aka_stores_get_address_latlng($_POST['location']);
                echo $location;
            }
            die();
        }

        public function aka_stores_save_posts( $post_id ) {

            if ( isset( $_POST['aka_store_meta'] ) && !empty( $_POST['aka_store_meta'] ) ) {

                update_post_meta( $post_id, 'aka_saved_locators', array_values( $_POST['aka_store_meta'] ) );
            } else {
                update_post_meta( $post_id, 'aka_saved_locators', '' );
            }

        }

        public function aka_list_stores( $atts ) {

            global $aka_store_setting;

            $values = shortcode_atts( array(
                'id'    => '',
                ), $atts );

            $post_id = get_the_ID();

            if ( isset( $values['id'] ) && !empty( $values['id'] ) ) {
                $post_id = $values['id'];
            }

            $aka_saved_locators = get_post_meta( $post_id, 'aka_saved_locators', true );
            ob_start();
            ?>
            <div class="aka-store-wrap">
                <aside class="aka-left-wrap">

                    <?php
                    if ( !empty( $aka_saved_locators ) ) { ?>
                    <ul class="store-ul-lists" id="aka-store-lists">

                        <?php
                        foreach ( $aka_saved_locators as $aka_key => $store_value ) {

                            $sn = $aka_key;

                            ?>
                            <li class="store-items" id="store-item-id-<?php echo $aka_key; ?>" data-storeid="<?php echo $aka_key; ?>" data-storename="<?php echo $store_value['aka_name']; ?>" data-storeurl="<?php echo $store_value['aka_url']; ?>" data-latlng="<?php echo $store_value['aka_location_latn']; ?>" data-phone="<?php echo $store_value['aka_phone']; ?>" data-address="<?php echo $store_value['aka_location']; ?>" data-desc="<?php echo $store_value['aka_description']; ?>">
                                <div class="map-content">
                                    <span class="store-key"><?php echo ++$sn; ?></span>
                                    <span class="store-title">
                                        <?php $return_output = aka_stores_get_link_title( $store_value['aka_name'], $store_value['aka_url'], $aka_store_setting['show_url_field'] );

                                        if ( !empty( $return_output ) ) {

                                            if ( !empty( $return_output['before_wrap'] ) ) {
                                                echo $return_output['before_wrap'];
                                            }
                                            if ( !empty( $return_output['title'] ) ) {
                                                echo $return_output['title'];
                                            }
                                            if ( !empty( $return_output['after_wrap'] ) ) {
                                                echo $return_output['after_wrap'];
                                            }
                                        }
                                        ?>
                                    </span>
                                    <?php
                                    if ( $aka_store_setting['show_phone_field'] ) {

                                        echo '<span class="store-items">'.$store_value['aka_phone'].'</span>';

                                    }

                                    echo '<span class="store-items">'.$store_value['aka_location'].'</span>';

                                    if ( $aka_store_setting['show_description_field'] ) {

                                        echo '<p>'.$store_value['aka_description'].'</p>';

                                    }

                                    if ( $aka_store_setting['direction_view_control'] ) {
                                        echo '<span class="store-items"><a class="aka-get-direction" href="#" id="get-direction-'.$aka_key.'">Direction</a></span>';
                                    }


                                    ?>
                                </div>
                            </li>
                            <?php
                        }
                        ?>

                    </ul>
                    <?php
                }

                //Render direction routes
                if ( $aka_store_setting['direction_view_control'] ) {
                    ?>
                    <div class="aka-ren-dir" id="aka-direction-detail" style="display: none;">
                        <ul></ul>
                    </div>
                    <?php
                }
                ?>
                </aside>

            <aside class="aka-right-wrap">

                <div id="aka-search-wrap">
                    <form class="aka-search-form">
                        <div class="aka-input">
                            <div>
                                <label for="aka-search-input">Location</label>
                            </div>
                            <input id="aka-search-input" value="" name="aka-search-input" placeholder="" aria-required="true" autocomplete="off" type="text">
                        </div>
                        <div class="aka-select-wrap">
                            <div id="aka-radius">
                                <label for="aka-radius-dropdown">Search Radius</label>
                                <div class="aka-dropdown">
                                    <select id="aka-radius-dropdown" class="" name="aka-radius">
                                        <?php echo aka_stores_get_dropdown_list('radius_options'); ?>
                                    </select>
                                </div>
                            </div>
                            <div id="aka-results">
                                <label for="aka-results-dropdown">Results</label>
                                <div class="aka-dropdown">
                                    <select id="aka-results-dropdown" class="" name="aka-results" >
                                        <?php echo aka_stores_get_dropdown_list('max_results'); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="aka-search-btn-wrap">
                            <input type="hidden" id="aka_post_id" name="aka_post_id" value="<?php echo $post_id; ?>">
                            <input id="aka-search-btn" value="Search" type="submit">
                        </div>
                    </form>
                </div>

                <div class="aka-map-wrap">
                    <div id="aka-map" style="height: 418px;"></div>
                </div>
            </aside>
        </div>
        <?php

        $return_content = ob_get_clean();
        ob_flush();
        return $return_content;
    }


    /**
     * Handle the Ajax search on the frontend.
     * @return json A list of store locations that are located within the selected search radius
     */
    function aka_store_search() {
        global $aka_store_setting;

        $exploded_start_latlng = explode( ',', $aka_store_setting['start_latlng'] );
        $post_id = $_POST['post_id'];

        $search_radius = $_POST['search_radius'];
        $stores_count = $_POST['stores_count'];


        $myformlat = ( isset( $_POST['lat'] ) && !empty( $_POST['lat'] ) ) ? $_POST['lat'] : $exploded_start_latlng[0];
        $myformlng = ( isset( $_POST['lng'] ) && !empty( $_POST['lng'] ) ) ? $_POST['lng'] : $exploded_start_latlng[1];

        $radius = ( $aka_store_setting['distance_unit'] == 'km' ) ? 6371 : 3959;
        $store_data = array();

        $aka_saved_locators = get_post_meta( $post_id, 'aka_saved_locators', true );
        if ( !empty( $aka_saved_locators ) ) {
            foreach ($aka_saved_locators as $store_key => $store_value) {
                if ( $store_key < $stores_count) {

                    $exploded_store_latlng = explode( ',', $store_value['aka_location_latn'] );
                    $store_lat = $exploded_store_latlng[0];
                    $store_lng = $exploded_store_latlng[1];

                    $distance = $radius * acos( cos( deg2rad( $myformlat ) ) * cos( deg2rad( $store_lat ) ) * cos( deg2rad( $store_lng ) - deg2rad( $myformlng ) ) + sin( deg2rad( $myformlat ) ) * sin( deg2rad( $store_lat ) ) );

                    if ( $distance <= $search_radius ) {
                        $store_data[] = $aka_saved_locators[$store_key];
                    }
                }

            }
            wp_send_json( $store_data );
        }
        die();

    }


        /**
         * Handle the different validation errors for the plugin settings.
         */
        private function settings_error( $error_type ) {

            switch ( $error_type ) {
                case 'max_results':
                $error_msg = __( 'The max results field cannot be empty, the default value has been restored.', 'aka_stores' );
                break;
                case 'radius_options':
                $error_msg = __( 'The search radius field cannot be empty, the default value has been restored.', 'aka_stores' );
                break;
                case 'start_point':
                $error_msg = sprintf( __( 'Please provide the name of a city or country that can be used as a starting point under "Map Settings". %s This will only be used if auto-locating the user fails, or the option itself is disabled.', 'aka_stores' ), '<br><br>' );
                break;
            }

            add_settings_error( 'setting-errors', esc_attr( 'settings_fail' ), $error_msg, 'error' );
        }
    }

    $aka_stores = new Aka_Stores();
}