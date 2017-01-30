<?php
/**
 * Utility to allow buffering WP functions that outputs stuff.
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */

namespace iamntz\handlebarsWP\utils;

/**
 * WordPress Wrapper
 */
class WP {

	/**
	 * Singleton instance
	 *
	 * @var WP
	 */
	private static $instance;

	/**
	 * Magic method
	 *
	 * @method __call
	 *
	 * @param  string $callback the WP function to be called.
	 * @param  mixed  $args     the arguments that should be passed to the WP function.
	 *
	 * @return mixed whatever the WP function returns
	 *
	 * @throws \Exception\InvalidArgumentException When the function is not valid.
	 */
	public function __call( $callback, $args ) {
		$buffer_pattern = '/buffer_/';
		$is_buffered = preg_match( $buffer_pattern, $callback );

		if ( $is_buffered ) {
			$callback = preg_replace( $buffer_pattern, '', $callback );
			ob_start();
		}

		if ( ! function_exists( $callback ) ) {
			throw new \Exception\InvalidArgumentException( "{$callback} is not a WordPress method!!", 1 );
		}

		if ( $is_buffered ) {
			call_user_func_array( $callback, $args );
			$buffer_content = ob_get_contents();
			ob_end_clean();
			return $buffer_content;
		}
		return call_user_func_array( $callback, $args );
	}

	/**
	 * Called function
	 *
	 * @method get
	 *
	 * @return mixed
	 */
	public static function get() {
		if ( ! self::$instance ) {
			self::$instance = new SELF;
		}

		return self::$instance;
	}
}
