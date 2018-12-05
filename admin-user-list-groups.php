<?php

function new_contact_methods( $contactmethods ) {
    $contactmethods['id_grupy'] = 'Grupa';
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'new_contact_methods', 10, 1 );


function new_modify_user_table( $column ) {
    $column['id_grupy'] = 'Grupa';
    return $column;
}
add_filter( 'manage_users_columns', 'new_modify_user_table' );

function new_modify_user_table_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
        case 'id_grupy' :
            return get_the_title(get_the_author_meta( 'id_grupy', $user_id ));
            break;
        default:
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );
