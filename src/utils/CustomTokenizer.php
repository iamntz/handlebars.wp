<?php
/**
 * Custom Handlebars Tokenizer to allow custom template tags
 */

namespace iamntz\handlebarsWP\utils;

/**
 * Custom Handlebars Tokenizer
 */
class CustomTokenizer extends \Handlebars\Tokenizer
{
	protected function reset()
	{
		parent::reset();
		$this->otag = '<%';
		$this->ctag = '%>';
	}
}