<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Query' ) ) {

    class WP_Adaptive_Query { 
        
        function __construct() {  
            
            add_action( 'wp_enqueue_scripts', array( $this, 'ajax_public_enqueue_scripts' ) );
            // ajax hook for logged-in users: wp_ajax_{action}
            add_action( 'wp_ajax_save_state', array( $this, 'save_state' ) );
            // ajax hook for non-logged-in users: wp_ajax_nopriv_{action}
            add_action( 'wp_ajax_nopriv_save_state', array( $this, 'save_state' ) );
            // Add LRS creds file
            require_once( plugin_dir_path( __FILE__ ).'wp-adaptive-lrs-creds.php' ); 

        }

        public function ajax_public_enqueue_scripts( $hook ) {
           
            // define script url
            $script_url = plugins_url( '/ajax-public.js', __FILE__ );
        
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

        // private function get_activity(){
        //     $post = get_post( 149 );
        //     $activity = $post->wp_adaptive_object_id;
        //     return $activity;
        // }

        private function get_agent( $user_id ){
            $user = get_user_by( 'ID', $user_id );
            $agent = new TinCan\Agent(
				['mbox' => 'mailto:' . $user->user_email]
            );
            return $agent;            
        }

        
        public function save_state(){

            $lrs = WP_Adaptive_LRS_Creds::get_lrs();

            $activity = 'http://bradyriordan/team-adaptive-learning/xapi/assessment/149';
            
            $agent = $this->get_agent( $_POST['wp_data'][1] );

            $activity = new TinCan\Activity(
                [ 'id' => 'http://bradyriordan/team-adaptive-learning/xapi/node/100' ]
            );

            $saveResponse = $lrs->saveState(
                $activity,
                $agent,
                'activity-state',
                '{
                    "expertModelItems:{
                        "actor": "0",
                        "verb": "0",
                        "object": "0",
                    }
                    "progress":"100"
                }'
            );

            if ($saveResponse->success) {                
                $retrieveResponse = $lrs->retrieveState(
                    $activity,
                    $agent,
                    'activity-state'
                );
                if ($retrieveResponse->success) {
                    if($retrieveResponse->content->getContent()){
                        print $retrieveResponse->content->getContent();
                    }     
                }                                        
                    //$deleteResponse = $lrs->deleteState($activity, $agent, 'document');
                
            } else {
                print "State not saved";
            }
            wp_die();

        }  

    }

}

new WP_Adaptive_Query();