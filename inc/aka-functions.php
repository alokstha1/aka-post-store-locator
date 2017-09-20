<?php
/**
* Returns map api url based on languare and regions set.
*/
function aka_stores_gmap_api_params( $api_key_type, $geocode_params = false ) {
    $aka_store_setting = get_option('aka_store_options');
    $api_params = '';
    $param_keys = array( 'language', 'region', 'key' );

    /**
     * The geocode params are included after the address so we need to
     * use a '&' as the first char, but when the maps script is included on
     * the front-end it does need to start with a '?'.
     */
    $first_sep = ( $geocode_params ) ? '&' : '?';

    foreach ( $param_keys as $param_key ) {
        if ( 'key' == $param_key ) {
            $option_key = $api_key_type;
        } else {
            $option_key = $param_key;
        }

        $param_val = $aka_store_setting[$option_key];

        if ( !empty( $param_val ) ) {
            $api_params .= $param_key . '=' . $param_val . '&';
        }
    }

    if ( $api_params ) {
        $api_params = $first_sep . rtrim( $api_params, '&' );
    }

    if ( 'browser_key' == $api_key_type && $aka_store_setting['autocomplete']  ) {
        $api_params .= '&libraries=places';
    }

    return apply_filters( 'aka_gmap_api_params', $api_params );
}

/**
* Return Map zoom level dropdown setting for admin.
*/
function aka_stores_map_zoom_levels( $saved_zoom_level = '' ) {
    $select_dropdown = '<select id="zoom-level" name="aka_store_setting[zoom_level]" autocomplete="off">';

    for ( $i = 1; $i < 13; $i++ ) {
        $selected = '';

        if ( isset( $saved_zoom_level ) && !empty( $saved_zoom_level ) ) {
            $selected = ( $saved_zoom_level == $i ) ? 'selected="selected"' : '';
        }

        switch ( $i ) {
            case 1:
            $zoom_desc = ' - ' . __( 'World view', 'aka-stores' );
            break;
            case 3:
            $zoom_desc = ' - ' . __( 'Default', 'aka-stores' );
            if ( !isset( $saved_zoom_level ) && empty( $saved_zoom_level ) ) {
                $selected = 'selected="selected"';
            }
            break;
            case 12:
            $zoom_desc = ' - ' . __( 'Roadmap', 'aka-stores' );
            break;
            default:
            $zoom_desc = '';
        }
        $select_dropdown .= "<option value='$i' $selected>". $i . esc_html( $zoom_desc ) . "</option>";
    }
    $select_dropdown .= "</select>";
    return $select_dropdown;
}

/**
* Return google map type dropdown.
*/
function aka_stores_map_type_options( $saved_map_type = '' ) {
    $map_types = array(
        'roadmap'   => __( 'Roadmap', 'aka-stores' ),
        'satellite' => __( 'Satellite', 'aka-stores' ),
        'hybrid'    => __( 'Hybrid', 'aka-stores' ),
        'terrain'   => __( 'Terrain', 'aka-stores' )
        );

    $select_dropdown = '<select name="aka_store_setting[map_type]" id="map-type">';
    foreach ( $map_types as $key => $map_type_value ) {
        $selected = ( $key == $saved_map_type ) ? 'selected="selected"' : '';
        $select_dropdown .= '<option value="' . esc_attr( $key ) . '" $selected>' . esc_html( $map_type_value ) . '</option>';
    }
    $select_dropdown .= '</select>';
    return $select_dropdown;
}


/**
* Return language and region setting dropdown for admin setting.
*/
function aka_stores_api_option_lists( $list, $list_option_value = '' ) {
    switch ( $list ) {
        case 'language':
        $api_option_list = array (
            __('Select your language', 'aka-stores')    => '',
            __('English', 'aka-stores')                 => 'en',
            __('Arabic', 'aka-stores')                  => 'ar',
            __('Basque', 'aka-stores')                  => 'eu',
            __('Bulgarian', 'aka-stores')               => 'bg',
            __('Bengali', 'aka-stores')                 => 'bn',
            __('Catalan', 'aka-stores')                 => 'ca',
            __('Czech', 'aka-stores')                   => 'cs',
            __('Danish', 'aka-stores')                  => 'da',
            __('German', 'aka-stores')                  => 'de',
            __('Greek', 'aka-stores')                   => 'el',
            __('English (Australian)', 'aka-stores')    => 'en-AU',
            __('English (Great Britain)', 'aka-stores') => 'en-GB',
            __('Spanish', 'aka-stores')                 => 'es',
            __('Farsi', 'aka-stores')                   => 'fa',
            __('Finnish', 'aka-stores')                 => 'fi',
            __('Filipino', 'aka-stores')                => 'fil',
            __('French', 'aka-stores')                  => 'fr',
            __('Galician', 'aka-stores')                => 'gl',
            __('Gujarati', 'aka-stores')                => 'gu',
            __('Hindi', 'aka-stores')                   => 'hi',
            __('Croatian', 'aka-stores')                => 'hr',
            __('Hungarian', 'aka-stores')               => 'hu',
            __('Indonesian', 'aka-stores')              => 'id',
            __('Italian', 'aka-stores')                 => 'it',
            __('Hebrew', 'aka-stores')                  => 'iw',
            __('Japanese', 'aka-stores')                => 'ja',
            __('Kannada', 'aka-stores')                 => 'kn',
            __('Korean', 'aka-stores')                  => 'ko',
            __('Lithuanian', 'aka-stores')              => 'lt',
            __('Latvian', 'aka-stores')                 => 'lv',
            __('Malayalam', 'aka-stores')               => 'ml',
            __('Marathi', 'aka-stores')                 => 'mr',
            __('Dutch', 'aka-stores')                   => 'nl',
            __('Norwegian', 'aka-stores')               => 'no',
            __('Norwegian Nynorsk', 'aka-stores')       => 'nn',
            __('Polish', 'aka-stores')                  => 'pl',
            __('Portuguese', 'aka-stores')              => 'pt',
            __('Portuguese (Brazil)', 'aka-stores')     => 'pt-BR',
            __('Portuguese (Portugal)', 'aka-stores')   => 'pt-PT',
            __('Romanian', 'aka-stores')                => 'ro',
            __('Russian', 'aka-stores')                 => 'ru',
            __('Slovak', 'aka-stores')                  => 'sk',
            __('Slovenian', 'aka-stores')               => 'sl',
            __('Serbian', 'aka-stores')                 => 'sr',
            __('Swedish', 'aka-stores')                 => 'sv',
            __('Tagalog', 'aka-stores')                 => 'tl',
            __('Tamil', 'aka-stores')                   => 'ta',
            __('Telugu', 'aka-stores')                  => 'te',
            __('Thai', 'aka-stores')                    => 'th',
            __('Turkish', 'aka-stores')                 => 'tr',
            __('Ukrainian', 'aka-stores')               => 'uk',
            __('Vietnamese', 'aka-stores')              => 'vi',
            __('Chinese (Simplified)', 'aka-stores')    => 'zh-CN',
            __('Chinese (Traditional)' ,'aka-stores')   => 'zh-TW'
            );
        break;
        case 'region':
        $api_option_list = array (
            __('Select your region', 'aka-stores')               => '',
            __('Afghanistan', 'aka-stores')                      => 'af',
            __('Albania', 'aka-stores')                          => 'al',
            __('Algeria', 'aka-stores')                          => 'dz',
            __('American Samoa', 'aka-stores')                   => 'as',
            __('Andorra', 'aka-stores')                          => 'ad',
            __('Anguilla', 'aka-stores')                         => 'ai',
            __('Angola', 'aka-stores')                           => 'ao',
            __('Antigua and Barbuda', 'aka-stores')              => 'ag',
            __('Argentina', 'aka-stores')                        => 'ar',
            __('Armenia', 'aka-stores')                          => 'am',
            __('Aruba', 'aka-stores')                            => 'aw',
            __('Australia', 'aka-stores')                        => 'au',
            __('Austria', 'aka-stores')                          => 'at',
            __('Azerbaijan', 'aka-stores')                       => 'az',
            __('Bahamas', 'aka-stores')                          => 'bs',
            __('Bahrain', 'aka-stores')                          => 'bh',
            __('Bangladesh', 'aka-stores')                       => 'bd',
            __('Barbados', 'aka-stores')                         => 'bb',
            __('Belarus', 'aka-stores')                          => 'by',
            __('Belgium', 'aka-stores')                          => 'be',
            __('Belize', 'aka-stores')                           => 'bz',
            __('Benin', 'aka-stores')                            => 'bj',
            __('Bermuda', 'aka-stores')                          => 'bm',
            __('Bhutan', 'aka-stores')                           => 'bt',
            __('Bolivia', 'aka-stores')                          => 'bo',
            __('Bosnia and Herzegovina', 'aka-stores')           => 'ba',
            __('Botswana', 'aka-stores')                         => 'bw',
            __('Brazil', 'aka-stores')                           => 'br',
            __('British Indian Ocean Territory', 'aka-stores')   => 'io',
            __('Brunei', 'aka-stores')                           => 'bn',
            __('Bulgaria', 'aka-stores')                         => 'bg',
            __('Burkina Faso', 'aka-stores')                     => 'bf',
            __('Burundi', 'aka-stores')                          => 'bi',
            __('Cambodia', 'aka-stores')                         => 'kh',
            __('Cameroon', 'aka-stores')                         => 'cm',
            __('Canada', 'aka-stores')                           => 'ca',
            __('Cape Verde', 'aka-stores')                       => 'cv',
            __('Cayman Islands', 'aka-stores')                   => 'ky',
            __('Central African Republic', 'aka-stores')         => 'cf',
            __('Chad', 'aka-stores')                             => 'td',
            __('Chile', 'aka-stores')                            => 'cl',
            __('China', 'aka-stores')                            => 'cn',
            __('Christmas Island', 'aka-stores')                 => 'cx',
            __('Cocos Islands', 'aka-stores')                    => 'cc',
            __('Colombia', 'aka-stores')                         => 'co',
            __('Comoros', 'aka-stores')                          => 'km',
            __('Congo', 'aka-stores')                            => 'cg',
            __('Costa Rica', 'aka-stores')                       => 'cr',
            __('Côte d\'Ivoire', 'aka-stores')                   => 'ci',
            __('Croatia', 'aka-stores')                          => 'hr',
            __('Cuba', 'aka-stores')                             => 'cu',
            __('Czech Republic', 'aka-stores')                   => 'cz',
            __('Denmark', 'aka-stores')                          => 'dk',
            __('Djibouti', 'aka-stores')                         => 'dj',
            __('Democratic Republic of the Congo', 'aka-stores') => 'cd',
            __('Dominica', 'aka-stores')                         => 'dm',
            __('Dominican Republic', 'aka-stores')               => 'do',
            __('Ecuador', 'aka-stores')                          => 'ec',
            __('Egypt', 'aka-stores')                            => 'eg',
            __('El Salvador', 'aka-stores')                      => 'sv',
            __('Equatorial Guinea', 'aka-stores')                => 'gq',
            __('Eritrea', 'aka-stores')                          => 'er',
            __('Estonia', 'aka-stores')                          => 'ee',
            __('Ethiopia', 'aka-stores')                         => 'et',
            __('Fiji', 'aka-stores')                             => 'fj',
            __('Finland', 'aka-stores')                          => 'fi',
            __('France', 'aka-stores')                           => 'fr',
            __('French Guiana', 'aka-stores')                    => 'gf',
            __('Gabon', 'aka-stores')                            => 'ga',
            __('Gambia', 'aka-stores')                           => 'gm',
            __('Germany', 'aka-stores')                          => 'de',
            __('Ghana', 'aka-stores')                            => 'gh',
            __('Greenland', 'aka-stores')                        => 'gl',
            __('Greece', 'aka-stores')                           => 'gr',
            __('Grenada', 'aka-stores')                          => 'gd',
            __('Guam', 'aka-stores')                             => 'gu',
            __('Guadeloupe', 'aka-stores')                       => 'gp',
            __('Guatemala', 'aka-stores')                        => 'gt',
            __('Guinea', 'aka-stores')                           => 'gn',
            __('Guinea-Bissau', 'aka-stores')                    => 'gw',
            __('Haiti', 'aka-stores')                            => 'ht',
            __('Honduras', 'aka-stores')                         => 'hn',
            __('Hong Kong', 'aka-stores')                        => 'hk',
            __('Hungary', 'aka-stores')                          => 'hu',
            __('Iceland', 'aka-stores')                          => 'is',
            __('India', 'aka-stores')                            => 'in',
            __('Indonesia', 'aka-stores')                        => 'id',
            __('Iran', 'aka-stores')                             => 'ir',
            __('Iraq', 'aka-stores')                             => 'iq',
            __('Ireland', 'aka-stores')                          => 'ie',
            __('Israel', 'aka-stores')                           => 'il',
            __('Italy', 'aka-stores')                            => 'it',
            __('Jamaica', 'aka-stores')                          => 'jm',
            __('Japan', 'aka-stores')                            => 'jp',
            __('Jordan', 'aka-stores')                           => 'jo',
            __('Kazakhstan', 'aka-stores')                       => 'kz',
            __('Kenya', 'aka-stores')                            => 'ke',
            __('Kuwait', 'aka-stores')                           => 'kw',
            __('Kyrgyzstan', 'aka-stores')                       => 'kg',
            __('Laos', 'aka-stores')                             => 'la',
            __('Latvia', 'aka-stores')                           => 'lv',
            __('Lebanon', 'aka-stores')                          => 'lb',
            __('Lesotho', 'aka-stores')                          => 'ls',
            __('Liberia', 'aka-stores')                          => 'lr',
            __('Libya', 'aka-stores')                            => 'ly',
            __('Liechtenstein', 'aka-stores')                    => 'li',
            __('Lithuania', 'aka-stores')                        => 'lt',
            __('Luxembourg', 'aka-stores')                       => 'lu',
            __('Macau', 'aka-stores')                            => 'mo',
            __('Macedonia', 'aka-stores')                        => 'mk',
            __('Madagascar', 'aka-stores')                       => 'mg',
            __('Malawi', 'aka-stores')                           => 'mw',
            __('Malaysia ', 'aka-stores')                        => 'my',
            __('Mali', 'aka-stores')                             => 'ml',
            __('Marshall Islands', 'aka-stores')                 => 'mh',
            __('Martinique', 'aka-stores')                       => 'il',
            __('Mauritania', 'aka-stores')                       => 'mr',
            __('Mauritius', 'aka-stores')                        => 'mu',
            __('Mexico', 'aka-stores')                           => 'mx',
            __('Micronesia', 'aka-stores')                       => 'fm',
            __('Moldova', 'aka-stores')                          => 'md',
            __('Monaco' ,'aka-stores')                           => 'mc',
            __('Mongolia', 'aka-stores')                         => 'mn',
            __('Montenegro', 'aka-stores')                       => 'me',
            __('Montserrat', 'aka-stores')                       => 'ms',
            __('Morocco', 'aka-stores')                          => 'ma',
            __('Mozambique', 'aka-stores')                       => 'mz',
            __('Myanmar', 'aka-stores')                          => 'mm',
            __('Namibia', 'aka-stores')                          => 'na',
            __('Nauru', 'aka-stores')                            => 'nr',
            __('Nepal', 'aka-stores')                            => 'np',
            __('Netherlands', 'aka-stores')                      => 'nl',
            __('Netherlands Antilles', 'aka-stores')             => 'an',
            __('New Zealand', 'aka-stores')                      => 'nz',
            __('Nicaragua', 'aka-stores')                        => 'ni',
            __('Niger', 'aka-stores')                            => 'ne',
            __('Nigeria', 'aka-stores')                          => 'ng',
            __('Niue', 'aka-stores')                             => 'nu',
            __('Northern Mariana Islands', 'aka-stores')         => 'mp',
            __('Norway', 'aka-stores')                           => 'no',
            __('Oman', 'aka-stores')                             => 'om',
            __('Pakistan', 'aka-stores')                         => 'pk',
            __('Panama' ,'aka-stores')                           => 'pa',
            __('Papua New Guinea', 'aka-stores')                 => 'pg',
            __('Paraguay' ,'aka-stores')                         => 'py',
            __('Peru', 'aka-stores')                             => 'pe',
            __('Philippines', 'aka-stores')                      => 'ph',
            __('Pitcairn Islands', 'aka-stores')                 => 'pn',
            __('Poland', 'aka-stores')                           => 'pl',
            __('Portugal', 'aka-stores')                         => 'pt',
            __('Qatar', 'aka-stores')                            => 'qa',
            __('Reunion', 'aka-stores')                          => 're',
            __('Romania', 'aka-stores')                          => 'ro',
            __('Russia', 'aka-stores')                           => 'ru',
            __('Rwanda', 'aka-stores')                           => 'rw',
            __('Saint Helena', 'aka-stores')                     => 'sh',
            __('Saint Kitts and Nevis', 'aka-stores')            => 'kn',
            __('Saint Vincent and the Grenadines', 'aka-stores') => 'vc',
            __('Saint Lucia', 'aka-stores')                      => 'lc',
            __('Samoa', 'aka-stores')                            => 'ws',
            __('San Marino', 'aka-stores')                       => 'sm',
            __('São Tomé and Príncipe', 'aka-stores')            => 'st',
            __('Saudi Arabia', 'aka-stores')                     => 'sa',
            __('Senegal', 'aka-stores')                          => 'sn',
            __('Serbia', 'aka-stores')                           => 'rs',
            __('Seychelles', 'aka-stores')                       => 'sc',
            __('Sierra Leone', 'aka-stores')                     => 'sl',
            __('Singapore', 'aka-stores')                        => 'sg',
            __('Slovakia', 'aka-stores')                         => 'si',
            __('Solomon Islands', 'aka-stores')                  => 'sb',
            __('Somalia', 'aka-stores')                          => 'so',
            __('South Africa', 'aka-stores')                     => 'za',
            __('South Korea', 'aka-stores')                      => 'kr',
            __('Spain', 'aka-stores')                            => 'es',
            __('Sri Lanka', 'aka-stores')                        => 'lk',
            __('Sudan', 'aka-stores')                            => 'sd',
            __('Swaziland', 'aka-stores')                        => 'sz',
            __('Sweden', 'aka-stores')                           => 'se',
            __('Switzerland', 'aka-stores')                      => 'ch',
            __('Syria', 'aka-stores')                            => 'sy',
            __('Taiwan', 'aka-stores')                           => 'tw',
            __('Tajikistan', 'aka-stores')                       => 'tj',
            __('Tanzania', 'aka-stores')                         => 'tz',
            __('Thailand', 'aka-stores')                         => 'th',
            __('Timor-Leste', 'aka-stores')                      => 'tl',
            __('Tokelau' ,'aka-stores')                          => 'tk',
            __('Togo', 'aka-stores')                             => 'tg',
            __('Tonga', 'aka-stores')                            => 'to',
            __('Trinidad and Tobago', 'aka-stores')              => 'tt',
            __('Tunisia', 'aka-stores')                          => 'tn',
            __('Turkey', 'aka-stores')                           => 'tr',
            __('Turkmenistan', 'aka-stores')                     => 'tm',
            __('Tuvalu', 'aka-stores')                           => 'tv',
            __('Uganda', 'aka-stores')                           => 'ug',
            __('Ukraine', 'aka-stores')                          => 'ua',
            __('United Arab Emirates', 'aka-stores')             => 'ae',
            __('United Kingdom', 'aka-stores')                   => 'gb',
            __('United States', 'aka-stores')                    => 'us',
            __('Uruguay', 'aka-stores')                          => 'uy',
            __('Uzbekistan', 'aka-stores')                       => 'uz',
            __('Wallis Futuna', 'aka-stores')                    => 'wf',
            __('Venezuela', 'aka-stores')                        => 've',
            __('Vietnam', 'aka-stores')                          => 'vn',
            __('Yemen', 'aka-stores')                            => 'ye',
            __('Zambia' ,'aka-stores')                           => 'zm',
            __('Zimbabwe', 'aka-stores')                         => 'zw'
            );
}

if ( !empty( $api_option_list ) && is_array( $api_option_list ) ) {
    $option_lists = '';

    foreach ($api_option_list as $option_api_key => $api_list_value) {
        $selected = ( $list_option_value == $api_list_value ) ? 'selected="selected"' : '';
        $option_lists .= '<option value="'.esc_attr( $api_list_value ).'" '.$selected.'>'.esc_html( $option_api_key ).'</option>';
    }
}
return $option_lists;
}


/**
* Return default admin form setting.
*/
function aka_stores_default_settings() {
    $default_setting = array(
        'server_key'                    => '',
        'browser_key'                   => '',
        'language'                      => 'en',
        'region'                        => '',
        'start_point'                   => '',
        'start_latlng'                  => '',
        'zoom_level'                    => 3,
        'max_zoom_level'                => 15,
        'direction_view_control'        => 0,
        'map_type_control'              => 0,
        'scrollwheel_zoom'              => 1,
        'map_type'                      => 'roadmap',
        'autocomplete'                  => 1,
        'distance_unit'                 => 'km',
        'max_results'                   => '[25],50,75,100',
        'radius_options'                => '10,25,[50],100,200,500',
        'post_type'                     => array(),
        'show_url_field'                => 0,
        'show_phone_field'              => 0,
        'show_description_field'        => 0,
        );

    $settings = get_option('aka_store_options');

    if ( empty( $settings ) ) {
        update_option( 'aka_store_options', $default_setting );
    }

    return $default_setting;
}


/**
* Deregister other Google Map
*/
function aka_stores_deregister_other_gmaps() {
    global $wp_scripts;
    if ( !empty( $wp_scripts->registered ) ) {
        foreach ( $wp_scripts->registered as $index => $script ) {
            if ( ( strpos( $script->src, 'font-awesome.min.css' ) !== false ) || ( strpos( $script->src, 'font-awesome.css' ) !== false ) && ( $script->handle !== 'aka-load-fa' ) ) {
                wp_deregister_script( $script->handle );
            }
        }
    }
}

/**
* Deregister other Font Awesome
*/
function aka_stores_deregister_other_font_awesome() {
    global $wp_scripts;
    if ( !empty( $wp_scripts->registered ) ) {
        foreach ( $wp_scripts->registered as $index => $script ) {
            if ( ( strpos( $script->src, 'maps.google.com' ) !== false ) || ( strpos( $script->src, 'maps.googleapis.com' ) !== false ) && ( $script->handle !== 'aka-gmap' ) ) {
                wp_deregister_script( $script->handle );
            }
        }
    }
}

/**
* Return default saved value
*/
function aka_stores_get_default_setting( $setting ) {
    global $aka_store_default_setting;
    return $aka_store_default_setting[$setting];
}

/**
 * Get the latlng for the provided address.
 */
function aka_stores_get_address_latlng( $address ) {
    $latlng   = '';
    $response = aka_stores_call_geocode_api( $address );
    if ( !is_wp_error( $response ) ) {
        $response = json_decode( $response['body'], true );
        if ( $response['status'] == 'OK' ) {
            $latlng = $response['results'][0]['geometry']['location']['lat'] . ',' . $response['results'][0]['geometry']['location']['lng'];
        }
    }
    return $latlng;
}


/**
 * @param string $address  The address to geocode.
 * @return array $response Either a WP_Error or the response from the Geocode API.
 */
function aka_stores_call_geocode_api( $address ) {
    $url      = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $address ) . aka_stores_gmap_api_params( 'server_key', true );
    $response = wp_remote_get( $url );
    return $response;
}

/**
* Max auto zoom level
* @param string $max_value from database
*/
function aka_stores_max_map_zoom_levels( $max_value ) {
    $max_zoom_levels = array();
    $zoom_level = array(
        'min' => 10,
        'max' => 21
        );

    $i = $zoom_level['min'];

    while ( $i <= $zoom_level['max'] ) {
        $max_zoom_levels[$i] = $i;
        $i++;
    }

    $dropdown = '<select id="max-zoom-level" name="aka_store_setting[max_zoom_level]" autocomplete="off">';

    foreach ( $max_zoom_levels as $key => $value ) {
        $selected = ( $max_value == $value ) ? 'selected="selected"' : '';
        $dropdown .= "<option value='" . esc_attr( $value ) . "' $selected>" . esc_html( $value ) . "</option>";
    }
    $dropdown .= '</select>';
    return $dropdown;
}


/**
 * Create a dropdown list holding the search radius or
 * max search results options.
 * @param $list_type either Search Results or Maximum no of result values
 */
function aka_stores_get_dropdown_list( $list_type ) {

    $aka_store_setting = get_option('aka_store_options');
    $dropdown_list = '';
    $settings      = explode( ',', $aka_store_setting[$list_type] );

    // Only show the distance unit if we are dealing with the search radius.
    if ( 'radius_options' == $list_type  ) {
        $distance_unit = ' '. esc_attr( $aka_store_setting['distance_unit'] );
    } else {
        $distance_unit = '';
    }

    foreach ( $settings as $index => $setting_value ) {

        // The default radius has a [] wrapped around it, so we check for that and filter out the [].
        if ( strpos( $setting_value, '[' ) !== false ) {
            $setting_value = filter_var( $setting_value, FILTER_SANITIZE_NUMBER_INT );
            $selected = 'selected="selected" ';
        } else {
            $selected = '';
        }

        $dropdown_list .= '<option ' . $selected . 'value="'. absint( $setting_value ) .'">'. absint( $setting_value ) . $distance_unit .'</option>';
    }

    return $dropdown_list;
}


/**
* @param $title String Title of locator
* @param $url String Url Added to the locator
* @param $show_url boolean
*/
function aka_stores_get_link_title( $title, $url, $show_url ) {
    $return_output = array();
    $return_output['before_wrap'] = '';
    $return_output['after_wrap'] = '';

    if ( !empty( $url ) && $show_url ) {
        $return_output['before_wrap'] = '<a href="'.$url.'" class="title-link" target="_blank">';
        $return_output['title'] = $title;
        $return_output['after_wrap'] = '</a>';
    } else {
        $return_output['before_wrap'] = '';
        $return_output['title'] = $title;
        $return_output['after_wrap'] = '';
    }
    return $return_output;
}
