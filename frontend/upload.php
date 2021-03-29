<?php
header("Content-Type: text/plain");
require_once('curlAPI.php');


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
$target_dir = "/uploads/";
$target_file = $target_dir . generateRandomZipfile();
$allowTypes = array('zip');
$uploadOk = 1;
$fileType = strtolower(pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION));

// Prepare JSON to inform user if the file uploaded successfully or alternatively what the error was
$status = [
  'response' => 'err',
  'message'  => '-'
];

// Check if file already exists
if (file_exists($target_file)) {
  $status['message'] = "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > return_bytes(ini_get('post_max_size'))) {  //500Mb  - 524288000
  $status['message'] = "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($fileType != "zip") {
  $status['message'] = "File format not supported, please supply zip file.";
  $uploadOk = 0;
}

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  $status['response'] = 'err';
// if everything is ok, try to upload file
} else {
  if(in_array($fileType, $allowTypes)) {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
       $data_array = array("filename" => $target_file);

      //  try {
      //   $response_raw = callAPI('POST', 'loader:1337/process_zip', json_encode($data_array));
      // } catch (Exception $e){          
      //   $status['response'] = 'err';
      //   $status['message'] = 'api call failed';
      // }

      // try {
      //   $response = json_decode($response_raw, true);

      //   if ($response['success']) {
      //     $status['response'] = 'ok';
      //     $status['message'] = "Debug: " . $response['success'] . " / " . $response['report'] . "-" . "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])) . " has been uploaded. It will be available in the next few minutes. Thank you for your contribution!";
      //   } else {
      //     $status['response'] = 'err';
      //     $status['message'] = "Debug: " . $response['success'] . " / " . $response['report'] . "-" . $response['report'] . "-";
      //   }
      // } catch (Exception $e){          
      //   $status['response'] = 'err';
      //   $status['message'] = "Debug: " . $response['success'] . " / " . $response['report'] . "-" . $response_raw . "-";
      // }
      //}
        $make_call = callAPI('POST', 'loader:1337/process_zip', json_encode($data_array));
        $response = json_decode($make_call, true);

        if ($response['success']) {
          $status['response'] = 'ok';
          $status['message'] = "Report: " . $response['report'] ." - The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])) . " has been uploaded. It will be available in the next few minutes. Thank you for your contribution!";
        } else {
          $status['response'] = 'err';
          $status['message'] = $response['report'];
        }
      } else {
        $status['response'] = 'err';
        $status['message'] = "Sorry, there was an error uploading your file.";
      }
  }
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($status);
?>

