# EightPageBundle


### Warning: this is an alpha version under development.


### Missing:
 - tests
 - documentation with examples
 - ease integration with other usefull bundles (eg: cmf)
 - ease switching to other admin bundle (alternative to sonata admin)
 - ease switching to other storage


### Features:
 - fully editable page properties (title, url, meta ...)
 - customizable tree structure of html blocks
 - automated form building for content editing
 - in page editor with preview and unobtrusive visual editor
 - yml content loader


### Objective:
 - ease cms pages creation
 - minimize markup/code overhead




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

