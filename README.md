# Magnetar

Magnetar is a web application framework that focuses on speed and ease of use.

To get started, check out the [Pulsar](https://www.github.com/pyxol/pulsar) starter application.

## Features

- **Dependecy Injection Container** is the core of Magnetar's framework. It manages global instances and automatically resolves injected dependencies in your classes.
- **Facades** give developers easy access to internal tools through static classes without having to manage instances or resolve dependencies.
- **Service Providers** provide dynamic configuration and wiring of services.
- **Models** are predictable data structures that interact with the database
- **Routing** processes incoming requests and directs them to the appropriate controller.
- **Controllers** uses a request to generate return responses.

## Kernels

Magnetar is designed from the ground up to be a multi-interface framework. Kernels handle the various ways of interfacing with the application. The **HTTP Kernel** processes incoming HTTP requests and the **Console Kernel** handles CLI requests. Kernels allows developers to use the same codebase for every interface of your application for code uniformity and predictability.

## Themes

Themes provide a web frontend to your application and are stored in the `themes/` directory in the root of your application. 

Magnetar implements a no-nonsense approach to template files. PHP is already an amazing templating engine so template files are purely PHP-based. With Magnetar themes, there is no need to learn arbitrary templating languages or syntaxes, no complex template file caching to deal with by default, and there isn't a faster PHP-based templating engine than raw PHP.

Usage of template files in themes is simple:

```php
// renders themes/my_theme/template_name.php
Theme::tpl('template_name', [
	'var' => 'value'
]);
```

In the template file, you have access to the contextualized variables passed to the template:

```php
<p>I am a template. My variable is: <?=esc_html( $this->var );?></p>
```

We provide a few global functions to make rendering templates easier. Some of these functions include: `display_tpl()` to embed another template, `esc_attr()` to safely escape a string in an HTML attribute, and `esc_html()` to safely escape a string for use everywhere else in HTML.

You can also render another template file from within a template file, with our without contextual variables.

```php
// themes/my_theme/frontpage.php
<?php
	$this->display_tpl('header');   // 'var' is passed to this template
?>

<p>I am a template. My variable is: <?=esc_html( $this->var );?></p>

<?php
	display_tpl('footer');   // contextual variable 'var' is NOT passed to this template
```

A different theme can be rendered anywhere in your application depending on your needs. If an endpoint in your controller decides to use a different theme, you can use:

```php
// renders themes/different_theme/template_name.php
Theme::theme('different_theme')->tpl('template_name', [
	'var' => 'value'
]);
```

## Adapters

### Databases

Drivers for **MySQL** / **MariaDB**, **PostgreSQL**, and **SQLite** are included by default. Fetching data from the database is as easy as using:

**Query Builder:**
```php
$results = DB::table('table')->where('id', 1)->fetch();
```

**Quick query methods:**
```php
$results = DB::get_rows("SELECT * FROM `table` WHERE `id` = :id", ['id' => 1]);
```

### Cache

Cache drivers for **Memcached** and **Redis** are available. Fetching cached data and resolving missing cache is as easy as using:

```php
$cached_value = Cache::get('key', fn () => {
	return 'value';
});
```


