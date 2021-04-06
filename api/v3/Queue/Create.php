<?php
use CRM_ProcaCivicrm_ExtensionUtil as E;

/**
 * Queue.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_queue_Create_spec(&$spec) {
  $spec['name']['api.required'] = 1;
  $spec['reset']['api.default'] = TRUE;
}

/**
 * Queue.Create API
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
function civicrm_api3_queue_Create($params) {
  $queue = CRM_Queue_Service::singleton()->create([
  'type'  => 'Sql',
  'name'  => $params['name'],
  'reset' => $params['reset'],
  ]);
    return civicrm_api3_create_success(array(), $params, 'Queue', 'Create');
//    throw new API_Exception(/*error_message*/ 'Everyone knows that the magicword is "sesame"', /*error_code*/ 'magicword_incorrect');
}
