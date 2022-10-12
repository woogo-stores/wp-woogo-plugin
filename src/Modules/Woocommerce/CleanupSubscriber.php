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
            'woocommerce_show_marketplace_suggestions' => '__return_false',
            'woocommerce_helper_suppress_admin_notices' => '__return_true',
            'woocommerce_admin_payment_gateway_suggestion_specs' => fn () => [],
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
            'woocommerce_defer_transactional_emails' => '__return_true',
            'jetpack_just_in_time_msgs' => '__return_false',
            'jetpack_show_promotions' => '__return_false',
            'woocommerce_prevent_automatic_wizard_redirect' => '__return_false',
            'woocommerce_enable_setup_wizard' => '__return_false',
            'use_block_editor_for_post' => '__return_false',
            'use_block_editor_for_post_type' => '__return_false',
            'admin_menu' => [function () {
                //Hide "WooCommerce → Marketplace".
                remove_submenu_page('woocommerce', 'wc-addons');
                //Hide "WooCommerce → My Subscriptions".
                remove_submenu_page('woocommerce', 'wc-addons&section=helper');
            },71],
            'admin_init' => function () {
                fn () => rand(1, 10) === 1 ? update_option('woocommerce_allow_tracking', 'false') : false;
                remove_submenu_page('index.php', 'update-core.php');
            },
            'wp_enqueue_scripts' => ['disableCartFragments',999],
            'plugins_loaded' => [fn () => remove_action('load-update-core.php', 'wp_update_plugins'),999],
        ];
    }

    public function disableCartFragments()
    {
        global $wp_scripts;

        $handle = 'wc-cart-fragments';
        if (isset($wp_scripts->registered[ $handle ]) && $wp_scripts->registered[ $handle ]) {
            $load_cart_fragments_path = $wp_scripts->registered[ $handle ]->src;
            $wp_scripts->registered[ $handle ]->src = null;
            wp_add_inline_script(
                'jquery',
                '
            function woogo_getCookie(name) {
                var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
                return v ? v[2] : null;
            }

            function woogo_check_wc_cart_script() {
            var cart_src = "' . $load_cart_fragments_path . '";
            var script_id = "woogo_loaded_wc_cart_fragments";

                if( document.getElementById(script_id) !== null ) {
                    return false;
                }

                if( woogo_getCookie("woocommerce_cart_hash") ) {
                    var script = document.createElement("script");
                    script.id = script_id;
                    script.src = cart_src;
                    script.async = true;
                    document.head.appendChild(script);
                }
            }

            woogo_check_wc_cart_script();
            document.addEventListener("click", function(){setTimeout(woogo_check_wc_cart_script,1000);});
            '
            );
        }
    }
}
