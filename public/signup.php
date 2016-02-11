<?php
	//requiring must come first
	require_once("../includes/functions.php");

	require_once("../includes/session.php");
	include("../includes/header.php");

	signup();
?>
		<login>
		
		<?php
			echo message(); //only runs if "else" was executed, prints errors
			if(($_SESSION["errors"])!=null)
				echo form_errors($_SESSION["errors"]);
			//forms below
		?>

		<h1>Sign up</h1>
		<form action="signup.php" method="post">
		
			Name:<br /> 
			<i>Type your first and last name to let other students know who you are.</i><br />
			<input type="text" name="username" value="<?php if($_SESSION["username"]!=null)echo htmlspecialchars($_SESSION["username"]); ?>" /> <br />
			Password:<br /> <input type = "password" name="password" value="" /><br />
			Confirm Password: <br /><input type = "password" name="confirm_password" value="" /><br />
			Grade Level:<br /><select name="grade">
			<?php for($count = 6; $count <= 12; $count++)
			{ 
				echo "<option value=\"{$count}\">{$count}</option>";
			}
			?></select><br />			
			Email:<br /> 
			<i>Emailing may be a function in the future. Please enter your email.</i><br />
			<input type = "text" name = "email" value = "" /><br />
			Secret Code:<br /><!--Secret Code given at SMIC each year to ensure the person using the site is from SMIC-->
			<i>Type the secret code announced at school. Please ask your Dao Shi if you don't know this code.</i><br />
			<input type = "text" name = "code" value = "" /><br />
			<input type="checkbox" name="read" value="">I have read the <a href="terms.pdf" target="_blank">Terms & Conditions</a></input><br />
			<input class="big" type="submit" name="submit" value="Submit">
		</form></login>
	</body>
</html>
<?php
	ob_end_flush();
?>