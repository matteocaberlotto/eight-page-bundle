# EightPageBundle


This bundle allows you to build cms editable pages within minutes without changing too much usual symfony development process.
Supports Symfony 2+ up to 3.3 (symfony 4.0 is in roadmap). Just be sure to select the proper version.

- ```1.0.x``` for Symfony ```2.x``` and Twitter Bootstrap ```3.3.x```
- ```1.3.x``` for Symfony ```3.x``` and Twitter Bootstrap ```4.x```

### Roadmap:
 - renderer tests
 - ease admin bundle switching
 - ease storage switching
 - symfony 4 compatibility


### Features:
 - fully editable page properties (title, url, meta, og, ...)
 - fully editable html layout, blocks tree with predefined widgets
 - auto form building for in-place content editing
 - yml content loader for programmatic page editing
 - very light!




### Installation

1. require via composer
    - composer require eight/page-bundle


2. install and configure dependencies (refer to each installation documentation)
    - sonata admin bundle
    - raindrop routing bundle
    - fos user bundle
    - twitter bootstrap assets (3.3.* for 1.0.* and 4.* for 1.3.*)

3. add following lines to config.yml if you need to configure one or more of the following features:
  - general encoding
  - default title
  - default description
  - locales
  - homepage redirect after locale has been detected
  - default controller (you can bind custom controllers to a page, else the default will be used)
  - default layout
  - assets to be loaded in each page
  - admin assets to be appended when editing
  - metatags you want to edit in admin area

```yml
    eight_page:
        encoding: utf-8
        seo_title: My website
        seo_description: My very cool website
        locales: [it]
        redirect_home: homepage
        default_controller: EightPageBundle:Default:index
        default_layout: AppBundle:Default:layout/default.html.twig
        js:
            - /bundles/app/js/bootstrap.min.js
            - /bundles/app/js/main.js
        admin_js:
            - /bundles/app/js/admin.js
        css:
            - /bundles/app/css/bootstrap.min.css
            - /bundles/app/css/main.css
        admin_css:
            - /bundles/app/css/admin.css
        http_metas:
            name:
                - ['keywords', 'text', { required: false }]
                - ['description', 'text', { required: false }]
                - ['robots', 'text', { required: false }]

                - ['google-site-verification', 'text', { required: false }]

                - ['twitter:title', 'text', { required: false }]
                - ['twitter:image', 'text', { required: false }]
                - ['twitter:description', 'text', { required: false }]

            property:
                - ['og:title', 'text', { required: false }]
                - ['og:url', 'text', { required: false }]
                - ['og:type', 'text', { required: false }]
                - ['og:image', 'text', { required: false }]
                - ['og:description', 'text', { required: false }]
                - ['og:site_name', 'text', { required: false }]

            http_equiv:
                - ['Content-type', 'text', { required: false }]
```

### Creating pages:
To create a page you need at least 1 layout and 1 block. A layout is simply a simfony page with at least 1 call to ```render_page_content()``` which is the twig function that dinamically appends blocks.
You can have multiple insertion points, just be sure to name each one by passing the label as parameter.
EG: ```render_page_content('head')```.
Once the insertion point is present, in the admin section you can append one of the widgets defined via configuration.

An example layout could look like this:
```twig
<html lang="{{ locale }}">
    <head>
        {{ render_encoding() }}

        {{ render_metadatas() }}

        <title>
            {% block title %}
                {% if page is not null %}{{ page.title }}{% else %}My website{% endif %}
            {% endblock %}
        </title>

        {{ eight_stylesheets() }}
    </head>
    <body>
        <header id="head">
            {{ render_static_blocks('head') }}
        </header>

        <div class="container" id="main-content">
            {{ render_page_content('default') }}
        </div>

        <footer class="footer">
            {{ render_static_blocks('footer') }}
        </footer>

        {{ eight_javascripts() }}
    </body>
</html>
```

You can use ```render_metadatas()``` to dinamically render metatags edited in the admin section.
```eight_stylesheets()``` to dinamically append stylesheet assets to the page (just read on to know how to).
```render_page_content()``` to dinamically append html editable blocks to the page.
```render_static_blocks()``` to dinamically append html editable blocks to all of the pages where this slot is rendered.
```eight_javascripts()``` to dinamically append javascript assets.


By default no widget is added, but you can use some defaults by simply adding this line to config.yml:
```yml
    - { resource: '@EightPageBundle/Resources/config/widgets.yml' }
```
To nest widgets, call ```render_inner_blocks(current_block)``` method inside a block template. Please note the 'current_block' variable as parameter which is mandatory. You can also add a label as second parameters to append multiple childrens to differents position of the current block.
E.G.:
```html
<div class="row content {{ html_classes }}">
    <div class="col-sm-8 pull-right">
        {{ render_inner_blocks(current_block, 'right') }}
    </div>
    <div class="col-sm-4 pull-left">
        {{ render_inner_blocks(current_block, 'left') }}
    </div>
</div>
```
This block template allows you to append elements to both the left and right column (without mixing).

A widget is like a symfony controller with some more features: a predefined layout and an array of editable variables. Each variable has its own database slot (even if not populated).
You have to use it as a symfony controller, eg:
```php
<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class PageLink extends AbstractWidget
{
    public function getVars()
    {
        // you can put custom logic in here

        return array(
            /**
             * This is a standard variable. You can assign all the variables
             * you need the old fashion way and they will not be altered.
             * They are converted to "mixed" types with predefined value.
             * Instead, when no value is given, variable is converted to a
             * value read from database and editable in the admin section.
             */
            'my_variable' => 4,

            /**
             * Since this variable has no value it is intended to be an
             * editable label.
             */
            'html_classes',

            /**
             * When more complex fields are required, you have to use the "Config"
             * class to specify options.
             */

            'wider_element' => new Config(array(
                'type' => 'checkbox', // eg: a checkbox you can use to tweak the template
                )),

            'html_icon_class' => new Config(array(
                'type' => 'icon', // an editable icon
                )),

            'page' => new Config(array(
                'type' => 'entity', // an editable entity
                'class' => 'Eight\PageBundle\Entity\Page',
                )),
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:page_link.html.twig';
    }

    public function getName()
    {
        return 'page_link';
    }

    public function getLabel()
    {
        return 'Link to a page';
    }
}
```

This widget has 1 "normal" variable and 3 editable variables: the first is a variable as you would declare in a symfony controller and it will be passed with its value without references to database. Instead all others will be filled with database values: the first editable one is a simple label (255 max chars), the second is an icon you can select among all fontawesome 4.7 list and the last is a link to an entity of the specified class. More types are available.
In order to create a widget you must implement all the abstract methods, a unique name to be identified and a layout. You must also register its class with the tag `eight_page.widget`.

To sum up, widget creation consists of 3 steps:

- create widget class
- create widget layout
- register wiget class with tagging

that's all.


Whenever you want to add the same block to multiple pages, use the ```render_static_content()``` method (which also accept a single string identifier as parameter). For example, you can add a static block of type "raw html" to all pages and fill with google analytics script of facebook sdk or anything that is supposed to be rendered in all pages.

In order to make jquery ui sortable feature work fine, there is one single requirement: each widget template must have a single parent html tag (usually a div or a span).

Sometimes you may need to adjust little css in administration in order to handle more complex layout editing situations. The editor adds a lot of custom classes in the admin section you can use to drive your rendering. The "preview" button will remove the editing classes from the page so you can check the page result on the fly.
You can also bind your own javascript "plugins" so they will be reloaded on blocks modification, eg:
```js
Editor.addPlugin(function () {
    // logic to init your plugin (EG: masonry reset/refresh...)
});
```

Note that everything else works the same as symfony standard so you can mix static contents with CMS ones (just note that the dinamic router of the cms has precedence in case of identical route paths).

## Twig helpers:
- ```render_title()```: renders page title taken from cms page.
- ```render_encoding()```: renders page encoding.
- ```render_metadatas()```: renders page metadata as edited in the admin.
- ```render_page_content(type)```: renders all page blocks of given type.
- ```render_inner_blocks(block, type)```: renders all child blocks of given block and type.
- ```render_static_blocks(type)```: renders all static page blocks of given type for current page.
- ```eight_stylesheets(type)```: renders all stylesheets of the current page and child blocks.
- ```eight_javascripts(type)```: renders all javascripts of the current page and child blocks.
- ```eight_body_class(type)```: renders page body class.
- ```is_current_path(name)```: returns 'active' if current path name is $name.
- ```is_host(test)```: returns true if test is contained in the current host.
- ```is_route(path)```: returns true if current path is equal to path or an index of it (accepts a label or an array).
- ```if_route(routes, string)```: returns the string if current route is in routes else an empty string.
- ```current_index(label, increment = false)```: a simple helper to count unrelated things at runtime. You can increment the counter for a given label or get its value.
- ```i18n_path(path, params)```: same as twig ```path()``` method for cms pages. It will search for a page with name $path, path $path or tag $path.
- ```i18n_path(page)```: returns the title of a page.

## More features:
You can also add js() or css() method to any widget to append assets dynamically. Just return an array of valid assets path.

Prefer ```i18n_path()``` helper in place of twig ```path()``` function to link to cms pages. It does exactly the same but with a little enhancement: you can also "search" for a page based on its tagging.
EG: ```{{ i18n_path('homepage') }}``` will link to a page tagged "homepage.<locale>" using current locale. This helper will also link to edit mode while in admin (the link will point to the editable version of the page which is quite handy for data entry process).


## Recommendations
Try to avoid adding new variables to existing widgets as this could lead to twig errors (though most are handled).
A good habit could be to always add an ```html_classes``` variable to add custom classes to any block.

