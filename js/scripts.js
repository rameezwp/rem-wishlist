jQuery(document).ready(function($) {

	/*
	 *
	 * Check user loged status 
	 *
	*/
	var data = {
    	action: 'is_user_logged_in'
	};
	var is_user_logged_in = '';
	$.post(rem_wishlist_var.ajaxurl, data, function(response) {

	    is_user_logged_in = response;
	});
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
				$(this).addClass('active');
				$(this).append( '<i class="fas fa-heart"></i>' );
				$(this).attr( "title", rem_wishlist_var.icon_title_attr_remove );
			};
		};
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
		$('.rem-loading-img').css({'display': 'inline-block'});
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
	if (property_ids != undefined ) {
		var data = {
			"action" : "rem_get_wishlist_properties",
			"property_ids" : property_ids,
		}
		// $(".rem-wishlist-table").hide();
		$.post(rem_wishlist_var.ajaxurl, data, function(resp) {
	        // console.log(resp);
	        $('.loading-table').remove();
	        $('.wishlist_table_boday').append(resp.html)
			$('.rem-wishlist-table').slideDown("slow");
	    });
	}else {
		if ($('body').hasClass('logged-in')) {
			var data = {
			"action" : "rem_get_wishlist_properties",
			}
			// $(".rem-wishlist-table").hide();
			$.post(rem_wishlist_var.ajaxurl, data, function(resp) {
		        $('.loading-table').remove();
		        if (resp.html != '') {

			        $('.wishlist_table_boday').append(resp.html)
					$('.rem-wishlist-table').slideDown("slow");
					$.each(resp.ids, function(index, id) {
						rem_set_wishlist(id);
					});
		        }else{
		        	var not_found_msg = '<p class="alert alert-danger"><strong>'+rem_wishlist_var.empty_list_msg+'</strong></p>';
					$('.wishlist-box').html(not_found_msg);
		        };
		    });
		}else{

			var not_found_msg = '<p class="alert alert-danger"><strong>'+rem_wishlist_var.empty_list_msg+'</strong></p>';
			$('.wishlist-box').html(not_found_msg);
		}
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
		if (is_user_logged_in && is_user_logged_in != '') {

        	wishlist_in_user_profile();
        }
		var property_ids = rem_get_wishlist_property();
		if (property_ids == undefined) {

			var not_found_msg = '<p class="alert alert-danger"><strong>'+rem_wishlist_var.empty_list_msg+'</strong></p>';
			$('.wishlist-box').html(not_found_msg);
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
		var already_in_wishlist = $(this).children("i.fas.fa-heart").length > 0 ? true : false;
		
		if ( !already_in_wishlist ) {
			// seting in local storage
			var stored = rem_set_wishlist(property_id);
			if (stored) {
	            // add icon by ajax
	            btn.html("");
	            btn.addClass('active');
	            btn.append( '<i class="fas fa-heart"></i>' );
	            btn.attr( "data-original-title", rem_wishlist_var.icon_title_attr_remove );
	            btn.attr( "title", rem_wishlist_var.icon_title_attr_remove );
				if (is_user_logged_in && is_user_logged_in != '') {

	            	wishlist_in_user_profile();
	            	swal({
					    title: rem_wishlist_var.add_property_title,
					    text: rem_wishlist_var.add_property_text,
					    timer: 2000,
					    icon: "success",
					    button: false
					});
				}else{
		            swal({
					    title: rem_wishlist_var.add_property_title,
					    text: rem_wishlist_var.add_property_text,
					    timer: 2000,
					    icon: "success",
					    button: false
					});
				};
			}else {
	            
			};
		}else{
			rem_reset_wishlist(property_id);
			
			btn.children("i.fas.fa-heart").remove();
			btn.removeClass('active');
			btn.append( '<i class="far fa-heart"></i>' );
			btn.attr( "data-original-title", rem_wishlist_var.icon_title_attr_added );
			if (is_user_logged_in && is_user_logged_in != '') {

				wishlist_in_user_profile();

				swal({
				    title: rem_wishlist_var.removed_property_title,
				    text: rem_wishlist_var.removed_property_text,
				    timer: 2000,
				    icon: "success",
				    button: false
				});
			}else {

				swal({
				    title: rem_wishlist_var.removed_property_title,
				    text: rem_wishlist_var.removed_property_text,
				    timer: 2000,
				    icon: "success",
				    button: false
				});
			};
		}
	});
	/*
	 *
	 * SEND AJAX REQUEST FOR ADDING WISHLISTING IN USER PROFILE 
	 *
	*/
	function wishlist_in_user_profile() {
		var property_ids = rem_get_wishlist_property();
		var data = {
			action : "wishlist_in_user_profile",
			property_ids : property_ids,
		}

		$.post(rem_wishlist_var.ajaxurl, data, function(response) {
		})
	}


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

		if ( selected_properties.length != 0 ) {

			var loading_img = $(this).find('.form-loader');	
			var ajaxurl = $('.ajaxurl').val();
			var data = $(this).serialize();
			var data = data+'&ids='+selected_properties;

			loading_img.css({'display': 'inline-block'});
			
			$.post(ajaxurl, data, function(resp) {
		            
	            // hide loading
	            loading_img.hide();
	            var css_class = 'alert alert-success';
	            $.each( resp ,function( index, val ){
	            	if (val.status == 'Fail') {
	            		css_class = 'alert alert-danger';
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