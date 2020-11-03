<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_LRS_Creds' ) ) {

    class WP_Adaptive_LRS_Creds{        
        
        private function get_endpoint(){
            $options = self::get_options();
            return $options['lrs_endpoint'];        
        }

        private function get_key(){
            $options = self::get_options();
            return $options['lrs_key'];       
        }

        private function get_secret(){
            $options = self::get_options();
            return $options['lrs_secret'];        
        }

        public static function get_lrs(){
            $wp_lrs = new TinCan\RemoteLRS(
				self::get_endpoint(),
				// xAPI version
				'1.0.1',
				self::get_key(), //key
				self::get_secret() //secret
            );
            return $wp_lrs;
        }

        public static function get_options(){
            if (get_option('wp_adaptive_options') != NULL) {
                $options = get_option('wp_adaptive_options');
                return $options;
            } else {
                return false;
            }
        }

    }
}

new WP_Adaptive_LRS_Creds();