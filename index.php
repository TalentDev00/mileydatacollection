<?php

include 'admin/config.php';
include 'functions.php';
include 'admin/html_dom_completed.php';

$conn = connection($bd_config);
if(!$conn) {
    die('Error');
}

$postFields = array(
    "username" => "pat",
    "password" => "patTempPass1"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://162.243.131.72/miley/admin/index.php?a=login");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
$response = curl_exec($ch);

if ($response) {
    curl_setopt($ch, CURLOPT_URL, "http://162.243.131.72/miley/admin/leadListing/index.php?a=export");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    $response = curl_exec($ch);

    //echo $response;

    /*
    if(isset($response) && !empty($response)) {
        $st = $conn->prepare("
            INSERT INTO contacts_data(email,first_name,last_name,data,columns)
            VALUES(?,?,?,?,?)
        ");
        $st->bind_param('sssi', $email, $first_name, $last_name, $data, $columns);
        $st->execute();            

        if($conn->affected_rows) {
            //Some alert to validate data input in db
        }
    }
    */

    /*
    
    /*
    $html = new simple_html_dom();
    $html->load($response);    
    echo $html;
    //$strData = strval($html);
    //var_dump($strData);
    //echo $strData;
    
    //$find = $html->find('option');
    //print_r($find);

    //foreach($html->find('a[href^=viewLesson.php?id=]') as $link)
    //    echo $link->plaintext . "<br>";

    //foreach($html->find('a[href^=viewLesson.php?id=]') as $link)
    //    echo $link->plaintext . "<br>";
    */
}

curl_close($ch);
close_connection($conn);