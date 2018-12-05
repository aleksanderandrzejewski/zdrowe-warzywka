<?php
//szortkod

add_shortcode('lista-punktow-odbioru', 'pokazpunktyodbioru');


function pokazpunktyodbioru() {

    $output = '<div class="clear"></div><div class="childs grid_12">';

    $andrew_query = new WP_Query( array( 'post_type' => 'akcja', 'paged' => false ) );
    while ( $andrew_query->have_posts() ) : $andrew_query->the_post();
	{
	if (!get_post_meta( get_the_ID(), 'prywatny', true ) )
		{

			$output .= '<div id="service-hp">'.
					   get_the_post_thumbnail('home-thumb').
					   '<h2 style="margin-bottom:5px">'.
					   get_the_title().
					   '</h2>'. 
					   'Adres: '. get_post_meta(get_the_ID(), 'adres', true);
			
			$facebook=get_post_meta(get_the_ID(), 'facebook', true);
			if ($facebook) $output .= '<br>Facebook: ' . '<a href="'. $facebook . '">'. $facebook .'</a>';     
					   
			$najblizsa=find_closest(get_post_custom_values('daty'), get_post_custom_values('wyprzedzenie'));
			if ($najblizsa) $output .= '<br>Najbliższa akcja: ' . $najblizsa ;   				   
					   
					   
			$output .= '<br>' . get_the_excerpt().'<a class="read-more" href="'. get_permalink().'">Czytaj więcej </a></div><br>';
		}
	}
	endwhile;
    wp_reset_query();
    $output .= '</div>';
    return $output;
	
}
