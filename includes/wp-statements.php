<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Statement' ) ) {

    class WP_Statement { 
        
        function __construct() {  
            
            add_action( 'wp_enqueue_scripts', array( $this, 'ajax_public_enqueue_scripts' ) );
            // ajax hook for logged-in users: wp_ajax_{action}
            add_action( 'wp_ajax_node_view_statement', array( $this, 'node_view_statement' ) );
            // ajax hook for non-logged-in users: wp_ajax_nopriv_{action}
            add_action( 'wp_ajax_nopriv_node_view_statement', array( $this, 'node_view_statement' ) );

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

        public function node_view_statement(){
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
				'yJV2TEVZl38wzLDxXhw', //key
				'AzvPZYLo3LsYe5O2UEc' //secret
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

    }
}

new WP_Statement();