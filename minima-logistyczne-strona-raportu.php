<?php




//dodanie strony minima logistyczne do menu
add_action( 'admin_menu', 'wpse_91693_register' );

function wpse_91693_register()
{
    add_menu_page(
        'Minima logistyczne',     // page title
        'Minima logistyczne',     // menu title
        'manage_options',   // capability
        'minima-logistyczne',     // menu slug
        'wpse_91693_render' // callback function
    );
}
function wpse_91693_render(){
    global $title;

    print '<div class="wrap">';
    print "<h1>$title</h1>";

    $file = plugin_dir_path( __FILE__ ) . "included.php";

    if ( file_exists( $file ) )
        require $file;

    print '</div>';
}
