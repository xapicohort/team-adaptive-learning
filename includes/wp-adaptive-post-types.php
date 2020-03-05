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


            // CONTENT
            
            register_post_type( 'content',
                    
                array(
                    'labels' => array(
                        'name' => __( 'Content' ),
                        'singular_name' => __( 'Content' ),
                        'menu_name' => __( 'Content' ),
                        'parent_item_colon' => __( 'Parent Content' ),
                        'edit_item' => __( 'Edit Content' ),
                        'new_item' => __( 'Add New Content' ),
                        'add_new_item' => __( 'Add New Content' )
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'rewrite' => array('slug' => 'content'),
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
                    'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes' ),
                    'taxonomies' => array( 'Node' ),
                    'menu_icon' => 'dashicons-randomize'                    
        
                )

            );


        }


        /************************************                 
               ADD METABOXES           
        ************************************/

        public function add_metaboxes(){            

                add_meta_box( 
                    'content-parent', 
                    'Module', 
                    'content_attributes_meta_box', 
                    'content', 
                    'side', 
                    'low'
                 );            
            
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
               CREATE TAXONOMIES            
        ************************************/

        public function add_custom_taxonomies() { 
                   
            
            // TOPICS FOR COURSES
            
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
            ));
            
            
            // NODES FOR CONTENT
            
             $labels = array(
                'name' => _x( 'Nodes', 'taxonomy general name' ),
                'singular_name' => _x( 'Node', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Node' ),
                'all_items' => __( 'All Nodes' ),
                'parent_item' => __( 'Parent Node' ),
                'parent_item_colon' => __( 'Parent Node:' ),
                'edit_item' => __( 'Edit Node' ), 
                'update_item' => __( 'Update Node' ),
                'add_new_item' => __( 'Add New Node' ),
                'new_item_name' => __( 'New Node Name' ),
                'menu_name' => __( 'Nodes' ),
            );
           
             
            register_taxonomy( 'nodes', array( 'content' ), array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'node' ),
            ));

        }        


    }

}

new WP_Adaptive_Post_Types();