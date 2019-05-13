<header>
	<h1>Tasks</h1>
	<nav>
		<a href="/index.php">Home</a> <a href="">Test 1</a> <a href="">Test 2</a>
    <?php
    include $_SERVER['DOCUMENT_ROOT']."/passwords.php";
    $conn = new mysqli($dbAddress, $dbUser, $dbPass);
    if ($conn->connect_error) {
        die("Connection failed" . $conn->connect_error);
    }

    if (isset($_COOKIE["token"])) {
        $ID = $_COOKIE["token"];
        $users = $conn->query("SELECT `username` FROM `tasks`.`users` WHERE `ID`=" . $ID)->fetch_assoc();

        echo "<a class='right' href = '/user/user.php'>" . $users["username"] . "</a>";
    } else {
        echo "<a class='right' href='/user/user.php'>Sign in</a>";
    }
    mysqli_close($conn);
    ?>

  </nav>
</header>
