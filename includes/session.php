<?php
	ob_start();
	session_start();
	$_SESSION["username"]=null;
	function message() {
		if (isset($_SESSION["message"])) {
			//$output = "<div class=\"message\">";
			$output .= htmlentities($_SESSION["message"]);
			//$output .= "</div>";
			
			// clear message after use
			$_SESSION["message"] = null;
			
			return $output;
		}
	}
	ob_end_flush();
	
?>