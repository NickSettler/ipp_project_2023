<?php

enum E_ARG_TYPE
{
    case VAR;
    case SYMB;
    case LABEL;
    case TYPE;
}

class CodeCommandB
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
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}

global $CODE_COMMANDS_B;
$CODE_COMMANDS_B = [
    // Scopes operations, function calls and returns
    'MOVE'        => new CodeCommandB('MOVE', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
    ]),
    'CREATEFRAME' => new CodeCommandB('CREATEFRAME', []),
    'PUSHFRAME'   => new CodeCommandB('PUSHFRAME', []),
    'POPFRAME'    => new CodeCommandB('POPFRAME', []),
    'DEFVAR'      => new CodeCommandB('DEFVAR', [
        E_ARG_TYPE::VAR,
    ]),
    'CALL'        => new CodeCommandB('CALL', [
        E_ARG_TYPE::LABEL,
    ]),
    'RETURN'      => new CodeCommandB('RETURN', []),

    // Stack operations
    'PUSHS'       => new CodeCommandB('PUSHS', [
        E_ARG_TYPE::SYMB,
    ]),
    'POPS'        => new CodeCommandB('POPS', [
        E_ARG_TYPE::VAR,
    ]),

    // Arithmetic, relation, boolean and conversion operations
    'ADD'         => new CodeCommandB('ADD', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'SUB'         => new CodeCommandB('SUB', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'MUL'         => new CodeCommandB('MUL', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'IDIV'        => new CodeCommandB('IDIV', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'LT'          => new CodeCommandB('LT', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'GT'          => new CodeCommandB('GT', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'EQ'          => new CodeCommandB('EQ', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'AND'         => new CodeCommandB('AND', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'OR'          => new CodeCommandB('OR', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'NOT'         => new CodeCommandB('NOT', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
    ]),
    'INT2CHAR'    => new CodeCommandB('INT2CHAR', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
    ]),
    'STRI2INT'    => new CodeCommandB('STRI2INT', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),

    // IO operations
    'READ'        => new CodeCommandB('READ', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::TYPE,
    ]),
    'WRITE'       => new CodeCommandB('WRITE', [
        E_ARG_TYPE::SYMB,
    ]),

    // String operations
    'CONCAT'      => new CodeCommandB('CONCAT', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'STRLEN'      => new CodeCommandB('STRLEN', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
    ]),
    'GETCHAR'     => new CodeCommandB('GETCHAR', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'SETCHAR'     => new CodeCommandB('SETCHAR', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),

    // Type operations
    'TYPE'        => new CodeCommandB('TYPE', [
        E_ARG_TYPE::VAR,
        E_ARG_TYPE::SYMB,
    ]),

    // Jump operations
    'LABEL'       => new CodeCommandB('LABEL', [
        E_ARG_TYPE::LABEL,
    ]),
    'JUMP'        => new CodeCommandB('JUMP', [
        E_ARG_TYPE::LABEL,
    ]),
    'JUMPIFEQ'    => new CodeCommandB('JUMPIFEQ', [
        E_ARG_TYPE::LABEL,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'JUMPIFNEQ'   => new CodeCommandB('JUMPIFNEQ', [
        E_ARG_TYPE::LABEL,
        E_ARG_TYPE::SYMB,
        E_ARG_TYPE::SYMB,
    ]),
    'EXIT'        => new CodeCommandB('EXIT', [
        E_ARG_TYPE::SYMB,
    ]),

    // Debug operations
    'DPRINT'      => new CodeCommandB('DPRINT', [
        E_ARG_TYPE::SYMB,
    ]),
    'BREAK'       => new CodeCommandB('BREAK', []),
];

class CodeCommandArgumentParsed
{
    private ?string $label = null;

    private ?string $frame = null;

    private ?string $var = null;

    private ?string $symb = null;

    private ?string $type = null;

    public function __construct(
        private E_ARG_TYPE $argType, private readonly string $input
    )
    {
        $this->processInput();
    }

    private function processInput(): void
    {
        $allowedTyped = ['int', 'bool', 'string', 'nil'];

        switch ($this->argType) {
        case E_ARG_TYPE::LABEL:
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

            if (!in_array($splitVar[0], ['GF', 'LF', 'TF'])) {
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

            if (!in_array(
                $splitSymb[0],
                ['GF', 'LF', 'TF', 'int', 'bool', 'string', 'nil']
            )
            ) {
                fprintf(
                    STDERR, "ERROR: Invalid symbol type '%s'", $splitSymb[0]
                );
                exit(23);
            }

            if ($splitSymb[0] === 'int' || $splitSymb[0] === 'bool'
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
            fprintf(STDERR, "ERROR: Invalid variable name '%s'", $name);
            exit(23);
        }

        for ($i = 1; $i < strlen($name); $i++) {
            if (!in_array($name[$i], $allowedCharacters)) {
                fprintf(STDERR, "ERROR: Invalid variable name '%s'", $name);
                exit(23);
            }
        }
    }

    private function checkBool(string $bool): void
    {
        if ($bool !== 'true' && $bool !== 'false') {
            fprintf(STDERR, "ERROR: Invalid bool '%s'", $bool);
            exit(23);
        }
    }

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

    private function checkNil(string $nil): void
    {
        if ($nil !== 'nil') {
            fprintf(STDERR, "ERROR: Invalid nil '%s'", $nil);
            exit(23);
        }
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getFrame(): ?string
    {
        return $this->frame;
    }

    /**
     * @return string|null
     */
    public function getVar(): ?string
    {
        return $this->var;
    }

    /**
     * @return string|null
     */
    public function getSymb(): ?string
    {
        return $this->symb;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    public function getArgType(): E_ARG_TYPE
    {
        return $this->argType;
    }

    public function getInput(): string
    {
        return $this->input;
    }
}

function remove_comments(string $input): string
{
    $input = preg_replace('/#.*$/m', '', $input);
    $input = preg_replace('/\h+/m', ' ', $input);
    $input = preg_replace('/^(\h+)|(\h+)$/m', '', $input);
    $input = preg_replace('/^\h*$/m', '', $input);
    $input = preg_replace('/\R\R+/', "\n", $input);
    $input = preg_replace('/\R$/', '', $input);
    $input = preg_replace('/^\R/', '', $input);

    return $input;
}

function is_command_right(string $command): void
{
    global $CODE_COMMANDS_B;

    if (!in_array(strtoupper($command), array_keys($CODE_COMMANDS_B))) {
        fprintf(STDERR, 'Unknown command: %s' . PHP_EOL, $command);
        exit(22);
    }
}

function is_args_right(string $command, array $argsArray): bool
{
    global $CODE_COMMANDS_B;

    /** @var CodeCommandB $currentCommand */
    $currentCommand = $CODE_COMMANDS_B[$command];

    if (count($argsArray) !== count($currentCommand->getArgs())) {
        fprintf(
            STDERR, 'Wrong number of arguments for command: %s' . PHP_EOL,
            $command
        );
        exit(23);
    }

    return true;
}

function main(): void
{
    global $CODE_COMMANDS_B;

//    $input = "
//        .IPPcode23
//        DEFVAR GF@counter
//        MOVE GF@counter string@ #Inicializace proměnné na prázdný řetězec #Jednoduchá iterace, dokud nebude splněna zadaná podmínka
//        LABEL while
//        JUMPIFEQ end GF@counter string@aaa
//        WRITE string@Proměnná\032GF@counter\032obsahuje\032
//        WRITE GF@counter
//        WRITE string@12
//        CONCAT GF@counter GF@counter string@a
//        JUMP while
//        LABEL end
//    ";

    $input = ".IPPcode23
DEFVAR GF@\$x
CALL f
POPS GF@\$x
DEFVAR GF@\$\$__COND_1_cond
EQ GF@\$\$__COND_1_cond GF@\$x nil@nil
NOT GF@\$\$__COND_1_cond GF@\$\$__COND_1_cond
JUMPIFEQ $\$__COND_1_body GF@\$\$__COND_1_cond bool@true
JUMPIFEQ $\$__COND_1_else GF@\$\$__COND_1_cond bool@false
LABEL $\$__COND_1_body
WRITE string@NOT\032NULL\010
JUMP $\$__COND_1_end
LABEL $\$__COND_1_else
WRITE string@NULL\010
JUMP $\$__COND_1_end
LABEL $\$__COND_1_end
EXIT int@0
LABEL f
CREATEFRAME
PUSHFRAME
PUSHS nil@nil
POPFRAME
RETURN";

    $xw = xmlwriter_open_memory();
    xmlwriter_set_indent($xw, 1);
    $res = xmlwriter_set_indent_string($xw, ' ');

    xmlwriter_start_document($xw, '1.0', 'UTF-8');

    $input = remove_comments($input);

    $lines = preg_split('/\R/', $input);

    xmlwriter_start_element($xw, 'program');
    xmlwriter_start_attribute($xw, 'language');
    xmlwriter_text($xw, 'IPPcode23');
    xmlwriter_end_attribute($xw);

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

        /** @var CodeCommandB $currentCommand */
        $currentCommand = $CODE_COMMANDS_B[$commandArray[0]];
        $argumentsCount = count($currentCommand->getArgs());

        xmlwriter_start_element($xw, 'instruction');
        xmlwriter_start_attribute($xw, 'order');
        xmlwriter_text($xw, $index);
        xmlwriter_end_attribute($xw);
        xmlwriter_start_attribute($xw, 'opcode');
        xmlwriter_text($xw, strtoupper($currentCommand->getCommand()));
        xmlwriter_end_attribute($xw);


        if ($argumentsCount === 0) {
            xmlwriter_end_element($xw);
            continue;
        }

        $argumentsArray = explode(' ', $commandArray[1], $argumentsCount);

        foreach ($argumentsArray as $key => $value) {
            $argType = $currentCommand->getArgs()[$key]->getType();

            $parsedCommand = new CodeCommandArgumentParsed($argType, $value);

            xmlwriter_start_element($xw, 'arg' . $key + 1);
            xmlwriter_start_attribute($xw, 'type');

            if ($parsedCommand->getArgType() === E_ARG_TYPE::LABEL) {
                xmlwriter_text($xw, 'label');
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::TYPE) {
                xmlwriter_text($xw, 'type');
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::VAR) {
                xmlwriter_text($xw, 'var');
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::SYMB) {
                xmlwriter_text($xw, $parsedCommand->getType());
            }

            xmlwriter_end_attribute($xw);

            if ($parsedCommand->getArgType() === E_ARG_TYPE::LABEL) {
                xmlwriter_text($xw, $parsedCommand->getLabel());
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::TYPE) {
                xmlwriter_text($xw, $parsedCommand->getType());
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::VAR) {
                xmlwriter_text(
                    $xw,
                    $parsedCommand->getFrame() . "@" . $parsedCommand->getVar()
                );
            } elseif ($parsedCommand->getArgType() === E_ARG_TYPE::SYMB) {
                xmlwriter_text($xw, $parsedCommand->getSymb());
            }

            xmlwriter_end_element($xw);
        }

        xmlwriter_end_element($xw);
    }

    xmlwriter_end_element($xw);
    xmlwriter_end_document($xw);

    echo xmlwriter_output_memory($xw);
}

main();
