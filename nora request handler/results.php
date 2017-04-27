	<?php
	
    //get query string
    $unfilteredWords = $_POST['query'];
    
    //array to hold words in the query
    $words = array();
    
    //Get information from the configuration file
    //filename: "config.ini"
    $settings = parse_ini_file('config.ini');
    $database = $settings['database'];
    $dbusername = $settings['dbusername'];
    $dbpassword = $settings['dbpassword'];
 
    //area which will later be transformed into a json response
    $response =  array();   
    
    //throw away non-english characters, and weird non text input
    //escape quotation marks and new line characters
    $filterString = "\\\n\ -+=-_)(*&^%$#@!~`\"'[]{}></?.,;:|";
    $token = strtok($unfilteredWords, $filterString);
    while($token != false){
    	array_push($words,$token);
    	$token = strtok($filterString);
    }
    
    //Connect to a database using the information from the configuration file.
    try{
    	$conn = new PDO($database,$dbusername,$dbpassword);
	    
	    
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
	    
	    //Create a statement to get results
	    $wordIdStatement = $conn->prepare($wordIdSql);
	    
	    //add words from array into statement
	    for($i = 1;$i <= sizeof($words);$i++){
	    	$wordIdStatement->bindValue($i,$words[$i-1],PDO::PARAM_STR);
	    }
	    
	    //execute statement
	    $wordIdStatement->execute();
	    $wordsIds = $wordIdStatement->fetchAll();
	    
	    //echo $wordsIds;
	    
	    //close statement;
	    $wordIdStatement->closeCursor();
	    //echo sizeof($wordsIds) . "\n";
	    
	    //If the query is empty or only contains stopwords
	    //return failure and echo back a json stating the query failed
	    if(sizeof($wordsIds) == 0){
	    	//	echo "no words found\n";
	    	$response['failure'] = true;
	    	echo json_encode($response);
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
	    
	    $resultsStatement = $conn->query($getUrlSql);	    
	    $results = $resultsStatement->fetchAll();
	    $resultsStatement->closeCursor();
	}
	
	//if at any point the connection to the database fails,
	//return false
	//respond with a failure.
	catch( PDOException $accessExp){
		$response['failure'] = true;
		echo json_encode($response);
		return false;
	}
	
    //if no URLs are returned by the query
    //return false
    //echo a json with a failure status of true
    if(sizeof($results) < 1){
    	$response['failure'] = true;
    	echo json_encode($response);
    	return false;
    }
    
    
    //if everything worked correctly,
    //echo json encoded URLs
    //echo back a failure status of false
    //return true
    $response['failure'] = false;
    $response['urls'] = $results; 
    echo json_encode($response);
    return true;
    
	?>