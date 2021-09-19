<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CompletedNewMember',
    'entity' => 'OptionValue',
    'params' => [
      'version' => 3,
      'option_group_id' => "activity_status",
      'name' => 'completed_new_member',
      'label' => 'Completed new member',
      'is_reserved' => 1
    ],
  ],[
    'name' => 'CronProca',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Job.Proca API',
      'description' => 'Add the actions taken on proca to your CiviCRM',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'Proca',
      'parameters' => '',
    ],
  ],
];
