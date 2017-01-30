<?php

use iamntz\handlebarsWP\Tpl;

class Test_Tpl extends WP_UnitTestCase {

	function test_Tpl_returns_handlebars_instance()
	{
		$this->assertTrue(is_a(Tpl::get_engine(), '\Handlebars\Handlebars'));
	}

	function test_Tpl_registered_helpers()
	{
		$engine = Tpl::get_engine();
		$this->assertTrue($engine->getHelpers()->has('sanitize'));
		$this->assertTrue($engine->getHelpers()->has('esc_attr'));
		$this->assertTrue($engine->getHelpers()->has('esc_textarea'));
		$this->assertTrue($engine->getHelpers()->has('sanitize_text_field'));
		$this->assertTrue($engine->getHelpers()->has('esc_url'));
		$this->assertTrue($engine->getHelpers()->has('checked_attr'));
		$this->assertTrue($engine->getHelpers()->has('selected_attr'));
	}
}