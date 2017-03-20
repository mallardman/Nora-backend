
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Nora test</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	</head>


<body>
<script>
function nora_search(query){
	var query = $("#query_sr").val();
	var convertedQuery = query.replace(/ /g, "_");
	var destUrl = 
		"http://versellie.com/nora/results.php?search=" +
		convertedQuery;
	$.ajax({
		url:destUrl,
		data:convertedQuery,
		dataType:"json",
		complete:function(data){
			var tableCont = "";
			if('failure' in data.responseJSON){
				$("#nora-results").html(tableCont);
			}

			else{
				var temp;
				for (url in data.responseJSON){
					temp = data.responseJSON[url];
					tableCont += '<tr><td><a href="' + 
						temp + '">' + temp + '</a></td></tr>';
				}
				
				$("#nora-results").html(tableCont);
			}
	
		}});
}
		

</script>

<div id="nora">
<span id="nora-bar-cont">
	<input type="text" name="query_sr" id="query_sr">
	<button onclick='nora_search()'>
	
	
	
	
	</button></span>
</div>
<table id="nora-results">
</table>
</div>





</body>

</html>