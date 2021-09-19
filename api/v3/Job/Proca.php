<?php
/**
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_proca_spec(&$spec)
{
  $spec["limit"]["description"] =
    "How many records to fetch or process?";
  $spec["max_time"]["api.default"] = 0;
  $spec["max_time"]["description"] =
    "Stop procesing after this many seconds. Zero means stop when all done.";
  $spec["fetch"]["api.default"] = true;
  $spec["fetch"]["api.description"] =
    "fetch a batch of contacts and queue them";
  $spec["process"]["api.default"] = true;
  $spec["process"]["api.description"] =
    "process the queue and create the contacts";
}

/**
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
function civicrm_api3_job_proca($params)
{
  $QUEUE_NAME = "proca";
  $fetched = 0;
  $processed = 0;

  $fetchBatch = function ($p) use (&$fetched) {
    $last = $p["last"];
    $queue = $p["queue"];
    try {
      $contacts = civicrm_api3("ActionContact", "fetch", [
        "org" => $p["org"],
        "start" => 1 + $last,
        "limit" => $p["limit"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
    }

    foreach ($contacts["values"] as $contact) {
      $last = $contact["actionId"];

      try {
        $task = $queue->createItem(
          new CRM_Queue_Task(
            ["CRM_Proca_ActionContact", "process"], // callback
            [$contact, $contact["actionId"]], // needs to be an array, if object gets flattened
            ts("import from proca ") .
              $contact["actionId"] .
              "-" .
              $contact["actionType"] // title
          )
        );
      } catch (Exception $e) {
        echo "aaaaaa" . $e->getMessage();
      }
    }
    $fetched = $contacts["count"];
    Civi::settings()->set("proca_lastid", $last);
  };

  if (($params["max_time"] ?? 0) == 0) {
    $stopAt = null;
  } else {
    $stopAt = time() + (int) $params["max_time"];
  }

  $queue = CRM_Queue_Service::singleton()->create([
    "type" => "Sql",
    "name" => $QUEUE_NAME,
    "reset" => false,
  ]);

  if ($params["fetch"]) {
    //    $org = Civi::settings()->get("proca_org");
    //    $limit = 0 + Civi::settings()->get("proca_limit");
    $fetchBatch([
      "queue" => $queue,
      "org" => Civi::settings()->get("proca_org"),
      "limit" => 0 + ($params["limit"] ? $params["limit"] : Civi::settings()->get("proca_limit")),
      "last" => Civi::settings()->get("proca_lastid"),
    ]);
    //$next=0;
  }

  if ($params["process"]) {
    $runner = new CRM_Queue_Runner([
      "title" => ts("Process petition signatures"),
      "queue" => $queue,
      //'errorMode' => CRM_Queue_Runner::ERROR_CONTINUE,
      //'onEnd' => callback
      //'onEndUrl' => CRM_Utils_System::url('civicrm/demo-queue/done'),
    ]);

    do {
      $result = $runner->runNext(false);
      if ($result["is_error"]) {
        $message = isset($result["exception"])
          ? $result["exception"]->getMessage()
          : "Unknown non-exception error";
        if ($message === "Failed to claim next task") {
          // Queue empty, or another process busy.
          // This is not an error to us, we just need to stop.
          break;
        } else {
          // Some other exception.
          throw new API_Exception($message);
        }
      }

      $processed++;
      if ($params["limit"] && ($processed >= $params["limit"]))
        $stopAt = time() -1;
      
      if (!$result["is_continue"]) {
        break; //all items in the queue are processed, or one failed.
      }
    } while (!$stopAt || time() < $stopAt);
  }

  return civicrm_api3_create_success(
    ["processed" => $processed, "fetched" => $fetched],
    $params,
    "Job",
    "process_proca"
  );
}
