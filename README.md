# Bolt Shortcodes
Simple Shortcode implementation for BoltCMS

## Requirements
Bolt Shortcodes requires PHP 5.4 or greater.

## What are "Shortcodes"?

Shortcodes are a common format used to provide macro support in various CMSs, editors, and libraries. Perhaps the most famous example is the [Wordpress Shortcode API](https://codex.wordpress.org/Shortcode_API), which provides developers an avenue by which they can enhance their theme or plugin's ease of use.

The anatomy of a shortcode is as follows:

```
[tag] - No content, no attributes
[tag]Content[/tag] - Tag with content
[tag atribute=value] - Tag with attributes
[tag atribute=value]Content[/tag] - Tag with content and attributes
```

There are many formats that shortcodes can follow, but ultimately the idea is that when a shortcode tag is matched, its contents are replaced by a server-side callback.

## Configuration

This Bolt Extension makes heavy use of both my [Shortcodes](https://github.com/maiorano84/shortcodes) library and Twig.

This extension primarily looks for Twig templates and Shortcode classes in both your active theme and extensions directories.

Out of the box, the Extension will look for classes and templates within the following directories:

**Shortcode Classes:**
* {your-theme}/shortcodes/library
* {extensions}/shortcodes/library

**Shortcode Templates:**
* {your-theme}/shortcodes/twig
* {extensions}/shortcodes/twig

The names of these directories are configurable within the provided .yml file.

It is best to have theme-specific shortcodes within your theme directory, and theme-agnostic shortcodes in your extensions directory. If an active theme has its own implementation of a given shortcode, that theme's implementation will take precedence.

To register a shortcode, you can either register it as a Twig Shortcode by simply providing a name and defer all logic to your Twig template, or you can provide the fully-qualified Class name to process any content or attributes beforehand.

**Twig Shortcode**

Out of the box, the configuration .yml file comes with support for the [SimpleForms](http://extensions.bolt.cm/view/28e36add-bb48-4692-be50-e677f73b8f89) extension with the following definition:

```
tags:
  - name: simpleform
    attributes:
      form_name: contact
```

If you have the SimpleForms extension installed already, you can render out your forms using the provided template in the examples directory. Otherwise, you can create a file called `_simpleform.twig` in the appropriate directory. All templates rendered this way will receive the following variables:

* `{{ content }}` - The content - if any - that was parsed from that particular shortcode instance (ie: [foo]Content[/foo])
* `{{ attributes.bar }}` - The attributes - if any - that were parsed from that particular shortcode instance (ie: [foo bar="baz"/])
* `{{ tag }}` - The name of the shortcode

**Custom Shortcode**

It is highly recommended that all custom shortcodes extend `\Bolt\Shortcodes\BaseShortcode`

By doing this, you will have access to everything you need to get started, and you can define simply your name, attributes, and handle method within this class.

A simple - albeit pointless - example could look something like this:

*Style.php*
```php
namespace Bolt\Shortcodes;
class Style extends BaseShortcode{
    protected $name = 'style';
    protected $attributes = ['type' => 'bold'];
    public function handle($content = null, array $atts = []){
        switch($atts['type']){
            case 'italics' :
                $css = 'font-style:italic';
                break;
            case 'underline' :
                $css = 'text-decoration:underline';
                break;
            case 'bold' :
            default:
                $css = 'font-weight:bold';
                break;
        }
        return $this->render(['css'=>$css, 'content'=>$content]);
    }
}
```

*_style.twig*
```html
<span style="{{ css }}">{{ content }}</span>
```

*bolt-shortcodes.maiorano84.yml*
```
tags:
  - class: '\Bolt\Shortcodes\Style'
```

## Usage

After configuring your tags, classes, and templates all that's left is to tell Bolt what text it needs to respond to in your theme.

If you would like to render shortcode directly in one of your theme's templates, you can use the provided `do_shortcode` Twig Function:

```
{{ do_shortcode('[foo]') }}
```

If you want to render the contents of your editor, you may also use `do_shortcode` as a Twig Filter:

```
{{ editor.body|do_shortcode }}
```

`do_shortcode` runs the Shortcode Manager's implementation of `doShortcode`. If you would like to see what other arguments you may provide, please review the README file in the original library [here](https://github.com/maiorano84/shortcodes/blob/master/README.md)

## Aliasing

By default, Aliasing is not supported in either the TwigShortcode or BaseShortcode classes. It is recommended that you set up each Shortcode in your configuration file.

However, if you would like a custom shortcode to utilize aliases, feel free to use the `\Maiorano\Shortcodes\Contracts\AliasInterface` Interface and `\Maiorano\Shortcodes\Contracts\Traits\Alias` Trait to bootstrap Aliasing capabilities. All aliases defined in your custom class will be available upon registration.