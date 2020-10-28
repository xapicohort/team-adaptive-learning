<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_LRS_Creds' ) ) {

    class WP_Adaptive_LRS_Creds{        
        
        public static function get_options(){
            if (get_option('wp_adaptive_options') != NULL) {
                $options = get_option('wp_adaptive_options');
                return $options;
            } else {
                return false;
            }
        }
        
        public static function get_endpoint(){
            $options = self::get_options();
            return $options['lrs_endpoint'];        
        }

        public static function get_key(){
            $options = self::get_options();
            return $options['lrs_key'];       
        }

        public static function get_secret(){
            $options = self::get_options();
            return $options['lrs_secret'];        
        }

    }
}

new WP_Adaptive_LRS_Creds();