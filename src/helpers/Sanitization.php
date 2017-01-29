<?php
/**
 * WP sanitization helpers
 *
 * @package iamntz Handlebars Helpers
 */

namespace iamntz\handlebars\helpers;

/**
 * Sanitization
 */
class Sanitization implements \Handlebars\Helper {
	/**
	 * Constructor
	 *
	 * @method __construct
	 *
	 * @param  string $callback the sanitization callback.
	 */
	public function __construct( $callback = 'esc_attr' ) {
		$this->callback = $callback;
	}

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

		if ( ! empty( $parsed_args[1] ) && function_exists( $parsed_args[1] ) ) {
			return $parsed_args[1]( $value );
		}

		if ( function_exists( $this->callback ) ) {
			$callback = $this->callback;
			return $callback($value);
		}

		return $value;
	}
}
