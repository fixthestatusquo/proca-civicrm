<?php
use CRM_Proca_ExtensionUtil as E;

/**
 * ActionContact.Sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_action_contact_Sync_spec(&$spec) {
}

/**
 * ActionContact.Sync API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_action_contact_Sync($params) {
  const QUEUE_NAME = 'proca';

  function run() {
    $queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      'reset' => FALSE,
    ]);


    $runner = new CRM_Queue_Runner([
      'title' => ts('Process new actions from proca'),
      'queue' => $queue,
//      'onEnd' => ['CRM_Proca_Page_Run', 'onEnd'],
//      'onEndUrl' => CRM_Utils_System::url('civicrm/proca/run'),
    ]);
    $runner->runAll(); // does not return

    return civicrm_api3_create_success($returnValues, $params, 'ActionContact', 'Sync');
  }
  else {
    throw new API_Exception(/*error_message*/ 'check the log', /*error_code*/ 'sync error');
  }
}
