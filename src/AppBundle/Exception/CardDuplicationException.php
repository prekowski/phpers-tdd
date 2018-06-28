<?php

namespace AppBundle\Exception;

use AppBundle\Dto\Card;

class CardDuplicationException extends \Exception
{
    public function __construct(Card $card, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf("Valid card get the same cards: %s %s", $card->getValue(), $card->getColor());
        parent::__construct($message, $code, $previous);
    }

}