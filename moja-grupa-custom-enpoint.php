<?php
 
 
function wk_custom_endpoint() {
  add_rewrite_endpoint( 'moje-grupy-odbioru', EP_ROOT | EP_PAGES );
}
 
add_action( 'init', 'wk_custom_endpoint' );


add_filter( 'woocommerce_account_menu_items', 'wk_new_menu_items' );
 
 
 
 
// endpoint - my account
function wk_new_menu_items( $items ) {
    $items[ 'moje-grupy-odbioru' ] = __( 'Moje grupy odbioru', 'webkul' );
    return $items;
}

function my_custom_flush_rewrite_rules() {
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'my_custom_flush_rewrite_rules' );


$endpoint = 'moje-grupy-odbioru';
 
add_action( 'woocommerce_account_' . $endpoint .  '_endpoint', 'wk_endpoint_content' );
 
function wk_endpoint_content() {
	
	echo '<h2 style="margin-bottom:5px">Moje grupy odbioru</h2>';
	$grupa_aktualnego_uz = get_user_meta(get_current_user_id(), 'id_grupy', true);
	$klucze_akcji = array_unique( array_merge(klucze_akcji_z_zamowieniami(), klucze_akcji_zaplanowanych()));
	
	$args = array(
	  'p'         => $grupa_aktualnego_uz, // ID of a page, post, or custom type
	  'post_type' => 'akcja'
	);
	
	$my_group_query = new WP_Query($args); //$grupa_aktualnego_uz
	
	while ( $my_group_query->have_posts() ) : $my_group_query->the_post();
		$output = '<div id="service-hp">'.
                   get_the_post_thumbnail('home-thumb').
                   '<h3 style="margin-bottom:5px">'.
                   get_the_title().
                   '</h3>'. 
				   'Adres: '. get_post_meta(get_the_ID(), 'adres', true);

		$minimum_logistyczne=get_post_meta(get_the_ID(), 'minimum_logistyczne', true);
		if ($minimum_logistyczne) $output .= '<br>Minimum logistyczne: '. $minimum_logistyczne .' zł';   
				   
		$facebook=get_post_meta(get_the_ID(), 'facebook', true);
		if ($facebook) $output .= '<br>Facebook: ' . '<a href="'. $facebook . '">'. $facebook .'</a>';     
				   
		$najblizsa=find_closest(get_post_custom_values('daty'), get_post_custom_values('wyprzedzenie'));
		if ($najblizsa) $output .= '<br>Najbliższa akcja: ' . $najblizsa ;   				   
				   
				   
//		$output .= '<br>' . get_the_excerpt().'<a class="read-more" href="'. get_permalink().'">Czytaj więcej </a>';
		$output .= '</div><br>';
		echo $output;
		if ($minimum_logistyczne)
			progress_bars_z_kluczy_frontend($klucze_akcji, get_the_ID());
	endwhile;
    wp_reset_query();

	
	
	
	
	
	
	
	
	
	
	
	
	$andrew_query = new WP_Query( array( 'post_type' => 'akcja', 'paged' => false ) ); //grupy publiczne
    while ( $andrew_query->have_posts() ) : $andrew_query->the_post();
		if (get_post_custom_values('dostepny_dla_wszystkich_kont')[0]){	
			$output = '<br><div id="service-hp">'.
					   get_the_post_thumbnail('home-thumb').
					   '<h3 style="margin-bottom:5px">'.
					   get_the_title().
					   '</h3>'. 
					   'Adres: '. get_post_meta(get_the_ID(), 'adres', true);
					   
		$minimum_logistyczne=get_post_meta(get_the_ID(), 'minimum_logistyczne', true);
		if ($minimum_logistyczne) $output .= '<br>Minimum logistyczne: '. $minimum_logistyczne .' zł';   	
	
			$facebook=get_post_meta(get_the_ID(), 'facebook', true);
			if ($facebook) $output .= '<br>Facebook: ' . '<a href="'. $facebook . '">'. $facebook .'</a>';     
					   
			$najblizsa=find_closest(get_post_custom_values('daty'), get_post_custom_values('wyprzedzenie'));
			if ($najblizsa) $output .= '<br>Najbliższa akcja: ' . $najblizsa ;   				   
					   
					   
	//		$output .= '<br>' . get_the_excerpt().'<a class="read-more" href="'. get_permalink().'">Czytaj więcej </a>';
			$output .= '</div>';	
			echo $output;
			if ($minimum_logistyczne)
				progress_bars_z_kluczy_frontend($klucze_akcji, get_the_ID());
		}
	endwhile;
    wp_reset_query();

	


}
	

	
	
	








