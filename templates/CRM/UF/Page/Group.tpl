{if $action eq 1 or $action eq 2 or $action eq 4}
    {include file="CRM/UF/Form/Group.tpl"}

{else}
    <div id="help">
    {ts}<p>By configuring 'User Sharing', you can allow end-users to edit and/or view
    specific fields from their own contact information. Additionally, 'User Sharing' fields
    control which data is used to match a contact record to a user. You can also mark 'User
    Sharing' fields as viewable by other users and site visitors.</p>
    <p>Each 'User Sharing Group' is presented as a separate form when new users register fo
    and account, as well as when they edit an existing account.</p>
    {/ts}
    </div>

    {if $rows}
    <div id="notes">
    <p>
        <div class="form-item">
        {strip}
        <table>
        <tr class="columnheader">
            <th>{ts}Group Title{/ts}</th>
            <th>{ts}Status?{/ts}</th>
            <th></th>
        </tr>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
            <td>{$row.title}</td>
            <td>{if $row.is_active eq 1} {ts}Active{/ts} {else} {ts}Inactive{/ts} {/if}</td>
            <td>{$row.action}</td>
        </tr>
        {/foreach}
        </table>
        
        {if NOT ($action eq 1 or $action eq 2) }
        <p>
        <div class="action-link">
        <a href="{crmURL p='civicrm/admin/uf/group' q="action=add&reset=1"}">&raquo;  {ts}New User Sharing Group{/ts}</a>
        </div>
        </p>
        {/if}

        {/strip}
        </div>
    </p>
    </div>
    {else}
       {if $action ne 1} {* When we are adding an item, we should not display this message *}
       <div class="message status">
       <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"> &nbsp;
         {ts}No User Sharing Groups have been created yet. You can {/ts}<a href="{crmURL p='civicrm/admin/uf/group' q='action=add&reset=1'}">{ts}add one{/ts} now</a>.
       </div>
       {/if}
    {/if}
{/if}
