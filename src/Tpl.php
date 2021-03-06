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
	 * Allows you to set a namespace for hooks
	 *
	 * @method get_namespace
	 *
	 * @return string
	 */
	public function get_namespace() {
		return 'iamntz';
	}

	/**
	 * Provides a folder where to look for SVG Icons
	 *
	 * @method get_icons_path
	 *
	 * @return string
	 */
	public function get_icons_path()
	{
		return get_template_directory() . '/icons';
	}

	/**
	 * Get the Handlebars template engine
	 *
	 * @method get_engine
	 *
	 * @param array $options override the default template options.
	 *
	 * @return Handlebars
	 */
	public function get_engine( $options = [] ) {
		/**
		 * Allows you to override the partials subdirectory.
		 *
		 * However, this subdirectory should be inside of the views folder.
		 *
		 * @var string
		 */
		$partials_path = apply_filters( $this->get_namespace() . '/templates/partials_path', 'partials' );

		$options = array_merge( $this->get_template_options(), $options );

		$engine = new Handlebars();

		$engine->setLoader( new FilesystemLoader( $this->get_template_paths(), $options ) );
		$engine->setPartialsLoader( new FilesystemLoader( $this->get_template_paths( $partials_path ), $options ) );

		$this->register_helpers($engine);

		return apply_filters( $this->get_namespace() . '/templates/engine', $engine );
	}

	protected function register_helpers($engine)
	{
		$engine->addHelper( '_ternary', new helpers\Ternary() );
		$engine->addHelper( '_sanitize', new helpers\Sanitization() );
		$engine->addHelper( '_esc_attr', new helpers\Sanitization( 'esc_attr' ) );
		$engine->addHelper( '_esc_textarea', new helpers\Sanitization( 'esc_textarea' ) );
		$engine->addHelper( '_sanitize_text_field', new helpers\Sanitization( 'sanitize_text_field' ) );
		$engine->addHelper( '_esc_url', new helpers\Sanitization( 'esc_url' ) );
		$engine->addHelper( '_urlencode', new helpers\Sanitization( 'urlencode' ) );
		$engine->addHelper( '_checked', new helpers\Checked );
		$engine->addHelper( '_selected', new helpers\Selected );
		$engine->addHelper( '_expand_attrs', new helpers\ExpandAttrs );
		$engine->addHelper( '_default', new helpers\DefaultValue );
		$engine->addHelper( '_icon', new helpers\IconHelper($this->get_icons_path()) );
		$engine->addHelper( '_the_content', new helpers\TheContent );
		$engine->addHelper( '_i18n', new helpers\I18N );
		$engine->addHelper( '_partial', new helpers\DynamicPartial );

		$engine->addHelper( '__pp', new helpers\Dump );
		$engine->addHelper( '__dump', new helpers\Dump(true) );

		do_action( $this->get_namespace() . '/templates/after_register_helpers', $engine );
	}


	/**
	 * Gets the directory paths where to look for templates
	 *
	 * @method get_paths
	 *
	 * @return array the directory paths
	 */
	public function get_paths() {
		$paths = [
			get_template_directory(),
			get_stylesheet_directory(),
		];

		$paths = array_reverse( apply_filters( $this->get_namespace() . '/template/directories', $paths ) );
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
	public function get_template_paths( $subdir = '' ) {
		$paths = array_map(function( $path ) use ( $subdir ) {
			return file_exists( $path . "/views/{$subdir}" )  ? $path . "views/{$subdir}" : null;
		}, $this->get_paths());

		return array_filter( $paths );
	}

	/**
	 * Get Template Engine options
	 *
	 * @method get_template_options
	 *
	 * @return array
	 */
	public function get_template_options() {
		$options = [
			'extension' => '.hbs',
		];

		return apply_filters( $this->get_namespace() . '/template/options', $options );
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
	public function get( $template, $content = [], $options = [], $tokenizer = null ) {
		return $this->render( $template, $content, $options, $tokenizer );
	}


	public function render( $template, $content, $options, $tokenizer ) {
		$content = utils\TplStrings::parseContent( $content, $this->get_namespace() );
		$engine = $this->get_engine( $options );

		if ( ! is_null( $tokenizer ) ) {
			$engine->setTokenizer( $tokenizer );
		}

		try {
			return $engine->render( $template, $this->get_content_with_id( $content ) );
		} catch (\InvalidArgumentEBxception $e) {
			return sprintf( '<strong style="background:#c00; color: #fff; padding: 10px">%s</strong>', $e->getMessage() );
		}
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
	public function show( $template, $content = [], $options = [], $tokenizer = null ) {
		// @codingStandardsIgnoreStart
		echo $this->render( $template, $content, $options, $tokenizer );
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
	public function get_content_with_id( $content ) {
		$hash_algorithm = apply_filters( $this->get_namespace() . '/template/hash', 'crc32b' );
		if ( ! isset( $content['_id'] ) ) {
			$content['_id'] = 'id-' . hash( $hash_algorithm, serialize( $content ) );
		}
		if ( ! isset( $content['_uniqid'] ) ) {
			$content['_uniqid'] = chr( rand( 64, 90 ) ) . str_replace( '.', '-', uniqid( '_', true ) );
		}

		return $content;
	}
}
