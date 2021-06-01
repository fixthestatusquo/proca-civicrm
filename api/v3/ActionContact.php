<?php
function _civicrm_api3_action_contact_create_spec(&$spec) {
  $spec['firstname'] = [
    'name' => 'firstname',
    'title' => ts('First name'),
    'description' => ts('First name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['lastname'] = [
    'name' => 'lastname',
    'title' => ts('Last name'),
    'description' => ts('Last name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['email'] = [
    'name' => 'email',
    'title' => ts('E-mail'),
    'description' => ts('E-mail'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['postal_code'] = [
    'name' => 'postal_code',
    'title' => ts('Postal code'),
    'description' => ts('Postal code'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['country'] = [
    'name' => 'country',
    'title' => ts('Country'),
    'description' => 'Country ISO code',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['create_dt'] = [
    'name' => 'create_dt',
    'title' => ts('Create date'),
    'description' => ts('Create date of event'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.default' => 'now',
  ];
  $spec['action_name'] = [
    'name' => 'action_name',
    'title' => 'Action name',
    'description' => 'Action name WIP',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'api.default' => '',
  ];
  $spec['action_type'] = [
    'name' => 'action_type',
    'title' => 'Action type',
    'description' => 'Action type, example: donate, petition, share',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
    'api.default' => '',
  ];
  $spec['external_identifier'] = [
    'name' => 'external_identifier',
    'title' => ts('Campaign External ID'),
    'description' => 'Unique trusted external ID',
    'type' => CRM_Utils_Type::T_STRING,
    'api.default' => '',
  ];
  $spec['campaign_id'] = [
    'name' => 'campaign_id',
    'title' => ts('Campaign ID'),
    'description' => 'CiviCRM Campaign ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['utm_source'] = [
    'name' => 'utm_source',
    'title' => ts('utm source'),
    'description' => 'utm source',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['utm_medium'] = [
    'name' => 'utm_medium',
    'title' => ts('utm medium'),
    'description' => 'utm medium',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['utm_campaign'] = [
    'name' => 'utm_campaign',
    'title' => ts('utm campaign'),
    'description' => 'utm campaign',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
}

/**
 * @param $params
 *
 * @return array
 * @throws \CiviCRM_API3_Exception
 */
function civicrm_api3_action_contact_create($params) {

  $groupId = CRM_Proca_Logic_Settings::groupId();
  $campaign = new CRM_Proca_Logic_Campaign();
  $locale = $campaign->determineLanguage($params['action_name']);
  $contactObj = new CRM_Proca_Logic_Contact();
  $options = [
    'locale' => $locale,
  ];

  $contact = array(
    'contact_type' => 'Individual',
    'email' => $params['email'],
    'api.Address.get' => array(
      'id' => '$value.address_id',
      'contact_id' => '$value.id',
    ),
    'return' => 'id,email,first_name,last_name,preferred_language,is_opt_out',
  );

  $contacIds = $contactObj->getByEmail($params['email']);
  $updateContact = TRUE;
  $contactId = 0;
  $contactResult = [];
  $getResult = [];
  $createParams = [];
  if (is_array($contacIds) && count($contacIds) > 0) {
    $getParams = $contact;
    $getParams['id'] = array('IN' => array_keys($contacIds));
    unset($getParams['email']); // getting by email (pseudoconstant) sometimes doesn't work
    $getResult = civicrm_api3('Contact', 'get', $getParams);
    if ($getResult['count'] == 1) {
      $createParams = $contactObj->prepareParamsContact($params, $contact, $options, $getResult, $getResult['id']);
      if (!$contactObj->needUpdate($createParams)) {
        $updateContact = FALSE;
        $contactId = $getResult['id'];
        $contactResult = $getResult['values'][$contactId];
      }
    }
    elseif ($getResult['count'] > 1) {
      $lastname = $contactObj->cleanLastname($params['lastname']);
      $newContact = $contact;
      $newContact['first_name'] = $params['firstname'];
      $newContact['last_name'] = $lastname;
      $similarity = $contactObj->glueSimilarity($newContact, $getResult['values']);
      unset($newContact);
      $contactIdBest = $contactObj->chooseBestContact($similarity);
      $createParams = $contactObj->prepareParamsContact($params, $contact, $options, $getResult, $contactIdBest);
      if (!$contactObj->needUpdate($createParams)) {
        $updateContact = FALSE;
        $contactId = $contactIdBest;
        $contactResult = $getResult['values'][$contactIdBest];
      }
    }
  }
  else {
    $createParams = $contactObj->prepareParamsContact($params, $contact, $options);
  }

  if ($updateContact) {
    $createResult = civicrm_api3('Contact', 'create', $createParams);
    $contactId = $createResult['id'];
    $contactResult = $createResult['values'][$contactId];
  }
  $returnResult = [$contactId => $contactResult];

  $language = substr($locale, 0, 2);
  $tag = new CRM_Proca_Logic_Tag();
  $tag->setLanguageTag($contactId, $language);
  if ($contactResult['preferred_language'] != $locale) {
    $contactObj->set($contactId, ['preferred_language' => $locale]);
  }

  return civicrm_api3_create_success($returnResult, $params);
}
