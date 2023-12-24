<?php
/**
  * Plugin Name:   HTML WP | A Complete Solution Of Converting Html site to Wordpress Site | Html Page Builder
  * Description: This plugin integrate your HTML to WP Theme.
  * Author: Krishnendu Paul
  * Author URI:       https://html-wp.com/
  * Version: 2.4
  * Copyright 2023 Krishnendu Paul (email :krshpaul@gmail.com)    
*/
   if ( ! defined( 'ABSPATH' ) ) {
   die( 'Invalid request.' );
}
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! function_exists( 'wp_handle_upload' ) )
{
require_once( ABSPATH . 'wp-admin/includes/file.php' );
}
require_once( ABSPATH . 'wp-admin/includes/image.php' );
if ( ! class_exists( 'HTMLWP_Plugin' ) ) :
class HTMLWP_Plugin {

   /**
    * Constructor.
    */
   public function __construct() {
      // register_activation_hook( __FILE__, array( $this , 'activate' ) );
   }


   /**
    * Intialize action after plugin loaded.
    */
   public static function init_actions() {
     
      
      add_action('admin_menu', 'HTMLWP_Plugin::setup_menu');
      add_action('init', 'HTMLWP_Plugin::admin_dynamic_content_structure');
      add_action( 'admin_enqueue_scripts', 'HTMLWP_Plugin::my_admin_page_editor' );
      add_action('admin_enqueue_scripts', 'HTMLWP_Plugin::callback_for_setting_up_scripts');
      add_action('in_admin_header', 'HTMLWP_Plugin::footer_wp_link');
      add_shortcode( 'dnc', 'HTMLWP_Plugin::dynamic_content_show_shortcode' );
      remove_filter( 'the_content', 'wpautop' );
      remove_filter( 'the_excerpt', 'wpautop' );
      //add_action( 'admin_notices', array( 'HTMLWP_Plugin', 'HTML_admin_notices' ) ); 
       if ( is_user_logged_in() ) {
         if ( current_user_can( 'edit_others_posts' ) ) {
       add_action('wp_ajax_htmlwp_upload_file', 'HTMLWP_Plugin::htmlwp_upload_file');
       // add_action('wp_ajax_htmlwp_change_editor', 'HTMLWP_Plugin::htmlwp_change_editor');
       // add_action('wp_ajax_htmlwp_editor_ajax', 'HTMLWP_Plugin::htmlwp_editor_ajax');
       // add_action('edit_form_after_editor',  'HTMLWP_Plugin::vvveb_editor_button',7);
       // add_action('edit_form_after_title',  'HTMLWP_Plugin::vvveb_editor_button_media',7);
       // add_action( 'post_action_vvvejs', 'HTMLWP_Plugin::connected_function' );
       // add_filter( 'page_row_actions', 'HTMLWP_Plugin::add_links',7,6 );
       add_filter('manage_dynamic_content_posts_columns' , 'HTMLWP_Plugin::add_shortcode_dn_columns');
       add_action( 'manage_dynamic_content_posts_custom_column' , 'HTMLWP_Plugin::custom_shortcode_dn_column',7,8 );

       //// post meta repeater custom // 
     //  add_action( 'save_dynamic_content_rep_post_type' , 'HTMLWP_Plugin::manage_dynamic_content_rep_post_type',7,8 );
        add_action( 'add_meta_boxes', 'HTMLWP_Plugin::cust_post_meta_field' );
     add_action('admin_footer', 'HTMLWP_Plugin::dynamic_content_pt_js');
      //  add_action('save_post', 'HTMLWP_Plugin::dynamic_content_structure_save_metabox');
       // add_action('save_post', 'HTMLWP_Plugin::dynamic_content_structure_save_metabox_submit', 10, 2);
        add_action('save_post', 'HTMLWP_Plugin::dynamic_content_structure_save_metabox');

   }
    }
      
      
       add_filter('https_ssl_verify', '__return_false');
      

    }
    
  //After execution we return to default: case in post.php
  //Where the wp_redirect() will be called.
 

   public static function dynamic_content_pt_js()
   {
    ?>
    <script type="text/javascript">
    jQuery('.add_media_cs').click(function(){
        var type=jQuery(this).attr('data-type');
        var thiss = jQuery(this).closest('div');
        var image = wp.media({ 
            title: 'Upload Media',
            // mutiple: true if you want to upload multiple files at once
            multiple: false,
            library: {
            type: [ type ]
    },
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
           // console.log(image_url);
            // Let's assign the url value to the input field
            thiss.find('.media_file').val(image_url);
            thiss.find('.prev_ar').html('<span class="dashicons dashicons-admin-media"></span> <a href="'+image_url+'" target="_blank">'+new URL(image_url).pathname.split('/').pop()+'</a>');
        });
    });
</script>
<script type="text/javascript">
 
    jQuery('body').on('click', '.add_link_f', function(event) {
        jQuery('#wp_link_post').click();
          var thiss = jQuery(this).closest('div');
          jQuery(document).on( 'wplink-close', function( wrap ) {
          var linkAtts = wpLink.getAttrs();
          thiss.find('.link_text').val(linkAtts.href);
          thiss.find('.prev_ar').html('<span class="dashicons dashicons-admin-links"></span> <a href="'+linkAtts.href+'" target="_blank">'+linkAtts.href+'</a>');
      
    });
        });

</script>
    <?php
   }

    /**
    * Attempts to activate the plugin if at least PHP 5.4 & deactivate Woocommerce.
    */
    public static function activate() {
    update_option('plugin_status', 'active');
    }

    public static function deactivate() {
        update_option('plugin_status', 'inactive');
    }
    
    public static function cust_post_meta_field()
    {
        $option_cust_post = get_option('option_cust_post');
       // print_r($option_cust_post);
        $arr_p=array();
        foreach ($option_cust_post['post_type'] as $key => $value) {
           $arr_p[]=$value;
        }
        $option_cust_post_data = get_option('option_cust_post_data');
        $screens = $arr_p;
        foreach ( $screens as $screen ) {
            if($option_cust_post_data[$screen])
            {
                foreach ($option_cust_post_data[$screen] as $screen_key => $screen_value) {
                    add_meta_box(
            'post_box_'.$screen_value['arr_box_single']['field_slug'],                 // Unique ID
            $screen_value['arr_box_single']['field_title'],      // Box title
            'HTMLWP_Plugin::cust_post_meta_field_html',  // Content callback, must be of type callable
            $screen,
            'advanced',
            'default',
            $screen_value['arr_box_single']                        // Post type

        );
                }
            }
        }
    }

    public static function cust_post_meta_field_html($post_ID, $Arg)
    {
        $arr_box_single = $Arg['args'];
        $child_arr=$arr_box_single['child'];
        $child_html=$arr_box_single['data_child_html'];
        // foreach ($child_arr as $ar_rep_key => $arr_rep_child) {
        //     $child_arr[$ar_rep_key]['data_type']='simple';
        //     //print_r(@$arr_rep_child);
        // }


        // echo "<pre>";
        // print_r($Arg['args']);
        // echo "</pre>";

                $doc_rep = new DOMDocument();
                libxml_use_internal_errors(true);
                $doc_rep->loadHTML($child_html);
                libxml_use_internal_errors(false);
                $xpath2 = new DOMXPath($doc_rep);
                $data_rep = $xpath2->query("//*[@data-input-type]");
                foreach($data_rep as $data_rep_simple) {
                if($data_rep_simple->getAttribute("data-name")!='' && $data_rep_simple->getAttribute('data-input-type')!='repeater'):
                    $san_name=sanitize_title($data_rep_simple->getAttribute("data-name"));
                    $san_val=get_post_meta($post_ID, sanitize_title($data_rep_simple->getAttribute("data-name")), true);
                    $arr_box['data']=$data_rep_simple; 
                   $arr_box['data_type']='simple';
                   $arr_box['field_title']=$data_rep_simple->getAttribute("data-name");
                   $arr_box['field_slug']=$san_name;
                    ?>
                <div class="formbold-mb-5 wp-core-ui simpl_box">
                <label class="formbold-form-label"><?php echo $data_rep_simple->getAttribute("data-name"); ?> :  </label>
                <?php self::structure_build($arr_box); ?>
                </div> 
 <?php
                endif;
                }

        ?>
        <?php
    }

    public static function admin_dynamic_content_structure()
    {
    register_post_type( 'dynamic_content',
        array(
            'labels' => array(
                'name' => __( 'Dynamic Content Structure' ),
                'singular_name' => __( 'Dynamic Content Structure' ),
                'add_new' => __('Add New Dynamic Content Structure'),
                'add_new_item' => __('Add New Dynamic Content Structure'),
                'edit_item' => __('Edit Dynamic Content Structure'),
                'new_item' => __('New Dynamic Content Structure'),
            ),
            'public' => true,
            'publicly_queryable'=>false,
             "show_in_menu" => false, 
            'show_in_rest' => true,
             '_builtin'     => false,
        'supports' => array('title'),
        'has_archive' => true,
        'rewrite'   => array( 'slug' => 'my-home-recipes' ),
        // 'taxonomies' => array('cuisines', 'post_tag') // this is IMPORTANT
        )
    );
    add_action('add_meta_boxes', 'HTMLWP_Plugin::dynamic_content_structure_meta_box' );
    
    }
    public static function footer_wp_link(){
    echo '<textarea id="wp_link_post" style="display:none;"></textarea>';

    // Require the core editor class so we can call wp_link_dialog function to print the HTML.
    // Luckly it is public static method ;)
    require_once ABSPATH . "wp-includes/class-wp-editor.php";
    _WP_Editors::wp_link_dialog(); ?>

    <script type="text/javascript">
        /* We need ajaxurl to send ajax to retrive links */
        var ajaxurl = "<?php echo admin_url( 'admin-ajax.php'); ?>";
        jQuery(document).ready(function (){
            jQuery('#wp_link_post').click(function (){
                wpLink.open('wp_link_post'); /* Bind to open link editor! */
            });
        })
    </script><?php

 }
    public static function dynamic_content_structure_meta_box($post){
        global $post;
        
        if($post->post_type=='dynamic_content'):
    add_meta_box('dn_st_meta_box', 'Dynamic Content Structure', 'HTMLWP_Plugin::dynamic_content_structure_class_meta_box', $post->post_type, 'normal' , 'high');
    global $pagenow;

    if ( ( 'post-new.php' != $pagenow ) ):
    add_meta_box("shortcode_dn", "Shortcode", "HTMLWP_Plugin::dynamic_content_shortcodebox", $post->post_type, "side", "high");
        $structure_type = get_post_meta($post->ID, 'structure_type', true); //true ensures you get just 
        $custom_html = get_post_meta($post->ID, 'custom_html', true); //one value instead of an array
        $custom_html_hd = get_post_meta($post->ID, 'custom_html_hd', true); //one value instead of an array
        $custom_html_loop = get_post_meta($post->ID, 'custom_html_loop', true); //one value instead of an array
        $custom_html_foot = get_post_meta($post->ID, 'custom_html_foot', true); //one value instead of an array
        if($custom_html!='')
        {
            add_meta_box('dn_st_data_meta_box', 'Dynamic Content Data', 'HTMLWP_Plugin::dynamic_content_data_meta_box', $post->post_type, 'normal' , 'high');
        }
    endif;
    endif;
    }


    public static function dynamic_content_structure_save_metabox(){ 
        global $post;
       // print_r($_POST['postdata']);
        if(isset($_POST['postdata']) && is_array($_POST['postdata']))
        {
            foreach ($_POST['postdata'] as $postdata_key => $postdata) {
                if(is_array($postdata))
                {
                   $postdata=array_values($postdata);
                   // print_r($postdata);
                }
                update_post_meta($post->ID, $postdata_key, $postdata);
            }
        }
       // die();
        if(isset($_POST["structure_type"])){
             //UPDATE: 
            $meta_element_class = $_POST['structure_type'];
            //END OF UPDATE

            update_post_meta($post->ID, 'structure_type', $meta_element_class);
            //print_r($_POST);
        }
        if(isset($_POST["custom_html"])){
             //UPDATE: 
            $meta_element_class = $_POST['custom_html'];
            //END OF UPDATE
           // if($_POST["structure_type"]=='simple'):
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($_POST['custom_html']);
            libxml_use_internal_errors(false);
            $xpath = new DOMXPath($doc);

            $data_text = $xpath->query("//*[@data-input-type]");
            $dup = $res = [];
            foreach($data_text as $data_text_simple) {
                if($data_text_simple->getAttribute("data-name")!=''):
                $san_name=sanitize_title($data_text_simple->getAttribute("data-name"));
                // $imploded = @implode(',',$san_name);
                // if(in_array($imploded, $dup)){
                //    return false;
                // }
                endif;
             }
           //  endif;

            update_post_meta($post->ID, 'custom_html', $meta_element_class);
            //print_r($_POST);
        }
        if(isset($_POST["custom_html_hd"])){
             //UPDATE: 
            $meta_element_class = $_POST['custom_html_hd'];
            //END OF UPDATE

            update_post_meta($post->ID, 'custom_html_hd', $meta_element_class);
            //print_r($_POST);
        }
        if(isset($_POST["custom_html_loop"])){
             //UPDATE: 
            $meta_element_class = $_POST['custom_html_loop'];
            //END OF UPDATE

            update_post_meta($post->ID, 'custom_html_loop', $meta_element_class);
            //print_r($_POST);
        }
        if(isset($_POST["custom_html_foot"])){
             //UPDATE: 
            $meta_element_class = $_POST['custom_html_foot'];
            //END OF UPDATE

            update_post_meta($post->ID, 'custom_html_foot', $meta_element_class);
            //print_r($_POST);
        }

        return true;
    }


    

    public static function my_admin_page_editor() {

            global $pagenow;

            if ( ( 'post-new.php' === $pagenow ) || ( 'post.php' === $pagenow ) ) {

                $custom_html = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() {
                            wp.codeEditor.initialize( jQuery( ".custom_html" ), %1$s );
                            wp.codeEditor.initialize( jQuery( ".custom_html_hd" ), %1$s );
                            wp.codeEditor.initialize( jQuery( ".custom_html_loop" ), %1$s );
                            wp.codeEditor.initialize( jQuery( ".custom_html_foot" ), %1$s );
                        });',
                        wp_json_encode( $custom_html )
                    )
                );
            }
        }
        public static function dynamic_content_shortcodebox($post){
            ?>
             <div class="formbold-mb-5">
            <label class="formbold-form-label">Shortcode :  </label>

            <input class="formbold-form-input sht_co" type="text" width="100%" readonly value='[dnc id=<?php echo $post->ID; ?>]'>
            </div>
            or use in template
            <div class="formbold-mb-5">
            <label class="formbold-form-label">Template :  </label>

            <input class="formbold-form-input sht_co" type="text" width="100%" readonly value='<?php echo "<?php echo do_shortcode("; ?>"[dnc id=<?php echo $post->ID; ?>]"<?php echo "); ?>" ?>'>
            </div>

            <?php
        }
        public static function dynamic_content_show_shortcode($atts, $content = null)
        {
             $attr = shortcode_atts( array(
                'id' => '',
             ), $atts );
             $id=$attr['id'];
             ob_start();
             //get_template_part('my_form_template');
        $structure_type = get_post_meta($id, 'structure_type', true); //true ensures you get just 
        $custom_html = get_post_meta($id, 'custom_html', true); //one value instead of an array
        $custom_html_hd = get_post_meta($id, 'custom_html_hd', true); //one value instead of an array
        $custom_html_loop = get_post_meta($id, 'custom_html_loop', true); //one value instead of an array
        $custom_html_foot = get_post_meta($id, 'custom_html_foot', true); //one value instead of an array 
       // if($structure_type=='simple'):
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($custom_html);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($doc);

        $data_text = $xpath->query("//*[@data-input-type]");
        foreach($data_text as $data_text_simple) {
         //   echo $data_text_simple->getAttribute("data-input-type");
          //  print_r();
           // echo "<br>";
          // var_dump(strpos($data_text_simple->getAttribute("data-input-type"),'post'));
//           if(strpos($data_text_simple->getAttribute("data-input-type"),'post')!==false)
//        {
//            echo 'hhh';
//        }
            if($data_text_simple->getAttribute("data-name")!='' || (strpos($data_text_simple->getAttribute("data-input-type"),'post')!==false) ):
// echo 'hhh';
                if($data_text_simple->getAttribute("data-child")=='' && $data_text_simple->getAttribute('data-input-type')!='repeater')
            {
                $san_name=sanitize_title($data_text_simple->getAttribute("data-name"));
                $san_val=get_post_meta($id, sanitize_title($data_text_simple->getAttribute("data-name")), true);
                if(!empty($san_val)){ 

                if($data_text_simple->getAttribute("data-input-type")=='image' || $data_text_simple->getAttribute("data-input-type")=='video' || $data_text_simple->getAttribute("data-input-type")=='file'):

                    $data_text_simple->setAttribute('src', $san_val);
                elseif($data_text_simple->getAttribute("data-input-type")=='link'):
                     $data_text_simple->setAttribute('href', $san_val);
                else:
                     $data_text_simple->nodeValue=$san_val;
                endif;
                 }else{ 
                   // echo trim($data_text_simple->nodeValue);
                    }
            }
            elseif($data_text_simple->getAttribute('data-input-type')=='repeater')
            {
                $html_rep=$data_text_simple->C14N();
                $doc_rep = new DOMDocument();
                libxml_use_internal_errors(true);
                $doc_rep->loadHTML($html_rep);
                libxml_use_internal_errors(false);
                $xpath2 = new DOMXPath($doc_rep);
                $data_rep = $xpath2->query("//*[@data-input-type]");

             // print_r($html_rep);

                if($data_text_simple->getAttribute('data-repeater')!='simple')
                {

                // loop start//
                $post_type=$data_text_simple->getAttribute('data-repeater');
                if(post_type_exists($post_type)):
                $arr_post['post_type']=$post_type;
                $arr_post=array('post_type' => $post_type);
                $f_sl=sanitize_title($data_text_simple->getAttribute("data-name"));
                $san_val_param=get_post_meta($id, $f_sl."-".$post_type."-param", true);
                if(empty($san_val_param))
                {
                $arr_param=array('posts_per_page' => -1 , 'post_status' => 'publish');
                }
                else
                {
                  eval("\$arr_param = $san_val_param;");
                }
              
                $arr_param_string=$arr_param;
                $arr_p=array_merge($arr_post,$arr_param_string);
                $loop = new WP_Query( $arr_p );
                if ( $loop->have_posts() ) :
                while ( $loop->have_posts() ) : $loop->the_post(); 
                $id=get_the_ID();
                foreach($data_rep as $data_rep_simple) {
                if($data_rep_simple->getAttribute("data-name")!='' && $data_rep_simple->getAttribute('data-input-type')!='repeater' ):
                $san_name=sanitize_title($data_rep_simple->getAttribute("data-name"));
                $san_val=get_post_meta($id, sanitize_title($data_rep_simple->getAttribute("data-name")), true);
                if(!empty($san_val)){ 
                if($data_rep_simple->getAttribute("data-input-type")=='image' || $data_rep_simple->getAttribute("data-input-type")=='video' || $data_rep_simple->getAttribute("data-input-type")=='file'):
                    $data_rep_simple->setAttribute('src', $san_val);
                elseif($data_rep_simple->getAttribute("data-input-type")=='link'):
                     $data_rep_simple->setAttribute('href', $san_val);
                else:
                     $data_rep_simple->nodeValue=$san_val;
                endif;
                }else{ 
                    if($data_rep_simple->getAttribute("data-input-type")=='post_title')
                    {
                         $data_rep_simple->nodeValue=get_the_title($id);
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_excerpt')
                    {
                         $data_rep_simple->nodeValue=get_the_excerpt($id);
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_link')
                    {
                         $data_rep_simple->setAttribute('href', get_the_permalink($id));
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_content')
                    {
                        if($data_rep_simple->getAttribute("data-input-limit")!='')
                        {
                            $limit=$data_rep_simple->getAttribute("data-input-limit");
                            $data_rep_simple->nodeValue=wp_trim_words(wp_filter_nohtml_kses(get_the_content('..',$id)),$limit);
                        }
                        else{
                            $data_rep_simple->nodeValue=get_the_content($id);
                        }
                        
                         
                    }
                }
                
                elseif(strpos($data_rep_simple->getAttribute("data-input-type"),'post')!==false):
                    if($data_rep_simple->getAttribute("data-input-type")=='post_title')
                    {
                         $data_rep_simple->nodeValue=get_the_title($id);
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_link')
                    {
                         $data_rep_simple->setAttribute('href', get_the_permalink($id));
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_excerpt')
                    {
                         $data_rep_simple->nodeValue=get_the_excerpt($id);
                    }
                    if($data_rep_simple->getAttribute("data-input-type")=='post_content')
                    {
                        if($data_rep_simple->getAttribute("data-input-limit")!='')
                        {
                            $limit=$data_rep_simple->getAttribute("data-input-limit");
                            $data_rep_simple->nodeValue=wp_trim_words(wp_filter_nohtml_kses(get_the_content('..',$id)),$limit);
                        }
                        else{
                            $data_rep_simple->nodeValue=get_the_content($id);
                        }
                    }
                endif;
                }
              
                $doc_rep_html=$doc_rep->saveHTML(); 
                $d = new DOMDocument;
                $d->loadHTML($doc_rep_html);
                $body = $d->getElementsByTagName('body')->item(0);
                $frag = $doc->createDocumentFragment();
                foreach($body->childNodes as $childNode) {
                $ch_str=$d->saveXML($childNode);
                // I append your first HTML fragment (must be valid XML)
                $frag->appendXML( $ch_str );
                $data_text_simple->parentNode->insertBefore( $frag, $data_text_simple );
                }
                endwhile;
                endif;
                wp_reset_postdata();
                wp_reset_query();
                $data_text_simple->parentNode->removeChild($data_text_simple);
                endif;
                /// end post loop
                /// Delete Last ///
                         


                }
                elseif($data_text_simple->getAttribute('data-repeater')=='simple')
                {
                    //echo $id;
                     $san_name_a=sanitize_title($data_text_simple->getAttribute("data-name"));
                    $san_val_a=get_post_meta($id, sanitize_title($data_text_simple->getAttribute("data-name")), true);
                    //print_r($san_val_a);
                if($data_text_simple->getAttribute('data-limit')!='')
                {
                    $data_limit=$data_text_simple->getAttribute('data-limit');
                    $san_val_a= array_splice($san_val_a, 0, intval($data_limit));
                }
                if($data_text_simple->getAttribute('data-order-by')!='' && $data_text_simple->getAttribute('data-order')!='')
                {
                    $data_order_by=sanitize_title($data_text_simple->getAttribute('data-order-by'));
                    $data_order=$data_text_simple->getAttribute('data-order');
                    if($data_order=='asc')
                    {
                        $t=SORT_ASC;
                    }
                    else{
                        $t=SORT_DESC;
                    }
                    //$san_val_a= array_splice($san_val_a, 0, intval($data_limit));
                    $key_values = array_column($san_val_a, $data_order_by); 
                    array_multisort($key_values, $t, $san_val_a);
                }
                    foreach($san_val_a as $san_val_v)
                    {
                         foreach($data_rep as $data_rep_simple) {
                if($data_rep_simple->getAttribute("data-name")!='' && $data_rep_simple->getAttribute('data-input-type')!='repeater'):
              //  $san_name=sanitize_title($data_rep_simple->getAttribute("data-name"));
                $san_val=$san_val_v[sanitize_title($data_rep_simple->getAttribute("data-name"))];
                if(!empty($san_val)){ 
                if($data_rep_simple->getAttribute("data-input-type")=='image' || $data_rep_simple->getAttribute("data-input-type")=='video' || $data_rep_simple->getAttribute("data-input-type")=='file'):
                    $data_rep_simple->setAttribute('src', $san_val);
                elseif($data_rep_simple->getAttribute("data-input-type")=='link'):
                     $data_rep_simple->setAttribute('href', $san_val);
                else:
                     $data_rep_simple->nodeValue=$san_val;
                endif;
                }else{ 
                }
                endif;
                }
              
                $doc_rep_html=$doc_rep->saveHTML(); 
                $d = new DOMDocument;
                $d->loadHTML($doc_rep_html);
                $body = $d->getElementsByTagName('body')->item(0);
                $frag = $doc->createDocumentFragment();
                foreach($body->childNodes as $childNode) {
                $ch_str=$d->saveXML($childNode);
                // I append your first HTML fragment (must be valid XML)
                $frag->appendXML( $ch_str );
                $data_text_simple->parentNode->insertBefore( $frag, $data_text_simple );
                    }
                    }
                    $data_text_simple->parentNode->removeChild($data_text_simple);
                //  
                
                    ////
                
                }
                else{
                    $san_name=sanitize_title($data_text_simple->getAttribute("data-name"));
                $san_val=get_post_meta($id, sanitize_title($data_text_simple->getAttribute("data-name")), true);
             //   print_r($san_val);
                }
               
                
            }


            endif;
         }
         echo apply_filters('the_content', html_entity_decode($doc->saveHTML()));
       // endif;
         return ob_get_clean();   
        }

        public static function dynamic_content_data_meta_box($post){

add_action('add_meta_boxes', 'HTMLWP_Plugin::meta_box_setup');

            

        $structure_type = get_post_meta($post->ID, 'structure_type', true); //true ensures you get just 
        $custom_html = get_post_meta($post->ID, 'custom_html', true); //one value instead of an array
        $custom_html_hd = get_post_meta($post->ID, 'custom_html_hd', true); //one value instead of an array
        $custom_html_loop = get_post_meta($post->ID, 'custom_html_loop', true); //one value instead of an array
        $custom_html_foot = get_post_meta($post->ID, 'custom_html_foot', true); //one value instead of an array 
      //  if($structure_type=='simple'):
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($custom_html);
        libxml_use_internal_errors(false);
        $xpath = new DOMXPath($doc);

        $data_text = $xpath->query("//*[@data-input-type]");
        $count_box=0;
        $data_text_c=1;
        $arr_box=array();
        foreach($data_text as $data_text_simple) {
            if($data_text_simple->getAttribute("data-name")!=''):
            $san_name=sanitize_title($data_text_simple->getAttribute("data-name"));
            $san_val=get_post_meta($post->ID, sanitize_title($data_text_simple->getAttribute("data-name")), true);
            if($data_text_simple->getAttribute("data-child")=='' && $data_text_simple->getAttribute('data-input-type')!='repeater')
            {
               $arr_box[$data_text_c]['data']=$data_text_simple; 
               $arr_box[$data_text_c]['data_type']='simple';
               $arr_box[$data_text_c]['field_title']=$data_text_simple->getAttribute("data-name");
               $arr_box[$data_text_c]['field_slug']=$san_name;
            }
            elseif($data_text_simple->getAttribute('data-input-type')=='repeater')
            {
                $html_rep=$data_text_simple->C14N();
                $doc_rep = new DOMDocument();
                libxml_use_internal_errors(true);
                $doc_rep->loadHTML($html_rep);
                libxml_use_internal_errors(false);
                $xpath2 = new DOMXPath($doc_rep);
                $data_rep = $xpath2->query("//*[@data-input-type]");
                $data_text_rep_c=1;
                $arr_box[$data_text_c]['data_type']='rep';
                $arr_box[$data_text_c]['field_title']=$data_text_simple->getAttribute("data-name");
                $arr_box[$data_text_c]['field_slug']=$san_name;
                foreach($data_rep as $data_rep_simple) {
                if($data_rep_simple->getAttribute("data-name")!='' && $data_rep_simple->getAttribute('data-input-type')!='repeater'):
                    $arr_box[$data_text_c]['child'][$data_text_rep_c]['data']=$data_rep_simple; 
                    $arr_box[$data_text_c]['child'][$data_text_rep_c]['data_type']='rep-child';
                    $arr_box[$data_text_c]['child'][$data_text_rep_c]['field_title']=$data_rep_simple->getAttribute("data-name");
                    $arr_box[$data_text_c]['child'][$data_text_rep_c]['field_slug']=$san_name;
                    $data_text_rep_c++;
                endif;
                }
                if($data_text_simple->getAttribute('data-repeater')!='simple')
                {
                 $arr_box[$data_text_c]['data_rep_type']=$data_text_simple->getAttribute('data-repeater');  
                 $arr_box[$data_text_c]['data_child_html']=$html_rep;  
                }
                else
                {
                  $arr_box[$data_text_c]['data_rep_type']='simple';  
                }

            }
            ?>
            

            <?php $count_box++; endif; ?>
            <?php
            //print_r($data_text_simple);
            // ...
           $data_text_c++; 
        }
        // echo "<pre>";
        // print_r($arr_box);
        // echo "</pre>";
    //    endif;

        foreach ($arr_box as $arr_box_key => $arr_box_single) {
           // echo $arr_box_single['data_type'];
           if($arr_box_single['data_type']=='simple')
           {
            ?>
            <div class="formbold-mb-5 wp-core-ui simpl_box">
            <label class="formbold-form-label"><?php echo $arr_box_single['field_title']; ?> :  </label>
            <?php self::structure_build($arr_box_single); ?>
            </div>
            <?php
           }else
           {
            $data_rep_type=$arr_box_single['data_rep_type'];
           // echo $arr_box_single['field_slug'];
            if($data_rep_type!='simple')
            {
                $f_sl=$arr_box_single['field_slug'];
                ?>
                <div class="formbold-mb-5 wp-core-ui simpl_box">
                <label class="formbold-form-label"><?php echo "Additional Parameter's - ".$data_rep_type; ?> :  </label>
                <textarea class="formbold-form-input" type="text" name="postdata[<?php echo $f_sl."-".$data_rep_type."-param"; ?>]" width="100%" ><?php echo $san_val=get_post_meta($post->ID, $f_sl."-".$data_rep_type."-param", true); ?></textarea>
                </div>

                <?php
               // print_r($arr_box_single);
               
                $option_cust_post = get_option('option_cust_post');

                //Alter the options array appropriately
                $option_cust_post['post_type'][$data_rep_type] = $data_rep_type;

                //Update entire array
                update_option('option_cust_post', $option_cust_post);


                $args = array('arr_box_single'=>$arr_box_single,'post_type'=>$data_rep_type);
                // delete_post_meta($post->ID,'data-rep-post-'.$arr_box_key);
                // update_post_meta($post->ID,'data-rep-post-'.$arr_box_key,json_encode($args));

                 $option_cust_post_data = get_option('option_cust_post_data');

                 if(empty($option_cust_post_data))
                 {
                    $option_cust_post_data=array();
                 }

                // //Alter the options array appropriately
                 @$option_cust_post_data[$data_rep_type][] = $args;

                //  echo "<pre>";
                // print_r(json_encode($option_cust_post_data));
                // echo "</pre>";

                //Update entire array
                //update_option('option_cust_post_data', '');
                update_option('option_cust_post_data', $option_cust_post_data);


                // foreach ($arr_box_single as $key => $arr_box_single_child_value) {
                   
                // }
                


            }
            else
            {
                ?>
                <?php 

$san_val_true=get_post_meta($post->ID, $arr_box_single['field_slug'], true);
 if(!empty($san_val_true))
            {
                
                  ?>
                  <div class="rep_new_box">
                  <div class="formbold-mb-5 wp-core-ui rep_box">
                    <label class="formbold-form-label"><?php echo $arr_box_single['field_title']; ?> :  </label>
                  </div>
                    <div class="repeater">
                        <div class="repeaterbox ">
                        <?php   foreach ($san_val_true as $san_val_true_key => $san_val_true_value) { ?>
                          <div class="contrep rep_v-<?php echo $san_val_true_key; ?>">
                    <?php foreach ($arr_box_single['child'] as $ar_rep_key => $arr_rep_child) {
                        ?>
                        <div class="formbold-mb-5">
                         <label class="formbold-form-label"><?php echo $arr_rep_child['field_title']; ?> :  </label>
                        <?php

                        $arr_san_rep=$san_val_true_value;
                        self::structure_build($arr_rep_child,$arr_san_rep,$san_val_true_key);
                     ?>
                        </div>
                    <?php } ?>
                           <!-- <input type="text" name="cat-slug" /> -->
                           <div class="del_box">
                           <input data-repeater-delete class="button  button-large" type="button" value="Delete"/>
                           </div>
                          </div>
                      <?php } ?>
                        </div>
                        <div class="del_box">
                        <input data-repeater-create class="button  button-large" type="button" value="Add"/> 
                        </div>
                    </div>
                </div>
                  <?php 
               // }
            }else{
            ?>
            <div class="rep_new_box">
            <div class="formbold-mb-5 wp-core-ui rep_box">
            <label class="formbold-form-label"><?php echo $arr_box_single['field_title']; ?> :  </label>
            </div>
            <div class="repeater">
                <div class="repeaterbox " style="display: none;">
                  <div class="contrep rep_v-0">
            <?php foreach ($arr_box_single['child'] as $ar_rep_key => $arr_rep_child) {
                ?>
                <div class="formbold-mb-5">
                 <label class="formbold-form-label"><?php echo $arr_rep_child['field_title']; ?> :  </label>
                <?php
                self::structure_build($arr_rep_child);
             ?>
                </div>
            <?php } ?>
                   <!-- <input type="text" name="cat-slug" /> -->
                   <div class="del_box">
                   <input data-repeater-delete class="button  button-large" type="button" value="Delete"/>
                   </div>
                  </div>
                </div>
                <div class="del_box">
                <input data-repeater-create class="button  button-large" type="button" value="Add"/> 
                </div>
             </div>
            </div>
            <?php } ?>
                <?php
            }
          //  print_r($san_val_true);
           ?>
            <?php
           }
        }




?>
<?php if($count_box==0): ?>
<p class="red_txt" style="text-align:center;">You have no dynamic content yet. Try to add one.</p>
<?php endif; ?>
<script type="text/javascript">
    // jQuery('.add_media_cs').click(function(){
    //     var type=jQuery(this).attr('data-type');
    //     var thiss = jQuery(this).closest('div');
    //     var image = wp.media({ 
    //         title: 'Upload Media',
    //         // mutiple: true if you want to upload multiple files at once
    //         multiple: false,
    //         library: {
    //         type: [ type ]
    // },
    //     }).open()
    //     .on('select', function(e){
    //         // This will return the selected image from the Media Uploader, the result is an object
    //         var uploaded_image = image.state().get('selection').first();
    //         var image_url = uploaded_image.toJSON().url;
    //        // console.log(image_url);
    //         // Let's assign the url value to the input field
    //         thiss.find('.media_file').val(image_url);
    //         thiss.find('.prev_ar').html('<span class="dashicons dashicons-admin-media"></span> <a href="'+image_url+'" target="_blank">'+new URL(image_url).pathname.split('/').pop()+'</a>');
    //     });
    // });
</script>
<script type="text/javascript">
 
    // jQuery('body').on('click', '.add_link_f', function(event) {
    //     jQuery('#wp_link_post').click();
    //       var thiss = jQuery(this).closest('div');
    //       jQuery(document).on( 'wplink-close', function( wrap ) {
    //       var linkAtts = wpLink.getAttrs();
    //       thiss.find('.link_text').val(linkAtts.href);
    //       thiss.find('.prev_ar').html('<span class="dashicons dashicons-admin-links"></span> <a href="'+linkAtts.href+'" target="_blank">'+linkAtts.href+'</a>');
      
    // });
    //     });

</script>
<?php
        }
public static function meta_box_setup()
{
    add_meta_box("shortcode_dn_pot", "xxx", "HTMLWP_Plugin::rm_register_meta_box", 'dynamic_content', "normal", "high");
}

        public function dynamic_content_structure_pt_meta_box($post_ID, $Arg)
        {
            $arr_box_single = $Arg['args']['arr_box_single'];

//print_r($arr_box_single);

            ?>

            <?php 

$san_val_true=get_post_meta($post_ID, $arr_box_single['field_slug'], true);
 if(!empty($san_val_true))
            {
                
                  ?>
                  <div class="rep_new_box">
                  <div class="formbold-mb-5 wp-core-ui rep_box">
                    <label class="formbold-form-label"><?php echo $arr_box_single['field_title']; ?> :  </label>
                  </div>
                    <div class="repeater">
                        <div class="repeaterbox ">
                        <?php   foreach ($san_val_true as $san_val_true_key => $san_val_true_value) { ?>
                          <div class="contrep rep_v-<?php echo $san_val_true_key; ?>">
                    <?php foreach ($arr_box_single['child'] as $ar_rep_key => $arr_rep_child) {
                        ?>
                        <div class="formbold-mb-5">
                         <label class="formbold-form-label"><?php echo $arr_rep_child['field_title']; ?> :  </label>
                        <?php

                        $arr_san_rep=$san_val_true_value;
                        self::structure_build($arr_rep_child,$arr_san_rep,$san_val_true_key);
                     ?>
                        </div>
                    <?php } ?>
                           <!-- <input type="text" name="cat-slug" /> -->
                           <div class="del_box">
                           <input data-repeater-delete class="button  button-large" type="button" value="Delete"/>
                           </div>
                          </div>
                      <?php } ?>
                        </div>
                        <div class="del_box">
                        <input data-repeater-create class="button  button-large" type="button" value="Add"/> 
                        </div>
                    </div>
                </div>
                  <?php 
               // }
            }else{
            ?>
            <div class="rep_new_box">
            <div class="formbold-mb-5 wp-core-ui rep_box">
            <label class="formbold-form-label"><?php echo $arr_box_single['field_title']; ?> :  </label>
            </div>
            <div class="repeater">
                <div class="repeaterbox " style="display: none;">
                  <div class="contrep rep_v-0">
            <?php foreach ($arr_box_single['child'] as $ar_rep_key => $arr_rep_child) {
                ?>
                <div class="formbold-mb-5">
                 <label class="formbold-form-label"><?php echo $arr_rep_child['field_title']; ?> :  </label>
                <?php
                self::structure_build($arr_rep_child);
             ?>
                </div>
            <?php } ?>
                   <!-- <input type="text" name="cat-slug" /> -->
                   <div class="del_box">
                   <input data-repeater-delete class="button  button-large" type="button" value="Delete"/>
                   </div>
                  </div>
                </div>
                <div class="del_box">
                <input data-repeater-create class="button  button-large" type="button" value="Add"/> 
                </div>
             </div>
            </div>
            <?php } ?>
            <?php
        }


        public static function structure_build($data_wh,$rep_value=null,$san_val_true_key=null)
        {
            //print_r($data_wh);
            global $post;
            $data_text_simple=$data_wh['data'];
            $san_name=sanitize_title($data_text_simple->getAttribute("data-name"));
            if($data_wh['data_type']=='simple'):
            $san_val=get_post_meta($post->ID, sanitize_title($data_text_simple->getAttribute("data-name")), true);
        else:
            @$san_val=$rep_value[sanitize_title($data_text_simple->getAttribute("data-name"))];
            endif;


            if(!empty($san_val_true_key))
            {
                $i_key=$san_val_true_key;
            }
            else
            {
                $i_key=0;
            }



            if($data_text_simple->getAttribute("data-input-type")=='text'): ?>
            <input class="formbold-form-input" type="text" name="postdata<?php if($data_wh['data_type']=='rep-child'){ echo '['.$data_wh['field_slug'].']'; } ?><?php if($data_wh['data_type']=='rep-child'){ echo '['.$i_key.']'; } ?>[<?php echo $san_name; ?>]" width="100%"  value="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->nodeValue); } ?>">
            <?php elseif($data_text_simple->getAttribute("data-input-type")=='textarea'): ?>
            
            <?php 
                $id = $san_name.'-'.$i_key;
                if($data_wh['data_type']=='rep-child'){ 
                    $name = 'postdata['.$data_wh['field_slug'].']['.$i_key.']['.$san_name.']';
                    $class="rep-text";
                }else{
                $name = 'postdata['.$san_name.']';
                $class="simp-text";
                }
                if(!empty($san_val)){ $content = html_entity_decode(stripslashes($san_val)); }else{ $content = esc_textarea(stripslashes(trim($data_text_simple->nodeValue))); }
               // $content = esc_textarea( stripslashes(trim($data_text_simple->nodeValue)) );
                $settings = array('tinymce' => true, 'textarea_name' => $name);
              //  wp_editor($content, $id, $settings);
               if(!empty($san_val)){ wp_editor($content, $id, $settings);  }else{ ?> <textarea class="<?php echo $class; ?>" width="100%" data-editor-type="tinymce" name="<?php echo $name; ?>" id="<?php echo $id; ?>"><?php echo $content; ?></textarea><?php }
            ?>
           
            <?php elseif($data_text_simple->getAttribute("data-input-type")=='image'): ?>
            <input type="hidden" name="postdata<?php if($data_wh['data_type']=='rep-child'){ echo '['.$data_wh['field_slug'].']'; } ?><?php if($data_wh['data_type']=='rep-child'){ echo '['.$i_key.']'; } ?>[<?php echo $san_name; ?>]" class="media_file" width="100%"  value="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>">
            <button class="add_media_cs button button-large" type="button" data-type="image"><span class="dashicons dashicons-format-image"></span> Add Image</button>
            <div class="prev_ar"><span class="dashicons dashicons-admin-media"></span> <a href="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>" target="_blank"><?php if(!empty($san_val)){ echo basename($san_val); }else{ echo basename(trim($data_text_simple->getAttribute("src"))); } ?></a></div>
        <?php elseif($data_text_simple->getAttribute("data-input-type")=='video'): ?>
            <input type="hidden" name="postdata<?php if($data_wh['data_type']=='rep-child'){ echo '['.$data_wh['field_slug'].']'; } ?><?php if($data_wh['data_type']=='rep-child'){ echo '['.$i_key.']'; } ?>[<?php echo $san_name; ?>]" class="media_file" width="100%"  value="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>">
            <button class="add_media_cs button button-large" type="button" data-type="video"><span class="dashicons dashicons-format-video"></span> Add Video</button>
            <div class="prev_ar"><span class="dashicons dashicons-admin-media"></span> <a href="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>" target="_blank"><?php if(!empty($san_val)){ echo basename($san_val); }else{ echo basename(trim($data_text_simple->getAttribute("src"))); } ?></a></div>
            <?php elseif($data_text_simple->getAttribute("data-input-type")=='file'): ?>
            <input type="hidden" name="postdata<?php if($data_wh['data_type']=='rep-child'){ echo '['.$data_wh['field_slug'].']'; } ?><?php if($data_wh['data_type']=='rep-child'){ echo '['.$i_key.']'; } ?>[<?php echo $san_name; ?>]" class="media_file" width="100%"  value="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>">
            <button class="add_media_cs button button-large" type="button" data-type=""><span class="dashicons dashicons-media-text"></span> Add File</button>
             <div class="prev_ar"><span class="dashicons dashicons-admin-media"></span> <a href="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("src")); } ?>" target="_blank"><?php if(!empty($san_val)){ echo basename($san_val); }else{ echo basename(trim($data_text_simple->getAttribute("src"))); } ?></a></div>
             <?php elseif($data_text_simple->getAttribute("data-input-type")=='link'): ?>
            <input class="formbold-form-input link_text" name="postdata<?php if($data_wh['data_type']=='rep-child'){ echo '['.$data_wh['field_slug'].']'; } ?><?php if($data_wh['data_type']=='rep-child'){ echo '['.$i_key.']'; } ?>[<?php echo $san_name; ?>]" type="hidden" width="100%"  value="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("href")); } ?>">
            <button class="add_link_f button  button-large" type="button"><span class="dashicons dashicons-admin-links"></span> Add Link</button>
            <div class="prev_ar"><span class="dashicons dashicons-admin-links"></span> <a href="<?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("href")); } ?>" target="_blank"><?php if(!empty($san_val)){ echo $san_val; }else{ echo trim($data_text_simple->getAttribute("href")); } ?></a></div>
            <?php endif; 
        }
        public static function dynamic_content_structure_class_meta_box($post){
        $structure_type = get_post_meta($post->ID, 'structure_type', true); //true ensures you get just 
        $custom_html = get_post_meta($post->ID, 'custom_html', true); //one value instead of an array
        $custom_html_hd = get_post_meta($post->ID, 'custom_html_hd', true); //one value instead of an array
        $custom_html_loop = get_post_meta($post->ID, 'custom_html_loop', true); //one value instead of an array
        $custom_html_foot = get_post_meta($post->ID, 'custom_html_foot', true); //one value instead of an array

        ?>   
       <!-- <div class="formbold-mb-5">
        <label class="formbold-form-label">Structure Type :  </label>

        <select class="formbold-form-input" name="structure_type" required onchange="toggle_structure()" id="structure_type">
          <option <?php if($structure_type==''): echo 'selected'; endif; ?> value="" >None</option>
          <option <?php if($structure_type=='simple'): echo 'selected'; endif; ?> value="simple" >Simple</option>
          <option <?php if($structure_type=='repeater'): echo 'selected'; endif; ?> value="repeater" >Repeater</option>
        </select>
        </div>-->
        <div class="wr_sec"> 
            <div id="overlay" onclick="off()">
              <div id="text" style="text-align: center;"><span style="font-size:75px;" class="dashicons dashicons-admin-generic"></span><span style="
    margin-left: 62px;
">Edit Structure</span></div>
            </div>
            <div class="formbold-mb-5 st_all d-none" id="st_all">
            <label class="formbold-form-label">Structure (Html) :  </label>

            <textarea name="custom_html" class="custom_html" placeholder="Structure"><?php echo $custom_html; ?></textarea>
            </div>
           <!--  <div class="rep_sec d-none" id="rep_sec">
                <div class="formbold-mb-5 st_header">
                    <label class="formbold-form-label">Structure (Starting Html) :  </label>
                    <textarea name="custom_html_hd" class="custom_html_hd" placeholder="Structure"><?php echo $custom_html_hd; ?></textarea>
                </div>
                <div class="formbold-mb-5 st_header">
                    <label class="formbold-form-label">Structure (Looping Html) :  </label>
                    <textarea name="custom_html_loop" class="custom_html_loop" placeholder="Structure"><?php echo $custom_html_loop; ?></textarea>
                </div>
                <div class="formbold-mb-5 st_header">
                    <label class="formbold-form-label">Structure (Ending Html) :  </label>
                    <textarea name="custom_html_foot" class="custom_html_foot" placeholder="Structure"><?php echo $custom_html_foot; ?></textarea>
                </div>
            </div> -->
        </div>
        
        <script>
            document.getElementById("overlay").style.display = "block";
function on() {
  document.getElementById("overlay").style.display = "block";
}

function off() {
  document.getElementById("overlay").style.display = "none";
}
</script>
       
        <?php
    }

    public static function add_shortcode_dn_columns($columns) {
    return array_merge($columns,
              array('shortcode' => __('Shortcode'),
                    'template_use' =>__( 'Template Use')));
    }


    public static function custom_shortcode_dn_column( $column, $post_id ) {
        switch ( $column ) {
          case 'shortcode':
             echo '<input class="formbold-form-input sht_co" type="text" width="100%" readonly value="'."[dnc id=".$post_id."]".'">';
            break;
          case 'template_use':
           ?>
           <input class="formbold-form-input sht_co" type="text" width="100%" readonly value='<?php echo "<?php echo do_shortcode("; ?>"[dnc id=<?php echo $post_id; ?>]"<?php echo "); ?>" ?>'>
           <?php
            break;
        }
    }

   
    public static function setup_menu() {
   // do_action("admin_dynamic_content_structure");
    add_menu_page('HTML WP', 'HTML WP', 'manage_options', 'HTMLwp-setings', 'HTMLWP_Plugin::setting_page', 'dashicons-html');
    add_submenu_page('HTMLwp-setings', 'Dynamic Content', 'Dynamic Content', 'manage_options', 'edit.php?post_type=dynamic_content', false);
    }

    public static function setting_page(){
       include( plugin_dir_path( __FILE__ ) . 'template/page_upload.php');
    }
    public static function dynamic_content(){
        echo 'gg';
     //  include( plugin_dir_path( __FILE__ ) . 'template/page_upload.php');
    }


    public static function callback_for_setting_up_scripts() {
        
        $screen = get_current_screen();
        global $post;
        if(isset($_GET['page'])):
             $page=sanitize_text_field($_GET['page']);
            if (str_contains(strtolower($page), 'htmlwp') )
           {
            $plugin_url = plugin_dir_url( __FILE__ );
            wp_enqueue_style( 'bootstrapcss', plugins_url( 'template/css/bootstrap.min.css' , __FILE__ ) );
            
            wp_enqueue_style( 'admin', plugins_url( 'template/css/admin.css' , __FILE__ ) );
           }
        endif;
        global $pagenow;
            wp_enqueue_style( 'sweetalertcss', plugins_url( 'template/css/sweetalert.min.css' , __FILE__ ) );
            wp_enqueue_script( 'sweetalertjs', plugins_url( 'template/css/sweetalert.min.js' , __FILE__ ) );
             wp_enqueue_style( 'admin', plugins_url( 'template/css/admin.css' , __FILE__ ) );
        // if((isset($post->post_type)) && ($post->post_type=='dynamic_content')):
        //     wp_enqueue_style( 'admin', plugins_url( 'template/css/admin.css' , __FILE__ ) );
        // endif;

        // Link

        wp_enqueue_script('wplink');
        wp_enqueue_style( 'editor-buttons' );
       

        // Media
         wp_enqueue_media();

        // repeater
         wp_enqueue_script( 'adminjs', plugins_url( 'template/css/admin.js' , __FILE__ ) );

        // Editor
         wp_enqueue_editor();
        
    }
   public static function assetExists($url) {
    //echo $url;
    if (($url == '') || ($url == null)) { return false; }
    $response = wp_remote_head( $url, array( 'timeout' => 65 ) );
    //   print_r($response); echo "<br>";
    $accepted_status_codes = array( 200, 301, 302 );
    if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
        return true;
    }
    return false;
    }
//     public static function htmlwp_change_editor(){
//         if ( isset( $_POST['action'] )
//          && isset( $_POST['nonce'] )
//          && 'htmlwp_change_editor' === $_POST['action']
//          && wp_verify_nonce( $_POST['nonce'], 'htmlwp_change_editor_action' ) ) {

//             update_post_meta( $_POST['post_id'], 'editor_type', $_POST['page_type'] );
//             wp_die();
//         }
// }
// public static function htmlwp_editor_ajax(){
//         if ( isset( $_POST['action'] )
//          && isset( $_POST['nonce'] )
//          && 'htmlwp_editor_ajax' === $_POST['action']
//          && wp_verify_nonce( $_POST['nonce'], 'htmlwp_editor_ajax_action' ) ) {

//             $post_id=$_POST['post_id'];
//             $html=$_POST['html'];
//             $css=$_POST['css'];

//             $body=preg_replace('/<footer(.*?)<\/body>/s', '', preg_replace('/<body>(.*?)<\/header>/s', '', $html));
//             $body = stripslashes($body);
//             update_post_meta( $post_id, '_html_tpl_data', $body );
//            // update_post_meta( $post_id, '_html_tpl_data_css', $css );
//             echo $css;
              


// wp_die();
//             }
//         }
    public static function htmlwp_upload_file(){
        if ( isset( $_POST['action'] )
         && isset( $_POST['nonce'] )
         && 'htmlwp_upload_file' === $_POST['action']
         && wp_verify_nonce( $_POST['nonce'], 'htmlwp_upload_file_action' ) ) {

        if(isset($_POST['theme-name'],$_FILES['file'],$_FILES['file_screenshot']))
        {
            //echo $_FILES['file']['size'];
            //print_r($_FILES['file']);
            $maxsize=wp_max_upload_size();
            $acceptable_zip = array(
        "application/x-rar-compressed", "application/zip", "application/x-zip", "application/octet-stream", "application/x-zip-compressed"
    );
            $acceptable = array(   
        'image/jpeg',
        'image/jpg',
        'image/png'
    );
            if(($_FILES['file']['size'] >= $maxsize) || ($_FILES["file"]["size"] == 0)) {
      //  $errors = 'File too large. File must be less than 2 megabytes.';
        $error=true;
    $error_msg='Zip File too large. File must be less than '.esc_html( size_format( $maxsize ) );
     echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
    }
    elseif(!in_array($_FILES['file']['type'], $acceptable_zip) && (!empty($_FILES["file"]["type"]))) {
    // $errors[] = 'Invalid file type. Only PDF, JPG, GIF and PNG types are accepted.';
         $error=true;
    $error_msg='Invalid file type. Only Zip file accepted';
     echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
}
  elseif(!in_array($_FILES['file_screenshot']['type'], $acceptable) && (!empty($_FILES["file_screenshot"]["type"]))) {
    // $errors[] = 'Invalid file type. Only PDF, JPG, GIF and PNG types are accepted.';
         $error=true;
    $error_msg='Invalid file type. Only JPG and PNG types are accepted.';
     echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
}
    elseif(($_FILES['file_screenshot']['size'] >= $maxsize) || ($_FILES["file_screenshot"]["size"] == 0)) {
      //  $errors = 'File too large. File must be less than 2 megabytes.';
        $error=true;
    $error_msg='Screenshot File too large. File must be less than '.esc_html( size_format( $maxsize ) );
     echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
    }
    else{
      @session_start();
      $template=sanitize_text_field($_POST['theme-name']);
      // Check Template Exist Or Not 
      $error=false;
      $progress=true;
      $next=true;
      $success=false;
      $arr_html_name=array();
      $header_html=array();
      $footer_html=array();
      $blog=array();


      $template_slug = strtolower(str_replace(' ', '', $template));
     
        function rrmdir($dir) {
        if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
          if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") 
               rrmdir($dir."/".$object); 
            else unlink   ($dir."/".$object);
          }
        }
        reset($objects);
        rmdir($dir);
        }
        }
      // Oeration Start
      // 1st - Template
      $progress_msg='Checking Template';
      $theme_base = dirname(dirname(dirname(__FILE__))).'/themes/';
      if($next==true)
      {
        if(is_dir($theme_base.$template_slug))
        {
          $error=true;
          $error_msg='Theme Already Exists';
        }
        else
        {
          // Create Theme
          $progress_msg='Creating Theme Folder';
          $progress=true;
          $error=false;      
          $progress_msg='Created Theme Folder';      
          $next=true;
          // Upload File
          $uploadedfile = $_FILES['file'];
          $upload_name = sanitize_file_name($_FILES['file']['name']);
          $uploads = dirname(__FILE__).'/OriginalHTMLFiles';
          $filepath = $uploads."/$upload_name";
          // sanitizing file name
          if($upload_name)
          {
          // delete folders first
          if ($dh = opendir($uploads)) {
             while (($file = readdir($dh)) !== false) {
              unlink($uploads.'/'.$file);
             }
            }
          rrmdir(dirname(__FILE__).'/OriginalHTMLFiles'.'/unzip');
        
          move_uploaded_file($_FILES['file']['tmp_name'], $filepath);
          
          mkdir($uploads.'/unzip/'.$template_slug, 0777, true);
          // Unzip
          WP_Filesystem();
          $result = unzip_file( $filepath, $uploads.'/unzip/'.$template_slug );
          $check_html_count=0;
          $check_folder_count=0;

           if (is_dir($uploads.'/unzip/'.$template_slug)) {
            if ($dh = opendir($uploads.'/unzip/'.$template_slug)) {
                while (($file = readdir($dh)) !== false) {
               $file_pth=$uploads.'/unzip/'.$template_slug.'/'.$file;
                  if(is_dir($file_pth) && $file != "." && $file != "..")
                   {
                          $file_pth=$uploads.'/unzip/'.$template_slug.'/'.$file; 
                       $file_pth_new=$uploads.'/unzip/'.$template_slug.'/'.$file;
                    $check_folder_count++;
                         if ($dh1 = opendir($file_pth)) {

                    while (($folder = readdir($dh1)) !== false) {
                    
                     
                         // check any html found or not 
                      if(!is_dir($file_pth.'/'.$folder) && (pathinfo($folder)['extension']=='html') && $folder != "." && $folder != "..")
                       {
                       
                        $arr_html_name[]=$folder;
                        $check_html_count++;
                       }
                       
                     }
                   }
                   closedir($dh1);
                   }
                 }
               }
               closedir($dh);
             }
            
             if($check_folder_count==1)
             {
               $next_again=true;
             }
             else
             {
               $error=true;
            $error_msg='Not Valid Folder.Check Folder and upload again.';
             }
         
          // check any html found or not
          if(($check_html_count>0) && isset($file_pth_new))
          {
            //check header & footer
             $file_pth_html_wh=$file_pth_new.'/'.$arr_html_name[0]; 
             $xml_wh = new DOMDocument(); 
             $xml_wh->loadHTMLFile($file_pth_html_wh); 
             $html_full_wh=$xml_wh->saveHtml();
             preg_match("~<body.*?>(.*?)<\/body>~is", $html, $match);
            //  echo $html_full_wh;
             if((preg_match('/<header(.*?)<\/header>/s', $html_full_wh) ) && (preg_match('/<footer(.*?)<\/footer>/s', $html_full_wh)) )
             {
                if($next_again==true)
              {
               mkdir($theme_base.$template_slug, 0777, true);
               move_uploaded_file($_FILES['file_screenshot']['tmp_name'], $theme_base.$template_slug.'/'.'screenshot.png');



            if (is_dir($uploads.'/unzip/'.$template_slug)) {
                if ($dh = opendir($uploads.'/unzip/'.$template_slug)) {
                    while (($file = readdir($dh)) !== false) {
                      $file_pth=$uploads.'/unzip/'.$template_slug.'/'.$file;
                      if(is_dir($file_pth) && $file != "." && $file != "..")
                       {
                      
                          if ($dh1 = opendir($file_pth)) {

                    while (($folder = readdir($dh1)) !== false) {
                    
                      if(is_dir($file_pth.'/'.$folder) && $folder != "." && $folder != "..")
                       {
                        
                        // Create dir in themes
                        mkdir($theme_base.$template_slug.'/'.$folder, 0777, true);
                        $dest=$theme_base.$template_slug.'/'.$folder;
                        $src = $file_pth.'/'.$folder;
                          if ($dh2 = opendir($src)) {
                    while (($file_of_folder1 = readdir($dh2)) !== false) {
                    
                      if($file_of_folder1 != "." && $file_of_folder1 != "..")
                       {
                          $src2 = $file_pth.'/'.$folder.'/'.$file_of_folder1; 
                          $dest2= $dest.'/'.$file_of_folder1; 
                          copy($src2, $dest2); 
                       }
                       if(is_dir($file_of_folder1) && $file_of_folder1 != "." && $file_of_folder1 != "..")
                       {
                         $src2 = $file_pth.'/'.$folder.'/'.$file_of_folder1; 
                         $dest2= $dest.'/'.$file_of_folder1; 
                          if ($dh3 = opendir($src2)) {
                          while (($file_of_folder2 = readdir($dh3)) !== false) {
                             if($file_of_folder2 != "." && $file_of_folder2 != "..")
                       {
                           $src3 = $src2.'/'.$file_of_folder2; 
                           mkdir($dest2, 0777, true);
                           $dest3= $dest2.'/'.$file_of_folder2; 
                           copy($src3, $dest3); 
                           $next=true; 
                       }  
                            
                          }
                          closedir($dh3);
                      }
                       }

                     
                    }
                    closedir($dh2);
                }
                        
                       

                       }
                      
                     
                       }
                    }
                    closedir($dh1);
                }
                       }

                    closedir($dh);
               
                }
             }

            }
            // get html files 
            $html_c=1;
            mkdir($theme_base.$template_slug.'/'.'templates', 0777, true);
            foreach ($arr_html_name as $key => $arr_html_name_val) {
               $arr_html_name_val; 
               $file_pth_html=$file_pth_new.'/'.$arr_html_name_val; 
               $xml = new DOMDocument(); 
               $xml->loadHTMLFile($file_pth_html); 
               $theme_uri=get_theme_root_uri().'/'.$template_slug.'/';
               foreach($xml->getElementsByTagName('link') as $link) { 
                 $oldLink = $link->getAttribute("href");
                 if (filter_var($oldLink, FILTER_VALIDATE_URL) === FALSE) {
                      $filtered_link=esc_url($theme_uri.$oldLink);
                      if (self::assetExists($filtered_link)) { 
                            $link->setAttribute('href', "<?php echo esc_url(get_template_directory_uri().\"/".$oldLink."\"); ?>");
                        }                    
                  }
                  else
                  {
                     $link->setAttribute('href', "<?php echo esc_url(\"".$oldLink."\"); ?>");
                  }
                 
               }
               foreach($xml->getElementsByTagName('script') as $src) { 
                if($src->hasAttribute("src"))
                {
                 $oldLinksrc = $src->getAttribute("src");
                 if (filter_var($oldLinksrc, FILTER_VALIDATE_URL) === FALSE) {
                     $filtered_linksrc=esc_url($theme_uri.$oldLinksrc);
                      if (self::assetExists($filtered_linksrc)) { 
                             $src->setAttribute('src', "<?php echo esc_url(get_template_directory_uri().\"/".$oldLinksrc."\"); ?>");
                        }  
                
                 }
                 else
                  {
                     $src->setAttribute('src', "<?php echo esc_url(\"".$oldLinksrc."\"); ?>");
                  }
               }
               }
               foreach($xml->getElementsByTagName('img') as $imgsrc) { 
                if($imgsrc->hasAttribute("src"))
                {
                 $oldLinkimgsrc = $imgsrc->getAttribute("src");
                 if (filter_var($oldLinkimgsrc, FILTER_VALIDATE_URL) === FALSE) {
                    $filtered_linkimgsrc=esc_url($theme_uri.$oldLinkimgsrc);
                     if (self::assetExists($filtered_linkimgsrc)) { 
                        if(isset($_POST['page_type']) && $_POST['page_type']=='builder'){
                            $imgsrc->setAttribute('src', $filtered_linkimgsrc);
                        }else
                        {
                            $imgsrc->setAttribute('src', "<?php echo esc_url(get_template_directory_uri().\"/".$oldLinkimgsrc."\"); ?>");
                        }
                        } 
                  
                 }
                 else
                  {

                     $imgsrc->setAttribute('src', "<?php echo esc_url(\"".$oldLinkimgsrc."\"); ?>");
                  }
               }
               }
               $html_full=$xml->saveHtml();

               
              $html_full=str_replace("</head>","<?php wp_head(); ?> </head>",$html_full);
              $html_full=str_replace("</body>","<?php wp_footer(); ?> </body>",$html_full);



               preg_match('/<!DOCTYPE html>(.*?)<\/header>/s', $html_full, $header);
               preg_match('/<footer(.*?)<\/html>/s', $html_full, $footer);
               

               if($html_c==1)
               {
                $header_html[]=esc_html($header[0]);
                $footer_html[]=esc_html($footer[0]);
               }
               $body=preg_replace('/<footer(.*?)<\/html>/s', '', preg_replace('/<!DOCTYPE html>(.*?)<\/header>/s', '', $html_full));
              
               
               // create templates
               $dest_tpl=$theme_base.$template_slug.'/'.'templates/tpl-'.sanitize_file_name(basename($arr_html_name_val,".html").'.php');
               
               $fp=fopen($dest_tpl,'w');
               $tpl_name=ucfirst(str_replace('-',' ',basename($arr_html_name_val,".html")));

              



               $tpl_data='<?php /* Template Name: Template '.$tpl_name.' 

             
               */ 
               get_header(); ?> '.$body."
               <?php
              // get_sidebar();
               get_footer(); 
               ?>
               ";
               fwrite($fp, urldecode(htmlspecialchars_decode($tpl_data)));
               fclose($fp);

               // create pages and assign templates
              
               $new_page = array(
                    'post_type'     => 'page',        // Post Type Slug eg: 'page', 'post'
                    'post_title'    => $tpl_name, // Title of the Content
                    'post_content'  => '', // Content
                    'post_status'   => 'publish',     // Post Status
                    'post_author'   => 1,         // Post Author ID
                );

                if (get_page_by_title( $tpl_name ) == null) { // Check If Page Not Exits
                    $new_page_id = wp_insert_post($new_page);
                    update_post_meta( $new_page_id, '_wp_page_template', 'templates/tpl-'.basename($arr_html_name_val,".html").'.php' );
                    update_post_meta( $new_page_id, '_html_tpl', $arr_html_name_val );
                    if(isset($_POST['page_type']) && $_POST['page_type']=='builder'){
                    update_post_meta( $new_page_id, '_html_tpl_data', $body );
                    update_post_meta( $new_page_id, 'editor_type', 'builder' );
                    }
                }
               $html_c++;
 
              
            }
            $dest_head=$theme_base.$template_slug.'/'.'header.php';
            //  header create
            $fp=fopen($dest_head,'w');
            fwrite($fp, urldecode(htmlspecialchars_decode($header_html[0])));
            fclose($fp);
            if(isset($_POST['page_type']) && $_POST['page_type']=='builder'){
            $dest_head_builder=$theme_base.$template_slug.'/'.'header-builder.php';
            //  header create
            $fp=fopen($dest_head_builder,'w');

            $dom = new DOMDocument();
            $dom->loadHTML($header_html[0]);
            $nodes = $dom->getElementsByTagName("*");

            foreach ($nodes as $node) {
                   $node->setAttribute('data-gjs-editable','false');
                   $node->setAttribute('data-gjs-removable','false');
                   $node->setAttribute('data-gjs-draggable','false');
            }
            $new_header_editor= $dom->saveHTML($node);

            fwrite($fp, urldecode(htmlspecialchars_decode($new_header_editor)));
            fclose($fp);
            }
            $dest_foot=$theme_base.$template_slug.'/'.'footer.php';
            //  footer create
            $fp=fopen($dest_foot,'w');
            fwrite($fp, urldecode(htmlspecialchars_decode($footer_html[0])));
            fclose($fp);
            if(isset($_POST['page_type']) && $_POST['page_type']=='builder'){
            $dest_foot_builder=$theme_base.$template_slug.'/'.'footer-builder.php';
            //  footer create
            $fp=fopen($dest_foot_builder,'w');

            $domx = new DOMDocument();
            $domx->loadHTML($footer_html[0]);
            $nodes = $domx->getElementsByTagName("*");

            foreach ($nodes as $node) {
                   $node->setAttribute('data-gjs-editable','false');
                   $node->setAttribute('data-gjs-removable','false');
                   $node->setAttribute('data-gjs-draggable','false');
            }
            $new_footer_editor= $domx->saveHTML($node);

            fwrite($fp, urldecode(htmlspecialchars_decode($new_footer_editor)));

            //fwrite($fp, urldecode(htmlspecialchars_decode($footer_html[0])));
            fclose($fp);
            }
            $dest_style=$theme_base.$template_slug.'/'.'style.css';
            
            global $hd;
            global $ft;
            global $hd_dest;
            global $foot_dest;
            global $theme_name;
            $hd=urldecode(htmlspecialchars_decode($header_html[0]));
            $ft=urldecode(htmlspecialchars_decode($footer_html[0]));
            $hd_dest=$dest_head;
            $foot_dest=$dest_foot;
            $theme_name=$template_slug;


            // style create
            $fp=fopen($dest_style,'w');
            $style_data="/*
            Theme Name: ".$template."
            Author: HTML WP
            Description: Theme ".$template." created by HTML WP.you can change this after theme creation.
            Version: 1.0
            */";
            fwrite($fp, urldecode(htmlspecialchars_decode($style_data)));
            fclose($fp);

            // template-part folder
            mkdir($theme_base.$template_slug.'/'.'template-parts', 0777, true);
            $dest_content=$theme_base.$template_slug.'/'.'template-parts/content.php';
             // content create
            $fp=fopen($dest_content,'w');
            $content_data='<?php
                        /**
                         * Template part for displaying posts
                         */

                        ?>

                        <article id="post-<?php the_ID(); ?>" class="entry">
                          <header class="entry-header">
                            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                          </header>

                          <div class="entry-content">
                            <?php the_content(); ?>
                          </div>
                        </article>';
            fwrite($fp, urldecode(htmlspecialchars_decode($content_data)));
            fclose($fp);

              $dest_index=$theme_base.$template_slug.'/'.'index.php';
               // index create
              $fp=fopen($dest_index,'w');
              
               $index_data='<?php
              /**
               * The main template file
               */

              get_header();
              ?>

              <main id="main" class="site-main" role="main">

              <?php
              if ( have_posts() ) : while ( have_posts() ) : the_post();

                  get_template_part( "template-parts/content", get_post_type() );

                endwhile;

                the_posts_pagination( array(
                  "prev_text" => __( "Previous page" ),
                  "next_text" => __( "Next page" ),
                ) );

              endif;
              ?>

              </main>

              <?php
             // get_sidebar();
              get_footer();';
            
             
              fwrite($fp, urldecode(htmlspecialchars_decode($index_data)));
              fclose($fp);

              $dest_404=$theme_base.$template_slug.'/'.'404.php';
             // 404 create
            $fp=fopen($dest_404,'w');
            $data404='<?php
              /**
               * The template for displaying 404 pages
               *
               */

              get_header();
              ?>

              <main id="main" class="site-main" role="main">

                <section class="page-section">
                  <header class="page-header">
                    <h1>404</h1>
                  </header>

                  <div class="page-content">
                    <p>Page not found.</p>
                  </div>
                </section>

              </main>

              <?php
              get_footer();';
              fwrite($fp, urldecode(htmlspecialchars_decode($data404)));
              fclose($fp);

              $dest_comments=$theme_base.$template_slug.'/'.'comments.php';
             // comments create
            $fp=fopen($dest_comments,'w');
            $comments_data='<?php
            /**
             * The template for displaying comments
             * 
             */

            if ( post_password_required() ) {
              return;
            }
            ?>

            <div id="comments" class="comments-area">

              <?php
              if ( have_comments() ) : ?>
                <h2 class="comments-title">Comments</h2>

                <?php the_comments_navigation(); ?>

                <ul class="comment-list">
                  <?php
                  wp_list_comments( array(
                    \'short_ping\' => true,
                  ) );
                  ?>
                </ul>

                <?php
                the_comments_navigation();

                // If comments are closed and there are comments, let\'s leave a little note, shall we?
                if ( ! comments_open() ) : ?>
                  <p class="no-comments">Comments are closed</p>
                <?php
                endif;

              endif;

              comment_form();
              ?>

            </div>';
            fwrite($fp, urldecode(htmlspecialchars_decode($comments_data)));
            fclose($fp);


            $dest_page=$theme_base.$template_slug.'/'.'page.php';
             // page create
            $fp=fopen($dest_page,'w');
            $page_data='<?php
                /**
                 * The template for displaying all pages
                 *
                 */

                get_header();
                ?>

                <main id="main" class="site-main" role="main">

                  <?php
                  while ( have_posts() ) : the_post();

                    get_template_part( \'template-parts/content\', \'page\' );

                    // If page are open or we have at least one comment, load up the comment template.
                   

                  endwhile; 
                  ?>

                </main>

                <?php

                get_footer();';
            fwrite($fp, urldecode(htmlspecialchars_decode($page_data)));
            fclose($fp);

             $dest_functions=$theme_base.$template_slug.'/'.'functions.php';
             // functions create
            $fp=fopen($dest_functions,'w');
            $functions_data="<?php
                        /**
                         * Functions and definitions
                         *
                         */

                        /*
                         * Let WordPress manage the document title.
                         */
                        add_theme_support( 'title-tag' );

                        /*
                         * Enable support for Post Thumbnails on posts and pages.
                         */
                        add_theme_support( 'post-thumbnails' );

                        /*
                         * Switch default core markup for search form, comment form, and comments
                         * to output valid HTML5.
                         */
                        add_theme_support( 'html5', array(
                          'search-form',
                          'comment-form',
                          'comment-list',
                          'gallery',
                          'caption',
                        ) );

                        /** 
                         * Include primary navigation menu
                         */
                        function htmlwp_nav_init() {
                          register_nav_menus( array(
                            'menu-header' => 'Header Menu',
                            'menu-footer' => 'Footer Menu',
                          ) );
                        }
                        add_action( 'init', 'htmlwp_nav_init' );

                        /**
                         * Register widget area.
                         *
                         */
                        function htmlwp_widgets_init() {
                          register_sidebar( array(
                            'name'          => 'Sidebar',
                            'id'            => 'sidebar-1',
                            'description'   => 'Add widgets',
                            'before_widget' => '<section id=\"%1$s\" class=\"widget %2$s\">',
                            'after_widget'  => '</section>',
                            'before_title'  => '<h2 class=\"widget-title\">',
                            'after_title'   => '</h2>',
                          ) );
                        }
                        add_action( 'widgets_init', 'htmlwp_widgets_init' );

                        /**
                         * Enqueue scripts and styles.
                         */
                        function htmlwp_scripts() {
                          wp_enqueue_style( 'htmlwp-style', get_stylesheet_uri() );
                          
                        }
                        add_action( 'wp_enqueue_scripts', 'htmlwp_scripts' );

                        function htmlwp_create_post_custom_post() {
                          register_post_type('custom_post', 
                            array(
                            'labels' => array(
                              'name' => __('Custom Post', 'htmlwp'),
                            ),
                            'public'       => true,
                            'hierarchical' => true,
                            'supports'     => array(
                              'title',
                              'editor',
                              'excerpt',
                              'custom-fields',
                              'thumbnail',
                            ), 
                            'taxonomies'   => array(
                                'post_tag',
                                'category',
                            ) 
                          ));
                        }
                        add_action('init', 'htmlwp_create_post_custom_post'); // Add our work type";
            fwrite($fp, urldecode(htmlspecialchars_decode($functions_data)));
            fclose($fp);

            $dest_search=$theme_base.$template_slug.'/'.'search.php';
             // search create
            $fp=fopen($dest_search,'w');
            $search_data='<?php
            /**
             * The template for displaying search results pages
             *
             */

            get_header();
            ?>

            <main id="main" class="site-main" role="main">

              <?php 
              if ( have_posts() ) : ?>

                <header class="page-header">
                  <h1>Results: <?php echo get_search_query(); ?></h1>
                </header>

                <?php
                while ( have_posts() ) : the_post();

                  get_template_part( \'template-parts/content\', \'search\' );

                endwhile;
              
              else: ?>

                <p>Sorry, but nothing matched your search terms.</p>
              
              <?php
              endif;
              ?>

            </main>

            <?php

            get_footer();';
            fwrite($fp, urldecode(htmlspecialchars_decode($search_data)));
            fclose($fp);

            $dest_sidebar=$theme_base.$template_slug.'/'.'sidebar.php';
             // sidebar create
            $fp=fopen($dest_sidebar,'w');
            $sidebar_data='<?php
            /**
             * The sidebar containing the main widget area
             *
             */

            if ( ! is_active_sidebar( \'sidebar-1\' ) ) {
              return;
            }
            ?>

            <aside class="widget-area">
              <?php dynamic_sidebar( \'sidebar-1\' ); ?>
            </aside>';
            fwrite($fp, urldecode(htmlspecialchars_decode($sidebar_data)));
            fclose($fp);
           
            $dest_single=$theme_base.$template_slug.'/'.'single.php';
             // single create
            $fp=fopen($dest_single,'w');
            
               $single_data='<?php
            /**
             * The template for displaying all single posts
             *
             */

            get_header();
            ?>

            <main id="main" class="site-main" role="main">

              <?php
              while ( have_posts() ) : the_post();

                get_template_part( \'template-parts/content\', get_post_type() );

                // If single are open or we have at least one comment, load up the comment template.
                if ( comments_open() || get_comments_number() ) :
                  comments_template();
                endif;

              endwhile;
              ?>

            </main>

            <?php

            get_footer();';
            
           
            fwrite($fp, urldecode(htmlspecialchars_decode($single_data)));
            fclose($fp);

            
            do_action('htmlwpaddonfunc');

            $success=true;
            $success_msg='Theme Successfully created';


            // print_r($header_html[0]);
            // print_r($footer_html[0]);
             }
             else
             {
              $error=true;
              $error_msg='you have no header and footer tag in your html file.Html Should have header in header tag and footer in footer tag';
             }

          }
          else
          {
            $error=true;
            $error_msg='Folder not have any Html file';
          }
  
      }
        } // end else theme exists
      }


      if($error==true)
      {
          echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
      }
      if($success==true)
      {
        unlink($filepath);
        rrmdir(dirname(__FILE__).'/OriginalHTMLFiles'.'/unzip/'.$template_slug);
        echo json_encode(array('success'=>esc_html($success),'message'=>esc_html($success_msg)));  
      }

   
    wp_die();
    } // check file size
   }
   else
   {
    $error=true;
    $error_msg='You have to fill all data';
     echo json_encode(array('error'=>esc_html($error),'message'=>esc_html($error_msg)));  
          exit;
   }
  }

}
}

add_action( 'plugins_loaded', array( 'HTMLWP_Plugin', 'init_actions' ) );
register_activation_hook(__FILE__, 'HTMLWP_Plugin::activate' );
register_deactivation_hook(__FILE__, 'HTMLWP_Plugin::deactivate');

endif;



?>