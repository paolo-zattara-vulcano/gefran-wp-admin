<?php

namespace ACP\Service;

use AC\Registerable;

class Banner implements Registerable {

	public function register() {
		add_filter( 'ac/show_banner', '__return_false' );
	}

}