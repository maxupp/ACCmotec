<?php

function generateRandomZipfile($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString . '.zip';
}

function return_bytes($val) {
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  $bytes = intval($val);
  switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $bytes *= 1024;
      case 'm':
        $bytes *= 1024;
      case 'k':
        $bytes *= 1024;
  }

  return $bytes;
}
$target_dir = "/srv/motec_data/";
$target_file = $target_dir . generateRandomZipfile();
$upload = 'err';
$allowTypes = array('zip');
$uploadOk = 1;
$fileType = strtolower(pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION));


// Check if file already exists
if (file_exists($target_file)) {
  //echo "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > return_bytes(ini_get('post_max_size'))) {  //500Mb  - 524288000
  //echo "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($fileType != "zip") {
  //echo "File format not supported, please supply zip file.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  //echo "Sorry, your file was not uploaded.";
  $upload = 'err';
// if everything is ok, try to upload file
} else {
  if(in_array($fileType, $allowTypes)) {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      $upload = 'ok';
      //echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])) . " has been uploaded. It will be processed and available shortly. Thank you for your contribution!";
    } else {
      $upload = 'err';
      //echo "Sorry, there was an error uploading your file.";
    }
  }
}
echo $upload;
?>