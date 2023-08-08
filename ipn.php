<?php 

include_once 'config.php'; 

include_once 'dbConnect.php'; 
          
$raw_post_data = file_get_contents('php://input'); 
$raw_post_array = explode('&', $raw_post_data); 
$myPost = array(); 
foreach ($raw_post_array as $keyval) { 
    $keyval = explode ('=', $keyval); 
    if (count($keyval) == 2) 
        $myPost[$keyval[0]] = urldecode($keyval[1]); 
} 
 
$req = 'cmd=_notify-validate'; 
if(function_exists('get_magic_quotes_gpc')) { 
    $get_magic_quotes_exists = true; 
} 
foreach ($myPost as $key => $value) { 
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
    } else { 
        $value = urlencode($value); 
    } 
    $req .= "&$key=$value"; 
} 
  
$paypalURL = PAYPAL_URL; 
$ch = curl_init($paypalURL); 
if ($ch == FALSE) { 
    return FALSE; 
} 
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $req); 
curl_setopt($ch, CURLOPT_SSLVERSION, 6); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name')); 
$res = curl_exec($ch); 
   
$tokens = explode("\r\n\r\n", trim($res)); 
$res = trim(end($tokens)); 
if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) { 
     
    $item_number    = $_POST['item_number']; 
    $txn_id         = $_POST['txn_id']; 
    $payment_gross  = $_POST['mc_gross']; 
    $currency_code  = $_POST['mc_currency']; 
    $payment_status = $_POST['payment_status']; 
    $prevPayment = $db->query("SELECT payment_id FROM payments WHERE txn_id = '".$txn_id."'"); 
    if($prevPayment->num_rows > 0){ 
        exit(); 
    }else{ 
        $insert = $db->query("INSERT INTO payments(item_number,txn_id,payment_gross,currency_code,payment_status) VALUES('".$item_number."','".$txn_id."','".$payment_gross."','".$currency_code."','".$payment_status."')"); 
    } 
 
} 
?>