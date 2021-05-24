<?php

class CRM_Proca_ActionContact{
  private static $campaigns = [];
  
  static function getCampaign($campaign) {
    $d = civicrm_api3 ('Campaign','get', {'name' => $campaign["name"]});
    if ($d["count"] === 0) {
      $d = civicrm_api3 ('Campaign','create', {'name' => $campaign["name"]});
    }
    print_r($d);
    return $d;

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

    public static function process($ctx,$contact,$id) {
      $campaign = CRM_Proca_ActionContact::getCampaign($contact["campaign"]);

    print_r($contact);

      print_r($campaign);

    return false;
  }
}
