<?php

use iamntz\handlebarsWP\Tpl;

class Test_Tpl extends WP_UnitTestCase {
	function test_Tpl_returns_handlebars_instance() {
		$this->assertTrue( is_a( Tpl::get_engine(), '\Handlebars\Handlebars' ) );
	}

	function test_Tpl_registered_helpers() {
		$engine = Tpl::get_engine();
		$this->assertTrue( $engine->getHelpers()->has( 'sanitize' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'esc_attr' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'esc_textarea' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'sanitize_text_field' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'esc_url' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'checked_attr' ) );
		$this->assertTrue( $engine->getHelpers()->has( 'selected_attr' ) );
	}

	function test_default_views_paths() {
		$this->assertTrue( in_array( trailingslashit( get_template_directory() ), Tpl::get_paths() ) );
	}

	function test_custom_views_paths() {
		add_filter( 'iamntz/template/directories', function( $paths ) {
			$paths[] = dirname( __FILE__ ) . '/../fixtures/views';
			return $paths;
		} );

		$this->assertTrue( in_array( trailingslashit( dirname( __FILE__ ) . '/../fixtures/views' ), Tpl::get_paths() ) );
	}

	function test_ids_that_are_added_automatically() {
		$content = Tpl::get_content_with_id( [] );

		$this->assertFalse( empty( $content['_id'] ) );
		$this->assertFalse( empty( $content['_uniqid'] ) );
	}

	function test_default_variables_passed_to_the_template() {
		add_filter('iamntz/templates/engine', function() {
			$stub = $this->createMock( 'Handlebars\Handlebars' );
			$stub->method( 'render' )->will( $this->returnArgument( 1 ) );

			return $stub;
		});

		$tpl = Tpl::get('dummy', [
			'foo' => 'Hello, World!',
		]);

		$this->assertEquals( $tpl['foo'], 'Hello, World!' );

		$this->assertEquals( $tpl['home_url'], home_url( '/' ) );
		$this->assertEquals( $tpl['theme_uri'], get_stylesheet_directory_uri() );
		$this->assertEquals( $tpl['parent_theme_uri'], get_template_directory_uri() );

		$this->assertTrue( isset( $tpl['is_child_theme?'] ) );
		$this->assertTrue( isset( $tpl['is_home?'] ) );
		$this->assertTrue( isset( $tpl['is_admin?'] ) );
	}
}
