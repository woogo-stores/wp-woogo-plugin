<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Licenses;

use Woogostores\Plugin\Inc\EventManagement\SubscriberInterface;

class DisablerSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        $events = [];

        if(class_exists('Atum\\Components\\AtumAdminNotices')){
            $events['woocommerce_loaded'] = fn () => remove_action('current_screen', [AtumAdminNotices::get_instance(), 'register_notices']);
        }

        return $events;
    }
}
