<?php
/**
 * This file is designed in such a way that it can be run as a standalone script via the command:
 * php cms/export.php
 *
 * or it can be accessed via a web request to the URL /cms/export.php
 *
 * either of these ways of calling this will cause a rebuild of a .zip export to assets/export.zip
 *
 * the export contains all of the contents of src (excluding dist) zipped together into a .zip file 
 */
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

// Use a Log Buffer so that we can set the HTTP response code later.
$logBuffer = "";

try {
    require_once "utils.php";

    $sourceDir = SRC_DIR;
    $zipFile = SRC_DIR . '/assets/export.zip';
    if (php_sapi_name() == 'cli') {
        echo "zip: " . $zipFile . "\n";
    }

    if (!extension_loaded('zip')) {
        throw new Exception("The ZIP extension is not installed.");
    }

    $zip = new ZipArchive();

    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Could not create assets/export.zip");
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    // recursive iterator of src that excludes "dist" directory
    $dirIterator = new RecursiveDirectoryIterator($sourceDir);
    $filteredIterator = new RecursiveCallbackFilterIterator($dirIterator, function ($current) {
        if ($current->isDir() && $current->getFilename() === 'dist') {
            return false; // Skip the "dist" directory
        }
        return true;
    });
    $files = new RecursiveIteratorIterator($filteredIterator, RecursiveIteratorIterator::LEAVES_ONLY);


    // go through each file and add it to the zip other than the .zip itself 
    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue; // Skip directories
        }
        $filePath = $file->getRealPath();
        if ($filePath === realpath($zipFile)) {
            continue; // don't rezip the export.zip file if one already exists
        }

        $relativePath = substr($filePath, strlen($sourceDir) + 1);
        $zip->addFile($filePath, $relativePath);
    }

    $zip->close();

    // if this is a web request, send a 200 response
    if (php_sapi_name() !== 'cli') {
        http_response_code(200);
        $logBuffer .= "Successfully created export in assets/export.zip\n";
    } else {
        echo "successfully created export in assets/export.zip\n";
    }
    
    echo "$logBuffer";
} catch (Exception $e) {
    // Handle errors and respond with a 500 status code
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        header('Content-Type: text/plain');
        echo "An error occurred: " . $e->getMessage();
        echo "\n\n$logBuffer";
    } else {
        // Log the error to the console when running via CLI
        fwrite(STDERR, "An error occurred: " . $e->getMessage() . "\n");
        echo "$logBuffer\n";
    }

    // Exit the script with an error code? - can't do this if we want to display a result!
    exit(0);
}
