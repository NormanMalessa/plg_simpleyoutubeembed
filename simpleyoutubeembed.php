<?php
/**
 * @author Norman Malessa <mail@norman-malessa.de>
 * @copyright 2018 Norman Malessa <mail@norman-malessa.de>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License, see LICENSE
 */

class plgContentSimpleYoutubeEmbed extends JPlugin {

	/**
	 * @param string $context
	 * @param object $article
	 * @param object $params
	 * @param int $page
	 *
	 * @return bool
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0) {
		$searches = $this->getRegexes();

		foreach ($searches as $regex) {
			if (preg_match_all($regex, $article->text, $matches)) {
				foreach ($matches[0] as $k => $source) {
					// Parse the URL
					$urlHash   = @$matches[2][$k];
					$videoCode = $matches[1][$k];
					$embedCode = $this->youtubeCodeEmbed($videoCode, $urlHash);

					$article->text = str_replace($source, $embedCode, $article->text);
				}
			}
		}

		return true;
	}

	/**
	 * Return the regular expressions to search for in the text
	 *
	 * @return array
	 */
	protected function getRegexes() {
		return [
			'#https?://(?:www\.)?youtube.com/watch\?v=([a-zA-Z0-9-_&;=]+)(\#[a-zA-Z0-9-_&;=]*)?#'
		];
	}

	/**
	 * @param string $videoCode
	 * @param string|null $urlHash
	 *
	 * @return string
	 */
	protected function youtubeCodeEmbed($videoCode, $urlHash = null) {
		$width	   = $this->params->get('width', 425);
		$height    = $this->params->get('height', 344);
		$query     = explode('&', htmlspecialchars_decode($videoCode));
		$videoCode = array_shift($query);

		return '<iframe id="youtube_' . $videoCode . '" frameborder="0" allowfullscreen'
		. ' width="' . $width . '" height="' . $height . '"'
		. ' src="' . $this->getUrl($videoCode, $query, $urlHash) . '"></iframe>';
	}

	/**
	 * @param array $query
	 *
	 * @return array
	 */
	protected static function buildUrlQuery($query) {
		$queryAssoc = array();

		if (!empty($query)) {
			foreach ($query as $key => $value) {
				if (is_numeric($key)) {
					$value = explode('=', $value);

					if (!isset($value[1])) {
						$queryAssoc[$value[0]] = 'true';
					} else {
						$queryAssoc[$value[0]] = $value[1];
					}
				}
			}
		}

		return $queryAssoc;
	}

	/**
	 * @param string   $videoCode
	 * @param array    $query
	 * @param string   $hash
	 *
	 * @return string
	 */
	protected static function getUrl($videoCode, $query = array(), $hash = null) {
		$url   = 'https://www.youtube.com/embed/' . $videoCode . '?wmode=transparent&rel=0&showinfo=0&modestbranding=1';
		$query = static::buildUrlQuery($query);

		if (!empty($query)) {
			$url .= '&' . http_build_query($query);
		}

		if (!empty($hash)) {
			$url .= $hash;
		}

		return $url;
	}
}
