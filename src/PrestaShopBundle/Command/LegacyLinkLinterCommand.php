<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Command;

use PrestaShopBundle\Routing\Linter\AdminRouteProvider;
use PrestaShopBundle\Routing\Linter\LegacyLinkLinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Checks if all admin routes are configured with _legacy_link
 */
class LegacyLinkLinterCommand extends Command
{
    /**
     * @var LegacyLinkLinter
     */
    private $legacyLinkLinter;

    /**
     * @var AdminRouteProvider
     */
    private $adminRouteProvider;

    /**
     * The _legacy_link configuration is not relevant for these routes, no need to apply the linter on them
     */
    private const ROUTE_WHITE_LIST = [
        'admin_alias_search_search_term_action',
        'admin_all_attribute_groups',
        'admin_catalog_price_rules_list_for_product',
        'admin_categories_get_categories_tree',
        'admin_close_showcase_card',
        'admin_common_notifications_ack',
        'admin_common_pagination',
        'admin_common_reset_search',
        'admin_common_reset_search_by_filter_id',
        'admin_common_sidebar',
        'admin_country_states',
        'admin_currencies_update_live_exchange_rates',
        'admin_errors_enable_debug_mode',
        'admin_feature_get_feature_values',
        'admin_international_translations_modify',
        'admin_legacy_controller_route',
        'admin_link_block_create',
        'admin_link_block_create_process',
        'admin_link_block_delete',
        'admin_link_block_edit',
        'admin_link_block_edit_process',
        'admin_link_block_update_positions',
        'admin_localization_advanced_save',
        'admin_localization_local_units_save',
        'admin_metas_seo_options_save',
        'admin_metas_seo_options_save',
        'admin_metas_shop_urls_save',
        'admin_metas_url_schema_save',
        'admin_module_configure_action',
        'admin_module_import',
        'admin_module_manage_action',
        'admin_module_manage_action_bulk',
        'admin_module_manage_update_all',
        'admin_order_preferences_gift_options_save',
        'admin_product_catalog',
        'admin_product_form',
        'admin_products_attribute_groups',
        'admin_products_combinations',
        'admin_products_combinations_bulk_combination_form',
        'admin_products_combinations_bulk_delete',
        'admin_products_combinations_bulk_edit_combination',
        'admin_products_combinations_delete_combination',
        'admin_products_combinations_edit_combination',
        'admin_products_combinations_generate',
        'admin_products_combinations_ids',
        'admin_products_combinations_update_combination_from_listing',
        'admin_products_delete_from_shop',
        'admin_products_delete_from_shop_group',
        'admin_products_disable_for_all_shops',
        'admin_products_disable_for_shop_group',
        'admin_products_download_virtual_product_file',
        'admin_products_enable_for_all_shops',
        'admin_products_enable_for_shop_group',
        'admin_products_grid_category_filter',
        'admin_products_grid_shop_previews',
        'admin_products_light_list',
        'admin_products_quantity',
        'admin_products_reset_grid_search',
        'admin_products_search',
        'admin_products_search_associations',
        'admin_products_search_combinations',
        'admin_products_search_product_combinations',
        'admin_products_specific_prices_create',
        'admin_products_specific_prices_delete',
        'admin_products_specific_prices_edit',
        'admin_products_specific_prices_list',
        'admin_products_toggle_status_for_shop',
        'admin_security_compromised',
        'admin_security_sessions_customer_search',
        'admin_security_sessions_employee_search',
        'admin_shipping_preferences_carrier_options_save',
        'admin_warehouse_refresh_product_warehouse_combination_form',
    ];

    public function __construct(LegacyLinkLinter $legacyLinkLinter, AdminRouteProvider $adminRouteProvider)
    {
        parent::__construct();
        $this->legacyLinkLinter = $legacyLinkLinter;
        $this->adminRouteProvider = $adminRouteProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:linter:legacy-link')
            ->setDescription('Checks if _legacy_link is configured in BackOffice routes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $unconfiguredRoutes = $this->getUnconfiguredRoutes();
        $io = new SymfonyStyle($input, $output);

        if (!empty($unconfiguredRoutes)) {
            $io->warning(sprintf(
                '%s routes are not configured with _legacy_link:',
                count($unconfiguredRoutes)
            ));
            $io->listing($unconfiguredRoutes);

            return 1;
        }

        $io->success('There is no routes without _legacy_link settings');

        return 0;
    }

    /**
     * Returns routes that are missing _legacy_link configuration
     *
     * @return array
     */
    private function getUnconfiguredRoutes()
    {
        $routes = $this->adminRouteProvider->getRoutes();
        $unconfiguredRoutes = [];

        foreach ($routes as $routeName => $route) {
            if (in_array($routeName, self::ROUTE_WHITE_LIST) || true === $this->legacyLinkLinter->lint('_legacy_link', $route)) {
                continue;
            }
            $unconfiguredRoutes[] = $routeName;
        }

        return $unconfiguredRoutes;
    }
}
