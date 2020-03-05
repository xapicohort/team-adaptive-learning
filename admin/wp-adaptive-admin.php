<?php

if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Admin' ) ) {

    class WP_Adaptive_Admin{
        
        // Holds the values to be used in the fields callbacks         
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
        }

        // Render form
        public function create_admin_page()
        {
            // Set class property
            $this->options = get_option( 'wp_adaptive_options' );
            ?>
            <div class="wrap">
                <h1>WP Adaptive</h1>
                
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
                LRS SECTION             
            ************************************/


            add_settings_section(
                'lrs_section_id', // ID
                'LRS', // Title
                array( $this, 'print_lrs_section_info' ), // Callback
                'wp-adaptive-setting-admin' // Page
            );             

            add_settings_field(
                'lrs_endoint', // ID
                'Endpoint', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section 
                ['id' => 'lrs_endpoint']               
            ); 

            add_settings_field(
                'lrs_key', // ID
                'LRS Key', // Title 
                array( $this, 'password_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section
                ['id' => 'lrs_key']            
            );   
            
            add_settings_field(
                'lrs_secret', // ID
                'LRS Secret', // Title 
                array( $this, 'password_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'lrs_section_id', // Section
                ['id' => 'lrs_secret']            
            );  

            
             /************************************                 
                AWS SECTION              
            ************************************/

            
            add_settings_section(
                'aws_section_id', // ID
                'AWS', // Title
                array( $this, 'print_aws_section_info' ), // Callback
                'wp-adaptive-setting-admin' // Page
            );             

            add_settings_field(
                'aws_endpoint_url', // ID
                'Endpoint URL', // Title 
                array( $this, 'text_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'aws_section_id', // Section  
                ['id' => 'aws_endpoint_url']          
            ); 

            add_settings_field(
                'aws_key', // ID
                'AWS Key', // Title 
                array( $this, 'password_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'aws_section_id', // Section  
                ['id' => 'aws_key']          
            ); 

            add_settings_field(
                'aws_secret', // ID
                'AWS Secret', // Title 
                array( $this, 'password_callback' ), // Callback
                'wp-adaptive-setting-admin', // Page
                'aws_section_id', // Section  
                ['id' => 'aws_secret']          
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

            if( isset( $input['aws_endpoint_url'] ) )
                $new_input['aws_endpoint_url'] = sanitize_text_field( $input['aws_endpoint_url'] );

            if( isset( $input['aws_key'] ) )
                $new_input['aws_key'] = sanitize_text_field( $input['aws_key'] );

            if( isset( $input['aws_secret'] ) )
                $new_input['aws_secret'] = sanitize_text_field( $input['aws_secret'] );           

            return $new_input;
        }


        /************************************                 
                Section Callbacks                
        ************************************/


        public function print_lrs_section_info()
        {
            print 'Enter your LRS credentials below:';
        }

        public function print_aws_section_info()
        {
            print 'Enter your AWS credentials below:';
        }


        // Callback for text fields
        public function text_callback($args){
            printf(
                '<input type="text" id="' . $args['id'] . '" name="wp_adaptive_options[' .  $args['id'] . ']" value="%s" />',
                isset( $this->options[$args['id']] ) ? esc_attr( $this->options[$args['id']]) : ''
            );
        }

        // Callback for password fields
        public function password_callback($args){
            printf(
                '<input type="password" id="' . $args['id'] . '" name="wp_adaptive_options[' .  $args['id'] . ']" value="%s" />',
                isset( $this->options[$args['id']] ) ? esc_attr( $this->options[$args['id']]) : ''
            );
        }

    }

}

new WP_Adaptive_Admin();