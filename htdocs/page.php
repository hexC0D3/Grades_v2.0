<?php
	if(isset($_GET['permalink'])&&!empty($_GET['permalink'])){
		$permalink=$_GET['permalink'];
		
		require_once 'load.php';
		
		if($permalink=='get-started'){
			require_once 'static/get-started.php';
		}
		
	}
?>