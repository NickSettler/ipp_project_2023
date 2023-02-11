<?php

enum E_ARG_TYPE
{
    case VAR;
    case SYMB;
    case LABEL;
    case TYPE;
}

/**
 * Class CodeCommand. Represents a command in the code.
 */
class CodeCommand
{
    /**
     * CodeCommand constructor.
     *
     * @param string       $command The command string.
     * @param E_ARG_TYPE[] $args    The arguments of the command.
     */
    public function __construct(
        private readonly string $command,
        private readonly array $args
    )
    {
    }

    /**
     * Getter for the command
     *
     * @return string The command
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Getter for the arguments of the command
     *
     * @return E_ARG_TYPE[] The arguments of the command
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}

global $CODE_COMMANDS;
$CODE_COMMANDS = [
    // Scopes operations, function calls and returns
    'MOVE'        => new CodeCommand(
        'MOVE',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB]
    ),
    'CREATEFRAME' => new CodeCommand('CREATEFRAME', []),
    'PUSHFRAME'   => new CodeCommand('PUSHFRAME', []),
    'POPFRAME'    => new CodeCommand('POPFRAME', []),
    'DEFVAR'      => new CodeCommand('DEFVAR', [E_ARG_TYPE::VAR]),
    'CALL'        => new CodeCommand('CALL', [E_ARG_TYPE::LABEL]),
    'RETURN'      => new CodeCommand('RETURN', []),

    // Stack operations
    'PUSHS'       => new CodeCommand('PUSHS', [E_ARG_TYPE::SYMB]),
    'POPS'        => new CodeCommand('POPS', [E_ARG_TYPE::VAR]),

    // Arithmetic, relation, boolean and conversion operations
    'ADD'         => new CodeCommand(
        'ADD',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'SUB'         => new CodeCommand(
        'SUB',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'MUL'         => new CodeCommand(
        'MUL',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'IDIV'        => new CodeCommand(
        'IDIV', [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'LT'          => new CodeCommand(
        'LT',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'GT'          => new CodeCommand(
        'GT',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'EQ'          => new CodeCommand(
        'EQ',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'AND'         => new CodeCommand(
        'AND',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'OR'          => new CodeCommand(
        'OR',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'NOT'         => new CodeCommand(
        'NOT', [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB]
    ),
    'INT2CHAR'    => new CodeCommand(
        'INT2CHAR', [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB]
    ),
    'STRI2INT'    => new CodeCommand(
        'STRI2INT',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),

    // IO operations
    'READ'        => new CodeCommand(
        'READ', [E_ARG_TYPE::VAR, E_ARG_TYPE::TYPE]
    ),
    'WRITE'       => new CodeCommand('WRITE', [E_ARG_TYPE::SYMB]),

    // String operations
    'CONCAT'      => new CodeCommand(
        'CONCAT',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'STRLEN'      => new CodeCommand(
        'STRLEN', [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB]
    ),
    'GETCHAR'     => new CodeCommand(
        'GETCHAR',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'SETCHAR'     => new CodeCommand(
        'SETCHAR',
        [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),

    // Type operations
    'TYPE'        => new CodeCommand(
        'TYPE', [E_ARG_TYPE::VAR, E_ARG_TYPE::SYMB]
    ),

    // Jump operations
    'LABEL'       => new CodeCommand('LABEL', [E_ARG_TYPE::LABEL]),
    'JUMP'        => new CodeCommand('JUMP', [E_ARG_TYPE::LABEL]),
    'JUMPIFEQ'    => new CodeCommand(
        'JUMPIFEQ',
        [E_ARG_TYPE::LABEL, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'JUMPIFNEQ'   => new CodeCommand(
        'JUMPIFNEQ',
        [E_ARG_TYPE::LABEL, E_ARG_TYPE::SYMB, E_ARG_TYPE::SYMB]
    ),
    'EXIT'        => new CodeCommand('EXIT', [E_ARG_TYPE::SYMB]),

    // Debug operations
    'DPRINT'      => new CodeCommand('DPRINT', [E_ARG_TYPE::SYMB]),
    'BREAK'       => new CodeCommand('BREAK', []),
];

class CodeCommandArgument
{
    /** @var string|null label for "label" non-terminal */
    private ?string $label = null;

    /** @var string|null frame for "var" non-terminal */
    private ?string $frame = null;

    /** @var string|null var for "var" non-terminal */
    private ?string $var = null;

    /** @var string|null symb for "symb" non-terminal */
    private ?string $symb = null;

    /** @var string|null type for "type"|"symb" non-terminal */
    private ?string $type = null;

    public function __construct(
        private E_ARG_TYPE $argType, private readonly string $input
    )
    {
        $this->processInput();
    }

    /**
     * Process input and set appropriate properties based on argument type and
     * input value.
     *
     * @return void
     */
    private function processInput(): void
    {
        $allowedTyped = ['int', 'bool', 'string', 'nil'];
        $allowedFrames = ['GF', 'LF', 'TF'];

        $allowedPrefixes = array_merge($allowedTyped, $allowedFrames);

        switch ($this->argType) {
        case E_ARG_TYPE::LABEL:
            $this->checkVariableName($this->input);

            $this->label = $this->input;
            break;
        case E_ARG_TYPE::TYPE:
            if (!in_array($this->input, $allowedTyped)) {
                fprintf(STDERR, "ERROR: Invalid type '%s'", $this->input);
                exit(23);
            }
            $this->type = $this->input;
            break;
        case E_ARG_TYPE::VAR:
            $splitVar = explode('@', $this->input);

            if (count($splitVar) !== 2) {
                fprintf(STDERR, "ERROR: Invalid variable '%s'", $this->input);
                exit(23);
            }

            if (!in_array($splitVar[0], $allowedFrames)) {
                fprintf(
                    STDERR, "ERROR: Invalid variable frame '%s'", $splitVar[0]
                );
                exit(23);
            }

            $this->checkVariableName($splitVar[1]);

            $this->frame = $splitVar[0];
            $this->var = $splitVar[1];
            break;
        case E_ARG_TYPE::SYMB:
            $splitSymb = explode('@', $this->input, 2);

            if (count($splitSymb) !== 2) {
                fprintf(STDERR, "ERROR: Invalid symbol '%s'", $this->input);
                exit(23);
            }

            if (!in_array($splitSymb[0], $allowedPrefixes)) {
                fprintf(
                    STDERR, "ERROR: Invalid symbol type '%s'", $splitSymb[0]
                );
                exit(23);
            }

            if ($splitSymb[0] === 'int'
                || $splitSymb[0] === 'bool'
                || $splitSymb[0] === 'string'
                || $splitSymb[0] === 'nil'
            ) {
                if ($splitSymb[0] === 'int') {
                    $this->checkInt($splitSymb[1]);
                } elseif ($splitSymb[0] === 'bool') {
                    $this->checkBool($splitSymb[1]);
                } elseif ($splitSymb[0] === 'string') {
                    $this->checkString($splitSymb[1]);
                } elseif ($splitSymb[0] === 'nil') {
                    $this->checkNil($splitSymb[1]);
                }

                $this->type = $splitSymb[0];
                $this->symb = $splitSymb[1];

                if ($this->type === "string") {
                    $this->symb = $this->processString($this->symb);
                }
            } else {
                $this->checkVariableName($splitSymb[1]);

                $this->argType = E_ARG_TYPE::VAR;

                $this->frame = $splitSymb[0];
                $this->var = $splitSymb[1];
            }
        }
    }

    /**
     * Check if variable name is valid. Must satisfy following conditions:
     * - must start with letter, underscore, dollar sign, ampersand, percent,
     *   asterisk, exclamation mark or question mark
     * - can contain only letters, numbers, underscore, dollar sign, ampersand,
     *   percent, asterisk, exclamation mark or question mark
     *
     * @param string $name Variable name to check
     *
     * @return void
     */
    private function checkVariableName(string $name): void
    {
        $allowedSpecialCharacters = ['_', '-', '$', '&', '%', '*', '!', '?'];
        $allowedStartCharacters = array_merge(
            range('a', 'z'), range('A', 'Z'), $allowedSpecialCharacters
        );
        $allowedCharacters = array_merge(
            $allowedStartCharacters, range('0', '9')
        );

        if (!in_array($name[0], $allowedStartCharacters)) {
            fprintf(STDERR, "ERROR: Invalid argument '%s'", $name);
            exit(23);
        }

        for ($i = 1; $i < strlen($name); $i++) {
            if (!in_array($name[$i], $allowedCharacters)) {
                fprintf(STDERR, "ERROR: Invalid argument '%s'", $name);
                exit(23);
            }
        }
    }

    /**
     * Check if the string is a valid boolean. Must be either 'true' or 'false'.
     *
     * @param string $bool The string with the boolean to check.
     *
     * @return void
     */
    private function checkBool(string $bool): void
    {
        if ($bool !== 'true' && $bool !== 'false') {
            fprintf(STDERR, "ERROR: Invalid bool '%s'", $bool);
            exit(23);
        }
    }

    /**
     * Check if the string is a valid int. Must satisfy the following
     * regular expression: ^[-+]?[0-9]+$
     *
     * @param string $int The string with the int to check.
     *
     * @return void
     */
    private function checkInt(string $int): void
    {
        if (preg_match('/^[-+]?[0-9]+$/', $int) === 0) {
            fprintf(STDERR, "ERROR: Invalid int '%s'", $int);
            exit(23);
        }
    }

    private function checkString(string $string): void
    {
    }

    /**
     * Process the string to escape special characters. Ranges from 0 to 32,
     * 35 and 92 are escaped. The escape sequence is \ followed by the
     * octal representation of the character.
     *
     * @param string $string The string to process.
     *
     * @return string The processed string.
     */
    private function processString(string $string): string
    {
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string[$i]) <= 32
                || ord($string[$i]) === 35
                || ord($string[$i]) === 92
            ) {
                $escapedNumber = sprintf("%03d", decoct(ord($string[$i])));

                $string = substr_replace(
                    $string, "\\$escapedNumber", $i, 1
                );
            }
        }

        return $string;
    }

    /**
     * Check if the nil is valid. Must satisfy the following pattern: nil@nil
     * Otherwise, the program will exit with error code 23.
     *
     * @param string $nil The string to check.
     *
     * @return void
     */
    private function checkNil(string $nil): void
    {
        if ($nil !== 'nil') {
            fprintf(STDERR, "ERROR: Invalid nil '%s'", $nil);
            exit(23);
        }
    }

    /**
     * Get the label of the argument.
     *
     * @return string|null The label of the argument.
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Get the frame of the variable in the argument.
     *
     * @return string|null The frame of the variable in the argument.
     */
    public function getFrame(): ?string
    {
        return $this->frame;
    }

    /**
     * Get the variable name in the argument.
     *
     * @return string|null The variable name in the argument.
     */
    public function getVar(): ?string
    {
        return $this->var;
    }

    /**
     * Get the symbolic value in the argument.
     *
     * @return string|null The symbolic value in the argument.
     */
    public function getSymb(): ?string
    {
        return $this->symb;
    }

    /**
     * Get the type of the argument.
     *
     * @return string|null The type of the argument.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the type of the operand.
     *
     * @return E_ARG_TYPE The type of the operand.
     */
    public function getArgType(): E_ARG_TYPE
    {
        return $this->argType;
    }
}

/**
 * Checks if all elements of the array satisfy the predicate.
 *
 * @param array    $arr       The array to check.
 * @param callable $predicate The predicate to check the elements of the array.
 *
 * @return bool true if all elements of the array satisfy the predicate
 */
function array_every(array $arr, callable $predicate): bool
{
    foreach ($arr as $e) {
        if (!call_user_func($predicate, $e)) {
            return false;
        }
    }

    return true;
}

/**
 * Checks if any element of the array satisfies the predicate.
 *
 * @param array    $arr       The array to check.
 * @param callable $predicate The predicate to check the elements of the array.
 *
 * @return bool true if any element of the array satisfies the predicate
 */
function array_any(array $arr, callable $predicate): bool
{
    return !array_every($arr, function ($e) use ($predicate) {
        return !call_user_func($predicate, $e);
    });
}

/**
 * Removes comments and useless symbols from the input.
 *
 * @param string $input The input to process.
 *
 * @return string The processed input.
 */
function remove_comments(string $input): string
{
    $input = preg_replace('/#.*$/m', '', $input);
    $input = preg_replace('/\h+/m', ' ', $input);
    $input = preg_replace('/^(\h+)|(\h+)$/m', '', $input);
    $input = preg_replace('/^\h*$/m', '', $input);
    $input = preg_replace('/\R\R+/', "\n", $input);
    $input = preg_replace('/\R$/', '', $input);

    return preg_replace('/^\R/', '', $input);
}

/**
 * Checks if the command is correct. If there's no such command, the program
 * exits with error code 22.
 *
 * @param string $command The command to check.
 *
 * @return void
 */
function is_command_right(string $command): void
{
    global $CODE_COMMANDS;

    if (!in_array($command, array_keys($CODE_COMMANDS))) {
        fprintf(STDERR, 'Unknown command: %s' . PHP_EOL, $command);
        exit(22);
    }
}

/**
 * Parses the argument string and returns the array with arguments.
 *
 * @param string $command    The command to check.
 * @param string $argsString The string with arguments.
 *
 * @return string[] The array with arguments.
 */
function parse_command_args(string $command, string $argsString): array
{
    global $CODE_COMMANDS;

    /** @var CodeCommand $currentCommand */
    $currentCommand = $CODE_COMMANDS[$command];
    $commandArgumentsCount = count($currentCommand->getArgs());

    if ($commandArgumentsCount === 0) {
        if (strlen($argsString) !== 0) {
            fprintf(
                STDERR, 'Wrong number of arguments for command: %s' . PHP_EOL,
                $command
            );
            exit(23);
        }

        return [];
    }

    $argumentsArray = explode(' ', $argsString, $commandArgumentsCount);

    $allArgsPresented = array_every($argumentsArray, function ($arg) {
        return strlen($arg) !== 0;
    });

    if (count($argumentsArray) !== $commandArgumentsCount
        || !$allArgsPresented
    ) {
        fprintf(
            STDERR, 'Wrong number of arguments for command: %s' . PHP_EOL,
            $command
        );
        exit(23);
    }

    return $argumentsArray;
}

/**
 * Class XMLManager.
 *
 * The *Singleton* XMLManager class. This class is responsible for
 * processing instructions and generating XML output.
 */
class XMLManager
{
    private XMLWriter $xw;

    /**
     * XMLManager constructor.
     *
     * Private constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    private function __construct()
    {
        $this->xw = xmlwriter_open_memory();
        xmlwriter_set_indent($this->xw, 1);
        xmlwriter_set_indent_string($this->xw, ' ');

        xmlwriter_start_document($this->xw, '1.0', 'UTF-8');
    }

    /**
     * Get the XMLManager instance.
     *
     * @return XMLManager the *Singleton* instance.
     */
    public static function getInstance(): XMLManager
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new XMLManager();
        }
        return $instance;
    }

    /**
     * Start the program element.
     *
     * @return void
     */
    public function startProgram(): void
    {
        xmlwriter_start_element($this->xw, 'program');
        xmlwriter_start_attribute($this->xw, 'language');
        xmlwriter_text($this->xw, 'IPPcode23');
        xmlwriter_end_attribute($this->xw);
    }

    /**
     * End the program element.
     *
     * @return void
     */
    public function endProgram(): void
    {
        xmlwriter_end_element($this->xw);
        xmlwriter_end_document($this->xw);
    }

    /**
     * Generate instruction element with attributes and nested arguments.
     *
     * @param string                $command       The command to generate.
     * @param int                   $order         The order of the
     *                                             instruction.
     * @param CodeCommandArgument[] $args          The arguments of the
     *                                             instruction.
     *
     * @return void
     */
    public function addInstruction(
        string $command,
        int $order,
        array $args
    ): void
    {
        global $CODE_COMMANDS;
        $currentCommand = $CODE_COMMANDS[$command];
        $argumentsCount = count($currentCommand->getArgs());

        xmlwriter_start_element($this->xw, 'instruction');

        xmlwriter_start_attribute($this->xw, 'order');
        xmlwriter_text($this->xw, $order);
        xmlwriter_end_attribute($this->xw);

        xmlwriter_start_attribute($this->xw, 'opcode');
        xmlwriter_text($this->xw, strtoupper($command));
        xmlwriter_end_attribute($this->xw);

        if ($argumentsCount === 0) {
            xmlwriter_end_element($this->xw);
            return;
        }

        foreach ($args as $index => $arg) {
            $this->addAttribute($index, $arg);
        }

        xmlwriter_end_element($this->xw);
    }

    /**
     * Add attribute to the instruction element.
     *
     * @param int                 $index The index of the argument.
     * @param CodeCommandArgument $arg   The argument to add.
     *
     * @return void
     */
    public function addAttribute(int $index, CodeCommandArgument $arg
    ): void
    {
        xmlwriter_start_element($this->xw, 'arg' . $index + 1);
        xmlwriter_start_attribute($this->xw, 'type');

        if ($arg->getArgType() === E_ARG_TYPE::LABEL) {
            xmlwriter_text($this->xw, 'label');
        } elseif ($arg->getArgType() === E_ARG_TYPE::TYPE) {
            xmlwriter_text($this->xw, 'type');
        } elseif ($arg->getArgType() === E_ARG_TYPE::VAR) {
            xmlwriter_text($this->xw, 'var');
        } elseif ($arg->getArgType() === E_ARG_TYPE::SYMB) {
            xmlwriter_text($this->xw, $arg->getType());
        }

        xmlwriter_end_attribute($this->xw);

        if ($arg->getArgType() === E_ARG_TYPE::LABEL) {
            xmlwriter_text($this->xw, $arg->getLabel());
        } elseif ($arg->getArgType() === E_ARG_TYPE::TYPE) {
            xmlwriter_text($this->xw, $arg->getType());
        } elseif ($arg->getArgType() === E_ARG_TYPE::VAR) {
            xmlwriter_text(
                $this->xw, $arg->getFrame() . "@" . $arg->getVar()
            );
        } elseif ($arg->getArgType() === E_ARG_TYPE::SYMB) {
            xmlwriter_text($this->xw, $arg->getSymb());
        }

        xmlwriter_end_element($this->xw);
    }

    public function output(): string
    {
        return xmlwriter_output_memory($this->xw);
    }
}

function main(): void
{
    global $CODE_COMMANDS;

    $input = ".IPPcode23
DEFVAR GF@a
READ GF@a int
WRITE GF@a
WRITE string@\032<not-tag/>\032
WRITE bool@true";

    $input = remove_comments($input);

    $lines = preg_split('/\R/', $input);

    XMLManager::getInstance()->startProgram();

    foreach ($lines as $index => $line) {
        if ($index === 0) {
            if ($line !== '.IPPcode23') {
                fprintf(STDERR, 'Wrong header: ' . $line . PHP_EOL);
                exit(21);
            }
            continue;
        }

        $commandArray = explode(' ', $line, 2);

        is_command_right($commandArray[0]);

        $currentCommand = $CODE_COMMANDS[$commandArray[0]];

        $argumentsArray = parse_command_args(
            $commandArray[0], count($commandArray) > 1 ? $commandArray[1] : ''
        );

        $args = [];

        foreach ($argumentsArray as $key => $value) {
            $argType = $currentCommand->getArgs()[$key];
            $parsedCommand = new CodeCommandArgument($argType, $value);
            $args[] = $parsedCommand;
        }


        XMLManager::getInstance()->addInstruction(
            $currentCommand->getCommand(), $index, $args
        );
    }

    XMLManager::getInstance()->endProgram();

    echo XMLManager::getInstance()->output();
}

main();
