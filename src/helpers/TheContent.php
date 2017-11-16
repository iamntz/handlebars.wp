<?php
/**
 * WP Content Parser Filter
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\helpers;

class TheContent implements \Handlebars\Helper
{
	public function execute(\Handlebars\Template $template, \Handlebars\Context $context, $args, $source)
	{
		$parsed_args = $template->parseArguments($args);
		$namedArgs = $template->parseNamedArguments($args);

		$attrs = array_map([$context, 'get'], $parsed_args);

		$content = apply_filters('the_content', $attrs[0]);
		return str_replace(']]>', ']]&gt;', $content);
	}
}
