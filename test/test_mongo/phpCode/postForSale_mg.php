<?php
// test case: seller want to post a product for sale
// input: detail product information where pid = 1 (query max _id before insert)

//step 1. connect to database
$dbhost = 'localhost';
$dbname = 'exchangeplus_worcester_mongo';
$m = new MongoClient();
$db = $m->$dbname;
$collection = $db->products;

//step 2. set float precision
ini_set('precision', 20);

//step 3 initialize: 
//step 3-1: initialize the product information (query from products where pid = 1) for insert into products
$query = array('_id'=>1);
$cursor = $collection->findOne($query);
$name = $cursor['name']; 
$category = $cursor['category'];
$demanding_price = $cursor['demanding_price'];
$image = $cursor['image'];
$seller_id = $cursor['seller_id'];
$buyerlist = array();

//step 3-2: initialize #tires
$triesArr = array(10, 100, 200, 500, 1000, 2000, 4000, 6000, 8000, 10000);
for($t = 0; $t < sizeof($triesArr); $t++){ 
	$tries = $triesArr[$t];
	echo "# of tries = ".$tries."<br>";
	//step 3-3. initialize $sum_time_taken = 0, 
	$sum_time_taken = 0;
	//step 3-4. initialize # of times tested, an array of size 10 (test 10 times to get the average time_taken)
	for($s = 0; $s < 10; $s++){ // test 10 times, and compute the average time_taken
		//step 3-5. each time, initialize the time_taken to be 0
		$time_taken = 0;
		$findMax_time_taken = 0;
		$insert_time_taken = 0;

		for($i=0; $i<$tries; $i++){
            //step 3-6:initialize _id: 
            // get the max pid (sort the array according to _id in descending order, then get the first one), 
            // then set the id of the inserted product to be max + 1
			$findMax_time_start = microtime(true); 
			$max_cursor = $collection->find(
				array(),
				array('_id' => 1)
				) -> sort(array('_id' => -1)) -> limit(1); 
			foreach($max_cursor as $max_doc){
				$max = $max_doc['_id'];
			}
			$findMax_time_end = microtime(true); 
	        //echo "i=".$i.": "."findMax_time_taken:".($findMax_time_end - $findMax_time_start)."<br>";
			$findMax_time_taken += ($findMax_time_end - $findMax_time_start)*1000; 

            //step 3-7:initialize docoment for insert: 
			$document = array(
				"_id"=> ++$max,
				"name"=> $name,
				"category"=> $category,
				"demanding_price"=> $demanding_price,
				"image"=> $image,
				"seller_id"=> $seller_id,
				"product_status"=>"forSale",
				"buyerlist"=>$buyerlist
				);

            //step 4: test insert
			$insert_time_start = microtime(true); 
			$collection->insert($document);
			$insert_time_end = microtime(true);
	        //echo "i=".$i.": "."insert_time_taken:".($insert_time_end - $insert_time_start)."<br>";
			$insert_time_taken += ($insert_time_end - $insert_time_start)*1000; 
		}
		$time_taken = $findMax_time_taken + $insert_time_taken;
		echo "the ".$s."th "."time".": "."time taken: ".$time_taken." ns"."<br>";
		$sum_time_taken += $time_taken;

        //step 5: recover the database to original state: delete insertion
		$collection->remove( array('_id'=> array('$gt'=>1500 ))); 
	}
	$av_time_taken = $sum_time_taken/10;	
	echo "the average time taken: ".$av_time_taken." ns"."<br>";
}
?>