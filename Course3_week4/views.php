<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Ammar Alhamoud</title>
  </head>

<?php
    session_start();
    require "pdo.php";
    if ( ! isset($_SESSION['name']) ) {
        die('Not logged in');
    } else {
        $name = $_SESSION['name'];
    }
?>

<body>
    <div class="container">
        <h1>Tracking Autos for <?php echo htmlentities($name); ?></h1>
        <h2>Automobiles</h2>
        <p>
            <a href="autos.php">Add New</a>
            |
            <a href="logout.php">Logout</a>

        </p>
        <?php
            if (isset($_SESSION['success'])) {
                echo "<p style='color: green'>".$_SESSION['success']."</p>";
                unset($_SESSION['success']);
            }
        ?>
        <ul>
            <?php

                $statement = $pdo->query("SELECT auto_id, make, year, mileage FROM autos");

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li> ";
                    echo $row['year']." ";
                    echo htmlentities($row['make'])." / ";
                    echo $row['mileage'];
                    echo "</li>";
                }
            ?>
        </ul>
    </div>
</body>

</html>
