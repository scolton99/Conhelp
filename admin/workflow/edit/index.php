<?php
require_once("../../../api/classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Conhelp</title>
		<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
		<link href="../../../assets/stylesheets/conhelp.css" rel="stylesheet" />
		<script>
			window.rel = "../../../";
		</script>
		<script src="../../../assets/scripts/conhelp.js"></script>
		<script src="https://use.fontawesome.com/370a81373c.js"></script>
		<script>
			window.addEventListener("load", initAdmin);
		</script>
	</head>
	<body>
		<header>
			<div id="flex-title-contaner" class="flex-header-container">
				<h1 id="title"><a href=".">Conhelp</a></h1>
			</div>
			<div id="flex-name-container" class="flex-header-container">
				<b>Spencer Colton</b>
			</div>
			<div id="flex-search-container" class="flex-header-container">
				<input autocomplete="off" type="text" name="search" placeholder="Search..." title="Search" id="search-bar">
			</div>
		</header>
		<section id="grid">
			<div class="loading"><span class="fa fa-spin fa-cog"></span></div>
		</section>
	</body>
</html>