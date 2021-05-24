<?php

function base64url_decode ($data) {
   return base64_decode(strtr($data, '-_', '+/'),'strict');
   return base64_decode(str_replace(['-','_'], ['+','/'], $data));
}

class CRM_Proca_ActionContact{
  private static $campaigns = [];
  
  static function getCampaign($campaign) {
    if (array_key_exists ($campaign["name"],CRM_Proca_ActionContact::$campaigns)) {
      return CRM_Proca_ActionContact::$campaigns[$campaign["name"]];
    }

    $d = civicrm_api3 ('Campaign','get', ['sequential'=>1,'name' => $campaign["name"]]);
    if ($d["count"] === 0) {
      $d = civicrm_api3 ('Campaign','create', ['sequential'=>1
        , 'title' => $campaign["name"]
        , 'name' => $campaign["name"]
        , 'start_date' => date("YmdHis")
        , 'external_identifier' => "proca_" . $campaign["name"]
        , 'description' => "created by proca when first encountered ".date("Y-m-d H:i:s")
      ]);
    }
    CRM_Proca_ActionContact::$campaigns[$campaign["name"]] = $d['values'][0];
    return $d['values'][0];

  }

  
   /**
   * Callback function for entity import task
   *
   * @param CRM_Queue_TaskContext $ctx
   * @param $entity
   * @param $batch
   * @param $errFileName
   *
   * @return bool
   */

  
    public static function process($ctx,$data,$id) {
      $campaign = CRM_Proca_ActionContact::getCampaign($data["campaign"]);
      $private_key = Civi::settings()->get('proca_private_key');
      //$public_key = Civi::settings()->get('proca_public_key');

      $c = sodium_crypto_box_open ( base64url_decode($data['contact']['payload']),base64url_decode($data['contact']['nonce']),
        base64url_decode($private_key).base64url_decode($data['contact']['signKey']['public'])); 
      if (!$c) {
        echo "problem decryption";
        return false;
      }
print_r($data);
      $contact = json_decode ($c,true);
      print_r($contact); 


    return false;
  }
}
