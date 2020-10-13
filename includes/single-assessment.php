<?php?>

<style>
    .wp-adaptive{
        width: 60%; 
        margin: 50px auto; 
        font-family: sans-serif; 
        font-size: 1.7em;
    }

    .wp-adaptive p{
        text-align: center;
        font-size: 1.6em;        
    }  

    .button{
        padding: 10px 30px;
        font-weight: bold;
        font-size: 1em;
        margin-top: 30px;
        text-decoration: none;
        color: black;
    }

    .button:hover{
        cursor: pointer;
        background-color: #4444442b;
    }
    
    .submit-button{
        float:right;
        border: 1px solid black;
        border-radius: 3px;         
    }

    .form input{
        height: 100%;
    }

    .form-element{
        margin: 30px 0px;
        display: flex;
    }

    .form-element input{
        -webkit-appearance:button;
        -moz-appearance:button;
        appearance:button;
        border:4px solid #ccc;
        border-top-color:#bbb;
        border-left-color:#bbb;
        background:#fff;
        width:30px;
        height:30px;
        border-radius:50%;
    }
    
    .form-element .radio-wrapper{        
        float: left;
        margin-right: 20px;
        height: 100%;
        width: 50px;
    }    

    .form-element .label-wrapper{   
        flex-grow: 1;
    }  

</style>

<?php 

if( $post->post_type == 'assessment' ) :
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();

            ?>

            <div class="wp-adaptive">

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