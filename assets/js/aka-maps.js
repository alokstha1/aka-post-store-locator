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
            streetViewControl: Number( aka_stores.aka_settings.street_view ) ? true : false,

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
        if ( jQuery('ul.aka-store-lists li').length > 0 ) {

            jQuery('ul.aka-store-lists li').each( function(index){

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
            map.fitBounds( bounds );
        }

        //initialize form store search button
        initialize_store_search( infoWindow );
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
            } else {
                url = aka_stores.marker_dir_url + 'blue.png';
            }

            mapIcon = {
                url: url,
                scaledSize: new google.maps.Size( 20, 32 ), //retina format
                origin: new google.maps.Point( 0, 0 ),
                anchor: new google.maps.Point( 0, 32 )
            };

            marker = new google.maps.Marker({
                position: latLng,
                map: map,
                optimized: false, //fixes markers flashing while bouncing
                title: 'Test Title',//decodeHtmlEntity( infoWindowData.store ),
                draggable: draggable,
                storeId: storeId,
                icon: mapIcon
            });

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

            // Only the start marker will be draggable.
            /*if ( draggable ) {
                google.maps.event.addListener( marker, "dragend", function( event ) {
                    deleteOverlays( keepStartMarker );
                    map.setCenter( event.latLng );
                    reverseGeocode( event.latLng );
                    findStoreLocations( event.latLng, resetMap, autoLoad = false, infoWindow );
                });
            }*/
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
        function prepareStoreSearch( latLng, infoWindow ) {
            var autoLoad = false;

            // Add a new start marker.
            addMarker( latLng, '', true, infoWindow );

            // Try to find stores that match the radius, location criteria.
            makeAjaxRequest( latLng, resetMap, autoLoad, infoWindow );
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
console.log(startLatLng);
            var post_id = jQuery('#aka_post_id').val();
            var storeList = jQuery("#aka-store-lists");

            var ajaxData = {
                action: "aka_store_search",
                lat: startLatLng.lat(),
                lng: startLatLng.lng(),
                post_id: post_id
            };

            jQuery(storeList).empty();

            jQuery.ajax({
                data: ajaxData,
                url: aka_stores.ajaxurl,
                success: function( response ){
                    console.log(response);
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

    });