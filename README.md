# WordPress plugin example

This project demonstrates how to build a custom WordPress plugin for Miso data integration.

### What will this plugin do?

* Offer a `WP_CLI` command to upload all your posts to Miso catalog.
* Register actions to update/delete Miso records when you create/update/trash WordPress posts.

## Requirements

To build your plugin, you need:

* A WordPress site.
* Basic knowledge of PHP and [composer](https://getcomposer.org/).

## Integration steps

1. Git clone or fork this project.
1. Run composer install under `miso` directory.
1. In `miso` directory, add an `.env` file to specify your Miso API key.
1. Modify the implementation according to your needs.
1. Install this custom plugin on your WordPress site.
1. Execute full-sync command.

### 1. Clone the project

Git clone this project to bootstrap.

```sh
git clone git@github.com:MisoAI/wordpress-plugin-example.git
```

### 2. Composer install

Use composer to install PHP dependencies.

```sh
cd miso
composer install
```

### 3. Add .env file

In `miso` directory, add an `.env` file to specify your Miso secret API key. See `miso/.env.sample` file for example. You can access your API key in [Miso dashboard](https://dojo.askmiso.com/).

### 4. Modify implementation

You can tailor this plugin to your need.

| File | Description |
| --- | --- |
| `.env` | A file to hold environment variables, including your API key. You have to create one by yourself. |
| `.env.sample` | A sample of `.env` |
| `client.php` | Classes to call Miso data API. You probably don't need to modify them. |
| `filters.php` | A filter function that transforms WordPress posts into Miso records. |
| `actions.php` | Actions to cascade WordPress post updates to Miso catalog. |
| `wp-cli.php` | WordPress CLI commands. |

### 5. Install the plugin

Compress the entire `miso` directory into a zip file, and then upload it as a custom plugin in your WordPress dashboard.

### 6. Execute fullsync command

In your WordPress terminal, execute:

```sh
wp miso fullsync
```

which will upload all your published posts to Miso catalog.
