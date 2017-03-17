jQuery(document).ready( function() {

    //not needed: var resetMap,

    var map, geocoder, displayDirection, serviceDirections, startMarkerData, infoWindow, autoCompleteLatLng,
    openInfoWindow = [],
    markersArray = [],
    markerSettings = {},
    directionMarkerPosition = {};

    if ( jQuery('#aka-map').length ) {

        initMap();
    }

    //Return initialized map
    function initMap() {

        var mapOptions, infoWindow, latLng, bounds, startLatLng,
        maxZoom = Number( aka_stores.aka_settings.max_zoom_level );

        //Create an infowindow
        infoWindow = new google.maps.InfoWindow();
        startLatLng = getStartLatlng();

        geocoder          = new google.maps.Geocoder();
        displayDirection = new google.maps.DirectionsRenderer();
        serviceDirections = new google.maps.DirectionsService();


        // Set map options.
        mapOptions = {
            zoom: Number( aka_stores.aka_settings.zoom_level ),
            center: startLatLng,
            mapTypeId: google.maps.MapTypeId[ aka_stores.aka_settings.map_type.toUpperCase() ],
            mapTypeControl: Number( aka_stores.aka_settings.map_type_control ) ? true : false,
            scrollwheel: Number( aka_stores.aka_settings.scrollwheel_zoom ) ? true : false,

        };

        map = new google.maps.Map(document.getElementById('aka-map'), mapOptions );

        // Only run this part if the store locator exist and we don't just have a basic map.
        if ( jQuery( "#aka-map" ).length ) {

            if ( aka_stores.aka_settings.autocomplete == 1 ) {
                initAutocomplete();
            }
        }

        bounds        = new google.maps.LatLngBounds();
        addMarker( startLatLng, '', false, infoWindow );
        bounds.extend( startLatLng );

        //loop over the store items and add map marker
        if ( jQuery('ul.store-ul-lists li').length > 0 ) {

            jQuery('ul.store-ul-lists li').each( function(index){

                var item_latlng = jQuery(this).data('latlng').split( "," );
                latLng = new google.maps.LatLng( item_latlng[0], item_latlng[1] );
                addMarker( latLng, index, false, infoWindow );
                bounds.extend( latLng );
            });


            // Make sure we don't zoom to far.
            google.maps.event.addListenerOnce( map, "bounds_changed", ( function( currentMap ) {
                return function() {
                    if ( currentMap.getZoom() > maxZoom ) {
                        currentMap.setZoom( maxZoom );
                    }
                };
            }( map ) ) );
            // Make all the markers fit on the map.
            fitBounds();
        }

        //initialize form store search button
        initialize_store_search( infoWindow );

        //initialize render direction event
        render_direction();


    }

    function initAutocomplete() {

        var input, autocomplete, place,
        options = {};

            // Check if we need to set the geocode component restrictions.
            if ( typeof aka_stores.aka_settings.region !== "undefined" && aka_stores.aka_settings.region.length > 0 ) {
                var regionComponents = {};
                regionComponents.country = aka_stores.aka_settings.region.toUpperCase();

                options.componentRestrictions = regionComponents;

            }

            input        = document.getElementById( "aka-search-input" );
            autocomplete = new google.maps.places.Autocomplete( input, options );

            autocomplete.addListener( "place_changed", function() {
                place = autocomplete.getPlace();

                /*
                 * Assign the returned latlng to the autoCompleteLatLng var.
                 * This var is used when the users submits the search.
                 */

                 if ( place.geometry ) {
                    autoCompleteLatLng = place.geometry.location;

                }
            });

        }


        /**
         * Return the latlng coordinates that are used to init the map.
         */
         function getStartLatlng() {
            var startLatLng, latLng;

            /*
             * Use coordinates from the default start point defined or we set it to 0,0
             */

             if ( aka_stores.aka_settings.start_latlng !== "" ) {
                latLng      = aka_stores.aka_settings.start_latlng.split( "," );
                startLatLng = new google.maps.LatLng( latLng[0], latLng[1] );
            } else {
                startLatLng = new google.maps.LatLng( 0,0 );
            }

            return startLatLng;
        }


        /**
         * Add a new marker to the map based on the provided location (latlng).
         *
         * @since  1.0.0
         * @param  {object}  latLng         The coordinates
         * @param  {number}  storeId        The store id
         * @param  {boolean} draggable      Should the marker be draggable
         * @param  {object}  infoWindow     The infoWindow object
         * @return {void}
         */
         function addMarker( latLng, storeId, draggable, infoWindow ) {

            var url, mapIcon, marker,
            keepStartMarker = true;

            if ( storeId === '' ) {

                url = aka_stores.marker_dir_url + 'red.png';
                mapIcon = {
                    url: url,
                    scaledSize: new google.maps.Size( 24, 36 ), //retina format
                    origin: new google.maps.Point( 0, 0 ),
                    anchor: new google.maps.Point( 0, 32 )
                };
            } else {
                url = aka_stores.marker_dir_url + 'blue.png';
                mapIcon = {
                    url: url,
                    scaledSize: new google.maps.Size( 20, 32 ), //retina format
                    origin: new google.maps.Point( 0, 0 ),
                    anchor: new google.maps.Point( 0, 32 )
                };
            }



            marker = new google.maps.Marker({
                position: latLng,
                map: map,
                optimized: false, //fixes markers flashing while bouncing
                title: 'Test Title',//decodeHtmlEntity( infoWindowData.store ),
                draggable: draggable,
                storeId: storeId,
                icon: mapIcon
            });

            if ( storeId === '') {
                startMarkerData = marker;
                console.log(startMarkerData);
            }

            // Store the marker for later use.
            markersArray.push( marker );

            google.maps.event.addListener( marker, "click",( function( currentMap ) {
                return function() {

                    if ( storeId !== '') {
                        setInfoWindowContent( storeId, marker, infoWindow, currentMap );
                        openInfoWindow.push( infoWindow );
                    }


                    google.maps.event.clearListeners( infoWindow, "domready" );

                    // google.maps.event.addListener( infoWindow, "domready", function() {
                    //     infoWindowClickActions( marker, currentMap );
                    //     checkMaxZoomLevel();
                    // });
                };
            }( map ) ) );


            //marker bounce animation
            toggleMarkerAnimation();

        }


        /**
         * Set the correct info window content for the marker.
         *
         * @since   1.2.20
         * @param   {integer} storeId          Store Id
         * @param   {object} marker            Marker data
         * @param   {object} infoWindow        The infoWindow object
         * @param   {object} currentMap        The map object
         * @returns {void}
         */
         function setInfoWindowContent( storeId, marker, infoWindow, currentMap ) {
            var infoWindowContent = '', storeName, storeUrl, storeLatLng, storePhone, storeAddress, url = '';
            storeName = jQuery('#store-item-id-'+storeId).data('storename');
            storeUrl = jQuery('#store-item-id-'+storeId).data('storeurl');
            // storeLatLng = jQuery('#store-item-id-'+storeId).data('storelatlng');
            storePhone = jQuery('#store-item-id-'+storeId).data('phone');
            storeAddress = jQuery('#store-item-id-'+storeId).data('address');

            if ( typeof storeUrl !== 'undefined') {
                url = storeUrl;
            }

            // aka_stores.aka_settings

            var title_url_wrap = setTitleUrl(storeName, url, aka_stores.aka_settings.show_url_field);


            infoWindowContent += '<div class="aka-info-wrap">';
            infoWindowContent += '<span class="aka-title"><label>Title:</label>';
            infoWindowContent += title_url_wrap.before_wrap;
            infoWindowContent += title_url_wrap.title;
            infoWindowContent += title_url_wrap.after_wrap;
            infoWindowContent += '</span>';
            infoWindowContent += '<span class="aka-address"><label>Address:</label>';
            infoWindowContent += storeAddress;
            infoWindowContent += '</span>';
            infoWindowContent += '<span class="aka-phone"><label>Phone No:</label>';
            infoWindowContent += storePhone;
            infoWindowContent += '</span>';
            infoWindowContent += '</div>';
            openInfoWindow.length = 0;

            infoWindow.setContent( infoWindowContent );
            infoWindow.open( currentMap, marker );

            openInfoWindow.push( infoWindow );
        }

        /**
        * Set the url for the title in infowindow
        *
        *
        */
        function setTitleUrl(title, url, show_url) {

            var return_output = {};
            return_output.before_wrap = '';
            return_output.after_wrap = '';

            if ( typeof url != 'undefined' && url != '' && show_url ) {

                return_output.before_wrap = '<a href="'+url+'" class="title-link" target="_blank">';
                return_output.title = title;
                return_output.after_wrap = '</a>';
            } else {
                return_output.before_wrap = '';
                return_output.title = title;
                return_output.after_wrap = '';
            }
            return return_output;
        }

        /**
        * Set form element to search stores
        */
        function initialize_store_search( infoWindow ) {

            jQuery( "#aka-search-btn" ).unbind( "click" ).bind( "click", function( e ) {
                e.preventDefault();

                var keepStartMarker = false;
                resetMap = false;

                // Force the open InfoBox info window to close.
                closeInfoBoxWindow();

                deleteOverlays( keepStartMarker );
                deleteStartMarker();

                /*
                 * Check if we need to geocode the user input,
                 * or if autocomplete is enabled and we already
                 * have the latlng values.
                 */
                 if ( aka_stores.aka_settings.autocomplete == 1 && typeof autoCompleteLatLng !== "undefined" ) {

                    prepareStoreSearch( autoCompleteLatLng, infoWindow );
                } else {
                    codeAddress( infoWindow );

                }

                return false;
            });

        }


        /**
         * Force the open InfoBox info window to close
         *
         * This is required if the user makes a new search.
         *
         */
         function closeInfoBoxWindow() {
            if ( typeof openInfoWindow[0] !== "undefined" ) {
                openInfoWindow[0].close();
            }
        }

        /**
         * Remove all existing markers from the map.
         *
         * @since   1.0.0
         * @param   {boolean} keepStartMarker Whether or not to keep the start marker while removing all the other markers from the map
         * @returns {void}
         */
         function deleteOverlays( keepStartMarker ) {
            var markerLen, i;

            displayDirection.setMap( null );

            // Remove all the markers from the map, and empty the array.
            if ( markersArray ) {
                for ( i = 0, markerLen = markersArray.length; i < markerLen; i++ ) {

                    // Check if we need to keep the start marker, or remove everything.
                    if ( keepStartMarker ) {
                        if ( markersArray[i].draggable != true ) {
                            markersArray[i].setMap( null );
                        } else {
                            startMarkerData = markersArray[i];
                        }
                    } else {
                        markersArray[i].setMap( null );
                    }
                }

                markersArray.length = 0;
            }

            // If marker clusters exist, remove them from the map.
            // if ( markerClusterer ) {
            //     markerClusterer.clearMarkers();
            // }
        }


        /**
         * Remove the start marker from the map.
         *
         * @since   1.2.12
         * @returns {void}
         */
         function deleteStartMarker() {
            if ( ( typeof( startMarkerData ) !== "undefined" ) && ( startMarkerData !== "" ) ) {
                startMarkerData.setMap( null );
                startMarkerData = "";
            }
        }

        /**
         * Geocode the user input.
         *
         * @since   1.0.0
         * @param   {object} infoWindow The infoWindow object
         * @returns {void}
         */
         function codeAddress( infoWindow ) {

            var request = {
                'address': jQuery( "#aka-search-input" ).val()
            };
            var latLng;

            // Check if we need to set the geocode component restrictions.
            if ( typeof aka_stores.aka_settings.region !== "undefined" && aka_stores.aka_settings.region.length > 0 ) {
                var regionComponents = {};
                regionComponents.country = aka_stores.aka_settings.region.toUpperCase();

                request.componentRestrictions = regionComponents;

            }

            geocoder.geocode( request, function( response, status ) {
                if ( status == google.maps.GeocoderStatus.OK ) {
                    latLng = response[0].geometry.location;

                    prepareStoreSearch( latLng, infoWindow );
                } else {
                    geocodeErrors( status );
                }
            });
        }


        /**
         * Prepare a new location search.
         * @param   {object} latLng
         * @param   {object} infoWindow The infoWindow object.
         * @returns {void}
         */
         function prepareStoreSearch( prepare_latLng, infoWindow ) {
            var autoLoad = false;

            // Add a new start marker.
            addMarker( prepare_latLng, '', true, infoWindow );

            // Try to find stores that match the radius, location criteria.
            makeAjaxRequest( prepare_latLng, resetMap, autoLoad, infoWindow );
        }


        /**
         * Make the AJAX request to load the store data.
         *
         * @since   1.2.0
         * @param   {object}  startLatLng The latlng used as the starting point
         * @param   {boolean} resetMap    Whether we should reset the map or not
         * @param   {string}  autoLoad    Check if we need to autoload all the stores
         * @param   {object}  infoWindow  The infoWindow object
         * @returns {void}
         */
         function makeAjaxRequest( startLatLng, resetMap, autoLoad, infoWindow ) {

            var post_id = jQuery('#aka_post_id').val();
            var storeList = jQuery("#aka-store-lists");
            var search_radius = jQuery('#aka-radius-dropdown').val();
            var stores_count = jQuery('#aka-results-dropdown').val();

            var ajaxData = {
                action: "aka_store_search",
                lat: startLatLng.lat(),
                lng: startLatLng.lng(),
                post_id: post_id,
                search_radius: search_radius,
                stores_count: stores_count
            };

            var maxZoom = Number( aka_stores.aka_settings.max_zoom_level );

            jQuery(storeList).empty();

            var result_html = '';

            jQuery.ajax({
                data: ajaxData,
                type: 'POST',
                url: aka_stores.ajaxurl,
                success: function( response ){
                    jQuery.each( response, function( index, value ){

                        var serial_no = index;
                        serial_no = ++serial_no;

                        var title_url_wrap = setTitleUrl(value.aka_name, value.aka_url, aka_stores.aka_settings.show_url_field);

                        result_html += '<li class="store-items" id="store-item-id-'+index+'" data-storeid="'+index+'" data-storename="'+value.aka_name+'" data-storeurl="'+value.aka_url+'" data-latlng="'+value.aka_location_latn+'" data-phone="'+value.aka_phone+'" data-address="'+value.aka_location+'">';
                        result_html += '<div class="map-content">';
                        result_html += '<span class="store-key">'+serial_no+'</span>';
                        result_html += '<span class="store-title">';
                        result_html += title_url_wrap.before_wrap;
                        result_html += title_url_wrap.title;
                        result_html += title_url_wrap.after_wrap;
                        result_html += '</span>';
                        if ( aka_stores.aka_settings.show_phone_field ) {

                            result_html += '<span class="store-phone">'+value.aka_phone+'</span>';
                        }
                        result_html += '<span class="store-address">'+value.aka_location;
                        result_html += '</span>';
                        if ( aka_stores.aka_settings.show_description_field ) {

                            result_html += '<p>'+value.aka_description+'</p>';
                        }
                        if ( aka_stores.aka_settings.direction_view_control ) {
                            result_html += '<span class="store-items"><a class="aka-get-direction" href="#" id="get-direction-'+index+'">Direction</a></span>';
                        }
                        result_html += '</div>';
                        result_html += '</li>';


                        var item_latlng = value.aka_location_latn.split( "," );
                        response_latLng = new google.maps.LatLng( item_latlng[0], item_latlng[1] );

                        addMarker( response_latLng, index, false, infoWindow );

                    });


                    //Append items to lists.
                    storeList.html(result_html);

                    // Make sure we don't zoom to far.
                    fitBounds();

                    render_direction();
                }
            });
        }


         /**
          * Handle the geocode errors.
          * @param   {string} status Contains the error code
          * @returns {void}
          */
          function geocodeErrors( status ) {
           var msg;

           switch ( status ) {
            case "ZERO_RESULTS":
            msg = 'No results found';
            break;
            case "OVER_QUERY_LIMIT":
            msg = 'API usage limit reached';
            break;
            default:
            msg = 'Something went wrong, please try again!';
            break;
        }

        alert( msg );
    }


         /**
          * Zoom the map so that all markers fit in the window.
          *
          * @since  1.0.0
          * @returns {void}
          */
          function fitBounds() {

           var i, markerLen,
           maxZoom = Number( aka_stores.aka_settings.max_zoom_level ),
           bounds  = new google.maps.LatLngBounds();

             // Make sure we don't zoom to far.
             google.maps.event.addListenerOnce( map, "bounds_changed", function( event ) {
                if ( this.getZoom() > maxZoom ) {
                    this.setZoom( maxZoom );
                }
            });

             for ( i = 0, markerLen = markersArray.length; i < markerLen; i++ ) {
                bounds.extend ( markersArray[i].position );
            }

            map.fitBounds( bounds );
        }


         /**
         *  Trigger to render driving directions.
         *
         */
         function render_direction() {

            jQuery( "#aka-store-lists" ).on( "click", ".aka-get-direction", function() {

                // Check if we need to render the direction on the map.
                if ( aka_stores.aka_settings.direction_view_control == 1 ) {
                    renderDirections( jQuery( this ) );

                    return false;
                }
            });

        }


         /**
          * Show the driving directions.
          *
          * @since  1.1.0
          * @param  {object} e The clicked elemennt
          * @returns {void}
          */
          function renderDirections( e ) {
           var i, start, end, len, store_Id;

            // Force the open InfoBox info window to close.
            closeInfoBoxWindow();

             /*
              * The storeId is placed on the li in the results list,
              * but in the marker it will be on the wrapper div. So we check which one we need to target.
              */
              if ( e.parents( "li" ).length > 0 ) {
                store_Id = e.parent().parent().closest( "li" ).data( "storeid" );
             } /*else {
                storeId = e.parents( ".wpsl-info-window" ).data( "store-id" );
            }*/

            // Check if we need to get the start point from a dragged marker.
            if ( ( typeof( startMarkerData ) !== "undefined" )  && ( startMarkerData !== "" ) ) {
                start = startMarkerData.getPosition();
            }

            // Used to restore the map back to the state it was in before the user clicked on 'directions'.
            directionMarkerPosition = {
                centerLatlng: map.getCenter(),
                zoomLevel: map.getZoom()
            };

             // Find the latlng that belongs to the start and end point.
             for ( i = 0, len = markersArray.length; i < len; i++ ) {
               // console.log(markersArray[i].getPosition().lat());

                // Only continue if the start data is still empty or undefined.
                /*if ( markersArray[i].storeId === '' ) {

                    start = markersArray[i].getPosition();
                } else */if ( markersArray[i].storeId === store_Id ) {
                    end = markersArray[i].getPosition();
                }
            }
            console.log(start);
            console.log(end);

            if ( start && end ) {
                jQuery( "#aka-direction-details ul" ).empty();
                // jQuery( ".wpsl-direction-before, .wpsl-direction-after" ).remove();
                calcRoute( start, end );

                //Trigger click on back button to locations lists when directions are shown.
                triggerLocationLists();
            } else {
                alert( 'Something went wrong, please try again!' );
            }
        }


         /**
          * Calculate the route from the start to the end.
          *
          * @since  1.0.0
          * @param  {object} start The latlng from the start point
          * @param  {object} end   The latlng from the end point
          * @returns {void}
          */
          function calcRoute( start, end ) {



           var legs, len, step, index, direction, i, j, distanceUnit, directionOffset,
           directionStops = "",
           request = {};

           if ( aka_stores.aka_settings.distance_unit == "km" ) {
            distanceUnit = 'METRIC';
        } else {
            distanceUnit = 'IMPERIAL';
        }

        request = {
            origin: start,
            destination: end,
            travelMode: 'DRIVING',
            unitSystem: google.maps.UnitSystem[ distanceUnit ]
        };

        serviceDirections.route( request, function( response, status ) {
            console.log(response);
            if ( status == google.maps.DirectionsStatus.OK ) {
                displayDirection.setMap( map );
                displayDirection.setDirections( response );

                if ( response.routes.length > 0 ) {
                    direction = response.routes[0];

                    directionStops += "<li><div class='aka-direction-before'><a class='aka-back' id='aka-direction-start' href='#'>Back</a><div class='aka-distance-time'><span class='aka-total-distance'>" + direction.legs[0].distance.text + "</span> - <span class='aka-total-durations'>" + direction.legs[0].duration.text + "</span></div></div></li>";

                        // Loop over the legs and steps of the directions.
                        for ( i = 0; i < direction.legs.length; i++ ) {
                            legs = direction.legs[i];

                            for ( j = 0, len = legs.steps.length; j < len; j++ ) {
                                step = legs.steps[j];
                                index = j+1;
                                directionStops = directionStops + "<li><div class='aka-direction-index'>" + index + "</div><div class='aka-direction-txt'>" + step.instructions + "</div><div class='aka-direction-distance'>" + step.distance.text + "</div></li>";
                            }
                        }
                        directionStops += "<p class='aka-direction-after'>" + response.routes[0].copyrights + "</p>";

                        jQuery( "#aka-direction-detail ul" ).html( directionStops );
                        jQuery( "#aka-store-lists" ).hide();
                        jQuery( "#aka-direction-detail" ).show();

                        // Remove all single markers from the map.
                        for ( i = 0, len = markersArray.length; i < len; i++ ) {
                            markersArray[i].setMap( null );
                        }

                        // Remove the start marker from the map.
                        if ( ( typeof( startMarkerData ) !== "undefined" ) && ( startMarkerData !== "" ) ) {
                            startMarkerData.setMap( null );
                        }


                        // Make sure the start of the route directions are visible if the store listings are shown below the map.
                        // if ( wpslSettings.templateId == 1 ) {
                        //     directionOffset = $( "#wpsl-gmap" ).offset();
                        //     $( window ).scrollTop( directionOffset.top );
                        // }
                    }
                } else {
                    geocodeErrors( status );
                }
            });
    }

    function triggerLocationLists() {

             // Handle the click on the back button when the route directions are displayed.
             jQuery( "#aka-direction-detail" ).on( "click", ".aka-back", function() {
                var i, len;

                 // Remove the directions from the map.
                 displayDirection.setMap( null );

                 // Restore the store markers on the map.
                 for ( i = 0, len = markersArray.length; i < len; i++ ) {
                    markersArray[i].setMap( map );
                }

                // Restore the start marker on the map.
                if ( ( typeof( startMarkerData ) !== "undefined" )  && ( startMarkerData !== "" ) ) {
                    startMarkerData.setMap( map );
                }


                map.setCenter( directionMarkerPosition.centerLatlng );
                map.setZoom( directionMarkerPosition.zoomLevel );

                jQuery( ".aka-direction-before, .aka-direction-after" ).remove();
                jQuery( "#aka-store-lists" ).show();
                jQuery( "#aka-direction-detail" ).hide();

                return false;
            });

         }


         function toggleMarkerAnimation() {

            jQuery('ul#aka-store-lists').on('mouseenter', 'li', function(){
                letsAnimate( jQuery(this).data('storeid'), 'start' );
            });

            jQuery('ul#aka-store-lists').on('mouseleave', 'li', function(){
                letsAnimate( jQuery(this).data('storeid'), 'stop' );
            });

         }


         function letsAnimate( storeId, status ) {
             var i, len, marker;

             // Find the correct marker to bounce based on the storeId.
             for ( i = 0, len = markersArray.length; i < len; i++ ) {
                if ( markersArray[i].storeId == storeId ) {

                    if ( status == "start" ) {
                        markersArray[i].setAnimation( google.maps.Animation.BOUNCE );
                    } else {
                        markersArray[i].setAnimation( null );
                    }
                }
             }
         }
     });