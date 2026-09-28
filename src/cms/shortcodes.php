<?php
require_once "constants.php";

// to add a shortcode, write a php function for the shortcode below,
// and then add a string to SHORTCODES with the name of the function
$SHORTCODES = [
    'gallery_shortcode',
];

/****************************************************************************************/
// Gallery
function gallery($matches) {
    $folder = SRC_DIR . "/" . trim($matches[1], "/") . "/";
    $images = glob("$folder{*.jpg,*.jpeg,*.png,*.gif, *.svg}", GLOB_BRACE);
    $output = '<div class="gallery">';
    foreach ($images as $img) {
        $publicPath = str_replace(SRC_DIR, '', $img);
        $output .= '<a href="' . $publicPath . '" alt="' . $publicPath . '" style="cursor:zoom-in"><img src="' . $publicPath . '" alt="' . $publicPath . '" loading="lazy"></a>';
    }
    $output .= '</div>';
    return $output;
}

function gallery_shortcode($html) {
    // Replace tags with gallery HTML
    $html = preg_replace_callback("|<!-- GALLERY: (.*) -->|", 'gallery', $html);
    $html = preg_replace_callback("/\[gallery (.*)\]/", 'gallery', $html);
    return $html;
}
/****************************************************************************************/