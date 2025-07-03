<?php
$user = getUserData();

if (isset($_GET['page'])) {
    $url = $_GET['page'];
    $arrayUrl = explode('.', $url);

    if (count($arrayUrl) > 1) {
        if (file_exists("../../dashboard/admin/$arrayUrl[0]/$arrayUrl[1].php") || file_exists("../../dashboard/user/$arrayUrl[0]/$arrayUrl[1].php")) {
            if ($user['peran'] === 'admin') {
                include "../../dashboard/admin/$arrayUrl[0]/$arrayUrl[1].php";
            } else {
                include "../../dashboard/user/$arrayUrl[0]/$arrayUrl[1].php";
            }
        } else {
            include('../../dashboard/not_found.php');
        }
    } elseif (count($arrayUrl) < 2) {
        if (file_exists("../../dashboard/admin/$arrayUrl[0].php") || file_exists("../../dashboard/user/$arrayUrl[0].php")) {
            if ($user['peran'] ===  'admin') {
                include "../../dashboard/admin/$page.php";
            } else {
                include "../../dashboard/user/$page.php";
            }
        } else {
            include "../../dashboard/not_found.php";
        }
    }
} else {
    if ($user['peran'] === 'admin') {
        include "../../dashboard/admin/index.php";
    } else {
        include "../../dashboard/user/index.php";
    }
}
