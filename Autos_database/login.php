<!-- Model -->
<?php
require_once "pdo.php";
if (isset($_POST['cancel'])) {
  header("Location: index.php");
  return;
}
$prefix = 'AmMaR_';
$usedHash = hash('md5', 'AmMaR_php123'); //Password is php123
$failure = false;
if (isset($_POST['who']) && isset($_POST['pass'])) {
  if (strlen($_POST['who']) < 1 || strlen($_POST['pass']) < 1) {
      $failure = "User name and password are required";
  } elseif (strpos($_POST['who'], '@') === false) {
    $failure = "Email must have an at-sign (@)";
  } else {
    $check = hash('md5', $prefix.$_POST['pass']);
    if ($check === $usedHash) {
      error_log('Login success '.$_POST['who']);
      header("Location: autos.php?name=".urlencode($_POST['who']));
      return;
    } else {
      $failure = "Incorrect password";
      error_log("Login fail ".$_POST['who']." $check");
    }
  }
}
?>

<!-- View -->

<!DOCTYPE html>
<html>
<head>
  <title>Ammar Alhamoud - Autos Database</title>
</head>
<body>
  <h1>Please Log In</h1>
  <?php
  if ($failure !== false) {
    echo('<p style="color: red;">' . htmlentities($failure) . "</p>\n");
  }
  ?>
  <form method="POST">
    <label for="nam">User Name</label>
    <input type="text" name="who" id="nam"><br/>
    <label for="pwLabel">Password</label>
    <input type="text" name="pass" id="pwLabel"><br/>
    <input type="submit" value="Log In">
    <input type="submit" name="cancel" value="Cancel">
  </form>
  <p>
      For a password hint, view source and find a password hint
      in the HTML comments.
      <!-- Hint: The password is the three character name of this programing language (all lower case) followed by 123 -->
  </p>
</body>
</html>
