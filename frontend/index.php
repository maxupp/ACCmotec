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
        $field5name = $row["best_time"]; 
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
                  <td><a href="'.$field7name.'">Download</a></td> 
                  <td><a href="'.$field8name.'">Download</a></td> 
              </tr>';
    }
    $result->free();
} 
?>
</body>
</html>