# EightPageBundle

### Installation

1. require via composer
    - composer require eight/page-bundle


2. install dependencies
    - sonata admin bundle
    - raindrop routing bundle
    - fos user bundle

3. add following lines to config.yml for following features:
  - general encoding
  - locales
  - homepage redirect after locale has been detected
  - default controller (you can bind it directly to page or default will be used)
  - default layout
  - assets to be loaded in each page
  - metatags you want to edit in admin area
        
```yml
    eight_page:
        encoding: utf-8
        locales: [it]
        redirect_home: homepage
        default_controller: EightPageBundle:Default:index
        default_layout: AppBundle:Default:layout/default.html.twig
        js:
            - /bundles/app/js/bootstrap.min.js
            - /bundles/app/js/main.js
        css:
            - /bundles/app/css/bootstrap.min.css
            - /bundles/app/css/main.css
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

4. add following parameters (where you want, eg: parameters.yml)
```yml
    parameters:
        seo_title: My website
        seo_description: My very cool website
        seo_encoding: utf-8
```
