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

		$content = $attrs[0];

		if (empty($namedArgs['no-texturize'])) {
			$content = wptexturize($content);
		}

		if (empty($namedArgs['no-smiles'])) {
			$content = convert_smilies($content);
		}

		if (empty($namedArgs['no-autop'])) {
			$content = wpautop($content);
		}

		if (empty($namedArgs['no-shortcodes'])) {
			$content = shortcode_unautop($content);
			$content = do_shortcode($content);
		}

		if (empty($namedArgs['no-prepend-attachment'])) {
			$content = prepend_attachment($content);
		}

		if (empty($namedArgs['no-responsive-images'])) {
			$content = wp_filter_content_tags($content);
		}

		$content = apply_filters('iamntz/handlebars/the_content', $content);

		return str_replace(']]>', ']]&gt;', $content);
	}
}
