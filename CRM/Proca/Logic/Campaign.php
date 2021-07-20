<?php

class CRM_Proca_Logic_Campaign {

  /**
   * Get campaign by external identifier or CiviCRM Id.
   *
   * @param int $id External identifier (default) or local civicrm_campaign.id
   * @param bool $useLocalId Use local id or external id (default)
   * @param bool $countActivities
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public function get($id, $useLocalId = FALSE, $countActivities = FALSE) {
    if ($id) {
      if ($useLocalId) {
        $field = 'id';
      }
      else {
        $field = 'external_identifier';
      }
      $params = array(
        'sequential' => 1,
        $field => $id,
      );
      if ($countActivities) {
        $params['api.Activity.getcount'] = array(
          'campaign_id' => '$value.id',
        );
      }
      $result = civicrm_api3('Campaign', 'get', $params);
      if ($result['count'] == 1) {
        return $result['values'][0];
      }
    }
    return array();
  }

  /**
   * Setting up new campaign in CiviCRM.
   *
   * @param $params
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public function set($params) {
    $params = array(
      'sequential' => 1,
      'name' => $params['action_name'],
      'title' => $params['action_name'],
      'description' => $params['action_name'],
      'external_identifier' => $params['external_identifier'],
      'start_date' => date('Y-m-d H:i:s')
    );
    $result = civicrm_api3('Campaign', 'create', $params);
    return $result['values'][0];
  }

  /**
   * Determine whether $campaign array has a valid structure.
   *
   * @param array $campaign
   *
   * @return bool
   */
  public function isValidCampaign($campaign) {
    if (
      is_array($campaign) &&
      array_key_exists('id', $campaign) &&
      $campaign['id'] > 0
    ) {
      return TRUE;
    }
    return FALSE;
  }
  
  public function getOrCreateCampaign($ActionContact) {
    $key = "WeAct:ActionPage:{$action->externalSystem}:{$action->actionPageId}";
    $entry = Civi::cache()->get($key);
    if (!$entry) {
      $external_id = $this->externalIdentifier($action->externalSystem, $action->actionPageId);
      $get_params = ['sequential' => 1, 'external_identifier' => $external_id];
      $get_result = civicrm_api3('Campaign', 'get', $get_params);
      if ($get_result['count'] == 1) {
        $entry = $get_result['values'][0];
      }
      else {
        $create_result = civicrm_api3('Campaign', 'create', [
          'sequential' => 1,
          'name' => $action->actionPageName,
          'title' => $action->actionPageName,
          'description' => $action->actionPageName,
          'external_identifier' => $external_id,
          'campaign_type_id' => $this->campaignType($action->actionType),
          'start_date' => date('Y-m-d H:i:s'),
          $this->settings->customFields['campaign_language'] => $action->language,
        ]);
        $entry = $create_result['values'][0];
      }
      Civi::cache()->set($key, $entry);
    }
    return $entry;
  }
}
