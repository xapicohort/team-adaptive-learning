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
                                <input class="radio" type="radio" id="option_' . $i . '" name="assessment_question" value="option_' . $i . '"/> 
                            </div>
                            <div class="clear"></div>
                            <div class="label-wrapper">
                                <label for="option_' . $i . '">' . get_post_meta( get_the_ID(), 'wp_adaptive_assessment_option_' . $i, true ) . '</label><br>
                            </div>
                        </div>';
                    }
                 }
                
                echo '</form>';  

                $next_post_link_url = get_permalink( get_adjacent_post(false,'',false)->ID );
                
                echo '<a class="submit-button button" href="' . $next_post_link_url .'"/>Submit</a>';
                                
                ?>

            </div>

            <?php         

        endwhile; 
    endif; 
endif;




?>