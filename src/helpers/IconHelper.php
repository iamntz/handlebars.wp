<?php

/**
 * WP Svg Icon Helper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\helpers;

class IconHelper implements \Handlebars\Helper
{

	public function __construct($path)
	{
		if (!is_array($path)) {
			$path = [$path];
		}

		$this->path = array_map('trailingslashit', $path);
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
	public function execute(\Handlebars\Template $template, \Handlebars\Context $context, $args, $source)
	{
		$parsed_args = $template->parseArguments($args);
		$namedArgs = $template->parseNamedArguments($args);

		$attrs = array_map([$context, 'get'], $parsed_args);

		$icon = $this->getIconMarkup($attrs[0], $attrs[1] ?? '');

		if (!$icon) {
			return $attrs[0];
		}

		return $icon;
	}

	private function getIconMarkup($iconName, $className)
	{
		foreach ($this->path as $path) {
			$iconFile = $path . "/{$iconName}.svg";
			if (file_exists($iconFile)) {
				return sprintf('<span class="svg-icon icon-%s %s">%s</span>', $iconName, $className, file_get_contents($iconFile));
			}
		}
	}
}
