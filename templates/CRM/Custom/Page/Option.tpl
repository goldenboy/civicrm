{if $action eq 1 or $action eq 2 or $action eq 4 or $action eq 8}
    {include file="CRM/Custom/Form/Option.tpl"}
{/if}

{if $customOption}
    {if $reusedNames}
        <div class="message status">
            <dl><dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt><dd>{ts 1=$reusedNames}These Multiple Choice Options are shared by the following custom fields: %1{/ts}</dd></dl>
        </div>
    {/if}
    
    <div id="field_page">
     <p></p>
        <div class="form-item">
        {strip}
	{* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
        <table class="selector">
        <tr class="columnheader">
        <th>{ts}Label{/ts}</th>
        <th>{ts}Value{/ts}</th>
	<th>{ts}Default{/ts}</th>
        <th>{ts}Weight{/ts}</th>
	<th>{ts}Enabled?{/ts}</th>
        <th>&nbsp;</th>
        </tr>
        {foreach from=$customOption item=row key=id}
	<tr id="row_{$id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
            <td>{$row.label}</td>
            <td>{$row.value}</td>
            <td>{$row.default_value}</td>
            <td class="nowrap">{$row.weight}</td>
	    <td id="row_{$id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
            <td>{$row.action|replace:'xx':$id}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}
        
        <div class="action-link">
            <a href="{crmURL q="reset=1&action=add&fid=$fid&gid=$gid"}" class="button"><span>&raquo; {ts 1=$fieldTitle}New Option for '%1'{/ts}</span></a>
        </div>

        </div>
     </div>

{else}
    {if $action eq 16}
        <div class="messages status">
        <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        <dd>{capture assign=crmURL}{crmURL p='civicrm/admin/custom/group/field/option' q="action=add&fid=$fid&gid=$gid"}{/capture}{ts 1=$fieldTitle 2=$crmURL}There are no multiple choice options for the custom field '%1', <a href='%2'>add one</a>.{/ts}</dd>
        </dl>
        </div>
    {/if}
{/if}
