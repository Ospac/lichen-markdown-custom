<?php
require_once "shortcodes.php";
require_once "constants.php";
require_once "vendor/php-markdown-lib-9.1/Michelf/MarkdownExtra.inc.php";

use Michelf\MarkdownExtra;


/**
 *
 * Get the page title according to the following priorities:
 * 
 * 1. get explicit page title
 * 2. get explicit header title
 * 3. get implicit header title
 * 4. get implicit page title
 *
 *
 * @param   string  $header The html of the header
 * @param   string  $body The html of the body
 * @return  string  The title 
 *
 */
function get_title($header, $body)
{
    //Stop deprecation errors with PHP 8 when editing the header
    if ($header === null)
        $header = "";
    if ($body === null)
        $body = "";

    // Get explicit page title
    preg_match_all('|<!-- TITLE:(.*) -->|', $body, $matches);
    $title = trim(implode($matches[1]));

    // Else get explicit header title
    if ($title == '') {
        preg_match_all('|<!-- TITLE:(.*) -->|', $header, $matches);
        $title = trim(implode($matches[1]));
    }

    // Else get implicit header title
    if ($title == '') {
        preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $header, $headings);
        $title = trim(implode($headings[1]));
    }

    // Else get implicit body title
    if ($title == '') {
        preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $body, $headings);
        $title = trim(implode($headings[1]));
    }

    return $title;
}


/**
 *   render_func takes in
 *   - path: a string path to the markdown file to be rendered
 *   - ext: a string of the file extension (.md)
 *   - _src: an open file handle for the file
 *
 *   and it returns a string of the HTML file rendered from that markdown file,
 *   with appropriate headers and title
 *
 */
function render_func($path, $ext, $_src = "")
{

    // Get rendered html and parse metadata title
    if ($ext == "md") {
        $body = markdown_to_html($_src);
    } else if ($ext == "html" or $ext == "htm") {
        //Source is HTML anyway
        $body = "";
        while (!feof($_src)) {
            $body .= fgets($_src);
        }
    }
    fclose($_src);

    // Handle header and footer
    $header = null;
    $footer = null;
    $headerTitle = null;

    $editingHeader = strpos($path, 'header.md');
    $editingFooter = strpos($path, 'footer.md');
    $editingHtml = $ext == 'html' || $ext == 'htm';

    // if rendering header or footer, they should be rendered in their correct layout-position,
    if ($editingHeader) {
        $header = $body;
        $body = "";
        $footer = "";
    } else if ($editingFooter) {
        $footer = $body;
        $header = "";
        $body = "";
        // if html is being editied, header or footer should not be displayed
    } else if ($editingHtml) {
        $header = "";
        $footer = "";
    } else {
        $header = render_l11n_layout("header.md", $path);
        $footer = render_l11n_layout("footer.md", $path);
    }

    $title = get_title($header, $body);

    ob_start();
    include SRC_DIR . "/theme/layout.php";
    $output = ob_get_clean();
    return rewrite_asset_paths($output, $path);
}

/*
 *   If the inputted file is a .md file, then it renders it and then saves the render to dist
 *   otherwise it just copies the input file to dist into the correct location
 */
function save_dist($relative_src_path)
{
    $ext = pathinfo($relative_src_path, PATHINFO_EXTENSION);
    if ($ext == "md") {
        $_md_src = fopen(SRC_DIR . $relative_src_path, "r") or die("File not found: " . $relative_src_path);
        $output = render_func($relative_src_path, $ext, $_md_src);
        $output_dest_path = DIST_DIR . preg_replace('"\.md$"', '.html', $relative_src_path);
    } else {
        $output_dest_path = DIST_DIR . $relative_src_path;
    }
    $directoryPath = dirname($output_dest_path);
    // check if the directory exists that will contain the rendered html file
    if (!is_dir($directoryPath)) {
        mkdir($directoryPath, 0755, true);
    }
    // if its a markdown source file, then copy the actual html over there
    if ($ext == "md") {
        file_put_contents($output_dest_path, $output);
    }
    // otherwise just create a symlink back to the source file (in order to save space)
    else {
        $targetPath = $output_dest_path;
        $absoluteSrcPath = SRC_DIR . $relative_src_path;
        @symlink($absoluteSrcPath, $targetPath);
    }
}

function save_all_to_dist($dir)
{
    // RecursiveDirectoryIterator to iterate through the directory
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            function ($current, $key, $iterator) {
                // Skip directories named "theme", or "cms" or "dist"
                $skipDirs = ['theme', 'cms', 'dist', 'update'];
                if ($current->isDir() && in_array($current->getBasename(), $skipDirs)) {
                    return false;  // Skip this directory
                }
                return true;  // Otherwise, include this file/directory
            }
        ),
        RecursiveIteratorIterator::LEAVES_ONLY // Only return files, not directories
    );
    foreach ($iterator as $file) {

        // process only regular files (not directories)
        if ($file->isFile()) {
            $absolutePath = $file->getRealPath();
            $relativePath = "/" . str_replace(SRC_DIR . DIRECTORY_SEPARATOR, '', $absolutePath);
            save_dist($relativePath); // save_dist on the file
        }
    }
}


/* Takes in a markdown file and renders it to HTML using MarkdownExtra.
 *  Additionally: for each registered shortcode, it parses the file for shortcodes and renders them appropriately.
 */
function markdown_to_html($file)
{
    global $SHORTCODES;

    $source = "";

    while (!feof($file)) {
        $source = $source . fgets($file);
    }

    $html = MarkdownExtra::defaultTransform($source);

    // For each registered shortcode, call the shortcode function on the HTML
    foreach ($SHORTCODES as $shortcode) {
        if (function_exists($shortcode)) {
            $html = $shortcode($html);
        }
    }

    return $html;
}

function delTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = "$dir/$file";

        // If it's a symlink, just unlink it (do not follow into it)
        if (is_link($path)) {
            unlink($path);

            // If it's a directory (and not a symlink), recurse
        } elseif (is_dir($path)) {
            delTree($path);

            // Otherwise, regular file — delete it
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

/*
 *   return a rendering of a markdown file if it exist, otherwise return null
 */
function render_file_if_exist_or_empty_string($path)
{
    if (file_exists($path)) {
        $src = fopen($path, 'r');
        $rendered = markdown_to_html($src);
        fclose($src);
        return $rendered;
    } else {
        return "";
    }
}

/*
 *  Render $fileName from /l11n/<LANG>/, if $targetPath is in /l11n/<LANG>/ dir. Useful for getting l11n header and footer when getting localized pages.
 *  This l11n feature works from a configuration where localized versions of the site is in /l11n/<LANG>/.
 */
function render_l11n_layout($fileName, $targetPath)
{
    $pathArray = explode("/", $targetPath);
    $file_path = SRC_DIR . "/" . $fileName;
    if ($pathArray[1] == "l11n") {
        $lang = $pathArray[2];
        $file_path = SRC_DIR . "/l11n" . "/" . $lang . "/" . $fileName;
    }

    return render_file_if_exist_or_empty_string($file_path);
}

// 마크다운 조각 파일을 HTML로 렌더링 (없으면 빈 문자열)
function render_component($name) {
    $path = SRC_DIR . "/" . $name . ".md";
    if (!file_exists($path)) return "";
    $src = fopen($path, "r");
    $html = markdown_to_html($src);
    fclose($src);
    return $html;
}

// Return an asset URL relative to the rendered page's directory.
function asset_url($pagePath, $assetPath) {
    $pageDirectory = trim(str_replace('\\', '/', dirname($pagePath)), '/');
    $depth = $pageDirectory === '' ? 0 : count(explode('/', $pageDirectory));

    return str_repeat('../', $depth) . ltrim($assetPath, '/');
}

// Static pages cannot resolve root-relative asset URLs when served below a domain root.
function rewrite_asset_paths($html, $pagePath) {
    return preg_replace_callback(
        '/(["\'])\/assets\/([^"\']*)/',
        function ($matches) use ($pagePath) {
            return $matches[1] . asset_url($pagePath, 'assets/' . $matches[2]);
        },
        $html
    );
}
