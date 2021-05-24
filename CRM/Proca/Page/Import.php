<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Import extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Import'));
  $returnValues = array();
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
  'name'  => 'proca',
    'reset' => false, //do not flush queue upon creation
  ));

  $mode = 
    CRM_Utils_Request::retrieve('mode', 'String') === 'abort' 
    ? CRM_Queue_Runner::ERROR_ABORT
    : CRM_Queue_Runner::ERROR_CONTINUE;

  $runner = new CRM_Queue_Runner([
    'title' => ts('Demo Queue Runner'),
    'queue' => $queue,
    'errorMode' => $mode,
  ]);

  $maxRunTime = time() + 30; //stop executing next item after 30 seconds
  $continue = TRUE;

  while(time() < $maxRunTime && $continue) {
    $result = $runner->runNext(false);
    print_r("<pre>");
    print_r($result);
    if (!$result['is_continue']) {
      $continue = false; //all items in the queue are processed
    }
    $returnValues[] = $result;
  }
  // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
//  return civicrm_api3_create_success($returnValues, $params, 'Demoqueue', 'Run');

    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));

    parent::run();
  }

}
