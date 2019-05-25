<?php
// ini_set('display_startup_errors', 1);
// ini_set('display_errors',1);
// error_reporting(E_ALL);

header("Content-Type: application/json");

require_once("../../classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();

use nuit\models\Step;

if (!isset($_GET["start"])) {
	$cats = \nuit\models\Category::all();

	$catsArr = [];

	foreach ($cats as $cat) {
		if ($cat->first == null)
			continue;

		$catsArr[] = $cat;
	}

	$catsJSON = [];

	foreach ($catsArr as $catArr) {
		$catsJSON[] = [
			"id" => $catArr->id,
			"name" => $catArr->text,
			"first" => $catArr->first
		];
	}

	exit(json_encode($catsJSON));
}

$start = intval($_GET["start"]);

function fetchSteps($start) {
	$arr = [];
	$arr[] = $start;

	$opts = \nuit\models\StepOption::custom("SELECT * FROM `step_options` WHERE `step_id`=$start->id");

	foreach ($opts as $opt) {
		$arr = array_merge($arr, fetchSteps(Step::find($opt->next)));
	}

	return $arr;
}

$steps = fetchSteps(Step::find($start));
// echo highlight_string("<?php\n\$data = ".var_export($steps,true).";\n").'</pre>';

function fetchStepOptions($arr) {
	$opts = [];

	foreach ($arr as $step) {
		$stepopts = \nuit\models\StepOption::custom("SELECT * FROM `step_options` WHERE `step_id`=$step->id");

		foreach ($stepopts as $opt) {
			$opts[] = $opt;
		}
	}

	return $opts;
}

$options = fetchStepOptions($steps);
// echo highlight_string("<?php\n\$data = ".var_export($options,true).";\n").'</pre>';

$optsJSON = [];
$usedOpts = [];
foreach ($options as $opt) {
	if (in_array($opt->id, $usedOpts))
		continue;

	$usedOpts[] = $opt->id;

	$optsJSON[] = [
		"id" => $opt->id,
		"step" => $opt->step->id,
		"type" => $opt->type,
		"text" => $opt->text,
		"next" => $opt->next
	];
}

$stepsJSON = [];
$usedSteps = [];
foreach ($steps as $step) {
	if(in_array($step->id, $usedSteps))
		continue;

	$usedSteps[] = $step->id;

	$stepsJSON[] = [
		"id" => $step->id,
		"type" => $step->type,
		"text" => $step->text
	];
}

$finObj = [
	"steps" => $stepsJSON,
	"options" => $optsJSON
];

echo json_encode($finObj);