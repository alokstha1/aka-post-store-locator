jQuery(document).ready( function() {

    jQuery('#aka-newmeta-submit').on( 'click', function(e) {
        e.preventDefault();

        var fields_count = jQuery('[name="aka_fields_count"]').val(),
        address_location = jQuery('[name="aka_location"]').val(),
        aka_name = jQuery('[name="aka_name"]').val(),
        aka_location = jQuery('[name="aka_location"]').val(),
        aka_url = jQuery('[name="aka_url"]').val(),
        aka_phone = jQuery('[name="aka_phone"]').val(),
        aka_description = jQuery('[name="aka_description"]').val();

        jQuery.ajax({
            type: 'POST',
            url: aka_stores.ajaxurl,
            data: {
                action: 'return_address_latlng',
                location: address_location
            },
            success: function( response ){

                if ( response.length > 0 ) {

                    var html_return = '<tr>';
                    html_return += '<td>';
                    html_return += '<span>'+aka_name+'</span><div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta['+fields_count+'][aka_name]" value="'+aka_name+'"></div>';
                    html_return += '</td>';
                    html_return += '<td>';
                    html_return += '<span>'+aka_location+'</span><div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta['+fields_count+'][aka_location]" value="'+aka_location+'"><input type="hidden" name="aka_store_meta['+fields_count+'][aka_location_latn]" value="'+response+'"></div>';
                    html_return += '</td>';
                    if ( aka_stores.aka_settings.show_url_field ) {
                    html_return += '<td>';
                        html_return += '<span>'+aka_url+'</span><div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta['+fields_count+'][aka_url]" value="'+aka_url+'"></div>';
                        html_return += '</td>';
                    }
                    if ( aka_stores.aka_settings.show_phone_field ) {
                        html_return += '<td>';
                        html_return += '<span>'+aka_phone+'</span><div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta['+fields_count+'][aka_phone]" value="'+aka_phone+'"></div>';
                        html_return += '</td>';
                    }
                    if ( aka_stores.aka_settings.show_description_field ) {
                        html_return += '<td>';
                        html_return += '<span>'+aka_description+'</span><div class="aka-input-wrap"><textarea class="hidden" name="aka_store_meta['+fields_count+'][aka_description]">'+aka_description+'</textarea></div>';
                        html_return += '</td>';
                    }
                    html_return += '<td class="aka-del-edit">';
                    html_return += '<a href="#" data-list="'+fields_count+'" class="aka-button-delete"></a></td>';
                    html_return += '</tr>';

                    jQuery('#aka-newmeta tbody.list-meta-body tr:last').before(html_return);
                    fields_count++;
                    jQuery('[name="aka_fields_count"]').val(fields_count);
                    jQuery('.aka-fields').val('');
                }

            }
        });
    });


    jQuery('a.aka-button-delete').live( 'click', function(e) {
        e.preventDefault();
        if ( confirm("Are you sure?") ) {
                jQuery(this).closest('tr').remove();
                var fields_count = jQuery('[name="aka_fields_count"]').val();
                jQuery('[name="aka_fields_count"]').val(--fields_count);
           }
    });

    // var input = jQuery('input.aka-location');
    // var input = document.getElementById('aka-location');
    // var autocomplete = new google.maps.places.Autocomplete(input);

    // Show the tooltips.
    jQuery( ".aka-info" ).on( "mouseover", function() {
        jQuery( this ).find( ".aka-info-text" ).css( 'display', 'block');
    });

    jQuery( ".aka-info" ).on( "mouseout", function() {
        jQuery( this ).find( ".aka-info-text" ).css( 'display', 'none');
    });

    jQuery('#add-selected-field').on( 'click', function(e) {
        e.preventDefault();

        var field_type = jQuery('select[name="field_type"]').val();

        var field_html = '';
        var custom_field_count = jQuery('[name="aka_store_setting[field_count]"]').val();

        if ( field_type == 'text' ) {

            field_html = return_field_html( field_type, custom_field_count );

        } else if ( field_type == 'textarea' ) {

            field_html = return_field_html( field_type, custom_field_count );

        } else if ( field_type == 'url' ) {

            field_html = return_field_html( field_type, custom_field_count );


        }

        jQuery('.field-added-response').append(field_html);

        jQuery(".field_settings").tabs();

    });

    jQuery('a.remove-fields').live( 'click', function(e) {
        e.preventDefault();

        jQuery(this).closest('span').remove();
        var count = jQuery('[name="aka_store_setting[field_count]"]').val();
        jQuery('[name="aka_store_setting[field_count]"]').val(--count);

    });


    // If we have a city/country input field enable the autocomplete.
    if ( jQuery( "#map-start-point" ).length > 0 ) {
        activateAutoComplete("map-start-point");
    }
    if ( jQuery( "#aka-location" ).length > 0 ) {
        activateAutoComplete("aka-location");
    }


});

function return_field_html( field_type, count) {

    var cnt = count;

    var field_html = '<span class="custom-field" id="custom-field-'+cnt+'">';
    field_html += '<div class="field_settings" >';
    field_html += '<ul>';
    field_html += '<li style="width:100px; padding:0px;">';
    field_html += '<a href="#gform_tab_1">General</a>';
    field_html += '</li>';
    field_html += '<li style="width:100px; padding:0px; ">';
    field_html += '<a href="#gform_tab_2">Appearance</a>';
    field_html += '</li>';
    field_html += '</ul>';
    field_html += '<div id="gform_tab_1">';
    field_html += '<input type="text" name="aka_store_setting[appended_field]['+cnt+'][name]" placeholder="Field Name">';
    field_html += '<input type="text" name="aka_store_setting[appended_field]['+cnt+'][label]" placeholder="Field Label">';
    field_html += '<input type="hidden" name="aka_store_setting[appended_field]['+cnt+'][type]" value="'+field_type+'">';
    field_html += '<a href="#" class="remove-fields" id="remove-'+cnt+'">Remove</a>';
    field_html += '</div>';
    field_html += '<div id="gform_tab_2">';
    field_html += 'Appearance section';
    field_html += '</div>';
    field_html += '</div>';
    // field_html += '</div>';
    field_html += '</span>';
    ++count;
    jQuery('[name="aka_store_setting[field_count]"]').val(count);

    return field_html;
}


/**
 * Activate the autocomplete function for the city/country field.
 */
function activateAutoComplete(address) {
    var latlng,
        input = document.getElementById( address ),
        options = {
          types: ['geocode']
        },
        autocomplete = new google.maps.places.Autocomplete( input, options );

    google.maps.event.addListener( autocomplete, "place_changed", function() {
        latlng = autocomplete.getPlace().geometry.location;
        setLatlng( latlng, "zoom" );
    });
}

/**
 * Update the hidden input field with the current latlng values.
 */
function setLatlng( latLng, target ) {
    var coordinates = stripCoordinates( latLng ),
        lat         = roundCoordinate( coordinates[0] ),
        lng         = roundCoordinate( coordinates[1] );

    if ( target == "store" ) {
        jQuery( "#aka-lat" ).val( lat );
        jQuery( "#aka-lng" ).val( lng );
    } else if ( target == "zoom" ) {
        jQuery( "#aka-latlng" ).val( lat + ',' + lng );
    }
}


/**
 * Strip the '(' and ')' from the captured coordinates and split them.
 */
function stripCoordinates( coordinates ) {
    var latLng    = [],
        selected  = coordinates.toString(),
        latLngStr = selected.split( ",", 2 );

    latLng[0] = latLngStr[0].replace( "(", "" );
    latLng[1] = latLngStr[1].replace( ")", "" );

    return latLng;
}

/**
 * Round the coordinate to 6 digits after the comma.
 */
function roundCoordinate( coordinate ) {
    var roundedCoord, decimals = 6;

    roundedCoord = Math.round( coordinate * Math.pow( 10, decimals ) ) / Math.pow( 10, decimals );

    return roundedCoord;
}