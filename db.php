<?php

declare(strict_types=1);

require('entities.php');

function generateSessionId()
{
    $randomizer = new \Random\Randomizer();
    return $randomizer->getBytesFromString("abcdef", 16);
}


function getSession(SQLite3 $connection, string $sessionId): ?Session
{
    $sessionsGetStatment = $connection->prepare(
        "SELECT * FROM sessions WHERE session_id = :sessionId"
    );

    $sessionsGetStatment->bindValue(':sessionId', $sessionId);
    $result = $sessionsGetStatment->execute()->fetchArray();
    return $result ? Session::createFromArray($result) : null;
}

function createSession(SQLite3 $connection): Session
{
    $sessionCreateStatement = $connection->prepare(
        'INSERT INTO sessions(session_id) VALUES(:sessionId) RETURNING *'
    );

    $sessionId = generateSessionId();
    $sessionCreateStatement->bindValue(':sessionId', $sessionId);

    // Something is not right with fetchArray() and sqlite RETURNING statement.
    // Calling fetchArray() leads to a warning that a UNIQUE constraint is
    // violated. Still the insertion succeeds.
    $sessionCreateStatement->execute();
    return getSession($connection, $sessionId);
}

function getHistoryBySession(SQLite3 $connection, string $sessionId): array
{
    $historyGetStatement = $connection->prepare(
        'SELECT * FROM histories WHERE session_id = :sessionId'
    );

    $historyGetStatement->bindValue(':sessionId', $sessionId);
    $exec = $historyGetStatement->execute();
    $result = [];

    while ($res = $exec->fetchArray(SQLITE3_ASSOC)) {
        $result[] = History::createFromArray($res);
    }

    return $result;
}

function getHistory(SQLite3 $connection, int $historyId): ?History
{
    $historyGetStatement = $connection->prepare(
        'SELECT * FROM histories WHERE history_id = :historyId'
    );

    $historyGetStatement->bindValue(':historyId', $historyId);
    $result = $historyGetStatement->execute()->fetchArray();

    return $result ? History::createFromArray($result) : null;
}

function createHistoryEntry(
    SQLite3 $connection,
    string $sessionId,
    string $expression,
    float $result
): History {
    $historyInsertStatement = $connection->prepare(
        'INSERT INTO histories(session_id, expression, result) VALUES(:sessionId, :expression, :result)'
    );

    $historyInsertStatement->bindValue(':sessionId', $sessionId);
    $historyInsertStatement->bindValue(':expression', $expression);
    $historyInsertStatement->bindValue(':result', $result);

    // Do not call fetchArray() here as it will insert the history entry
    // an additional time.
    $result = $historyInsertStatement->execute();
    return getHistory($connection, $connection->lastInsertRowID());
}

function ensureSession(SQLite3 $connection, ?string $sessionId)
{
    if (!isset($sessionId)) {
        return createSession($connection);
    }

    $session = getSession($connection, $sessionId);

    if (!isset($session)) {
        return createSession($connection);
    }

    return $session;
}

class Database
{
    public static ?SQLite3 $connection = null;

    public static function ensureConnection(): SQLite3
    {
        $noConnection = is_null(self::$connection);

        if ($noConnection) {
            self::$connection = new SQLite3("./db.sqlite");
            self::$connection->exec("PRAGMA foreign_keys = ON");

            self::$connection->exec('CREATE TABLE IF NOT EXISTS sessions(
    session_id TEXT NOT NULL PRIMARY KEY,
created_at_in_s INTEGER NOT NULL DEFAULT (unixepoch())
) STRICT, WITHOUT ROWID');

            self::$connection->exec('CREATE TABLE IF NOT EXISTS histories(
    history_id INTEGER NOT NULL PRIMARY KEY,
session_id TEXT NOT NULL REFERENCES sessions(session_id) ON DELETE CASCADE ON UPDATE CASCADE,
expression TEXT NOT NULL,
result REAL NOT NULL
) STRICT');
        }

        return self::$connection;
    }
}
