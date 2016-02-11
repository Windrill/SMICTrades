<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php"); 
	
	global $current_category;
	global $current_subject;
?>
<div id="page"><br /><br />
	<?php
		$book_id = get_id_from_url(); //know which book the user is viewing by getting the id from the URL that corresponds to a book
	?>
	
	<h1 class="h1_spacing">
	<?php
		echo print_basic_book_info($book_id); //prints some book info
	?>
	</h1>
	
	
	<h2>
		<?php
			$output = "";
			if (is_logged_in())
			{
				$output = "<a href=\"sell.php?id="; //create a link to sell.php that holds the book id
				$output .= urlencode($book_id);
				$output .= "\">";
				$output .= "Sell This Book";
				$output .= "</a><br /><br />";
			}
			else
			{
				$output = cannot_buy_or_sell();
			}
			echo $output;	
		?>
	</h2>
	<br />
	<?php
		// Build query for finding the name
		if (is_logged_in()) //protect students' names if a random guest (out of SMIC) is browsing
		{
			$query = "SELECT * "; //prepare a query that displays all the sellers of the book
			$query .= "FROM sell ";
			$query .= "WHERE book_id = {$book_id}";
			$seller_set = mysqli_query($connection, $query);
			confirm_query($seller_set);
		
			$output = "<h2>Buy This Book:</h2><h3>";
			$has_seller = 0; //check to see if there is a seller for the book (0 is false)
				
			while ($seller = mysqli_fetch_assoc($seller_set))
			{
				$has_seller = 1; //set to true
				$output .= "<li>"; //HTML list to show all the selelrs
				$sell_id = $seller["id"];
				if (!already_bought($sell_id) && !is_seller($sell_id))
				//user cannot buy same book twice from same seller or buy from self
				//creates a link if the user has never bought the book before and is not his/her own book
				{
					$output .= "<a href=\"buy.php?id=";
					$output .= urlencode($sell_id);
					$output .= "&book=";
					$output .= urlencode($seller["book_id"]);
					$output .= "\">";
				}
		
				$seller_info = $seller["seller_id"]; //store the seller's id for the query
				//query to get information on the user to be printed
				$query = "SELECT * ";
				$query .= "FROM users ";
				$query .= "WHERE id = {$seller_info} "; //use the id to pull out information of the user
				$query .= "LIMIT 1";
				$seller_name = mysqli_query($connection, $query);
				confirm_query($seller_name);
				//pull the name and the grade of the seller
				while ($user = mysqli_fetch_assoc($seller_name))
				{
					$output .= $user["username"];
					$output .= " (Grade ";
					$output .= $user["grade"];
					$output .= ")";
				}
		
				$output .= " (Seller's Price: ";
				$output .= $seller["seller_price"];
				$output .= ", ";
				$output .= compare_price($book_id, $seller["seller_price"]); //%age compared to SBT price
				$output .= " of SBT's Price) (";
				$output .= "# of Buyers: ";
				$output .= $seller["buyers"];
				$output .= ") <br />";
				
				/*
				if (already_bought($sell_id))
				{
					$output .= make_press_mail_button($book_id);
					send_press_mail(); //button to send a press mail if seller doesn't respond
				}
				*/
		
		
				if (!already_bought($sell_id))
					$output .= "</a>"; //closing tag of a link
			}
		
			if(!$has_seller) //if the while loop did not come through once, the function goes here, and $has_seller is still 0, meaning there are currently no sellers for the book
			{
				$output = "There are currently no sellers for this book. Please try again later.";
			}
			$output .= "</h3>";
			echo $output;
		}
		
	?>
	
	<!--
	<h3><a href="index.php?category=
	<?php //echo strtolower(urlencode($current_category)); ?>
	&subject=<?php //echo strtolower(urlencode($current_subject)); ?>">Back</a></h3>
	<br />-->
</div>

	</body>
</html>
<?php
	ob_end_flush();
?>