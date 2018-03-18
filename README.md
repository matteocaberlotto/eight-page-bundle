# EightPageBundle


### This is a beta version but all the twig API are in a stable state.

Supports Symfony 2+ up to 3.3 (symfony 4.0 is in roadmap). Just be sure to select the proper version. Sorry for the mess in the tagging/versioning, a cleanup is upcoming. The Symfony 3.3 branch is ```feature-3.x```.

### Missing:
 - renderer tests
 - ease integration with other usefull bundles
 - ease switching to other admin bundle (alternative to sonata admin)
 - ease switching to other storage


### Features:
 - fully editable page properties (title, url, meta, og, ...)
 - fully editable html layout, blocks tree with predefined widgets
 - auto form building for in-place content editing
 - yml content loader for programmatic page editing
 - very light!




### Installation

1. require via composer
    - composer require eight/page-bundle


2. install and configure dependencies
    - sonata admin bundle
    - raindrop routing bundle
    - fos user bundle

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

            property:
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
<html>
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
```ender_page_content()``` to dinamically append html editable blocks to the page.
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
        // you can put your controller logic in here

        return array(
            'html_classes',
            'html_icon_class' => new Config(array(
                'type' => 'icon',
                )),
            'page' => new Config(array(
                'type' => 'entity',
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
This widget has 3 editable variables: the first is a simple label (255 max chars), the second is an icon you can select among all fontawesome 4.7 list and the last is a link to an entity of the specified class.
In order to create a widget you must feature all the abstract methods, a unique name to be identified and a layout. You must also register its class with the tag `eight_page.widget`.
- create widget class
- create widget layout
- register wiget class with tagging
that's all.


Whenever you want to add 1 block statically to all pages (or better, all pages sharing same layout) use the ```render_static_content()``` method (which also accept a single string identifier as parameter).

The only requirement (in order to make the jquery ui sorting work in layout editing) is that every widgets template must have a single html tag as parent (usually a div or a span).

Sometimes you may need to adjust little css in administration in order to handle more complex layout editing situations. The editor adds a lot of custom classes in the admin section you can use to drive your rendering. The "preview" button will remove the editing classes from the page so you can check the page result on the fly.
You can also bind your own javascript "plugins" so they will be reloaded on blocks modification, eg:
```js
Editor.addPlugin(function () {
    // logic to init your plugin (EG: masonry reset/refresh...)
});
```

Note that everything else works the same as symfony standard so you can mix static contents with CMS ones (just note that the dinamic router of the cms has precedence in case of identical route paths).


## More functions
You can also add js() or css() method to any widget to append assets dynamically. Just return an array of valid assets path.

Use ```i18n_path()``` helper in place of twig ```path()``` function. It does exactly the same but with a little enhancement: you can "search" for a page based on its tagging.
EG: ```{{ i18n_path('homepage') }}``` will link to a page tagged 'homepage.en' if current route is 'en', 'homepage.it' if current route is 'it' etc... This helper will also link directly to edit mode when editing a page (the link will point to "edit homepage it" instead of the it version of homepage which is quite handy for editors).


## Recommendations
Try to avoid adding new variables to existing widgets as this could lead to twig errors (though most are handled).
A good habit could be to always add an ```html_classes``` variable to add custom classes to any block.

