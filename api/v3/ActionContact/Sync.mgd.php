<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:ActionContact.Sync',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'import external activities into civicrm',
      'description' => 'Made for actions taken on proca',
      'run_frequency' => 'Hourly',
      'api_entity' => 'ActionContact',
      'api_action' => 'Sync',
      'parameters' => '',
    ],
  ],
];
