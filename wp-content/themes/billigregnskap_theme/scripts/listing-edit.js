function vantage_map_edit() {
	var geocoder = new google.maps.Geocoder();

	var map = new google.maps.Map(document.getElementById('listing-map'), {
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	var marker = new google.maps.Marker({
		map: map,
		draggable: true
	});
	
	google.maps.event.addListener(marker, 'dragend', function() {
		var drag_position = marker.getPosition();

		jQuery('input[name="lat"]').val(drag_position.lat());
		jQuery('input[name="lng"]').val(drag_position.lng());
		update_position(drag_position);

		jQuery.getJSON( Vantage.ajaxurl, {
			action: 'vantage_create_listing_geocode',
			lat: drag_position.lat(),
			lng: drag_position.lng()
		}, function(response) {
			if ( response.status == 'OK' ) {
				var found_location = response.results[0].geometry.location;

				jQuery('#listing-address').val(response.results[0].formatted_address);

			} else {
				alert("Google Maps error: " + response.status );
			}
		} );
	});	

	function update_position(found_location) {
		map.setCenter(found_location);
		marker.setPosition(found_location);
	}

	var address = jQuery('#listing-address').val();
	
	if ( address != '' )
		 update_map(jQuery.noop);

	var lat = jQuery('input[name="lat"]').val();
	var lng = jQuery('input[name="lng"]').val();

	if ( lat != 0 && lng != 0 )
		update_position(new google.maps.LatLng(lat, lng));

	var map_initialized = false;

	function update_map(callback) {
		if ( typeof Vantage === 'undefined' ) {
			return setTimeout('update_map(callback)', 500);
		}

		map_initialized = true;

		jQuery.getJSON( Vantage.ajaxurl, {
			action: 'vantage_create_listing_geocode',
			address: jQuery('#listing-address').val(),
		}, function(response) {
			if ( response.status == 'OK' ) {
				var found_location = response.results[0].geometry.location;

				jQuery('#listing-address').val(response.results[0].formatted_address);

				jQuery('input[name="lat"]').val(found_location.lat);
				jQuery('input[name="lng"]').val(found_location.lng);

				update_position(new google.maps.LatLng(found_location.lat,found_location.lng));

			} else {
				alert("Google Maps error: " + response.status );
			}

			callback(status);
		} );
	}

	jQuery('#listing-find-on-map').click(function(ev) {
		update_map(jQuery.noop);
	});

	function ensureMapInit(form) {
		if ( map_initialized ) {
			form.submit();
			return;
		}

		update_map(function(status) {
			form.submit();
		});
	}

	function loadFormFields() {
		var data = {
			action: 'app-render-form',
			listing_category: jQuery(this).val()
		};

		jQuery.post(VA_i18n.ajaxurl, data, function(response) {
			jQuery('#custom-fields').html(response);
		});
	}

	jQuery('#_listing_category')
		.change(loadFormFields)
		.find('option').eq(0).val(''); // needed for jQuery.validate()

	jQuery('.uploaded').sortable();

	jQuery('#create-listing').validate({
		submitHandler: ensureMapInit
	});
}

	jQuery('#create-listing input[type="file"]').after('<input type="button" class="clear-file" value="' + VA_i18n.clear + '">');

	jQuery('#create-listing .clear-file').live('click', function() {
		jQuery(this).parent().html( jQuery(this).parent().html() );
		return false;
	});
