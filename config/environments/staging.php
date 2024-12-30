<?php
/**
 * Configuration overrides for WP_ENV === 'staging'
 */

use Roots\WPConfig\Config;

/**
 * You should try to keep staging as close to production as possible. However,
 * should you need to, you can always override production configuration values
 * with `Config::define`.
 *
 * Example: `Config::define('WP_DEBUG', true);`
 * Example: `Config::define('DISALLOW_FILE_MODS', false);`
 */

Config::define('DISALLOW_INDEXING', true);

define('THEME_PROPERTIES', [
    // Theme title used in menus and other places
    'theme_title' => 'AC Green',
    // Absolute path to Icon used for the theme menu, in the backend
    // eg: https://mysite.com/wp-content/uploads/some-icon.svg
    'menu_icon' => '',
    // Core plugin title, used in various places
    'core_title' => 'ACG Core',
    // Disables various "rey" symbols, "What's new" page, etc.
    'branding' => false,
    // Disables "help" links that go to Rey's KB
    'kb_links' => false,
    // Exclude boxes in Rey dashboard
    'excluded_dashboxes' => [
        'help',
        'newsletter',
        'register',
        'required_plugins',
        'versions',
        'child_theme',
        'system_status',
    ],
    // Replaces "Rey" button icons in backend, admin bar or Elementor
    // Absolute path eg: https://mysite.com/wp-content/uploads/some-icon.svg
    'button_icon' => '',
    // Fallback text for "Rey" button icons, if the icon is disabled
    'button_text' => 'ACG',
    // Disables the Setup Wizard
    'setup_wizard' => false,
    // Disables the demo import page. Better to just remove "One click demo import" plugin entirely.
    // supports "user_id" & "capability"
    'demo_import' => false,
    // Disables the Plugins manager page in Rey sub-menu.
    // supports "user_id" & "capability"
    'plugins_manager' => false,
    // Disables the Elements manager page in Rey sub-menu.
    // supports "user_id" & "capability"
    'elements_manager' => false,
    // Disables the Modules manager page in Rey sub-menu.
    // supports "user_id" & "capability"
    'modules_manager' => false,
    // Disables the Theme admin menu in the backend
    // supports "user_id" & "capability"
    // 'admin_menu' => false,
    // Disables the theme menu in the admin bar
    // 'admin_bar_menu' => false,
    // Disables the Elementor Menu in the editor bottom-left toolbar
    // 'elementor_menu' => false,
    // Override theme data in Appearance > Themes
    'theme_data' => [
        // Parent theme
        'parent' => [
            'name' => 'AC Green',
            'screenshot' => [
                '/screenshot.png' // absolute path to screenshot
            ],
            'description' => 'The theme for acgreencaps.com.',
            'author' => 'Newloong LLC',
            'authorAndUri' => '<a href="https://newloong.com">Newloong LLC</a>',
        ],
        // Child theme
        'child' => [
            'name' => 'AC Green - Child',
            'screenshot' => [
                '/screenshot.png' // absolute path to screenshot
            ],
            'description' => 'The child theme for AC Green.',
            'author' => 'Newloong LLC',
            'authorAndUri' => '<a href="https://newloong.com">Newloong LLC</a>',
        ],
    ],
    // Will cleanup Rey's module plugins (Fullscreen menu, Preloaders etc.)
    'whitelabel_plugins' => true,
]);