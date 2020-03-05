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

        function __construct() {   

            if ( is_admin() ) {

                require_once( plugin_dir_path( __FILE__ ).'admin/wp-adaptive-admin.php' );
                require_once( plugin_dir_path( __FILE__ ).'includes/wp-adaptive-post-types.php' );  

            }		            
        } 
    }

}
new WP_Adaptive();
