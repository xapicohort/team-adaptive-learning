<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Taxonomies' ) ) {

    class WP_Adaptive_Taxonomies {       

        function __construct() {   

            if ( is_admin() ) {
                
                add_action( 'init', array( $this, 'add_custom_taxonomies' ) ); 
                add_action( 'expert-model-item_add_form_fields', array( $this, 'taxonomy_display_custom_meta_field' ) );
                add_action( 'expert-model-item_edit_form_fields', array( $this, 'taxonomy_display_custom_meta_field' ) );
                add_action( 'create_expert-model-item', array( $this, 'save_taxonomy_custom_fields' ) );
                add_action( 'edited_expert-model-item', array( $this, 'save_taxonomy_custom_fields' ) );  

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
new WP_Adaptive_Taxonomies();