{* Open Activities table and Activity History are toggled on this page for now because we don't have a solution for including 2 'selectors' on one page. *}

<div>
  <fieldset>
  <legend>{ts}Open Activities{/ts}</legend>

{if $rows}
  <form title="activity_pager" action="{crmURL}" method="post">
  {include file="CRM/common/pager.tpl" location="top"}

  {strip}
    <table border="1">
      <tr class="columnheader">
      {foreach from=$columnHeaders item=header}
        <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
        </th>
      {/foreach}
      </tr>

      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
      <tr class="{cycle values="odd-row,even-row"}">

        <td>{$row.activity_type}</td>

        <td><a href="{crmURL p='civicrm/contact/view/case' 
                             q="action=view&selectedChild=case&id=`$row.case_id`&cid=`$row.sourceID`"}">
                             {$row.case}</a>
        </td>

        <td><a href="{$viewURL}">{$row.subject}</td></a>
      
        <td>
        {if !$row.source_contact_id}
	  <em>n/a</em>
	{elseif $contactId NEQ $row.source_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.source_contact_id`"}">{$row.source_contact_name}</a>
        {else}
          {$row.source_contact_name}
        {/if}			
        </td>

        <td>
        {if !$row.target_contact_id}
          <em>n/a</em>
        {elseif $contactId NEQ $row.target_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.target_contact_id`"}">{$row.target_contact_name}</a>
        {else}
          {$row.target_contact_name}
        {/if}			
        </td>

        <td>
        {if !$row.assignee_contact_id}
	  <em>n/a</em>
        {elseif $contactId NEQ $row.assignee_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.assignee_contact_id`"}">{$row.assignee_contact_name}</a>
        {else}
          {$row.assignee_contact_name}
        {/if}			
        </td>

        <td>{$row.activity_date_time|crmDate}</td>

        <td>{$row.action}</td>    
      </tr>
      {/foreach}

    </table>
  {/strip}

  {include file="CRM/common/pager.tpl" location="bottom"}
  </form>


</fieldset>
</div>

{else}

  <div class="messages status">
  <dl>{ts}No Activites for this contact.{/ts} 
  <a href="{crmURL p='civicrm/contact/view/activity/' 
                   q="activity_id=5&action=add&reset=1&context=case&caseid=`$caseId`&cid=`$contactId`"}">
                   {ts}Record a new Activity.{/ts}</a>
  </dl>
  </div>

{/if}