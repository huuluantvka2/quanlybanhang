<?php

function getCurrencyExchangeRate($from, $to, $amount)
{    
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.apilayer.com/exchangerates_data/convert?to=". $to ."&from=". $from ."&amount=" . $amount,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
            "apikey: yWPIz7h9bshtwV65VQ73PpnTBTPanVGa"
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
    ));
    
    $response = curl_exec($curl);
    
    $result_json = (array)json_decode($response);
    
    curl_close($curl);
    return $result_json['result'];
}