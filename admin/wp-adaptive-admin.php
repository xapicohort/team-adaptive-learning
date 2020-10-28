<?php

if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Admin' ) ) {

    class WP_Adaptive_Admin{
        /**
         * Holds the values to be used in the fields callbacks
         */
        private $options;

        // Initialize
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }

        // Admin page
        public function add_plugin_page()
        {
            // Will appear as it's own menu option
            add_menu_page(
                'Settings Admin', 
                'WP Adaptive', 
                'manage_options', 
                'wp-adaptive', 
                array( $this, 'create_admin_page' )
            );

            add_submenu_page(
                'wp-adaptive', 
                'Settings', 
                'Settings', 
                'edit_posts', 
                '?page=wp-adaptive',
                false, 
                0
            );
        }

        // Render form
        public function create_admin_page()
        {
            // Set class property
            $this->options = get_option( 'wp_adaptive_options' );
            ?>
            <div class="wrap">
                <h1>WP Adaptive Options</h1>
                
                <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields( 'wp_adaptive_option_group' );
                    do_settings_sections( 'wp-adaptive-setting-admin' );
                    submit_button();
                ?>
                </form>
            </div>
            <?php
        }

        // Register settings and sections
        public function page_init()
        {        
            register_setting(
                'wp_adaptive_option_group', // Option group
                'wp_adaptive_options', // Option name
                array( $this, 'sanitize' ) // Sanitize
            );

                     
            /************************************ 
                
                LRS Section
                
            ************************************/
            // LRS section
            add_settings_section(
                'lrs_section_id', // ID
                'LRS', // Title
                array( $this, 'print_lrs_section_info' ), // Callback
                'wp-adaptive-setting-admin' // Page
            );             

            add_settings_field(
                'lrs_endpoint', // ID
                'LRS endpoint', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section 
                ['id' => 'lrs_endpoint']          
            ); 

            add_settings_field(
                'lrs_key', // ID
                'LRS key', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section 
                ['id' => 'lrs_key']          
            ); 

            add_settings_field(
                'lrs_secret', // ID
                'LRS secret', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section 
                ['id' => 'lrs_secret']          
            ); 

             /************************************ 
                
                Object ID Format
                
            ************************************/
            // LRS section
            add_settings_section(
                'object_id_format_section_id', // ID
                'Object ID Format', // Title
                array( $this, 'print_object_id_section_info' ), // Callback
                'wp-adaptive-setting-admin' // Page
            );             

            add_settings_field(
                'object_id_format', // ID
                'Object ID format', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'object_id_format_section_id', // Section 
                ['id' => 'object_id_format']          
            );

        }

        /**
         * Sanitize each setting field
         *
         * @param array $input Contains all settings fields as array keys
         */
        public function sanitize( $input )
        {
            $new_input = array();
            
            if( isset( $input['lrs_endpoint'] ) )
                $new_input['lrs_endpoint'] = sanitize_text_field( $input['lrs_endpoint'] );
            
            if( isset( $input['lrs_key'] ) )
                $new_input['lrs_key'] = sanitize_text_field( $input['lrs_key'] );

            if( isset( $input['lrs_secret'] ) )
                $new_input['lrs_secret'] = sanitize_text_field( $input['lrs_secret'] );

            if( isset( $input['object_id_format'] ) )
                $new_input['object_id_format'] = sanitize_text_field( $input['object_id_format'] );

            return $new_input;
        }

        /************************************ 
                
                Section Callbacks
                
        ************************************/
        public function print_lrs_section_info()
        {
            print 'Enter your LRS credentials below:';
        }

        public function print_object_id_section_info()
        {
            print 'Enter your Object ID format below. Object IDs will be generated use the base below appended with the post type and ID.';
        }


        // Callback for text fields
        public function text_callback($args){
            printf(
                '<input type="text" style="width:450px;" id="' . $args['id'] . '" name="wp_adaptive_options[' .  $args['id'] . ']" value="%s" />',
                isset( $this->options[$args['id']] ) ? esc_attr( $this->options[$args['id']]) : ''
            );
        }
        
    }

}

new WP_Adaptive_Admin();



