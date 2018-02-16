## What is this?

Since dawn of time, WordPress encouraged mixing PHP and HTML code. This should stop! Here is an utility that will help you to deal with some stuff.

This package was built by being actually used and is a wrapper/helper for [Handlebars.php](https://github.com/XaminProject/handlebars.php) adapted to WordPress usage. It provides separation of HTML/PHP, cascade fallback (i.e. you can allow users to overwrite template files) and much more.

So instead of one file like this:

```
while( have_posts() ){ the_post();
  ?>
  <h2><?php the_title(); ?></h2>
  <?php if(is_single()) { ?>
    <div class="theContent"><?php the_content ?></div>
  <?php } else { ?>
    <div class="theExcerpt"><?php the_excerpt(); ?></div>
  <?php }
  <?php
}
```

You'll have two files:


```
// index.php
use \iamntz\handlebarsWP\WP;
(new \MyTpl\Tpl )->show('index', [
  'the_content' => WP::get()->buffer_the_content(),
  'the_excerpt' => WP::get()->buffer_the_excerpt(),
  'title' => WP::get()->buffer_the_title(),
  'is_single' => is_single()
]);
```

```
// index.hbs
<h2>{{{ title }}}</h2>

{{#if is_single}}
  <div class="theContent">{{{ the_content }}}</div>
{{else}}
  <div class="theExcerpt">{{{ the_excerpt }}}</div>
{{/if}}
```

Better, right? You already have the responsabilities separated; you know that in your `php` file you'll find **only** PHP code. Arguably, this looks like learning a whole new language, but really, you need to spend like 15 minutes to get used to handlebars syntax!

Bonus points: you can reuse these templates in JavaScript!


## Installing

The easy way is to install it via composer: `composer require iamntz/handlebars.wp` and `require 'vendor/autoload.php';` in your `functions.php`.

The hard way is to download the zip, download Handlebars.php and deal with all the require by yourself. So just go the easy way :)

## How to use?


The best way to isolate things out is to extend the `Tpl` class and override methods. The minimum is to override the `get_namespace` method:

```php
class MyTpl extends \iamntz\handlebarsWP\Tpl {
  public function get_namespace() {
    return 'my_namespace';
  }
```

NB: this part is **totally** optional, but **strongly** recommended, because you'll be sure you won't have any conflicts with plugins and themes that are also using this awesome package.


```php
(new \MyTpl\Tpl )->show('foo', []);

echo (new \MyTpl\Tpl )->get('foo', []);
```

This will search for `views/foo.hbs` in your current theme directory, then in your parent theme directory and, if the file is not found, it will throw an exception.

If you're including a partial, that will be search in `views/partials/foo.hbs`  in the same order as above.

## WordPress helpers

For now, there are only a bunch of helpers: various sanitization, `_checked` and `_selected` for `checkbox|radio` inputs and selects.

#### Select & Checkboxes

```php
(new \MyTpl\Tpl )->show('select', [
  'options' => [
    [
      'optionValue' => 'foo'
    ],
    [
      'optionValue' => 'bar'
    ]
  ],
  'currentValue' => 'bar'
]);
```

```
{{each options}}
  <option value="{{ optionValue }}" {{_selected optionValue currentValue}}></option>
{{/each}}
```

Same works for checkboxes and radios.

#### Sanitization

You can use few builtin helpers to sanitize fields.

```
<input value="{{{_esc_attr myValue}}}">
```

By default, there are only a bunch of functions available: `_esc_attr`, `_esc_textarea`, `_sanitize_text_field` and `_esc_url`, but if the desired one isn't present, you could do one of the following:

1. Run a generic helper: `{{_sanitize myValue sanitize_key}}` (where `sanitize_key`) is an WP function (however, this will allow you to run **ANY** function, so beware!);
2. register a new helper like this:

```
add_filter('iamntz/templates/engine', function($engine){
  $engine->addHelper( 'my_helper', new MyHelperClass );
  return $engine
});
```

#### Attributes
You can also pass an array to the `_expand_attrs` helper that will be unfurled as a string of attributes:

```php
(new \MyTpl\Tpl )->get('template_file', [
    'attrs' => [
      'foo' => 'bar',
      'baz',
    ]
  ]);
```

In your `template_file.hbs` you will use it like this:

```html
<div {{{_expand_attrs attrs}}}></div>
```

And will expand to this:

```html
<div foo="bar" baz></div>
```

#### A note about custom helpers

The idea of using a template engine is to move the logic out of HTML, so don't overdo it! Although you could do a lot of stuff with helpers, consider moving the logic to your PHP files!

## What about WordPress functions that echoes things?

There are some WordPress functions that echoes things without any built in way of disabling this (e.g. `the_content` or `the_excerpt`). You could use the built in helper to deal with that as well:

```
\iamntz\handlebarsWP\WP::get()->buffer_the_content()
```

Basically all functions that are prefixed with `buffer_` will be... well, buffered.

## Customizing

Of course, you can customize most of the configuration:

#### Adding a custom path

If you're using this package in your plugin, then you may want to add the plugin path to be search for:

```php
add_filter('my_namespace/template/directories', function($paths)
{
  $paths[] = plugin_dir_path( __FILE__ );
  return $paths;
});
```

This way, you will allow your users to customize plugin views. Isn't that cool?

#### Changing default extension

By default, the template extension is `.hbs`, but if you want to change that, you can use the `my_namespace/template/options` filter to do it (and change a bunch of other options as well).

#### i18n

"But what about translation?" You may ask. Fear not, you're also covered!

```php
add_filter('my_namespace/template/i18n_strings', function($strings)
{
  $strings['hello'] = __('hello world!');
  return $strings;
});
```

Then, in your template, just use `{{{ i18n.hello }}}`. Easy peasy, right?

#### Adding IDs?

Sometimes you need to add an ID to an element. Instead of manually doing this on every single template, you can already use either `{{ _id }}` or, if you prefer, a ridiculous long id, `{{ _uniqid }}`.

Please note that `_id` is basically a `crc32` hash of your data passed to your template and if that seems too slow to you, you can specify a different algorithm by using `my_namespace/template/hash` filter.

## Found this useful?

You can get [hosting](https://m.do.co/c/c95a44d0e992), [donate](https://www.paypal.me/iamntz) or buy me a [gift](http://iamntz.com/wishlist).

## License

MIT.
