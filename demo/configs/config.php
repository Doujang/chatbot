<?php



return [

    'chatbotName' => 'demo',

    'debug' => true,

    'configBindings' => [
        \Commune\Chatbot\App\Platform\ConsoleConfig::class => [
            'allowIPs' => ['127.0.0.1'],
        ],
    ],
    'components' => [
        \Commune\Demo\App\DemoComponent::class,
//        \Commune\Chatbot\App\Components\ConfigurableComponent::class,
//        \Commune\Chatbot\App\Components\NLUExamplesComponent::class => [
//            'repository' => __DIR__ .'/repository.json'
//        ],
//        \Commune\Chatbot\App\Components\SimpleFileChatComponent::class,
//        \Commune\Chatbot\App\Components\RasaComponent::class => [
//            'output' => __DIR__ .'/nlu.md',
//        ],
//        \Commune\Components\Predefined\IntentsIntComponent::class,
//        \Commune\Chatbot\App\Components\SimpleChatComponent::class => [
//            'default' => 'demo',
//            'resources' => [
//                [
//                    'id' => 'demo',
//                    'resource' => __DIR__ .'/chats/example.yml'
//                ]
//            ]
//        ],
    ],
    'processProviders' => [
        \Commune\Demo\App\Providers\EventServiceProvider::class,
    ],
    'conversationProviders' => [
        \Commune\Chatbot\App\Drivers\Demo\CacheServiceProvider::class,
        \Commune\Chatbot\App\Drivers\Demo\SessionServiceProvider::class,
    ],
    'chatbotPipes' =>
        [
            'onUserMessage' => [
                \Commune\Chatbot\App\ChatPipe\MessengerPipe::class,
                \Commune\Chatbot\App\ChatPipe\ChattingPipe::class,
                \Commune\Chatbot\OOHost\OOHostPipe::class,
            ],
        ],
    'translation' =>
        [
            'loader' => 'php',
            'resourcesPath' => __DIR__ . '/../../src/Chatbot/App/trans',
            'defaultLocale' => 'zh',
            'cacheDir' => NULL,
        ],
    'logger' =>
        [
            'path' => __DIR__ . '/cache/tmp.log',
            'days' => 0,
            'level' => 'debug',
            'bubble' => true,
            'permission' => NULL,
            'locking' => false,
        ],
    'defaultMessages' =>
        [
            'platformNotAvailable' => 'system.platformNotAvailable',
            'chatIsTooBusy' => 'system.chatIsTooBusy',
            'systemError' => 'system.systemError',
            'farewell' => 'dialog.farewell',
            'messageMissMatched' => 'dialog.missMatched',
        ],
    'eventRegister' =>[
        
    ],
        
    'host' => [
        'rootContextName' => \Commune\Demo\App\Contexts\TestCase::class,
        'sessionPipes' => [
            \Commune\Chatbot\App\SessionPipe\EventMsgPipe::class,
            \Commune\Chatbot\App\Commands\UserCommandsPipe::class,
            \Commune\Chatbot\App\Commands\AnalyserPipe::class,
            \Commune\Chatbot\App\SessionPipe\MarkedIntentPipe::class,
            \Commune\Chatbot\App\SessionPipe\NavigationPipe::class,
            // \Commune\Chatbot\App\Components\Rasa\RasaNLUPipe::class,
        ],
        'hearingFallback' => \Commune\Chatbot\App\Components\SimpleChat\Callables\SimpleChatAction::class,
    ] ,

];