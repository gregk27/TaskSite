<header>
    <script>
        function signout(){
            xhttp.open("GET", "/signout.php");
            xhttp.send();

            location.reload();
        }

    </script>
	<h1>Tasks</h1>
	<nav>
		<a href="/">Home</a> <a href="/tasks">Tasks</a><a href="/subteams">Subteams</a> <a href="/users">Users</a>
    <?php
    require_once ($_SERVER['DOCUMENT_ROOT']."/include.php");
    if ($conn->connect_error) {
        die("Connection failed" . $conn->connect_error);
    }

    if(USER["ID"] == -1){
        echo "<a class='right' href='/user'>Sign in</a>";
    } else {
        echo "<a class='right' id='users' href = '/user'>" . USER["name"] . "</a><a class='dropdown' onclick='signout()'>Sign out</a>";
    }

    ?>

  </nav>
</header>
