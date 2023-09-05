<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


// Expose taxonomy labels
// https://github.com/wp-graphql/wp-graphql/issues/1304

add_filter( 'graphql_allowed_fields_on_restricted_type', function( $fields, $model_name, $data, $visibility, $owner, $current_user ) {
	if ( 'PostTypeObject' === $model_name || 'TermObject' === $model_name ) {
		$fields[] = 'label';
	}
	return $fields;
}, 10, 6 );



// https://github.com/wp-graphql/wp-graphql/issues/1255

add_action( 'graphql_register_types', function() {
	register_graphql_field( 'termNode', 'termOrder',[
		'type' => 'Int',
		'description' => __( 'Get the term order', 'text-domain' ),
		'resolve' => function( $term ) {
			$data = get_term($term->term_id);
			return isset($data->term_order) && !empty($data->term_order) ? (Int) $data->term_order : 0;
		},
	] );
} );


// LANGUAGE SWITCHER

add_action('graphql_register_types', function () {
	register_graphql_object_type('Translation', [
		'fields' => [
			'siteId' => [
				'type' => 'Integer',
			],
			'siteName' => [
				'type' => 'String',
			],
			'postId' => [
				'type' => 'Integer',
			],
			'postSlug' => [
				'type' => 'String',
			],
			'postUrl' => [
				'type' => 'String',
			],
		]
	]);

	register_graphql_field('Page', 'translations', [
		'type' => ['list_of' => 'Translation'],
		'resolve' => function ($post) {
			$blog = get_current_blog_id();
			$translations = \Inpsyde\MultilingualPress\translationIds($post->ID, 'Post', $blog);
			if ($translations) {
				foreach ($translations as $siteId => $postId) {
					switch_to_blog($siteId);
					$slug = get_post_field( 'post_name', $postId );
					$blogDetails = get_blog_details( array( 'blog_id' => $siteId ) );
					$elements[] = array(
						"siteId" => $siteId,
						"siteName" => $blogDetails->siteurl,
						"postId" => $postId,
						"postSlug" => $slug,
						"postUrl" => get_blog_permalink($siteId, $postId),
					);
					restore_current_blog();
				}
			}
			return $elements;
		}
	]);

	register_graphql_field('Product', 'translations', [
		'type' => ['list_of' => 'Translation'],
		'resolve' => function ($post) {
			$blog = get_current_blog_id();
			$translations = \Inpsyde\MultilingualPress\translationIds($post->ID, 'Post', $blog);
			if ($translations) {
				foreach ($translations as $siteId => $postId) {
					switch_to_blog($siteId);
					$slug = get_post_field( 'post_name', $postId );
					$blogDetails = get_blog_details( array( 'blog_id' => $siteId ) );
					$elements[] = array(
						"siteId" => $siteId,
						"siteName" => $blogDetails->siteurl,
						"postId" => $postId,
						"postSlug" => $slug,
						"postUrl" => get_blog_permalink($siteId, $postId),
					);
					restore_current_blog();
				}
			}
			return $elements;
		}
	]);

	register_graphql_field('Application', 'translations', [
			'type' => ['list_of' => 'Translation'],
			'resolve' => function ($post) {
				$blog = get_current_blog_id();
				$translations = \Inpsyde\MultilingualPress\translationIds($post->ID, 'Post', $blog);
				if ($translations) {
					foreach ($translations as $siteId => $postId) {
						switch_to_blog($siteId);
						$slug = get_post_field( 'post_name', $postId );
						$blogDetails = get_blog_details( array( 'blog_id' => $siteId ) );
						$elements[] = array(
							"siteId" => $siteId,
							"siteName" => $blogDetails->siteurl,
							"postId" => $postId,
							"postSlug" => $slug,
							"postUrl" => get_blog_permalink($siteId, $postId),
						);
						restore_current_blog();
					}
				}
				return $elements;
			}
		]);

	register_graphql_field('Post', 'translations', [
		'type' => ['list_of' => 'Translation'],
		'resolve' => function ($post) {
			$blog = get_current_blog_id();
			$translations = \Inpsyde\MultilingualPress\translationIds($post->ID, 'Post', $blog);
			if ($translations) {
				foreach ($translations as $siteId => $postId) {
					switch_to_blog($siteId);
					$slug = get_post_field( 'post_name', $postId );
					$blogDetails = get_blog_details( array( 'blog_id' => $siteId ) );
					$elements[] = array(
						"siteId" => $siteId,
						"siteName" => $blogDetails->siteurl,
						"postId" => $postId,
						"postSlug" => $slug,
						"postUrl" => get_blog_permalink($siteId, $postId),
					);
					restore_current_blog();
				}
			}
			return $elements;
		}
	]);

	register_graphql_field('ProductCategory', 'translations', [
		'type' => ['list_of' => 'Translation'],
		'resolve' => function ($post) {
			$blog = get_current_blog_id();
			$translations = \Inpsyde\MultilingualPress\translationIds($post->databaseId, 'Term', $blog);
			if (count($translations)) {
				foreach ($translations as $siteId => $postId) {
					switch_to_blog($siteId);
					$blogDetails = get_blog_details( array( 'blog_id' => $siteId ) );
					$termObject = get_term( $postId );
					$elements[] = array(
						"siteId" => $siteId,
						"siteName" => $blogDetails->siteurl,
						"postId" => $postId,
						"postSlug" => $termObject->slug,
						"postUrl" => get_term_link($postId),
					);
					restore_current_blog();
				}
			}
			return $elements;
		}
	]);
});
