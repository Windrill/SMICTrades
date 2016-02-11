<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php");
	
	check_login(); //makes sure people cannot skip to this page without logging in
?>
<div id="page" style=" font-size:16px; font-family: Verdana;">
	<h1>Buy Book:</h1>
	<?php
		$selling_id = get_id_from_url();//in this case, the id corresponds to the id in the "sell" database
		$book_id = mysqli_real_escape_string($connection, $_GET["book"]);//this id corresponds to the id in the "book" database
		$seller_id = 0; //will be used later
	?>
	<h3>
	<?php
		echo print_basic_book_info($book_id);
	?>
	</h3>
	
		<?php
			$output = "<h2>Seller: ";
			//set up a query from the "sell" database to pull out information of the user selling the book
			$query = "SELECT * ";
			$query .= "FROM sell ";
			$query .= "WHERE id = {$selling_id} ";
			$query .= "LIMIT 1";
			$seller_query = mysqli_query($connection, $query);
			confirm_query($seller_query);
			
			while ($seller = mysqli_fetch_assoc($seller_query))
			{
				$seller_id = $seller["seller_id"]; //id of the user selling
				//use this query to pull out the username and grade level of the seller
				$query = "SELECT * ";
				$query .= "FROM users ";
				$query .= "WHERE id = {$seller_id} ";
				$query .= "LIMIT 1";
				$user_query = mysqli_query($connection, $query);
				confirm_query($user_query);
				
				while ($user = mysqli_fetch_assoc($user_query))
				{
					$output .= $user["username"];
					$output .= " (Grade ";
					$output .= $user["grade"];
					$output .= ")";
				}
				$output .= ", Price: ";
				$output .= $seller["seller_price"];
				$output .= "</h2>";
				$output .= "<h3>Seller's Description: </h3>";
				$output .= "<h4>";
				$output .= $seller["description"];
				$output .= "</h4>";
				echo $output;
			}
			
		?>
	
	<h3>Send Message to Seller:</h3>
	<!-- creates a textfield and a button for the buyer to send a message to the seller -->
	<!--text-->
	
	<form action="buy.php?id=<?php echo $selling_id; ?>&book=<?php echo $book_id; ?>" method="post">
		<textarea class="large" rows="10" name="text" value="Enter Message"></textarea>
		<input type="submit" name="send" value="Send">
	</form>
	
	<?php 
		$text = "";
		if (isset($_POST["text"]))
		{
			$text = $_POST["text"];
		}
		send_message($text, $seller_id, $selling_id); 
	?>

<!-- Starting from here, it's going to be email function -->
<!-- All commented out because email doesn't work right now -->
<!--to-->
<?php
/* 
$query_seller_id = "SELECT id FROM sell WHERE id=$selling_id";
$result=mysqli_query($connnection, $query_seller_id);
confirm_query($result);
while($row=mysqli_fetch_assoc($result))
{
	$seller_id = $row["id"];
}

$query_seller_email = "SELECT email FROM users Where id=$seller_id";
$result=mysqli_query($connection, $query_seller_email);
confirm_query($result);
while($row=mysqli_fetch_assoc($result))
{
	$to = $row["email"];
}
*/
?>

<!--header-->
<?php
/*
$query_buyer_email = "SELECT email FROM users WHERE username='{$_SESSION["username"]}'";
$buyer_email = mysqli_query($connection, $query_buyer_email);
confirm_query($buyer_email);
while($row = mysqli_fetch_assoc($buyer_email))
{
	$request_email = $row["email"] . "\r\n" ; 
}
	$header="From: $request_email";
?>

<!--subject-->
<?php
$query_username = "SELECT username FROM users WHERE username='{$_SESSION["username"]}'";
$result = mysqli_query($connection, $query_username);
confirm_query($result); 
while($row = mysqli_fetch_assoc($result)){
	$subject_temp=$row["username"];
}
	$subject = "Book Request From $subject_temp";
*/
?>


<?php
/*
if(isset($_POST["text"])
{
	$to=$_POST["text"];
}
*/
?>



<?php
/*
if(isset($_POST["send"])&&$_POST["send"]=="send")
{
	mail($to,$subject,$txt,$headers);
}
*/
?>


</body>
</html>
<?php
	ob_end_flush();
?>
