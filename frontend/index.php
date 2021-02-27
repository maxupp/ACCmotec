<html>
<body>
<h1>Supercool Motec Server</h1>
<?php 
$username = "motec"; 
$password = "motec4thepeople"; 
$database = "motec_db"; 
$mysqli = new mysqli("db", $username, $password, $database); 
$query = "SELECT * FROM telemetry";

echo '<table border="0" cellspacing="2" cellpadding="2"> 
      <tr> 
          <td> <font face="Arial">Filename</font> </td> 
          <td> <font face="Arial">Track</font> </td> 
          <td> <font face="Arial">Car</font> </td> 
          <td> <font face="Arial">Date</font> </td> 
          <td> <font face="Arial">Time</font> </td> 
          <td> <font face="Arial">Best Laptime</font> </td> 
          <td> <font face="Arial">Best Lap</font> </td> 
      </tr>';

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $field1name = $row["filename"];
        $field2name = $row["track"];
        $field3name = $row["car"];
        $field4name = $row["date"];
        $field5name = $row["time"]; 
        $field6name = $row["best_time"]; 
        $field7name = $row["best_lap"]; 

        echo '<tr> 
                  <td>'.$field1name.'</td> 
                  <td>'.$field2name.'</td> 
                  <td>'.$field3name.'</td> 
                  <td>'.$field4name.'</td> 
                  <td>'.$field5name.'</td> 
                  <td>'.$field6name.'</td> 
                  <td>'.$field7name.'</td> 
              </tr>';
    }
    $result->free();
} 
?>
</body>
</html>