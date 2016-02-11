<?php
	ob_start(); //enable output buffering, allows the use of the header() function anywhere (used for redirecting pages)
?>
<?php

function redirect_to($new_location) 
{
	header("Location: " . $new_location);
	exit;
}

function db_connection()
{
	global $connection;

	//defining constants (these never vary) for the connection
	define("DB_SERVER", "127.0.0.1"); //IP address of the website: 107.180.50.184 (when developing on localhost: 127.0.0.1)
	define("DB_USER", "smic_admin"); //must be consistent to the database you make
	define("DB_PASS", "sharks2015");
	define("DB_NAME", "sharks_book_trading");

	// 1. Create a database connection
	$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME); //connection to database

	// Test if connection occurred.
	if (mysqli_connect_errno()) 
	{   //if there was an error
		//quit it with die()
		die("Database connection failed: " . mysqli_connect_error() . " (" . mysqli_connect_errno() . ")");
	}
}

//for testing code, allows us to know if the database query was successful
//place the query set (mysqli_query($connection, $query)) as the parameter to check
function confirm_query($result_set)
{
	if (!$result_set) //if there was query error
	{
		die("Database query failed.");
	}
}

function find_all_categories($connection)
{
	$query = "SELECT * ";
	$query .= "FROM books "; //pick up all books from the database
	$category_set = mysqli_query($connection, $query);
	confirm_query($category_set);
	return $category_set;
}

//find pages in a certain subject
function find_selected_page() 
{
	global $connection;
	global $current_category;
	global $current_subject;

	if (isset($_GET["category"])) //pick up the selected category, if possible, allows to print all books in category
	{
		$valid_category = 0; //boolean variable to make an if-statement test later
		$current_category = mysqli_real_escape_string($connection, $_GET["category"]); //stores the category the user is currently in
		$category_set = find_all_categories($connection);
		while ($category = mysqli_fetch_assoc($category_set))
		{
			if ($current_category == strtolower($category["category"]))
			{
				$valid_category = 1;
				if ($current_category == "ap")
					$current_category = strtoupper($current_category); //special case because ucwords() cannot capitalize "P" in "AP"
				else 
					$current_category = ucwords($current_category);
			}
		}
		
		if(!$valid_category) //if the user enters a wrong category in the URL, the site automatically brings the user back to the homepage
			redirect_to("index.php");

		//must have a category before you have a subject
		if (isset($_GET["subject"])) //pick up the selected subject, if possible, allows to print all books in subject within category
		{
			$current_subject = mysqli_real_escape_string($connection, $_GET["subject"]);
			if ($current_subject == "us history")
				$current_subject = "US History"; //special case because ucwords() cannot capitalize "S" in "US"
			else
				$current_subject = ucwords($current_subject);
		}
	}
	elseif(!isset($_GET["category"]) && isset($_GET["subject"])) //if only a subject is entered, redirect
		redirect_to("index.php");
}


function find_unique_categories($category_set) {
	$total = array();
	$con = 0;
	foreach($category_set as $cat) {
		foreach($total as $tot) {
		if($cat["category"] == $tot){ 
			$con = 1; 
			continue;
			}
		}
		if($con == 1) { 
			$con=0;
			continue;
		}
		else $total [] = $cat["category"];
	}
	return $total;
}

function find_unique_subjects($subject_set) {
	$total = array();
	$con = 0;
	foreach($subject_set as $cat){
		foreach($total as $tot) {
			if($cat["subject"] == $tot) { 
				$con = 1; continue;
			}	
		}
		if($con == 1) { 
			$con=0;
			continue;
		}
		else $total [] = $cat["subject"];
	}
	return $total;
}

function index_header()
{ 
	global $connection;
	global $current_category;
	global $current_subject;
	
	$output = "<a class=\"divide\">Category:</a>"; //preparing to print the categories on the top of the main page
	$category_set = find_all_categories($connection); //pull all the categories in the book list
	$unique_categories = find_unique_categories($category_set);
	$unique_subjects = null;
	$selected_cat = null;
	for($i=0; $i< count($unique_categories); $i++){
		//prints a button for each category
		$cat = $unique_categories[$i]; //current category
		$output.="<a class=\"no_line\" href=\"index.php?category=".strtolower(urlencode($cat))."\"><sort"; //create links
		if($current_category==$cat) { //so that subject highlighting only happens once
			$output.=" class=\"selected\">".$cat; //highlights the current subject selected
		
			$query = "SELECT * FROM books WHERE category = '{$cat}' ";
			$query.= "ORDER BY subject ASC"; //3 lines of query
			$books_in_category = mysqli_query($connection, $query);
			confirm_query($books_in_category);
		
			$unique_subjects = find_unique_subjects($books_in_category); //find all subjects under a category
			$selected_cat = $cat;
		} //should only happen once
		else {
			$output.=">".$cat;
		}//happens all other times

		$output.="</a>"; //link and category		
	}
	
	$output.="</sort><br /><br /><a class=\"divide\">Subject:</a><sorting>"; //break between categories and subjects
	if (count($unique_subjects) != 0)
	{
		for($j=0; $j<count($unique_subjects); $j++) {
			//prints a button for each subject
			$sub = $unique_subjects[$j];
			$output.="<sort2";
			if($current_subject==$sub) {
				$output.=" class=\"selected\"";
			}
			$output.="><a class=\"no_line\"href=\"index.php
				?category=".strtolower(urlencode($selected_cat))."&subject=".strtolower(urlencode($sub))."\">";
			$output.=$sub."</a></sort2>";
		}
	}
	else
	{
		$output .= "<i>Please select a category above.</i>";
	}
	$output .= "</sorting><br />";//group all category and subject buttons into 2 <> tags both called sorting
	$subject_output_all = "<br /><form action=\"index.php\" method=\"post\">"; //final printing
	$subject_output_all .= "<b>Search book by ISBN-13: </b>";
	$subject_output_all .= "<input type=\"text\" name=\"isbn\" value=\"\" />&nbsp;";
	$subject_output_all .= "<input type=\"submit\" name=\"search\" value=\"Search\" /><br />";
	$subject_output_all .= "</form><br />";
	$subject_output_all .= "<font color=\"#3D3D3D\">";
	$subject_output_all .= isbn_search(); //for searching the isbn
	$subject_output_all .= "</font>";
	$output .= $subject_output_all;
	return $output;
}
//prints categories in a drop down list when mouse hovers over the "Books" button
function category_dropdownlist()
{ 
	global $connection;
	$subject_output_all="";
	$category_set = find_all_categories($connection);
	$previous_category = ""; //holds previously printed category to prevent printing multiple times

	while($category = mysqli_fetch_assoc($category_set)){
			if ($category["category"] != $previous_category) //make sure this is a new category to print
		{
		$cat = $category["category"];
		$subject_output_all.="<a href=\"index.php?category=";
		$subject_output_all .= $cat;
		$subject_output_all.="\"><li>";
		$subject_output_all.=$cat; $subject_output_all.="</li></a>";

		$previous_category =$category["category"];
		}	
	} //end while
	return $subject_output_all;
	
}
//selects books belonging to a category to be printed
function index_body()
{
	global $connection;
	global $current_category;
	global $current_subject;
	
	$ret = ""; //what is returned at the end
	
	$query = "SELECT * ";
	$query .= "FROM books ";
	
	if($current_category) //if there is a selected category
	{
		//creates a link to G11 english because AP English books are there too
		if ($current_category == "AP" && !$current_subject) //link appears only when user didn't select any subject
		{
			$ret .= "<h3>Are you in AP English Language? Find your books ";
			$ret .= "<a href=\"index.php?category=grade+11&subject=english\">";
			$ret .= "here";
			$ret .= "</a>.</h3>";
		}
		$query .= "WHERE category = '{$current_category}' ";
		//place the category into the query
		if ($current_subject) //if there is a selected subject
		{
			$query .= "AND subject = '{$current_subject}' "; //add the subject into the query along with the category
			if ($current_subject == "US History")//special case for APUSH
			{
				$ret .= "<h3>Purchase ";
				$ret .= "<a href=\"book.php?id=85\">"; //if the id of this book changes, be sure to change this id
				$ret .= "The Narrative of the Life of Frederick Douglass: An American Slave";
				$ret .= "</a></h3>";
			}
		}
	} //end if current category

	$query .= "ORDER BY name ASC";
	$book_set = mysqli_query($connection, $query);
	confirm_query($book_set);
	
	$ret .= print_book_details($book_set); //prints each book's details
	
	/*
	if ($current_category != "Other") //if people can't find their books because it is likely in "other"
	{	
		//needs to be below all the book boxes, requires css
		$ret .= "<h3>Can't find your book? Your book may be "; 
		$ret .= "<a href=\"index.php?category=other\">";
		$ret .= "here";
		$ret .= "</a>.</h3>";
	}
	*/
	
	return $ret; //ret means return but return is a keyword
}

//outputs book details for each book under selected category and subject
function print_book_details($book_set)
{ 
	//$array=array(); seemingly unimportant code?
	$return="";
	//this will output specific book details
	while ($book = mysqli_fetch_assoc($book_set)) //print every book within the category (and subject)
	{
		$output="";
		$img="";
		//adds a picture of the book
		if(file_exists("../pictures/".$book["isbn"].".PNG")){
			$img.="<img id=".$book["isbn"]."src=\"../pictures/".$book["isbn"].".PNG\"> </img>"; 
		}
		
		//output: link
		$output.= "<a href=\"book.php?id=".$book["id"]."\">"; 
		
		//book information: contents of each summary
		$output .= ",contents><b_name class=\"header\">".$book["name"]."</b_name>"; //information for each book, each book occupies a square
		$output .= "<br />Publisher: ".$book["publisher"];
		$output .= "<br />ISBN: ".$book["isbn"];
		$output .= "<br />SBT Price: ".$book["sbt_price"];
		$output .= " <br /></contents>";	
/*
		if(isset($_SESSION["login"]) && $_SESSION["login"]) 
		$output .= "<b>Sell this book(no link yet)</b><br />";

		$output.="<b>Look for buyers (no link yet)</b>";
*/
		$return.= "<books>" .$img.$output."</books></a>";

	} //end assoc while loop
	return $return;
}

function print_book_details_for_sales($sell_order)
{
	//designed for sales.php page, only includes certain details not included in print_book_details()
	global $connection;
	
	$output = "<li>";
	$book_id = $sell_order["book_id"];
	$price = $sell_order["seller_price"];

	$query = "SELECT * ";
	$query .= "FROM books ";
	$query .= "WHERE id = {$book_id}";

	$book_set = mysqli_query($connection, $query);
	confirm_query($book_set);

	while ($book = mysqli_fetch_assoc($book_set))
	{
		$output .= $book["name"];
		$output .= " (";
		$output .= $book["category"];
		$output .= " - ";
		$output .= $book["subject"];
		$output .= ")";
	}
	
	$output .= " (Selling Price: ";
	$output .= $price;
	$output .= ") ";
	
	return $output;
}

//signup function
function signup()
{ 
	$_SESSION["errors"]=array();
	$_SESSION["message"]="";
	if (isset($_POST['submit'])) //when the submit button is hit
	{
		$username = mysqli_real_escape_string($connection, trim($_POST["username"])); //trim spaces, save the info
		$_SESSION["username"]=$username;
		$password = mysqli_real_escape_string($connection, trim($_POST["password"]));
		$confirm_password = mysqli_real_escape_string($connection, trim($_POST["confirm_password"]));
		$grade = $_POST["grade"];
		$email = mysqli_real_escape_string($connection, trim($_POST["email"]));
		$code = mysqli_real_escape_string($connection, trim($_POST["code"]));
		$fields_required = array("username", "password", "confirm_password", "grade", "email", "code");

		foreach($fields_required as $field) //check each field if they are filled
		{
			$value = trim($_POST[$field]);
			if (!has_presence($value))//if something isn't there, return text that shows the error(s)
			{
				if ($field == "confirm_password") //special case for confirming passwords
				{ //error message will show "Confirm_password" can't be blank without this
					$_SESSION["errors"][$field] = "Please confirm your password.";
				}
				else
				{
					$_SESSION["errors"][$field] = ucfirst($field) . " can't be blank.";
				}
			}
		}

		if (!isset($_POST["read"])) //special for checking if the user agrees to the terms & conditions
			$_SESSION["errors"]["read"] = "You must agree to our Terms & Conditions.";
			
		//associative array to hold maximum lengths
		$fields_with_max_lengths = array("username" => 50, "password" => 50, "confirm_password" => 50, "email" => 75);

		validate_max_lengths($fields_with_max_lengths); //check the parameters
		validate_username($username);
		validate_password($password, $confirm_password);
		validate_code($code);
		if (empty($_SESSION["errors"])) //if no errors, user can move on
		{
			//add user
			db_connection();
			global $connection;
			$encrypted = encrypt($password);
			$query = "INSERT INTO users (username, password, grade, email, hash) ";//add into the database
			$query .= "VALUES ('{$username}', '{$password}', {$grade}, '{$email}', '{$encrypted}')";
			$signingup = mysqli_query($connection, $query);
			confirm_query($signingup);
			$_SESSION["errors"]=array();
			$_SESSION["message"]="";
			redirect_to("login.php");
			return $signingup;
		}//end empty session errors
	}
	else //fix the errors
	{
	$username = "";
	$_SESSION["message"] .= "Please fill in the form below.";
	}
	
}

//password
function encrypt($password)
{ 
	$hash_format="$2y$10$";
	$salt=salt(22);
	$format_and_salt=$hash_format.$salt;
	$hash=crypt($password,$format_and_salt);
	return $hash;
}

//helps password, used example lecture instructions
function salt($length)
{ 
	$str=md5(uniqid(mt_rand(), true));
	$base=base64_encode($str);
	$base64=str_replace('+','.',$base);
	$salt=substr($base64, 0, $length);
	return $salt;
}


//small functions to check small things
//length
function under_max_length($value, $max)
{
	return strlen($value) <= $max;
}
function validate_max_lengths($fields_with_max_lengths)
{
	foreach($fields_with_max_lengths as $field => $max)
	{
		$value = trim($_POST[$field]);
		if (!under_max_length($value,$max))
		{
			$_SESSION["errors"][$field] = ucfirst($field) . " is too long.";
		}
	}
}

function has_presence($value) {
	return isset($value) && $value !== "";
}

function has_max_length($value, $max) {
	return strlen($value) <= $max;
}

function validate_username($username)
{
	//people cannot have the exact same username
	global $connection;
	
	$query = "SELECT * ";
	$query .= "FROM users";
	
	$user_set = mysqli_query($connection, $query);
	confirm_query($user_set);
	
	while ($user = mysqli_fetch_assoc($user_set))
	{
		if ($user["username"] == $username)
		{
			$_SESSION["errors"]["username"] = "Your username has been used already. Try using an underscore/space instead.";
			return;
		}
	}
}

function validate_password($password, $confirm)
{
	if ($password != $confirm)
	{
		$_SESSION["errors"][$confirm] = "Your password and confirmation don't match.";
	}
}

function validate_code($code)
{
	if ($code != "asfoihqweoirh") //need to change this code every year
	{
		$_SESSION["errors"][$code] = "The code is incorrect.";
	}
}
s
//login check for sanity
function login_process()
{ 
	if(isset($_POST['submit']) && isset($_POST["username"]) && isset($_POST["password"]))
	{
		//echo $_POST['submit'];
		$username=trim($_POST["username"]);
		$password=trim($_POST["password"]);
		
		$fields_required = array("username", "password");
		foreach($fields_required as $field)
		{
		$value = trim($_POST[$field]);
		if(!has_presence($value)) 
		{
		$_SESSION["errors"][$field] = ucfirst($field)." can't be blank";
		}
		}

		$fields_with_max_lengths = array("username" => 50,"password" => 50);
		validate_max_lengths($fields_with_max_lengths);
		//echo print_r($_SESSION["errors"]);
		if (empty($_SESSION["errors"]))
		{
			if(login($username, $password))
			{
				$_SESSION["username"]=$username;
				$_SESSION["password"]=$password;
				$_SESSION["login"]=true;
				redirect_to("index.php");
			}
			else
			{
				$_SESSION["message"]="Your username or password does not match.";
			}
		}
		else
		{
			$username="";
			$_SESSION["message"]="Please log in.";
		}
		
	} 
	else
	{
		$username="";
		$_SESSION["message"]="Please log in.";
	}

}

//login function			
function login($username, $password)
{ 
	//db_connection();
	global $connection;
	$login = "SELECT * ";
	$login .= "FROM users ";
	$login .= "WHERE username = '{$username}' ";
	$login .= "LIMIT 1";
	$user_query = mysqli_query($connection, $login);
	confirm_query($user_query);
	
	$hash = "";
	
	while ($user = mysqli_fetch_assoc($user_query))
	{
		$_SESSION["id"] = $user["id"];
		$hash = $user["hash"];
	}
	
	if(crypt($password, $hash)==$hash) 
		return true;
	else 
		return false;
}

//inclusion in a set
function has_inclusion_in($value, $set)
{
	return !in_array($value, $set);
}

function form_errors($error_list = array())
{
	$output = "";
	if (!empty($error_list))
	{
		//$output .= "<div class=\"error\">";
		$output .= "Please fix the following errors:";
		$output .=  "<ul>";
		foreach ($error_list as $key => $error)
			$output .=  "<li>{$error}</li>";
		$output .=  "</ul>";
		//$output .=  "</div>";
	}
	return $output;
}
	




function get_id_from_url()
{
	global $connection;
	$book_id = "";
	if ($_GET["id"])
	{
		$book_id = mysqli_real_escape_string($connection, $_GET["id"]);
	}
	else
	{
		redirect_to("index.php"); //prevent people from seeing errors if they're smart to remove the id in the url
	}
	return $book_id;
}

function print_basic_book_info($book_id) //currently only the book name
{
	global $connection;
	global $current_category;
	global $current_subject;
	$query = "SELECT * ";
	$query .= "FROM books ";
	$query .= "WHERE id = {$book_id} ";
	$query .= "LIMIT 1";
	$book_from_query = mysqli_query($connection, $query);
	confirm_query($book_from_query);
	
	//Top row of a specific book found by id number
	$current_category = "";
	$current_subject = "";
	
	/*while (*/
	$book = mysqli_fetch_assoc($book_from_query);
	/*)
	{*/
	if($book !=null){
		$current_category = $book["category"];
		$current_subject = $book["subject"];
		
		$output = $book["name"];
		$output .= " ("."<a href=\"index.php?category={$current_category}\">" . $current_category;
		$output .= "</a> - " ." ("."<a href=\"index.php?category={$current_category}&subject={$current_subject}\">". $current_subject;
		$output .= "</a>)";
		echo $output;
	}/*end if*/
	/*}*/
}

function isbn_search() //searches for books with the ISBN #
{
	global $connection;
	if (isset($_POST['search'])) //the search button is called 'search,' after it is hit, run the statements below
	{
		$isbn = (int)(trim($_POST["isbn"]));
		$query = "SELECT * ";
		$query .= "FROM books ";
		$query .= "ORDER BY id ASC";
		$book_set = mysqli_query($connection, $query);
		confirm_query($book_set);
		
		while ($book = mysqli_fetch_assoc($book_set))
		{
			if ($isbn == $book["isbn"])//look for the book based on the ISBN #
			{
				$book_id = $book["id"];
				redirect_to("book.php?id={$book_id}");
				return ""; 
				//since function returns something if there is no result, there needs to be an empty return here if the book is found
			}
		}
		
		return "Your search matched no results. Please try again.<br /><br />";
	}
}

function send_message($text, $seller_id, $sell_id)
{
	if (isset($_POST["send"]))// || isset($_POST["press"]))
	{
	//	if (!isset($_POST["press"])) //if user wishes to send press mail, can't add buyer
	//	{
			$buyer_id = add_buyer();//add the buyer into the database
			create_message_row($text, $buyer_id, $seller_id, $sell_id);//create a conversation between buyer and seller
	//	}
		redirect_to("index.php");
	}
}
/* currently not in use
function make_press_mail_button($book_id) //for buyers to send press mails to sellers if they don't respond
{
	$output = "<form action=\"book.php?id=";
	$output .= urlencode($book_id);
	$output .= "\" method=\"post\">";
	$output .= "<input type=\"submit\" name=\"press\" value=\"Send Press Mail\"></form>";
	return $output;
}

function send_press_mail()
{
	if (isset($_POST["press"]))
	{
		send_message();
	}
}
*/
function reset_cookie()
{
	//every time a user clicks into something, the user has one hour before auto-logout
	if (is_logged_in())
	{
		set_cookie();
	}
}

function set_cookie()
{
	//gives one hour for user before auto-logout if nothing was pressed in that hour
	setcookie(session_name(), session_id(), time()+(60*60));
}

function check_login()
{
	//makes sure people don't jump to pages by typing in url without logging in
	if (!is_logged_in())
	{
		redirect_to("index.php");
	}
}

function add_buyer()
{
	//adds a buyer count to the database
	global $connection;
	
	$sell_id = (int)mysqli_real_escape_string($connection, $_GET["id"]);
	//the id stored in the "sell" database is in the URL, must use this id to register the buyer with the seller
	$buyer_id = (int)$_SESSION["id"];
	//this is the id of the current user, which is the buyer
	$buyers = 0;
	//initializing the # of buyers. this number will change
	
	$query = "SELECT * ";
	$query .= "FROM sell ";
	$query .= "WHERE id = {$sell_id} ";
	$query .= "LIMIT 1";//read back the row that contains the information of the seller and the book that he/she is selling
	$sell_set = mysqli_query($connection, $query);
	confirm_query($sell_set);
	
	while ($sell = mysqli_fetch_assoc($sell_set))
	{
		$buyers = $sell["buyers"];
		//this database contains a column called "buyers," which is responsible for holding the # of people who bought this book.
		//this number is displayed at each book.php page to let buyers know how many people have already purchased each book.
	}
	
	$buyers++; //add one buyer
	
	$query = "UPDATE sell ";//place that update above and store it in the database (yep, all those lines just to add 1 to a column..)
	$query .= "SET buyers = {$buyers} ";
	$query .= "WHERE id = {$sell_id}";
	$sell_set = mysqli_query($connection, $query);
	confirm_query($sell_set);
	
	$query = "INSERT INTO buy (sell_id, buyer_id) ";//record the buyer into the database
	$query .= "VALUES ({$sell_id}, {$buyer_id})";
	//sell_id is the id in the "sell" database, 
	//buyer_id is the user id which is buying a certain book. this allows the database to remember who bought which book
	$buyer = mysqli_query($connection, $query);
	confirm_query($buyer);
	
	return $buyer_id;
	//this return allows callers of the function to remember the buyer's id
}

//creates a row in the "messages" database. this opens a conversation between the buyer and seller in the website's messaging system
function create_message_row($text, $buyer_id, $seller_id, $sell_id)
{
	global $connection;
	
	//need to know what the book is to let the buyer and seller know which book is being discussed for selling
	$book_id = mysqli_real_escape_string($connection, $_GET["book"]);
	
	$query = "INSERT INTO messages ";
	$query .= "(buyer_id, seller_id, book_id, sell_id, ";
	$query .= "message1, message2, message3, message4, message5, speaker1, speaker2, speaker3, speaker4, speaker5) ";
	//the messaging system only records the 5 most recent messages between the buyer and seller. once 5 messages are reached,
	//the messages are shifted back one to leave space for the new message, which is covered in shift_messages_left().
	$query .= "VALUES ({$buyer_id}, {$seller_id}, {$book_id}, {$sell_id}, '{$text}', '', '', '', '', 'b', '', '', '', '')";
	/* values put inside this database:
	 * buyer_id: the user id of the buyer
	 * seller_id: the user id of the sller
	 * book_id: the id of the book
	 * sell_id: the id in the "sell" database
	 * message1 ~ message5: 5 most recent messages between the buyer and seller
	 * speaker1 ~ speaker5: records who wrote what message. "b" for buyer, "s" for seller
	 */
	$message = mysqli_query($connection, $query);
	confirm_query($message);
}

//places an offer to sell a certain book
function sell_book()
{
	global $connection;
	
	if (isset($_POST['sell']))//if the "sell" button is pressed
	{	
		$price = $_POST["price"];//pull the price the seller placed
		$price = check_price($price); //ensure the price is a number
		$description = $_POST["description"];//pull the description of the book
		
		if ($price > 0)
		{
			$id = (int)$_SESSION["id"]; //id of the seller
			$book_id = (int)mysqli_real_escape_string($connection, $_GET["id"]);
			$query = "INSERT INTO sell (book_id, seller_id, seller_price, buyers, description) ";
			$query .= "VALUES ({$book_id}, {$id}, {$price}, 0, '{$description}')";
		
			$result = mysqli_query($connection, $query);
			confirm_query($result);
		
			redirect_to("index.php");
			return "";//allows a return of text if the price was invalid (below)
		}
		else
			return "<br />The price you entered was invalid. Please try again.";
	}
	else //when the sell button isn't pressed (the user has just entered the page)
		return "";
		//allows a return of text if the price was invalid (above)
}

function check_price($price)
{
	if (is_numeric($price))
	{
		$price = (int)$price;
		return $price;
	}
	else return 0; //since price must be above 0, this allows an error if the user entered an invalid price
}

//check to see if the user is logged in. this is an important check to disable guests from selling books.
function is_logged_in()
{
	//all must be satisfied to ensure the user is logged in (cookies and sessions check)
	return isset($_COOKIE[session_name()]) && isset($_SESSION["login"]) && $_SESSION["login"];
}

//check to see if the user has already bought a certain book from a certain seller. one cannot double-buy the same book from the same seller
//use sell_id to check. so if the seller sells two of the same book, they have different entries in the "sell" database, and students are allowed to buy both of these
function already_bought($sell_id)
{
	global $connection;
	
	$user_id = $_SESSION["id"]; //check the user's id (buyer)
	
	$query = "SELECT * ";
	$query .= "FROM buy ";
	$query .= "WHERE buyer_id = {$user_id}";
	$buyer_set = mysqli_query($connection, $query);
	
	while ($buyer = mysqli_fetch_assoc($buyer_set))
	{
		if ($buyer["sell_id"] == $sell_id) //since the "buy" database records the "sell_id," we are able to check if the buyer has already made a purchase on a certain book from a certain seller
			return true;
	}
	return false;
}

//check to see if the current logged in user is the seller of the book. this is used in the book.php page. you cannot buy your own book!
function is_seller($sell_id)
{
	global $connection;
	
	$user_id = $_SESSION["id"]; //current user id
	
	$query = "SELECT * ";
	$query .= "FROM sell "; //the sell database contains a "sell_id" and a "sellER_id" that knows which user is selling a certain book at this price
	$query .= "WHERE id = {$sell_id} ";
	$query .= "LIMIT 1";
	$seller_set = mysqli_query($connection, $query);
	
	while ($seller = mysqli_fetch_assoc($seller_set))
	{
		if ($seller["seller_id"] == $user_id)
			return true;
	}
	return false;
}

//deletes an offer when the seller has successfully sold a book or decides not to sell a book anymore
function delete_offer()
{
	if (isset($_POST["delete"]))
	{		
		global $connection;
		
		$sell_id = mysqli_real_escape_string($connection, $_GET["id"]); //the id in the URL is the id from the "sell" database
	
		//the "sell," "buy," and "messages," database all use the "sell_id," so each database can use the "sell_id" to remove all the necessary rows from the database to prevent any empty purchases/conversations
		
		$query = "DELETE FROM sell ";
		$query .= "WHERE id = {$sell_id}";
		$delete = mysqli_query($connection, $query);
		
		$query = "DELETE FROM buy "; //must remove the buyers too
		$query .= "WHERE sell_id = {$sell_id}";
		$delete = mysqli_query($connection, $query);
		
		$query = "DELETE FROM messages "; //must remove conversation too
		$query .= "WHERE sell_id = {$sell_id}";
		$delete = mysqli_query($connection, $query);
		
		redirect_to("sales.php");
	}
}

//gives a direct %age comparison of the secondhand book and a new book from Shanghai Book Traders for buyers to know how reasonable each price is
function compare_price($book_id, $seller_price)
{
	global $connection;
	
	$query = "SELECT * ";
	$query .= "FROM books ";
	$query .= "WHERE id = {$book_id} ";
	$query .= "LIMIT 1";
	$book_set = mysqli_query($connection, $query);
	
	$sbt_price = 0; //initialize the price from Shanghai Book Trading
	
	while ($book = mysqli_fetch_assoc($book_set))
	{
		$sbt_price = $book["sbt_price"];
	}
	
	$output = ($seller_price/$sbt_price)*100; //math to output the %age
	$output = round($output, 1);
	$output .= "%";
	return $output;
}


//a small output to guests if they wish to buy/sell books
function cannot_buy_or_sell()
{
	$output = "You must ";
	$output .= "<a href=\"login.php\">";
	$output .= "log in";
	$output .= "</a>";
	$output .= " to buy or sell books.";
	return $output;
}

//function that creates a textfield and a button in the messages.php page for the buyer/seller, depending on if he/she will reply or add to the previous message
//the action is either "buy" or "sell."
function make_field_and_button($message_id, $action)
{
	global $connection;
	$output = "<form action=\"messages.php?id={$message_id}&action={$action}\" method=\"post\">"; //both the buyer and seller need a textarea
	$output .= "<textarea rows=\"5\" cols=\"50\" name=\"add_text\" value=\"\"></textarea><br />";
	$action_short = substr($action, 0, 1); //taking the first character from these words allows us to use those characters in the "speaker1 ~ speaker5" to know whether the buyer or seller created a certain message
	
	//below is a check to see what type of button ("reply" or "add message") to add on the bottom 
	$query = "SELECT * ";
	$query .= "FROM messages ";
	$query .= "WHERE id = {$message_id} ";
	$query .= "LIMIT 1";
	$message_set = mysqli_query($connection, $query);
	
	while ($message = mysqli_fetch_assoc($message_set))
	{
		for ($i = 5; $i >= 1; $i--)
		{
			$speaker_var = "speaker" . $i;
			//the $i = 0; statement within each condition allows the for loop to jump out
			if ($action_short == 'b') //if the user is a buyer of a book
			{
				if ($message["{$speaker_var}"] == 'b') //if the most recent message is also from the buyer
				{
					$output .= "<input type=\"submit\" name=\"add\" value=\"Add Message\">"; //user can add a message
					$i = 0;
				}
				elseif($message["{$speaker_var}"] == 's') //if the most recent message is from the seller
				{
					$output .= "<input type=\"submit\" name=\"reply\" value=\"Reply\">"; //user replies
					$i = 0;
				}
				//no condition for blank - the for loop just skips blank entries
			}
			else //if action_short == 's'
			{	
				if ($message["{$speaker_var}"] == 's') //if the most recent message is also from the seller
				{
					$output .= "<input type=\"submit\" name=\"add\" value=\"Add Message\">"; //user can add a message
					$i = 0;
				}
				elseif($message["{$speaker_var}"] == 'b') //if the most recent message is from the buyer
				{ 
					$output .= "<input type=\"submit\" name=\"reply\" value=\"Reply\">"; //user replies
					$i = 0;
				}
			}
		}
	}
	$output .= "</form>";
	return $output;
}

//if the user wishes to add a message to his/her previous message (prevents one from overflowing the conversation)
//the action is either "buy" or "sell."
function add_message($message_id, $action)
{
	if (isset($_POST["add_text"]) && isset($_POST["add"]))
	{
		global $connection;
		$action_short = substr($action, 0, 1); //taking the first character from these words allows us to use those characters in the "speaker1 ~ speaker5" to know whether the buyer or seller created a certain message
	
		$query = "SELECT * ";
		$query .= "FROM messages ";
		$query .= "WHERE id = {$message_id} ";
		$query .= "LIMIT 1";
		$message_set = mysqli_query($connection, $query);

		while ($message = mysqli_fetch_assoc($message_set))
		{
			for ($i = 5; $i >= 1; $i--) //use a for-loop to find which one the database needs to update (message1 ~ message5)
			{
				$speaker_var = "speaker" . $i;
				$message_var = "message" . $i;
				if (isset($message["{$message_var}"]) && $message["{$speaker_var}"] == $action_short) //if there is a message and it is by the same person trying to add the message, then the message can be updated
				{
					$current_message = $message["{$message_var}"]; //what is already stored in the database
					$added_message = $_POST["add_text"]; //what the user wishes to add
					$new_message = $current_message . "<br />" . $added_message; //new message that contains old and new message
										
					$query = "UPDATE messages "; //update it in the database
					$query .= "SET {$message_var} = '{$new_message}' ";
					$query .= "WHERE id = {$message_id}";
					$update = mysqli_query($connection, $query);
					
					$i = 0; //jump out of the loop
					redirect_to("messages.php");
				}
				//if the condition isn't met, the for loop goes here and loops again after i--
			}
		}
	}
}

//if the user wishes to reply to a message
//the action is either "buy" or "sell."
function reply($message_id, $action)
{
	if (isset($_POST["add_text"]) && isset($_POST["reply"]))
	{
		global $connection;
		$action_short = substr($action, 0, 1); //taking the first character from these words allows us to use those characters in the "speaker1 ~ speaker5" to know whether the buyer or seller created a certain message
		
		$query = "SELECT * "; //find which message slot (message1 ~ message5) the message needs to be placed
		$query .= "FROM messages ";
		$query .= "WHERE id = {$message_id} ";
		$query .= "LIMIT 1";
		$message_set = mysqli_query($connection, $query);

		while ($message = mysqli_fetch_assoc($message_set))
		{
			$i = 2; //declare here because it is used after for loop too. i = 2 because the first message is always taken by the buyer
			for ($i = 2; $i <= 5; $i++)
			{
				$speaker_var = "speaker" . $i;
				$message_var = "message" . $i;
				if ($message["{$message_var}"] == "") //if there is no message in the message slot
				{
					$reply_message = $_POST["add_text"];
					
					$query = "UPDATE messages ";
					$query .= "SET {$message_var} = '{$reply_message}', ";
					$query .= "    {$speaker_var} = '{$action_short}' "; //this is the 'b' or the 's' that records whether the buyer or seller created that message
					$query .= "WHERE id = {$message_id}";
					$update = mysqli_query($connection, $query);
					
					$i = 10; //jump out of the loop (why 10? i don't know :P but it cannot be 6 because of the if-statement below)
					redirect_to("messages.php");
				}
			}
			if ($i == 6) //after looping (means the database table is full in that row, the "if($message == "")" was not met)
				shift_messages_left($message_id, $action);
		}
	}
}

//if the row in the message database is full, then the row needs to update the 5 most recent messages. this function deletes the least recent message and replaces it with the new message.
//the action is either "buy" or "sell."
function shift_messages_left($message_id, $action)
{
	global $connection;
	$action_short = substr($action, 0, 1); //taking the first character from these words allows us to use those characters in the "speaker1 ~ speaker5" to know whether the buyer or seller created a certain message
	
	$query = "SELECT * ";
	$query .= "FROM messages ";
	$query .= "WHERE id = {$message_id} ";
	$query .= "LIMIT 1";
	$message_set = mysqli_query($connection, $query);

	while ($message = mysqli_fetch_assoc($message_set))
	{
		//hold all the messages and their speakers ('b' or 's')
		$messages = array($message["message1"], $message["message2"], $message["message3"], $message["message4"], $message["message5"]);
		$speakers = array($message["speaker1"], $message["speaker2"], $message["speaker3"], $message["speaker4"], $message["speaker5"]);
		
		//shifts all of the elements to the left
		for ($i = 0; $i < 4; $i++)
		{
			$messages[$i] = $messages[$i+1];
			$speakers[$i] = $speakers[$i+1];
		}
		
		//clear the last element in the array
		$messages[4] = "";
		$speakers[4] = "";
		
		//place the 4 most recent messages back into the database, but shifted one position left
		for ($i = 1; $i <= 5; $i++)
		{
			$speaker_var = "speaker" . $i;
			$message_var = "message" . $i;
			
			//when $i = 1, the corresponding array element is 0; when $i = 2, the corresponding array element is 1, and so on
			$changed_message = $messages[$i-1]; //the message content
			$changed_speaker = $speakers[$i-1];

			$query = "UPDATE messages ";
			$query .= "SET {$message_var} = '{$changed_message}', ";
			$query .= "    {$speaker_var} = '{$changed_speaker}' ";
			$query .= "WHERE id = {$message_id}";
			$update = mysqli_query($connection, $query);
		}
	}
	reply($message_id, $action); //go back and reply with the empty message5 and speaker5 column
}

?>
<?php
	ob_end_flush();
?>