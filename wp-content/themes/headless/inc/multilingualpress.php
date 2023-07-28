<?php
/**
 * Multilingualpress Helper functions
 *
 * @package overstrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// https://multilingualpress.org/docs/create-custom-language-switcher-multilingualpress/

function multilingualpress_get_translations()
{
   $args = \Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs::forContext(new \Inpsyde\MultilingualPress\Framework\WordpressContext())
      ->forSiteId(get_current_blog_id())
      ->includeBase();

   $translations = \Inpsyde\MultilingualPress\resolve(
      \Inpsyde\MultilingualPress\Framework\Api\Translations::class
   )->searchTranslations($args);

   return $translations;
}


// ---------------- Example:

// $translations = multilingualpress_get_translations();
//
// foreach ($translations as $translation) {
//    $language = $translation->language();
//    $language_iso_name = $language->isoName();
//    $language_locale = $language->locale();
//    $url = $translation->remoteUrl();
// }
