
    <?php
    require 'request_handler.php';
    error_reporting(E_ALL);
    
    //throw away non-english characters, and weird non text input
    //escape quotation marks and new line characters
    $searchQuery = filter_input(INPUT_GET, 'search',
    FILTER_SANITIZE_SPECIAL_CHARS);
    //
   
    //delimiting characters used to seperate words
    $delimiter = " ,/;:!~!#$%&'*+-=?^_`{|}~@.[]./n/";
    
    //array to store words
    $words = array();
    
    //seperate string passed via GET request
    //into words
    $token = strtok($searchQuery,$delimiter);
    while($token !== false){
    	array_push($words, $token);
    	$token = strtok($delimiter);
    }
    
    //pass to handler function
    //if the handler function fails
    //echo no results found
    if(!responseHandle($words)){
    	$response = array();
    	$response['failure'] = 'yes';
    	echo json_encode($response);
    }
    
	?>