<?php

function callAPI($method, $url, $data){
   $curl = curl_init();
   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data))                                                                       
    );                                                                                                                   
   
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
 
 
    //additional options
//    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);  // Needs to be a long time if 500Mb files being uncompressed
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}
/***  Source: https://weichie.com/blog/curl-api-calls-with-php/ */
/***  Example call after setting up the values:
 * $data_array =  array(
      "customer"        => $user['User']['customer_id'],
      "payment"         => array(
            "number"         => $this->request->data['account'],
            "routing"        => $this->request->data['routing'],
            "method"         => $this->request->data['method']
      ),
);
$make_call = callAPI('POST', 'loader:1337/process_zip', json_encode($data_array));
$response = json_decode($make_call, true);
$errors   = $response['response']['errors'];
$data     = $response['response']['data'][0];
 * 
 * 
 */

?>