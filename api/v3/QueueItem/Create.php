<?php
use CRM_ProcaCivicrm_ExtensionUtil as E;

/**
 * QueueItem.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_queue_item_Create_spec(&$spec) {
//  $spec['queue']['api.required'] = 1;
  $spec['queue']['api.default'] = 'api';
  $spec['entity']['api.required'] = 1;
  $spec['action']['api.required'] = 1;
}

/**
 * QueueItem.Create API
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
function civicrm_api3_queue_item_Create($params) {
  $queue = CRM_Queue_Service::singleton()->create(array(
    'type' => 'Sql',
  'name'  => $params['queue'],
    'reset' => false, //do not flush queue upon creation
  ));

$task = $queue->createItem(new CRM_Queue_Task(
  ['CRM_Queue_Page_QueuedAPI', 'process'], // callback
  $params,
  "api.".$params['entity'].".".$params['action'] // title
));
    // ALTERNATIVE: $returnValues = []; // OK, success
    // ALTERNATIVE: $returnValues = ["Some value"]; // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
    return civicrm_api3_create_success([], $params, 'QueueItem', 'Create');
//    throw new API_Exception(/*error_message*/ 'Everyone knows that the magicword is "sesame"', /*error_code*/ 'magicword_incorrect');
}
