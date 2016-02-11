<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php");

	if (is_logged_in())
	{
		redirect_to("index.php");
	}
	$_SESSION["errors"]=array();
	$_SESSION["message"]="";
	login_process();
?>

		<login>
			<h1>Login</h1>

			<?php echo $_SESSION["message"]; ?>
			<br />

			<?php echo form_errors($_SESSION["errors"]); ?>
			<form action="login.php" method="post"><br />
				Name:
				<br /><input type="text" name="username" value="
				<?php
				if(isset($_SESSION["username"]))
				echo htmlspecialchars($_SESSION["username"]); 
				?>
				"/>
				<br />Password:<br /><input type="password" name="password" value="" /><br /><br />
				<input class="big" type="submit" name="submit" value="Login"/><br /><br />
				<a href="signup.php"> Do not have an account? Sign up!</a>
			</form>

		</login>

	</body>
</html>
<?php
	ob_end_flush();
?>