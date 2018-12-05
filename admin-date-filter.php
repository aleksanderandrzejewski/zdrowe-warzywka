<?php

class zwDateRange{
 
	function __construct(){
 


		add_action( 'admin_enqueue_scripts', array( $this, 'jqueryui' ) );
 

		add_action( 'restrict_manage_posts', array( $this, 'form' ) );
 

		add_action( 'pre_get_posts', array( $this, 'filterquery' ) );
 
	}
 
	/*
	 * Add jQuery UI CSS and the datepicker script
	 * Everything else should be already included in /wp-admin/ like jquery, jquery-ui-core etc
	 * If you use WooCommerce, you can skip this function completely
	 */
	function jqueryui(){
		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
 
	/*
	 * Two input fields with CSS/JS
	 * If you would like to move CSS and JavaScript to the external file - welcome.
	 */
	function form(){
 
		$from = ( isset( $_GET['zwDateFrom'] ) && $_GET['zwDateFrom'] ) ? $_GET['zwDateFrom'] : '';
		$to = ( isset( $_GET['zwDateTo'] ) && $_GET['zwDateTo'] ) ? $_GET['zwDateTo'] : '';
 
		echo '<style>
		input[name="zwDateFrom"], input[name="zwDateTo"]{
			line-height: 28px;
			height: 28px;
			margin: 0;
			width:125px;
		}
		</style>
 
		<input type="text" name="zwDateFrom" placeholder="Date From" value="' . $from . '" />
		<input type="text" name="zwDateTo" placeholder="Date To" value="' . $to . '" />
 
		<script>
		jQuery( function($) {
			var from = $(\'input[name="zwDateFrom"]\'),
			    to = $(\'input[name="zwDateTo"]\');
 
			$( \'input[name="zwDateFrom"], input[name="zwDateTo"]\' ).datepicker();
			// by default, the dates look like this "April 3, 2017" but you can use any strtotime()-acceptable date format
    			// to make it 2017-04-03, add this - datepicker({dateFormat : "yy-mm-dd"});
 
 
    			// the rest part of the script prevents from choosing incorrect date interval
    			from.on( \'change\', function() {
				to.datepicker( \'option\', \'minDate\', from.val() );
			});
 
			to.on( \'change\', function() {
				from.datepicker( \'option\', \'maxDate\', to.val() );
			});
 
		});
		</script>';
 
	}
 
	/*
	 * The main function that actually filters the posts
	 */
	function filterquery( $admin_query ){
		global $pagenow;
 
		if (
			is_admin()
			&& $admin_query->is_main_query()
			// by default filter will be added to all post types, you can operate with $_GET['post_type'] to restrict it for some types
			&& in_array( $pagenow, array( 'edit.php', 'upload.php' ) )
			&& ( ! empty( $_GET['zwDateFrom'] ) || ! empty( $_GET['zwDateTo'] ) )
		) {
 
			$admin_query->set(
				'date_query', // I love date_query appeared in WordPress 3.7!
				array(
					'after' => $_GET['zwDateFrom'], // any strtotime()-acceptable format!
					'before' => $_GET['zwDateTo'],
					'inclusive' => true, // include the selected days as well
					'column'    => 'post_date' // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
				)
			);
 
		}
 
		return $admin_query;
 
	}
 
}
new zwDateRange();