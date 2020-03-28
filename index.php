<?php

// ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);

function create_db($dbName, $url, $port) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_PORT => $port,
        CURLOPT_URL => $url."/query",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "q=CREATE%20DATABASE%20".$dbName,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

function insert_data($dbName, $url, $port, $data) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_PORT => $port,
        CURLOPT_URL => $url.":".$port."/write?db=".$dbName,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "Postman-Token: 53e2eb77-1fa1-4ba1-8548-99e7e1e58d83",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

function get_last($dbName, $url, $port) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_PORT => $port,
        CURLOPT_URL => $url.":".$port."/query?epoch=ns&db=".$dbName,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "q=SELECT LAST(as_of_date) FROM coins",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
            "Postman-Token: 2149ade3-5484-41e3-9cef-1a686bf51e18",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        // return "cURL Error #:" . $err;
        return false;
    } else {
        $response = json_decode($response);
        if($response->results[0]->series[0]->values[0][0]) {
            return $response->results[0]->series[0]->values[0][0];
        }
        return false;
    }
}

function getRandCoin() {
    $coins = [
        'BTC',
        'ABC',
        'XCV',
        'TYU',
        'IUO',
        'POL',
        'QWE',
        'CVB',
        'VBN',
        'MBA',
        'CVM',
    ];

    return $coins[array_rand($coins)];

}

function getRandDecimal($start=5,$end=60) {
    return rand($start, $end) / 10;
}

function getRandInt($start=34,$end=99) {
    return rand($start, $end);
}

function prepareData($time) {
    $coin = getRandCoin();

    $data = ' coins,subject='.$coin;
    $data .= ' as_of_date="'.$time.'",ticker="'.$coin.'",price='.getRandDecimal().',last_update_ts="'.$time.'",last_volume='.getRandInt().'i,last_volume_to='.getRandInt().'i,last_trade_id=1,volume_day="'.$time.'",volume_day_to="'.$time.'",volume_24hr='.getRandInt().'i,volume_24hr_to='.getRandInt().'i,open_price='.getRandDecimal().',high_price='.getRandDecimal().',low_price='.getRandDecimal().',close_price='.getRandDecimal().',open_24hr='.getRandDecimal().',high_24hr='.getRandInt().'i,low_24hr='.getRandInt().'i,last_market='.getRandDecimal().',change_24hr='.getRandDecimal().',change_pct_24hr='.getRandInt().'i,change_day="'.$time.'",change_pct_day="'.$time.'",supply="abc",mkt_cap='.getRandInt().'i,total_volume_24h='.getRandInt().'i,total_volume_24h_to='.getRandInt().'i,mkt_cap_ord="asc",price_change_1hr='.getRandDecimal().',price_change_12hr='.getRandDecimal().',price_change_24hr='.getRandDecimal().',price_change_1hr_percent='.getRandDecimal().',price_change_12hr_percent='.getRandDecimal().',price_change_24hr_percent='.getRandDecimal().',return1hr='.getRandInt().'i,return12hr='.getRandInt().'i,return24hr='.getRandInt().'i,return7day='.getRandInt().'i,return30day='.getRandInt().'i,avg_volume_30day='.getRandInt().'i,nvt_ratio='.getRandInt().'i,derived_market_cap='.getRandInt().'i,center_date="'.$time.'",center_time="'.$time.'",center_tz="UTC" '.$time;

    return $data;
}

$url = "http://localhost";
$port = "8086";
$db_name = "tie_db_billion_records";
$limit = 100000;

$last_timestamp = get_last($db_name,$url,$port);

if(!$last_timestamp) {
    $last_timestamp = strtotime('now');
}

for ($i=0; $i < $limit; $i++) {
    
    $last_timestamp = $last_timestamp + 60000000000;
    $data = prepareData($last_timestamp);
    insert_data($db_name,$url,$port,$data);
}

die("DONE! - ".date("Y-m-d H:i:s"));


