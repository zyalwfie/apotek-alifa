<?php
ob_start();
require_once '../../auth_functions.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apotek Alifa | Dashboard</title>

    <?php include('partials/links.php') ?>

</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include('partials/sidebar.php') ?>

        <!--  Main wrapper -->
        <div class="body-wrapper">

            <?php include('partials/header.php') ?>

            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <?php include('content.php') ?>

                    <?php include('partials/footer.php') ?>
                </div>
            </div>
        </div>

    </div>

    <?php include('partials/scripts.php') ?>
</body>

</html>
<?php ob_end_flush() ?>