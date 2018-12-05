<?php
//dołaczenie generatora pdfów
require_once dirname( __FILE__ ) .'/tfpdf.php';

//stworzenie obiektu pdf	
$pdf = new tFPDF();

//ustawienie czcionki
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu','',10);


$nazwa_pliku='';
$licznik=0;



if(isset($_POST['submit'])){//to run PHP script on submit
	if(!empty($_POST['check_list'])){
		//wczytaj w pętli wszystkie wartości pobrane z metody post
		foreach($_POST['check_list'] as $label => $selected){
		 $pdf->AddPage();
		 //oczyszczenie kodu ze znaczników html
		 $nazwa_pliku= ' ' .$label;
		 $licznik+=1;
		 $string=str_replace("<br>", "\n", $selected);
		 $string=str_replace("<strong>", "" , $string);	  
		 $string=str_replace("</strong>", "" , $string);		
		$pdf->Multicell(0,5,$string); 	
		}
	}
}

if($licznik!=1)
	$nazwa_pliku='';

if(isset($_POST['data']))
		$nazwa_pliku= $_POST['data'] .  $nazwa_pliku;

	
	
	
//wygenerowanie pdf
if ($nazwa_pliku)
	$pdf->Output($nazwa_pliku . '.pdf' ,'I');
else
	$pdf->Output();	
?>

