<?php


function fetch ($p) {
  
$query = 'query actions ($org:String!,$limit:Int,$start:Int,$campaign:Int) {
  exportActions(start:$start,campaignId:$campaign,limit:$limit,orgName:$org,onlyOptIn:true) {
    actionId, actionType, createdAt
    actionPage {
      id, locale,name
    },campaign {
      externalId,name
    },contact {
      contactRef,nonce,payload,publicKey {public},signKey{public}
    },
    fields {key,value},
    tracking {medium,campaign,source,content}
    privacy {optIn}
  }
}';
  $params = array (
    "operationName" => "actions",
    "query" => $query,
    "variables" => array (
      "org" => $p['org'],
      "limit" => $p['limit'] ?:10,
      "start" => $p['start'] ?:0
    )
);

  if (array_key_exists('campaign',$p))
    $params["variables"]["campaign"]=$p['campaign'];
  $username = Civi::settings()->get('proca_login');
$password = Civi::settings()->get('proca_password');
$ch = curl_init();
//http://php.net/manual/en/function.curl-setopt.php
curl_setopt($ch, CURLOPT_URL, "https://api.proca.app/api");
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER,
    array(
      'Content-Type:application/json'
//      ,        'Content-Length: ' . strlen($params)
    )
);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_TIMEOUT, 5);
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//curl_setopt($ch, CURLOPT_ENCODING, "");

//for debugging?
//curl_setopt($ch, CURLOPT_VERBOSE, true);

$data = curl_exec($ch);
curl_close($ch);
$obj = json_decode($data,$p['associative'] ?: true);
  return $obj;
}

