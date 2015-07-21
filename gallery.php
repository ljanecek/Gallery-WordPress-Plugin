<?php
/*
 * Plugin Name: Better Gallery
 * Plugin URI: http://brainz.cz
 * Description: Better Gallery for wordpress programer using attachment etc.
 * Version: 0.1
 * Author: Ladislav Janeček
 * Author URI: http://brainz.cz
 */

define('THIS_URL', plugins_url( '', __FILE__ ));


function gallery_metabox() {

	/*
		get list of post types
	*/
	$get_post_types_option = array(
		'public' => true
	);

	$list_of_post_types = get_post_types($get_post_types_option, 'names');

	/*

	*/
	foreach ($list_of_post_types as $post_type) if($post_type !== 'attachment') {
		add_meta_box(
			'gallery_section_hash',
			'Gallery',
			'gallery_list_content',
			$post_type
		);
	}
}

add_action('add_meta_boxes', 'gallery_metabox');


function gallery_list_content($post){

	wp_enqueue_script('jQuery');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('scporderjs', THIS_URL . '/assets/better_gallery.js', array('jquery'), null, true);
	wp_enqueue_style('scporder', THIS_URL . '/assets/better_gallery.css', array(), null);

	/*
		Render list of uploaded images
	*/
	$images = get_atttachment_img($post);

	if(!empty($images)){
		echo "<div id=\"wrapper-ajax-better-gallery\">";
		echo get_list_view($images, $post);
		echo "</div>";
	}else{
		echo get_no_images_view();
	}

	/*
		Render button for upload
	*/
	echo get_upload_view($post);
}


function get_atttachment_img($post){

	$args_attach =  array(
		'post_type' => 'attachment',
		'post_parent' => $post->ID,
		'order' => 'ASC',
		'posts_per_page' => -1,
		'orderby' => 'menu_order'
	);

	$attachments = get_posts($args_attach);

	$array = array();

	foreach($attachments as $key => $attachment){
		$thumb = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
		if(!empty($thumb[0])) $array[$key]['src'] = $thumb[0];
		$array[$key]['id'] = $attachment->ID;
		$array[$key]['menu_order'] = $attachment->menu_order;
	}

	return $array;
}


add_action( 'wp_ajax_update_attachment_sorting', 'update_attachment_sorting_callback' );

function update_attachment_sorting_callback() {

	$post_id = $_POST['ID'];
	$attachment_id = $_POST['data'];

	wp_update_post(array(
			'ID' => $attachment_id,
			'post_parent' => $post_id
		)
	);

	regenerate_attachment($post_id);

	wp_die();
}


add_action( 'wp_ajax_delete_attachment', 'delete_attachment_callback' );

function delete_attachment_callback() {

	$post_id = $_POST['ID'];
	$attachment_id = $_POST['data'];

	wp_delete_attachment($attachment_id);

	regenerate_attachment($post_id);

	wp_die();
}


add_action( 'wp_ajax_update_list_attachment', 'update_list_attachment_callback' );

function update_list_attachment_callback() {

	$post_id = $_POST['ID'];

	regenerate_attachment($post_id);

	wp_die();
}


function regenerate_attachment($post_id){

	$post = (object) array('ID' =>  $post_id);
	$images = get_atttachment_img($post);

	echo get_list_view($images, $post);
}



function get_list_view($images){

	$thumb = get_post_thumbnail_id($post->ID);
	?>

	<div class="better-gallery-wrapper">
		<ul id="better-gallery" class="attachments">
			<?php foreach($images as $key => $img) { ?>
				<li id="attachments[<?=$img['id']?>]" class="attachment">
					<div class="thumb">
						<img src="<?=$img['src']?>" />
						<?php if($thumb == $img['id']) { ?>
							<span>Featured Image</span>
						<?php } ?>
						<a class="remove" href="javascript:Main.remove(<?=$img['id']?>);">×</a>
						<a class="edit" href="<?=home_url("/wp-admin/post.php?post={$img['id']}&action=edit&image-editor")?>" target="_blank">Edit</a>
					</div>
				</li>
		    <?php } ?>
		</ul>
	</div>

	<?php
}


function get_upload_view($post){

	?>

	<div class="wrapper" style="overflow: hidden;">
		<input name="save" type="submit" class="button button-primary button-large" onclick="Main.openFileUpload(); return false;" value="Add New Image">
	</div>
	<?php

}

function get_no_images_view(){
	?>
	<p class="howto"> ... there aren't any images yet.</p>
	<?php
}
