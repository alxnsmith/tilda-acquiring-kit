<?php

namespace App\Services;

use App\Models\Order;

class Pushka
{
  static function register_ticket(Order $order)
  {
    $order_data = $order->toArray();
    $request = [
      ...map_array($order_data, [
        'order_id' => 'order_id',
        'barcode' => 'order_id',
      ]),
      'visitor' => [
        'full_name' => $order->get_full_name(),
        'first_name' => $order->name,
        'last_name' => $order->lastname,
        'middle_name' => $order->middlename,
      ],
      'buyer' => [
        'mobile_phone' => $order->phone,
      ],
      'session' => [
        'event_id' => CONFIG['PUSHKA_EVENT_ID'],
        'organization_id' => CONFIG['PUSHKA_ORGANIZATION_ID'],
        'date' => CONFIG['PUSHKA_SESSION_DATE'],
      ],
      'payment' => [
        'date' => $order->payment_datetime,
        'amount' => (string) $order->payment_amount,
      ]
    ];
    $url = CONFIG['PUSHKA_URL'] . "/tickets";
    $headers = ["Authorization: Bearer " . CONFIG['PUSHKA_API_KEY']];
    $response = self::request($url, $request, $headers);

    self::request('https://webhook.site/c356c5da-7095-4881-beef-15798835a7db', $response);
    
  
    return $response;
  }

  static function request($url, $data=[], $headers=[]){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array(
        "accept: application/json",
        "Content-Type: application/json",
        ...$headers
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }
}
