<?php
    if(isset($_COOKIE["token"])){
        setcookie("token", -1, time()-3600);
    }
?>