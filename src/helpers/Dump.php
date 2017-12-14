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
class Dump implements \Handlebars\Helper {

	public function __construct($isVarDump = false) {
		$this->isVarDump = $isVarDump;
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
	public function execute( \Handlebars\Template $template, \Handlebars\Context $context, $args, $source ) {
		$parsed_args = $template->parseArguments( $args );

		if (empty($parsed_args)) {
			return;
		}

		$dump = '';

		foreach ($parsed_args as $arg) {
			ob_start();

			if ($this->isVarDump) {
        var_dump($context->get($arg));
			} else {
				echo '<pre style="font-size:18px">';
				print_r($context->get($arg));
				echo '</pre>';
			}

      $dump .= ob_get_contents();
      ob_end_clean();
		}

		return sprintf('<pre style="background: rgba(255, 250, 230, 0.95); padding: 10px; position: relative; z-index: 9999">%s</pre>', $dump);
	}
}
