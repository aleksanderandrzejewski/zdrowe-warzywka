<?php

// Dodanie pola wyboru do formuarza zamówienia
add_action('woocommerce_before_order_notes', 'wps_add_select_checkout_field'); //woocommerce_before_order_notes woocommerce_review_order_before_payment
function wps_add_select_checkout_field( $checkout ) {
	
	$product_id = 22; //doładowanie konta
	$in_cart = false;
	foreach( WC()->cart->get_cart() as $cart_item ) {
    $product_in_cart = $cart_item['product_id'];
	if ( $product_in_cart === $product_id ) $in_cart = true;
	}
 
	//sprawdzenie czy doładowanie konta jest w koszyku
    if ( !$in_cart ) {
		
		echo '<h2>'.__('Odbiór w:').'</h2>';
		
		$my_array=array();
		$my_array['']='Wybierz punkt odbioru'; //placeholder

		//punkt własny	
		$id_grupy_odb = get_user_meta(get_current_user_id(), 'id_grupy', true);
		$najblizsza_data = find_closest(get_post_meta($id_grupy_odb,'daty',true ), get_post_meta($id_grupy_odb,'wyprzedzenie',true ) );
		
		if ($najblizsza_data != '') 
		{	
			$opcja1 = $najblizsza_data .' '. get_the_title($id_grupy_odb);
			$opcja1_id=serialize(array('_data_odbioru' => $najblizsza_data, '_id_grupy_odb' => $id_grupy_odb));
			$my_array[$opcja1_id]= $opcja1;
		}	


		//punkty publiczne
		$args=array('post_type' => 'akcja','posts_per_page'=>100,'orderby'=>'date', 'order'=>'ASC');
		$my_query = new WP_Query($args);
			
		if ($my_query->have_posts()) : 
			while ($my_query->have_posts()) : $my_query->the_post();   
				$id_grupy_odb = get_the_ID();
				
				if (get_post_custom_values('dostepny_dla_wszystkich_kont')[0]){
					$najblizsza_data = find_closest( unserialize(get_post_custom_values('daty')[0])  , get_post_custom_values('wyprzedzenie'));
					if ($najblizsza_data != '') {
						$opcja1 = $najblizsza_data . ' ' . get_the_title(get_the_ID());
						$opcja1_id=serialize(array('_data_odbioru' => $najblizsza_data, '_id_grupy_odb' => get_the_ID()));
						$my_array[$opcja1_id]= $opcja1;
					}
				}

				
			endwhile;
		endif;

		
		woocommerce_form_field( '_klucz_akcji', array(
			'type'          => 'select',
			'class'         => array( 'wps-drop' ),
			'label'         => __( 'Wybierz' ),
			'required'      => true,					
			'options'       => $my_array
		),
		$checkout->get_value( '_klucz_akcji' ));
	
	
	}
}

//komunikat o błędzie w przypadku nie wybrania daty/punktu odbioru
add_action('woocommerce_checkout_process', 'czyjestpunktodbioru');
function czyjestpunktodbioru() {
 
    $product_id = 22; //doładowanie konta
	$in_cart = false;
	foreach( WC()->cart->get_cart() as $cart_item ) {
    $product_in_cart = $cart_item['product_id'];
	if ( $product_in_cart === $product_id ) $in_cart = true;
	}
 
	//sprawdzenie czy doładowanie konta jest w koszyku
    if ( !$in_cart ) {
	if ( empty( $_POST['_klucz_akcji'] ) )
		wc_add_notice( __( 'Wybierz punkt odbioru z rozwijanej listy' ), 'error' );
    }
}



//skasowenie pól w formularzu zamówienia
add_filter( 'woocommerce_checkout_fields', 'remove_fields', 9999 );
 
function remove_fields( $woo_checkout_fields_array ) {
 
	// unset( $woo_checkout_fields_array['billing']['billing_first_name'] );
	// unset( $woo_checkout_fields_array['billing']['billing_last_name'] );
	// unset( $woo_checkout_fields_array['billing']['billing_phone'] );
	// unset( $woo_checkout_fields_array['billing']['billing_email'] );
	// unset( $woo_checkout_fields_array['order']['order_comments'] );
	
	unset( $woo_checkout_fields_array['billing']['billing_company'] );
	unset( $woo_checkout_fields_array['billing']['billing_country'] );
	unset( $woo_checkout_fields_array['billing']['billing_address_1'] );
	unset( $woo_checkout_fields_array['billing']['billing_address_2'] );
	unset( $woo_checkout_fields_array['billing']['billing_city'] );
	unset( $woo_checkout_fields_array['billing']['billing_state'] );
	//unset( $woo_checkout_fields_array['billing']['billing_postcode'] );
	return $woo_checkout_fields_array;
}

//zrobienie pol niewyymaganymi
function sv_unrequire_wc_fields( $fields ) {
    $fields['billing']['billing_country'] = false;
	$fields['billing']['billing_address_1'] = false;
	$fields['billing']['billing_address_2'] = false;
	$fields['billing']['billing_city'] = false;
	$fields['billing']['billing_state'] = false;
    return $fields;
}
//add_filter( 'woocommerce_billing_fields', 'sv_unrequire_wc_fields' );



//ukrycie sekcji alternatywnego adresu
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false');



//dodanie informacji meta do zamowienia
 add_action('woocommerce_checkout_update_order_meta', 'wps_select_checkout_field_update_order_meta');
 function wps_select_checkout_field_update_order_meta( $order_id ) {
   if ($_POST['_klucz_akcji']) 
   {
	   //dodanie klucza akcji do zamówienia
		$post3=$_POST['_klucz_akcji'];
		update_post_meta( $order_id, '_klucz_akcji', $post3 );	  	
		
   }
 }
 
//dodanie pól z datą, numerem grupy na podstawie klucza akcji 
add_action('woocommerce_thankyou', 'before_checkout_create_order', 7, 2);
function before_checkout_create_order( $order_id ) {
	$odczytane = unserialize(get_post_meta( $order_id, '_klucz_akcji', true));
	
	$data_odbioru=$odczytane['_data_odbioru'];
	$id_grupy=$odczytane['_id_grupy_odb'];
	
	update_post_meta( $order_id, '_data_odbioru', $data_odbioru  );
	update_post_meta( $order_id, '_id_grupy', $id_grupy );
		
	$opcja1 = $data_odbioru .' '. get_the_title($id_grupy);
	update_post_meta( $order_id,'_data_i_grupa',$opcja1 );
	
			//dodanie kelejnego numeru paczki do zamowienia
		$numer_paczki=get_orders_next_shipment_number_na_akcje(get_post_meta( $order_id, '_klucz_akcji', true));
		update_post_meta( $order_id, '_numer_paczki', $numer_paczki );
}
 
 

//* wyswietalnie informacji o dacie odbioru i grupie w podgladzie zamowienia w panelu admina
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wps_select_checkout_field_display_admin_order_meta', 10, 1 );
function wps_select_checkout_field_display_admin_order_meta($order){
	echo '<p><strong>'.__('Data i miejsce odbioru').':</strong> ' . get_post_meta( $order->id, '_data_i_grupa', true ) . '</p>';
}
//*


//automatyczne przeliczanie oplaty po zmianie miejsca odbioru (jquery)
add_action( 'wp_footer', 'woocommerce_przelicz_dostawe' );
function woocommerce_przelicz_dostawe() {
    if (is_checkout()) {
    ?>
    <script type="text/javascript">
    jQuery( document ).ready(function( $ ) {
        $('#_klucz_akcji').change(function(){							//$('#_klucz_akcji').click(function(){
            jQuery('body').trigger("update_checkout");                 
        });
    });
    </script>
    <?php
    }
}


//dodanie oplaty za dostawe w momencie przeliczania oplat
add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee', 999);

function woo_add_cart_fee( WC_Cart $cart ){                                             
        if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {
        return;
    }

    if ( isset( $_POST['post_data'] ) ) {                 
        parse_str( $_POST['post_data'], $post_data );		        
    } else {
        $post_data = $_POST; // fallback for final checkout (non-ajax)
    }

    if (isset( $post_data['_klucz_akcji'])) {	
		$post=stripslashes($post_data['_klucz_akcji']);
		$odczytane=unserialize($post);
		$id_grupy=$odczytane['_id_grupy_odb'];
		$fees = get_post_meta($id_grupy, 'cena_dostawy', true);		
		if($id_grupy)
			WC()->cart->add_fee('Odbiór w: '. get_the_title($id_grupy) .', data: '. $odczytane['_data_odbioru'] , $fees ); 	//zarejestrowanie oplaty za dostawę
    }	
}