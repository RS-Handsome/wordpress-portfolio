<?php
/**
 * Plugin Name: Threepress 
 * Plugin URI: https://threepress.shop
 * Version: 1.6.2
 * Description: Generate 3D gallery shortcodes powered by three.js
 * Text Domain: threepress
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

/*
Threepress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Threepress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Threepress. If not, see https://www.gnu.org/licenses/.
*/


if ( !defined('ABSPATH') ) { 
    die;
}

if ( !defined('DS') ) { define( 'DS', DIRECTORY_SEPARATOR ); }

require_once( ABSPATH . 'wp-includes/pluggable.php' );

// require_once( __DIR__ . '/inc/gallery-form.php' );

$threepress_dir = plugins_url( '', __FILE__ );

$threepress_version = '1.6.2';

$threepress_settings = [];

if ( !class_exists( 'Threepress' ) ) {

	abstract class Threepress{

	    public static function activate(){
			global $wpdb;

	    	$sql = $wpdb->prepare('SHOW TABLES LIKE "threepress_shortcodes"', array() );
	    	$has_table = $wpdb->query( $sql );
	    	
	    	if( $has_table ){  // Threepress has been previously activated

				// Threepress::LOG('reactivating Threepress; skipping init sequence');

			}else{ // Threepress install procedures

				// database
		    	$sql = $wpdb->prepare('
		    		CREATE TABLE IF NOT EXISTS threepress_shortcodes (
		    		id int(11) NOT NULL auto_increment PRIMARY KEY,
		    		author_key int(11),
		    		name varchar(255),
		    		created datetime,
		    		edited datetime,
		    		shortcode text )');
		    	$results = $wpdb->query( $sql );

		    	$starters = new stdClass();
		    	// $starters->bmw = [
		    	// 	'bmw', 
		    	// 	'Sample BMW for Threepress', 
		    	// 	'BMW E36 Low Poly by <a target="_blank" href="https://sketchfab.com/marooned3d">Constantine Tvalashvili</a> is licensed under <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank">CC BY 4.0</a>'
		    	// ];
		    	$starters->flying_car = [
		    		'flying_car',
		    		'Sample Car for Threepress',
		    		'Flying car by (Discord) TrohanPickle#9239'
		    	];

		    	foreach ($starters as $key => $value) {
		    		Threepress::load_starter( $value[0], $value[1], $value[2] );
		    	}

	    	}

	    }


 	    public static function global_scripts() {
 	    	global $threepress_version;
    		wp_enqueue_style( 
				'threepress-global-css', 
				plugins_url('/static/css/global.css?v=' . $threepress_version, __FILE__ ), 
				array()
			);
			wp_enqueue_style( 
				'threepress-modal-css', 
				plugins_url('/static/css/modal.css?v=' . $threepress_version, __FILE__ ), 
				array()
			);	    	
    		wp_enqueue_script( 
				'threepress-global-js', 
				plugins_url( '/static/js/global.js?v=' . $threepress_version, __FILE__ ),
				array('jquery')
			);

			wp_localize_script( 'threepress-global-js', 'THREEPRESS', array(
					'plugin_url' => plugins_url( '', __FILE__ ), //plugins_url(), // '/static/js/global.js', __FILE__
					'home_url' => home_url(),
					'ajaxurl' => admin_url( 'global-ajax.php' ),
					'version' => $threepress_version,
				)
			);

	    }


	    public static function load_starter( $slug, $title, $caption ) {

	    	// starter model
	    	global $wpdb;
	 		$starter = plugins_url( '/starter-models/' . $slug . '.glb', __FILE__ );
			$starter_id = Threepress::sideload( $starter, null, $title, array( 'post_excerpt' => wp_kses_post( $caption ) ) );
			if( gettype( $starter_id ) === 'integer' ){
				$now = Threepress::datetime();
				$sql = $wpdb->prepare(
					'INSERT INTO threepress_shortcodes ( author_key, name, created, edited, shortcode ) 
					VALUES (%d, %s, %s, %s, %s)', 
					get_current_user_id(), 
					sanitize_text_field( $title ),
					$now,
					$now,
					'[threepress model_id=' . $starter_id . ' controls=orbit light=sun light_pos=1,1,0 intensity=5 cam_pos=1,1,1 has_lensflare=true zoom_speed=5 rotate_scene=true rotate_speed=3 bg_color=linear-gradient(45deg,lightblue,white)]' 
				);
				$res = $wpdb->query( $sql );

			}else{
				Threepress::LOG( $id ); // error
			} 
	    }


	    public static function base_scripts() {
	    	global $threepress_version;
    		wp_enqueue_script( 
				'threepress-base-js', 
				plugins_url( '/static/js/init_base.js?v=' . $threepress_version, __FILE__ ),
				array() 
			);
	    }


	    public static function admin_scripts() {
	    	global $threepress_version;
    		wp_enqueue_script( 
				'threepress-admin-js', 
				plugins_url( '/static/js/init_admin.js?v=' . $threepress_version, __FILE__ ),
				array()
			);

	    }


	    public static function admin_styles() {
	    	global $threepress_version;
			wp_enqueue_style( 
				'threepress-admin-css', 
				plugins_url('/static/css/admin.css?v=' . $threepress_version, __FILE__ ), 
				array()
			);

	    }


	    public static function filter_modules( $tag, $handle, $src ) {
	    	$defer_modules = ['threepress-base-js', 'threepress-admin-js', 'threepress-posts-js', 'threepress-lib-js'];
		    if ( !in_array($handle, $defer_modules ) ){
		        return $tag;		    	
		    }
		    $tag = '<script type="module" src="' . $src . '" defer="defer"></script>';
		    return $tag;
		}


	    public static function options_page() {
			$threepress_page_title = 'Threepress';
			$threepress_menu_title = 'Threepress';
			$threepress_capability = 'administrator';
			$threepress_menu_slug = 'inc/admin.php';

			add_menu_page(
			    $threepress_page_title,
			    $threepress_menu_title,
			    $threepress_capability,
			    plugin_dir_path(__FILE__) . $threepress_menu_slug,
			    null,
			    false,
			    20
			);	
	    }


	    public static function shortcode( $attr, $content, $name ){

	    	$model_id = (int)$attr['model_id'];
	    	if( isset( $attr['ground_tex_id'] ) ){ 
		    	$ground_tex_id = (int)$attr['ground_tex_id'];
	    	}
	    	if( isset( $attr['ground_map_id'] ) ){ 
		    	$ground_map_id = (int)$attr['ground_map_id'];
	    	}

    		global $wpdb;
    		global $table_prefix;

	    	if( gettype( $model_id ) !== 'integer' ){

	    		$attr['invalid'] = true;

	    	}else{

	    		$sql = $wpdb->prepare('SELECT * FROM ' . $table_prefix . 'posts WHERE id=%d', $model_id );
	    		$results = $wpdb->get_results( $sql );
				if( !empty( $results ) ){
					$attr['model'] = $results[0];
				}

	    	}

	    	if( isset( $ground_tex_id ) ){
		    	if( gettype( $ground_tex_id ) !== 'integer' ){

		    		// no ground texture

		    	}else{

		    		$sql = $wpdb->prepare('SELECT * FROM ' . $table_prefix . 'posts WHERE id=%d', $ground_tex_id );
		    		$results = $wpdb->get_results( $sql );
		    		if( count( $results ) ){
			    		$attr['ground_tex_guid'] = $results[0]->guid;
		    		}

		    	}
		    }

		    if( isset( $ground_map_id ) ){
		    	if( gettype( $ground_map_id ) !== 'integer' ){

		    		// no ground map

		    	}else{

		    		$sql = $wpdb->prepare('SELECT * FROM ' . $table_prefix . 'posts WHERE id=%d', $ground_map_id );
		    		$results = $wpdb->get_results( $sql );
		    		if( count( $results ) ){
			    		$attr['ground_map_guid'] = $results[0]->guid;
		    		}

		    	}
		    }

	    	$id = '';

	    	if( isset( $attr['name'] ) ){
	    		$id = 'id="threepress-gallery-' . $attr['name'] . '"';
	    	}

	    	if( isset( $attr['model'] ) ){
		    	$attr['model']->post_excerpt = ''; // shim - it can contain values that break JSON.parse - fix later
	    	}

	    	// Threepress::LOG( $attr );

	    	return '
	    	<div ' . $id . ' class="threepress-gallery">
	    		<pre class="threepress-gallery-data">' . json_encode($attr) . '</pre>
	    	</div>';

	    }


	    public static function shortcode_world( $attr, $content, $name ){

    		return '<div id="threepress-world" class="threepress"></div>';

	    }



	    // ajax
	    public static function fill_library(){
			global $wpdb;
			global $table_prefix;
			$sql = $wpdb->prepare('SELECT * FROM ' . $table_prefix . 'posts 
				WHERE post_type="attachment" AND guid LIKE "%.glb%" ORDER BY id DESC');

			$rows = $wpdb->get_results( $sql );
			wp_send_json( $rows );

	    }


	    public static function fill_gallery(){

			global $wpdb;
			$id = get_current_user_id();

			$sql2 = $wpdb->prepare('SELECT * FROM threepress_shortcodes WHERE author_key=%d ORDER BY edited DESC', $id);
			$rows = $wpdb->get_results( $sql2 );
			wp_send_json( $rows ); 

		}


		 public static function delete_gallery(){

			global $wpdb;
			$response = new stdClass();
			$id = $_POST['id'];
			if( !is_numeric($id) ){
				$response->success = false;
				$response->msg ='invalid id';
			}else{
				$sql = $wpdb->prepare('DELETE FROM threepress_shortcodes WHERE id=%d', (int)$id );
				$res = $wpdb->query( $sql );
				$response->success = true;				
			}

			wp_send_json( $response );

		}


	    public static function save_shortcode(){

	    	global $wpdb;

	    	$res = new stdClass();
	    	$res->success = false;

	    	$gallery = new stdClass();
	    	$gallery->datetime = Threepress::datetime();
	    	$gallery->user_id = get_current_user_id();
	    	$gallery->name = sanitize_text_field( $_POST['name'] );
	    	$gallery->shortcode = sanitize_text_field( $_POST['shortcode'] );

	    	// Threepress::LOG( 'saving:' . $gallery->shortcode );

	    	// edit
	    	if( $_POST['shortcode_id'] ){

		    	if( !is_numeric( $_POST['shortcode_id'] ) ){
		    		wp_die( json_encode($res) );
		    	}

	    		$sql = $wpdb->prepare('UPDATE threepress_shortcodes SET name=%s, edited=%s, shortcode=%s WHERE author_key=%d AND id=%d', $gallery->name, $gallery->datetime, $gallery->shortcode, $gallery->user_id, $_POST['shortcode_id']);
	    		$results = $wpdb->query( $sql );
	    		
	    		if( $results ) $gallery->id = $_POST['shortcode_id'];
    		
    		// create
	    	}else{ 

		    	$results = $wpdb->insert('threepress_shortcodes', array(
		    		'author_key' => $gallery->user_id,
		    		'name' => $gallery->name,
		    		'edited' => $gallery->datetime,
		    		'created' => $gallery->datetime,
		    		'shortcode' => $gallery->shortcode,
		    	));

		    	$gallery->created = $gallery->datetime;
			    $gallery->id = $wpdb->insert_id;

	    	}

	    	$gallery->edited = $gallery->datetime;
	    	$res->gallery = $gallery;

	    	$res->success = true;

	    	wp_send_json( $res );

		}


	    public static function get_model( $direct_id ){
			global $wpdb;

			$res = new stdClass();
			$res->success = false;

			$direct = isset( $direct_id ) && is_numeric( $direct_id );

			if( $direct  ){
				$id = $direct_id;
			}else if( isset( $_POST['id'] ) ){
				$id = $_POST['id'];
			}

			if( isset( $id ) && is_numeric($id) ){
				$post = get_post( (int)$id );
				if( $post ){
					$res->success = true;
					$res->model = $post;
				}
			}

			if( $direct ){
				return $res;
			}else{
				wp_send_json( $res );
			}

		}




	    public static function get_image( $direct_id ){
			global $wpdb;

			$res = new stdClass();
			$res->success = false;

			$direct = isset( $direct_id ) && is_numeric( $direct_id );

			if( $direct  ){
				$id = $direct_id;
			}else if( isset( $_POST['id'] ) ){
				$id = $_POST['id'];
			}

			if( isset( $id ) && is_numeric( $id ) ){
				$post = get_post( (int)$id );
				if( $post ){
					$res->success = true;
					$res->image = $post;
				}
			}

			if( $direct ){
				return $res;
			}else{
				wp_send_json( $res );
			}

		}


		public static function allow_glb( $mimes ){
			$mimes['glb'] = 'application/octet-stream';
			return $mimes;
		}



		public static function LOG( $msg ){

			if( !file_exists( __DIR__ . '/.threepress-log.txt') ){
				// return;
			}

			$type = gettype( $msg );
			if( $type  === 'object' || $type === 'array' ){
				$msg = '(' . $type . ')
' . json_encode($msg, JSON_PRETTY_PRINT);
			}
		    $logfile = __DIR__ . '/.threepress-log.txt';
		    // file_put_contents($logfile, date('M:D:H:i') . ':
// ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
		    file_put_contents($logfile, $msg . PHP_EOL, FILE_APPEND | LOCK_EX);

		}	


		public static function datetime(){
			return gmdate( 'Y-m-d H:i:s', time() );
		}


		public static function sideload( $file, $post_id = 0, $desc = null, $post_data = null ){

			if( empty( $file ) ) {
				return new WP_Error( 'error', 'File is empty' );
			}

			if( empty( $post_data ) || $post_data === null ) $post_data = [];

			$file_array = array();

			// Get filename and store it into $file_array
			// Add more file types if necessary
			preg_match( '/[^\?]+\.(glb)\b/i', $file, $matches ); // jpe?g|jpe|gif|png|pdf
			$file_array['name'] = basename( $matches[0] );

			// Download file into temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return new WP_Error( 'error', 'Error while storing file temporarily' );
			}

			// Store and validate
			$id = media_handle_sideload( $file_array, $post_id, $desc, $post_data );

			// Unlink if couldn't store permanently
			if ( is_wp_error( $id ) ) {
				unlink( $file_array['tmp_name'] );
				return new WP_Error( 'error', "Couldn't store upload permanently" );
			}

			if ( empty( $id ) ) {
				return new WP_Error( 'error', "Upload ID is empty" );
			}

			return $id;

		}

		public static function admin_menu_items(){
			global $threepress_page_title;
			echo 
	        "<h1>" . $threepress_page_title . "</h1>
	        <div class='nav-tab-wrapper'>
	    	<a class='nav-tab main-tab' data-section='model-library'>
	    		models
	    	</a>
	    	<a class='nav-tab main-tab' data-section='model-galleries'>
	    		galleries
 	    	</a>
	    	<!--a class='nav-tab main-tab' data-section='model-extensions'>
	    		extensions
 	    	</a-->
	    	<a class='nav-tab main-tab' data-section='tab-worlds'>
	    		worlds
 	    	</a>
	    	<a class='nav-tab main-tab' data-section='model-help'>
	    		help
 	    	</a>";
		}

	    public static function get_settings(){
	    	global $wpdb;
	    	global $table_prefix;
	    	$res = new stdClass();
	    	$sql = $wpdb->prepare('SELECT * FROM ' . $table_prefix . 'options WHERE option_name LIKE "%threepress%"');
	    	$results = $wpdb->get_results( $sql );
	    	Threepress::LOG( $results );

	    	$res->success = true;
	    	$res->results = $results;
	    	wp_send_json( json_encode( $res ) );
	    }

	    // public static function save_settings(){
	    // 	global $threepress_settings;
	    // 	$res = new  stdClass();
	    // 	$res->success = false;
	    // 	foreach( $_POST as $key => $value ){
		   //  	if( array_key_exists( $key, $threepress_settings ) ){
		   //  		Threepress::LOG('saveveve ' . $key . ' ' . $value );
		   //  		if( gettype( $value ) !== $threepress_settings[ $key ] ){
		   //  			Threepress::LOG('invalid type option: ' . $key . ' ' . $value );
		   //  			continue;
		   //  		}
		   //  		// Threepress::LOG('saving ' . $key . ' ' . $value );
		   //  		update_option( 'threepress_' . $key, $value );
		   //  	}
	    // 	}
	    // 	$res->success = true;
	    // 	wp_send_json( json_encode( $res ) );
	    // }



	}

	$has_module = false;

	// --------------------------------------------- admin init

	if( current_user_can('manage_options') ){

		$threepress = strpos( $_SERVER['REQUEST_URI'], 'page=threepress' );
		$admin_ajax = strpos( $_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax' );		
		$post_edit = strpos( $_SERVER['REQUEST_URI'], 'wp-admin/post.php');
		$admin_any = strpos( $_SERVER['REQUEST_URI'], 'wp-admin/');

		if( $admin_ajax ){ // _____ ajax requests

			add_action( 'wp_ajax_fill_library', 'Threepress::fill_library' );
			add_action( 'wp_ajax_fill_gallery', 'Threepress::fill_gallery' );
			add_action( 'wp_ajax_threepress_save_shortcode', 'Threepress::save_shortcode' );
			// add_action( 'wp_ajax_threepress_save_settings', 'Threepress::save_settings' );
			add_action( 'wp_ajax_threepress_delete_gallery', 'Threepress::delete_gallery' );
			add_action( 'wp_ajax_threepress_get_model', 'Threepress::get_model' );
			add_action( 'wp_ajax_threepress_get_image', 'Threepress::get_image' );
			add_action( 'wp_ajax_threepress_settings', 'Threepress::get_settings', 100 );


		}else{

			if( $admin_any ){

				add_action( 'admin_enqueue_scripts', 'Threepress::admin_styles', 100 );
				add_action( 'admin_enqueue_scripts', 'wp_enqueue_media', 100 );


			}

			if( $threepress ){ // _____ admin page

				add_action( 'admin_enqueue_scripts', 'Threepress::admin_scripts', 100 );
				// add_action( 'threepress_gallery_form', 'threepress_gallery_form');
				$has_module = true;

			}

		}

	}

	// -------------------------------------------- global init

	add_action('init', 'Threepress::global_scripts', 100);
	add_action('admin_menu', 'Threepress::options_page');
	add_action('threepress_admin_menu', 'Threepress::admin_menu_items');


	if( !$has_module ) add_action('init', 'Threepress::base_scripts', 100);

	add_filter('script_loader_tag', 'Threepress::filter_modules' , 10, 3);
	add_filter('upload_mimes', 'Threepress::allow_glb');

	add_shortcode('threepress', 'Threepress::shortcode');
	add_shortcode('threepress_world', 'Threepress::shortcode_world');

	register_activation_hook( __FILE__, 'Threepress::activate' );

}



