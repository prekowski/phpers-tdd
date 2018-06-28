<?php

namespace AppBundle\Validator;

use AppBundle\Dto\Card;
use AppBundle\Exception\CardDuplicationException;

class CardValidator
{
    /**
     * @param Card   $activeCard
     * @param Card   $newCard
     * @param string $acceptColor
     *
     * @return bool
     *
     * @throws CardDuplicationException
     */
    public function valid(Card $activeCard, Card $newCard, $acceptColor)
    {
        if ($activeCard === $newCard) {
            throw new CardDuplicationException($newCard);
        }

        return $activeCard->getColor() === $newCard->getColor()
            || $activeCard->getValue() === $newCard->getValue()
            || $newCard->getValue() === Card::VALUE_QUEEN
            || $activeCard->getValue() === Card::VALUE_QUEEN
            || $acceptColor === $newCard->getColor();
    }
}