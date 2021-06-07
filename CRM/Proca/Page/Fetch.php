<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Fetch extends CRM_Core_Page {
  const QUEUE_NAME = 'proca';
  var int $last=0;
  var int $processed=0;
  var bool $fetch = false;

  function __construct() {
    parent::__construct();
    $this->last=Civi::settings()->get('proca_lastid');
    $this->fetch = CRM_Utils_Request::retrieve('force', 'Boolean') ? true : false;
    }

  public function run () {

    if ($this->fetch) {
      $this->fetch();
    }
     $this->assign('fetched', $this->processed);
     $this->assign('lastid', $this->last);

     parent::run();

  }

  function fetch () {
    // $encrypted = Civi::service('crypto.token')->encrypt('t0ps3cr37', 'CRED');
    // Civi::settings()->set('frobulatorApiKey', $encrypted);
    $org = Civi::settings()->get('proca_org');
    $limit=0 + Civi::settings()->get('proca_limit');
    $next=1+$this->last;
    //$next=0;
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
    'name' => self::QUEUE_NAME,
    'reset' => false, //do not flush queue upon creation
  ));
    try {

  $contacts = civicrm_api3('ActionContact', 'fetch', array('org' => $org,'start' => $next, 'limit' => $limit));
}
catch (CiviCRM_API3_Exception $e) {
  $error = $e->getMessage();
}

foreach ($contacts["values"] as $contact) {
  $this->last=$contact["actionId"];


$task = $queue->createItem(new CRM_Queue_Task(
  ['CRM_Proca_ActionContact', 'process'], // callback
  [$contact,$contact["actionId"]], // needs to be an array, if object gets flattened
ts("import from proca ") . $contact["actionId"] . "-" . $contact["actionType"] // title
));
 

}
  $this->processed=$contacts["count"];
    Civi::settings()->set('proca_lastid',$this->last);
    // Example: Assign a variable for use in a template
}
}
