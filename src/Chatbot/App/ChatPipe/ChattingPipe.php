<?php

namespace Commune\Chatbot\App\ChatPipe;


use Commune\Chatbot\Blueprint\Conversation\Conversation;
use Commune\Chatbot\App\ChatPipe\Chatting\ReceiveIncomingMessage;
use Commune\Chatbot\App\ChatPipe\Chatting\SendingOutgoingMessage;
use Commune\Chatbot\Config\ChatbotConfig;
use Commune\Chatbot\Contracts\CacheAdapter;
use Commune\Chatbot\Contracts\EventDispatcher;
use Commune\Chatbot\Framework\Pipeline\ChatbotPipeImpl;

class ChattingPipe extends ChatbotPipeImpl
{

    /**
     * @var CacheAdapter
     */
    public $cache;

    /**
     * @var EventDispatcher
     */
    public $dispatcher;

    /**
     * @var ChatbotConfig
     */
    public $config;


    public function __construct(
        CacheAdapter $cache,
        EventDispatcher $dispatcher,
        ChatbotConfig $config
    )
    {
        $this->cache = $cache;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }


    /*----------- on user message -----------*/


    public function handleUserMessage(Conversation $conversation, \Closure $next): Conversation
    {
        $chat = $conversation->getChat();
        $chatId = $chat->getChatId();

        // 锁chat 失败
        $locked = $this->lockChat($chatId);

        // 触发接受信息事件.
        $incomingMessage = $conversation->getIncomingMessage();
        $this->dispatcher->dispatch(
            new ReceiveIncomingMessage(
                $incomingMessage,
                $locked
            )
        );

        // 没锁到就直接返回好了.
        if (! $locked) {
            $conversation->monolog()->warning(
                $this->config
                    ->defaultMessages
                    ->chatIsTooBusy
            );
            return $conversation;
        }

        /**
         * @var Conversation $replyConversation
         */
        $replyConversation = $next($conversation);

        $replies = $replyConversation->getOutgoingMessages();
        // 记录消息的事件.
        $this->dispatcher->dispatch(
            new SendingOutgoingMessage($replies)
        );

        $this->unlockChat($chatId);
        return $replyConversation;

    }
    /*----------- on message to user -----------*/



    /*----------- finally -----------*/

    public function onUserMessageFinally(Conversation $conversation): void
    {
        $this->unlockChat($conversation->getChat()->getChatId());
    }


    /*----------- chat 管理 -----------*/

    public function lockChat(string $chatId) : bool
    {
        return $this->cache->lock($this->getChatLockerKey($chatId), 2);
    }

    public function unlockChat(string $chatId) : void
    {
        $this->cache->forget($this->getChatLockerKey($chatId));
    }

    public function getChatLockerKey(string $chatId) : string
    {
        return "commune:chatbot:chatLocker:" . $chatId;
    }

}