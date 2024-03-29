<?php

	ini_set("display_errors",1);
	error_reporting(E_ALL);

	$url = 'https://pro-api.coinmarketcap.com/v2/tools/price-conversion';
	//$url2 = 'https://api.coinmarketcap.com/data-api/v3/cryptocurrency/detail/chart?id=1&range=1D&convertId=2806';
	$parameters = [
	  'amount' => '1',
	  'symbol' => 'ETH',
	  'convert' => 'USDT'
	];

	$headers = [
	  'Accepts: application/json',
	  'X-CMC_PRO_API_KEY: b92d36a6-750a-4ffd-87c0-3027dfc17d93'
	];
	$qs = http_build_query($parameters); // query string encode the parameters
	$request = "{$url}?{$qs}"; // create the request URL

	$curl = curl_init(); // Get cURL resource
	// Set cURL options
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $request,            // set the request URL
	  CURLOPT_HTTPHEADER => $headers,     // set the headers 
	  CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
	));
	
	$response = curl_exec($curl); // Send the request, save the response
	
	$result = json_decode($response);
	$array = get_object_vars($result);
	
	//echo "<pre>";
	//print_r($array['data']); // print json decoded response		
	//echo "</pre>";
	
	$arr0 = get_object_vars($array['data'][0]);
	
	//echo "<pre>";
	//print_r($arr0); // print json decoded response		
	//echo "</pre>";
	
	$arrQuote = get_object_vars($arr0['quote']);
	
	//echo "<pre>";
	//print_r($arrQuote); // print json decoded response		
	//echo "</pre>";
	
	$arrETH = get_object_vars($arrQuote['USDT']);
	
	//echo "<pre>";
	//print_r($arrRUB['price']); // print json decoded response		
	//echo "</pre>";

	$eth = $arrETH['price'];

	curl_close($curl); // Close request
?>