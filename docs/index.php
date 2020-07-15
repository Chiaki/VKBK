<?php

echo <<<E
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>VKBK Documentation</title>
    <link href="../favicon.png" rel="shortcut icon">
    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/fontawesome-all.min.css" rel="stylesheet">
    
    <style type="text/css">
	.text-white a { color: rgba(255, 255, 255, .85); }
	.bg-header { background-color: #597da3; }
	.bg-header small { color: rgba(255, 255, 255, .85); }
	.border-bottom { border-bottom: 1px solid #e5e5e5; }
	.box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
	.lh-100 { line-height: 1; }
	.lh-125 { line-height: 1.25; }
	.lh-150 { line-height: 1.5; }
    </style>
  </head>
  <body>

<div class="container">
    <div class="d-flex align-items-center p-3 my-3 text-white1 bg-header rounded box-shadow">
	<div class="lh-100">
          <h6 class="mb-0 text-white lh-100">VKBK Документация</h6>
          <small>Версия 0.8.13</small>
        </div>
    </div>
    
    <div class="my-3 p-3 bg-white rounded box-shadow">
        <h6 class="border-bottom border-gray pb-2 mb-0">Содержание</h6>
        <div class="text-muted pt-3">
          <div class="pb-0 mb-0 small lh-125">
            <span class="d-block">
		<h6>База данных</h6>
		    <ul>
E;

require_once('data_db.php');

foreach($db as $dbTable => $dbStructure){
    echo '<li><a href="db.php#'.$db_prefix.$dbTable.'">'.$db_prefix.$dbTable.'</li>';
}

echo <<<E
		</ul>
	    </span>
          </div>
        </div>
    </div>
    
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script type="text/javascript" src="../js/jquery-3.4.1.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    
  </body>
</html>
E;
?>