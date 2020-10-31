<?php

/* 
 * TEMPLATE NAME: SINGLE-ASSESMENT
 */

if( $post->post_type == 'assessment' ) :
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();

            ?>

            <div class="wp-adaptive assessment">

                <?php
                echo the_content();                
                echo '<form class="form">'; 
                    
                for ($i = 1 ; $i <= 5 ; $i++){ 
                    if( get_post_meta( get_the_ID(), 'wp_adaptive_assessment_option_' . $i, true ) ){
                        echo
                        '<div class="form-element">                        
                            <div class="radio-wrapper">
                                <input class="radio" type="radio" id="wp_adaptive_assessment_option_' . $i . '" name="assessment_question" value="wp_adaptive_assessment_option_' . $i . '"/> 
                            </div>
                            <div class="clear"></div>
                            <div class="label-wrapper">
                                <label for="wp_adaptive_assessment_option_' . $i . '">' . get_post_meta( get_the_ID(), 'wp_adaptive_assessment_option_' . $i, true ) . '</label><br>
                            </div>
                        </div>';
                    }
                 }
                
                echo '</form>';
                
                echo '<div class="assessment-correct">That\'s correct! Sequencing content, hold tight... </div>';
                echo '<div class="assessment-incorrect">Sorry, that\'s incorrect! Sequencing content, hold tight... </div>';

                echo '<div class="submit-buttons">';
                $next_post_link_url = get_permalink( get_adjacent_post(false,'',false)->ID );
                
                echo '<a class="submit-button wp-adaptive-button i-dont" id="1" href=""/>I don\'t know this</a>';
                echo '<a class="submit-button wp-adaptive-button i-think" id="2" href=""/>I think I know this</a>';
                echo '<a class="submit-button wp-adaptive-button i-know" id="3" href=""/>I know this</a>';

                echo '</div>';  

                ?>

            </div>

            <script>
            
            function getResponse() { 
                var ele = document.getElementsByName('assessment_question');                
                for(i = 0; i < ele.length; i++) { 
                    if(ele[i].checked) 
                    return (ele[i].value); 
                } 
            } 
                            
		
            // when user clicks the link
            $('.submit-button').click( function(e) { 
                e.preventDefault();
                var confidence = $(this).attr("id");
                // submit the data
                $.post(ajax_public.ajaxurl, {
                    <?php
                        $post = get_the_id();
                        $user = wp_get_current_user()->ID;
                    ?>
                    nonce:     ajax_public.nonce,
                    action:    'assessment_submit_statement',
                    wp_data:    [<?php echo $post . ',' . $user ?>, confidence, getResponse()]                    
                    
                }, function(data) {
                    
                    // log data
                    console.log(data);
                    if(data == 'Correct'){
                        $('.assessment-correct').fadeIn();
                    } else {
                        $('.assessment-incorrect').fadeIn();
                    }
                    
                });
                
            });
            
            </script>

            <?php         

        endwhile; 
    endif; 
endif;




?>