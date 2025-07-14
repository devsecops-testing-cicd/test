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
  </ul>

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
</body>
</html>
