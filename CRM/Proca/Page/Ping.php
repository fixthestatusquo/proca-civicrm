<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Ping extends CRM_Core_Page {

  public function run() {
    // $encrypted = Civi::service('crypto.token')->encrypt('t0ps3cr37', 'CRED');
    // Civi::settings()->set('frobulatorApiKey', $encrypted);
    $org = Civi::settings()->get('proca_org');
    $last=Civi::settings()->get('proca_lastid');
    //$next=1+$last;

    $next=0;
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
  'name'  => 'proca',
    'reset' => false, //do not flush queue upon creation
  ));
    try {

  $contacts = civicrm_api3('ActionContact', 'fetch', array('org' => $org,'start' => $next, 'limit' => 3));
}
catch (CiviCRM_API3_Exception $e) {
  $error = $e->getMessage();
}

foreach ($contacts["values"] as $contact) {
  $last=$contact["actionId"];


$task = $queue->createItem(new CRM_Queue_Task(
  ['CRM_Proca_ActionContact', 'process'], // callback
  [$contact,$contact["actionId"]], // needs to be an array, if object gets flattened
ts("import from proca ") . $contact["actionId"] . "-" . $contact["actionType"] // title
));
 

  }
    Civi::settings()->set('proca_lastid',$last);
    // Example: Assign a variable for use in a template
    $this->assign('fetched', $contacts["count"]);
    $this->assign('lastid', $last);

    parent::run();

}
}
