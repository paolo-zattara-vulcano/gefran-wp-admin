<?php

use function Inpsyde\MultilingualPress\siteLocale;

function gfrnGetProductsWithCats($request){

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


	if ($notIn){

		$notPreviouslyReturned = [];

		foreach ($productsArr as $item) {
			if (!in_array($item->id,$notIn)){
				$notPreviouslyReturned[] = $item;
			}
		}

		$productsArr = $notPreviouslyReturned;

	}

	$products_response = array();
	$productFields = array();

	foreach ($productsArr as $product){
		$prodID = $product->ID;
		$productFields = _getProductFields($prodID);
		$isEndOfProduction = false;
		if (empty($productFields['parent_cat']) && (get_field('badge', $prodID) == 'eop')){
			$productFields['parent_cat'] = array('name' => 'End of Production');
			$isEndOfProduction = true;
		}

		$translations = \Inpsyde\MultilingualPress\translationIds($prodID, 'Post', 1);

        try {
        	$prod_to_add = array(
        		"product" => $productFields,
        		"translations" => []
        	);

            if (count($translations)) {
                foreach ($translations as $siteId => $postId) {
                    //error_log('siteId:' . $siteId . ' postId:' . $postId);

                    switch_to_blog($siteId);

					// Get the locale for the specific site
					$lang = siteLocale( $siteId );

					$tx_productFields = _getProductFields($postId);
					if ($isEndOfProduction){
						$tx_productFields['parent_cat'] = array(
							'name' => 'End of Production'
						);
					}

					$prod_to_add['translations'][$lang] = $tx_productFields;
					restore_current_blog();
                }
            }

			array_push($products_response, $prod_to_add);

        } catch (Exception $e) {
            error_log($e->getMessage());
            die('Error: ' . $e->getMessage());
        }
	}

	$response = new WP_REST_Response($products_response, 200);

	$response->set_headers([ 'Cache-Control' => 'must-revalidate, no-cache, no-store, private' ]);

	return $response;

}


function _getProductFields($prodID){
	$siteUrl = home_url();
	$prodTitle = get_the_title($prodID);
	$prodOriginalID = get_post_meta($prodID,'original_id',true);
	$configuratore = get_field('configurator_link', $prodID);
	$source_cats = get_the_terms($prodID, 'product_category');
	$sub_title = " ";
	if(get_field('sub_title',$prodID)){
		$sub_title =  get_field('sub_title',$prodID);
	}
	$overview = get_field('overview',$prodID);
	$description = get_field('description_accordion_tab',$prodID);
	$badge = get_field('badge', $prodID);
	$permalink = str_replace(home_url(), 'https://www.gefran.com', get_permalink($prodID)); 
	$image = get_the_post_thumbnail_url($prodID,"full");
	$parent_cat = array();
	if(! empty($source_cats) && ! is_wp_error($source_cats) ){
		foreach($source_cats as $cat ){
			// manual hardcoding for get automation-platform prod - instead of other tax
			// automation-platform must be always for all blog term_id = 4158 #### THIS IS A POTENZIAL BUG
			if($cat->parent == 0 && $cat->term_id == 4158 ){
				$parent_cat = $cat;
                break;
			}
            if($cat->parent == 0 ){
				$parent_cat = $cat;
			}
		}
	}

	$productFields = array(
		"ID" => $prodID,
		"title" => $prodTitle,
		"original_id" => $prodOriginalID,
		"configuratore"=> $configuratore,
		"sub_title"=> $sub_title,
		"description" => wp_strip_all_tags($description),
		"overview" => wp_strip_all_tags($overview),
		"image" => $image,
		"badge" => $badge,
		"permalink" => $permalink,
		"parent_cat" => $parent_cat,
	);

	return $productFields;
}

/*
	Get Products
	https://gefran.kinsta.cloud/wp-json/gfrn/v1/products/
*/
add_action( 'rest_api_init', function () {
	register_rest_route( 'gfrn/v1', '/products-with-cats/', array(
		'methods' => 'GET',
		'callback' => 'gfrnGetProductsWithCats',
		'permission_callback' => '__return_true',
	) );
});
