<?php
// Plik odpowiedzialny za pole wyboru grupy przy rejestracji i pole wyboru gruoy w widoku edycji użytkownika w panelu admina


// --- POLA PRZY REJESTRACJI  ---


function zw_get_account_fields($wszystkie=false) {
	
	$args=array('post_type' => 'akcja','posts_per_page'=>100,'orderby'=>'title', 'order'=>'ASC');
$my_query = new WP_Query($args);

	$my_array['']='Wybierz'; //placeholder

if($wszystkie)	
	{	
		if ($my_query->have_posts()): 
			while ($my_query->have_posts()) : $my_query->the_post();   
					$my_array[get_the_ID()]= get_the_title(get_the_ID());
			endwhile;
		endif;
	}
else	
	{	
		if ($my_query->have_posts()): 
			while ($my_query->have_posts()) : $my_query->the_post();   
				if( !get_post_meta( get_the_ID(), 'dostepny_dla_wszystkich_kont', true ) and !get_post_meta( get_the_ID(), 'prywatny', true ) )
					$my_array[get_the_ID()]= get_the_title(get_the_ID());
			endwhile;
		endif;
	}	
	
    return apply_filters( 'zw_account_fields', array(
        'id_grupy'     => array(
            'type'    => 'select',
            'label'   => __( 'Wybierz grupę:', 'zw' ),
			'required'=> true,	
            'options' => $my_array
        ),
    ) );
}


function zw_print_user_frontend_fields_in_registration() {
    $fields            = zw_get_account_fields();
    $is_user_logged_in = is_user_logged_in();
 
    foreach ( $fields as $key => $field_args ) {

        if ( ! $is_user_logged_in && ! empty( $field_args['hide_in_registration'] ) ) {
            continue;
        }
 
        woocommerce_form_field( $key, $field_args );
    }
}

function zw_print_user_frontend_fields_in_account() {
    $fields            = zw_get_account_fields();
    $is_user_logged_in = is_user_logged_in();
 
    foreach ( $fields as $key => $field_args ) {
        if ( $is_user_logged_in && ! empty( $field_args['hide_in_account'] ) ) {
            continue;
        }
 
        woocommerce_form_field( $key, $field_args );
    }
}
 
/*
 * Add fields to admin area
 */
function zw_print_user_admin_fields() {
    $fields = zw_get_account_fields(true);
    ?>
    <h2><?php _e( 'Dodatkowe informacje', 'zw' ); ?></h2>
    <table class="form-table" id="zw-additional-information">
        <tbody>
        <?php foreach ( $fields as $key => $field_args ) { ?>
            <?php
            if ( ! empty( $field_args['hide_in_admin'] ) ) {
                continue;
            }
 
            $user_id = zw_get_edit_user_id();
            $value   = zw_get_userdata( $user_id, $key );
            ?>
            <tr>
                <th>
                    <label for="<?php echo $key; ?>"><?php echo $field_args['label']; ?></label>
                </th>
                <td>
                    <?php $field_args['label'] = false; ?>
                    <?php woocommerce_form_field( $key, $field_args, $value[0] ); ?>
                </td>
            </tr>
        <?php } ?>


           <tr> 
                <th>
                    <label>Aktualna grupa</label>
                </th>
                <td>
                    <?php  $grupa=zw_get_userdata(zw_get_edit_user_id(), 'id_grupy' ); $drukuj = "ID:" . $grupa[0] . ', ' . get_the_title($grupa[0]); if($grupa[0] ) echo $drukuj; else echo 'Użytkownik nie jest przypisany do żadnej grupy.'; ?>
                </td>
            </tr>        
		
		</tbody>
    </table>
    <?php
}
 
/*
 * Get currently editing user ID (frontend account/edit profile/edit other user).
 */
function zw_get_edit_user_id() {
    return isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : get_current_user_id();
}
 
/**
 * Show fields at checkout.
 *
 * @see https://zwwp.com/blog/the-ultimate-guide-to-adding-custom-woocommerce-user-account-fields/
 */
function zw_checkout_fields( $checkout_fields ) {
    $fields = zw_get_account_fields();
 
    foreach ( $fields as $key => $field_args ) {
        if ( ! empty( $field_args['hide_in_checkout'] ) ) {
            continue;
        }
 
        $checkout_fields['account'][ $key ] = $field_args;
    }
 
    return $checkout_fields;
}

//Showing fields in different places using hooks:

//add_action( 'woocommerce_edit_account_form', 'zw_print_user_frontend_fields_in_account', 10 ); // my account
//add_filter( 'woocommerce_checkout_fields', 'zw_checkout_fields', 10, 1 );
add_action( 'woocommerce_register_form', 'zw_print_user_frontend_fields_in_registration', 10 ); // register form
add_action( 'show_user_profile', 'zw_print_user_admin_fields', 30 ); // admin: edit profile
add_action( 'edit_user_profile', 'zw_print_user_admin_fields', 30 ); // admin: edit other users



/**
 * Save registration fields.
 */
function zw_save_account_fields( $customer_id ) {
	$fields = zw_get_account_fields();
	$sanitized_data = array();

	foreach ( $fields as $key => $field_args ) {
		if ( ! zw_is_field_visible( $field_args ) ) {
			continue;
		}

		$sanitize = isset( $field_args['sanitize'] ) ? $field_args['sanitize'] : 'wc_clean';
		$value    = isset( $_POST[ $key ] ) ? call_user_func( $sanitize, $_POST[ $key ] ) : '';

		if ( zw_is_userdata( $key ) ) {
			$sanitized_data[ $key ] = $value;
			continue;
		}		


		update_user_meta( $customer_id, $key, $value );
	}

	if ( ! empty( $sanitized_data ) ) {
		$sanitized_data['ID'] = $customer_id;
		wp_update_user( $sanitized_data );
	}
}

add_action( 'woocommerce_created_customer', 'zw_save_account_fields' ); // register/checkout
add_action( 'personal_options_update', 'zw_save_account_fields' ); // edit own account admin
add_action( 'edit_user_profile_update', 'zw_save_account_fields' ); // edit other account admin
//add_action( 'woocommerce_save_account_details', 'zw_save_account_fields' ); // edit WC account




/*
 * Is field visible.
 */
function zw_is_field_visible( $field_args ) {
    $visible = true;
    $action = filter_input( INPUT_POST, 'action' );
 
    if ( is_admin() && ! empty( $field_args['hide_in_admin'] ) ) {
        $visible = false;
    } elseif ( ( is_account_page() || $action === 'save_account_details' ) && is_user_logged_in() && ! empty( $field_args['hide_in_account'] ) ) {
        $visible = false;
    } elseif ( ( is_account_page() || $action === 'save_account_details' ) && ! is_user_logged_in() && ! empty( $field_args['hide_in_registration'] ) ) {
        $visible = false;
    } elseif ( is_checkout() && ! empty( $field_args['hide_in_checkout'] ) ) {
        $visible = false;
    }
 
    return $visible;
}


/*
 * Is this field core user data.
 */
function zw_is_userdata( $key ) {
	$userdata = array(
		'user_pass',
		'user_login',
		'user_nicename',
		'user_url',
		'user_email',
		'display_name',
		'nickname',
		'first_name',
		'last_name',
		'description',
		'rich_editing',
		'user_registered',
		'role',
		'jabber',
		'aim',
		'yim',
		'show_admin_bar_front',
	);

	return in_array( $key, $userdata );
}



/*
 * Validate fields on frontend.
*/
function zw_validate_user_frontend_fields( $errors ) {
	$fields = zw_get_account_fields();

	foreach ( $fields as $key => $field_args ) {
		if ( empty( $field_args['required'] ) ) {
			continue;
		}

		if ( ! isset( $_POST['register'] ) && ! empty( $field_args['hide_in_account'] ) ) {
			continue;
		}

		if ( isset( $_POST['register'] ) && ! empty( $field_args['hide_in_registration'] ) ) {
			continue;
		}

		if ( empty( $_POST[ $key ] ) ) {
			$message = sprintf( __( '%s to wymagane pole.', 'zw' ), '<strong>' . $field_args['label'] . '</strong>' );
			$errors->add( $key, $message );
		}
	}

	return $errors;
}

add_filter( 'woocommerce_registration_errors', 'zw_validate_user_frontend_fields', 10 );
//add_filter( 'woocommerce_save_account_details_errors', 'zw_validate_user_frontend_fields', 10 );



/*
 * Add post values to account fields if set.
 */
function zw_add_post_data_to_account_fields( $fields ) {
	if ( empty( $_POST ) ) {
		return $fields;
	}

	foreach ( $fields as $key => $field_args ) {
		if ( empty( $_POST[ $key ] ) ) {
			$fields[ $key ]['value'] = '';
			continue;
		}

		$fields[ $key ]['value'] = $_POST[ $key ];
	}

	return $fields;
}

add_filter( 'zw_account_fields', 'zw_add_post_data_to_account_fields', 10, 1 );





/*
  Wyciaganie danych
 */
function zw_get_userdata( $user_id, $key ) {
	if ( ! zw_is_userdata( $key ) ) {
		return get_user_meta( $user_id, $key );
	}

	$userdata = get_userdata( $user_id );

	if ( ! $userdata || ! isset( $userdata->{$key} ) ) {
		return '';
	}

	return $userdata->{$key};
}


// DODATKOWE POLA UZYTKOWNIKA KONIEC




