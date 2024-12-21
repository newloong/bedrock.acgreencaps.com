<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API;

use Barn2\Plugin\Discount_Manager\Dependencies\Lib\Plugin\Plugin;
use JsonSerializable;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Tabs\Tab_Interface;
/**
 * Class Settings_Manager
 *
 * @package Barn2\Settings_API
 */
class Settings_Manager implements JsonSerializable
{
    /**
     * @var Plugin
     */
    protected $plugin;
    /**
     * @var Tab_Interface[]
     */
    protected $tabs = [];
    /**
     * @var string
     */
    protected $layout = 'default';
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var string
     */
    protected $library_path;
    /**
     * @var string
     */
    protected $library_url;
    /**
     * @var Rest_Api
     */
    protected $api;
    /**
     * @var callable
     */
    protected $validation;
    /**
     * Constructor.
     *
     * @param Plugin $plugin
     * @param string $custom_prefix The custom prefix to use for the option name.
     */
    public function __construct(Plugin $plugin, string $custom_prefix = '')
    {
        $this->plugin = $plugin;
        $this->helper = new Helper($plugin->get_slug(), $custom_prefix);
        $this->library_path = \trailingslashit(\plugin_dir_path(__DIR__));
        $this->library_url = \trailingslashit(\plugin_dir_url(__DIR__));
    }
    /**
     * Get the plugin.
     *
     * @return Plugin
     */
    public function get_plugin() : Plugin
    {
        return $this->plugin;
    }
    /**
     * Get the layout.
     *
     * @return string
     */
    public function get_layout() : string
    {
        return $this->layout;
    }
    /**
     * Set the layout.
     *
     * @param string $layout The layout.
     */
    public function set_layout(string $layout) : self
    {
        $this->layout = $layout;
        return $this;
    }
    /**
     * Manually set the path to the library.
     *
     * @param string $path
     * @return self
     */
    public function set_library_path(string $path) : self
    {
        $this->library_path = $path;
        return $this;
    }
    /**
     * Get the path to the library.
     *
     * @return string
     */
    public function get_library_path() : string
    {
        return $this->library_path;
    }
    /**
     * Manually set the URL to the library.
     *
     * @param string $url
     * @return self
     */
    public function set_library_url(string $url) : self
    {
        $this->library_url = $url;
        return $this;
    }
    /**
     * Get the URL to the library.
     *
     * @return string
     */
    public function get_library_url() : string
    {
        return $this->library_url;
    }
    /**
     * Add tabs to the settings manager.
     *
     * @param array $tabs The tabs to add.
     * @return self
     */
    public function add_tabs(array $tabs) : self
    {
        $this->tabs = \array_merge($this->tabs, $tabs);
        return $this;
    }
    /**
     * Get the tabs.
     *
     * @return Tab_Interface[]
     */
    public function get_tabs() : array
    {
        return $this->tabs;
    }
    /**
     * Enqueue the settings API assets.
     *
     * @return void
     */
    public function register_and_enqueue_assets() : void
    {
        $asset_name = $this->plugin->get_slug() . '-settings-api';
        \wp_register_script($asset_name, $this->library_url . '/assets/js/admin/settings-panel.js', \array_merge([], Util::get_script_dependencies($this, 'admin/settings-panel.js')['dependencies']), $this->plugin->get_version(), \true);
        \wp_enqueue_script($asset_name);
        \wp_enqueue_script('wp-color-picker');
        \wp_enqueue_style('wp-color-picker');
        \wp_register_style($asset_name, $this->library_url . '/assets/css/admin/settings-panel.css', ['wp-components'], $this->plugin->get_version());
        \wp_enqueue_style($asset_name);
        // Add inline script to pass the settings to the JS.
        $slug = $this->plugin->get_slug();
        $slug = \str_replace('-', '_', $slug);
        \wp_add_inline_script($asset_name, \sprintf('window.%s = %s;', 'barn2_settings_api', \wp_json_encode($this)), 'before');
    }
    /**
     * Register the settings.
     *
     * @return void
     */
    public function render_settings() : void
    {
        echo '<div id="barn2-settings-panel"></div>';
    }
    /**
     * Register the REST API.
     *
     * @return void
     */
    public function boot_api() : void
    {
        $api = new Rest_Api($this->plugin, $this);
        $api->register_routes();
        $this->api = $api;
    }
    /**
     * Boot the settings manager.
     *
     * Yes this code can be improved but readability is more important
     * for now.
     *
     * @return void
     */
    public function boot() : void
    {
        // Attach the manager to the tabs.
        foreach ($this->tabs as $tab) {
            $tab->set_manager($this);
        }
        // Attach the manager to the sections.
        foreach ($this->tabs as $tab) {
            foreach ($tab->get_sections() as $section) {
                $section->set_manager($this);
            }
        }
        // Attach the manager to the fields.
        foreach ($this->tabs as $tab) {
            foreach ($tab->get_sections() as $section) {
                foreach ($section->get_fields() as $field) {
                    $field->set_manager($this);
                }
            }
        }
        // Now attach the uninstall section.
        $uninstall = new Uninstall($this);
        $uninstall->add_uninstall_section();
        /**
         * Fires after the settings manager has been booted.
         *
         * @param Settings_Manager $manager The settings manager.
         */
        \do_action("barn2_settings_api_{$this->plugin->get_slug()}_boot", $this);
        $this->boot_api();
    }
    /**
     * Get the helper.
     *
     * @return Helper
     */
    public function get_helper() : Helper
    {
        return $this->helper;
    }
    /**
     * Returns the list of language variables to be used in the JS.
     *
     * @return array
     */
    public function get_lang_config() : array
    {
        return ['not_found_title' => __('Page not found', 'woocommerce-discount-manager'), 'not_found_text' => __('The page you are looking for does not exist.', 'woocommerce-discount-manager'), 'not_found_back' => __('Go back', 'woocommerce-discount-manager'), 'save_changes' => __('Save changes', 'woocommerce-discount-manager'), 'settings_saved' => __('Your settings have been saved.', 'woocommerce-discount-manager'), 'check' => __('Check', 'woocommerce-discount-manager'), 'activate' => __('Activate', 'woocommerce-discount-manager'), 'deactivate' => __('Deactivate', 'woocommerce-discount-manager')];
    }
    /**
     * Get all options including defaults if they don't exist.
     *
     * @return array
     */
    public function get_all_options() : array
    {
        $database_options = \get_option($this->get_helper()->get_option_name(), []);
        $options = [];
        foreach ($this->tabs as $tab) {
            foreach ($tab->get_sections() as $section) {
                foreach ($section->get_fields() as $field) {
                    $options[$field->get_name()] = $field->get_default();
                }
            }
        }
        $options = \array_merge($options, $database_options);
        // Untransform the options.
        foreach ($options as $name => $value) {
            $field = $this->get_field_by_name($name);
            if ($field instanceof Fields\Transformable) {
                $options[$name] = $field->untransform($value);
            }
        }
        return $options;
    }
    /**
     * Find a field by its name.
     *
     * @param string $name The field name.
     * @return Fields\Field|null
     */
    public function get_field_by_name(string $name) : ?Fields\Field
    {
        foreach ($this->tabs as $tab) {
            foreach ($tab->get_sections() as $section) {
                foreach ($section->get_fields() as $field) {
                    if ($field->get_name() === $name) {
                        return $field;
                    }
                }
            }
        }
        return null;
    }
    /**
     * Find the license field.
     *
     * @return Fields\License|null
     */
    public function get_license_field() : ?Fields\License
    {
        foreach ($this->tabs as $tab) {
            foreach ($tab->get_sections() as $section) {
                foreach ($section->get_fields() as $field) {
                    if ($field instanceof Fields\License) {
                        return $field;
                    }
                }
            }
        }
        return null;
    }
    /**
     * Setup the settings manager via an array.
     *
     * The structure of the array is as follows:
     * [
     *      [
     *          'id'       => 'tab_id',
     *          'title'    => 'Tab Title',
     *          'sections' => [
     *              [
     *                  'id'     => 'section_id',
     *                  'title'  => 'Section Title',
     *                  'fields' => [
     *                      [
     *                          'name'  => 'field_id',
     *                          'label' => 'Field Title',
     *                          'type'  => 'text',
     *                          ....See the field classes constructor for more options.
     *                      ],
     *                  ],
     *              ],
     *          ],
     *      ],
     * ]
     *
     * @param array $tabs_sections_and_fields The tabs, sections and fields.
     * @return self
     */
    public function setup(array $tabs_sections_and_fields) : self
    {
        $tabs = [];
        foreach ($tabs_sections_and_fields as $defined_tab) {
            $tab = new Tabs\Tab($defined_tab['id'], $defined_tab['title']);
            foreach ($defined_tab['sections'] as $defined_section) {
                $section = new Sections\Section($defined_section['id'], $defined_section['title'], isset($defined_section['description']) ? $defined_section['description'] : '');
                foreach ($defined_section['fields'] as $field) {
                    $class = $this->get_field_class_by_type($field['type']);
                    unset($field['type']);
                    // Remove the type from the field array.
                    $args = Util::preprare_field_args($field);
                    // Now unpack array with string keys into arguments.
                    $field = new $class(...\array_values($args['supported']));
                    // Pass the field array as arguments to the field class.
                    Util::populate_field($field, $args['extra']);
                    $section->add_field($field);
                }
                $tab->add_section($section);
            }
            $tabs[] = $tab;
        }
        /**
         * Filter the tabs, sections and fields before they are added to the settings manager.
         *
         * @param array $tabs The tabs.
         * @return array
         */
        $tabs = \apply_filters("barn2_settings_api_{$this->plugin->get_slug()}_tabs", $tabs);
        $this->add_tabs($tabs);
        return $this;
    }
    /**
     * Given a field type, return the field class.
     *
     * @param string $type The field type.
     * @return string
     */
    public function get_field_class_by_type(string $type) : string
    {
        $types = ['checkbox' => Fields\Checkbox::class, 'checkboxes' => Fields\Checkboxes::class, 'color_size' => Fields\Color_Size::class, 'color' => Fields\Color::class, 'hidden' => Fields\Hidden::class, 'license' => Fields\License::class, 'multiselect' => Fields\Multiselect::class, 'number' => Fields\Number::class, 'radio' => Fields\Radio::class, 'select' => Fields\Select::class, 'text' => Fields\Text::class, 'textarea' => Fields\Textarea::class, 'toggle' => Fields\Toggle::class];
        /**
         * Filter the field class by type.
         *
         * @param string $class The field class.
         * @param string $type The field type.
         * @return string
         */
        return \apply_filters("barn2_settings_api_{$this->plugin->get_slug()}_field_class_by_type", $types[$type], $type);
    }
    /**
     * Set the validation callback.
     *
     * @param callable $validation The validation callback.
     * @return self
     */
    public function set_validation(callable $validation) : self
    {
        $this->validation = $validation;
        return $this;
    }
    /**
     * Check if the settings manager has a validation callback.
     *
     * @return bool
     */
    public function has_validation() : bool
    {
        return \is_callable($this->validation);
    }
    /**
     * Get the validation callback.
     *
     * @return callable
     */
    public function get_validation() : callable
    {
        return $this->validation;
    }
    /**
     * Given an array of field names, return the fields as an array compatible with the setup wizard.
     * This method is used by the setup wizard to get the fields to display.
     *
     * The structure of the array is as follows:
     *
     * [
     *  'field_name' => [
     *      'label' => 'Field Label',
     *      'type' => 'text',
     *      'value' => 'value',
     *      'description' => 'Field Description',
     *      'options' => [
     *          [
     *              'value' => 'value',
     *              'label' => 'Label',
     *          ]
     *      ]
     *  ]
     *
     * @param array $fields_names
     * @return array
     */
    public function get_fields_for_setup_wizard(array $fields_names) : array
    {
        $fields = [];
        foreach ($fields_names as $field_name) {
            $field = $this->get_field_by_name($field_name);
            if (!$field) {
                continue;
            }
            $fields[$field_name] = ['label' => $field->get_label(), 'type' => $field->get_type(), 'value' => $this->get_helper()->get_option($field_name), 'description' => $field->get_description()];
            if (\method_exists($field, 'get_options')) {
                $fields[$field_name]['options'] = $field->get_options();
            }
            if (!empty($field->get_conditions())) {
                $fields[$field_name]['conditions'] = $field->get_conditions();
            }
            if (\method_exists($field, 'has_callable') && $field->has_callable()) {
                $fields[$field_name]['options'] = $field->get_callable();
            }
        }
        return $fields;
    }
    /**
     * Get the settings manager as a JSON string.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        // Determine if the license should be overridden.
        $override = isset($_GET['license_override']) ? \sanitize_text_field(\wp_unslash($_GET['license_override'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return ['tabs' => $this->tabs, 'layout' => $this->layout, 'apiURL' => $this->api->get_api_namespace(), 'lang' => $this->get_lang_config(), 'options' => $this->get_all_options(), 'hasLicenseOverride' => $override === 'true'];
    }
}
