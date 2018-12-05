<?php
  $file = plugin_dir_url( __FILE__ ) . "pdfgenerator/generatepdf.php";
 ?>

<form action="https://www.zdrowewarzywka.pl/wp-admin/admin.php?page=raportpdf" method="POST" name="theForm" id="theForm">
	
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

function wygeneruj_paskii($requested_date , $requested_date2){
	$file = plugin_dir_url( __FILE__ ) . "pdfgenerator/generatepdf.php";	
	if(!$requested_date or !$requested_date2)
	{
		return "Błąd. Nie wybrano zakresu dat!";	
	}	
	else
	{

		$daty = array_unique(array_merge(daty_akcji_zaplanowanych(), daty_z_zamowieniami()));
		sort($daty);

		

		
		foreach($daty as $date){
			if($requested_date<=$date and $date<=$requested_date2)
			{	
				echo '<h1>'. $date . '</h1>';
					
				//wygenerowanie pasków minimów logistycznych	
				$klucze_akcji = array_unique( array_merge(klucze_akcji_z_zamowieniami(), klucze_akcji_zaplanowanych()));
				foreach($klucze_akcji as $key){
					$odczytane = $key; 
					$data=data_odb_z_klucza($odczytane);	
					$id_grupy_odb=id_grupy_odb_z_klucza($odczytane);
					
					if($date==$data)
						echo progress_bar_z_klucza($key, 1);
				}

				
				$packages = array(packages_report($date));	
				$orders_report=orders_report_array($date);	

				if($orders_report){
					?><form target=”_blank” action="<?php echo $file ?>" method="post">
					<input type="checkbox" name="check_list[zestawy]" value='<?php echo $packages[0] ?>' checked><label>Zestawy do przygotowania</label><br/><?php 
					
					foreach ($orders_report as $id => $content)
					{
					
						?><input type="checkbox" name="check_list[<?php echo get_the_title($id) ?>]" value="<?php echo $content ?>" checked><label><?php echo get_the_title($id) ?></label><br/><?php 
					
					}
					
					?>
					<input type="hidden" name="data" id="data" value="<?php echo $date ?>" />
					<input type="submit" name="submit" value="Generuj PDF"/>
					</form><?php 
				}
				echo '<br>';
				
			}
		}
		
			
	}
	
}



if(isset($_POST['ADMIN_FILTER_FIELD_DATE']))	
	$requested_date = $_POST['ADMIN_FILTER_FIELD_DATE'];
else
	$requested_date = date('Y-m-d');

if(isset($_POST['ADMIN_FILTER_FIELD_DATE2']))	
	$requested_date2 = $_POST['ADMIN_FILTER_FIELD_DATE2'];
else
	$requested_date2 = date('Y-m-d', strtotime(date('Y-m-d') . ' +7 days'));

wygeneruj_paskii($requested_date, $requested_date2);
