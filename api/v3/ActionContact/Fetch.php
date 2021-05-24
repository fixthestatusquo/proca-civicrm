<?php
use CRM_Proca_ExtensionUtil as E;

/**
 * ActionContact.Fetch API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_action_contact_Fetch_spec(&$spec) {
//  sodium_crypto_secretbox_open
  $spec['limit']['api.default'] = 100;
  $spec['start']['api.default'] = 0;
  $spec['associative']['api.default'] = true;
//  $spec['campaign']['api.required'] = true;
  $spec['org']['api.required'] = true;
}

/**
 * ActionContact.Fetch API
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
function civicrm_api3_action_contact_Fetch($params) {
  include 'lib/proca.php';

  $returnValues = fetch($params);
  if (!$returnValues || array_key_exists("errors",$returnValues)) {
    throw new API_Exception ($returnValues["errors"]["message"]);
  }
  return civicrm_api3_create_success($returnValues["data"]["exportActions"], $params, 'ActionContact', 'Fetch');

}
