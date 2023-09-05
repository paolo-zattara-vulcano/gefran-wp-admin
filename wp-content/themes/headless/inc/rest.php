<?php

function gfrnGetProducts($request){

	$debug = [];



	$products_args = array(
		"numberposts" => -1,
		"post_type" => "product",
		'post_status' => array('publish', 'draft'),
		'meta_query' => [],
		'orderby' => "title",
		'order' => "ASC"
	);

	$products = get_posts($products_args);

	// print_r($products);

	$debug[] = $products;

	$maxPosts = $request->get_param( 'maxposts' );
	$max = $maxPosts ? $maxPosts : 20;

	$notInArr = $request->get_param( 'not_in' );
	$notIn = $notInArr ? explode(",",$notInArr) : [];

	$productsArr = $products;

	// count total before removing returned items
	$totalCount = count($productsArr);

	// echo '$totalCount:';
	// print_r($totalCount);
	// echo "\n";



	// remove items that have been previously returned

	if ($notIn){

		$notPreviouslyReturned = [];

		foreach ($productsArr as $item) {
			if (!in_array($item->id,$notIn)){
				$notPreviouslyReturned[] = $item;
			}
		}

		$productsArr = $notPreviouslyReturned;

	}

	$productsFields = [];

	foreach ($productsArr as $product){

		// print_r($product);

		$prodID = $product->ID;
		$prodTitle = get_the_title($prodID);
		$prodOriginalID = get_post_meta($prodID,'original_id',true);

		/*
		echo '$prodID:';
		print_r($prodID);
		echo "\n";
		echo '$prodTitle:';
		print_r($prodTitle );
		echo "\n";
		echo '$prodOriginalID:';
		print_r($prodOriginalID);
		echo "\n";

		echo '-----------';
		echo "\n";
		*/

		if ($prodID && $prodTitle && $prodOriginalID){
			$productsFields[] = [
				"ID" => $prodID,
				"title" => $prodTitle,
				"original_id" => $prodOriginalID,
			];
		} else {

			// echo 'missing: ';
			// print_r($product);
			// echo "\n";

		}
	}

	/*
	$output = array(
		"items" => $productsArr,
		"total" => $totalCount,
		"debug" => $debug,
	);
	*/

	// echo '$productsFields:';
	// print_r(count($productsFields));
	// echo "\n";


	$response = new WP_REST_Response($productsFields, 200);

	$response->set_headers([ 'Cache-Control' => 'must-revalidate, no-cache, no-store, private' ]);

	return $response;

}


/*
	Get Products
	https://gefran.kinsta.cloud/wp-json/gfrn/v1/products/
*/
add_action( 'rest_api_init', function () {
	register_rest_route( 'gfrn/v1', '/products/', array(
		'methods' => 'GET',
		'callback' => 'gfrnGetProducts',
		'permission_callback' => '__return_true',
	) );
});
