<?php
	header("Content-Type: text/xml"); 

	$db_user="sakila";
	$host="localhost";
	$password="cbuilderc";
	$database="klccomp_sakila";

	$connectHost = "mysql:host=$host;dbname=$database";
	$db = new PDO($connectHost, $db_user, $password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

	$sql = "select a.film_id as filmID, a.title as title, a.release_year as releaseYear from film a ";
	$where = $join = "";

	$inParams = (array) json_decode(file_get_contents('php://input'), TRUE);

	$ratings = array();
	if (isset($inParams['ratings'])) {
		$paramRatings = $inParams['ratings'];
      	$values = false;
		foreach($paramRatings as $key => $val) {
          	if (!empty($val)) {
              	foreach($val as $rval) {
                  	if (!empty($rval)) {
						$ratings[] = $rval;
    		          	$values = true;
                    }
                }
            }
		}
      	if ($values) {
			$in  = str_repeat('?,', count($ratings) - 1) . '?';
			$where = "where a.rating in ($in) ";
        }
    }

//	$ratings = array();
//	if (isset($inParams['ratings'])) {
//		$paramRatings = $inParams['ratings'];
//     	$values = false;
//		foreach($paramRatings as $key => $val) {
//          	if (!empty($val)) {
//				$ratings[] = $val;
//              	$values = true;
//            }
//		}
//      	if ($values) {
//			$in  = str_repeat('?,', count($ratings) - 1) . '?';
//			$where = "where a.rating in ($in) ";
//        }
//    }

	$title = array();
	if (isset($inParams['title'])) {
      	$titleString = $inParams['title'];
      	if (!empty($titleString)) {
          $title[] = "%".$titleString."%";
          $where = (strlen($where) > 0 ? $where . " and " : "where ");
          $where .= " a.title like ?";
        }
    }

	$categories = array();
	if (isset($inParams['categories'])) {
		$paramCategories = $inParams['categories'];
      	$values = false;
		foreach($paramCategories as $val) {
          	if (!empty($val)) {
              	foreach($val as $rval) {
                  	if (!empty($rval)) {
						$categories[] = $rval;
		              	$values = true;
                    }
                }
            }
		}
      	if ($values) {
			$in  = str_repeat('?,', count($categories) - 1) . '?';
			$join = "join film_category b on b.film_id = a.film_id and b.category_id in ($in)";
        }
    }

	$sql .= "$join $where order by a.title";
    $stmt = $db->prepare($sql);
	$sqlParams = array_merge($categories, $ratings, $title);
	$stmt->execute($sqlParams);
    $results = $stmt->fetchAll();

	$movies = array();
	
	foreach ($results as $row) {
		$movies[] = array('filmID' => $row['filmID'], 'title' => $row['title'], 'releaseYear' => $row['releaseYear']);
	}

    $stmt->closeCursor();

	$result = "{\"movies\":" . json_encode($movies) . "}";

	echo $result;
?>