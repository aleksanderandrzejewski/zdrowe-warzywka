<?php

//znajdz najblizsza date z listy dat
function find_closest($dates_array, $days_before)
{
	if(gettype($days_before=='array'))
		$days_before=$days_before[0];
	
	$wyprzedzenie=($days_before-1)*86400;
	$teraz= strtotime( date('m/d/Y h:i:s a', time()));
	
    foreach($dates_array as $day)
    {
         $interval[] = strtotime($day) - ( $teraz + $wyprzedzenie);
    }
	
$positive = array_filter($interval, function ($v) {
  return $v > 0;
});
	
	
    asort($positive);
    $closest = key($positive);
	
    return $dates_array[$closest];
	

}


function zamowienia_do($data_odbioru, $days_before){
	return date('Y-m-d', strtotime($data_odbioru. ' - ' . $days_before . ' days'));
}


function _get_all_meta_values($key) {
    global $wpdb;
	$result = $wpdb->get_col( 
		$wpdb->prepare( "
			SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s' 
			AND p.post_status = 'publish'
			ORDER BY pm.meta_value", 
			$key
		) 
	);

	return $result;
}



function klucze_akcji_z_zamowieniami() {
	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_type = '%s'
    ",  '_klucz_akcji' , 'shop_order' ) );
    return array_map("unserialize", $res);
}

function daty_z_zamowieniami() {
	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_type = '%s'
    ",  '_data_odbioru' , 'shop_order' ) );
    //return array_map("unserialize", $res);
	return $res;
}


function klucze_akcji_zaplanowanych(){
	
	$args=array('post_type' => 'akcja','posts_per_page'=>100,'orderby'=>'date', 'order'=>'ASC');
	$my_query = new WP_Query($args);
		
	if ($my_query->have_posts()) : 
		while ($my_query->have_posts()) : $my_query->the_post();   
			
				$daty= unserialize(get_post_custom_values('daty')[0]);
				foreach ($daty as $data)
					$klucze_akcji[]=serialize(array('_data_odbioru' => $data, '_id_grupy_odb' => (string) get_the_ID()));	
		endwhile;
	endif;
	return $klucze_akcji;
}



function daty_akcji_zaplanowanych(){
	
	$args=array('post_type' => 'akcja','posts_per_page'=>100,'orderby'=>'date', 'order'=>'ASC');
	$my_query = new WP_Query($args);
	$daty=[];
	if ($my_query->have_posts()) : 
		while ($my_query->have_posts()) : $my_query->the_post();   
				$daty=array_merge($daty,unserialize(get_post_custom_values('daty')[0]));
		endwhile;
	endif;
	
	return $daty;
}




function data_odb_z_klucza($klucz_akcji){
	$odczytane = unserialize($klucz_akcji); 
	$data = $odczytane['_data_odbioru'];
	return $data;
}
	
function data_odb_z_order_id($order_id){
	$odczytane = unserialize(get_post_meta( $order_id, '_klucz_akcji', true));
	return $odczytane['_data_odbioru'];
}
		
function id_grupy_odb_z_klucza($klucz_akcji){
	$odczytane = unserialize($klucz_akcji);
	return $odczytane['_id_grupy_odb'];
}
	
function id_grupy_odb_z_order_id($order_id){
	$odczytane = unserialize(get_post_meta( $order_id, '_klucz_akcji', true));
	return $odczytane['_id_grupy_odb'];
}



function get_orders_total_na_akcje($value, $key='_klucz_akcji')
{	
	$customer_orders = get_posts( array(
        'numberposts' => - 1,
       'meta_key'    => $key,
        'meta_value'  => $value,
        'post_type'   => array( 'shop_order' ),
        'post_status' => array( 'wc-completed', 'wc-processing'  )     
    ) );
	
    $total = 0;
    foreach ( $customer_orders as $customer_order ) {
        $order = wc_get_order( $customer_order );
        $total += $order->get_total();
    }
    return $total;
}	


//wygenerowanie numerow zamowien na podstawie klucza akcji
function order_ids_z_klucza($klucz) {
	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT p.ID FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_klucz_akcji'
		AND pm.meta_value='%s' 
        AND p.post_type = 'shop_order'
    ",  serialize($klucz) ) );
    return $res;
}



//wyznaczenie kolejnego numeru paczki
function get_orders_next_shipment_number_na_akcje($value, $key='_klucz_akcji')
{	

	
	$order_ids=order_ids_z_klucza($value);
	
    $max_shipment_number = 0;
    foreach ( $order_ids as $id ) {
		$numer_paczki = get_post_meta($id, '_numer_paczki', true);
		if($numer_paczki>$max_shipment_number)
			$max_shipment_number=$numer_paczki;
    }
	
	
	$max_shipment_number += 1;
    return $max_shipment_number;
}	




