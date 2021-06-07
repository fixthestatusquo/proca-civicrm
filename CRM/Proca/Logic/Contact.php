<?php

class CRM_Proca_Logic_Contact {

  const API_GROUPCONTACT_GET = 'api.GroupContact.get';
  const API_GROUPCONTACT_CREATE = 'api.GroupContact.create';

  /**
   * Get contact id (or ids) by using Email API
   *
   * @param $email
   *
   * @return array
   */
  public function getByEmail($email) {
    $query = "SELECT e.contact_id
              FROM civicrm_email e
                JOIN civicrm_contact c ON e.contact_id = c.id
              WHERE email = %1 AND c.is_deleted = 0
              ORDER BY e.contact_id ";
    $params = [
      1 => [$email, 'String'],
    ];
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    $ids = [];
    while ($dao->fetch()) {
      $ids[$dao->contact_id] = $dao->contact_id;
    }
    return $ids;
  }

  /**
   * Set contact params
   *
   * @param int $contactId
   * @param array $contactParams
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function set($contactId, $contactParams) {
    $params = array(
      'sequential' => 1,
      'id' => $contactId,
    );
    $params = $params + $contactParams;
    if (count($params) > 2) {
      civicrm_api3('Contact', 'create', $params);
    }
  }

  public function prepareLanguage($locale) {
    if ($locale == "en") {
      return "en_US"; //because 
    }
    if (length($locale) == 2)  {
      return $locale . "_" . strtoupper($locale);
    }
  }

  /**
   * Preparing params for API Contact.create based on retrieved result.
   *
   * @param array $params
   * @param array $contact
   * @param array $options
   * @param array $result
   * @param int $basedOnContactId
   *
   * @return mixed
   */
  public function prepareParamsContact($params, $contact, $options, $result = array(), $basedOnContactId = 0) {

    unset($contact['return']);
    unset($contact['api.Address.get']);
    unset($contact['api.GroupContact.get']);

    $existingContact = array();
    if ($basedOnContactId > 0) {
      foreach ($result['values'] as $id => $res) {
        if ($res['id'] == $basedOnContactId) {
          $existingContact = $res;
          break;
        }
      }
    }

    $address = new CRM_Proca_Logic_Address();
    $params['country_id'] = CRM_Proca_Logic_Country::getId($params['country']);
    if (is_array($existingContact) && count($existingContact) > 0) {
      $contact['id'] = $existingContact['id'];
      if ($existingContact['first_name'] == '' && $params['first_name']) {
        $contact['first_name'] = $params['first_name'];
      }
      if ($existingContact['last_name'] == '' && $params['last_name']) {
        $contact['last_name'] = $params['last_name'];
      }
      $contact = $address->prepareParamsAddress($contact, $existingContact, $params);
    }
    else {
//      $genderId = $this->getGenderId($params['last_name']);
//      $genderShortcut = $this->getGenderShortcut($params['last_name']);
      $contact['first_name'] = $params['first_name'];
      $contact['last_name'] = $params['last_name'];
      $contact['external_identifier'] = $params["identifier"];
      $contact['source'] = $this->determineSource($params);
      $contact = $address->prepareParamsAddressDefault($contact, $params);
    }
    $contact['preferred_language'] = $this->prepareLanguage($params["locale"]);
    $contact = $address->removeNullAddress($contact);
    return $contact;
  }


  /**
   * Calculate and glue similarity between new contact and all retrieved from database
   *
   * @param array $newContact
   * @param array $contacts Array from API.Contact.get, key 'values'
   *
   * @return array
   */
  public function glueSimilarity($newContact, $contacts) {
    $similarity = array();
    foreach ($contacts as $k => $c) {
      $similarity[$c['id']] = $this->calculateSimilarity($newContact, $c);
    }
    return $similarity;
  }

  /**
   * Calculate similarity between two contacts based on defined keys
   *
   * @param $contact1
   * @param $contact2
   *
   * @return int
   */
  private function calculateSimilarity($contact1, $contact2) {
    $keys = array(
      'first_name',
      'last_name',
      'email',
    );
    $points = 0;
    foreach ($keys as $key) {
      if ($contact1[$key] == $contact2[$key]) {
        $points++;
      }
    }
    return $points;
  }

  /**
   * Choose the best contact based on similarity. If similarity is the same, choose the oldest one.
   *
   * @param $similarity
   *
   * @return mixed
   */
  public function chooseBestContact($similarity) {
    $max = max($similarity);
    $contactIds = array();
    foreach ($similarity as $k => $v) {
      if ($max == $v) {
        $contactIds[$k] = $k;
      }
    }
    return min(array_keys($contactIds));
  }

  /**
   * Check if updating of contact if it's necessary.
   *
   * @param array $params Array of params for API contact
   *
   * @return bool
   */
  public function needUpdate($params) {
    unset($params['sequential']);
    unset($params['contact_type']);
    unset($params['email']);
    unset($params['id']);
    return (bool) count($params);
  }


  /**
   * Determine source for new contact.
   *
   * @param array $params
   *
   * @return string
   */
  private function determineSource($params) {
    $prefix = 'proca ';
    return $prefix . $params['action_type'] . ' ' . $params['campaign'] . ' #'.$params['page_id'];
  }


}
