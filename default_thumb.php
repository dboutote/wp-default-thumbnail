<?php

/**
 * Load up a default thumbnail
 *
 * Checks for the requested thumbnail size first, if it's not found, will then check for the 
 * existence of a default image named "default_thumb.jpg".
 * Will write to either the parent image folder or the child image folder, depending on where it
 * finds the default thumb image.
 *
 * @since DBDB 1.0
 *
 * @param string $html              The post thumbnail HTML.
 * @param string $post_id           The post ID.
 * @param string $post_thumbnail_id The post thumbnail ID.
 * @param string $size              The post thumbnail size.
 * @param string $attr              Query string of attributes. 
 */
function dbdb_get_default_post_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	
    if ( empty( $html ) ) {
		global $_wp_additional_image_sizes;		
		$thumb_name = '';
		$thumb_base_dir = get_template_directory();
		$thumb_base_url = get_template_directory_uri();		
		$thumb_img_dir = apply_filters('default_thumbnail_dir', 'img');		
		$use_child_thumb = false;
		
		if( isset($_wp_additional_image_sizes[$size]) ) {
			$width = intval($_wp_additional_image_sizes[$size]['width']);
			$height = intval($_wp_additional_image_sizes[$size]['height']);
		} else {
			$width = get_option($size.'_size_w');
			$height = get_option($size.'_size_h');
		}
		
		// this is the thumbnail we're looking for
		$requested_thumb_name = "default_thumb-{$width}x{$height}.jpg";
		
		// check child theme for requested thumbnail size 
		$requested_thumb_path = get_stylesheet_directory() . '/' . $thumb_img_dir . '/' . $requested_thumb_name;

		if( file_exists( $requested_thumb_path ) ) {
			$use_child_thumb = true;			
		} 
		
		if( ! $use_child_thumb ) {
			$default_thumb_path = get_stylesheet_directory().'/'.$thumb_img_dir.'/default_thumb.jpg';	
			if(  file_exists( $default_thumb_path ) ){				
				$use_child_thumb = true;			
			}
		}
	
		if( $use_child_thumb ){
			$thumb_base_dir = get_stylesheet_directory();
			$thumb_base_url = get_stylesheet_directory_uri();
		}
		
		// if the child theme has no default thumb, check parent theme for requested_thumb size
		if( ! $use_child_thumb ) {			
			$requested_thumb_path = get_template_directory() . '/' . $thumb_img_dir . '/' . $requested_thumb_name;
		}

		// if requested post thumbnail doesn't exist, see if it can be created
		if ( !file_exists( $requested_thumb_path ) ) {
			
			$default_thumb_path = $thumb_base_dir . '/'. $thumb_img_dir .'/default_thumb.jpg';

			if( file_exists( $default_thumb_path ) ) {
				$image = wp_get_image_editor( $default_thumb_path );				
				if ( ! is_wp_error( $image ) ) {
					 $image->resize( $width, $height, true );
					 $image->save( $thumb_base_dir . '/' . $thumb_img_dir . '/' . $requested_thumb_name);
					 $thumb_name = $requested_thumb_name;
				}
			}
		} else {
			$thumb_name = $requested_thumb_name;
		}
		
		if( '' !== $thumb_name ) {
			$img_attr = array(
				'src' => $thumb_base_url . '/'.$thumb_img_dir.'/' . $thumb_name,
				'class' => 'attachment-'.$size.' wp-post-image',
				'alt' => '',
				'width' => $width,
				'height' => $height
			);
			$img_attr = apply_filters('default_post_thumb_attributes', $img_attr);
			$img_attr = array_map( 'esc_attr', $img_attr );
			$html = '<img';
			foreach ( $img_attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';
		}
		
    }    
	return apply_filters( 'default_post_thumbnail_html', $html, $post_id, $post_thumbnail_id, $size, $attr );
}
add_filter( 'post_thumbnail_html', 'dbdb_get_default_post_thumbnail', 20, 5 );
