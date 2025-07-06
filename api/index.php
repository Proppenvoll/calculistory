<?php

declare(strict_types=1);
require('../shell.php');
require('../validation.php');
require('../calculation.php');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exitWithJsonError('Only POST supported', 405);
    }

    if ($_SERVER["CONTENT_TYPE"] !== "application/json") {
        exitWithJsonError('Content-Type must be application/json', 400);
    }

    $rawData = file_get_contents("php://input");
    $parsed = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        exitWithJsonError('Invalid json payload', 400);
    }

    if (empty($parsed['expression'])) {
        exitWithJsonError('Missing expression key', 422);
    }

    if (!is_string($parsed['expression'])) {
        exitWithJsonError('Expression key needs to be a string', 422);
    }

    $inputString = validateInput($parsed["expression"]);
    $whitespaceStripped = stripWhitespace($inputString);
    $tokens = tokenize($whitespaceStripped);

    if (is_bool($tokens)) {
        throw new Exception();
    }

    $rpnTokens = transformToReversePolishNotation($tokens);
    $result = evaluateReversePolishNotationExpression($rpnTokens);

    header("Content-Type: application/json");
    exit(json_encode(['result' => $result]));
} catch (Throwable $e) {
    print_r($e);
    exitWithJsonError("An unexpected error occurred", 500);
}
