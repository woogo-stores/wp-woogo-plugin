<?php

declare(strict_types=1);

namespace Woogostores\Plugin\Modules\Woogo;

use PDO;
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
            'auto_core_update_send_email' => '__return_false',
            'auto_plugin_update_send_email' => '__return_false',
            'auto_theme_update_send_email' => '__return_false',
            'admin_init' => fn () => remove_action('admin_notices', 'update_nag', 3),
            'wp_mail' => ['disableEmails', PHP_INT_MAX],
            'user_has_cap' => ['disableOptionPage', 10, 3],
            'wp_editor_set_quality' => fn () => 96, //keep images at high quality
            'jpeg_quality' => fn () => 96, //Jpeg has its own setting.
        ];
    }

    public function disableOptionPage($allCaps, $caps, $args): array
    {
        if ($_SERVER['SCRIPT_NAME'] == '/wp/wp-admin/options.php' && $_SERVER['REQUEST_METHOD'] == 'GET') {
            $allCaps['manage_options'] = false;
        }
        return $allCaps;
    }

    public function preOptionBlogPublic()
    {
        if (defined('WP_CLI')) {
            return;
        }

        if (str_contains($_SERVER['SERVER_NAME'], 'mywoogo.dev') || str_ends_with($_SERVER['SERVER_NAME'], '.test')) {
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
        $url = getenv('WOOGO_PROJECT_ID') === false ? 'https://app.woogostores.com' : 'https://app.woogostores.com/project/edit/'.getenv('WOOGO_PROJECT_ID');

        $message = sprintf(
            __('%1$s <strong>Search engine indexing has been disabled.</strong> You need to validate your domain to enable it! <a href="%2$s" target="_blank">Click here to validate your domain.</a>', 'woogo'),
            '<strong>Woogo Stores:</strong>',
            $url
        );

        echo "<div class='notice notice-error'><p>{$message}</p></div>";
    }


    private function noticeEmailsDisabled()
    {
        $url = getenv('WOOGO_PROJECT_ID') === false ? 'https://app.woogostores.com' : 'https://app.woogostores.com/project/edit/'.getenv('WOOGO_PROJECT_ID');
        $message = sprintf(
            __('%1$s <strong>Emails have been disabled</strong>. You need to validate your domain to enable them! <a href="%2$s" target="_blank">Click here to validate your domain.</a>', 'woogo'),
            '<strong>Woogo Stores:</strong>',
            $url
        );

        echo "<div class='notice notice-error'><p>{$message}</p></div>";
    }
}
