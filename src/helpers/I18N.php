<?php

/**
 * WP i18n helper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\helpers;

class I18N implements \Handlebars\Helper
{

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
	public function execute(\Handlebars\Template $template, \Handlebars\Context $context, $args, $source)
	{
		$parsed_args = $template->parseArguments($args);
		$namedArgs = $template->parseNamedArguments($args);

		$attrs = array_map([$context, 'get'], $parsed_args);

		if (count($attrs) === 1) {
			return $attrs[0];
		}

		return call_user_func_array('sprintf', $attrs);
	}
}
