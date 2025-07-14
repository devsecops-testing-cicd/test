<?php
/* ──────────────────────────────────
   *  VERY VULNERABLE DEMO APP
   *  ───────────────────────────────
   *  VULNERABILITIES INCLUDED
   *   1. Reflected XSS                (?name=<script>alert(1)</script>)
   *   2. Stored  XSS                  (comment form)
   *   3. SQL Injection                (?user=admin' OR '1'='1&pass=x)
   *   4. Command Injection            (?cmd=ls+-al)
   *   5. Local File Inclusion (LFI)   (?page=../../etc/passwd)
   *   6. Open Redirect                (?url=https://evil.example)
   *   7. CSRF‑less state‑changing form
   *
   *  Run with:  php -S 127.0.0.1:8000 index.php
   *
   *  ───────────────────────────────── */

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ── SQLite setup (auto‑create on first run) ── */
$db = new SQLite3('demo.db');
$db->exec("CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, msg TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, username TEXT, password TEXT)");
$db->exec("INSERT OR IGNORE INTO users (id, username, password) VALUES (1,'admin','admin')");

/* ── Parameters (no validation on purpose) ── */
$name = $_GET['name']  ?? '';
$user = $_GET['user']  ?? '';
$pass = $_GET['pass']  ?? '';
$cmd  = $_GET['cmd']   ?? '';
$page = $_GET['page']  ?? '';
$url  = $_GET['url']   ?? '';

/* ── 1. Reflected XSS ── */
$greeting = $name ? "Hello, $name!" : '';

/* ── 2. Stored XSS (comment form POST) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->exec("INSERT INTO comments (msg) VALUES ('{$_POST['comment']}')");
}

/* ── 3. SQL injection (login check) ── */
$loginResult = '';
if ($user || $pass) {
    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $res = $db->querySingle($sql, true);
    $loginResult = $res ? "Logged in as {$res['username']}" : 'Login failed';
}

/* ── 4. Command injection ── */
$cmdOutput = $cmd ? shell_exec($cmd) : '';

/* ── 5. Local File Inclusion (suppressed errors for readability) ── */
$lfiContent = '';
if ($page) {
    ob_start();
    @include $page;
    $lfiContent = ob_get_clean();
}

/* ── 6. Open Redirect ── */
if ($url) {
    header("Location: $url");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Super‑Insecure PHP Demo</title></head>
<body>
  <h1>Super‑Insecure PHP Demo</h1>

  <!-- 1. Reflected XSS -->
  <form method="GET">
    <label>Your name: <input name="name"></label>
    <button>Say hi</button>
  </form>
  <p><?= $greeting ?></p>

  <!-- 2. Stored XSS -->
  <h2>Guestbook</h2>
  <form method="POST">
    <textarea name="comment" rows="3" cols="40"></textarea><br>
    <button>Leave comment</button>
  </form>
  <ul>
    <?php
      $res = $db->query("SELECT msg FROM comments ORDER BY id DESC");
      while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
          echo "<li>{$row['msg']}</li>"; // unsanitized → stored XSS
      }
    ?>
  </ul>

  <!-- 3. SQL injection login -->
  <h2>Login (vulnerable to SQLi)</h2>
  <form>
    <input name="user" placeholder="username">
    <input name="pass" placeholder="password">
    <button>Login</button>
  </form>
  <p><?= $loginResult ?></p>

  <!-- 4. Command injection -->
  <h2>Run a shell command</h2>
  <form>
    <input name="cmd" placeholder="e.g. ls -al">
    <button>Run</button>
  </form>
  <pre><?= htmlspecialchars($cmdOutput) ?></pre>

  <!-- 5. Local File Inclusion -->
  <h2>Include a file</h2>
  <form>
    <input name="page" placeholder="/etc/passwd">
    <button>Include</button>
  </form>
  <pre><?= htmlspecialchars($lfiContent) ?></pre>

  <!-- 6. Open Redirect -->
  <h2>Redirect to another site</h2>
  <form>
    <input name="url" placeholder="https://example.com">
    <button>Go</button>
  </form>

  <!-- 7. CSRF‑less state change -->
  <h2>Reset Guestbook (no CSRF token)</h2>
  <form method="POST" action="?reset=1">
    <button style="color:red">Wipe all comments</button>
  </form>
  <?php
    if (isset($_GET['reset'])) {
        $db->exec("DELETE FROM comments");   // no CSRF protection
        echo "<p>Guestbook wiped.</p>";
    }
  ?>
</body>
</html>
