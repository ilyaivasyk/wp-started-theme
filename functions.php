<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

if (! function_exists('\Roots\bootloader')) {
    wp_die(
        __('You need to install Acorn to use this theme.', 'sage'),
        '',
        [
            'link_url' => 'https://roots.io/acorn/docs/installation/',
            'link_text' => __('Acorn Docs: Installation', 'sage'),
        ]
    );
}

\Roots\bootloader()->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });


//acf blocks
//acf register blocks
add_filter('block_categories', function ($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'stellar-theme-blocks',
                'title' => __('Stellar Theme Blocks', 'stellar'),
            ),
        )
    );
}, 10, 2);

function init_block_types()
{
    if (function_exists('acf_register_block_type')) {
        acf_register_custom_block('stellar-theme-blocks', 'header-section-1', 'Header Section 1', 'dashicons-layout', true, 'header-section-1.jpg', 'resources/acf-blocks/header-section-1.php');
        // Додайте всі інші блоки, які ви створили, і вкажіть шлях до файлу PHP кожного блоку
    }
}
add_action('acf/init', 'init_block_types');

// Функція для реєстрації власних блоків ACF
function acf_register_custom_block($category, $name, $title, $icon, $multiple, $media, $php_file_path)
{
    acf_register_block_type(array(
        'name'              => $name,
        'title'             => $title,
        'description'       => '',
        'render_callback'   => function ($block) use ($media, $php_file_path) {
            // Завантажте файл PHP блоку з вказаного шляху
            if (file_exists(get_template_directory() . '/' . $php_file_path)) {
                include(get_template_directory() . '/' . $php_file_path);
            }
        },
        'category'          => $category,
        'icon'              => $icon,
        'mode'              => 'edit',
        'align'             => 'full',
        'supports'          => array(
            'align'    => false,
            'mode'     => false,
            'multiple' => $multiple,
        ),
        'example'           => array(
            'attributes' => array(
                'mode' => 'preview',
                'data' => array(
                    'is_example' => true,
                    'media' => 'resources/block-previews/' . $media,
                ),
            ),
        ),
    ));
}

// Створення пункту меню "Reusable Blocks"
function add_reusable_blocks_admin_menu()
{
    add_menu_page('Reusable Blocks', 'Reusable Blocks', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 22);
}
add_action('admin_menu', 'add_reusable_blocks_admin_menu');
