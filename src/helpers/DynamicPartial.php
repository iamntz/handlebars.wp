<?php
/**
 * Dynamic Partial
 *
 * @package HandlebarsWP
 * @author Ionuț Staicu <handlebarswp@iamntz.com>
 */
namespace iamntz\handlebarsWP\helpers;

use Handlebars\Context;
use Handlebars\Handlebars;
use Handlebars\Helper;
use Handlebars\Template;

class DynamicPartial implements Helper
{
	/**
	 * Execute the helper
	 *
	 * @param \Handlebars\Template $template The template instance
	 * @param \Handlebars\Context  $context  The current context
	 * @param array                $args     The arguments passed the the helper
	 * @param string               $source   The source
	 *
	 * @return mixed
	 */
	public function execute(Template $template, Context $context, $args, $source)
	{
		$name = $context->get($args);
		// https://github.com/XaminProject/handlebars.php/issues/171#issuecomment-316038386
		try {
			$h = $template->getEngine();
			$partial = $h->loadPartial($name);
			$buffer = $partial->render($context);
		} catch (\Exception $e) {}
		return $buffer;
	}
}
