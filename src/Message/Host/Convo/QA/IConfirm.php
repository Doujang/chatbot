<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Message\Host\Convo\QA;

use Commune\Blueprint\Ghost\Ucl;
use Commune\Protocals\Comprehension;
use Commune\Blueprint\Ghost\Cloner;
use Commune\Blueprint\Ghost\MindDef\EmotionDef;
use Commune\Protocals\HostMsg\Convo\QA\AnswerMsg;
use Commune\Protocals\HostMsg\Convo\QA\Confirm;
use Commune\Protocals\HostMsg\Convo\QA\Confirmation;
use Commune\Support\Struct\Struct;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IConfirm extends IQuestionMsg implements Confirm
{
    const POSITIVE_INDEX = 1;
    const NEGATIVE_INDEX = 0;

    protected $acceptAnyTextAsValue = false;

    public function __construct(
        string $query,
        bool $default = null,
        string $positive = null,
        string $negative = null,
        array $routes = []
    )
    {
        $suggestions = [];

        $suggestions[self::POSITIVE_INDEX] = $positive ?? self::POSITIVE_LANG;
        $suggestions[self::NEGATIVE_INDEX] = $negative ?? self::NEGATIVE_LANG;

        $default = isset($default)
            ? (
                $default ? self::POSITIVE_INDEX : self::NEGATIVE_INDEX
            )
            : null;

        parent::__construct(
            $query,
            $default,
            $suggestions,
            $routes
        );
    }

    public static function create(array $data = []): Struct
    {
        return new static(
            $data['query'] ?? '',
            $data['default'] === self::POSITIVE_INDEX,
            $data['suggestions'][self::POSITIVE_INDEX] ?? null,
            $data['suggestions'][self::NEGATIVE_INDEX] ?? null,
            $data['routes'] ?? []
        );
    }

    protected function parseAnswerByMatcher(Cloner $cloner): ? AnswerMsg
    {
        $matcher = $cloner->matcher;

        if ($matcher->refresh()->isPositive()->truly()) {
            return $this->newAnswer($this->_data['suggestions'][self::POSITIVE_INDEX], self::POSITIVE_INDEX);
        }

        if($matcher->refresh()->isNegative()->truly()) {
            return $this->newAnswer($this->_data['suggestions'][self::NEGATIVE_INDEX], self::NEGATIVE_INDEX);
        }

        return null;
    }

    protected function parseInputText(string $text): string
    {
        switch($text) {
            case 'y' :
            case '1' :
                return self::POSITIVE_INDEX;
            case 'n' :
            case '0' :
                return self::NEGATIVE_LANG;
            default:
                return parent::parseInputText($text);
        }
    }

    /**
     * @param Confirmation $answer
     * @param Comprehension $comprehension
     * @return AnswerMsg
     */
    protected function setAnswerToComprehension(AnswerMsg $answer, Comprehension $comprehension): AnswerMsg
    {
        $positive = $answer->isPositive();

        $comprehension->emotion->setEmotion(EmotionDef::EMO_POSITIVE, $positive);
        $comprehension->emotion->setEmotion(EmotionDef::EMO_NEGATIVE, !$positive);

        return $answer;
    }

    protected function newAnswer(string $answer, string $choice = null): AnswerMsg
    {
        return new IConfirmation([
            'answer' => $answer,
            'choice' => intval($choice) ? self::POSITIVE_INDEX : self::NEGATIVE_INDEX
        ]);
    }


    public function setPositive(string $suggestion, Ucl $ucl = null) : Confirm
    {
        $this->addSuggestion(
            $suggestion,
            self::POSITIVE_INDEX,
            $ucl ? $ucl->encode() : null
        );
        return $this;
    }

    public function setNegative(string $suggestion, Ucl $ucl = null) : Confirm
    {
        $this->addSuggestion(
            $suggestion,
            self::NEGATIVE_INDEX,
            $ucl ? $ucl->encode() : null
        );
        return $this;
    }

    public function getPositiveSuggestion(): string
    {
        return $this->_data['suggestions'][self::POSITIVE_INDEX] ?? 'y';
    }

    public function getNegativeSuggestion(): string
    {
        return $this->_data['suggestions'][self::NEGATIVE_INDEX] ?? 'n';
    }


    public function getText(): string
    {
        $query = $this->query;
        $p = $this->getPositiveSuggestion();
        $n = $this->getNegativeSuggestion();

        return "$query ($p|$n)";
    }
}