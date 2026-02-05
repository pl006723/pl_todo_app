<?php

/* ------------------------------------------------------------------
 * auth.php
 * Provides: requireLogin(), register(), login(), logout()
 * Session handling is 100 % procedural; no external libraries.
 * ------------------------------------------------------------------ */

/* Start or resume the user’s session (must be FIRST output) */

session_start([
    'cookie_httponly' => true, // Prevents JS access to cookies (protects against XSS)
    'cookie_secure' => true,   // Requires HTTPS connection
    'cookie_samesite' => 'Strict'
]);

session_regenerate_id(true); // Prevents Session Fixation attacks


require_once 'config.php';             // re-use the same $conn

/* ----------  simple gate-keeper  ---------- */
/**
 * requireLogin()
 * Call this at the top of any private endpoint.
 * If the visitor is NOT logged in, we immediately abort with 401 JSON.
 */

function requireLogin(): void {
    if (empty($_SESSION['uid'])) {          // uid = user-id stored at login
        http_response_code(401);
        exit(json_encode(['error' => 'login required']));
    }
}

/* ----------  registration  ---------- */
/**
 * register(string $u, string $p): array
 * INPUT:  raw username & password from front-end
 * OUTPUT: associative array either ["success"=>true] or ["error"=>"msg"]
 * DB:     inserts a new row into table `users`
 */

function register(string $u, string $p): array {
    global $conn;               // pull in the connection

     /* Basic length check */

    if (strlen($u) < 3 || strlen($p) < 6) {
        return ['error' => 'Username ≥3 & password ≥6 chars'];
    }

     /* Hash the password with PHP’s built-in default algorithm (bcrypt) */

    $hash = password_hash($p, PASSWORD_DEFAULT);

     /* Prepared statement prevents SQL-injection */

    $stmt = $conn->prepare('INSERT INTO users (username, password_hash) VALUES (?,?)');
    $stmt->bind_param('ss', $u, $hash);      // 's' = string, two params

     /* execute() returns FALSE if username is UNIQUE and duplicate */

    if (!$stmt->execute()) {
        return ['error' => 'Username already taken'];
    }
    return ['success' => true];         // caller will JSON-encode this
}

/* ----------  login  ---------- */
/**
 * login(string $u, string $p): array
 * INPUT:  username & raw password
 * OUTPUT: ["success"=>true, "username"=>u]  OR  ["error"=>"Bad credentials"]
 * SIDE-EFFECT: on success we fill $_SESSION[uid|uname] so future calls know who we are
 */

function login(string $u, string $p): array {
    global $conn;

    /* 1. fetch the row that matches username */
    $stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE username=?');
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $stmt->store_result();      // moves whole result set to PHP

     /* If we don’t have exactly 1 row, credentials are wrong */
    if ($stmt->num_rows !== 1) {
        return ['error' => 'Bad credentials'];
    }

     /* 2. pull the columns into PHP variables */
    $stmt->bind_result($id, $hash);
    $stmt->fetch();

     /* 3. let PHP verify the hash (timing-safe) */
    if (!password_verify($p, $hash)) {
        return ['error' => 'Bad credentials'];
    }

    /* 4. SUCCESS – remember the user in the session */
    $_SESSION['uid']   = $id;           // integer user-id
    $_SESSION['uname'] = $u;            // string username (for greeting)
    return ['success' => true, 'username' => $u];
}

/* ----------  logout  ---------- */
/**
 * logout(): void
 * Simply destroys the session cookie + data, then the client is a guest again.
 */

function logout(): void {
    session_destroy();
}
?>