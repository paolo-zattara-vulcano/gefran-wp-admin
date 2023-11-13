# Fix docugefran cors problems

- wp-includes/rest-api.php -> 743 comment //header( 'Access-Control-Allow-Origin: ' . $origin );
- wp-includes/http.php -> if ( is_allowed_http_origin( $origin ) ) {
        comment only -> header( 'Access-Control-Allow-Origin: ' . $origin );
