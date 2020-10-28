<?php

/* 
 * TEMPLATE NAME: SINGLE-NODE
 */

require_once( plugin_dir_path( __FILE__ ).'wp-adaptive-lrs-creds.php' ); 

if( $post->post_type == 'node' ) :

    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();

            ?>

            <div class="wp-adaptive">

                <?php
                echo '<h1>' . get_the_title() . '</h1>';
                echo '<div>' . the_content() . '</div>';

                switch ($post->wp_adaptive_license) {
                    case 1:
                        echo "<p class='license'>Attribution (CC BY)</p>";
                        break;
                    case 2:
                        echo "<p class='license'>Attribution ShareAlike (CC BY-SA)</p>";
                        break;
                    case 3:
                        echo "<p class='license'>Attribution-NoDerivs (CC BY-ND)</p>";
                        break;
                    case 4:
                        echo "<p class='license'>Attribution-NonCommercial (CC BY-NC)</p>";
                        break;
                     case 5:
                        echo "<p class='license'>Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)</p>";
                        break;
                    case 6:
                        echo "<p class='license'>Attribution-NonCommercial-NoDerivs (CC BY-NC-ND)</p>";
                        break;   
                    case 7:
                        echo "<p class='license'>Copyright</p>";
                        break; 
                }               

                $next_post_link_url = get_permalink( get_adjacent_post(false,'',false)->ID );
                $prev_post_link_url = get_permalink( get_adjacent_post(false,'',true)->ID );

                echo '<a class="next-button wp-adaptive-button" href="' . $next_post_link_url .'"/>Next  &rarr;</a>';
                echo '<a class="back-button wp-adaptive-button" href="' . $prev_post_link_url .'"/>&larr;  Back</a>';

                ?>

            </div>

            <script>                
		
                // when user clicks the link
                $(document).ready( function(event) {  
                                   
                    // submit the data
                    $.post(ajax_public.ajaxurl, {
                        <?php
                            $post = get_the_id();
                            $user = wp_get_current_user()->ID;
                        ?>
                        nonce:     ajax_public.nonce,
                        action:    'node_view_statement',
                        wp_data:    [<?php echo $post . ',' . $user ?>]                    
                        
                    }, function(data) {
                        
                        // log data
                        console.log(data);
                        
                    });
                    
                });
                
            </script>

            <?php         

        endwhile; 
    endif; 
endif;




?>







