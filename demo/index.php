<?php
	include("summariser.php"); //Link to the summarising bit
	
	////////////////////////////////////////////////////////////
	//We use the networkobject in that file to download a page//
	////////////////////////////////////////////////////////////
	$nm = new networkobject();
	$nm->url = "http://www.lipsum.com/feed/html";
	$nm->run();
	
	//If that does not load, then you may need to write it with cURL.
	
	////////////////////////////////////////////////////////////
	//The splice bit does not always work, so we will do that //
	//here                                                    //
	////////////////////////////////////////////////////////////
	$start = '<div id="lipsum">'; //Starting tag
	$end   = '<div id="generated">';
	$begpo = strpos($nm->response, $start);
	$endpo = strpos($nm->response, $end, $begpo);
	$conte = substr($nm->response, $begpo, $endpo-$begpo);
	
	////////////////////////////////////////////////////////////
	//And now onto the summarisation                          //
	////////////////////////////////////////////////////////////
	$sum = new summariser();
	$sum->string = $conte;
	$sum->s_split(); //We provide something to split up sentences. 
					 //If your string contains brackets, remove_brackets
					 //Will get rid of them for you.
	$sum->summarise(5); //Summarises it down to 5 points;
	
	echo $sum->response;
?>