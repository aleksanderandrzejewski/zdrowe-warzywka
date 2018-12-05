<?php
	
//zarejestrowanie nowej roli	
function shop_new_role() {  
  
  //add the special customer role
  add_role(
    'sklep',
    "Sklep",
    array(
      'read'         => true,
      'delete_posts' => false
    )
  );
  
}
add_action('admin_init', 'shop_new_role');