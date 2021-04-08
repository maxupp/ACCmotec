<?php
    $file = basename($_GET['file']);
    $file = '/srv/motec_data/'.$file;

    // Checking ZIP extension is available
    if(extension_loaded('zip')){

        // Check to see if the ldx file does not exist
        if(!file_exists($file.".ldx")){
            die($file.'.ldx - File not found');
        } else {

            $zip = new ZipArchive(); // Load zip library
            $zip_name = $file.".zip"; // Zip name - based around the ld/ldx filename
            if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
            {
                // Opening zip file to load files did not work
                die('Sorry'. $file .'.zip creation failed at this time');
            }

            // found the ld and ldx files and ready to add them to the zipfile
            $filename_in_zip = substr($file, strrpos($file,'/') + 1);
            $zip->addFile($file.".ld", $filename_in_zip.".ld"); // Adding files into zip
            $zip->addFile($file.".ldx", $filename_in_zip.".ldx"); // Adding files into zip

            $zip->close();

            // Check the temporary zipfile is now available and push to user
            if(file_exists($zip_name)){
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                // push to download the zip
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="'.$zip_name.'"');
                header("Content-Transfer-Encoding: binary");
                // read the file from disk
                readfile($zip_name);
                // remove zip file is exists in temp path
                unlink($zip_name);
            }
        }
    } else {
        die('You do not have the ZIP extension installed on the server');
    }
?>