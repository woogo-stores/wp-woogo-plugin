<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Plugins;

use Woogostores\Plugin\Inc\EventManagement\SubscriberInterface;

class ElementorConfigSubscriber implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'elementor/files/temp-dir' => fn ($tmp_base) => '/tmp/elementor/',
        ];
    }
}
