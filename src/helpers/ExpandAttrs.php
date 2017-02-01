<?php
/**
 * WP Expand Attrs Helper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\helpers;

/**
 * ExpandAttrs
 */
class ExpandAttrs implements \Handlebars\Helper {
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
		$raw_attrs = $context->get( $parsed_args[0] );

		if ( empty( $raw_attrs ) ) {
			return '';
		}

		$attrs = array_map(function( $attr, $key ) {
			if ( is_int( $key ) ) {
				return $attr;
			}

			return sprintf( '%s="%s"', $key, esc_attr( $attr ) );
		}, $raw_attrs, array_keys( $raw_attrs ));

		return implode( ' ', $attrs );
	}
}
