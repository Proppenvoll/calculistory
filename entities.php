<?php

declare(strict_types=1);

abstract class Entity
{
    abstract static function createFromArray(array $entity): static;
}


class Session extends Entity
{
    function __construct(
        public readonly string $sessionId,
        public readonly int $createdAtInS,
    ) {}

    static function createFromArray(array $sessionArray): static
    {
        [
            'session_id' => $sessionId,
            'created_at_in_s' => $createdAtInS,
        ] = $sessionArray;

        return new Session($sessionId, $createdAtInS);
    }
}


class History extends Entity
{
    function __construct(
        public readonly int $historyId,
        public readonly string $sessionId,
        public readonly string $expression,
        public readonly float $result
    ) {}

    static function createFromArray(array $historyArray): static
    {
        [
            'history_id' => $historyId,
            'session_id' => $sessionId,
            'expression' => $expression,
            'result' => $result,
        ] = $historyArray;

        return new History($historyId, $sessionId, $expression, $result);
    }
}
