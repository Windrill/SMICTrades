<?php
ob_start();
require_once("../includes/functions.php");

/* 	$_SESSION["password"] = null;
	$_SESSION["username"] = null;
	$_SESSION["login"] = 0;
	$_SESSION["errors"]=null;
	$_SESSSION["message"]=null; */
	
	 session_start();
	 set_cookie();
	 $_SESSION = array();
	 if (isset($_COOKIE[session_name()])) { /*if (isset($_COOKIE["login"])) ??*/
	   setcookie(session_name(), '', time()-42000, '/');
	 }

	session_destroy(); 
	redirect_to("login.php");
	
	ob_end_flush();
	
	?>