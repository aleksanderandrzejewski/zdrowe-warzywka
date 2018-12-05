<form action="https://www.zdrowewarzywka.pl/wp-admin/admin.php?page=minima-logistyczne" method="POST" name="theForm" id="theForm">
<?php 	//	<form action="https://www.zdrowewarzywka.pl/testing/" method="POST" name="theForm" id="theForm">
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
        <select form="theForm" name="ADMIN_FILTER_FIELD_VALUE">
        <option value=""><?php _e('Pokaż wszystkie grupy ', 'wose45436'); ?></option>
        <?php
            $current_v = isset($_POST['ADMIN_FILTER_FIELD_VALUE'])? $_POST['ADMIN_FILTER_FIELD_VALUE']:'';
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

Od:
<input type="date" form="theForm" name="ADMIN_FILTER_FIELD_DATE" 
<?php 
if(!isset($_POST['ADMIN_FILTER_FIELD_DATE']))
	echo 'value="' . date('Y-m-d') . '"'; 
else
	echo 'value="' . $_POST['ADMIN_FILTER_FIELD_DATE'] . '"';

?>" />
Do:
<input type="date" form="theForm" name="ADMIN_FILTER_FIELD_DATE2" 

<?php 

if(!isset($_POST['ADMIN_FILTER_FIELD_DATE2']))
	echo 'value="' . date('Y-m-d', strtotime(date('Y-m-d') . ' +7 days')) . '"';
else
	echo 'value="' . $_POST['ADMIN_FILTER_FIELD_DATE2'] . '"';
?> />

<input type="submit" value="Pokaż" />
</form>
  
  
<?php 


function wygeneruj_paskii($requested_group, $requested_date , $requested_date2){	
	if(!$requested_date or !$requested_date2)
	{
		echo "Błąd. Nie wybrano zakresu dat!";	
	}	
	else
	{
		$klucze_akcji = array_unique( array_merge(klucze_akcji_z_zamowieniami(), klucze_akcji_zaplanowanych()));
		 $kodhtml='';
		foreach($klucze_akcji as $key){
			$odczytane = $key; 
			$data=data_odb_z_klucza($odczytane);	
			$id_grupy_odb=id_grupy_odb_z_klucza($odczytane);
			if(($requested_group==$id_grupy_odb or !$requested_group) and $requested_date<=$data and $data<=$requested_date2)
				$kodhtml .= progress_bar_z_klucza($key, 1);
		}
		return $kodhtml;
		
		
	}
	
}


if(isset($_POST['ADMIN_FILTER_FIELD_DATE']))	
	$requested_date = $_POST['ADMIN_FILTER_FIELD_DATE'];
else
	$requested_date = date('Y-m-d');

if(isset($_POST['ADMIN_FILTER_FIELD_DATE2']))	
	$requested_date2 = $_POST['ADMIN_FILTER_FIELD_DATE2'];
else
	$requested_date2 = date('Y-m-d', strtotime(date('Y-m-d') . ' +3 days'));

$requested_group = '';
if(isset($_POST['ADMIN_FILTER_FIELD_VALUE']))	
	$requested_group = $_POST['ADMIN_FILTER_FIELD_VALUE'];


echo wygeneruj_paskii($requested_group, $requested_date, $requested_date2);