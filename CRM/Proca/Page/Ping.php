<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Ping extends CRM_Core_Page {

  public function run() {
    // $encrypted = Civi::service('crypto.token')->encrypt('t0ps3cr37', 'CRED');
    // Civi::settings()->set('frobulatorApiKey', $encrypted);
    $org = Civi::settings()->get('proca_org');
    $last=Civi::settings()->get('proca_lastid');
    $next=1+$last;
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
  'name'  => 'proca',
    'reset' => false, //do not flush queue upon creation
  ));
    try {

  $contacts = civicrm_api3('ActionContact', 'fetch', array('org' => $org,'start' => $next, 'limit' => 1000));
}
catch (CiviCRM_API3_Exception $e) {
  $error = $e->getMessage();
  print_r($error);
}

foreach ($contacts["values"] as $contact) {
  $last=$contact["actionId"];
$task = $queue->createItem(new CRM_Queue_Task(
  ['CRM_Proca_Page_ActionContact', 'process'], // callback
  $contact,
ts("import from proca") . $contact["actionId"] . "-" . $contact["actionType"] // title
));


  }
    $last=Civi::settings()->set('proca_lastid',$last);
    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));

    parent::run();

}
}
