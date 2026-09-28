<?php
require_once "utils.php";

// get the body content
$_src = null;
$path = null;
$ext = null;
$_content = null;

// decodeurl
$_SERVER['PATH_INFO'] = urldecode($_SERVER['PATH_INFO']);
// replace all spaces with underscores
$_SERVER['PATH_INFO'] = str_replace(" ", "_", $_SERVER['PATH_INFO']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_src = fopen("php://input", "r");
    $path = $_SERVER['PATH_INFO'];
    $ext = pathinfo($path, PATHINFO_EXTENSION);
} else if (isset($_SERVER['REDIRECT_URL'])) {
    $path = $_SERVER['REDIRECT_URL'];
    $_src = fopen(SRC_DIR . $path, "r") or die("File not found: " . $path);
    $ext = pathinfo(SRC_DIR . $path, PATHINFO_EXTENSION);
    header("Last-Modified: " . date("r", filemtime(SRC_DIR . $path)));
} else {
    $path = $_SERVER['PATH_INFO'];
    $_src = fopen(SRC_DIR . $path, "r") or die("File not found: " . $path);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    header("Last-Modified: " . date("r", filemtime(SRC_DIR . $path)));
}

$output = render_func($path, $ext, $_src);

echo $output;

?>