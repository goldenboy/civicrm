<form action="index.php?q=crm/contact/list" method="post" name="list" id="list">
{include file="CRM/pager.tpl" location="top"}
<table>
<tr>
{foreach from=$columnHeaders item=header}
<th>{$header.label}</th>
{/foreach}
</tr>
{foreach from=$rows item=row}
<tr>
<td>{$row.contact_id}</td><td>{$row.first_name}</td><td>{$row.last_name}</td><td>{$row.email}</td>
</tr>
{/foreach}
</table>
{include file="CRM/pager.tpl" location="bottom"}
</form>
