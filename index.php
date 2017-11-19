<?php require_once('models.php'); ?>
<?php require_once('publish.php'); ?>
<?php require_once('passthrough.php'); ?>
<?php require_once('config.php'); ?>
<?php

$entityBody = file_get_contents("php://input");
if (! empty($entityBody)){
	$json = json_decode($entityBody, true);
	if ($json['secret'] && ($json['secret'] == WMIO_WEBHOOK_TOKEN)) {
		echo "Processing a mention!\n";
		$post = $json['post'];
		$mention = new Webmention($post);
		$ws = new WebmentionStore();
		$ws->add_mention($mention);
		$wi = new WebmentionIndex();
		$wi->add_mention($mention);
		$wi->save();
		publish();
		passthrough_webmention($entityBody);
		echo "OK\n";
	}
}
?>
<?php if (empty($entityBody)): ?>
	<html><body>
	Hi. I'm a receiver for mentions from webmention.io!
	</body></html>
<?php endif ?>
