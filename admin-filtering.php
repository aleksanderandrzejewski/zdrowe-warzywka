<?php


//FILTROWANIE PO GRUPIE i DACIE





add_action( 'restrict_manage_posts', 'zw_admin_posts_filter_restrict_manage_posts' );
/*
 * dropdown
 */
function zw_admin_posts_filter_restrict_manage_posts(){
    $type = 'shop_order';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }


    if ('shop_order' == $type){
	$args=array('post_type' => 'akcja','posts_per_page'=>100,'orderby'=>'title', 'order'=>'ASC');
$my_query14344 = new WP_Query($args);

	$my_array=array();


if ($my_query14344->have_posts()): 
    while ($my_query14344->have_posts()) : $my_query14344->the_post();    
        $my_array[get_the_title(get_the_ID())]= get_the_ID();
    endwhile;
endif;		
		
	

        $values = $my_array;
		
		
        ?>
        <select name="ADMIN_FILTER_FIELD_VALUE">
        <option value=""><?php _e('Filtruj po grupie odbioru ', 'wose45436'); ?></option>
        <?php
            $current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
            foreach ($values as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
        ?>
        </select>
		<?php 
		$current_d = isset($_GET['ADMIN_FILTER_FIELD_DATE'])? $_GET['ADMIN_FILTER_FIELD_DATE']:'';
		_e('Filtruj po dacie odbioru:', 'baapf'); ?> <input type="DATE" name="ADMIN_FILTER_FIELD_DATE" value="<?php echo $current_d; ?>" />

        <?php
    }
}


add_filter( 'parse_query', 'zw_posts_filter' );
/**
 * if submitted filter by post meta
 * 
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 * 
 * @return Void
 */
function zw_posts_filter( $query ){
    global $pagenow;
    $type = 'shop_order';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'shop_order' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
        $query->query_vars['meta_key'] = '_id_grupy';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
    }
	
   if ( 'shop_order' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_DATE']) && $_GET['ADMIN_FILTER_FIELD_DATE'] != '') {
        $query->query_vars['meta_key'] = '_data_odbioru';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_DATE'];
    }	
	
}





function zw_column_header( $columns ) {

    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {

        $new_columns[ $column_name ] = $column_info;

        if ( 'order_total' === $column_name ) {
            $new_columns['order_profit'] = __( 'Data odbioru i grupa', 'my-textdomain' );
        }
    }

    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'zw_column_header', 20 );



if ( ! function_exists( 'sv_helper_get_order_meta' ) ) :


	//	get meta for an order
    function sv_helper_get_order_meta( $order, $key = '', $single = true, $context = 'edit' ) {


        if ( defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, '3.0', '>=' ) ) {

            $value = $order->get_meta( $key, $single, $context );

        } else {

            $order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
            $value    = get_post_meta( $order_id, $key, $single );
        }

        return $value;
    }

endif;


	//new column content to 'Orders' page immediately after 'Total' column
function sv_wc_cogs_add_order_profit_column_content( $column ) {
    global $post;

    if ( 'order_profit' === $column ) {

        $order    = wc_get_order( $post->ID );
        $data_i_gripa   = '';
        $data_i_gripa    = sv_helper_get_order_meta( $order, '_data_i_grupa' );

        echo  $data_i_gripa;
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'sv_wc_cogs_add_order_profit_column_content' );









