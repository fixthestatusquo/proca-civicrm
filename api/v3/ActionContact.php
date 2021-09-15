<?php
function _civicrm_api3_action_contact_create_spec(&$spec) {
  $spec['first_name'] = [
    'name' => 'first_name',
    'title' => ts('First name'),
    'description' => ts('First name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['last_name'] = [
    'name' => 'last_name',
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
  $spec['phone'] = [
    'name' => 'phone',
    'title' => ts('Phone'),
    'description' => 'Phone',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0,
    'api.default' => '',
  ];
  $spec['created_date'] = [
    'name' => 'created_date',
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
    'title' => ts('External identifier'),
    'description' => 'Unique contactRef',
    'type' => CRM_Utils_Type::T_STRING,
    'api.default' => '',
  ];
  $spec['campaign'] = [
    'name' => 'campaign_name',
    'title' => ts('Campaign External ID'),
    'description' => 'Unique campaign name',
    'type' => CRM_Utils_Type::T_STRING,
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
//  $campaign = new CRM_Proca_Logic_Campaign();
//  $locale = $campaign->determineLanguage($params['action_name']);
  $contactObj = new CRM_Proca_Logic_Contact();

  $contact = array(
    'contact_type' => 'Individual',
    'email' => $params['email'],
    'api.Address.get' => array(
      'id' => '$value.address_id',
      'contact_id' => '$value.id',
    ),
    'return' => 'id,email,first_name,last_name,preferred_language,is_opt_out',
  );

/*     $activityTypeId = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', $activityType);
    $activityStatusId = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'status_id', $activityStatus);
    $params = array(
      'sequential' => 1,
      'source_contact_id' => $contactId,
      'source_record_id' => $param->external_id,
      'campaign_id' => $this->campaignId,
      'activity_type_id' => $activityTypeId,
      'activity_date_time' => $param->create_dt,
      'subject' => $param->action_name,
      'location' => $param->action_technical_type,
      'status_id' => $activityStatusId,
      'details' => $this->determineDetails($param),
    );
 */

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
      $createParams = $contactObj->prepareParamsContact($params, $contact, [], $getResult, $getResult['id']);
      if (!$contactObj->needUpdate($createParams)) {
        $updateContact = FALSE;
        $contactId = $getResult['id'];
        $contactResult = $getResult['values'][$contactId];
      }
    }
    elseif ($getResult['count'] > 1) {
      $last_name = $params['last_name'];
      $newContact = $contact;
      $newContact['first_name'] = $params['first_name'];
      $newContact['last_name'] = $last_name;
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
    $createParams = $contactObj->prepareParamsContact($params, $contact, []);
    $createParams["created_date"] =$params['created_date'] ;

  }


  if ($updateContact) {
    $createParams["sequential"] =true ;
    $createParams["is_transactional"] = false;
    try {
      $createResult = civicrm_api3('Contact', 'create', $createParams);
    } catch (Exception $e) {
      if ($e->getMessage() != "DB Error: already exists")
        return civicrm_api3_create_error($e->getMessage(), $params);
     
     
      $trashed = \Civi\Api4\Contact::get()
        ->addSelect('id')
        ->addWhere('is_deleted', '=', TRUE)
        ->setLimit(1)
        ->execute();
        if ($trashed->count()!=1) 
          return civicrm_api3_create_error($e->getMessage(), $params);

      $createParams["id"]=$trashed->first()["id"];
      $createParams["is_deleted"]=0;
      $result = civicrm_api3('Contact', 'create', [
        'id' => 10,
        'is_deleted' => 0,
        'is_transactional' => false,
      ]);
      $createResult = civicrm_api3('Contact', 'create', $createParams);
    }
    $contactResult = $createResult['values'][0];
  }

  $activity = [
    "sequential" => 1,
    "is_transactional" => false,
    "source_contact_id" => $contactResult["id"],
    "activity_type_id" => $params["action_type"],
    "activity_date_time" => $params["created_date"],
    "subject" => $params["action_name"],
    "details" => '',
    "campaign_id" => $params["campaign"],
    "location" => "proca page#" . $params ["page_id"]
  ];
  if (in_array("comment",$params))
    $activity["details"] = $params["comment"];


  try {
    $d=civicrm_api3 ("Activity","create",$activity);
    print_r($d);exit(1);
  } catch (Exception $e) {
    	

    $result = civicrm_api3('OptionValue', 'create', [
      'option_group_id' => "activity_type",
      'name' => $params["action_type"],
      'description' => "proca ".$params["action_type"],
      'label' => "proca ".$params["action_type"]
    ]);
    if ($result["count"] != 1) {
      return civicrm_api3_create_error($e>-getMessage(), $params);
    }
    $d=civicrm_api3 ("Activity","create",$activity);

  }

  $contactResult["api.activity.create"]=$d;
  return civicrm_api3_create_success([$contactResult['id'] => $contactResult], $params);
}
