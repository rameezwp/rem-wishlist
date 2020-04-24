<?php 
$args = array(
	'meta_key'     => 'rem_wishlist_properties',
	'fields'       => array( 'id' ,'display_name' ),
 ); 
$users = get_users( $args );
?>
<table class="table" width="100%">
	<tr>
		<th>No.</th>
		<th>Agent Name</th>
		<th>Wishlisted Properties</th>
	</tr>
	<?php 
	if (!empty($users)) {
	foreach ($users as $key => $user) { ?>
	<tr>
		<th><?php echo $key+1; ?>-</th>
		<th><?php echo $user->display_name; ?></th>
		<td><?php  
			$wishlistings = get_user_meta( $user->id, "rem_wishlist_properties", true );
			// var_dump($wishlistings);
			foreach ($wishlistings as $key => $id) {
				echo $key+1 . '- ';
				echo '<a href="'.get_permalink($id).'">'.get_the_title( $id ).'</a>';
				echo " <br>";
			}
		?></td>

	</tr>
	<?php }	 }else{
		echo "No Users";
		} ?>
</table>