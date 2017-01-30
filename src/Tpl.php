<?php
/**
 * Views Wrapper
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP;

use \Handlebars\Handlebars;
use \Handlebars\Loader\FilesystemLoader;

/**
 * Template system wrapper
 */
class Tpl {

	/**
	 * Get the Handlebars template engine
	 *
	 * @method get_engine
	 *
	 * @param array $options override the default template options.
	 *
	 * @return Handlebars
	 */
	public static function get_engine( $options = [] ) {
		/**
		 * Allows you to override the partials subdirectory.
		 *
		 * However, this subdirectory should be inside of the views folder.
		 *
		 * @var string
		 */
		$partials_path = apply_filters( 'iamntz/templates/partials_path', 'partials' );

		$options = array_merge( self::get_template_options(), $options );

		$engine = new Handlebars();

		$engine->setLoader(new FilesystemLoader( self::get_template_paths(), $options ));
		$engine->setPartialsLoader(new FilesystemLoader( self::get_template_paths( $partials_path ), $options ));

		$engine->addHelper( 'sanitize', new helpers\Sanitization() );
		$engine->addHelper( 'esc_attr', new helpers\Sanitization( 'esc_attr' ) );
		$engine->addHelper( 'esc_textarea', new helpers\Sanitization( 'esc_textarea' ) );
		$engine->addHelper( 'sanitize_text_field', new helpers\Sanitization( 'sanitize_text_field' ) );
		$engine->addHelper( 'esc_url', new helpers\Sanitization( 'esc_url' ) );

		$engine->addHelper( 'checked_attr', new helpers\Checked );
		$engine->addHelper( 'selected_attr', new helpers\Selected );

		return apply_filters( 'iamntz/templates/engine', $engine );
	}


	/**
	 * Gets the directory paths where to look for templates
	 *
	 * @method get_paths
	 *
	 * @return array the directory paths
	 */
	public static function get_paths() {
		$paths = [
			get_template_directory(),
			get_stylesheet_directory(),
		];

		$paths = array_reverse( apply_filters( 'iamntz/template/directories', $paths ) );
		$paths = array_unique( $paths, SORT_REGULAR );

		return array_map( 'trailingslashit', $paths );
	}


	/**
	 * Filter template directories and returns only paths that actually exists.
	 *
	 * @method get_template_paths
	 *
	 * @param  string $subdir additional subdirectory to search into.
	 *
	 * @return array             filtered array.
	 */
	public static function get_template_paths( $subdir = '' ) {
		$paths = array_map(function( $path ) use ( $subdir ) {
			return file_exists( $path . "/views/{$subdir}" )  ? $path . "views/{$subdir}" : null;
		}, self::get_paths());

		return array_filter( $paths );
	}

	/**
	 * Get Template Engine options
	 *
	 * @method get_template_options
	 *
	 * @return array
	 */
	public static function get_template_options() {
		$options = [
			'extension' => '.hbs',
		];

		return apply_filters( 'iamntz/template/options', $options );
	}

	/**
	 * Gets the compiled template
	 *
	 * @method get
	 *
	 * @param  string                $template the template file name without extension.
	 * @param  array                 $content  the data to be passed to the template.
	 * @param array                 $options override the default template options.
	 * @param  \Handlebars\Tokenizer $tokenizer override the default tokenizer.
	 *
	 * @return string compiled template
	 */
	public static function get( $template, $content = [], $options = [], $tokenizer = null ) {
		$content = apply_filters( 'iamntz/template/content', $content );
		$content['i18n'] = apply_filters( 'iamntz/template/i18n_strings', [] );

		$content['home_url'] = esc_url( home_url( '/' ) );
		$content['theme_uri'] = get_stylesheet_directory_uri();
		$content['parent_theme_uri'] = get_template_directory_uri();

		$content['is_child_theme?'] = is_child_theme();
		$content['is_home?'] = is_front_page() && is_home();
		$content['is_admin?'] = is_admin();

		$engine = self::get_engine( $options );

		if ( ! is_null( $tokenizer ) ) {
			$engine->setTokenizer( $tokenizer );
		}

		return $engine->render( $template, self::get_content_with_id( $content ) );
	}

	/**
	 * Echoes the template
	 *
	 * @method show
	 *
	 * @param  string                $template the template file.
	 * @param  array                 $content  the data to be passed.
	 * @param  array                 $options override the default template options.
	 * @param  \Handlebars\Tokenizer $tokenizer override the default tokenizer.
	 */
	public static function show( $template, $content = [], $options = [], $tokenizer = null ) {
		// @codingStandardsIgnoreStart
		echo self::get( $template, $content, $options, $tokenizer );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Automatically add an ID to template data in order to always have an ID available in HTML
	 *
	 * @method get_content_with_id
	 *
	 * @param  array $content the content data.
	 *
	 * @return array the content data with IDs added.
	 */
	public static function get_content_with_id( $content ) {
		$hash_algorithm = apply_filters( 'iamntz/template/hash', 'crc32b' );
		if ( ! isset( $content['_id'] ) ) {
			$content['_id'] = 'id-' . hash( $hash_algorithm, serialize( $content ) );
		}
		if ( ! isset( $content['_uniqid'] ) ) {
			$content['_uniqid'] = chr( rand( 64, 90 ) ) . str_replace( '.', '-', uniqid( '_', true ) );
		}

		return $content;
	}
}
