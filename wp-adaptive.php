<?php
/*
Plugin Name: WP Adaptive
Plugin URI: http://bradyriordan.com/wp-adaptive
Description: Adapative learning plugin for Wordpress.
Author: Brady Riordan
Author URI: http://bradyriordan.com
Version: 1.0.0
*/

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

// Plugin version
define( 'WP_ADAPTIVE_VERSION', '0.0.1' );


if ( !class_exists( 'WP_Adaptive' ) ) {

    class WP_Adaptive {  
        
        const PREFIX = 'wp_adaptive';
        
        function __construct() {   

            if ( is_admin() ) {
                
                require_once( plugin_dir_path( __FILE__ ).'admin/wp-adaptive-admin.php' ); 
                wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . '/admin/css/admin-styles.css', array(), null, 'screen' );                
                
                // Post types
                add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
                add_action( 'manage_node_posts_custom_column' , array ( $this, 'node_posts_custom_column' ), 10, 2 );  
                add_action( 'manage_assessment_posts_custom_column' , array ( $this, 'assessment_posts_custom_column' ), 10, 2 );                         
                add_action( 'save_post', array ( $this, 'save_metabox' ) );
                add_filter( 'manage_node_posts_columns', array( $this, 'set_custom_edit_node_columns' ) );
                add_filter( 'manage_assessment_posts_columns', array( $this, 'set_custom_edit_assessment_columns' ) );

            }

            // Taxonomies
            add_action( 'init', array( $this, 'add_custom_taxonomies' ) ); 
            add_action( 'expert-model-item_add_form_fields', array( $this, 'taxonomy_display_custom_meta_field' ) );
            add_action( 'expert-model-item_edit_form_fields', array( $this, 'taxonomy_display_custom_meta_field' ) );
            add_action( 'create_expert-model-item', array( $this, 'save_taxonomy_custom_fields' ) );
            add_action( 'edited_expert-model-item', array( $this, 'save_taxonomy_custom_fields' ) ); 

            add_action( 'wp_enqueue_scripts', array( $this, 'ajax_public_enqueue_scripts' ) );
            // ajax hook for logged-in users: wp_ajax_{action}
            add_action( 'wp_ajax_public_hook', array( $this, 'send_statement' ) );
            // ajax hook for non-logged-in users: wp_ajax_nopriv_{action}
            add_action( 'wp_ajax_nopriv_public_hook', array( $this, 'send_statement' ) );

            add_action( 'init', array( $this, 'create_post_types' ) );
            add_action( 'the_post' , array ($this, 'modify_post') );
            add_filter( 'single_template', array ( $this, 'post_templates'), 10, 2 );
            require_once( plugin_dir_path(__FILE__) . 'includes/TinCanPHP-master/autoload.php' );            

        }

        /************************************
                AJAX
        ************************************/

        public function ajax_public_enqueue_scripts( $hook ) {
           
                // define script url
                $script_url = plugins_url( '/includes/ajax-public.js', __FILE__ );
            
                // enqueue script
                wp_enqueue_script( 'ajax-public', $script_url, array( 'jquery' ) );
            
                // create nonce
                $nonce = wp_create_nonce( 'ajax_public' );
            
                // define ajax url
                $ajax_url = admin_url( 'admin-ajax.php' );
            
                // define script
                $script = array( 'nonce' => $nonce, 'ajaxurl' => $ajax_url );
            
                // localize script
                wp_localize_script( 'ajax-public', 'ajax_public', $script );            
        
        }


        public function send_statement(){
            // get post, user, taxonomy data
            $post = get_post( intval ( $_POST['wp_data'][0] ) );
            $user = get_user_by( 'ID', $_POST['wp_data'][1] );
            $term = get_the_terms( $post->ID, 'expert-model-item' );
            $parent = get_post( $post->node_parent_id );                   
            
            // check nonce  
            check_ajax_referer( 'ajax_public', 'nonce' );          

			$lrs = new TinCan\RemoteLRS(
				'https://cloud.scorm.com/lrs/F583XZFRS8/sandbox/',
				// xAPI version
				'1.0.1',
				'', //key
				'' //secret
            );
            
           $actor = new TinCan\Agent(
				['mbox' => 'mailto:' . $user->user_email ]
            );
            
            $verb = new TinCan\Verb(
                ['id' => 'http://id.tincanapi.com/verb/viewed',
                'display' => [
                    'en-us' => 'viewed'
                ]
                ]
            );           
            
			$activity = new TinCan\Activity(
                ['id' => 'http://bradyriordan/team-adaptive-learning/'. $post->post_type . '/' . $post->ID,
                'definition' => [
                    'name' => [
                        'en-US' => $post->post_title
                    ],
                    'description' => [
                        'en-US' => $post->post_title
                    ],
                    'type' => 'https://bradyriordan.com/team-adaptive-learning/content-type/' . $post->wp_adaptive_content_type,
                    'moreInfo' => 'https://bradyriordan.com/team-adaptive-learning/difficulty/' . $post->wp_adaptive_difficulty
                ],
                'objectType' => 'Activity'
                ]
            );
            
            $context = new TinCan\Context(
                ['registration' => '91bfd506-1279-11eb-adc1-0242ac120002', // Need to generate real UUID
                'contextActivities' => [
                    'grouping' => [
                        'definition' => [
                            'name' => [
                                'en-US' => $term[0]->name
                            ]
                        ],
                        'id' => 'https://bradyriordan.com/team-adaptive-learning/expert-model-item/' . $term[0]->slug,
                        'objectType' => 'Activity' // Not sure if this is correct

                    ],
                    'parent' => [
                        'definition' => [
                            'name' => [
                                'en-US' => $parent->post_title // need to get name
                            ],
                            'description' => [
                                'en-US' => $parent->post_title // need to get name
                            ],
                        ],
                        'id' => 'https://bradyriordan.com/team-adaptive-learning/' . $parent->ID, // need to get accurate URL
                        'objectType' => 'Activity'
                    ]
                ]               
                ]
            );

			$statement = new TinCan\Statement(
				[
					'actor' => $actor,
					'verb'  => $verb,
                    'object' => $activity,
                    'context' => $context,
				]
			);

			$response = $lrs->saveStatement($statement);
			if ($response->success) {
				print "Statement sent successfully!\n";				
			} else {
				print "Error statement not sent: " . $response->content . "\n";
			}            
			wp_die();
		}

        /************************************
                MODIFY POSTS
        ************************************/
        public function modify_post(){
            if( ($this->is_post_type( 'node' ) OR $this->is_post_type( 'assessment' )) AND is_single() ) {               
                require_once( plugin_dir_path( __FILE__ ).'includes/header-wp-adaptive.php' );
            }
        }

        private function is_post_type($type){
            global $wp_query;
            if($type == get_post_type($wp_query->post->ID)) 
                return true;
            return false;
        }        

        
        
         /************************************                 
               CREATE POST TYPES             
        ************************************/
        
        public function create_post_types() {
    
            // Look into why this is required for viewing nodes
            flush_rewrite_rules();
            
            // MODULE

            register_post_type( 'module',
                    
                array(
                    'labels' => array(
                        'name' => __( 'Module' ),
                        'singular_name' => __( 'Module' ),
                        'menu_name' => __( 'Modules' ),
                        'parent_item_colon' => __( 'Parent Module' ),
                        'edit_item' => __( 'Edit Module' ),
                        'new_item' => __( 'Add New Module' ),
                        'add_new_item' => __( 'Add New Module' )
                    ),
                    'public' => true,
                    'has_archive' => true,
                    //'rewrite' => array('slug' => 'modules'),
                    'show_in_rest' => false,
                    'description' => 'Container for adaptive learning content created with the WP Adaptive plugin.',
                    'hierarchical' => true,                    
                    'show_ui' => true,
                    'show_in_menu' => 'wp-adaptive',
                    'show_in_nav_menus' => true,
                    'show_in_admin_bar' => true,                    
                    'can_export' => true,                    
                    'exclude_from_search' => false,
                    'publicly_queryable' => true,
                    'capability_type' => 'page',
                    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
                    'taxonomies' => array( 'topic' ),
                    'menu_icon' => 'dashicons-randomize'                                
        
                )

            );


            // NODE
            
            register_post_type( 'node',
                    
                array(
                    'labels' => array(
                        'name' => __( 'Node' ),
                        'singular_name' => __( 'Node' ),
                        'menu_name' => __( 'Nodes' ),
                        'parent_item_colon' => __( 'Parent Node' ),
                        'edit_item' => __( 'Edit Node' ),
                        'new_item' => __( 'Add New Node' ),
                        'add_new_item' => __( 'Add New Node' )
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'node'),
                    'show_in_rest' => false,
                    'description' => 'Education content for adaptive learning for the WP Adaptive plugin.',
                    'hierarchical' => false,                    
                    'show_ui' => true,
                    'show_in_menu' => 'wp-adaptive',
                    'show_in_nav_menus' => true,
                    'show_in_admin_bar' => true,                    
                    'can_export' => true,                    
                    'exclude_from_search' => false,
                    'publicly_queryable' => true,
                    'capability_type' => 'post',
                    'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
                    'taxonomies' => array( 'expert-model-item' ),
                    'menu_icon' => 'dashicons-randomize'                    
        
                )

            );

            
            // Assessment
            
            register_post_type( 'assessment',
                    
                array(
                    'labels' => array(
                        'name' => __( 'Assessment' ),
                        'singular_name' => __( 'Assessment' ),
                        'menu_name' => __( 'Assessments' ),                        
                        'edit_item' => __( 'Edit Assessment' ),
                        'new_item' => __( 'Add New Assessment' ),
                        'add_new_item' => __( 'Add New Assessment' )
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'assessment'),
                    'show_in_rest' => false,
                    'description' => 'Assessments for adaptive learning for the WP Adaptive plugin.',
                    'hierarchical' => false,                    
                    'show_ui' => true,
                    'show_in_menu' => 'wp-adaptive',
                    'show_in_nav_menus' => true,
                    'show_in_admin_bar' => true,                    
                    'can_export' => true,                    
                    'exclude_from_search' => false,
                    'publicly_queryable' => true,
                    'capability_type' => 'post',
                    'supports' => array( 'title', 'editor', 'revisions' ),
                    'taxonomies' => array( 'expert-model-item' ),
                    'menu_icon' => 'dashicons-randomize'                    
        
                )

            );


        }


        /************************************                 
               ADD METABOXES           
        ************************************/

        public function add_metaboxes(){            

            /************************************                 
               MODULE          
            ************************************/            
            
            // xAPI Object ID for module

            add_meta_box( 
                WP_Adaptive::PREFIX . '_module_metabox', 
                'Module Options', 
                WP_Adaptive::PREFIX . '_module_metabox', 
                'module', 
                'normal'                
            );
            
            // xAPI Object Id callback for module

            function wp_adaptive_module_metabox() {                
                
                wp_nonce_field( 'nonce_object_metabox_action', 'nonce_object_metabox_field' );

                ?>
                
                <label for="wp_adaptive_object_id_module">Object ID</label><br/>
                <input class="wp-adaptive-text-input" type="text" name="wp_adaptive_object_id_module" id="wp_adaptive_object_id_module" placeholder="Link" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id_module', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id_module', $single = true) : ""; ?> ">                   
                
                <?php

            }

            // Module expert model items

            add_meta_box( 
                'expert-model-items', 
                'Expert Model Items', 
                'module_expert_model_items_meta_box', 
                'module', 
                'normal', 
                'low'
            ); 

            function module_expert_model_items_meta_box() {                
                

                ?>

                <div id="module_expert_model_items_meta_box">
                
                <ul>

                <?php

                
                $cats = wp_list_categories( array (
                    'title_li' => '',
                    'style' => 'list',                    
                    'taxonomy' => 'expert-model-item',
                    'hide_empty' => 0,
                    'show_count' => 1,  
                    'hierarchical' => 1,                   
                ) );
                

                
                ?>

                </ul>
                
                </div>                    
                
                <?php

            }
            
            
            /************************************                 
               NODE           
            ************************************/            
            
            // ADD NODE METABOX

            add_meta_box( 
                WP_Adaptive::PREFIX . '_node_metabox', 
                'Node Options', 
                WP_Adaptive::PREFIX . '_node_metabox', 
                'node', 
                'normal'
            ); 

            
            // NODE METABOX CALLBACK

            function wp_adaptive_node_metabox() {                
                
                wp_nonce_field( 'nonce_node_metabox_action', 'nonce_node_metabox_field' );

                ?>

                <!-- PARENT MODULE -->

                <?php

                $post_type_object = get_post_type_object( $post->post_type );

                $pages = wp_dropdown_pages( 
                    array( 
                        'post_type' => 'module', 
                        'selected' => get_post_meta(get_the_ID(), $key = 'node_parent_id', $single = true), 
                        'name' => 'node_parent_id', 
                        'show_option_none' => __( 
                            '(no parent)' ), 
                            'sort_column'=> 'menu_order, 
                            post_title', 
                            'echo' => 0 
                        ) 
                    );

                ?>
                
                <label for="node_parent_id">Module</label><br/>

                <?php
                
                if ( ! empty( $pages ) ) {
                    echo $pages . '</br></br>';
                }

                ?>
                
                <!-- OBJECT ID -->
                
                <label for="wp_adaptive_object_id">Object ID</label><br/>
                <input class="wp-adaptive-text-input" type="text" name="wp_adaptive_object_id" id="wp_adaptive_object_id" placeholder="Link" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id', $single = true) : ""; ?> ">               
                </br></br>

                <!-- SOURCE -->

                <label for="wp_adaptive_source">Source</label><br/>
                <input class="wp-adaptive-text-input" type="text" name="wp_adaptive_source" id="wp_adaptive_source" placeholder="Source" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_source', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_source', $single = true) : ""; ?> "> 
                </br></br>
                
                <!-- LINK -->

                <label for="wp_adaptive_link">Link</label><br/>
                <input class="wp-adaptive-text-input" type="text" name="wp_adaptive_link" id="wp_adaptive_link" placeholder="Link" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_link', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_link', $single = true) : ""; ?> "> 
                </br></br>

                <!-- VIDEO URL -->
                
                <label for="wp_adaptive_video_url">Video URL</label><br/>
                <input class="wp-adaptive-text-input" type="text" name="wp_adaptive_video_url" id="wp_adaptive_video_url" placeholder="Video URL" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_video_url', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_video_url', $single = true) : ""; ?> "> 
                </br></br>
                
                <!-- CONTENT TYPE -->
                
                <?php
                $options = [ 

                    ['_','Choose...'],
                    ['text','Text'],  
                    ['text_image','Text & Image'],  
                    ['image','Image'],  
                    ['link','Link'],  
                    ['video','Video'],
                    ['audio','Audio'],  
                    ['web_object','Web Object'],    

                ];
                
                ?>

                <label for="wp_adaptive_content_type">Content Type</label><br/>
                
                <select name="wp_adaptive_content_type" id="wp_adaptive_content_type"> 
                    
                    <?php
                                  
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_content_type', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?>                   

                </select></br></br>

                
                <!-- DIFFICULTY -->
                
                <?php

                $options = [ 

                    ['_','Choose...'],
                    ['1','1 - understand/remember'],  
                    ['2','2 - apply/analyze'],  
                    ['3','3 - create/evaluate'], 

                ];

                ?>

                <label for="wp_adaptive_difficulty">Difficulty</label><br/>
                
                <select name="wp_adaptive_difficulty" id="wp_adaptive_difficulty"> 
                
                    <?php   
                    
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_difficulty', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?> 
                
                </select></br></br>

                
                <!-- LICENSE -->
                
                <?php

                $options = [ 

                    ['_','Choose...'],
                    ['1','Attribution (CC BY)'],  
                    ['2','Attribution ShareAlike (CC BY-SA)'],  
                    ['3','Attribution-NoDerivs (CC BY-ND)'],
                    ['4','Attribution-NonCommercial (CC BY-NC)'], 
                    ['5','Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)'], 
                    ['6','Attribution-NonCommercial-NoDerivs (CC BY-NC-ND)'], 
                    ['7','Copyright'],                      

                ];

                ?>

                <label for="wp_adaptive_license">License</label><br/>
                
                <select name="wp_adaptive_license" id="wp_adaptive_license"> 
                
                    <?php   
                    
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_license', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?> 
                
                </select></br></br> 


                <?php

            }
            
            
            /************************************                 
               ASSESSMENT          
            ************************************/ 
            
            // ADD ASSESSMENT METABOX

             add_meta_box( 
                WP_Adaptive::PREFIX . '_assessment_metabox', 
                'Assessment Options', 
                WP_Adaptive::PREFIX . '_difficulty_metabox_assessment', 
                'assessment', 
                'normal', 
                'high'
            ); 

            // ASSESSMENT METABOX CALLBACK

            function wp_adaptive_difficulty_metabox_assessment() {
                
                wp_nonce_field( 'nonce_assessment_metabox_action', 'nonce_assessment_metabox_field' );

                
                // PARENT MODULE /               

                $post_type_object = get_post_type_object( $post->post_type );

                $pages = wp_dropdown_pages( 
                    array( 
                        'post_type' => 'module', 
                        'selected' => get_post_meta(get_the_ID(), $key = 'assessment_parent_id', $single = true), 
                        'name' => 'assessment_parent_id', 
                        'show_option_none' => __( 
                            '(no parent)' ), 
                            'sort_column'=> 'menu_order, 
                            post_title', 
                            'echo' => 0 
                        ) 
                    );

                ?>
                
                <label for="assessment_parent_id">Module</label><br/>

                <?php
                
                if ( ! empty( $pages ) ) {
                    echo $pages . '</br></br>';
                }

                
                // DIFFICULTY
                
                $options = [ 

                    ['_','Choose...'],
                    ['1','1 - understand/remember'],  
                    ['2','2 - apply/analyze'],  
                    ['3','3 - create/evaluate'], 

                ];

                ?>

                <label for="wp_adaptive_difficulty_assessment">Difficulty</label><br/>
                
                <select name="wp_adaptive_difficulty_assessment" id="wp_adaptive_difficulty_assessment"> 
                
                    <?php   
                    
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_difficulty_assessment', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?> 
                
                </select>

            <?php        

            }

            
            // ASSESSMENT OPTIONS
            
            function assessment_options_meta_boxes(){

                add_meta_box( 
                    WP_Adaptive::PREFIX . '_assessment_options',
                    'Options', 
                    WP_Adaptive::PREFIX . '_assessment_options', 
                    'assessment', 
                    'normal', 
                    'high'              
                );                

                function wp_adaptive_assessment_options(  ){
                    
                    wp_nonce_field( 'nonce_assessment_options_metabox_action', 'nonce_assessment_options_metabox_field' );

                    for( $i = 1; $i <= 5; $i++ ){
                    
                        ?>

                        <!-- DON'T SPACE TEXTAREA. WILL CREATE EXTRA SPACES AND LINE BREAKS  -->
                        <textarea rows='4' cols='50' name='wp_adaptive_assessment_option_<?php echo $i ?>' id='wp_adaptive_assessment_option_<?php echo $i ?>'><?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_assessment_option_' . $i, $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_assessment_option_' . $i, $single = true) : ""; ?></textarea> </br>
                        <label for='wp_adaptive_assessment_option_<?php echo $i ?>'>Correct </label>
                        <input class="wp-adaptive-text-input" type='radio' id='wp_adaptive_assessment_option_<?php echo $i ?>_correct' name='wp_adaptive_assessment_option_correct' value='wp_adaptive_assessment_option_<?php echo $i ?>_correct' <?php if ( ( get_post_meta( get_the_ID(), $key = 'wp_adaptive_assessment_option_correct', $single = true) ) == 'wp_adaptive_assessment_option_' . $i . '_correct' ) {
                            echo "checked";
                        } else {
                            echo "";
                        } ?> > </br></br> 

                        <?php

                    }

                }

            }

            assessment_options_meta_boxes();                                      

        }
        
        
        /************************************                 
            SAVE METABOX          
        ************************************/
        
        public function save_metabox() {

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

            // MODULE
            
            if ( isset( $_POST[ 'wp_adaptive_object_id_module' ] ) && isset( $_POST['nonce_object_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_object_metabox_field'], 'nonce_object_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_object_id_module', sanitize_text_field( $_POST[ 'wp_adaptive_object_id_module' ] ) );
            }

            // NODE

            if ( isset( $_POST[ 'node_parent_id' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'node_parent_id', sanitize_text_field( $_POST[ 'node_parent_id' ] ) );
            }
            
            if ( isset( $_POST[ 'wp_adaptive_object_id' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_object_id', sanitize_text_field( $_POST[ 'wp_adaptive_object_id' ] ) );
            }

            if ( isset( $_POST[ 'wp_adaptive_link' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
                update_post_meta( get_the_id(), 'wp_adaptive_link', sanitize_text_field( $_POST[ 'wp_adaptive_link' ] ) );                
            }

            if ( isset( $_POST[ 'wp_adaptive_video_url' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_video_url', sanitize_text_field( $_POST[ 'wp_adaptive_video_url' ] ) );
            }

            if ( isset( $_POST[ 'wp_adaptive_content_type' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_content_type', sanitize_text_field( $_POST[ 'wp_adaptive_content_type' ] ) );
            } 

            if ( isset( $_POST[ 'wp_adaptive_difficulty' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_difficulty', sanitize_text_field( $_POST[ 'wp_adaptive_difficulty' ] ) );
            } 

            if ( isset( $_POST[ 'wp_adaptive_source' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_source', sanitize_text_field( $_POST[ 'wp_adaptive_source' ] ) );
            }

            if ( isset( $_POST[ 'wp_adaptive_license' ] ) && isset( $_POST['nonce_node_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_node_metabox_field'], 'nonce_node_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_license', sanitize_text_field( $_POST[ 'wp_adaptive_license' ] ) );
            } 

            // ASSESSMENT  

            if ( isset( $_POST[ 'wp_adaptive_difficulty_assessment' ] ) && isset( $_POST['nonce_assessment_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_assessment_metabox_field'], 'nonce_assessment_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_difficulty_assessment', sanitize_text_field( $_POST[ 'wp_adaptive_difficulty_assessment' ] ) );
            }
            
            if ( isset( $_POST[ 'assessment_parent_id' ] ) && isset( $_POST['nonce_assessment_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_assessment_metabox_field'], 'nonce_assessment_metabox_action' ) ) {
				update_post_meta( get_the_id(), 'assessment_parent_id', sanitize_text_field( $_POST[ 'assessment_parent_id' ] ) );
            }
            
            // ASSESSMENT OPTIONS

            for( $i = 1; $i <= 5; $i++ ){

                if ( isset( $_POST[ 'wp_adaptive_assessment_option_' . $i ] ) && isset( $_POST['nonce_assessment_options_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_assessment_options_metabox_field'], 'nonce_assessment_options_metabox_action' ) ) {
                    update_post_meta( get_the_id(), 'wp_adaptive_assessment_option_' . $i, trim( sanitize_text_field( $_POST[ 'wp_adaptive_assessment_option_' . $i ] ) ) );                
                } 

                if ( isset( $_POST[ 'wp_adaptive_assessment_option_correct' ] ) && isset( $_POST['nonce_assessment_options_metabox_field'] ) && wp_verify_nonce( $_POST['nonce_assessment_options_metabox_field'], 'nonce_assessment_options_metabox_action' ) ) {                    
                    update_post_meta( get_the_id(), 'wp_adaptive_assessment_option_correct', sanitize_html_class( $_POST[ 'wp_adaptive_assessment_option_correct' ] ) );              
                } 

            } 

        }


        /************************************                 
            ADD COLUMNS TO ADMIN SCREEN           
        ************************************/
        
        
        // Add columns node
        public function set_custom_edit_node_columns($columns) {
            
            $columns['wp_adaptive_content_type'] = __( 'Content Type' );
            $columns['wp_adaptive_difficulty'] = __( 'Difficulty' );                      
            return $columns;

        }

        // Populate columns node
        public function node_posts_custom_column( $column, $post_id ){

            switch ( $column ) {

                case 'wp_adaptive_content_type' :
                    echo get_post_meta( $post_id, 'wp_adaptive_content_type' )[0];                     
                    break;
        
                case 'wp_adaptive_difficulty' :
                    echo get_post_meta( $post_id, 'wp_adaptive_difficulty' )[0]; 
                    break;
        
            }

        }


        // Add columns assessment
        public function set_custom_edit_assessment_columns($columns) {
            
            
            $columns['wp_adaptive_difficulty_assessment'] = __( 'Difficulty' );
            $columns['content'] = __( 'Question' );                                    
            return $columns;

        }

        // Populate columns assessment
        public function assessment_posts_custom_column( $column, $post_id ){

            switch ( $column ) {

                case 'wp_adaptive_difficulty_assessment' :
                    echo get_post_meta( $post_id, 'wp_adaptive_difficulty_assessment' )[0]; 
                    break;
                
                case 'content' :
                    echo get_post_field('post_content', $post_id); 
                    break;               
        
            }

        }


        /*********************************                 
               POST TEMPLATES           
        *********************************/       

        public function post_templates( $template ) {  
            
            global $post;
            
            if ( 'node' === $post->post_type ) {
                
                return plugin_dir_path( __FILE__ ) . '/includes/single-node.php';
                
            }

            if ( 'assessment' === $post->post_type ) {
                
                return plugin_dir_path( __FILE__ ) . '/includes/single-assessment.php';
                
            }
        
            return $template;
        }

        /************************************                 
               CREATE TAXONOMIES            
        ************************************/

        public function add_custom_taxonomies() { 
                   
            
            // TOPICS FOR MODULES
            
            $labels = array(
                'name' => _x( 'Topics', 'taxonomy general name' ),
                'singular_name' => _x( 'Topic', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Topics' ),
                'all_items' => __( 'All Topics' ),
                'parent_item' => __( 'Parent Topic' ),
                'parent_item_colon' => __( 'Parent Topic:' ),
                'edit_item' => __( 'Edit Topic' ), 
                'update_item' => __( 'Update Topic' ),
                'add_new_item' => __( 'Add New Topic' ),
                'new_item_name' => __( 'New Topic Name' ),
                'menu_name' => __( 'Topics' ),
            );
           
             
            register_taxonomy('topics', array('module'), array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'topic' ),                
                'show_in_nav_menus' => true,
                'show_in_menu' => true,
            ));
            
            
            // EXPERT MODEL ITEM FOR NODES
            
             $labels = array(
                'name' => _x( 'Expert Model Items', 'taxonomy general name' ),
                'singular_name' => _x( 'Item', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Items' ),
                'all_items' => __( 'All Items' ),
                'parent_item' => __( 'Parent Item' ),
                'parent_item_colon' => __( 'Parent Item:' ),
                'edit_item' => __( 'Edit Item' ), 
                'update_item' => __( 'Update Item' ),
                'add_new_item' => __( 'Add New Item' ),
                'new_item_name' => __( 'New Item Name' ),
                'menu_name' => __( 'Expert Model Items' ),
            );
           
             
            register_taxonomy( 'expert-model-item', array( 'node', 'assessment' ), array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'node' ),
                'show_in_menu' => true,
                'show_in_nav_menus' => true,               
            ));

        }
        
        
        /***************************************************                 
                ASSOCIATE EXPERT MODEL ITEM WITH MODULE           
        ****************************************************/
        
        
        // ADD DROPDOWN TO ADD AND EDIT SCREEN
        
        public function taxonomy_display_custom_meta_field( $term ){
            
            $args = array(
                'post_type' => 'module',
                'post_status' => 'publish'
              );
               
            $modules = get_posts( $args );
              
            $term_id = $term->term_id;             

            ?>

            <tr class="form-field"> 

                <th scope="row" valign="top">  
                    <label for="wp_adaptive_expert_model_item_module">Module</label>  
                </th> 

                <td>

                    <select name="wp_adaptive_expert_model_item_module" class="wp-adaptive-text-input">
                    <option value="None">None</option>
                    <?php

                            if( !empty ( $modules ) ){
                                foreach( $modules as $module ){

                                    ?>                          
                                    
                                    <option value="<?php echo $module->id; ?>"><?php echo $module->post_name; ?></option>

                                    <?php
                                    
                                }
                            } 

                    ?>

                    </select></br></br>

                <td>

            </tr>

            <?php

        }
        
        
        // SAVE DROPDOWN VALUE

        public function save_taxonomy_custom_fields( $term_id ) {
            
            if ( isset( $_POST['wp_adaptive_expert_model_item_module'] ) ) { 
                
                $t_id = $term_id;  
                $term_meta = get_option( "taxonomy_term_$t_id" );

                $cat_keys = array_keys( $_POST['term_meta'] );  
                    foreach ( $cat_keys as $key ){  
                    if ( isset( $_POST['term_meta'][$key] ) ){  
                        $term_meta[$key] = $_POST['term_meta'][$key];  
                    }  
                }  

                //save the option array  
                update_option( "taxonomy_term_$t_id", $term_meta );  

            }

        }  
        
    }

}
new WP_Adaptive();

