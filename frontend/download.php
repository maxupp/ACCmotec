<?php
    $file = basename($_GET['file']);
    $file = '/srv/motec_data/'.$file;

    if(!file_exists($file)){ // file does not exist
        die('File not found');
    } else {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$file");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");

        // read the file from disk
        readfile($file);
    }

?>