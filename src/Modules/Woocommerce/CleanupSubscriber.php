<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Woocommerce;

use Woogostores\Plugin\Inc\EventManagement\SubscriberInterface;

class CleanupSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Remove order total from my account orders
            'woocommerce_my_account_my_orders_columns' => function ($order) {
                unset($order['order-total']);
                return $order;
            },
            'wp_dashboard_setup' => [function () {
                // Disable status dashboard widget
                remove_meta_box('woocommerce_dashboard_status', 'dashboard', 'normal');
                // Disable reviews dashboard widget
                remove_meta_box('woocommerce_dashboard_recent_reviews', 'dashboard', 'normal');
                // Disable setup widget
                remove_meta_box('wc_admin_dashboard_setup', 'dashboard', 'normal');
            },40],
            // Remove order count from admin menu
            'woocommerce_include_processing_order_count_in_menu' => '__return_false',
            // Hide tags, featured and type admin columns from the product list
            'manage_edit-product_columns' => function ($column_headers) {
                unset($column_headers['product_tag']);
                unset($column_headers['featured']);
                unset($column_headers['product_type']);
                return $column_headers;
            },
            'woocommerce_background_image_regeneration' => '__return_false',
            'wp_print_scripts' => fn () => wp_dequeue_script('wc-password-strength-meter'),
            'woocommerce_product_export_batch_limit' => [fn () => 5000,999],
            'woocommerce_allow_marketplace_suggestions'=> '__return_false',
            'woocommerce_helper_suppress_admin_notices' => '__return_true',
            'woocommerce_show_admin_notice' => [function ($notice_enabled, $notice) {
                if ('wc_admin' === $notice) {
                    return false;
                }
                return $notice_enabled;
            },10,2],
            // Disable the WooCommerce Admin (Analytics)
            'woocommerce_admin_disabled' => fn () => (getenv('WOOGO_DISABLE_WOOCOMMERCE_ADMIN') === false ? false : filter_var(getenv('WOOGO_DISABLE_WOOCOMMERCE_ADMIN'), FILTER_VALIDATE_BOOL)),
            //Disable the WooCommere Marketing Hub'
            'woocommerce_admin_features' =>  function ($features) {
                $marketing = array_search('marketing', $features);
                unset($features[$marketing]);
                return $features;
            },
            // Supress WooCommerce Helper Admin Notices
            'woocommerce_helper_suppress_admin_notices' => '__return_true',
            'woocommerce_menu_order_count' => '__return_false',
            'wp_lazy_loading_enabled' => '__return_false',
            'woocommerce_enable_nocache_headers' => '__return_false',
            'admin_menu' => [function () {
                //Hide "WooCommerce → Marketplace".
                remove_submenu_page('woocommerce', 'wc-addons');
                //Hide "WooCommerce → My Subscriptions".
                remove_submenu_page('woocommerce', 'wc-addons&section=helper');
            },71],
            'admin_init' => fn () => rand(1, 10) === 1 ? update_option('woocommerce_allow_tracking', 'false') : false,

        ];
    }
}
