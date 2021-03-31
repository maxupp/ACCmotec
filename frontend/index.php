<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ArisDrives Motec Server</title>
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">
    <link rel="manifest" href="icon/site.webmanifest">

    <link rel="stylesheet" href="css/style.css" >
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" >

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css" >
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" >
</head>
<body>
    <div id="container" class="jumbotron">
    <div class="logo-bg clearfix"><img src="https://www.assettocorsa.it/competizione/wp-content/themes/fosfostrap/_style/build/img/gtwc.png" width="140" height="80" class="logo-bg pos-left"> <span class="motec">ArisDrives Motec Server</span><img src="icon/icon.png" width="60" height="56" alt="track report" title="Track Report">
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
<input type="file"  accept=".zip" class="btn btn-success" name="fileToUpload" id="fileToUpload">
<span>
    <input type="submit" class="btn btn-primary" value="Start Upload" name="submit" id="startBtn">
    <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true" id="startSpinner"></span>
</span>
</form>
<!-- Display upload status -->
<div id="uploadStatus"></div>
<!-- The DataTable Update -->
<span id="refreshStatus">
    The table has been refreshed...
</span>
</div>  <!-- div id=container -->
<!-- Progress bar -->
<div class="progress">
    <div class="progress-bar"></div>
</div>

<div id="donate">
    <form action="https://www.paypal.com/donate" method="post" target="_top">
        Donations go towards hosting and maintenance. And ACC DLC. <input type="hidden" name="hosted_button_id" value="RCTKH7F9FU77L" />
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
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
<script src="js/app.js"></script>
</body>
</html>
