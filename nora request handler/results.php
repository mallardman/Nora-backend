
    <?php
    require 'request_handler.php';
    error_reporting(E_ALL);
    
    //throw away non-english characters, and weird non text input
    //escape quotation marks and new line characters
    $unfilteredWords = $_POST['queryArray'];
    $words = array();
    foreach($unfilteredWords as $word){
    	array_push($words,filter_var($word,FILTER_SANITIZE_SPECIAL_CHARS));
    }
    
    //pass to handler function
    //if the handler function fails
    //echo no results found
    $results = responseHandle($words);
    if($results == false){
    	$response = array();
    	$response['failure'] = 'true';
    	echo json_encode($response);
    }
    
    else{
    	echo $results;
    }
    
	?>