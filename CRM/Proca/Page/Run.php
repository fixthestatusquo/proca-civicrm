<?php

class CRM_Proca_Page_Run extends CRM_Core_Page {
  const QUEUE_NAME = 'proca';

  function run() {
    $queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      'reset' => FALSE,
    ]);


    $runner = new CRM_Queue_Runner([
      'title' => ts('Process new actions from proca'),
      'queue' => $queue,
      'onEnd' => ['CRM_Proca_Page_Run', 'onEnd'],
//      'onEndUrl' => CRM_Utils_System::url('civicrm/proca/run'),
    ]);
    $runner->runAllViaWeb(); // does not return
  }

  /**
   * Handle the final step of the queue
   * @param \CRM_Queue_TaskContext $ctx
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    print_r($ctx);
    //CRM_Utils_System::redirect('civicrm/demo-queue/done');
    CRM_Core_Error::debug_log_message('finished import');
    //$ctx->logy->info($message); // PEAR Log interface -- broken, PHP error
    //CRM_Core_DAO::executeQuery('select from alsdkjfasdf'); // broken, PEAR error
    //throw new Exception('whoz'); // broken, exception
  }
}
