<?php

/**
* Passes through a received webmention. For example,
* to send it to a pre-existing notification endpoint.
*/
function passthrough_webmention($data) {
	$opts = array ( 'http' => array(
		'method' => 'POST',
		'header' => "Content-Type: application/json\r\n",
		'content' => $data
	));
	if( defined( 'APP_PASSTHROUGH_URLS' ) ) {
		foreach( APP_PASSTHROUGH_URLS as $url ) {
			file_get_contents($url, false, stream_context_create($opts));
		}
	}
}
