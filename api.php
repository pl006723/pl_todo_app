<?php
/* Single end-point that answers all CRUD requests via AJAX */

/* ------------------------------------------------------------------
 * api.php
 * Universal end-point: every front-end fetch() hits here with
 *     POST  api.php?action=<verb>
 *     JSON body when needed.
 * Returns ONLY JSON; http_response_code() sets the status.
 * ------------------------------------------------------------------ */
require_once 'config.php';
require_once 'auth.php';   // pulls in requireLogin() + auth helpers

/* Tell every browser / AJAX caller we speak JSON */
header('Content-Type: application/json');

/* ---------- Helper to centralise JSON replies ---------- */
/**
 * send($data, $status=200)
 * Encodes array to JSON, sets HTTP status, and terminates script.
 */
function send($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/* ---------- Read the JSON body (for POST/PUT) once ---------- */
$input = json_decode(file_get_contents('php://input'), true);

/* ---------- Simple router: ?action=create|read|update|delete|register|login… ---------- */
$action = $_GET['action'] ?? '';            // which action the JS asked for

// =====================  READ  (all tasks for THIS user)  =====================
if ($action === 'read') {
    requireLogin();                         // 401 if guest
    $uid = $_SESSION['uid'];                // from login session
    /* Pull only columns we actually need, newest first */
    $sql = "SELECT id, task, description, priority, completed 
            FROM tasks 
            WHERE user_id = ? 
            ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $uid);           // 'i' = integer
    $stmt->execute();
    $res = $stmt->get_result();
    send($res->fetch_all(MYSQLI_ASSOC));    // pure array of rows → JS
}

// =====================  CREATE  =====================
elseif ($action === 'create' && isset($input['task'])) {
    requireLogin();
    $task = trim($input['task']);
    $description = trim($input['description'] ?? '');
    $priority = $input['priority'] ?? 'medium';
    if ($task === '') {
        send(['error' => 'Empty task'], 400);
    }
    // Validate priority
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $priority = 'medium'; // Fallback to default
    }
    $uid = $_SESSION['uid'];
    $stmt = $conn->prepare("INSERT INTO tasks (task, description, priority, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssi', $task, $description, $priority, $uid);
    $stmt->execute();
    $id = $conn->insert_id;
    send(['id' => $id, 'task' => $task, 'description' => $description, 'priority' => $priority, 'completed' => 0]);
}

// =====================  UPDATE (status or content)  =====================
elseif ($action === 'update' && isset($input['id'])) {
    requireLogin();
    $uid = $_SESSION['uid'];
    $id  = (int)$input['id'];

    if (isset($input['completed'])) {
        // Original functionality: Toggle completion status
        $comp = (int)$input['completed'];
        $stmt = $conn->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('iii', $comp, $id, $uid);
    } else {
        // NEW: Edit task content
        $task = trim($input['task'] ?? '');
        $description = trim($input['description'] ?? '');
        $priority = $input['priority'] ?? 'medium';

        if ($task === '') {
            send(['error' => 'Task name cannot be empty'], 400);
        }

        $stmt = $conn->prepare("UPDATE tasks SET task = ?, description = ?, priority = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('sssii', $task, $description, $priority, $id, $uid);
    }

    $stmt->execute();
    send(['success' => true]);
}

// =====================  DELETE  =====================
elseif ($action === 'delete' && isset($input['id'])) {
    requireLogin();
    $uid  = $_SESSION['uid'];
    $id   = (int)$input['id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    send(['success' => true]);
}

/* ----------  auth routes  ---------- */
elseif ($action === 'register' && isset($input['username'], $input['password'])) {
    $out = register($input['username'], $input['password']);
    send($out, isset($out['error']) ? 400 : 200);
}
elseif ($action === 'login' && isset($input['username'], $input['password'])) {
    $out = login($input['username'], $input['password']);
    send($out, isset($out['error']) ? 401 : 200);
}
elseif ($action === 'logout') {
    logout();
    send(['success' => true]);
}
elseif ($action === 'whoami') {
     /* Tiny helper so JS can know whether to show login or todo screen */
    if (empty($_SESSION['uid'])) send(['guest' => true]);
    send(['uid' => $_SESSION['uid'], 'username' => $_SESSION['uname']]);
}



// ---------------------- UNKNOWN ACTION --------------------------
else {
    send(['error' => 'Invalid action'], 400);
}
?>