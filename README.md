## What is this?

Since dawn of time, WordPress encouraged mixing PHP and HTML code. This should stop! Here is an utility that will help you to deal with some stuff.

## Installing

The easy way is to install it via composer: `composer require iamntz/handlebars.wp` and `require 'vendor/autoload.php';` in your `functions.php`.

The hard way is to download the zip, download Handlebars.php and deal with all the require by yourself. So just go the easy way :)

## How to use?

```php
\iamntz\handlebarsWP\Tpl::show('foo', []);

echo \iamntz\handlebarsWP\Tpl::get('foo', []);
```

This will search for `views/foo.hbs` in your current theme directory, then in your parent theme directory and, if the file is not found, it will throw an exception.

If you're including a partial, that will be search in `views/partials/foo.hbs`  in the same order as above.

## WordPress helpers

For now, there are only a bunch of helpers: various sanitization, `checked_attr` and `selected_attr` for `checkbox|radio` inputs and selects.

#### Select & Checkboxes

```php
Tpl::show('select', [
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
  <option value="{{ optionValue }}" {{selected_attr optionValue currentValue}}></option>
{{/each}}
```

Same works for checkboxes and radios.

#### Sanitization

You can use few builtin helpers to sanitize fields.

```
<input value="{{{esc_attr myValue}}}">
```

By default, there are only a bunch of functions available: `esc_attr`, `esc_textarea`, `sanitize_text_field` and `esc_url`, but if the desired one isn't present, you could do one of the following:

1. Run a generic helper: `{{sanitize myValue sanitize_key}}` (where `sanitize_key`) is an WP function (however, this will allow you to run **ANY** function, so beware!);
2. register a new helper like this:

```
add_filter('iamntz/templates/engine', function($engine){
  $engine->addHelper( 'my_helper', new MyHelperClass );
  return $engine
});
```

#### A note about custom helpers

The idea of using a template engine is to move the logic out of HTML, so don't overdo it! Although you could do a lot of stuff with helpers, consider moving the logic to your PHP files!

## Customizing

Of course, you can customize most of the configuration:

#### Adding a custom path

If you're using this package in your plugin, then you may want to add the plugin path to be search for:

```php
add_filter('iamntz/template/directories', function($paths)
{
  $paths[] = plugin_dir_path( __FILE__ );
  return $paths;
});
```

This way, you will allow your users to customize plugin views. Isn't that cool?

#### Changing default extension

By default, the template extension is `.hbs`, but if you want to change that, you can use the `iamntz/template/options` filter to do it (and change a bunch of other options as well).

#### i18n

"But what about translation?" You may ask. Fear not, you're also covered!

```php
add_filter('iamntz/template/i18n_strings', function($strings)
{
  $strings['hello'] = __('hello world!');
  return $strings;
});
```

Then, in your template, just use `{{{ i18n.hello }}}`. Easy peasy, right?

#### Adding IDs?

Sometimes you need to add an ID to an element. Instead of manually doing this on every single template, you can already use either `{{ _id }}` or, if you prefer, a ridiculous long id, `{{ _uniqid }}`.

Please note that `_id` is basically a `crc32` hash of your data passed to your template and if that seems too slow to you, you can specify a different algorithm by using `iamntz/template/hash` filter.

## License

MIT.