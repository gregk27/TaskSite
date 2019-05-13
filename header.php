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
		$stmt = $conn->prepare("SELECT `username` FROM `tasks`.`users` WHERE `ID`= ?"); 
		$stmt->bind_param("i", $ID);
		$stmt->execute();
		
        $users = $stmt->get_result()->fetch_assoc();

        echo "<a class='right' href = '/user/user.php'>" . $users["username"] . "</a>";
    } else {
        echo "<a class='right' href='/user/user.php'>Sign in</a>";
    }
    mysqli_close($conn);
    ?>

  </nav>
</header>
