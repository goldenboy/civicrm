{* this template is used for adding/editing calls  *}
 <link rel="stylesheet" type="text/css" media="all" href="{$config->resourceBase}css/skins/aqua/theme.css" title="Aqua" />
 <script type="text/javascript" src="{$config->resourceBase}js/calendar.js"></script>
 <script type="text/javascript" src="{$config->resourceBase}js/lang/calendar-en.js"></script>
 <script type="text/javascript" src="{$config->resourceBase}js/calendar-setup.js"></script>

<div class="form-item">
  <fieldset>
   <legend>
    {if $action eq 1}
        {if $log}{ts}Log a Phone Call{/ts}{else}{ts}Schedule a Phone Call{/ts}{/if}
    {elseif $action eq 2}{ts}Edit Scheduled Call{/ts}
    {elseif $action eq 8}{ts}Delete Phone Call{/ts}
    {else}{ts}View Scheduled Call{/ts}{/if}
  </legend>
  <dl>
     {if $action eq 1 or $action eq 2  or $action eq 4}	
        {if $action eq 1}
          <dt>{ts}With Contact{/ts}</dt><dd>{$displayName}&nbsp;</dd>
        {else}
  	  <dt>{ts}With Contact{/ts}</dt><dd>{$targetName}&nbsp;</dd>
	  <dt>{ts}Created By{/ts}</dt><dd>{$sourceName}&nbsp;</dd>
        {/if}
	<dt>{$form.subject.label}</dt><dd>{$form.subject.html}</dd>
	<dt>{$form.phone_id.label}</dt><dd>{$form.phone_id.html}{if $action neq 4}&nbsp;{$form.phone_number.label}&nbsp;{/if}{$form.phone_number.html}</dd>
    {if $action eq 4}
        <dt>{$form.scheduled_date_time.label}</dt><dd>{$scheduled_date_time|crmDate}</dd>
    {else}
        <dt>{$form.scheduled_date_time.label}</dt><dd>{$form.scheduled_date_time.html}
<button id="trigger">...</button>
{literal}
<script type="text/javascript">
  var obj = new Date();
  var currentYear = obj.getFullYear();

  Calendar.setup(
    {
      dateField   : "scheduled_date_time[d]",
      monthField  : "scheduled_date_time[M]",
      yearField   : "scheduled_date_time[Y]",
      button      : "trigger",
      range       : [currentYear, 2008]  
    }
  );
</script>
{/literal}

</dd>
    {/if}
	<dt>{ts}Duration{/ts}</dt><dd>{$form.duration_hours.html} {ts}Hrs{/ts} &nbsp; {$form.duration_minutes.html} {ts}Min{/ts} &nbsp;</dd>
	<dt>{$form.status.label}</dt><dd>{$form.status.html}</dd>
    {if $action neq 4}
        <dt>&nbsp;</dt><dd class="description">{ts}Call will be moved to Activity History when status is 'Completed'.{/ts}</dd>
    {/if}
	<dt>{$form.details.label}</dt><dd>{$form.details.html|crmReplace:class:huge}</dd>
    {/if}

    <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
    {if $action eq 8 }
    <div class="status">{ts} Are you sure you want to delete "{$delName}" ?{/ts}</div>
    {/if}	
    <dt></dt><dd>{$form.buttons.html}</dd>
    
  </dl>
</fieldset>
</div>
