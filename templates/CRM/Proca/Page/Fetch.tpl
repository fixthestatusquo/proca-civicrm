<h3>Interface with proca</h3>

<p>Proca is the most privacy friendly campaign tool, and this plugin is to interface it with your civicrm</p>
<p> you need to <a href="{crmURL p="civicrm/admin/setting/proca"}">configure it first</a><p>
<p>  

<form action="{crmURL p='civicrm/proca/fetch' q="force=1"}" method="post"> 
<button>Fetch more</button>
</form>

{* Example: Display a variable directly *}
<p>Fetched {$fetched} actionContacts. You can <a href="{crmURL p="civicrm/proca/run"}">process them</a></p>

<p>Last id {$lastid}</p>
