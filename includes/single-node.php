<?php 
?>

<style>
    .wp-adaptive-content{
        width: 60%; 
        margin: 50px auto; 
        font-family: 
        sans-serif; 
        font-size: 1.6em;
    }

    .wp-adaptive-content h1{
        text-align: center;
    }   

    .wp-adaptive-license{
        font-size: .6em;
        font-style: italic;
        color: grey;
        text-align: center;
    }

    .wp-adaptive-button{
        padding: 10px 30px;
        font-weight: bold;
        font-size: 1em;
        margin-top: 30px;
        text-decoration: none;
        color: black;
    }

    .wp-adaptive-button:hover{
        cursor: pointer;
    }
    
    .next-button{
        float:right;        
    }

    .back-button{
        float:left;         
    }
    
</style>

<?php

if( $post->post_type == 'node' ) :
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();

            ?>

            <div class="wp-adaptive-content">

                <?php
                echo '<h1>' . get_the_title() . '</h1>';
                echo '<div>' . the_content() . '</div>';

                switch ($post->wp_adaptive_license) {
                    case 1:
                        echo "<p class='wp-adaptive-license'>Attribution (CC BY)</p>";
                        break;
                    case 2:
                        echo "<p class='wp-adaptive-license'>Attribution ShareAlike (CC BY-SA)</p>";
                        break;
                    case 3:
                        echo "<p class='wp-adaptive-license'>Attribution-NoDerivs (CC BY-ND)</p>";
                        break;
                    case 4:
                        echo "<p class='wp-adaptive-license'>Attribution-NonCommercial (CC BY-NC)</p>";
                        break;
                     case 5:
                        echo "<p class='wp-adaptive-license'>Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)</p>";
                        break;
                    case 6:
                        echo "<p class='wp-adaptive-license'>Attribution-NonCommercial-NoDerivs (CC BY-NC-ND)</p>";
                        break;   
                    case 7:
                        echo "<p class='wp-adaptive-license'>Copyright</p>";
                        break; 
                }               

                $next_post_link_url = get_permalink( get_adjacent_post(false,'',false)->ID );
                $prev_post_link_url = get_permalink( get_adjacent_post(false,'',true)->ID );

                echo '<a class="next-button wp-adaptive-button" href="' . $next_post_link_url .'"/>Next  &rarr;</a>';
                echo '<a class="back-button wp-adaptive-button" href="' . $prev_post_link_url .'"/>&larr;  Back</a>';               
                
                ?>

            </div>

            <?php         

        endwhile; 
    endif; 
endif;




?>







