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
    <div class="logo-bg clearfix"><img src="https://www.assettocorsa.it/competizione/wp-content/themes/fosfostrap/_style/build/img/gtwc.png" width="140" height="80" class="logo-bg pos-left"> <span class="motec">ArisDrives Motec Server</span>
    <img src="https://www.assettocorsa.it/competizione/wp-content/themes/fosfostrap/_style/build/img/logo-acc-gtwc.png" width="250" height="80"  class="logo-bg pos-right"></div>
<p>
    This is an effort to build an extensive collection of motec data for as many car/track combinations as possible.    
</p>
<p>
    If you would like to contribute, just zip your motec folder and upload it using the button below. <br>
    The loader relies upon the .ld file not being renamed and having an ldx file with same name present as well. Otherwise they will be ignored.
    <br>
    For the time being there is a size limit of <strong><?php echo ini_get('post_max_size'); ?></strong> due to limitations in hosting and bandwidth.
</p>
<form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
Select .zip file to upload:
<input type="file" class="btn btn-success" name="fileToUpload" id="fileToUpload">
<input type="submit" class="btn btn-primary" value="Start Upload" name="submit" >
</form><!-- Display upload status -->
<div id="uploadStatus"></div>
</div>  <!-- div id=container -->
<!-- Progress bar -->
<div class="progress">
    <div class="progress-bar"></div>
</div>

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
                  <td><a class="btn btn-info" href="download.php?file='.$field7name.'">Download</a></td>
                  <td><a class="btn btn-info" href="download.php?file='.$field8name.'">Download</a></td>
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
	
$(document).ready(function() {
    $('#motecData').DataTable( {
        initComplete: function () {
            this.api().columns([0, 1]).every( function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );
                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
    } );
} );

$(document).ready(function(){
    $('.progress').hide();
    $('#uploadStatus').empty();
    // File upload via Ajax
    $("#uploadForm").on('submit', function(e){
        e.preventDefault();
        $('.progress').show();
        $('#uploadStatus').empty();
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round(((evt.loaded / evt.total) * 100));
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete+'%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: 'upload.php',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            dataType: "json",
            beforeSend: function(){
                $(".progress-bar").width('0%');
            },
            error:function(){
                $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
            },
            success: function(data){
                if(data.response == 'ok'){
                    $('#uploadForm')[0].reset();
                    $('#uploadStatus').html('<p style="color:#28A74B;">' + data.message + '</p>');
                    $('.progress').hide();
                }else if(data.response == 'err'){
                    $('#uploadStatus').html('<p style="color:#EA4335;">' + data.message + '</p>');
                }
            }
        });
    });
	
    // File type validation
    $("#fileToUpload").change(function(){
	var allowedTypes = ['application/zip', 'application/x-zip-compressed'];
        var file = this.files[0];
	var fileType = file.type;
	console.log(fileType);
        if(!allowedTypes.includes(fileType)){
            alert('Filetype not supported: ' + fileType);
            $("#fileToUpload").val('');
            return false;
        }
    });
});
</script>
</body>
</html>
