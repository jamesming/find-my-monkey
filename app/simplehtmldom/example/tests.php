<?php
// example of how to use basic selector to retrieve HTML contents
include('../simple_html_dom.php');
 
// get DOM from URL or file
$html = file_get_html('http://livingsocial.com/deals?preferred_city=66');


		foreach($html->find('title') as $e)
		    echo $e->innertext . '<br>';
		    

		echo $html->find('div.deal-title h1', 0)->innertext . '<br>';  
		echo $html->find('div.deal-title p', 0)->innertext . '<br>';  
		$price =  str_replace("<span class=\"dollar_sign\">$</span>", "", $html->find('div.deal-price', 0)->innertext);  
		echo $price;
		
  

//				// find all link
//				foreach($html->find('a') as $e) 
//				    echo $e->href . '<br>';
//				
//				// find all image
//				foreach($html->find('img') as $e)
//				    echo $e->src . '<br>';
//				
//				// find all image with full tag
//				foreach($html->find('img') as $e)
//				    echo $e->outertext . '<br>';

//// find all div tags with id=gbar
//foreach($html->find('a.addthis_button_facebook') as $e)
//    echo $e->href . '<br>';

//		// find all span tags with class=gb1
//		foreach($html->find('span.gb1') as $e)
//		    echo $e->outertext . '<br>';
//		
//		// find all td tags with attribite align=center
//		foreach($html->find('td[align=center]') as $e)
//		    echo $e->innertext . '<br>';
//		    
//		// extract text from table
//		echo $html->find('td[align="center"]', 1)->plaintext.'<br><hr>';
//		
//		// extract text from HTML
//		echo $html->plaintext;
?>