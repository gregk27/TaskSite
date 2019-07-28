<?php
include("header.php");

if(USER["ID"] == -1){
    echo "<div style=\"text-align:center; background-color:#e9e9e9; padding:25px 100px; margin:50px;\">No one has posted yet.</div>";
    exit();
}
$stmt = $conn->prepare("SELECT * from tasks.tasks WHERE (heads REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) OR (contributors REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) ORDER BY (heads REGEXP CONCAT('[[:<:]]',?,'[[:>:]]')) DESC, progress DESC");
$user = USER;
$stmt -> bind_param("iii", $user["ID"], $user["ID"], $user["ID"]);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$fullTitle = true;
foreach ($tasks as $task){
    include ("tasks/small.php");
}

?>