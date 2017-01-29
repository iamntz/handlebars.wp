<?php
/**
 * WP Selected Helper
 *
 * @package iamntz Handlebars Helpers
 */

namespace iamntz\handlebars\helpers;

/**
 * Selected
 */
class Selected implements \Handlebars\Helper {
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
		$value = $context->get( $parsed_args[0] );
		return  $context->get( $parsed_args[0] ) === $context->get( $parsed_args[1] ) ? ' selected="selected"' : '';
	}
}
