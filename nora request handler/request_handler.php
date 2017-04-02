<?php

function responseHandle(array $words){
	//Global settings file to open
	$settings = parse_ini_file('config.ini');
	
	$database = $settings['database'];
	$username = $settings['dbusername'];
	$password = $settings['dbpassword'];
	
	//create connection to MySQL database
	//attempt to create database connection.
	try{
		$conn = new PDO($database,$username,$password);
	}
	
	//on failure output error code 
	catch( PDOException $accessExp){
		return false;
	}
	
	//generating sql for a prepared statement
	//gets numeric ids for words used in next sql query
	$wordIdSql = 'SELECT * FROM word_master WHERE';
	for($i = 0;$i < sizeof($words);$i++){
		$wordIdSql = $wordIdSql . " word = ?";
		//ensure that an 'OR' isn't present for the last entry
		if($i < (sizeof($words) - 1)){
			$wordIdSql = $wordIdSql . " OR";
		}
	}
	
	//Create a statement and get results
	$wordIdStatement = $conn->prepare($wordIdSql);
	$wordIdStatement->execute($words);
	$wordsIds = $wordIdStatement->fetchAll();
	
	//close statement;
	$wordIdStatement->closeCursor();
	
	//if there exists a word 
	//which is not in any of the pages
	//exit
	if(sizeof($wordsIds) < sizeof($words)) 
		return false;
	
	//if there are no words, return null.
	if(sizeof($wordsIds) == 0){
		return false;
	}
	
	//get the table for the first word in the search query
	$firstTable = "wc_" . $wordsIds[0]['word_id'];
	
	//if there is only one word bering queried, 
	//use a simple one table SELECT query.
	if(sizeof($wordsIds) == 1){
		$getUrlSql = "SELECT master_id.url FROM " . 
		$firstTable . " INNER JOIN master_id" .
			" ON " . $firstTable . ".id = master_id.id";
		" ORDER BY " . $firstTable . ".count DESC";
	}
	
	
	//more than two words being queried requires a join statement
	//join is on all word tables required.
	else{
		$nextTable = "wc_" . $wordsIds[1]['word_id'];
		
		$getUrlSql = "SELECT master_id.url" .  
			" FROM " . $firstTable .
			" INNER JOIN " . $nextTable . 
			" ON ". $nextTable . ".id = " . $firstTable . ".id";
		
		//generating sql joining on each table
		for($i = 2;$i < sizeof($wordsIds);$i++){
			$nextTable = "wc_" . $wordsIds[$i]['word_id'];
			$getUrlSql .= 
			" INNER JOIN " . $nextTable . 
			" ON " . $firstTable  . ".id = " . $nextTable .  ".id";
		}
		
		//get URLS by using the master_id table
		$getUrlSql .= 
			" INNER JOIN master_id" .
			" ON " . $firstTable . ".id = master_id.id";
		
		//Add ORDER BY Statement
		$getUrlSql .= " ORDER BY ( " . $firstTable . ".count";
		
		//add all add statements using a for loop
		for($j = 1;$j < sizeof($wordsIds);$j++){
			$nextTable = "wc_" . $wordsIds[$j]['word_id'];
			$getUrlSql = $getUrlSql . " + " . $nextTable . ".count";
		}
		
		$getUrlSql = $getUrlSql . ") DESC";
		
	}
	
	//array to hold results
	$results = array();
	
	//obtain array of results
	foreach($conn->query($getUrlSql) as $row){
		array_push($results,$row['url']);
	}
	
	//if no URLs are returned by the query
	//return false
	if(sizeof($results) < 1){
		return false;
	}
	
	
	//if everything worked correctly,
	//echo json encoded URLs
	return json_encode($results);
	
}
?>