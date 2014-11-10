jQuery(function() {
	jQuery('.menu > ul > li:first').addClass('first');
	jQuery('.menu > ul > li:last').addClass('last');

	function switch_to_tab(tab_id) {
		if ( tab_id == '#overview' ) {
			jQuery('#overview-tab').addClass('active-tab');
			jQuery('#overview').show();
			jQuery('#reviews-tab').removeClass('active-tab');
			jQuery('#reviews').hide();
		} else if ( tab_id == '#reviews' ) {
			jQuery('#reviews-tab').addClass('active-tab');
			jQuery('#reviews').show();
			jQuery('#overview-tab').removeClass('active-tab');
			jQuery('#overview').hide();
		}
		
		var yScroll = document.body.scrollTop;
		window.location.hash = tab_id;
		document.body.scrollTop = yScroll;
	}

	if ( 0 === window.location.hash.indexOf('#review') ) {
		switch_to_tab('#reviews');
		
		if ( jQuery(window.location.hash).length == 0 ) return;
		
		jQuery('html, body').animate({
			scrollTop: ( jQuery(window.location.hash).offset().top -= 35 )
		}, 25);
	}

	jQuery('.tabs > a').click(function(e){
		e.preventDefault();

		switch_to_tab(jQuery(this).attr('href'));
	});

	if ( jQuery('#refine-distance').length ) {
		jQuery('#refine-distance input[type="range"]').range({
			range: false,
			change: function(val) {
				jQuery('#radius-info').html( ( Math.round( parseFloat(val) * 100 ) / 100 ) );
			},
		});
	}

	jQuery('#refine-categories').on('change', ':checkbox', function() {
		var $checkbox = jQuery(this);

		if ( $checkbox.prop('checked') ) {
			var $item = $checkbox.closest('li');

			// Uncheck parents
			$item.parents('li').children('label').children(':checkbox')
				.prop('checked', false);

			// Uncheck children
			$item.children('.children').find(':checkbox')
				.prop('checked', false);
		}
	});

	if ( jQuery.fn.colorbox ) {
		jQuery("a[rel='colorbox']").colorbox({transition:'fade',
			current:'',
			slideshow: false,
			slideshowAuto: false,
			maxWidth: '100%',
			maxHeight: '100%',
			scalePhotos: true
		});
	}

	jQuery('.reply-link').click(function(e){
		e.preventDefault();

		jQuery('#reply-review-form').slideUp('fast');
		jQuery('#comment_parent').val('');

		var parent = jQuery(this).closest('.review');

		if (parent.hasClass('replying')) {
			jQuery('.review').removeClass('replying');
			return;
		}

		var parent_comment_id = parent.attr('id').split('-')[1];

		parent.addClass('replying');
		parent.children('.review-content').append(jQuery('#reply-review-form'));
		jQuery('#reply-review-form').slideDown('slow');
		jQuery('#reply-review-form').validate();
		jQuery('#comment_parent').val(parent_comment_id);
	});
	
	if ( jQuery('#add-review-form').length ) {
		jQuery('#add-review-form').validate({
			submitHandler: ensureReviewRating
		});
	}
	
	jQuery('.listing-faves > a').live('click', function(e){
		e.preventDefault();

		var fave = jQuery(this);
		var fave_data = vantage_parse_url_vars(fave.attr('href'));

		var faved_count = jQuery('.listing-unfave-link').length;
		var unfaved_count = 0;

		jQuery('.fave-icon', fave).toggleClass('processing-fave');
		jQuery('.fave-icon', fave).text('Please wait');

		jQuery.post( Vantage.ajaxurl, {
			action: 'vantage_favorites',
			current_url: Vantage.current_url,
			_ajax_nonce: fave_data['ajax_nonce'],
			favorite: fave_data['favorite'],
			listing_id: fave_data['listing_id']
		}, function(data) {

				jQuery('.notice').fadeOut('slow');
				jQuery('#main:first-child').prepend(data.notice);

				fave.replaceWith(data.html);

				if ( data.redirect )
				 	return;

				if ( window.location.pathname.indexOf('favorites') > 0 && fave.hasClass('listing-unfave-link') ) {				
					jQuery('article#post-'+fave_data['listing_id']).fadeOut();
					unfaved_count++;

					if ( faved_count == unfaved_count )	location.reload();
				}
		}, "json");

	});

});

function ensureReviewRating(form) {
	if ( jQuery('input[name="review_rating"]').val().length > 0 ) {
		form.submit();
	} else {
		jQuery('#review-rating').after('<label for="review_rating" generated="true" class="error rating-error" style="display: block; ">The rating is required.</label>');
		return false;
	}		
}

function vantage_map_view() {
	var mapDiv = jQuery('#listing-map');

	if ( !mapDiv.length )
		return;

	function show_map(listing_location) {
		var map = new google.maps.Map(mapDiv.get(0), {
			zoom: 14,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});

		var marker = new google.maps.Marker({
			map: map
		});

		map.setCenter(listing_location);
		marker.setPosition(listing_location);
	}

	if ( mapDiv.data('lat') ) {
		 show_map(new google.maps.LatLng(mapDiv.data('lat'), mapDiv.data('lng')));
	} else {
		jQuery.getJSON( Vantage.ajaxurl, {
			action: 'vantage_listing_geocode',
			listing_id: mapDiv.data('listing_id')
		}, function(response) {
			show_map(new google.maps.LatLng(response.lat, response.lng));
		} );
	}
}

// Read url parameters and return them as an associative array.
function vantage_parse_url_vars(url){

    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
