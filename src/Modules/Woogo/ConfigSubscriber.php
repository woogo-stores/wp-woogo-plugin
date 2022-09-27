<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Woogo;

use Woogostores\Plugin\Inc\EventManagement\SubscriberInterface;

class ConfigSubscriber implements SubscriberInterface
{
    public bool $publicBlog = true;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'pre_option_blog_public' => 'preOptionBlogPublic',
            'admin_notices' => 'adminNotices',
            'wp_mail' => 'disableEmails',
        ];
    }

    public function preOptionBlogPublic()
    {
        if(defined('WP_CLI') ){
            return;
        }

        if ( str_contains($_SERVER['SERVER_NAME'], 'mywoogo.dev') || str_ends_with($_SERVER['SERVER_NAME'], '.test')) {
            $this->publicBlog = false;
            return 0;
        }
    }

    public function adminNotices()
    {
        if (!$this->publicBlog) {
            $this->noticeEmailsDisabled();
            $this->noticeIndexingDisabled();
        }
    }

    public function disableEmails($args)
    {
        if (!$this->publicBlog) {
            unset($args['to']);
        }
        return $args;
    }

    private function noticeIndexingDisabled()
    {
        $message = sprintf(
            __('%1$s <strong>Search engine indexing has been disabled.</strong> You need to validate your domain to enable it! <a href="%2$s" target="_blank">Click here to validate your domain.</a>', 'woogo'),
            '<strong>Woogo Stores:</strong>',
            'https://app.woogostores.com/project/edit/'.getenv('WOOGO_PROJECT_ID')
        );

        echo "<div class='notice notice-error'><p>{$message}</p></div>";
    }


    private function noticeEmailsDisabled()
    {
        $message = sprintf(
            __('%1$s <strong>Emails have been disabled</strong>. You need to validate your domain to enable them! <a href="%2$s" target="_blank">Click here to validate your domain.</a>', 'woogo'),
            '<strong>Woogo Stores:</strong>',
            'https://app.woogostores.com/project/edit/'.getenv('WOOGO_PROJECT_ID')
        );

        echo "<div class='notice notice-error'><p>{$message}</p></div>";

    }
}
