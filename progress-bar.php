<?php
function koo_progress_bar($aktualne, $wymagane, $zamowienia_do=''){
	echo '<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">';
	if($aktualne>=$wymagane){
		$procent = 100;
		
		if(is_admin())
		{
			if(date("Y-m-d") > $zamowienia_do)
				$kolor = 'green';
			else
				$kolor = 'blue';	
		}
		else
		{
			$kolor = 'green';
			$aktualne=$wymagane;
		}
	}
	else 
	{
		$procent = $aktualne/$wymagane*100;
		
		if(date("Y-m-d") > $zamowienia_do)
			$kolor = 'red';
		else
			$kolor = 'blue';
	}	
	
	
	
return
'
<div class="w3-grey">
  <div class="w3-container w3-'.$kolor.' w3-center" style="width:'.$procent.'%">'.$aktualne.'zł/'.$wymagane.'zł</div>
</div>';
}



function progress_bar_z_klucza($key,$ret=false){	
	$odczytane = $key; 

	$data=data_odb_z_klucza($odczytane);	
	$id_grupy_odb=id_grupy_odb_z_klucza($odczytane);
	$nazwa_grupy=get_the_title($id_grupy_odb);
	$suma=get_orders_total_na_akcje(serialize($key), '_klucz_akcji');
	$wymagane= get_post_meta($id_grupy_odb, 'minimum_logistyczne', true);
	$wyprzedzenie=get_post_meta($id_grupy_odb, 'wyprzedzenie', true);
	$zamowienia_do=zamowienia_do($data, $wyprzedzenie);
	
	if($ret)
	{
		return '<br>' . $nazwa_grupy . '<br>Data odbioru: ' . $data . ', zamówienia do: ' . $zamowienia_do . koo_progress_bar($suma,$wymagane,$zamowienia_do);
	}		
	else
	{
		echo '<br>' . $nazwa_grupy . '<br>Data odbioru: ' . $data . ', zamówienia do: ' . $zamowienia_do;
		echo koo_progress_bar($suma,$wymagane,$zamowienia_do);
	}
	
}


function progress_bars_z_kluczy_frontend($klucze_akcji, $zadane_id_grupy_odb){	

	foreach($klucze_akcji as $odczytane){
		
		$data=data_odb_z_klucza($odczytane);	
		$id_grupy_odb=id_grupy_odb_z_klucza($odczytane);
		
		if($zadane_id_grupy_odb==$id_grupy_odb and $data>=date("Y-m-d")){
		
			$nazwa_grupy=get_the_title($id_grupy_odb);
			$suma=get_orders_total_na_akcje(serialize($odczytane), '_klucz_akcji');
			$wymagane= get_post_meta($id_grupy_odb, 'minimum_logistyczne', true);
			$wyprzedzenie=get_post_meta($id_grupy_odb, 'wyprzedzenie', true);
			$zamowienia_do=zamowienia_do($data, $wyprzedzenie);
			
//			echo '<br>' . $nazwa_grupy;
			echo '<br>Data odbioru: ' . $data . ', zamówienia do: ' . $zamowienia_do;
			echo koo_progress_bar($suma,$wymagane,$zamowienia_do);
		}
	}
	return 1;
}

add_shortcode('minimum-log-sidebar', 'progress_bar_na_najblizsza_akcje');



function progress_bar_na_najblizsza_akcje(){	
		
		$id_grupy_odb = get_user_meta(get_current_user_id(), 'id_grupy', true);
		$data = find_closest(get_post_meta($id_grupy_odb,'daty',true ), get_post_meta($id_grupy_odb,'wyprzedzenie',true ) );
		$odczytane=serialize(array('_data_odbioru' => $data, '_id_grupy_odb' => $id_grupy_odb));
		
		if($data){
		
			$nazwa_grupy=get_the_title($id_grupy_odb);
			$suma=get_orders_total_na_akcje(serialize($odczytane), '_klucz_akcji');
			$wymagane= get_post_meta($id_grupy_odb, 'minimum_logistyczne', true);
			$wyprzedzenie=get_post_meta($id_grupy_odb, 'wyprzedzenie', true);
			$zamowienia_do=zamowienia_do($data, $wyprzedzenie);
			
			echo '<br>' . $nazwa_grupy;
			echo '<br>Data odbioru: ' . $data . ', zamówienia do: ' . $zamowienia_do;
			echo koo_progress_bar($suma,$wymagane,$zamowienia_do);
	
		}
}
