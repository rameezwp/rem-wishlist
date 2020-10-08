<?php 
$args = array(
	'meta_key'     => 'rem_wishlist_properties',
	'fields'       => array( 'id' ,'display_name' ),
 ); 
$users = get_users( $args );
?>
<div class="ich-settings-main-wrap">
	<table class="table table-bordered" width="100%">
		<tr>
			<th><?php _e( "No.", "rem-wishlist"); ?></th>
			<th><?php _e( "User Name", "rem-wishlist"); ?></th>
			<th><?php _e( "Wishlisted Properties", "rem-wishlist"); ?></th>
		</tr>
		<?php 
		if (!empty($users)) {
		foreach ($users as $key => $user) { ?>
		<tr>
			<th><?php echo $key+1; ?>-</th>
			<th><?php echo $user->display_name; ?></th>
			<td><?php  
				$wishlistings = get_user_meta( $user->id, "rem_wishlist_properties", true );
					
				if (!empty($wishlistings)) {
					foreach ($wishlistings as $key => $id) {
						echo $key+1 . '- ';
						echo '<a href="'.get_permalink($id).'">'.get_the_title( $id ).'</a>';
						echo " <br>";
					}
				}
			?></td>

		</tr>
		<?php }	 }else{
			echo "No Users";
			} ?>
	</table>
</div>