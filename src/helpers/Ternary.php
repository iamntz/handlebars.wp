<?php
/**
 * WP Selected Helper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\helpers;

/**
 * Ternary
 */
class Ternary implements \Handlebars\Helper {
	/**
	 * Handlebars Helper to be executed
	 *
	 * @method execute
	 *
	 * @param  \Handlebars\Template  $template the template.
	 * @param  \Handlebars\Context   $context  the context.
	 * @param  \Handlebars\Arguments $args     arguments.
	 * @param  string                $source    The source.
	 *
	 * @return string
	 */
	public function execute( \Handlebars\Template $template, \Handlebars\Context $context, $args, $source ) {
		$parsed_args = $template->parseArguments( $args );

		$values = array_map([$context, 'get'], $parsed_args);

		if (count($values) !== 3) {
			throw new \Exception("Exactly three arguments are required", 1);
		}

		return !empty($values[0]) ? $values[1] : $values[2];
	}
}
