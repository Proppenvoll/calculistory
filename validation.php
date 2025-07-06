<?php
declare(strict_types=1);

function validateInput(string $input): string {
    $ensuredInput = $input ?: '0';
    $validatedInput = preg_replace('/[^0-9\s+\-*\/]/', '', $ensuredInput);
    return is_string($validatedInput) ? $validatedInput : "0";
}

