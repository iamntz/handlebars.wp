<?php
/**
 * Template strings cacher
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\utils;

class TplStrings
{
	private static $content;

	static public function parseContent( $content, $namespace ) {
		$content = apply_filters( "{$namespace}/template/content", $content );

		if (self::$content ?? null) {
			return $content + self::$content;
		}

		$content = apply_filters( "{$namespace}/template/content-cached", $content );

		$i18n = $content['i18n'] ?? [];
		$content['i18n'] = apply_filters( "{$namespace}/template/i18n_strings", $i18n );

		$content['home_url'] = esc_url( home_url( '/' ) );
		$content['theme_uri'] = get_stylesheet_directory_uri();
		$content['parent_theme_uri'] = get_template_directory_uri();

		$content['is_child_theme?'] = is_child_theme();
		$content['is_home?'] = is_front_page() && is_home();
		$content['is_admin?'] = is_admin();
		$content['search_query'] = get_search_query();

		self::$content = $content;

		return $content;
	}
}
