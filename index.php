<?php

declare(strict_types=1);

require('shell.php');
require('db.php');
require('validation.php');
require('calculation.php');

const FORM_INPUT_NAME = 'expression';
const COOKIE_SESSION_KEY = "sessionId";
const MINUTE_IN_S = 60;

$result = null;
$history = [];

$databaseConnection = Database::ensureConnection();

$providedSessionId = isset($_COOKIE[COOKIE_SESSION_KEY])
    ? $_COOKIE[COOKIE_SESSION_KEY]
    : null;

$sessionIdProvided = isset($providedSessionId);

if ($sessionIdProvided) {
    $history = getHistoryBySession($databaseConnection, $providedSessionId);
    $nowInS = time();
    setSessionCookie($nowInS, $providedSessionId);
}

$formInputProvided = array_key_exists(FORM_INPUT_NAME, $_POST);

if ($formInputProvided) {
    $validatedInput = validateInput($_POST[FORM_INPUT_NAME]);
    $result = evaluateExpression($validatedInput);
    $session = ensureSession($databaseConnection, $providedSessionId);

    $historyEntry = createHistoryEntry(
        $databaseConnection,
        $session->sessionId,
        $validatedInput,
        $result
    );

    $history[] = $historyEntry;
    $nowInS = time();
    setSessionCookie($nowInS, $session->sessionId);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Calculistory</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Calculistory</h1>

    <div>
        <p>Enter your expression. +, -, *, / are supported.</p>
        <p>E.g. 10 + 2 - 3 * 4</p>
    </div>

    <form method="post">
        <label>
            Expression
            <input name="<?= FORM_INPUT_NAME ?>" />
        </label>

        <button>Calculate</button>
    </form>

    <?php if (isset($result)) { ?>
        <output>
            <span>Result</span>
            <?= $result ?>
        </output>
    <?php } ?>

    <?php if ($history) { ?>
        <table>
            <thead>
                <tr>
                    <th>Expression</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($history as $entry) { ?>
                    <tr>
                      <td><?= htmlspecialchars($entry->expression) ?></td>
                      <td><?= htmlspecialchars((string)$entry->result) ?></td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    <?php } ?>
</body>

</html>
