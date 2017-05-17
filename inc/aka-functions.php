<?php
/*
* Returns map api url based on languare and regions set.
*/
function aka_stores_gmap_api_params( $api_key_type, $geocode_params = false ) {
    global $aka_store_setting;
    $api_params = '';
    $param_keys = array( 'language', 'region', 'key' );

    /*
     * The geocode params are included after the address so we need to
     * use a '&' as the first char, but when the maps script is included on
     * the front-end it does need to start with a '?'.
     */
    $first_sep = ( $geocode_params ) ? '&' : '?';

    foreach ( $param_keys as $param_key ) {
        if ( $param_key == 'key' ) {
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

    if ( $aka_store_setting['autolocate_users'] && $api_key_type == 'browser_key' ) {
        $api_params .= '&libraries=places';
    }

    return apply_filters( 'aka_gmap_api_params', $api_params );
}

/*
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
            $zoom_desc = ' - ' . __( 'World view', 'aka_stores' );
            break;
            case 3:
            $zoom_desc = ' - ' . __( 'Default', 'aka_stores' );
            if ( !isset( $saved_zoom_level ) && empty( $saved_zoom_level ) ) {
                $selected = 'selected="selected"';
            }
            break;
            case 12:
            $zoom_desc = ' - ' . __( 'Roadmap', 'aka_stores' );
            break;
            default:
            $zoom_desc = '';
        }
        $select_dropdown .= "<option value='$i' $selected>". $i . esc_html( $zoom_desc ) . "</option>";
    }
    $select_dropdown .= "</select>";
    return $select_dropdown;
}

/*
* Return google map type dropdown.
*/
function aka_stores_map_type_options( $saved_map_type = '' ) {
    $map_types = array(
        'roadmap'   => __( 'Roadmap', 'aka_stores' ),
        'satellite' => __( 'Satellite', 'aka_stores' ),
        'hybrid'    => __( 'Hybrid', 'aka_stores' ),
        'terrain'   => __( 'Terrain', 'aka_stores' )
        );

    $select_dropdown = '<select name="aka_store_setting[map_type]" id="map-type">';
    foreach ( $map_types as $key => $map_type_value ) {
        $selected = ( $key == $saved_map_type ) ? 'selected="selected"' : '';
        $select_dropdown .= '<option value="' . esc_attr( $key ) . '" $selected>' . esc_html( $map_type_value ) . '</option>';
    }
    $select_dropdown .= '<select>';
    return $select_dropdown;
}


/*
* Return language and region setting dropdown for admin setting.
*/
function aka_stores_api_option_lists( $list, $list_option_value = '' ) {
    switch ( $list ) {
        case 'language':
        $api_option_list = array (
            __('Select your language', 'aka_stores')    => '',
            __('English', 'aka_stores')                 => 'en',
            __('Arabic', 'aka_stores')                  => 'ar',
            __('Basque', 'aka_stores')                  => 'eu',
            __('Bulgarian', 'aka_stores')               => 'bg',
            __('Bengali', 'aka_stores')                 => 'bn',
            __('Catalan', 'aka_stores')                 => 'ca',
            __('Czech', 'aka_stores')                   => 'cs',
            __('Danish', 'aka_stores')                  => 'da',
            __('German', 'aka_stores')                  => 'de',
            __('Greek', 'aka_stores')                   => 'el',
            __('English (Australian)', 'aka_stores')    => 'en-AU',
            __('English (Great Britain)', 'aka_stores') => 'en-GB',
            __('Spanish', 'aka_stores')                 => 'es',
            __('Farsi', 'aka_stores')                   => 'fa',
            __('Finnish', 'aka_stores')                 => 'fi',
            __('Filipino', 'aka_stores')                => 'fil',
            __('French', 'aka_stores')                  => 'fr',
            __('Galician', 'aka_stores')                => 'gl',
            __('Gujarati', 'aka_stores')                => 'gu',
            __('Hindi', 'aka_stores')                   => 'hi',
            __('Croatian', 'aka_stores')                => 'hr',
            __('Hungarian', 'aka_stores')               => 'hu',
            __('Indonesian', 'aka_stores')              => 'id',
            __('Italian', 'aka_stores')                 => 'it',
            __('Hebrew', 'aka_stores')                  => 'iw',
            __('Japanese', 'aka_stores')                => 'ja',
            __('Kannada', 'aka_stores')                 => 'kn',
            __('Korean', 'aka_stores')                  => 'ko',
            __('Lithuanian', 'aka_stores')              => 'lt',
            __('Latvian', 'aka_stores')                 => 'lv',
            __('Malayalam', 'aka_stores')               => 'ml',
            __('Marathi', 'aka_stores')                 => 'mr',
            __('Dutch', 'aka_stores')                   => 'nl',
            __('Norwegian', 'aka_stores')               => 'no',
            __('Norwegian Nynorsk', 'aka_stores')       => 'nn',
            __('Polish', 'aka_stores')                  => 'pl',
            __('Portuguese', 'aka_stores')              => 'pt',
            __('Portuguese (Brazil)', 'aka_stores')     => 'pt-BR',
            __('Portuguese (Portugal)', 'aka_stores')   => 'pt-PT',
            __('Romanian', 'aka_stores')                => 'ro',
            __('Russian', 'aka_stores')                 => 'ru',
            __('Slovak', 'aka_stores')                  => 'sk',
            __('Slovenian', 'aka_stores')               => 'sl',
            __('Serbian', 'aka_stores')                 => 'sr',
            __('Swedish', 'aka_stores')                 => 'sv',
            __('Tagalog', 'aka_stores')                 => 'tl',
            __('Tamil', 'aka_stores')                   => 'ta',
            __('Telugu', 'aka_stores')                  => 'te',
            __('Thai', 'aka_stores')                    => 'th',
            __('Turkish', 'aka_stores')                 => 'tr',
            __('Ukrainian', 'aka_stores')               => 'uk',
            __('Vietnamese', 'aka_stores')              => 'vi',
            __('Chinese (Simplified)', 'aka_stores')    => 'zh-CN',
            __('Chinese (Traditional)' ,'aka_stores')   => 'zh-TW'
            );
        break;
        case 'region':
        $api_option_list = array (
            __('Select your region', 'aka_stores')               => '',
            __('Afghanistan', 'aka_stores')                      => 'af',
            __('Albania', 'aka_stores')                          => 'al',
            __('Algeria', 'aka_stores')                          => 'dz',
            __('American Samoa', 'aka_stores')                   => 'as',
            __('Andorra', 'aka_stores')                          => 'ad',
            __('Anguilla', 'aka_stores')                         => 'ai',
            __('Angola', 'aka_stores')                           => 'ao',
            __('Antigua and Barbuda', 'aka_stores')              => 'ag',
            __('Argentina', 'aka_stores')                        => 'ar',
            __('Armenia', 'aka_stores')                          => 'am',
            __('Aruba', 'aka_stores')                            => 'aw',
            __('Australia', 'aka_stores')                        => 'au',
            __('Austria', 'aka_stores')                          => 'at',
            __('Azerbaijan', 'aka_stores')                       => 'az',
            __('Bahamas', 'aka_stores')                          => 'bs',
            __('Bahrain', 'aka_stores')                          => 'bh',
            __('Bangladesh', 'aka_stores')                       => 'bd',
            __('Barbados', 'aka_stores')                         => 'bb',
            __('Belarus', 'aka_stores')                          => 'by',
            __('Belgium', 'aka_stores')                          => 'be',
            __('Belize', 'aka_stores')                           => 'bz',
            __('Benin', 'aka_stores')                            => 'bj',
            __('Bermuda', 'aka_stores')                          => 'bm',
            __('Bhutan', 'aka_stores')                           => 'bt',
            __('Bolivia', 'aka_stores')                          => 'bo',
            __('Bosnia and Herzegovina', 'aka_stores')           => 'ba',
            __('Botswana', 'aka_stores')                         => 'bw',
            __('Brazil', 'aka_stores')                           => 'br',
            __('British Indian Ocean Territory', 'aka_stores')   => 'io',
            __('Brunei', 'aka_stores')                           => 'bn',
            __('Bulgaria', 'aka_stores')                         => 'bg',
            __('Burkina Faso', 'aka_stores')                     => 'bf',
            __('Burundi', 'aka_stores')                          => 'bi',
            __('Cambodia', 'aka_stores')                         => 'kh',
            __('Cameroon', 'aka_stores')                         => 'cm',
            __('Canada', 'aka_stores')                           => 'ca',
            __('Cape Verde', 'aka_stores')                       => 'cv',
            __('Cayman Islands', 'aka_stores')                   => 'ky',
            __('Central African Republic', 'aka_stores')         => 'cf',
            __('Chad', 'aka_stores')                             => 'td',
            __('Chile', 'aka_stores')                            => 'cl',
            __('China', 'aka_stores')                            => 'cn',
            __('Christmas Island', 'aka_stores')                 => 'cx',
            __('Cocos Islands', 'aka_stores')                    => 'cc',
            __('Colombia', 'aka_stores')                         => 'co',
            __('Comoros', 'aka_stores')                          => 'km',
            __('Congo', 'aka_stores')                            => 'cg',
            __('Costa Rica', 'aka_stores')                       => 'cr',
            __('Côte d\'Ivoire', 'aka_stores')                   => 'ci',
            __('Croatia', 'aka_stores')                          => 'hr',
            __('Cuba', 'aka_stores')                             => 'cu',
            __('Czech Republic', 'aka_stores')                   => 'cz',
            __('Denmark', 'aka_stores')                          => 'dk',
            __('Djibouti', 'aka_stores')                         => 'dj',
            __('Democratic Republic of the Congo', 'aka_stores') => 'cd',
            __('Dominica', 'aka_stores')                         => 'dm',
            __('Dominican Republic', 'aka_stores')               => 'do',
            __('Ecuador', 'aka_stores')                          => 'ec',
            __('Egypt', 'aka_stores')                            => 'eg',
            __('El Salvador', 'aka_stores')                      => 'sv',
            __('Equatorial Guinea', 'aka_stores')                => 'gq',
            __('Eritrea', 'aka_stores')                          => 'er',
            __('Estonia', 'aka_stores')                          => 'ee',
            __('Ethiopia', 'aka_stores')                         => 'et',
            __('Fiji', 'aka_stores')                             => 'fj',
            __('Finland', 'aka_stores')                          => 'fi',
            __('France', 'aka_stores')                           => 'fr',
            __('French Guiana', 'aka_stores')                    => 'gf',
            __('Gabon', 'aka_stores')                            => 'ga',
            __('Gambia', 'aka_stores')                           => 'gm',
            __('Germany', 'aka_stores')                          => 'de',
            __('Ghana', 'aka_stores')                            => 'gh',
            __('Greenland', 'aka_stores')                        => 'gl',
            __('Greece', 'aka_stores')                           => 'gr',
            __('Grenada', 'aka_stores')                          => 'gd',
            __('Guam', 'aka_stores')                             => 'gu',
            __('Guadeloupe', 'aka_stores')                       => 'gp',
            __('Guatemala', 'aka_stores')                        => 'gt',
            __('Guinea', 'aka_stores')                           => 'gn',
            __('Guinea-Bissau', 'aka_stores')                    => 'gw',
            __('Haiti', 'aka_stores')                            => 'ht',
            __('Honduras', 'aka_stores')                         => 'hn',
            __('Hong Kong', 'aka_stores')                        => 'hk',
            __('Hungary', 'aka_stores')                          => 'hu',
            __('Iceland', 'aka_stores')                          => 'is',
            __('India', 'aka_stores')                            => 'in',
            __('Indonesia', 'aka_stores')                        => 'id',
            __('Iran', 'aka_stores')                             => 'ir',
            __('Iraq', 'aka_stores')                             => 'iq',
            __('Ireland', 'aka_stores')                          => 'ie',
            __('Israel', 'aka_stores')                           => 'il',
            __('Italy', 'aka_stores')                            => 'it',
            __('Jamaica', 'aka_stores')                          => 'jm',
            __('Japan', 'aka_stores')                            => 'jp',
            __('Jordan', 'aka_stores')                           => 'jo',
            __('Kazakhstan', 'aka_stores')                       => 'kz',
            __('Kenya', 'aka_stores')                            => 'ke',
            __('Kuwait', 'aka_stores')                           => 'kw',
            __('Kyrgyzstan', 'aka_stores')                       => 'kg',
            __('Laos', 'aka_stores')                             => 'la',
            __('Latvia', 'aka_stores')                           => 'lv',
            __('Lebanon', 'aka_stores')                          => 'lb',
            __('Lesotho', 'aka_stores')                          => 'ls',
            __('Liberia', 'aka_stores')                          => 'lr',
            __('Libya', 'aka_stores')                            => 'ly',
            __('Liechtenstein', 'aka_stores')                    => 'li',
            __('Lithuania', 'aka_stores')                        => 'lt',
            __('Luxembourg', 'aka_stores')                       => 'lu',
            __('Macau', 'aka_stores')                            => 'mo',
            __('Macedonia', 'aka_stores')                        => 'mk',
            __('Madagascar', 'aka_stores')                       => 'mg',
            __('Malawi', 'aka_stores')                           => 'mw',
            __('Malaysia ', 'aka_stores')                        => 'my',
            __('Mali', 'aka_stores')                             => 'ml',
            __('Marshall Islands', 'aka_stores')                 => 'mh',
            __('Martinique', 'aka_stores')                       => 'il',
            __('Mauritania', 'aka_stores')                       => 'mr',
            __('Mauritius', 'aka_stores')                        => 'mu',
            __('Mexico', 'aka_stores')                           => 'mx',
            __('Micronesia', 'aka_stores')                       => 'fm',
            __('Moldova', 'aka_stores')                          => 'md',
            __('Monaco' ,'aka_stores')                           => 'mc',
            __('Mongolia', 'aka_stores')                         => 'mn',
            __('Montenegro', 'aka_stores')                       => 'me',
            __('Montserrat', 'aka_stores')                       => 'ms',
            __('Morocco', 'aka_stores')                          => 'ma',
            __('Mozambique', 'aka_stores')                       => 'mz',
            __('Myanmar', 'aka_stores')                          => 'mm',
            __('Namibia', 'aka_stores')                          => 'na',
            __('Nauru', 'aka_stores')                            => 'nr',
            __('Nepal', 'aka_stores')                            => 'np',
            __('Netherlands', 'aka_stores')                      => 'nl',
            __('Netherlands Antilles', 'aka_stores')             => 'an',
            __('New Zealand', 'aka_stores')                      => 'nz',
            __('Nicaragua', 'aka_stores')                        => 'ni',
            __('Niger', 'aka_stores')                            => 'ne',
            __('Nigeria', 'aka_stores')                          => 'ng',
            __('Niue', 'aka_stores')                             => 'nu',
            __('Northern Mariana Islands', 'aka_stores')         => 'mp',
            __('Norway', 'aka_stores')                           => 'no',
            __('Oman', 'aka_stores')                             => 'om',
            __('Pakistan', 'aka_stores')                         => 'pk',
            __('Panama' ,'aka_stores')                           => 'pa',
            __('Papua New Guinea', 'aka_stores')                 => 'pg',
            __('Paraguay' ,'aka_stores')                         => 'py',
            __('Peru', 'aka_stores')                             => 'pe',
            __('Philippines', 'aka_stores')                      => 'ph',
            __('Pitcairn Islands', 'aka_stores')                 => 'pn',
            __('Poland', 'aka_stores')                           => 'pl',
            __('Portugal', 'aka_stores')                         => 'pt',
            __('Qatar', 'aka_stores')                            => 'qa',
            __('Reunion', 'aka_stores')                          => 're',
            __('Romania', 'aka_stores')                          => 'ro',
            __('Russia', 'aka_stores')                           => 'ru',
            __('Rwanda', 'aka_stores')                           => 'rw',
            __('Saint Helena', 'aka_stores')                     => 'sh',
            __('Saint Kitts and Nevis', 'aka_stores')            => 'kn',
            __('Saint Vincent and the Grenadines', 'aka_stores') => 'vc',
            __('Saint Lucia', 'aka_stores')                      => 'lc',
            __('Samoa', 'aka_stores')                            => 'ws',
            __('San Marino', 'aka_stores')                       => 'sm',
            __('São Tomé and Príncipe', 'aka_stores')            => 'st',
            __('Saudi Arabia', 'aka_stores')                     => 'sa',
            __('Senegal', 'aka_stores')                          => 'sn',
            __('Serbia', 'aka_stores')                           => 'rs',
            __('Seychelles', 'aka_stores')                       => 'sc',
            __('Sierra Leone', 'aka_stores')                     => 'sl',
            __('Singapore', 'aka_stores')                        => 'sg',
            __('Slovakia', 'aka_stores')                         => 'si',
            __('Solomon Islands', 'aka_stores')                  => 'sb',
            __('Somalia', 'aka_stores')                          => 'so',
            __('South Africa', 'aka_stores')                     => 'za',
            __('South Korea', 'aka_stores')                      => 'kr',
            __('Spain', 'aka_stores')                            => 'es',
            __('Sri Lanka', 'aka_stores')                        => 'lk',
            __('Sudan', 'aka_stores')                            => 'sd',
            __('Swaziland', 'aka_stores')                        => 'sz',
            __('Sweden', 'aka_stores')                           => 'se',
            __('Switzerland', 'aka_stores')                      => 'ch',
            __('Syria', 'aka_stores')                            => 'sy',
            __('Taiwan', 'aka_stores')                           => 'tw',
            __('Tajikistan', 'aka_stores')                       => 'tj',
            __('Tanzania', 'aka_stores')                         => 'tz',
            __('Thailand', 'aka_stores')                         => 'th',
            __('Timor-Leste', 'aka_stores')                      => 'tl',
            __('Tokelau' ,'aka_stores')                          => 'tk',
            __('Togo', 'aka_stores')                             => 'tg',
            __('Tonga', 'aka_stores')                            => 'to',
            __('Trinidad and Tobago', 'aka_stores')              => 'tt',
            __('Tunisia', 'aka_stores')                          => 'tn',
            __('Turkey', 'aka_stores')                           => 'tr',
            __('Turkmenistan', 'aka_stores')                     => 'tm',
            __('Tuvalu', 'aka_stores')                           => 'tv',
            __('Uganda', 'aka_stores')                           => 'ug',
            __('Ukraine', 'aka_stores')                          => 'ua',
            __('United Arab Emirates', 'aka_stores')             => 'ae',
            __('United Kingdom', 'aka_stores')                   => 'gb',
            __('United States', 'aka_stores')                    => 'us',
            __('Uruguay', 'aka_stores')                          => 'uy',
            __('Uzbekistan', 'aka_stores')                       => 'uz',
            __('Wallis Futuna', 'aka_stores')                    => 'wf',
            __('Venezuela', 'aka_stores')                        => 've',
            __('Vietnam', 'aka_stores')                          => 'vn',
            __('Yemen', 'aka_stores')                            => 'ye',
            __('Zambia' ,'aka_stores')                           => 'zm',
            __('Zimbabwe', 'aka_stores')                         => 'zw'
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


/*
* Return default admin form setting.
*/
function aka_stores_default_settings() {
    $default_setting = array(
        'server_key'                    => '',
        'browser_key'                   => '',
        'language'                      => 'en',
        'region'                        => '',
        'autolocate_users'              => 1,
        'no_of_locations'               => '50',
        'start_point'                   => '',
        'start_latlng'                  => '',
        'zoom_level'                    => 3,
        'max_zoom_level'                => 15,
        'direction_view_control'        => 0,
        'map_type_control'              => 0,
        'scrollwheel_zoom'              => 1,
        'map_type'                      => 'roadmap',
        'autocomplete'                  => 0,
        'radius_dropdown'               => 1,
        'max_results_dropdown'          => 0,
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


/*
* Deregister other Google Map
*/
function aka_stores_deregister_other_gmaps() {
    global $wp_scripts;
    if ( !empty( $wp_scripts->registered ) ) {
        foreach ( $wp_scripts->registered as $index => $script ) {
            if ( ( strpos( $script->src, 'maps.google.com' ) !== false ) || ( strpos( $script->src, 'maps.googleapis.com' ) !== false ) && ( $script->handle !== 'aka-gmap' ) ) {
                wp_deregister_script( $script->handle );
            }
        }
    }
}

/*
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

/*
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
    global $aka_store_setting;
    $dropdown_list = '';
    $settings      = explode( ',', $aka_store_setting[$list_type] );

    // Only show the distance unit if we are dealing with the search radius.
    if ( $list_type == 'radius_options' ) {
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


/*
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


/*
* Debug
*/
function pre_debug( $value, $die = false ) {

    echo "<pre>";
    print_r( $value );
    echo "</pre>";
    if ($die) {
        die();
    }
}
