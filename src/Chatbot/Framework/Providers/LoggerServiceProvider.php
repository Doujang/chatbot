<?php


namespace Commune\Chatbot\Framework\Providers;


use Commune\Chatbot\Config\ChatbotConfig;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Monolog\Logger as Monolog;

class LoggerServiceProvider extends BaseServiceProvider
{
    const IS_PROCESS_SERVICE_PROVIDER = true;

    public function boot($app): void
    {
    }

    public function register(): void
    {
        $this->app->instance(
            LoggerInterface::class,
            $this->makeLogger()
        );
    }

    protected function makeLogger() : LoggerInterface
    {
        $app = $this->app;
        /**
         * @var ChatbotConfig $chatbotConfig
         */
        $chatbotConfig = $app[ChatbotConfig::class];
        $config = $chatbotConfig->logger;

        $level = Monolog::toMonologLevel($config->level);

        if ($config->days > 0) {
            $handler = new RotatingFileHandler(
                $config->path,
                $config->days,
                $level,
                $config->bubble,
                $config->permission,
                $config->locking
            );
        } else {
            $handler = new StreamHandler(
                $config->path,
                $level,
                $config->bubble,
                $config->permission,
                $config->locking
            );
        }

        $logger = new Monolog($config->name, [
            $handler
        ]);

        return $logger;
    }


}