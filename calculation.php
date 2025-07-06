<?php

declare(strict_types=1);

function tokenize(string $value): array
{
    $result = preg_split('/([+\-*\/])/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
    return $result === false ? [] : $result;
}

function stripWhitespace(string $value)
{
    return str_replace(' ', '', $value);
}

const OPERATOR_PRECEDENCE = [
    '+' => 1,
    '-' => 1,
    '*' => 2,
    '/' => 2,
];

function hasHigherPrecedence(string $firstOperator, string $secondOperator)
{
    return OPERATOR_PRECEDENCE[$firstOperator] > OPERATOR_PRECEDENCE[$secondOperator];
}

function hasLowerEqualPrecedence(string $firstOperator, string $secondOperator)
{
    return !hasHigherPrecedence($firstOperator, $secondOperator);
}

/**
 * For more information look up "Shunting yard algorithm".
 * @return string[]
 */
function transformToReversePolishNotation(array $tokens)
{
    $interirmResult = array_reduce($tokens, function ($accumulator, $token) {
        if (is_numeric($token)) {
            $accumulator['result'][] = $token;
            return $accumulator;
        }

        while (
            !empty($accumulator['operatorStack']) &&
            hasLowerEqualPrecedence($token, end($accumulator['operatorStack']))
        ) {
            $previousOperator = array_pop($accumulator["operatorStack"]);
            $accumulator["result"][] = $previousOperator;
        }

        $accumulator['operatorStack'][] = $token;
        return $accumulator;
    }, ['result' => [], 'operatorStack' => []]);
    $reversedOperatorStack = array_reverse($interirmResult['operatorStack']);
    return array_merge($interirmResult['result'], $reversedOperatorStack);
}


function evaluateReversePolishNotationExpression(array $rpnTokens): int | float
{
    [$result] = array_reduce($rpnTokens, function (array $result, $numberOrOperator) {
        if (is_numeric($numberOrOperator)) {
            $result[] = (float)$numberOrOperator;
            return $result;
        }

        $rightOperand = array_pop($result);
        $leftOperand = array_pop($result);

        switch ($numberOrOperator) {
            case '+':
                $result[] = $leftOperand + $rightOperand;
                break;
            case '-':
                $result[] = $leftOperand - $rightOperand;
                break;
            case '*':
                $result[] = $leftOperand * $rightOperand;
                break;
            case '/':
                $result[] = $leftOperand / $rightOperand;
                break;
        }

        return $result;
    }, []);

    return $result;
}

function evaluateExpression(string $expression): int | float
{
    $whitespaceStripped = stripWhitespace($expression);
    $tokens = tokenize($whitespaceStripped);
    $rpnTokens = transformToReversePolishNotation($tokens);
    return evaluateReversePolishNotationExpression($rpnTokens);
}
