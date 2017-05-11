# An awesome code generator for laravel framework - with client-side validation

For full documentation and live demo please visit <a href="https://crestapps.com/laravel-code-generator/docs/1.2" target="_blank" title="Laravel Code Generator Documentation">CrestApps.com</a>

## Introduction

A clean code generator for Laravel framework that will save you time! This awesome tool will help you generate resources like views, controllers, routes, migrations, languages or request-forms! It is extremely flexible and customizable to cover many on the use cases. It is shipped with cross-browsers compatible template, along with a client-side validation to modernize your application.

## Features

* Create very clean code to build on.
* Create full resources using a single command with/without <strong>migration</strong> or from <strong>existing database</strong>.
* Create standard CRUD controllers with simple or form-request validation.
* Create model with relations.
* Create named routes.
* Create standard CRUD views.
* Very flexible and rich with configurable options.
* (Beta) Client-side validation.
* File uploading handling.
* Auto multiple-response storing in the database.
* Create form-request for complex validation.
* Customizable view’s templates to enable you to change the standard look and feel of your application.
* Create view's layouts with and without client-side validation.
* Change the template at run time to generate different views.
* Create code to upload file.
* Ability to generate views with and without Laravel-Collective.
* Nicely deals with and format datetime, date or time field.
* Auto handles any boolean field.
* Auto add foreign relations to the model.
* Auto use foreign relation in the controller and the views when needed.
* Lots of documentation.

## Installation

 To download this package into your laravel project, use the command-line to execute the following command
  
```
composer require crestapps/laravel-code-generator --dev
```
 
 To bootstrap the packages into your project, open the `config/app.php` file in your project. Then, look for the providers array.

Add the following line to bootstrap laravel-code-generator to the framework.

```
CrestApps\CodeGenerator\CodeGeneratorServiceProvider::class,
```

Finally, execute the following command from the command-line to publish the package's config and the default template to start generating awesome code.
```
php artisan vendor:publish --provider="CrestApps\CodeGenerator\CodeGeneratorServiceProvider" --tag=default
```

> A layout is required for the default views! The code generator allows you to create a layout using the command-line. Of cource you can use your own layout. You'll only need to include [CSS bootstrap framework](http://getbootstrap.com/ "CSS bootstrap framework") in your layout for the default templates to work properly. Additionally, you can chose to you design your own templetes using a different or no css framework. 


## Available Commands

> The command in between the square brackets [] must be replaced with a variable of your choice.

* php artisan create:resources [model-name]
* php artisan create:layout [application-name]
* php artisan create:controller [controller-name]
* php artisan create:model [model-name]
* php artisan create:routes [controller-name]
* php artisan create:views [model-name]
* php artisan create:create-view [model-name]
* php artisan create:edit-view [model-name]
* php artisan create:index-view [model-name]
* php artisan create:show-view [model-name]
* php artisan create:form-view [model-name]
* php artisan create:migration [table-name]
* php artisan create:form-request [class-name]
* php artisan create:language [language-file-name]
* php artisan create:fields-file [table-name]

> Full documentation available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/docs/1.2 "Laravel Code Generator Documentation"). 

> Live demo is available at [CrestApps.com](https://www.crestapps.com/laravel-code-generator/demos/1.2 "Laravel Code Generator Live Demo"). 


## License

"Laravel Code Generator" is an open-sourced software licensed under the <a href="https://opensource.org/licenses/MIT" target="_blank" title="MIT license">MIT license</a>

