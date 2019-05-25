<?php
require_once("../../classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();

use nuit\models\Category;

$categories = Category::all();

$catsJSON = [];
foreach ($categories as $category) {
	if ($category->super != null)
		continue;

	$catsJSON[] = [
		"id" => $category->id,
		"first" => $category->first,
		"text" => $category->text
	];
}

echo json_encode($catsJSON);