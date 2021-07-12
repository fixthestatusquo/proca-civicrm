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
  $QUEUE_NAME = 'proca';

    $queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => $QUEUE_NAME,
      'reset' => FALSE,
    ]);


    $runner = new CRM_Queue_Runner([
      'title' => ts('Process new actions from proca'),
      'queue' => $queue,
//      'onEnd' => ['CRM_Proca_Page_Run', 'onEnd'],
//      'onEndUrl' => CRM_Utils_System::url('civicrm/proca/run'),
    ]);
    $queueResult = $runner->runAll(); 
    if ($queueResult !== TRUE) {
      $errorMessage = CRM_Core_Error::formatTextException($queueResult['exception']);
      CRM_Core_Error::debug_log_message($errorMessage);
      throw new API_Exception($errorMessage, 'sync error');
    }
    return civicrm_api3_create_success($returnValues, $params, 'ActionContact', 'Sync');
}
