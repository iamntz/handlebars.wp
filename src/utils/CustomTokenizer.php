<?php
/**
 * Custom Handlebars Tokenizer to allow custom template tags
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\utils;

/**
 * Custom Handlebars Tokenizer
 */
class CustomTokenizer extends \Handlebars\Tokenizer {
	/**
	 * Reset open/close tags
	 *
	 * @method reset
	 */
	protected function reset() {
		parent::reset();
		$this->otag = '<%';
		$this->ctag = '%>';
	}
}
