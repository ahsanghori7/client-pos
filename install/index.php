<?php 
header('Content-type: text/html; charset=ISO-8859-1');
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);


$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ?  "https" : "http");
$base_url .= "://".$_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Repair/Workshop Management System - Installation</title>
    <link rel="stylesheet" type="text/css" href="<?=$base_url;?>css/bootstrap-cosmo.css"/>
    <link href="<?=$base_url;?>css/custom.css" rel="stylesheet" type="text/css" />
    <link href="<?=$base_url;?>css/font-awesome.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        html{ height: 100%; }
        body { padding-bottom:40px; height:auto; background:url("img/bg2.png"); }
        form { margin:0; }
    </style>
</head>
<body>
    <div id="install-header">
        <img src="<?=$base_url;?>img/logo_install.png" width="300px" />
    </div>
    <div class="install">
        <?php 
        require("install.php");
        ?>
    </div>
    <script src="js/jquery.js"></script>
    <script type="text/javascript" src="<?=$base_url;?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?=$base_url;?>js/validation.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('form').form();
            $('.tip').tooltip();
        });
    </script>      
</body>
</html>