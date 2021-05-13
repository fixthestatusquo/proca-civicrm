<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Ping extends CRM_Core_Page {

  public function run() {
    // $encrypted = Civi::service('crypto.token')->encrypt('t0ps3cr37', 'CRED');
    // Civi::settings()->set('frobulatorApiKey', $encrypted);
    $org = Civi::settings()->get('proca_org');
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
  'name'  => 'proca',
    'reset' => false, //do not flush queue upon creation
  ));
try {
  $contacts = civicrm_api3('ActionContact', 'fetch', array('org' => $org));
}
catch (CiviCRM_API3_Exception $e) {
  $error = $e->getMessage();
  print_r($error);
}
  foreach ($contacts["values"] as $contact) {
echo "here";
$task = $queue->createItem(new CRM_Queue_Task(
  ['CRM_Proca_ActionContact', 'process'], // callback
  $contact,
  $contact["actionId"] . "-" . $contact["actionType"] // title
));
    
  }
    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));

    parent::run();

}
}
