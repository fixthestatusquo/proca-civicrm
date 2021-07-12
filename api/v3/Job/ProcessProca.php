<?php
use CRM_Inlaypetition_ExtensionUtil as E;

/**
 * Job.Processpetitioninlays API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_ProcessProca_spec(&$spec) {
  $spec['max_time']['api.default'] = 0;
  $spec['max_time']['description'] = 'Stop procesing after this many seconds. Zero means stop when all done.';
}

/**
 * Job.Processpetitioninlays API
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
function civicrm_api3_job_ProcessProca($params) {

  if (($params['max_time'] ?? 0) == 0) {
    $stopAt = NULL;
  }
  else {
    $stopAt = time() + (int) $params['max_time'];
  }

  $queue = \Civi\Inlay\Petition::getQueueService();
  $runner = new CRM_Queue_Runner([
    'title' => ts('Process petition signatures'),
    'queue' => $queue,
    //'errorMode' => CRM_Queue_Runner::ERROR_CONTINUE,
    //'onEnd' => callback
    //'onEndUrl' => CRM_Utils_System::url('civicrm/demo-queue/done'),
  ]);

  $processed = 0;
  do {
    $result = $runner->runNext(false);
    if ($result['is_error']) {
      $message = isset($result['exception']) ? $result['exception']->getMessage() : 'Unknown non-exception error';
      if ($message === 'Failed to claim next task') {
        // Queue empty, or another process busy.
        // This is not an error to us, we just need to stop.
        break;
      }
      else {
        // Some other exception.
        throw new API_Exception($message);
      }
    }

    $processed++;

    if (!$result['is_continue']) {
      break; //all items in the queue are processed, or one failed.
    }
  } while (!$stopAt || time() < $stopAt);

  return civicrm_api3_create_success(['processed' => $processed], $params, 'Job', 'Processpetitioninlays');
}
