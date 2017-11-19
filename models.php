<?php

class Webmention {
	public $data = null;
	public $sourceUrl = null;
	public $targetUrl = null;
	public $mentionId = null;

	public function __construct($mention) {
		$this->data = $mention;
		$prop = $mention['wm-property'];
		if ($prop == 'rsvp') {
			$prop = "in-reply-to";
		}
		$this->targetUrl = $mention[$prop];
		$this->sourceUrl = $mention['url'];
		$this->mentionId = hash('sha256', $this->sourceUrl . $this->targetUrl);
	}

}

class WebmentionStore {
	public function add_mention($mention) {
		$file_path = APP_DATA_DIR . DIRECTORY_SEPARATOR . $mention->mentionId . ".json";
		file_put_contents(
			$file_path, 
			json_encode($mention->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		);
	}
}

class WebmentionIndex {

	/**
 	* Array of URL path => [ mentionId, mentionId, ... ]
	* TODO: make sortable by sub-indexing on wm-received time.
	*/
	private $_index = array();

	public function __construct() {
		$index_path = APP_INDEX_PATH;
		if (file_exists($index_path)) {
			$this->_index = json_decode(file_get_contents($index_path), true);
		}
	}

	public function add_mention($mention) {
		$path = parse_url($mention->targetUrl, PHP_URL_PATH);
		$mention_ids = array_key_exists($path, $this->_index)
				 ? $this->_index[$path] : [];
		if (! in_array($mention->mentionId, $mention_ids) ) {
			$mention_ids []= $mention->mentionId;
			$this->_index[$path] = $mention_ids;
		}
	}

	public function save() {
		$index_path = APP_INDEX_PATH;
		file_put_contents(
			$index_path,
			json_encode($this->_index, JSON_UNESCAPED_SLASHES)
		);
	}
}
