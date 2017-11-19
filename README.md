# Morris

Morris is a service for storing [Webmentions](http://webmention.net) from
[Webmention.io](https://webmention.io/) in a way that static sites can easily
render them.

## Installing

* Clone this repo
* Copy `config.php.example` to `config.php` and enter values relevant to your site.
	* See below for configuration details.
* Run `refresh.php` to fetch all webmentions for your domain.
* Copy `publish.php.example` to `publish.php` and customize it so that it triggers
  a rebuild and republish of your site.
* Configure your webserver to make `index.php` available.
* Enter the URL to your `index.php` and your chosen `WMIO_WEBHOOK_TOKEN` in the
  **Web Hook** section of your [webmention.io settings page](https://webmention.io/settings).

## Configuration Options

* `WMIO_WEBHOOK_TOKEN` - a secret string chosen by you that webmention.io will
  include in webmentions in order to verify that they came from webmention.io
* `WMIO_API_TOKEN` - a secret provided by webmention.io that allows `refresh.php`
  to fetch all mentions for your domain. Accessible on your [webmention.io settings page](https://webmention.io/settings) in the **API Key** section.
* `WMIO_DOMAIN` - the domain for which to fetch mentions
* `APP_DATA_DIR` - the location where mention files should be stored. The directory must
  exist, and PHP must be able to write to this directory.
* `APP_INDEX_PATH` - the location of the file that will contain the index of mentions.
  PHP must be able to write to this file.
* `APP_PASSTHROUGH_URLS` - *(optional)* an array of URLs to forward received mentions,
  in case you have existing notification services set up.

## Serving Morris with Apache

Morris expects to be able to access its sibling PHP files in a single directory,
but you probably don't want to make `refresh.php` accessible to the world.

One way to do this with Apache is to set up a `ScriptAlias` directly to `index.php`:

```apache
ScriptAlias /wmio-receiver/ /path/to/morris/index.php
<Directory /path/to/morris>
	Options +ExecCGI
	Require all granted
</Directory>
```

## Using The Stored Mentions with Your Static Site Generator

Static site generators like [Jekyll](https://jekyllrb.com/) and
[Hugo](https://gohugo.io/) can access data files stored in JSON format.

### How Things are Stored

Each webmention is stored in `APP_DATA_DIR` in [jf2](https://indieweb.org/jf2)
format in its file.

The filename is of the form `KEY.json`, where `KEY` is the `SHA256` hash of the
`source` + `target` URLs of the webmention.

The index, stored at `APP_INDEX_PATH`, contains a JSON object of the form:

```json
{
	'/some/path/on/your/site.html`: [
		'SOMEKEY',
		'ANOTHERKEY',
		'YETANOTHERKEY',
		...
	],
	...
}
```

Here's an example of how to use these with a Hugo site.

* create a `data/webmention_io` folder in your static site
* configure Morris to store mentions at `data/webmention_io/mentions`
* configure Morris to store the index at `data/webmention_io/index.json`

(*Note: Hugo doesn't like symlinks, so you can either configure Morris to store
webmentions here directly, or create the directory in your Hugo installation and
configure Morris to write into a symlink'd folder*).

Now you can look up all mentions for a given Page:

```
{{ $mention_id := index $.Site.Data.webmention_io.index .URL }}
{{ $mentions := apply $mention_ids "index" $.Site.Data.webmention_io.mentions "." }}
{{ $mentions := sort $mentions "wm-received" }}
{{ range $mentions }}
	... display things! ...
{{ end }}
```

The content inside of a given mention looks something like:

```
{
    "type": "entry",
    "author": {
      "name": "Amy Guy",
      "photo": "http://webmention.io/avatar/rhiaro.co.uk/829d3f6e7083d7ee8bd7b20363da84d88ce5b4ce094f78fd1b27d8d3dc42560e.png",
      "url": "http://rhiaro.co.uk/about#me"
    },
    "url": "http://rhiaro.co.uk/2015/11/1446953889",
    "published": "2015-11-08T03:38:09+00:00",
    "name": "repost of http://aaronparecki.com/notes/2015/11/07/4/indiewebcamp",
    "repost-of": "http://aaronparecki.com/notes/2015/11/07/4/indiewebcamp",
    "wm-property": "repost-of"
    "wm-received": "2015-11-08T03:38:19+00:00",
}
```

## License

Copyright 2017 by [Marty McGuire](https://martymcgui.re/)

Licensed under the [MIT License](LICENSE).
