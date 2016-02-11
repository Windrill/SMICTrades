<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php");
	
	check_login(); //makes sure people cannot skip to this page without logging in
	delete_offer(); //if the user clicks on "delete offer," this function will run
	
	$user_id = $_SESSION["id"];
?>
<div id="page" style="font-size:16px; font-family: Verdana;">
	<h1 class="h1_spacing">My Books to Sell:</h1>
	<b><i>Please close your offer if you have successfully sold your book!</i></b>
	<br /><br />
	<?php		
		$query = "SELECT * "; //know which books the user is selling
		$query .= "FROM sell ";
		$query .= "WHERE seller_id = {$user_id} ";
		$query .= "ORDER BY id ASC";
		$sell_set = mysqli_query($connection, $query);

		while ($sell_order = mysqli_fetch_assoc($sell_set))
		{
			$output = print_book_details_for_sales($sell_order);
			$output .= "<form action=\"sales.php?id="; //create a delete button that corresponds to the id in the "sell" database
			$output .= urlencode($sell_order["id"]);
			$output .= "\" method=\"post\">";
			$output .= "<input type=\"submit\" name=\"delete\" value=";
			if ($sell_order["buyers"] > 0)
				$output .= "\"Close Offer\">"; //just a manner of the text displayed. Both buttons do the same thing
			else
				$output .= "\"Cancel Offer\">";
			$output .= "</form>";
			echo $output;
		}		
	?>
	<h1 class="h1_spacing">Purchased Books:</h1>
	<br />
	<?php		
		$query = "SELECT * ";
		$query .= "FROM buy ";
		$query .= "WHERE buyer_id = {$user_id}";
		
		$buy_set = mysqli_query($connection, $query);
		
		while ($buy_order = mysqli_fetch_assoc($buy_set)) //pull all the books that the user bought
		{
			$sell_id = $buy_order["sell_id"];
			
			$query = "SELECT * ";
			$query .= "FROM sell ";
			$query .= "WHERE id = {$sell_id}";
			
			$sell_set = mysqli_query($connection, $query);
			
			while ($sell_order = mysqli_fetch_assoc($sell_set))
				echo print_book_details_for_sales($sell_order); //print some book details
		}	
	?>
</div>
</body>
</html>
<?php
	ob_end_flush();
?>