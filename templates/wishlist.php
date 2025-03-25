<div class="ich-settings-main-wrap wishlist-box">
	<div id="wishlist-wrap">
		<div class="table-responsive property-list">
			<table class="table rem-wishlist-table">
			  <thead>
				<tr>
					<th></th>
					
					<?php if (in_array("thumbnail", $fields)) { ?>
						<th><?php _e( 'Thumbnail', "rem-wishlist" ); ?></th>
					<?php } ?>

					<?php if (in_array("property_title", $fields)) { ?>
						<th><?php _e( 'Title', "rem-wishlist" ); ?></th>	
					<?php } ?>
					
					<?php
						foreach ($fields as $field_key) {
							$field_key = trim($field_key);
							if ($field_key != 'thumbnail' && $field_key != 'property_title' && function_exists('rem_get_field_label')) {
								echo '<th>'.rem_get_field_label($field_key).'</th>';
							}
						}
					?>
					
					<th><?php _e( 'Actions', "rem-wishlist" ); ?></th>
				</tr>
			  </thead>
			  <tbody class="wishlist_table_boday">
			  </tbody>
			</table>
			<div class="loading-table text-center">
				<div class="rem-loading-img"></div>
			</div>
		</div>
	</div>
	<div class="wishlist-msg">
		<h2 class="text-center"><?php echo rem_get_option( "wl_inquiry_heading", "Contact Agents"); ?></h2>
		<input type="hidden" class="ajaxurl" value="<?php echo admin_url('admin-ajax.php') ?>">
		<form class="form-horizontal rem-wishlist-inquiry-frm">
			<input type="hidden" name="action" value="rem_wishlist_properties_inquiry">
		  <div class="form-group">
		    <div class="col-sm-12">
		      <input type="text" class="form-control" name="client_name"  placeholder=<?php _e( 'Name', "rem-wishlist" ); ?> required>
		    </div>
		  </div>
		  <div class="form-group">
		    <div class="col-sm-12">
		      <input type="email" class="form-control" name="client_email" placeholder=<?php _e( 'Email', "rem-wishlist" ); ?> required>
		    </div>
		  </div>
		  <div class="form-group">
		    <div class="col-sm-12">
		      <input type="tel" class="form-control" name="client_phone" placeholder=<?php _e( 'Phone No.', "rem-wishlist" ); ?> >
		    </div>
		  </div>
		  <div class="form-group">
		    <div class=" col-sm-12">
		      <textarea class="form-control" rows="3" name="message" placeholder=<?php _e( 'Message', "rem-wishlist" ); ?> required></textarea>
		    </div>
		  </div>
		  <div class="form-group">
		    <div class="col-sm-12 text-right">
				<div class="form-loader"></div>
		      	<input type="submit" class="btn btn-primary" value="<?php _e( 'Send', "rem-wishlist"); ?>">
		    </div>
		  </div>
		</form>
		<div class="responce-mesages"></div>
	</div>
</div>