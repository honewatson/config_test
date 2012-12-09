<?php

function get_contents($filename){
    $contents = file_get_contents(api\base_path::$base_path.$filename);
    $contents = explode('/* class */', $contents);
    if(!isset($contents[1])) echo $filename;
    return $contents[1];
}

$content = "<?php\nnamespace api;\n";
//$content .= get_contents("faux_singleton.php");
$content .= get_contents("api.php");
$content .= get_contents("app.php");
$content .= get_contents("autoload.php");
$content .= get_contents("base_path.php");
$content .= get_contents("controller.php");
$content .= get_contents("core_app_routes.php");
$content .= get_contents("factory.php");
//$content .= get_contents("factory_instance.php");
$content .= get_contents("folder_params.php");
$content .= get_contents("loader.php");
$content .= get_contents("request.php");
$content .= get_contents("reusable.php");
//$content .= get_contents("reusable_instance.php");
$content .= get_contents("reusable_keys.php");
//$content .= get_contents("reusable_instance_keys.php");
$content .= get_contents("router_main.php");
$content .= get_contents("router_page.php");
$content .= get_contents("template_path.php");
$content .= get_contents("view.php");
$content .= get_contents("response.php");
$content .= get_contents("db/db.php");
file_put_contents(api\base_path::$base_path."compiled.php", $content);
