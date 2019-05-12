jQuery(document).ready(function($) {

	/*
	 *
	 * ADD TICK ICON ON EVERY WISHLIST PROPERTY
	 *
	*/
	$('.rem-wishlist-btn').each(function(index, val){
		var existing_prop = rem_get_wishlist_property();
		var prop_id = $(this).data('id');
		if ( existing_prop != undefined ) {

			if (existing_prop.indexOf(prop_id) !== -1) {
				$(this).html("");
				$(this).append( '<i class="fas fa-heart"></i>' );
				$(this).attr( "title", rem_wishlist_var.icon_title_attr_remove );
			};
		};
	});
	/*
	 *
	 * WISHLIST BUTTON CLICK 
	 *
	*/
	$(document).on('click', '.rem-wishlist-btn', function(event){
		event.preventDefault();
		var btn  = $(this);
		var property_id = $(this).data('id');
		var loading_img = $(this).siblings('.rem-loading-img');
		var already_in_wishlist = $(this).children("i.fas.fa-heart").length > 0 ? true : false;
		
		loading_img.css( 'opacity', "1");
		if ( !already_in_wishlist ) {
			// seting in local storage
			var stored = rem_set_wishlist(property_id);
			if (stored) {
				// hide loading
	            loading_img.css( 'opacity', '0' );
	            // add icon by ajax
	            btn.html("");
	            btn.append( '<i class="fas fa-heart"></i>' );
	            btn.attr( "title", rem_wishlist_var.icon_title_attr_remove );
	            swal({
				    title: rem_wishlist_var.add_property_title,
				    text: rem_wishlist_var.add_property_text,
				    timer: 2000,
				    icon: "success",
				    button: false
				});
			}else {
				// hide loading
	            loading_img.css( 'opacity', '0' );
			};
		}else{
			rem_reset_wishlist(property_id);
			loading_img.css( 'opacity', '0' );
			btn.children("i.fas.fa-heart").remove();
			btn.append( '<i class="far fa-heart"></i>' );
			btn.attr( "title", rem_wishlist_var.icon_title_attr_added );
			swal({
			    title: rem_wishlist_var.removed_property_title,
			    text: rem_wishlist_var.removed_property_text,
			    timer: 2000,
			    icon: "success",
			    button: false
			});
		}
	});

	/*
	 *
	 * ADDING PROPERTY IN WISHLIST ( LOCAL STORAGE )
	 *
	*/
	function rem_set_wishlist( pid ) {
		var stored = false;
		var existing_prop = store.get('rem_wishlist_properties_test');
		if (existing_prop == undefined) {
			var prop_array = [pid];
			store.set('rem_wishlist_properties_test', prop_array);
			stored = true;
		}
		else {
			// check if already exists
			if (existing_prop.indexOf(pid) === -1) {
				existing_prop.push(pid);
				store.set('rem_wishlist_properties_test', existing_prop);
				stored = true;
			}else {
				swal({
				    title: rem_wishlist_var.already_exist_title,
				    text: rem_wishlist_var.already_exist_text,
				    timer: 2000,
				    button: false
				});
			}
		}
		
		return stored;
	}

	/*
	 *
	 * REMOVEING PROPERTY FORM WISHLIST ( LOCAL STORAGE )
	 *
	 *
	*/
	function rem_reset_wishlist(pid) {
		var existing_prop = rem_get_wishlist_property();
		//removing index
		var prop_index = existing_prop.indexOf(pid);
		console.log(prop_index);
		existing_prop.splice(prop_index, 1);
		// now updating fav local storage
		store.set('rem_wishlist_properties_test', existing_prop);	
		
		if( existing_prop.length == 0 ){
			rem_clear_prop_wishlist();
		}
	}

	/*
	 *
	 * GET ALL STORED WISHLIST PROPERTIES
	 *
	*/
	function rem_get_wishlist_property() {
		var existing_prop = store.get('rem_wishlist_properties_test');
		if( existing_prop !== undefined ){
			return existing_prop;
		} else {
			return null;
		}
	}

	/*
	 *
	 * CLEAR WISHLIST PROERTIES FORM LOCAL STORAGE 
	 *
	*/
	function rem_clear_prop_wishlist() {
		store.remove('rem_wishlist_properties_test');
	}

	/*
	 *
	 * LOAD ALL WISHLIST PROPERTIES IN SHORTCODE
	 *
	*/
	var property_ids = rem_get_wishlist_property();
	console.log(property_ids);
	if (property_ids != undefined) {
		var data = {
			"action" : "rem_get_wishlist_properties",
			"property_ids" : property_ids,
		}
		// $(".rem-wishlist-table").hide();
		$.post(rem_wishlist_var.ajaxurl, data, function(resp) {
	        // console.log(resp);
	        $('.loading-table').remove();
	        $('.wishlist_table_boday').append(resp)
			$('.rem-wishlist-table').slideDown("slow");
	    });
	}else {
		var not_found_msg = '<p class="alert alert-danger"><strong>'+rem_wishlist_var.empty_list_msg+'</strong></p>';
		$('.wishlist-box').html(not_found_msg);
	};
	/*
	 *
	 * REMOVE PROPERTY FORM SHORTCOED BIN BUTTON 
	 *
	*/
	$(document).on("click", ".remove-property-btn", function(event){
		event.preventDefault();
		var prop_id = $(this).data('id');
		rem_reset_wishlist(prop_id);	
		$(this).closest("tr").remove();

		var property_ids = rem_get_wishlist_property();
		if (property_ids == undefined) {

			var not_found_msg = '<p class="alert alert-danger"><strong>'+rem_wishlist_var.empty_list_msg+'</strong></p>';
			$('.wishlist-box').html(not_found_msg);
		};
	});

	/*
	 *
	 * INQUERY FORM 
	 *
	*/
	$(document).on('submit', '.rem-wishlist-inquiry-frm', function(event){
		event.preventDefault();
		var selected_properties = $('.rem-wishlist-table .property-check:checked').map(function() {
        	return $(this).val();
       	}).get();

       	console.log(selected_properties);

		if ( selected_properties.length != 0 ) {

			var loading_img = $(this).find('.rem-loading-img');	
			var ajaxurl = $('.ajaxurl').val();
			var data = $(this).serialize();
			var data = data+'&ids='+selected_properties;

			loading_img.css( 'opacity', "1");
			$.post(ajaxurl, data, function(resp) {
		            
	            // hide loading
	            loading_img.css( 'opacity', '0' );
	            console.log(resp);
	            var css_class = 'alert alert-success';
	            $.each( resp ,function( index, val ){
	            	if (val.status == 'Fail') {
	            		css_class = 'alert alert-danger';
	            		console.log(css_class);
	            	};
	            	$( ".responce-mesages" ).append( "<p class='"+css_class+"'><strong>"+val.msg+"</strong></p>" );
	            });
	        });

	    }else {
			swal({
			    title: rem_wishlist_var.form_property_empty_title,
			    text: rem_wishlist_var.form_property_empty_text,
			    timer: 2000,
			    button: false
			});
		};
	});
});