<html>
<head>
    <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<h1>ArisDrives Motec Server</h1>
<p>
    This is an effort to build an extensive collection of motec data for as many car/track combinations as possible.    
</p>
<p>
    If you would like to contribute, just zip your motec folder and upload it using the button below.
</p>
<!DOCTYPE html>
<form action="upload.php" method="post" enctype="multipart/form-data">
  Select .zip file to upload:
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input type="submit" value="Upload Zipfile" name="submit">
</form>


<form action="https://www.paypal.com/donate" method="post" target="_top">
<input type="hidden" name="hosted_button_id" value="RCTKH7F9FU77L" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
<img alt="" border="0" src="https://www.paypal.com/en_DE/i/scr/pixel.gif" width="1" height="1" />
</form>

<?php 
$username = "motec"; 
$password = "motec4thepeople"; 
$database = "motec_db"; 
$mysqli = new mysqli("db", $username, $password, $database); 
$query = "SELECT * FROM telemetry ORDER BY track";

echo '<table border="0" cellspacing="2" cellpadding="2"> 
      <tr> 
          
          <td> <font face="Arial">Track</font> </td> 
          <td> <font face="Arial">Car</font> </td> 
          <td> <font face="Arial">Date</font> </td> 
          <td> <font face="Arial">Time</font> </td> 
          <td> <font face="Arial">Best Laptime</font> </td> 
          <td> <font face="Arial">Best Lap</font> </td>
          <td> <font face="Arial">ld Download</font> </td> 
          <td> <font face="Arial">ldx Download</font> </td> 
      </tr>';

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $field1name = $row["track"];
        $field2name = $row["car"];
        $field3name = $row["date"];
        $field4name = $row["time"];

        $whole = intval($row["best_time"]); 
        $decimal1 = $row["best_time"] - $whole; 
        $decimal2 = round($decimal1, 2);
        $decimal = substr($decimal2, 1);

        $field5name = gmdate("i:s", $row["best_time"]) . $decimal; 
        $field6name = $row["best_lap"];
        $field7name = $row["filename"] . '.ld';
        $field8name = $row["filename"] . '.ldx';

        echo '<tr> 
                  <td>'.$field1name.'</td> 
                  <td>'.$field2name.'</td> 
                  <td>'.$field3name.'</td> 
                  <td>'.$field4name.'</td> 
                  <td>'.$field5name.'</td> 
                  <td>'.$field6name.'</td> 
                  <td><a href="download.php?file='.$field7name.'">Download</a></td> 
                  <td><a href="download.php?file='.$field8name.'">Download</a></td> 
              </tr>';
    }
    $result->free();
} 
?>
</body>
</html>
