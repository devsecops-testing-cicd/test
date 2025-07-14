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

/* ── 4. Command injection ── */
$cmdOutput = $cmd ? shell_exec($cmd) : '';
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
  <!-- 4. Command injection -->
  <h2>Run a shell command</h2>
  <form>
    <input name="cmd" placeholder="e.g. ls -al">
    <button>Run</button>
  </form>
  <pre><?= htmlspecialchars($cmdOutput) ?></pre>
