<?php
	header("Content-Type: text/xml"); 

	$actors = array();
   	$movie = "";

	$inParams = (array) json_decode(file_get_contents('php://input'), TRUE);
	if (isset($inParams['filmID'])) {

		$db_user="sakila";
		$host="localhost";
		$password="cbuilderc";
		$database="klccomp_sakila";

		$connectHost = "mysql:host=$host;dbname=$database";
		$db = new PDO($connectHost, $db_user, $password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

		$sql = "select a.title as title,
        			a.description as description,
        			a.release_year as releaseYear,
                    a.rental_duration as rentalDuration,
                    a.rental_rate as rentalRate,
                    a.length as length,
                    a.replacement_cost as replacementCost,
                    a.rating as rating,
                    a.special_features as specialFeatures,
                    b.name as categoryName,
                    CONCAT(c.first_name, _utf8' ', c.last_name) AS actorName
                    from film a 
                    left outer join film_category fc on fc.film_id = a.film_id
                    left outer join category b on b.category_id = fc.category_id
                    left outer join film_actor fa on fa.film_id = a.film_id
                    left outer join actor c on c.actor_id = fa.actor_id
                    where a.film_id = :filmID
                    order by c.last_name, c.first_name";
    	$stmt = $db->prepare($sql);
		$stmt->execute(array(':filmID' => $inParams['filmID']));
	    $results = $stmt->fetchAll();
	
		foreach ($results as $row) {
          	if (sizeof($actors) <= 0) {
              	$movie = array('title' => $row['title'],
					'description' => $row['description'],
					'releaseYear' => $row['releaseYear'],
					'rentalDuration' => $row['rentalDuration'],
                    'rentalRate' => $row['rentalRate'],
                    'length' => $row['length'],
                    'replacementCost' => $row['replacementCost'],
                    'rating' => $row['rating'],
                    'specialFeatures' => $row['specialFeatures'],
                    'category' => $row['categoryName']);
            }

             $actors[] = array('actorName' => $row['actorName']);
		}

	    $stmt->closeCursor();
    }
	$result = "{\"movie\":" . json_encode($movie) . ", \"actors\":" . json_encode($actors) . "}";

	echo $result;
?>