<?php
header("Content-Type: text/xml"); 

	$db_user="sakila";
	$host="localhost";
	$password="cbuilderc";
	$database="klccomp_sakila";

	$connectHost = "mysql:host=$host;dbname=$database";
	$db = new PDO($connectHost, $db_user, $password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

	$stmt = $db->prepare("SELECT column_type FROM information_schema.columns WHERE table_name = 'film' AND column_name = 'rating'");
	$stmt->execute();
	$ratingsVar = $stmt->fetchColumn();

	$stmt->closeCursor();
	
	$ratingsVar = str_replace(array("enum('", "')", "''"), array('', '', "'"), $ratingsVar);
	$ratingsArray = explode("','", $ratingsVar);

	$stmt = $db->prepare("SELECT category_id as categoryID, name FROM category");
	$stmt->execute();
	$results = $stmt->fetchAll();

	$categories = array();

	foreach ($results as $row) {
    	$categories[] = array('categoryID' => $row['categoryID'], 'categoryName' => $row['name']);
    }

	$stmt->closeCursor();

	$ratings = "{\"ratings\":" . json_encode($ratingsArray) . ", \"categories\":" . json_encode($categories) . "}";
	
	echo $ratings;
?>
