<?php

/* 
 * WP ADAPTIVE HEADER
 */

?><!DOCTYPE html>

<html>

    <head>
        <?php wp_head(); ?>
        <!-- Need to find reliable way of including this js -->
        <script type='text/javascript' src='/wordpress/wp-content/plugins/wp-adaptive/includes/jquery-3.5.1.min.js'></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" >

        <link rel="profile" href="https://gmpg.org/xfn/11">

        <style>
            .wp-adaptive{
                width: 60%; 
                margin: 50px auto; 
                font-family: 
                sans-serif; 
                
            }

            .wp-adaptive h1{
                text-align: center;
            }   

            .assessment p{
                text-align: center;
                font-size: 1.8em;  
                font-weight: bold;      
            }  

            .license{
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

            .wp-adaptive-button:hover{
                cursor: pointer;
                background-color: #4444442b;
            }
            
            .next-button{
                float:right;        
            }

            .back-button{
                float:left;         
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

    </head>

    <body>


