<?php
 
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'telemetry';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'track', 'dt' => 0 ),
    array( 'db' => 'car',  'dt' => 1 ),
    array( 'db' => 'date',  'dt' => 2 ),

    array( 'db' => 'time',  'dt' => 3 ),
    array(
        'db'        => 'best_time',
        'dt'        => 4,
        'formatter' => function( $d, $row ) {
            $whole = intval($d); 
            $decimal1 = $d - $whole; 
            $decimal = substr($decimal1, 1, 3);
            return gmdate("i:s", $d) . $decimal;
        }
    ),
    array( 'db' => 'best_lap',  'dt' => 5 ),
    array( 'db' => 'filename',  'dt' => 6 ),
    array( 'db' => 'filename',  'dt' => 7 )
);

// SQL server connection information
$sql_details = array(
    'user' => 'motec',
    'pass' => 'motec4thepeople',
    'db'   => 'motec_db',
    'host' => 'db'
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);