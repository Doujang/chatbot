<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\IMindDef;

use Commune\Blueprint\Exceptions\Logic\InvalidArgumentException;
use Commune\Blueprint\Ghost\MindDef\EntityDef;
use Commune\Blueprint\Ghost\MindReg\SynonymReg;
use Commune\Blueprint\Ghost\MindMeta\EntityMeta;
use Commune\Support\Option\Meta;
use Commune\Support\Option\Wrapper;
use Commune\Support\WordSearch\Tree;


/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IEntityDef implements EntityDef
{
    /**
     * @var EntityMeta
     */
    protected $meta;

    /**
     * @var Tree
     */
    protected $tree;

    /**
     * IEntityDef constructor.
     * @param EntityMeta $meta
     */
    public function __construct(EntityMeta $meta)
    {
        $this->meta = $meta;
    }

    /**
     * @param Meta $meta
     * @return Wrapper
     */
    public static function wrapMeta(Meta $meta): Wrapper
    {
        if (!$meta instanceof EntityMeta) {
            throw new InvalidArgumentException('meta should be subclass of '. EntityMeta::class);
        }

        return new static($meta);
    }

    protected function getTree(SynonymReg $reg) : Tree
    {
        if (isset($this->tree)) {
            return $this->tree;
        }

        $values = $this->getValues();

        $keywords = [];
        foreach ($values as $value) {
            $value = strval(trim($value));
            $keywords[$value] = $value;

            // 同义词
            if ($reg->hasDef($value)) {
                $synonyms = $reg->getDef($value)->getValues();
                foreach ($synonyms as  $synonym) {
                    $synonym = strval(trim($synonym));
                    $keywords[$synonym] =  $value;
                }
            }
        }

        return $this->tree = new Tree($keywords);
    }


    public function match(string $text, SynonymReg $reg): array
    {
        $tree = $this->getTree($reg);
        $matches = $tree->search($text);
        return array_keys($matches);
    }



    public function getName(): string
    {
        return $this->meta->name;
    }

    public function getTitle(): string
    {
        return $this->meta->title;
    }

    public function getDescription(): string
    {
        return $this->meta->desc;
    }


    public function getValues(): array
    {
        return $this->meta->values;
    }

    public function getBlacklist(): array
    {
        return $this->meta->blacklist;
    }

    public function toMeta(): Meta
    {
        return $this->meta;
    }


}