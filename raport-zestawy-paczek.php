<?php



//wygenerowanie numerow zamowien na podstawie daty odbioru
function order_ids_z_daty($data) {
	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT p.ID FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_data_odbioru'
		AND pm.meta_value='%s' 
        AND p.post_type = 'shop_order'
        AND (p.post_status = 'wc-completed' OR p.post_status = 'wc-processing')
    ",  $data) );
    return $res;
}


//wygenerowanie numerow elementow zamowien na podstawie daty odbioru
function orders_items_ids_z_daty($data) {
	global $wpdb;
	$wpdb_woocommerce_order_items=$wpdb->prefix.'woocommerce_order_items';

	$res = $wpdb->get_col( $wpdb->prepare("
  		SELECT oi.order_item_id FROM 
		{$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p 	
		ON p.ID = pm.post_id
		INNER JOIN {$wpdb_woocommerce_order_items} oi
		ON p.ID=oi.order_id
		WHERE pm.meta_key = '_data_odbioru'
		AND pm.meta_value='%s' 
        AND p.post_type = 'shop_order'
        AND (p.post_status = 'wc-completed' OR p.post_status = 'wc-processing')
        ", $data) );
    return $res; 
}


//raportowanie zestawow do paczek
function packages_report($date) {
	$ids=orders_items_ids_z_daty($date);
	foreach ($ids as $id){
		$product_id=wc_get_order_item_meta($id, '_product_id', TRUE);
		if ($product_id){
			$qty=wc_get_order_item_meta($id, '_qty', TRUE);		
			$product_name=get_the_title($product_id);
			$product_sums[$product_id]+=$qty;
			$products_packages[$product_id][$qty]+=1;	
		}
	}
	$res='';
	$res.= '<strong>Data:</strong> ' . $date . '<br><strong>Sumy zamówień:</strong><br>';
	if(isset($product_sums))
	foreach ($product_sums as $product_id => $product_sum){
		$res.=  $product_sum . ' x ' .get_the_title($product_id) . '<br>' ;
	}
	$res.= '<br><strong>Zestawy do przygotowania:</strong> (wielkość zestawu x liczba zestawów)<br>';
	if(isset($products_packages))
	foreach ($products_packages as $product_id => $product_packages){
		$res.= get_the_title($product_id) . ' - ';
		foreach ($product_packages as $package_qty => $number_of_packages)
				$res.= $package_qty . 'x' .$number_of_packages . ', ';
		$res.= '<br>';
	}

	return $res;
}


function orders_report_array($date) {

	$order_ids=order_ids_z_daty($date);
//	print_r($order_ids);
	$groups = []; 
	
	foreach ($order_ids as $order_id){
		$zam='';
		$id_grupy_odb=get_post_meta($order_id, '_id_grupy', true);	
		$prefix=get_post_meta($id_grupy_odb, 'prefix', true);
		if ($prefix){
			$prefix.= '-';
		}
		$numer_paczki=get_post_meta($order_id,'_numer_paczki', true );
		$user_id=get_post_meta($order_id, '_customer_user', true);
		$user_info = get_userdata($user_id);
		$zam.='<strong>Paczka: '. $prefix . $numer_paczki .', zam: ' . $order_id . ' ' . $user_info->first_name . ' ' . $user_info->last_name . ', Tel: ' . get_user_meta( $user_id, 'billing_phone', true ) .'</strong><br>';
		
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ( $items as $item ) {
			
			$product_name = $item->get_name();
			$product_qty = $item->get_quantity();		
//			$product_id = $item->get_product_id();
			$zam.= $product_name .' x' . $product_qty . ', '; 
		}		
		$zam.= '<br><br>';
		$groups[$id_grupy_odb].=$zam;	
		}
		
		$res=[];
		foreach ( $groups as $group => $zamowienia ) {
			$res[$group]= '<strong>Data:</strong> ' .$date. ', <strong>Grupa odbioru:</strong> ' . get_the_title($group) . '<br><br>' . $zamowienia;
	}
	
	return $res;
}

function orders_report($date){
	$res='';
	$res.= '<br><strong>Zamowienia:</strong><br>';
	$groups=orders_report_array($date);
	foreach ( $groups as $zamowienia) {
		$res.=$zamowienia . '<br>';
	}
	return $res;
}







//dodanie strony raport do menu
add_action( 'admin_menu', 'wpse_916933_register' );

function wpse_916933_register()
{
    add_menu_page(
        'Raport PDF',     // page title
        'Raport PDF',     // menu title
        'view_woocommerce_reports',   // capability
        'raportpdf',     // menu slug
        'wpse_916933_render' // callback function
    );
}
function wpse_916933_render(){
    global $title;

    print '<div class="wrap">';
    print "<h1>$title</h1>";

    $file = plugin_dir_path( __FILE__ ) . "strona-raportu-paczek-admin.php";

 
    if ( file_exists( $file ) )
        require $file;

    print '</div>';
}







//dodanie strony raport z podgladem html do menu
add_action( 'admin_menu', 'wpse_9169333_register' );

function wpse_9169333_register()
{
    add_menu_page(
        'Raport',     // page title
        'Raport',     // menu title
        'view_woocommerce_reports',   // capability
        'raport',     // menu slug
        'wpse_9169333_render' // callback function
    );
}
function wpse_9169333_render(){
    global $title;

    print '<div class="wrap">';
    print "<h1>$title</h1>";

    $file = plugin_dir_path( __FILE__ ) . "strona-raportu-paczek-admin-html.php";

 
    if ( file_exists( $file ) )
        require $file;

 //   print "<p class='description'>Included from <code>$file</code></p>";

    print '</div>';
}

