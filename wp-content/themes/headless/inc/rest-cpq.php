<?php
function gefran_cpq_endpoint( $request_data ) {
    // Recupera i parametri dalla richiesta
    $language = $request_data['language'];
    $material_code = $request_data['material_code'];

    // Inizializza un array vuoto per i risultati
    $results = array();

    // Ottieni l'elenco dei blog multisito
    $blogs = get_sites();

    // Itera su ciascun blog
    foreach ( $blogs as $blog ) {
        // Cambia il contesto del blog corrente
        switch_to_blog( $blog->blog_id );
        $locale = get_blog_option( $blog->blog_id, 'WPLANG' );

        // Esegui la query per cercare il prodotto basato su WPLANG e original_id
        $product_query = new WP_Query( array(
            'post_type' => 'product',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'original_id', // Sostituisci con la chiave ACF corretta
                    'value' => $material_code,
                    'compare' => '='
                )
            ),
            'lang' => $language // Filtra in base alla lingua del blog
        ) );

        // Verifica se è stata trovata una corrispondenza
        if ( $product_query->have_posts() ) {
            while ( $product_query->have_posts() ) {
                $product_query->the_post();

                // Ottieni l'URL e il titolo del prodotto
                $product_url = get_permalink();
                $product_title = get_the_title();

                $modified_url = modifyProductUrl($product_url, $locale);
                $translated_product_slug_url = translateProductSlug($modified_url, $locale);

                // Solo la lingua richiesta
                if($locale === $language){
                  // Aggiungi l'URL e il titolo all'array dei risultati
                  $results[] = array(
                      'url' => $translated_product_slug_url,
                      'url_wp' => $product_url,
                      'title' => $product_title,
                      'lang' => $locale
                  );
                }
            }
        }

        // Ripristina il contesto del blog principale
        restore_current_blog();
    }

    // Restituisci l'array dei risultati come JSON
    return $results;
}


// Funzione helper per sostituire l'URL
function modifyProductUrl($originalUrl, $locale) {
    $backendMap = [
        'gefran-admin.dvl' => 'gefran',
        'gefranstg.kinsta.cloud' => 'stg--gefran',
        'gefran.kinsta.cloud' => 'gefran'
    ];

    $langMapStaging = [
        'it_IT' => '-it',
        'de_DE' => '-de',
        'fr_FR' => '-fr',
        'es_ES' => '-es',
        'pt_PT' => '-pt',
        'zh_CN' => '-cn'
    ];

    $langMapProduction = [
        'it_IT' => '.it',
        'de_DE' => '.de',
        'fr_FR' => '.fr',
        'es_ES' => '.es',
        'pt_PT' => '.com.br',
        'zh_CN' => '.cn',
        'en_US' => '.com'
    ];

    $parsedUrl = parse_url($originalUrl);
    $host = $parsedUrl['host'];
    $path = preg_replace('!^/[\w_]+!', '', $parsedUrl['path']);  // Remove language part from the URL

    $newBase = isset($backendMap[$host]) ? $backendMap[$host] : '';

    if ($newBase === 'stg--gefran') {
        $newLang = isset($langMapStaging[$locale]) ? $langMapStaging[$locale] : '';
        return "https://{$newBase}{$newLang}.netlify.app{$path}";
    } elseif ($newBase === 'gefran') {
        $newLang = isset($langMapProduction[$locale]) ? $langMapProduction[$locale] : '';
        return "https://www.{$newBase}{$newLang}{$path}";
    }

    return $originalUrl;
}


function translateProductSlug($originalUrl, $locale) {
    $parsedUrl = parse_url($originalUrl);
    $path = $parsedUrl['path'];

    $slug_map_product = [
        'en_US' => 'products',
        'it_IT' => 'prodotti',
        'fr_FR' => 'produits',
        'de_DE' => 'produkte',
        'es_ES' => 'productos',
        'pt_PT' => 'produtos',
        'zh_CN' => '产品',
        // add more languages here
    ];

    // Default to 'products' if language not found
    $new_slug = isset($slug_map_product[$locale]) ? $slug_map_product[$locale] : 'products';

    // Replace old slug with new slug based on language
    $path = preg_replace('#/products/#', "/$new_slug/", $path, 1);

    // Rebuild the URL with the new path
    $newUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$path}";
    return $newUrl;
}



// Registra l'endpoint REST personalizzato per CPQ di Gefran
function register_gefran_cpq_rest_route() {
    register_rest_route( 'gefran-cpq/v1', '/get-info/', array(
        'methods' => 'GET',
        'callback' => 'gefran_cpq_endpoint',
    ) );
}
add_action( 'rest_api_init', 'register_gefran_cpq_rest_route' );
