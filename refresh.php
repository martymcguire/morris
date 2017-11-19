<?php require_once('models.php'); ?>
<?php require_once('config.php'); ?>
<?php

function get_mentions_page($page = 0) {
	$params = array(
		'token' => WMIO_API_TOKEN,
		'domain' => WMIO_DOMAIN,
		'per-page' => 100,
		'page' => $page
	);
	$path = "https://webmention.io/api/mentions.jf2?" . http_build_query($params);
	$raw = file_get_contents($path);
	return json_decode($raw, true);
}

$ws = new WebmentionStore();
$wi = new WebmentionIndex();
$done = false;
$page = 0;
while( ! $done ) {
	$jf2 = get_mentions_page($page++);
	$mentions = $jf2['children'];
	foreach ($mentions as $mention_data) {
		$mention = new Webmention($mention_data);
		$ws->add_mention($mention);
		$wi->add_mention($mention);
	}
	echo "Processed " . count($mentions) . " mentions.\n";
	$done = empty($mentions);
}
$wi->save();
