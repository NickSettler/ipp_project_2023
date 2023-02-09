<?php

enum E_LEXER_STATES
{
    case START;
    case KEYWORD;
    case IDENTIFIER;
}

enum E_LEXER_TOKENS: string
{
    case END_OF_FILE = "END_OF_FILE";
    case IDENTIFIER = "IDENTIFIER";
    case KEYWORD_TRUE = "KEYWORD_TRUE";
    case KEYWORD_FALSE = "KEYWORD_FALSE";
    case COMMAND = "COMMAND";

    public function toString(): string
    {
        return $this->value;
    }
}

global $KEYWORD_MAP;
$KEYWORD_MAP = array(
    "true"  => E_LEXER_TOKENS::KEYWORD_TRUE,
    "false" => E_LEXER_TOKENS::KEYWORD_FALSE
);

/**
 * Class CodeCommand
 *
 * This class represents a command in the code IPPCode23
 */
class CodeCommand
{
    /**
     * CodeCommand constructor.
     *
     * @param string $command    The command string.
     * @param int    $args_count The number of arguments.
     */
    public function __construct(
        private readonly string $command,
        private readonly int $args_count
    )
    {

    }

    /**
     * Getter for the command
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Getter for the number of arguments
     *
     * @return int
     */
    public function getArgsCount(): int
    {
        return $this->args_count;
    }
}

global $CODE_COMMANDS;
$CODE_COMMANDS = array(
    // Scopes operations, function calls and returns
    "MOVE"        => new CodeCommand("MOVE", 2),
    "CREATEFRAME" => new CodeCommand("CREATEFRAME", 0),
    "PUSHFRAME"   => new CodeCommand("PUSHFRAME", 0),
    "POPFRAME"    => new CodeCommand("POPFRAME", 0),
    "DEFVAR"      => new CodeCommand("DEFVAR", 1),
    "CALL"        => new CodeCommand("CALL", 1),
    "RETURN"      => new CodeCommand("RETURN", 0),

    // Stack operations
    "PUSHS"       => new CodeCommand("PUSHS", 1),
    "POPS"        => new CodeCommand("POPS", 1),

    // Arithmetic, relation, boolean and conversion operations
    "ADD"         => new CodeCommand("ADD", 3),
    "SUB"         => new CodeCommand("SUB", 3),
    "MUL"         => new CodeCommand("MUL", 3),
    "IDIV"        => new CodeCommand("IDIV", 3),
    "LT"          => new CodeCommand("LT", 3),
    "GT"          => new CodeCommand("GT", 3),
    "EQ"          => new CodeCommand("EQ", 3),
    "AND"         => new CodeCommand("AND", 3),
    "OR"          => new CodeCommand("OR", 3),
    "NOT"         => new CodeCommand("NOT", 2),
    "INT2CHAR"    => new CodeCommand("INT2CHAR", 2),
    "STRI2INT"    => new CodeCommand("STRI2INT", 3),

    // IO operations
    "READ"        => new CodeCommand("READ", 2),
    "WRITE"       => new CodeCommand("WRITE", 1),

    // String operations
    "CONCAT"      => new CodeCommand("CONCAT", 3),
    "STRLEN"      => new CodeCommand("STRLEN", 2),
    "GETCHAR"     => new CodeCommand("GETCHAR", 3),
    "SETCHAR"     => new CodeCommand("SETCHAR", 3),

    // Type operations
    "TYPE"        => new CodeCommand("TYPE", 2),

    // Jump operations
    "LABEL"       => new CodeCommand("LABEL", 1),
    "JUMP"        => new CodeCommand("JUMP", 1),
    "JUMPIFEQ"    => new CodeCommand("JUMPIFEQ", 3),
    "JUMPIFNEQ"   => new CodeCommand("JUMPIFNEQ", 3),
    "EXIT"        => new CodeCommand("EXIT", 1),

    // Debug operations
    "DPRINT"      => new CodeCommand("DPRINT", 1),
    "BREAK"       => new CodeCommand("BREAK", 0)
);

/**
 * Class LexicalToken
 */
class LexicalToken
{
    /**
     * LexicalToken constructor.
     *
     * @param string         $value The value of the token
     * @param E_LEXER_TOKENS $type  The type of the token
     */
    public function __construct(
        private readonly string $value,
        private readonly E_LEXER_TOKENS $type
    )
    {
    }

    /**
     * Getter for the value of the token
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Getter for the type of the token
     *
     * @return E_LEXER_TOKENS
     */
    public function getType(): E_LEXER_TOKENS
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return "LexicalToken(value: \"" . $this->value . "\", type: "
            . $this->type->toString() . ")";
    }
}

/**
 * Class LexicalAnalysis
 *
 * This class is responsible for lexical analysis of the input string
 */
class LexicalAnalysis
{
    private E_LEXER_STATES $current_state = E_LEXER_STATES::START;

    private int $input_string_index = -1;

    /**
     * LexicalAnalysis constructor.
     *
     * @param string $input The input string to be processed
     */
    public function __construct(private readonly string $input)
    {

    }

    /**
     * Processes the next token in the input string
     *
     * @return LexicalToken
     * @throws Exception
     */
    private function processNextToken(): LexicalToken
    {
        $token_string = "";

        while (true) {
            $this->input_string_index++;
            if ($this->input_string_index >= strlen($this->input)) {
                $current_char = '\0';
            } else {
                $current_char = $this->input[$this->input_string_index];
            }

            switch ($this->current_state) {
            case E_LEXER_STATES::START:
                switch ($current_char) {
                case ' ':
                case '\t':
                case '\n':
                case '\r':
                    break;
                case '\0':
                    return new LexicalToken("", E_LEXER_TOKENS::END_OF_FILE);
                default:
                    if (preg_match("/^[a-zA-Z]$/", $current_char)) {
                        $this->current_state = E_LEXER_STATES::KEYWORD;
                        $token_string .= $current_char;
                        break;
                    } else {
                        throw new Exception(
                            "Unexpected character: " . $current_char
                        );
                    }
                }
                break;
            case E_LEXER_STATES::KEYWORD:
                if (preg_match("/^[a-zA-Z]$/", $current_char)) {
                    $token_string .= $current_char;
                    break;
                } else {
                    $command_keys = array_keys($GLOBALS["CODE_COMMANDS"]);
                    $keyword_keys = array_keys($GLOBALS["KEYWORD_MAP"]);

                    if (in_array($token_string, $command_keys)) {
                        $this->current_state = E_LEXER_STATES::START;
                        return new LexicalToken(
                            $token_string, E_LEXER_TOKENS::COMMAND
                        );
                    } else {
                        if (in_array($token_string, $keyword_keys)) {
                            $this->current_state = E_LEXER_STATES::START;
                            return new LexicalToken(
                                $token_string,
                                $GLOBALS["KEYWORD_MAP"][$token_string]
                            );
                        } else {
                            $this->current_state = E_LEXER_STATES::IDENTIFIER;
                        }
                    }

                    $this->input_string_index--;
                }
                break;

            case E_LEXER_STATES::IDENTIFIER:
                if (preg_match("/^[a-zA-Z]$/", $current_char)) {
                    $token_string .= $current_char;
                } else {
                    $this->current_state = E_LEXER_STATES::START;
                    return new LexicalToken(
                        $token_string, E_LEXER_TOKENS::IDENTIFIER
                    );
                }
                break;
            }
        }
    }

    /**
     * Returns the next token in the input string
     *
     * @return LexicalToken
     * @throws Exception
     */
    public function getNextToken(): LexicalToken
    {
        return $this->processNextToken();
    }
}


function main(): void
{
//    $stdin = fopen("php://stdin", "r");
    $input_string = "ab ac false MOVE true";

    /** @var LexicalToken[] $tokens */
    $tokens = [];

    $lexicalAnalysis = new LexicalAnalysis($input_string);

    try {
        $token = $lexicalAnalysis->getNextToken();
        while ($token->getType() !== E_LEXER_TOKENS::END_OF_FILE) {
            $tokens[] = $token;
            $token = $lexicalAnalysis->getNextToken();
        }

        foreach ($tokens as $token) {
            echo $token . "\n";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

main();