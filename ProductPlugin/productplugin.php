<?php

/*
* Plugin Name: ProductPlugin
* Plugin URI: http://test.plugin.com
* Description:  Product Plugin for test task
* Version: 1.0
* Author: Oleh Pomazan <oleh.pomazan@gmail.com>
* Author URI: http://github.com/DanmerZ
* License: GPL2
*/

add_action('add_meta_boxes', 'add_metabox');
add_action('save_post', 'product_save_metabox');
add_action('widgets_init','product_widget_init');

function add_metabox() {
	add_meta_box( 'product_meta_box', 'Product', 'product_metabox_handler','post', 'side', 'high');
}

function product_metabox_handler() {
	$value = get_post_custom($post->ID);
	$prod_val = esc_attr($value['product'][0]);
	$desc_val = esc_attr($value['description'][0]);
	$price_val = esc_attr($value['price'][0]);
	$image_val = esc_attr($value['upload_image'][0]);
	$currency_val = esc_attr($value['currency'][0]);

	$currencies = ['USD', 'EUR', 'UAH', 'RUB'];

	$product = '<p><label for="product">Product</label><input type="text" class="widefat" id="product" name="product" value="'.$prod_val.'" /></p>';
	$description = '<p><label for="description">Description</label><input type="text" class="widefat" id="description" name="description" value="'.$desc_val.'" /></p>';
	$price = '<p><label for="price">Price</label><input type="text" class="widefat" id="price" name="price" value="'.$price_val.'" /></p>';	
	
	$currency = '<label for="currency">Currency</label><select name="currency" class="widefat" id="currency"><option selected="selected">'.$currency_val.'</option>';
	foreach ($currencies as $cur) {
		$currency .= '<option>'. $cur .'</option>';
	}
	$currency .= '</select>';
	$image = '<p><label for="upload_image">Image URL</label><input type="text" id="upload_image" name="upload_image" value="'.$image_val.'" /></p>';
	$image .= '<input type="button" id="upload_image_button" value="Upload Image" />';

	echo $product . $description . $price. $currency . $image;
}

function product_save_metabox($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post')) {
		return;
	}

	if ( isset($_POST['product']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['currency']) && isset($_POST['upload_image'])) {
		update_post_meta($post_id, 'product', esc_attr($_POST['product']));
		update_post_meta($post_id, 'description', esc_attr($_POST['description']));
		update_post_meta($post_id, 'price', esc_attr($_POST['price']));
		update_post_meta($post_id, 'currency', esc_attr($_POST['currency']));
		update_post_meta($post_id, 'upload_image', esc_attr($_POST['upload_image']));		
	}
}

function product_widget_init() {
	register_widget(Product_Widget);
}

class Product_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'product_widget', 
			'Product',
			['description' => 'Show product info']
		);
	}

	public function widget($args,$instance) {
		if (is_single()) {	
			echo make_widget_or_block_view(get_the_ID(),'widget');
		}
	}

	public function form($instance) {
		$defaults = ['titles' => 'Product'];
		echo 'Product Widget show something on sidebar!';
	}

	public function update($new_instance, $old_instance) {

	}
}


/**
*  Media uploader
*/

function product_admin_scripts() {    
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_register_script('my-upload', plugin_dir_url( __FILE__ ) . '/main.js', array('jquery','media-upload','thickbox'));
    wp_enqueue_script('my-upload');
}

add_action('admin_print_scripts', 'product_admin_scripts');


/**
* shortcode 
*/
add_action('init', 'product_register_shortcode');

function product_register_shortcode() {
	add_shortcode('product','product_shortcode_func');
}

function product_shortcode_func($args, $content) {
	if (isset($args['id'])) {
		$id = $args['id'];		
	} else {
		$id = get_the_ID();
	}
	return make_widget_or_block_view($id);
}


/**
* Show widget or insert block into page
*/
function make_widget_or_block_view($id, $class='') {
	$product = get_post_meta($id,'product',true);
	$description = get_post_meta($id,'description',true);
	$price = get_post_meta($id,'price',true);
	$currency = get_post_meta($id,'currency',true);
	$image = get_post_meta($id,'upload_image',true);			
	
	$header = '<div itemscope itemtype="http://schema.org/Product" class="'.$class.'" >';
	$prod_html = '<h1 itemprop="name">'.$product.'</h1>';
	$descr_html = '<span itemprop="description">'.$description.'</span>';

	$image_html = '<img src="'. $image .'" itemprop="image" />';

	$offer = '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
	$price_html = '<span itemprop="price">Price: '.$price.'</span>';
	$currency_html = '<span itemprop="priceCurrency"> '. $currency .'</span>';			

	$footer = '</div></div>';
	return $header.$prod_html.$descr_html.'<br />'.$image_html.$offer.$price_html.$currency_html.$footer;
}


?>