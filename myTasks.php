<?php
include("header.php");

if(USER["ID"] == -1){
    echo "<div class='error'>Please sign in to view this page.</div>";
    exit();
}
$stmt = $conn->prepare("SELECT * from tasks.tasks WHERE (heads REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) OR (contributors REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) ORDER BY (heads REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) DESC, progress DESC");
$user = USER;
$stmt -> bind_param("iii", $user["ID"], $user["ID"], $user["ID"]);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$topLevel = -1;
include("tasks/filterPage.php");

?>