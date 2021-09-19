{literal}
<script>
  jQuery( $ => {
    $("#fetch").click( e => {
      e.preventDefault();
      CRM.api3('Job', 'proca', {"limit": 10,"fetch": 1,"process": 0},
        {start:"fetching...",success:"fetched"}
      ).then(function(result) {
        CRM.status("Fetched " + result.values.fetched);
        console.log("fetched",result);
      }, function(error) {
        console.log(error);
      });
    });
    $("#process").click( e => {
      e.preventDefault();
      CRM.api3('Job', 'proca', {"limit": 2,"fetch": 0,"process": 1},
        {start:"process...",success:"processed"}
      ).then(function(result) {
        CRM.status("Processed " + result.values.processed);
        console.log("processed",result);
      }, function(error) {
        console.log(error);
      });
    });
  });
</script>
{/literal}


<h3>Interface with proca</h3>


<p>Proca is the most privacy friendly campaign tool and this plugin is to interface it with your civicrm</p>
<div class="crm-accordion-wrapper collapsed">
  <div class="crm-accordion-header">
Configuration
  </div>
  <div class="crm-accordion-body">
     <div class="crm-block crm-form-block crm-form-title-here-form-block">
To fetch the data from proca, you need at least an account (login+password), and if you encrypted the data, the private and public key
<p> 
<div>
{crmButton p="civicrm/admin/setting/proca" class="button-name" title="configuration of the interface with proca" icon="fa-wrench"}Configure{/crmButton}
</div>

<p>&nbsp;</p>
     </div>
   </div>
</div>
<div class="crm-accordion-wrapper collapsed">
  <div class="crm-accordion-header">
    Fetch and process
  </div>
  <div class="crm-accordion-body">
     <div class="crm-block crm-form-block crm-form-title-here-form-block">
<p>You must enable a scheduled job to import your new supporters to your CiviCRM. Check you the <a href="https://docs.civicrm.org/sysadmin/en/latest/setup/jobs/">documentation</a></p>
<p></p>
<p>For testing purpose, you can:
<ul>
<li><button class="crm-button btn btn-lg" id="fetch">Fetch more</button><br></li>
<li><button class="crm-button btn btn-lg" id="process">Process</button><br></li>
<li><a href="{crmURL p="civicrm/proca/run"}">process all</a><br></li>
</ul>

<p>Last id {$lastid}</p>
     </div>
   </div>
</div>


<div class="crm-accordion-wrapper">
  <div class="crm-accordion-header">
    Latest actions processed <i>({$unprocessed} unprocessed)</i>
  </div>
  <div class="crm-accordion-body">
     <div class="crm-block crm-form-block crm-form-title-here-form-block">
<table>
<thead>
<th>date</th>
<th>action</th>
<th>contact</th>
<th>Campaign</th>
<th>Page</th>
</thead>
{foreach from=$activities item=action}
<tbody>
<tr>
<th>{$action.activity_date_time}</th>
<th>{$action.activity_type_id_name}</th>
<th><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$action.source_contact_id`"}">{$action.source_contact_name}</a></th>
<th>{$action.campaign_id_name}</th>
<th>{$action.subject}</th>
</tr>
{/foreach}
</table>



     </div>
   </div>
</div>

