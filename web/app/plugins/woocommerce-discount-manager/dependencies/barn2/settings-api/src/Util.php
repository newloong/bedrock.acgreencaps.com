<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields\Field;
/**
 * Utility class.
 */
class Util
{
    /**
     * Get the script dependencies.
     *
     * @param Settings_Manager $manager The settings manager.
     * @param string           $filename The filename.
     * @return array
     */
    public static function get_script_dependencies(Settings_Manager $manager, string $filename) : array
    {
        $script_dependencies_file = $manager->get_library_path() . '/assets/js/wp-dependencies.json';
        $script_dependencies = \file_exists($script_dependencies_file) ? \file_get_contents($script_dependencies_file) : \false;
        // bail if the wp-dependencies.json file doesn't exist
        if ($script_dependencies === \false) {
            return ['dependencies' => [], 'version' => ''];
        }
        $script_dependencies = \json_decode($script_dependencies, \true);
        // if the asset doesn't exist, and the path is relative to the 'js' directory then try a full path
        if (!isset($script_dependencies[$filename]) && \strpos($filename, './assets/js') === \false && isset($script_dependencies[\sprintf('./assets/js/%s', $filename)])) {
            $filename = \sprintf('./assets/js/%s', $filename);
        }
        if (!isset($script_dependencies[$filename])) {
            return ['dependencies' => [], 'version' => ''];
        }
        return $script_dependencies[$filename];
    }
    /**
     * Split supported and extra arguments.
     * Supported arguments are those that are supported by the field.
     * Extra arguments are those that are supported by the field but not all fields share the same arguments.
     *
     * @param arrat $args
     * @return array
     */
    public static function preprare_field_args($args) : array
    {
        $supported_args = ['name', 'label', 'description', 'tooltip', 'default_value', 'attributes', 'conditions'];
        $supported = [];
        $extra = [];
        foreach ($args as $key => $value) {
            if (\in_array($key, $supported_args, \true)) {
                $supported[$key] = $value;
            } else {
                $extra[$key] = $value;
            }
        }
        return ['supported' => $supported, 'extra' => $extra];
    }
    /**
     * Given a field, populate it with the given arguments.
     *
     * @param Field $field The field.
     * @param array $args  The arguments.
     * @return void
     */
    public static function populate_field(Field &$field, array $args) : void
    {
        foreach ($args as $key => $value) {
            $method = \sprintf('set_%s', $key);
            if (\method_exists($field, $method)) {
                $field->{$method}($value);
            }
        }
    }
}
