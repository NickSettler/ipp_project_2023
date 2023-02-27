# Documentation of Project Implementation for IPP 2022/2023

## Name and surname: Nikita Moiseev

## Login: xmoise01

# Project 1: Interpretation of IPPcode23

IPPCode23 is designed to be an assembly-like language. Each command is placed on a separate line. The first word on the
line is the name of the command. The rest of the line is a list of arguments. The arguments are separated by spaces.

## 1.1. Lexical analysis

The lexical analysis is performed by splitting the input into lines. Each line is then split by spaces.
The first word is the name of the command. The rest of the line is a list of arguments. The arguments are separated by
spaces.

To implement the lexical analysis, the `strtok` function was used. The `strtok` function splits the input string into
tokens.

## 1.2. Syntax analysis

The syntax analysis is performed by checking the correctness of the arguments of the command. First token in each line
is a command. It is checked if the command is valid. Then according to the command, the number of arguments and their
types are checked.

### Label arguments

Label arguments are checked according to the task rules:

* Starts with a letter or special character
* Contains only letters, numbers and special characters
* Does not contain space
* Special characters are: `_`, `-`, `$`, `&`, `%`, `*`, `!`, `?`

### Variable arguments

Variable arguments are divided by the `@` character. The first part is the frame of the variable. The second part is the
name of the variable. The frame can be `GF`, `LF` or `TF`. Rules for the name of the variable are the same as for the
label.

### Constant arguments

Constant arguments are divided by the `@` character. The first part is the type of the constant. The second part is the
value of the constant. The type can be `int`, `bool`, `string` or `nil`. The value is checked according to the type.




