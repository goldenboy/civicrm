<div id="help">
    {ts}Activity types are various activities that can be added/modified by the user. CiviCRM provides some default activities{/ts}
</div>

{if $action eq 1 or $action eq 2}
   {include file="CRM/Admin/Form/ActivityType.tpl"}
{/if}

{if $rows}
<div id="ltype">
<p>
    <div class="form-item">
        {strip}
        <table>
        <tr class="columnheader">
            <th>{ts}Name{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Reserved?{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
	    <th>{ts}Default?{/ts}</th>
            <th></th>
        </tr>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.name}</td>	
            <td>{$row.description}</td>
	        <td>{if $row.is_reserved eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
		<td>{if $row.is_default eq 1} [X] {else}  {/if}</td>
	        <td>{$row.action}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}">&raquo; {ts}New Activity Type{/ts}</a>
        </div>
        {/if}
    </div>
</p>
</div>
{else}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"></dt>
        {capture assign=crmURL}{crmURL p='civicrm/admin/activityType' q="action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no Activity Types entered. You can <a href="%1">add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
