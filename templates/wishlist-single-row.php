<tr>
	<td>
		<label class="product-check-label">
			<input type='checkbox' class='property-check' value='<?php echo esc_attr($property->ID); ?>'>
			<span class='checkmark'></span>
		</label>
	</td>

	<?php if (in_array("thumbnail", $fields)) { ?>
		<td class="img-wrap">
			<?php echo get_the_post_thumbnail( $property->ID, array( '50', '50' )) ?>
		</td>
	<?php } ?>

	<?php if (in_array("property_title", $fields)) { ?>
		<td>
			<a href="<?php echo get_the_permalink($property->ID); ?>"><?php echo esc_attr($property->post_title); ?></a>
		</td>
	<?php } ?>

	<?php
		foreach ($fields as $field_key) {
			$field_key = trim($field_key);
			if ($field_key != 'thumbnail' && $field_key != 'property_title' && function_exists('rem_get_field_value')) {
				echo '<td>'.rem_get_field_value($field_key, $property->ID).'</td>';
			}
		}
	?>

	<td>
		<a href="#" class="remove-property-btn btn btn-sm btn-danger" data-id="<?php echo esc_attr($property->ID); ?>"><i class='fa fa-trash'></i> <?php esc_attr_e('Remove', 'rem-wishlist'); ?></a>
	</td>
</tr>