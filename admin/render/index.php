<?php
/**
 * Created by PhpStorm.
 * User: scolton
 * Date: 2017-11-14
 * Time: 12:26
 */
require_once ("../../api/classroot/nuit/Conhelp.php");
\nuit\Conhelp::setup();

if (!isset ($_GET["workflow"]))
    die;

$workflowStarter = \nuit\models\Step::find(intval($_GET["workflow"]));

$workflow = [
    "first" => [
        "id" => $workflowStarter->id,
        "text" => $workflowStarter->text,
        "children" => []
    ]
];

function fetch_children($step) {
    $children = \nuit\models\StepOption::custom("SELECT * FROM `step_options` WHERE `step_id`=$step->id");

    $childs = [];
    foreach($children as $child) {
        $childNode = ["id" => $child->id, "text" => ($child->text ?? ($child->type == "Y" ? "Yes" : ($child->type == "N" ? "No" : "??")))];

        $nextStep = \nuit\models\Step::find($child->next);
        $childNode["children"] = [["id" => $nextStep->id, "text" => $nextStep->text, "children" => []]];

        $nextStep->type == "END" ? $childNode["children"][0]["children"] = [] : $childNode["children"][0]["children"] = fetch_children($nextStep);

        $childs[] = $childNode;
    }
    return $childs;
}

$workflow["first"]["children"] = fetch_children($workflowStarter);

$start = $workflow["first"];

function render($start) {
    $type = null;

    if (sizeof($start["children"]) == 0)
        $type = "end";

    if ($start["text"] == "Yes" || $start["text"] == "No")
        $type = "yn";

    if (sizeof($start["children"]) == 1 && ($start["text"] != "Yes" && $start["text"] != "No"))
        $type = "mca";

    $type = $type ?? "mcq";

    $str = "<div class='item $type'><div class='title'><span>".$start["text"]."</span></div>";
    if (sizeof($start["children"]) > 0)
        $str .= "<div class='connections'><canvas></canvas></div><div class='children'>".render_children($start)."</div></div>";
    else
        $str .= "</div>";
    return $str;
}

function render_children($start) {
    $str = "";

    foreach ($start["children"] as $child) {
        $str .= render($child);
    }

    return $str;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Render</title>
        <style type="text/css">
            * {
                font-family: sans-serif;
            }

            html, body {
                height: 100%;
            }

            body {
                margin: 0;
            }

            div.item {
                display: flex;
                position: relative;
                align-items: stretch;
            }

            div.item.end > div.title > span {
                background-color: #ef6969;
                border-radius: 40px;
            }

            div.item.mca > div.title > span {
                background-color: #8af294;
                border-radius: 0;
            }

            div.item.mcq > div.title > span {
                background-color: #eef28a;
            }

            div > div.title > span {
                display: inline-block;
                position: relative;

                padding: 15px;
                border-radius: 3px;
                border: 1px solid #000;
                margin-top: 5px;
                margin-bottom: 5px;
                text-align: center;
            }

            div > div.title,
            div > div.connections,
            div > div.children {
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            canvas {
                height: 100%;
                width: 150px;
            }
        </style>
        <script>
            window.drawCurves = function(cvs) {
                let ctx = cvs.getContext("2d");

                cvs.width = cvs.offsetWidth * 2;
                cvs.height = cvs.offsetHeight * 2;

                ctx.scale(2,2);

                let children = cvs.parentElement.parentElement.children[2].children;

                ctx.beginPath();
                for (let i = 0; i < children.length; i++) {
                    let useOffset = children[i].offsetTop === 1 ? 0 : children[i].offsetTop;

                    let endy = useOffset + children[i].offsetHeight / 2;
                    let starty = cvs.offsetHeight / 2;

                    let startx = 0;
                    let endx = cvs.offsetWidth;

                    let midy = starty;
                    let midx = endx / 2;

                    let mid2y = endy;
                    let mid2x = midx;

                    ctx.moveTo(startx, starty);
                    ctx.lineTo(startx + 25, starty);
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.moveTo(startx + 25, starty);
                    ctx.bezierCurveTo(midx, midy, mid2x, mid2y, endx - 25, endy);
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.moveTo(endx - 25, endy);
                    ctx.lineTo(endx, endy);
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.moveTo(endx, endy);
                    ctx.lineTo(endx - 10, endy + 3);
                    ctx.lineTo(endx - 10, endy - 3);
                    ctx.lineTo(endx, endy);
                    ctx.fillStyle = "black";
                    ctx.fill();
                }
            };

            window.init = function() {
                let canvases = document.querySelectorAll("canvas");

                for (let i = 0; i < canvases.length; i++) {
                    canvases[i].addEventListener("resize", drawCurves);
                    drawCurves(canvases[i]);
                }
            };

            window.addEventListener("load", init);
        </script>
    </head>
    <body>
        <?= render($start) ?>
    </body>
</html>