<?php
require_once("../../classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();

use nuit\models\Category;

$super = $_GET["super"];

$subcats = Category::custom("SELECT * FROM `categories` WHERE `super`=$super");
$subcatsJSON = [];

foreach ($subcats as $subcat) {
	$subcatsJSON[] = [
		"id" => $subcat->id,
		"text" => $subcat->text,
		"first" => $subcat->first
	];
}

echo json_encode($subcatsJSON);