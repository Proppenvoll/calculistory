<?php
declare(strict_types=1);

function exitWithJsonError(string $message, int $statusCode): never
{
    http_response_code($statusCode);
    $errorMessage = json_encode(['error' => $message]);
    exit($errorMessage);
}

function setSessionCookie(int $nowInS, string $sessionId) {
    $expiresInS = $nowInS + MINUTE_IN_S * 5;
    setcookie(COOKIE_SESSION_KEY, $sessionId, $expiresInS, httponly: true);
}

