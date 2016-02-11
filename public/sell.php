<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php");
	
	check_login();
?>
<login id="page" style="font-size:16px; font-family: Verdana;">
	<h1>Sell Book:</h1>
	<?php
		$book_id = get_id_from_url();
	?>
	<h3>
	<?php
		echo print_basic_book_info($book_id);
	?>
	</h3>
	<form action="sell.php?id=<?php echo $book_id; ?>" method="post">
	<i>Note: Decimals will be truncated.</i>
	<h3>Your price: <input type="text" name="price" value="" /></h3>
	<h3>Add a description:</h3>
	<textarea class="large" rows="10" name="description" value="Enter Description"></textarea>
	<br /><input class="big"type="submit" name="sell" value="Sell" />
	</form>
	<?php
		echo sell_book();	//echo because an error will be printed back if one occurs
	?>
</login>
</body>
</html>
<?php
	ob_end_flush();
?>