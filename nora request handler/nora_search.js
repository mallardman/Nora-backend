//requires JQuery


function nora_search(){
	var query = $("#query_sr").val();
	destUrl = "https://www.versellie.com/nora/results.php";
	$.ajax(
		{url:destUrl,
		data:{'query':query},
		type:"POST",
		complete:function(data){
			var results = JSON.parse(data.responseText);
			var tableCont = "";
			if(results.failure){
				console.log("failed");
				//#nora-results must be a table
				$("#nora-results").html(tableCont);
			}

			else{
				var rawUrlArray = results.urls;
				var temp = "";
				for (var i = 0;i < rawUrlArray.length;i++){
					temp = rawUrlArray[i].url;
					console.log(temp);
					tableCont += '<tr><td><a href="' + 
						temp + '">' + temp + '</a></td></tr>';
				}
				
				//#nora-results must be a table
				$("#nora-results").html(tableCont);
			}
	
		}});
}