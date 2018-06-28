<?php

namespace Tests\AppBundle\Validator;

use AppBundle\Dto\Card;
use AppBundle\Exception\CardDuplicationException;
use AppBundle\Validator\CardValidator;
use PHPUnit\Framework\TestCase;

class CardValidatorTest extends TestCase
{
    /** @var CardValidator */
    private $cardValidatorUnderTest;

    protected function setUp()
    {
        $this->cardValidatorUnderTest = new CardValidator();
    }

    public function cardsProvider()
    {
        return [
            'Return True When Valid Cards With The Same Colors' => [
                new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
                true
            ],
            'Return False When Valid Cards With Different Colors and Values' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
                false
            ],
            'Return True When Valid Cards With The Same Values' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
                true
            ],
            'Queen for all' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_FIVE),
                new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
                true
            ],
            'All for Queen' => [
                new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
                new Card(Card::COLOR_SPADE, Card::VALUE_FIVE),
                true
            ],
        ];
    }

    /**
     * @dataProvider cardsProvider
     *
     * @param Card $activeCard
     * @param Card $newCard
     * @param bool $expected
     *
     * @throws CardDuplicationException
     */
    public function testShouldValidCards(Card $activeCard, Card $newCard, $expected)
    {
        // When
        $actual = $this->cardValidatorUnderTest->valid($activeCard, $newCard, $activeCard->getColor());

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @throws CardDuplicationException
     */
    public function testShouldThrowCardDuplicationExceptionWhenValidCardsAreTheSame()
    {
        // Expect
        $this->expectException(CardDuplicationException::class);
        $this->expectExceptionMessage('Valid card get the same cards: 5 spade');

        // Given
        $card = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);

        // When
        $this->cardValidatorUnderTest->valid($card, $card, $card->getColor());
    }

    /**
     * @throws CardDuplicationException
     */
    public function testShouldReturnTrueWhenAceChangeAcceptColorToDifferent()
    {
        // Given
        $requestedColor = Card::COLOR_HEART;
        $activeCard = new Card(Card::COLOR_SPADE, Card::VALUE_ACE);
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_FIVE);

        // When
        $actual = $this->cardValidatorUnderTest->valid($activeCard, $newCard, $requestedColor);

        // Then
        $this->assertTrue($actual);
    }

    /**
     * @throws CardDuplicationException
     */
    public function testShouldReturnFalseWhenAceChangeAcceptColorToDifferentAndYouTryPutTheSame()
    {
        // @todo
    }
}