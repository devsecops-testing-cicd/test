<?php
// index.php
$name = $_GET['name'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <title>XSS Demo</title>
</head>
<body>
  <h1>Welcome to the XSS demo!</h1>
  <form method="GET" action="">
    <label for="name">Enter your name:</label>
    <input type="text" name="name" id="name" />
    <button type="submit">Submit</button>
  </form>
  <p>Hello, <?php echo $name; ?>!</p>
</body>
</html>
