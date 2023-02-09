<?php

declare(strict_types=1);

namespace tests;

use E_LEXER_TOKENS;
use Exception;
use LexicalAnalysis;
use LexicalToken;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    private function getLexer(string $input): LexicalAnalysis
    {
        return new LexicalAnalysis($input);
    }

    private function testCaseProvider(): array
    {
        return [
            [
                'input'    => 'true false',
                'expected' => [
                    new LexicalToken('true', E_LEXER_TOKENS::KEYWORD_TRUE),
                    new LexicalToken('false', E_LEXER_TOKENS::KEYWORD_FALSE),
                ],
            ],
            [
                'input'    => 'MOVE ADD',
                'expected' => [
                    new LexicalToken('MOVE', E_LEXER_TOKENS::COMMAND),
                    new LexicalToken('ADD', E_LEXER_TOKENS::COMMAND),
                ],
            ],
        ];
    }

    /**
     * @dataProvider testCaseProvider
     */
    public function test(string $input, array $expected): void
    {
        $lexer = $this->getLexer($input);
        $actual = [];
        $token = $lexer->getNextToken();
        while ($token->getType() !== E_LEXER_TOKENS::END_OF_FILE) {
            $actual[] = $token;
            $token = $lexer->getNextToken();
        }
        $this->assertEquals($expected, $actual);
    }
}
