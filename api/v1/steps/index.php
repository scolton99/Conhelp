<?php
require_once("../../classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();

use nuit\models\Step;

$steps = Step::all();

