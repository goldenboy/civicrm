{* this template is used for adding/editing calls  *}

<div class="form-item">
  <fieldset>
   <legend>
    {if $action eq 1}
        {if $log}{ts}Log a Phone Call{/ts}{else}{ts}Schedule a Phone Call{/ts}{/if}
    {elseif $action eq 2}{ts}Edit Scheduled Call{/ts}
    {elseif $action eq 8}{ts}Delete Phone Call{/ts}
    {else}{ts}View Scheduled Call{/ts}{/if}
  </legend>
  <dl class="html-adjust">
    {if $action eq 1 or $action eq 2  or $action eq 4}	
    {include file="CRM/Activity/Form/Activity.tpl"}
  </dl>
  <div class="spacer"></div>  
  <dl class="html-adjust">  
	<dt>{$form.subject.label}</dt><dd>{$form.subject.html}</dd>
   	<dt>{$form.phone_id.label}</dt><dd>{$form.phone_id.html}
        {edit}
                {*if $action neq 4*}{* Commented for crm-914*}
        &nbsp;{$form.phone_number.label}&nbsp;
                {*/if*}
        {/edit}{$form.phone_number.html}</dd>
    {if $action eq 4}
        <dt>{$form.scheduled_date_time.label}</dt><dd>{$scheduled_date_time|crmDate}</dd>
    {else}
        <dt>{$form.scheduled_date_time.label}</dt>
        <dd>{$form.scheduled_date_time.html}</dd>
        <dt>&nbsp;</dt>
        <dd class="description">
               {include file="CRM/common/calendar/desc.tpl" trigger=trigger_activity_1}
        </dd>
        <dt>&nbsp;</dt>
        <dd class="description">               
{include file="CRM/common/calendar/body.tpl" dateVar=scheduled_date_time startDate=currentYear endDate=endYear offset=3 doTime=1 trigger=trigger_activity_1}
        </dd>
    {/if}
	<dt>{ts}Duration{/ts}</dt><dd>{$form.duration_hours.html} {ts}Hrs{/ts} &nbsp; {$form.duration_minutes.html} {ts}Min{/ts} &nbsp;</dd>
	<dt>{$form.status.label}</dt><dd>{$form.status.html}</dd>
    {edit}      {*if $action neq 4*}   {*Commented for crm-914*}
        <dt>&nbsp;</dt><dd class="description">{ts}Call will be moved to Activity History when status is 'Completed'.{/ts}</dd>
    {/edit}     {*/if*}
	<dt>{$form.details.label}</dt><dd>{$form.details.html|crmReplace:class:huge}&nbsp;</dd>
    

    <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
    <dt>&nbsp;</dt>
        <dd class="description"> 
    {if $action eq 4} 
      {include file="CRM/Contact/Page/View/InlineCustomData.tpl"}
    {else}
      {include file="CRM/Contact/Page/View/CustomData.tpl" mainEditForm=1}
    {/if}
        </dd>
{/if}
    {if $action eq 8 }
    <div class="status">{ts} Are you sure you want to delete "{$delName}" ?{/ts}</div>
    {/if}	
    <dt></dt><dd>{$form.buttons.html}</dd>
    {if $action eq 4 and !$history }
    <dl><dt></dt> <dd>&nbsp;&nbsp;</dd>&nbsp;<dd><a href="{crmURL p='civicrm/contact/view/activity' q="activity_id=2&action=update&reset=1&id=`$id`&cid=`$contactId`&context=`$context`&subType=2&edit=1&caseid=`$caseid`"}" ">{ts}Edit Phone Call{/ts}</a>{ts} | {/ts}
   <a href="{crmURL p='civicrm/contact/view/activity'
     q="activity_id=2&action=delete&reset=1&id=`$id`&cid=`$contactId`&context=`$context`&subType=2&caseid=`$caseid`"}" ">{ts}  Delete PhoneCall {/ts}</a>{ts} | {/ts}
        
        {if $subject_value}  
           <a href="{crmURL p='civicrm/contact/view/case'
     q="activity_id=2&action=delete&reset=1&id=`$id`&cid=`$contactId`&context=`$context`&subType=2&caseid=`$caseid`"}" ">{ts}  Detach Phone from Case {/ts}</a>
        {/if}
        </dd>
    </dl>
    {/if}    
  </dl>
</fieldset>
</div>
