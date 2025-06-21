<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    if (file_exists("../../$page.php")) {
        include "../../$page.php";
    } else {
        include('../../not_found.php');
    }
} else {
    include "../../landing.php";
}
