<h3>Interface with proca</h3>


<p>Proca is the most privacy friendly campaign tool and this plugin is to interface it with your civicrm</p>
<p> you need to <a href="{crmURL p="civicrm/admin/setting/proca"}">configure it first</a><p>
<p>  
<dl>
<dt>Unprocessed</dt><dd>{$unprocessed}</dd>
</dl>
<form action="{crmURL p='civicrm/proca/fetch' q="force=1"}" method="post"> 
<button>Fetch more</button>
</form>

<h3>Latest actions processed</h3>
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


<p>Fetched {$fetched} actionContacts. You can <a href="{crmURL p="civicrm/proca/run"}">process them</a></p>

<p>Last id {$lastid}</p>
