
<?php
	/**
		Step 1: Read file
	**/
	echo "Please Wait.....";
	echo "\n";
	$handle = fopen("listings.txt", "r");
	$sortedList = []; // store the sorted items 
	$errorList = ["invalid_json"=>[],"missing_key"=>[]];  // line which is not in correct JSON format
	$trackItems = []; // an utility array used for tracking items
	$output = [];
	/**
		Step 2: Go through List File and catgories the list, it will mock up the smiliar structure like
		the products.txt with extra two keys, model and family.
	**/

	if ($handle) {
	    while (($line = fgets($handle)) !== false) {
	    	$line = rtrim($line, "\n");
	    	$tmp_line = strtolower($line);  // avoid Uppsercase issue

	    	$decoded_line = json_decode($tmp_line,true);
	    	if(!is_null($decoded_line)){
	    		 /* assume that the decode JSON always have the manufacturer & title KEY, otherwise it will
	    		 	be considered as invalid format
	    		 */
	    		 if(isset($decoded_line["manufacturer"]) && isset($decoded_line["title"])){
	    		 	$tmpM = $decoded_line["manufacturer"];
	    		 	$tmpT = $decoded_line["title"];
	    		 	if(!isset($sortedList[$tmpM])){
	    		 		// The item never be in the list
	    		 		$sortedList[$tmpM] = [];
	    		 		$trackItems[$tmpM] = [];
	    		 		array_push($trackItems[$tmpM],$tmpT);
	    		 		array_push($sortedList[$tmpM],["model"=>$tmpT,"family"=>$tmpT, "children"=>[$line]]);
	    		 	}else{
	    		 		/**
	    		 			If the manufacture is already in the list, then if the title are exactly same, 
	    		 			we will consider it as same item, and add to its children, otherwise, FOR NOW, it
	    		 			will be considered as different item, it will be refiltered later on
	    		 		**/
	    		 		$index = array_search($tmpT, $trackItems[$tmpM]);
	    		 		if($index === false){
	    		 			array_push($trackItems[$tmpM],$tmpT);
	    		 			array_push($sortedList[$tmpM],["model"=>$tmpT,"family"=>$tmpT, "children"=>[$line]]);
	    		 		}else{
	    		 			array_push($sortedList[$tmpM][$index]["children"],$line);
	    		 		}
	    		 	}
	    		 }else{
	    		 	array_push($errorList["missing_key"],$line);
	    		 }

	    	}else{
	    		array_push($errorList["invalid_json"],$line);
	    	}
	        // process the line read.
	    }
	    fclose($handle);
	    $myFile = "output/testFile.txt";
		$fh = fopen($myFile, 'w') or die("can't open file, please assign proper privilges to output folder, then try again!");
	   /**
	    Step 3: Read Product.txt file
	   **/
		$be_replaced = array("_", "-");
		$replaced   = array("", "");
		if(!is_null($sortedList) && sizeof($sortedList) > 0){
			$product_handle = fopen("products.txt", "r");
			if($product_handle){
				 while (($product_line = fgets($product_handle)) !== false) {
				 	$tmp = strtolower($product_line);
				 	$real_str = json_decode($product_line,true);
				 	$tmp_decode = json_decode($tmp,true);
				 	if(isset($tmp_decode["manufacturer"]) && isset($sortedList[$tmp_decode["manufacturer"]])){
				 		// Go to that list
				 		$tmpList = $sortedList[$tmp_decode["manufacturer"]];
				 		// take away special character
						$model = str_replace($be_replaced, $replaced, $tmp_decode["model"]);
						$family = isset($tmp_decode["family"]) ? str_replace($be_replaced, $replaced, $tmp_decode["family"]) : null;
				 		$sub_elem = ["product_name"=>$real_str["product_name"],"listings"=>[]];
				 		for($idx=0; $idx < sizeof($tmpList);$idx++){
				 			$candidate= $tmpList[$idx];
				 			$candidate_model = str_replace($be_replaced, $replaced, $candidate["model"]);
				 			$candidate_family = str_replace($be_replaced, $replaced,$candidate["family"]);
				 			$candidate_child = $candidate["children"];
				 			if(is_null($family)){
				 				// model is the only consideration
				 				if(strpos($candidate_model, $model) !== false){
				 					// push the chld to the listings
				 					for($j=0; $j < sizeof($candidate_child);$j++){
				 						array_push($sub_elem["listings"],$candidate_child[$j]);
				 					}
				 				}
				 			}else{
				 				// if there is family, then
				 				if(strpos($candidate_family, $family) !== false && strpos($candidate_model, $model) !== false ){
				 					for($j=0; $j < sizeof($candidate_child);$j++){
				 						array_push($sub_elem["listings"],$candidate_child[$j]);
				 					}	
				 				}
				 			}
				 		}

				 	}
				 	//print_r($sub_elem);
				 	file_put_contents($myFile, json_encode($sub_elem),FILE_APPEND);
				 	file_put_contents($myFile, "\n",FILE_APPEND);
				 }
				 fclose($product_handle);
			}else{
				die("Please supply file named products.txt");
			}
			
		}
	} else {
	    // error opening the file.
	    die("Please supply file named listings.txt");
	}

	fclose($fh);
	echo "File is successfully generated, it is under output output directory";
	
?>
