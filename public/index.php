<?php
	require_once("../includes/functions.php");
	require_once("../includes/session.php");
	include("../includes/header.php");
	
	find_selected_page(); //find page user is in
?>
<!--<div id="main">
		</ul> </div> <!--end categories and navigation-->
		
		<div id="page"><br /> <!--page of textbooks, each occupies a square in space-->
		<br />
		<!--sorting selection should go here-->
		<?php
			echo index_header(); //create the buttons of categories and subjects
		?>
		
		<h2>
		<?php
			if (!is_logged_in())
				echo cannot_buy_or_sell();
		?>
		</h2>
		<!-- books-->
		<?php
			echo index_body();	
		?>

		</div> <!-- div for page content-->
<!--</div>-->
</body>
</html>
<?php
	ob_end_flush();
?>