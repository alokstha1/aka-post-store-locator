jQuery(document).ready( function() {

    var map, geocoder, displayDirection, serviceDirections, infoWindow, autoCompleteLatLng,
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

        addMarker( startLatLng, '', false, infoWindow );

        //loop over the store items and add map marker
        if ( jQuery('ul.aka-store-lists li').length > 0 ) {
            bounds        = new google.maps.LatLngBounds();

            jQuery('ul.aka-store-lists li').each( function(index){

                var item_latlng = jQuery(this).data('latlng').split( "," );
                latLng = new google.maps.LatLng( item_latlng[0], item_latlng[1] );
                addMarker( latLng, index, false, infoWindow );
                bounds.extend( latLng );
            });

            // Make all the markers fit on the map.
            map.fitBounds( bounds );
        }
    }

    function initAutocomplete() {

        var input, autocomplete, place,
            options = {};

            // Check if we need to set the geocode component restrictions.
            if ( typeof aka_stores.aka_settings.region !== "undefined" && aka_stores.aka_settings.region.length > 0 ) {
                var regionComponents = [];
                regionComponents['country'] = aka_stores.aka_settings.region.toUpperCase();

                options.componentRestrictions = aka_stores.aka_settings.regionComponents;
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
                // infoWindowData = {
                //     store: wpslLabels.startPoint
                // };

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
            console.log(markersArray);

            google.maps.event.addListener( marker, "click",( function( map ) {
                return function() {

                    // The start marker will have a store id of 0, all others won't.
                    /*if ( storeId != 0 ) {

                        // Check if streetview is available at the clicked location.
                        if ( typeof wpslSettings.markerStreetView !== "undefined" && wpslSettings.markerStreetView == 1 ) {
                            checkStreetViewStatus( latLng, function() {
                                setInfoWindowContent( marker, createInfoWindowHtml( infoWindowData ), infoWindow, currentMap );
                            });
                        } else {
                            setInfoWindowContent( marker, createInfoWindowHtml( infoWindowData ), infoWindow, currentMap );
                        }
                    } else {
                        setInfoWindowContent( marker, wpslLabels.startPoint, infoWindow, currentMap );
                    }

                    google.maps.event.clearListeners( infoWindow, "domready" );

                    google.maps.event.addListener( infoWindow, "domready", function() {
                        infoWindowClickActions( marker, currentMap );
                        checkMaxZoomLevel();
                    });*/
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
    });