<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Plugins;

use Woogostores\Plugin\Inc\EventManagement\SubscriberInterface;

class PDFConfigSubscriber implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'wpo_wcpdf_tmp_path' => fn ($tmp_base) => '/tmp/',
            'wpo_wcpdf_use_path' => fn () => false,
            'wpo_wcpdf_use_path' => fn ($tmp_base) => '/tmp/',
        ];
    }
}
