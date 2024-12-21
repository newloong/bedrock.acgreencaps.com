<?php

/**
 * @package   Barn2\barn2-settings-api
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Fields\Checkbox;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Sections\Section;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Settings_API\Tabs\Tab_Interface;
/**
 * This class handles the injection of the
 * uninstall checkbox into the settings page.
 */
class Uninstall
{
    /**
     * The settings manager.
     *
     * @var Settings_Manager
     */
    private $manager;
    /**
     * Constructor.
     *
     * @param Settings_Manager $manager The settings manager.
     */
    public function __construct(Settings_Manager $manager)
    {
        $this->manager = $manager;
    }
    /**
     * Add uninstall section and checkbox to the first tab.
     *
     * @return void
     */
    public function add_uninstall_section()
    {
        /** @var Tab_Interface $first_tab */
        $first_tab = $this->manager->get_tabs()[0] ?? null;
        if (!$first_tab || !$first_tab instanceof Tab_Interface) {
            return;
        }
        // translators: %s is the plugin name.
        $uninstall_section = new Section('uninstall_section', \sprintf(esc_html__('Uninstalling %s', 'woocommerce-discount-manager'), $this->manager->get_plugin()->get_name()));
        $uninstall_checkbox = (new Checkbox('delete_data', esc_html__('Delete data on uninstall', 'woocommerce-discount-manager')))->set_help(\sprintf(esc_html__('Permanently delete all %s settings and data when uninstalling the plugin.', 'woocommerce-discount-manager'), $this->manager->get_plugin()->get_name()))->set_default(\false);
        $uninstall_section->add_field($uninstall_checkbox);
        $first_tab->add_section($uninstall_section);
    }
}
