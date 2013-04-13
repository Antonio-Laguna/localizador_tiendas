(function($){
    var $page = $('#localiza');

    var Localizador = {
        el : {
            $page : $page,
            $formulario : $page.find('form'),
            $selectProvincias : $('#provincias'),
            $selectPoblaciones : $('#poblaciones'),
            $botonObtener : $page.find('#ver_listado'),
            $botonCerca : $page.find('#obtener_cerca'),
            $errorLocalizacion : $page.find('#errorLocalizacion'),
            $mapDiv : $('#mapa'),
            $contenidoMarker : $('#contenido-marker').hide(),
            $direccionMarker : $('#direccion-marker'),
            $botonLlegar : $('#llegar')  
        },
        API_URL : 'controller.php',
        REVERSE_GEOCODING_URL : 'http://maps.googleapis.com/maps/api/geocode/json',
        FIT_BOUNDS_TIMEOUT : 500,
        geocoder : null,
        viewed_marker : null,
        markers : [],
        stores : [],
        dir_display : new google.maps.DirectionsRenderer(),
        dir_service : new google.maps.DirectionsService(),
        infowindow : new google.maps.InfoWindow({
            content: ''
        }),

        init : function() {
            Localizador.el.$botonObtener.button('disable');

            // Google
            Localizador.geocoder = new google.maps.Geocoder();
            Localizador.dir_display = new google.maps.DirectionsRenderer();
            Localizador.dir_service = new google.maps.DirectionsService();
            Localizador.infowindow = new google.maps.InfoWindow({
                content: ''
            });

            Localizador.el.$errorLocalizacion.popup({ positionTo: "window" });

            if (! "geolocation" in navigator) {
                // Si no hay geolocalizador en el navegador ocultamos el botón
                Localizador.el.$botonCerca.hide();
            }

            Localizador.bindEvents();
        },
        bindEvents : function() {
            this.el.$page.on('pageshow', Localizador.loadProvincias);
            this.el.$selectProvincias.on('change', Localizador.loadPoblaciones);
            this.el.$selectPoblaciones.on('change',Localizador.allowSubmit);
            this.el.$formulario.on('submit',function(e){
                e.preventDefault();
                Localizador.requestTiendas().done(Localizador.loadMap);
            });
            this.el.$botonCerca.on('click', function(e){
                e.preventDefault();
                navigator.geolocation.getCurrentPosition(Localizador.geoCodePosition, Localizador.errorPosition);
            });
            this.el.$botonLlegar.on('click', function(e){
                e.preventDefault();
                navigator.geolocation.getCurrentPosition(Localizador.getDirection);
            });
            this.el.$mapDiv.on('click', 'a.mas-informacion', Localizador.showInfoAboutMarker);
        },
        initMap : function() {
            if (Localizador.markers.length){
                var mapOptions = {
                    zoom: 13,
                    center: new google.maps.LatLng(40.41694, -3.70081), // Madrid
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    disableDefaultUI: true // Disabling buttons and stuff
                };

                Localizador.map = new google.maps.Map(document.getElementById('map'),mapOptions);
                Localizador.dir_display.setMap(Localizador.map);
                google.maps.event.addListenerOnce(Localizador.map, 'idle', Localizador.associateMarkersToMap);
            }
            else {
                $.mobile.changePage('#localiza');
            }
        },
        errorPosition : function(){
            Localizador.el.$errorLocalizacion.popup("open");
        },
        geoCodePosition : function(position){
            var latLng = new google.maps.LatLng(
                position.coords.latitude,
                position.coords.longitude
            );

            Localizador.geocoder.geocode({
                'latLng' : latLng
            }, function(results, status){
                if (status == google.maps.GeocoderStatus.OK) {
                    var provincia = results[0].address_components[3].long_name,
                        poblacion = results[0].address_components[2].long_name;

                    Localizador.requestNearStores(
                            provincia,
                            poblacion,
                            position.coords.latitude,
                            position.coords.longitude
                    ).done(Localizador.loadMap);
                }
                else {
                    Localizador.errorPosition();
                }
            });
        },
        getDirection : function(position){
            var origin = new google.maps.LatLng(
                position.coords.latitude,
                position.coords.longitude
            ),
            destination = new google.maps.LatLng(
                Localizador.viewed_marker.lat,
                Localizador.viewed_marker.lng
            );

            Localizador.dir_service.route({
                origin: origin,
                destination : destination,
                travelMode: google.maps.TravelMode.DRIVING
            },function(response, status){
                Localizador.hideAllButCurrent();
                if (status == google.maps.DirectionsStatus.OK) {
                    Localizador.dir_display.setDirections(response);
                }
            })
        },
        loadProvincias : function(){
            Localizador.requestProvincias().done(Localizador.insertProvincias);
        },
        loadPoblaciones : function(){
            Localizador.requestPoblaciones().done(Localizador.insertPoblaciones);
        },
        requestProvincias : function(){
            return $.getJSON(Localizador.API_URL, {
                accion : 'obtenerProvincias'
            });
        },
        requestPoblaciones : function() {
            return $.getJSON(Localizador.API_URL, {
                accion : 'obtenerPoblaciones',
                provincia : Localizador.el.$selectProvincias.val()
            });
        },
        requestTiendas : function(){
            return $.getJSON(Localizador.API_URL, {
                accion : 'obtenerTiendas',
                poblacion : Localizador.el.$selectPoblaciones.val()
            });
        },
        requestNearStores : function(provincia, poblacion, lat, lng){
            return $.getJSON(Localizador.API_URL, {
                accion : 'obtenerTiendasCercanas',
                provincia : provincia,
                poblacion : poblacion,
                lat : lat,
                lng : lng
            });
        },
        insertProvincias : function(data){
            var provincias = '<option value="">seleccione</option>';
            $.each(data,function(i,obj){
                provincias += '<option value="'+ obj.id +'">' + obj.nombre + '</option>';
            });

            Localizador.el.$selectProvincias.html(provincias).selectmenu('refresh');
        },
        insertPoblaciones : function(data){
            var poblaciones = '';

            if (data.error){
                poblaciones = '<option value="">seleccione</option>';

                if(!Localizador.el.$selectPoblaciones.attr('disabled')){
                    Localizador.el.$selectPoblaciones.selectmenu('disable');
                }
            }
            else {
                Localizador.el.$selectPoblaciones.selectmenu('enable');
                poblaciones = '<option value="">seleccione población</option>';

                $.each(data,function(i,obj){
                    poblaciones += '<option value="'+ obj.id +'">' + obj.nombre + '</option>';
                });
            }

            Localizador.el.$selectPoblaciones.html(poblaciones).selectmenu('refresh');
        },
        allowSubmit : function() {
            if (Localizador.el.$selectPoblaciones.val()){
                Localizador.el.$botonObtener.button('enable');
            }
            else {
                Localizador.el.$botonObtener.button('disable');
            }
        },
        loadMap : function(data) {
            if (data.error){
                Localizador.el.$selectProvincias.val('').trigger('change');
                Localizador.el.$botonObtener.val('').trigger('change').button('disable');
            }
            else {
                Localizador.insertMarkers(data);
                $.mobile.changePage('#mapa');
            }
        },
        getIcon : function(type){
            var icon = '';
            switch (type) {
                case 'Apple Store':
                    icon = 'img/default_blk_apple_pin.png';
                    break;
            }

            return icon;
        },
        insertMarkers : function(data){
            Localizador.deleteMarkers();
            var mapBounds = new google.maps.LatLngBounds();

            $.each(data,function(i,obj){
                var marker = new google.maps.Marker({
                    map : null,
                    position : new google.maps.LatLng(obj.lat,obj.lng),
                    array_index : Localizador.markers.length,
                    icon : Localizador.getIcon(obj.tipo),
                    shop_id : obj.id
                });
                Localizador.markers.push(marker);
                Localizador.stores.push(obj);
                mapBounds.extend(marker.position);
                google.maps.event.addListener(marker, 'click', Localizador.openInfoWindow);
            });

            setTimeout(function(){
                Localizador.map.fitBounds(mapBounds);
            }, Localizador.FIT_BOUNDS_TIMEOUT);
        },
        openInfoWindow : function(){
            var obj = Localizador.stores[this.array_index];

            Localizador.viewed_marker = obj;

            var html = '<p>' + obj.nombre_comercial + '</p>' +
                    '<p><a href="#" class="mas-informacion">detalle</a></p>';

            Localizador.infowindow.setContent(html);
            Localizador.infowindow.open(Localizador.map,this);
        },
        associateMarkersToMap : function(){
            $.each(Localizador.markers,function(i,marker){
                marker.setMap(Localizador.map);
            });
        },
        deleteMarkers : function(){
            $.each(Localizador.markers,function(i,marker){
                marker.setMap(null);
                google.maps.event.clearInstanceListeners(marker);
            });

            Localizador.markers = [];
            Localizador.stores = [];
        },
        hideAllButCurrent : function() {
            var current = Localizador.viewed_marker;

            $.each(Localizador.markers,function(i,marker){
                if (current.id !== marker.id){
                    marker.setVisible(false);
                }
            });
        },
        showInfoAboutMarker : function(e){
            e.preventDefault();
            Localizador.el.$contenidoMarker.show();
            var obj = Localizador.viewed_marker,
                html = '<p>'+ obj.direccion +'</p>' +
                        '<p>' + [obj.poblacion,obj.provincia].join(', ') + '</p>' +
                        '<p>CP: ' + obj.cp + '</p>';
            Localizador.el.$direccionMarker.html(html);
        }
    };

    Localizador.el.$page.on('pageinit', Localizador.init);
    Localizador.el.$mapDiv.on('pageshow', Localizador.initMap);
})(jQuery);