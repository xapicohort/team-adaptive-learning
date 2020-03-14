<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Post_Types' ) ) {

    class WP_Adaptive_Post_Types{

        // Initialize
        public function __construct()
        {
            add_action( 'init', array( $this, 'create_post_types' ) );  
            add_action( 'init', array( $this, 'add_custom_taxonomies' ) );    
            add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
            add_action( 'manage_node_posts_custom_column' , array ( $this, 'node_posts_custom_column' ), 10, 2 );                          
            add_filter( 'manage_node_posts_columns', array( $this, 'set_custom_edit_node_columns' ) );
            add_action( 'save_post', array ( $this, 'save_metabox' ) );   
        }

     
        /************************************                 
               CREATE POST TYPES             
        ************************************/
        
        public function create_post_types() {
    
            
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
                    'rewrite' => array('slug' => 'modules'),
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
                    'capability_type' => 'post',
                    'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
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


        }


        /************************************                 
               ADD METABOXES           
        ************************************/

        public function add_metaboxes(){            

            
            // Object ID

            add_meta_box( 
                'wp_adaptive_object_id', 
                'Object ID', 
                'object_id_meta_box', 
                'node', 
                'normal'
            ); 

            
            // Object Id callback

            function object_id_meta_box() {                
                
                wp_nonce_field( 'wp_adaptive_metabox_nonce', 'wp_adaptive_metabox_nonce' );

                ?>
                
                <label for="wp_adaptive_object_id" style="display:none;">Object ID</label><br/>
                <input type="text" name="wp_adaptive_object_id" id="wp_adaptive_object_id" placeholder="Link" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_object_id', $single = true) : ""; ?> ">                   
                
                <?php

            }

            
            // Link

            add_meta_box( 
                'wp_adaptive_link', 
                'Link', 
                'link_meta_box', 
                'node', 
                'normal'
            ); 

            
            // Link callback

            function link_meta_box() {                
                
                wp_nonce_field( 'wp_adaptive_metabox_nonce', 'wp_adaptive_metabox_nonce' );

                ?>
                
                <label for="wp_adaptive_link" style="display:none;">Link</label><br/>
                <input type="text" name="wp_adaptive_link" id="wp_adaptive_link" placeholder="Link" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_link', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_link', $single = true) : ""; ?> ">                   
                
                <?php

            }

            
            // Video URL

            add_meta_box( 
                'wp_adaptive_video_url', 
                'Video URL', 
                'video_url_meta_box', 
                'node', 
                'normal'
            ); 

            
            // Video URL callback

            function video_url_meta_box() {
                
                wp_nonce_field( 'wp_adaptive_metabox_nonce', 'wp_adaptive_metabox_nonce' );

                ?>

                <label for="wp_adaptive_video_url" style="display:none;">Video URL</label><br/>
                <input type="text" name="wp_adaptive_video_url" id="wp_adaptive_video_url" placeholder="Video URL" length="50" value="<?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_video_url', $single = true)) ? get_post_meta(get_the_ID(), $key = 'wp_adaptive_video_url', $single = true) : ""; ?> ">                      
                
                <?php

            }

            
            // Content type dropdown

            add_meta_box( 
                'wp_adapative_content_type', 
                'Content Type', 
                'content_type_meta_box', 
                'node', 
                'side', 
                'low'
            ); 

            // Content type callback

            function content_type_meta_box( $post ) {
                
                wp_nonce_field( 'wp_adaptive_metabox_nonce', 'wp_adaptive_metabox_nonce' );
                
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

                <label for="wp_adaptive_content_type" style="display:none;">Content Type</label><br/>
                
                <select name="wp_adaptive_content_type" id="wp_adaptive_content_type"> 
                    
                    <?php
                                  
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_content_type', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?>                   

                </select>
                
                <?php

            }
            
            // Difficulty dropdown

            add_meta_box( 
                'wp_adaptive_difficulty', 
                'Difficulty', 
                'difficulty_meta_box', 
                'node', 
                'side', 
                'low'
            ); 

            // Difficulty callback

            function difficulty_meta_box( ) {
                
                wp_nonce_field( 'wp_adaptive_metabox_nonce', 'wp_adaptive_metabox_nonce' );

                $options = [ 

                    ['_','Choose...'],
                    ['1','1 - understand/remember'],  
                    ['2','2 - apply/analyze'],  
                    ['3','3 - create/evaluate'],  
                     

                ];

                ?>

                <label for="wp_adaptive_difficulty" style="display:none;">Difficulty</label><br/>
                
                <select name="wp_adaptive_difficulty" id="wp_adaptive_difficulty"> 
                
                    <?php   
                    
                    foreach ($options as $option) { ?>

                        <option value="<?php echo strtolower($option[0]); ?>" <?php echo (get_post_meta(get_the_ID(), $key = 'wp_adaptive_difficulty', $single = true) == strtolower($option[0])) ? 'selected="selected"' : ""; ?>><?php echo $option[1]; ?></option>

                    <?php } ?> 
                
                </select>
                
                <?php               

            }

            
            // Module dropdown
            
            add_meta_box( 
                'content-parent', 
                'Module', 
                'content_attributes_meta_box', 
                'node', 
                'side', 
                'low'
            );            
            
            // Module callback
            
            function content_attributes_meta_box( $post ) {

                $post_type_object = get_post_type_object( $post->post_type );

                $pages = wp_dropdown_pages( 
                    array( 
                        'post_type' => 'module', 
                        'selected' => $post->post_parent, 
                        'name' => 'parent_id', 
                        'show_option_none' => __( 
                            '(no parent)' ), 
                            'sort_column'=> 'menu_order, 
                            post_title', 
                            'echo' => 0 
                        ) 
                    );

                if ( ! empty( $pages ) ) {
                    echo $pages;
                }

            }

        }

        
        
        /************************************                 
            SAVE METABOX          
        ************************************/
        
        public function save_metabox() {

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

            if ( !isset( $_POST[ 'wp_adaptive_metabox_nonce' ]) || !wp_verify_nonce( $_POST['wp_adaptive_metabox_nonce'], 'wp_adaptive_metabox_nonce' ) ) return;

            if ( isset( $_POST[ 'wp_adaptive_link' ] ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_link', sanitize_text_field( $_POST[ 'wp_adaptive_link' ] ) );
            }

            if ( isset( $_POST[ 'wp_adaptive_video_url' ] ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_video_url', sanitize_text_field( $_POST[ 'wp_adaptive_video_url' ] ) );
            }
            
            if ( isset( $_POST[ 'wp_adaptive_content_type' ] ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_content_type', sanitize_text_field( $_POST[ 'wp_adaptive_content_type' ] ) );
            } 

            if ( isset( $_POST[ 'wp_adaptive_difficulty' ] ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_difficulty', sanitize_text_field( $_POST[ 'wp_adaptive_difficulty' ] ) );
            } 

            if ( isset( $_POST[ 'wp_adaptive_object_id' ] ) ) {
				update_post_meta( get_the_id(), 'wp_adaptive_object_id', sanitize_text_field( $_POST[ 'wp_adaptive_object_id' ] ) );
            } 
            

        }


        /************************************                 
            ADD COLUMNS TO ADMIN SCREEN           
        ************************************/
        
        
        // Add columns
        public function set_custom_edit_node_columns($columns) {
            
            $columns['wp_adaptive_content_type'] = __( 'Content Type' );
            $columns['wp_adaptive_difficulty'] = __( 'Difficulty' );                      
            return $columns;

        }

        // Populate columns
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
           
             
            register_taxonomy( 'expert-model-item', array( 'node' ), array(
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
    }

}

new WP_Adaptive_Post_Types();