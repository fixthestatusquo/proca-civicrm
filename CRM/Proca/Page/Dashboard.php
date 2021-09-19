<?php
use CRM_Proca_ExtensionUtil as E;

class CRM_Proca_Page_Dashboard extends CRM_Core_Page
{
    const QUEUE_NAME = "proca";
    var $last = 0;
    var $processed = 0;
    var $fetch = false;

    function __construct()
    {
        parent::__construct();
        $this->last = Civi::settings()->get("proca_lastid");
        $this->fetch = CRM_Utils_Request::retrieve("force", "Boolean")
            ? true
            : false;
    }

    public function run()
    {
        if ($this->fetch) {
            $this->fetch();
        }
        $unprocessed = civicrm_api3('QueueItem', 'getcount', ['queue_name' => "proca"]);
    $latestActivities = civicrm_api3('Activity', 'get', [
      'sequential' => 1,
      'return' => ['activity_date_time', 'subject', "source_record_id", "campaign_id.name", "activity_type_id.name"],
      'location' => ['LIKE' => "proca%"],
      'options' => ['sort' => "id desc"],
    ]);
        foreach($latestActivities["values"] as $i => $a) {
          foreach($a as $key => $value) {
          $a[str_replace('.','_',$key)] = $value;
          }
          $latestActivities["values"][$i]=$a;
        }
        $this->assign("activities", $latestActivities["values"]);
        $this->assign("unprocessed", $unprocessed);
        $this->assign("fetched", $this->processed);
        $this->assign("lastid", $this->last);

        parent::run();
    }

    function remove_emoji($string)
    {
        // Match Enclosed Alphanumeric Supplement
        $regex_alphanumeric = "/[\x{1F100}-\x{1F1FF}]/u";
        $clear_string = preg_replace($regex_alphanumeric, "[emoji]", $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = "/[\x{1F300}-\x{1F5FF}]/u";
        $clear_string = preg_replace($regex_symbols, "[emoji]", $clear_string);

        // Match Emoticons
        $regex_emoticons = "/[\x{1F600}-\x{1F64F}]/u";
        $clear_string = preg_replace(
            $regex_emoticons,
            "[emoji]",
            $clear_string
        );

        // Match Transport And Map Symbols
        $regex_transport = "/[\x{1F680}-\x{1F6FF}]/u";
        $clear_string = preg_replace(
            $regex_transport,
            "[emoji]",
            $clear_string
        );

        // Match Supplemental Symbols and Pictographs
        $regex_supplemental = "/[\x{1F900}-\x{1F9FF}]/u";
        $clear_string = preg_replace(
            $regex_supplemental,
            "[emoji]",
            $clear_string
        );

        // Match Miscellaneous Symbols
        $regex_misc = "/[\x{2600}-\x{26FF}]/u";
        $clear_string = preg_replace($regex_misc, "[emoji]", $clear_string);

        // Match Dingbats
        $regex_dingbats = "/[\x{2700}-\x{27BF}]/u";
        $clear_string = preg_replace($regex_dingbats, "[emoji]", $clear_string);

        return $clear_string;
    }

    function fetch()
    {
        // $encrypted = Civi::service('crypto.token')->encrypt('t0ps3cr37', 'CRED');
        // Civi::settings()->set('frobulatorApiKey', $encrypted);
        $org = Civi::settings()->get("proca_org");
        $limit = 0 + Civi::settings()->get("proca_limit");
        $next = 1 + $this->last;
        //$next=0;
        $queue = CRM_Queue_Service::singleton()->create([
            "type" => "Sql",
            "name" => self::QUEUE_NAME,
            "reset" => false, //do not flush queue upon creation
        ]);
        try {
            $contacts = civicrm_api3("ActionContact", "fetch", [
                "org" => $org,
                "start" => $next,
                "limit" => $limit,
            ]);
        } catch (CiviCRM_API3_Exception $e) {
            $error = $e->getMessage();
        }

        foreach ($contacts["values"] as $contact) {
            $this->last = $contact["actionId"];

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
        $this->processed = $contacts["count"];
        Civi::settings()->set("proca_lastid", $this->last);
        // Example: Assign a variable for use in a template
    }
}
