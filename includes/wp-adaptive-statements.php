<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
	exit;
}

if ( !class_exists( 'WP_Adaptive_Statement' ) ) {

    class WP_Adaptive_Statement { 
        
        function __construct() {  
            
            add_action( 'wp_enqueue_scripts', array( $this, 'ajax_public_enqueue_scripts' ) );
            // ajax hook for logged-in users: wp_ajax_{action}
            add_action( 'wp_ajax_node_view_statement', array( $this, 'node_view_statement' ) );
            // ajax hook for non-logged-in users: wp_ajax_nopriv_{action}
            add_action( 'wp_ajax_nopriv_node_view_statement', array( $this, 'node_view_statement' ) );
            // ajax hook for logged-in users: wp_ajax_{action}
            add_action( 'wp_ajax_assessment_submit_statement', array( $this, 'assessment_submit_statement' ) );
            // ajax hook for non-logged-in users: wp_ajax_nopriv_{action}
            add_action( 'wp_ajax_nopriv_assessment_submit_statement', array( $this, 'assessment_submit_statement' ) );
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

        public static function get_object_id( $post ){
            // Add / if not the last character in options field
            $object_id = WP_Adaptive_LRS_Creds::get_options()['object_id_format'];
            if( substr( $object_id, -1 ) != '/' ){ $object_id .= '/'; }
            // Concatenates object id from options field with post type and post id
            $object_id .=  $post->post_type . '/' . $post->ID;
            return $object_id;
        }



        /************************************                 
             NODE VIEW      
        ************************************/ 


        public function node_view_statement(){
            // get post, user, taxonomy data
            $post = get_post( intval ( $_POST['wp_data'][0] ) );
            $user = get_user_by( 'ID', $_POST['wp_data'][1] );
            $term = get_the_terms( $post->ID, 'expert-model-item' );
            $parent = get_post( $post->node_parent_id );                   
            
            // check nonce  
            check_ajax_referer( 'ajax_public', 'nonce' );

		    $lrs = WP_Adaptive_LRS_Creds::get_lrs();
            
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
                ['id' => $post->wp_adaptive_object_id,
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



        /************************************                 
               ASSESSMENT ATTEMPT   
        ************************************/ 


        
        private function get_mc_options($post, $choiceNum){
            $options = '';
            for($x=1; $x<=$choiceNum; $x++){               
                $option = get_post_meta( $post, 'wp_adaptive_assessment_option_' . $x, true );
                if( !empty( $option ) ){
                    $options .= 
                    '{
                        "id": "'. $option .'",
                        "description": {
                            "en-US": "'. $option .'"
                        }
                    },';        
                }                
            } 
            $options = substr($options, 0, -1);
            return $options; 
        }

        private function get_mc_correct_answer_val( $post ){
            $correctResponse = get_post_meta( $post->ID, 'wp_adaptive_assessment_option_correct', true  );
            $correctResponseKey = substr($correctResponse, 0, -8);
            $correctResponseVal = get_post_meta( $post->ID, $correctResponseKey, true );
            return $correctResponseVal;
        }

        private function get_mc_correct_answer_key($post){
            $correctResponse = get_post_meta( $post->ID, 'wp_adaptive_assessment_option_correct', true  );
            return $correctResponse;
        }

        private function get_mc_response_val($post, $response){
            $responseVal = get_post_meta( $post->ID, $response, true );
            return $responseVal;
        }

        private function is_mc_correct( $post, $response ){
            if( $response . '_correct' == $this->get_mc_correct_answer_key( $post ) ){
                return true;
            } else {
                return false;
            }
        } 

        public function assessment_submit_statement(){
            // get post, user, taxonomy data
            $post = get_post( intval ( $_POST['wp_data'][0] ) );
            $user = get_user_by( 'ID', $_POST['wp_data'][1] );
            $term = get_the_terms( $post->ID, 'expert-model-item' );
            $parent = get_post( $post->assessment_parent_id );
            $confidence = $_POST['wp_data'][2]; 
            $response = $_POST['wp_data'][3];
            $responseVal = $this->get_mc_response_val( $post, $response );
            $isCorrect = $this->is_mc_correct( $post, $response ); 
            $score = ( $isCorrect ) ? '1' : '0';

               
            // check nonce  
            check_ajax_referer( 'ajax_public', 'nonce' );

		    $lrs = WP_Adaptive_LRS_Creds::get_lrs();
            
            $actor = new TinCan\Agent(
				['mbox' => 'mailto:' . $user->user_email ]
            );

            $verb = new TinCan\Verb(
                ['id' => 'http://adlnet.gov/expapi/verbs/attempted',
                'display' => [
                    'en-US' => 'attempted'
                ]
                ]
            );

                     
            $activityJSON = 
            '{
                "id": "' . $this->get_object_id( $post ) . '",
                "definition": {
                    "name": {
                        "en-US":"' . $post->post_content . '"
                    },
                    "description":{
                        "en-US":"' . $post->post_content . '"
                    },
                    "type": "http://adlnet.gov/expapi/activities/cmi.interaction",
                    "moreInfo": "https://bradyriordan.com/team-adaptive-learning/difficulty/'. $post->wp_adaptive_difficulty_assessment .'",
                    "interactionType": "choice",
                    "choices":[' . $this->get_mc_options( $post->ID, 5 ) . '],
                    "correctResponsesPattern": [
                        "' . $this->get_mc_correct_answer_val( $post ) . '"
                    ] 
                }            
                      
            }';

            $activity = TinCan\Activity::fromJSON($activityJSON);

            $result = new TinCan\Result(
                ['score' => [
                    'min' => '0',
                    'max' => '1',
                    'raw' => $score
                ],
                'success' => $isCorrect,
                'completion' => 'true',
                'response' => $responseVal,
                'extensions' => [
                    'https://bradyriordan.com/team-adaptive-learning/confidence' => $confidence
                ]
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
                    'result' => $result
				]
			);

			$response = $lrs->saveStatement($statement);
			if ($response->success) {
                if( $isCorrect ) { 
                    print "Correct"; 
                } else { 
                    print "Incorrect"; 
                }		
			} else {
				print "Error statement not sent: " . $response->content . "\n";
			}            
			wp_die();
		}

    }
}

new WP_Adaptive_Statement();