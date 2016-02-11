<?php
	ob_start();
	require_once("../includes/functions.php");
		require_once("../includes/session.php");


		db_connection(); /*did books.php worth of db connection here*/
		reset_cookie();//when the user clicks into something, the cookie is reset, and the user has one hour before auto-logout
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
	<head>
		<title>Sharks Book Trading</title>		
	<link href="../public/stylesheets/main_style.css" media="all" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="https://ajax.microsoft.com/ajax/jQuery/jquery-1.4.2.min.js"></script>
	<script src="../public/stylesheets/script.js"></script>
	</head>
	<body>
	
	<nav id="header">
		<h1>Sharks Book Trading</h1>
		
		<span>
				<ul>
				<li id="books"> 				
					<a href="index.php" class="no_underline" >
					Books
					</a>
					
					<ul>
						<?php 
						/*db_connection(); /*access mysql*/
						/*category_dropdownlist() = dropdown menu*/
						 echo category_dropdownlist(); //produces the dropdown list under "Books" when the mouse hovers over it
						?>
					</ul>
				</li>
				
				<li><a href="about.php" >
					About
				</a></li> 
					<?php
						if(is_logged_in()) {
						//echo $_SESSION["login"];
						//available buttons if the user is logged in
						echo "<a href=\"sales.php\"><li>Sales</li></a>";
						echo "<a href=\"messages.php\"><li>Messages</li></a>";
						echo "<a href=\"logout.php\"><li>Logout</li></a>";
						}
						else {
						echo "<a href=\"login.php\"><li>Login</li></a>"; 
					/* quick login bar: future development
					$log ="Name: <input type=\"text\" name=\"username\" value=\"";
					if(isset($_SESSION["username"]))
					$log.=htmlspecialchars($_SESSION["username"]);

					$log.="\"/>";

					$log.="<br />Password:<input type=\"password\" name=\"password\" value=\"\" />";
					$log.="<input type=\"submit\" name=\"submit\" value=\"Quick login\"/>";
					echo $log;
			*/
						}
					?>
		</ul>
		
	</span>
</nav>