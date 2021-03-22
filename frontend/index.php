<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css" >
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" >

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" >
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" >
</head>
<body>
    <div id="container" class="jumbotron">
<h1>ArisDrives Motec Server</h1>
<p>
    This is an effort to build an extensive collection of motec data for as many car/track combinations as possible.    
</p>
<p>
    If you would like to contribute, just zip your motec folder and upload it using the button below. <br>
    The loader relies upon the .ld file not being renamed and having an ldx file with same name present as well. Otherwise they will be ignored.
    <br>
    For the time being there is a size limit of <strong><?php echo ini_get('post_max_size'); ?></strong> due to limitations in hosting and bandwidth.
</p>
<form action="upload.php" method="post" enctype="multipart/form-data">
Select .zip file to upload:
<input type="file" class="btn btn-success" name="fileToUpload" id="fileToUpload">
<input type="submit" class="btn btn-primary" value="Start Upload" name="submit">
</form>
</div>  <!-- div id=container -->

<div id="donate">
    <form action="https://www.paypal.com/donate" method="post" target="_top">
        Donations go towards hosting and maintenance. And ACC DLC. <input type="hidden" name="hosted_button_id" value="RCTKH7F9FU77L" />
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
    <img alt="" border="0" src="https://www.paypal.com/en_DE/i/scr/pixel.gif" width="1" height="1" />
    </form>
</div> <!-- div id=donate -->
<table id="motecData" class="table is-striped table-bordered" style="width:100%">
    <thead>
      <tr> 
          
          <td> Track </td> 
          <td> Car </td> 
          <td> Date </td> 
          <td> Time of Day </td> 
          <td> Best Laptime </td> 
          <td> Best Lap </td>
          <td> Motec Download </td>
          <td> LDX Download </td>
      </tr>
    </thead>
    <tbody>
<?php 
$username = "motec"; 
$password = "motec4thepeople"; 
$database = "motec_db"; 
$mysqli = new mysqli("db", $username, $password, $database); 
$query = "SELECT * FROM telemetry ORDER BY track";

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $field1name = $row["track"];
        $field2name = $row["car"];
        $field3name = $row["date"];
        $field4name = $row["time"];

        $whole = intval($row["best_time"]); 
        $decimal1 = $row["best_time"] - $whole; 
        $decimal = substr($decimal1, 1, 3);

        $field5name = gmdate("i:s", $row["best_time"]) . $decimal; 
        $field6name = $row["best_lap"];
        $field7name = $row["filename"] . '.ld';
        $field8name = $row["filename"] . '.ldx';

        echo '<tr> 
                  <td>'.$field1name.'</td> 
                  <td>'.$field2name.'</td> 
                  <td>'.$field3name.'</td> 
                  <td>'.$field4name.'</td> 
                  <td><b>'.$field5name.'</b></td> 
                  <td>'.$field6name.'</td> 
                  <td><a href="download.php?file='.$field7name.'">Download</a></td>
                  <td><a href="download.php?file='.$field8name.'">Download</a></td>
              </tr>';
    }
    $result->free();
} 
?>
</tbody>
<tfoot>
      <tr> 
          
          <td> Track </td> 
          <td> Car </td> 
          <td> Date </td> 
          <td> Time of Day </td> 
          <td> Best Laptime </td> 
          <td> Best Lap </td>
          <td> Motec Download </td>
          <td> LDX Download </td>
      </tr>
</tfoot>
</table>
<!-- This is where the jQuery should be placed  -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready( function () {
    $('#motecData').DataTable({ pageLength: 25 });
} );
</script>
</body>
</html>
