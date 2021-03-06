{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="top"}
{/if}
{strip}
<table class="caseSelector">
  <tr class="columnheader">

  {if ! $single and $context eq 'Search' }
    <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th>
  {/if}

  {if $single}
    <th scope="col">{ts}ID{/ts}</th>
  {else}
    <th></th>
  {/if}

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

  <tr id='rowid{$list}{$row.case_id}'>
    {if $context eq 'Search' && !$single}
        {assign var=cbName value=$row.checkbox}
        <td>{$form.$cbName.html}</td> 
    {/if}
    {if $single }
        <td>{$row.case_id}</td>
    {/if}
    {if $context != 'case'}	
        <td>
        <span id="{$list}{$row.case_id}_show">
            <a href="#" onclick="show('caseDetails{$list}{$row.case_id}', 'table-row'); 
                                 buildCaseDetails('{$list}{$row.case_id}','{$row.contact_id}'); 
                                 hide('{$list}{$row.case_id}_show');
                                 show('minus{$list}{$row.case_id}_hide');
                                 show('{$list}{$row.case_id}_hide','table-row');
                                 return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a>
        </span>
        <span id="minus{$list}{$row.case_id}_hide">
            <a href="#" onclick="hide('caseDetails{$list}{$row.case_id}'); 
                                 show('{$list}{$row.case_id}_show', 'table-row');
                                 hide('{$list}{$row.case_id}_hide');
                                 hide('minus{$list}{$row.case_id}_hide');
                                 return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a>
        </td>
    {/if}	
  
    {if !$single}
    	<td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}" title="{ts}view contact details{/ts}">{$row.sort_name}</a>{if $row.phone}<br /><span class="description">{$row.phone}</span>{/if}<br /><span class="description">{ts}Case ID{/ts}: {$row.case_id}</span></td>
    {/if}
    
    <td class="{$row.class}">{$row.case_status_id}</td>
    <td>{$row.case_type_id}</td>
    <td>{if $row.case_role}{$row.case_role}{else}---{/if}</td>
    <td>{if $row.casemanager_id}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.casemanager_id`"}">{$row.casemanager}</a>{else}---{/if}</td>
    <td>{if $row.case_recent_activity_type}
	{$row.case_recent_activity_type}<br />{$row.case_recent_activity_date|crmDate}{else}---{/if}</td>
    <td>{if $row.case_scheduled_activity_type}
	{$row.case_scheduled_activity_type}<br />{$row.case_scheduled_activity_date|crmDate}{else}---{/if}</td>
    <td>{$row.action|replace:'xx':$row.case_id}</td>
   </tr>
{if $context != 'case'}
   <tr id="{$list}{$row.case_id}_hide" class='{$rowClass}'>
     <td>
     </td>
{if $context EQ 'Search'}
     <td colspan="10" class="enclosingNested">
{else}
     <td colspan="9" class="enclosingNested">
{/if}
        <div id="caseDetails{$list}{$row.case_id}"></div>
     </td>
   </tr>
 <script type="text/javascript">
     hide('{$list}{$row.case_id}_hide');
     hide('minus{$list}{$row.case_id}_hide');
 </script>
{/if}
  {/foreach}

    {* Dashboard only lists 10 most recent casess. *}
    {if $context EQ 'dashboard' and $limit and $pager->_totalItems GT $limit }
      <tr class="even-row">
        <td colspan="10"><a href="{crmURL p='civicrm/case/search' q='reset=1'}">&raquo; {ts}Find more cases{/ts}... </a></td>
      </tr>
    {/if}

</table>
{/strip}

{*include activity view js file*}
{include file="CRM/common/activityView.tpl"}
<div id="view-activity" style="display:none;">
     <div id="activity-content"></div>
</div>
{if $context EQ 'Search'}
 <script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 </script>
{/if}

{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="bottom"}
{/if}

{* Build case details*}
{literal}
<script type="text/javascript">

function buildCaseDetails( caseId, contactId )
{
  var dataUrl = {/literal}"{crmURL p='civicrm/case/details' h=0 q='snippet=4&caseId='}{literal}" + caseId +'&cid=' + contactId;
  cj.ajax({
            url     : dataUrl,
            dataType: "html",
            timeout : 5000, //Time in milliseconds
            success : function( data ){
                           cj( '#caseDetails' + caseId ).html( data );
                      },
            error   : function( XMLHttpRequest, textStatus, errorThrown ) {
                              console.error( 'Error: '+ textStatus );
                    }
         });
}
</script>

{/literal}	