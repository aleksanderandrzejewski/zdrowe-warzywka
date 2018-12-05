<?php

add_action( 'woocommerce_checkout_process', 'tn_minimum_order_amount' );
add_action( 'woocommerce_review_order_before_submit' , 'tn_minimum_order_amount' );
add_action( 'woocommerce_before_cart' , 'tn_minimum_order_amount' );

function tn_hide_woocommerce_order_button_html($html) {
	$html = '';
	return $html;
} 


function tn_minimum_order_amount() {

//	$minimum = get_option( 'ordervalue_minimum' );
//	$enabled = (get_option( 'ordervalue_enabled' ) == 'yes') ? TRUE : FALSE; 

 $minimum = 10;
 $enabled = true;
 

    if ( ( WC()->cart->subtotal < $minimum ) && ($enabled == TRUE) ) {

        if( is_cart() ) {

            wc_print_notice( 
                sprintf( 'Minimalna wartość zamówienia to %s, aktualna wartość koszyka to %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->subtotal )
                ), 'error' 
            );
            
            remove_action( 'woocommerce_proceed_to_checkout','woocommerce_button_proceed_to_checkout', 20);
        
       } elseif( is_checkout() ) {

            wc_print_notice( 
                sprintf( 'Minimalna wartość zamówienia to %s, aktualna wartość koszyka to %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->subtotal )
                ), 'notice' 
            ); 
          
            add_filter( 'woocommerce_order_button_html', 'tn_hide_woocommerce_order_button_html');

        } else {

            wc_add_notice( 
                sprintf( 'Minimalna wartość zamówienia to %s, aktualna wartość koszyka to %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->subtotal )
                ), 'error' 
            );
			
         }
    }

}
