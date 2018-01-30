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

		$content = wptexturize($attrs[0]);
		// $content = convert_smilies($content);
		$content = wpautop($content);
		$content = shortcode_unautop($content);
		// $content = prepend_attachment($content);
		// $content = wp_make_content_images_responsive($content);

		$content = apply_filters('iamntz/handlebars/the_content', $content);
		return str_replace(']]>', ']]&gt;', $content);
	}
}
