<div id="help">
  {if $gName eq "gender"}
    <p>{ts}CiviCRM is pre-configured with standard options for individual gender (e.g. Male, Female, Transgender). You can use this page to customize these options and add new options as needed for your installation.{/ts}</p>

<script type="text/javascript" src="{$config->resourceBase}packages/dojo/dojo.js"></script>
{literal}
<script type="text/javascript">
dojo.require('dojo.widget.Dialog');
var dlg;
function init(e) {
	dlg = dojo.widget.byId("DialogContent");
}
dojo.addOnLoad(init);
</script>
{/literal}

<a href="javascript:dlg.show()">Show</a>

<a dojoType="dialog" id="DialogContent" toggle="wipe" toggleDuration="250" bgColor="black" bgOpacity="0.5"
   executeScripts="true" href="http://civicrm1.electricembers.net/~lobo/drupal/civicrm/admin/gender?reset=1&action=update&id=1&snippet=1"></a>

  {else}
    {if $gName eq "individual_prefix"}
      <p>{ts}CiviCRM is pre-configured with standard options for individual contact prefixes (e.g. Ms., Mr., Dr. etc.). You can use this page to customize these options and add new ones as needed for your installation.{/ts}</p>
    {else}
      {if $gName eq "mobile_provider"}
     <p>{ts}When recording mobile phone numbers for contacts, it may be useful to include the Mobile Phone Service Provider (e.g. Cingular, Sprint, etc.). CiviCRM is installed with the most commonly encountered service providers. Administrators may define as many additional providers as needed.{/ts}</p>
      {else}
        {if $gName eq "instant_messenger_service"}
          <p>{ts}When recording Instant Messenger (IM) 'screen names' for contacts, it is useful to include the IM Service Provider (e.g. AOL, Yahoo, etc.). CiviCRM is installed with the most commonly encountered service providers. Administrators may define as many additional providers as needed.{/ts}</p>
        {else}
          {if $gName eq "individual_suffix"}
            <p>{ts}CiviCRM is pre-configured with standard options for individual contact name suffixes (e.g. Jr., Sr., II etc.). You can use this page to customize these options and add new ones as needed for your installation.{/ts}</p>
          {else}
    	    <p>{ts}The existing option choices for {$GName} group are listed below. You can add, edit or delete them from this screen.{/ts}</p>
          {/if}  
        {/if}  
      {/if}  
    {/if}  
  {/if}
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Options.tpl"}
{*elseif $rows*}
{/if}	

{if $rows}
<div id={$gName}>
<p></p>
    <div class="form-item">
        {strip}
        <table>
        <tr class="columnheader">
            <th>{ts}Name{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Weight{/ts}</th>
            <th>{ts}Reserved{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th></th>
        </tr>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.name}</td>	
	        <td>{$row.description}</td>	
	        <td>{$row.weight}</td>
	        <td>{if $row.is_reserved eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{$row.action}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="group="|cat:$gName|cat:"&action=add&reset=1"}" id="new"|cat:$GName >&raquo; {ts}New {$GName} Option{/ts}</a>
        </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        {capture assign=crmURL}{crmURL p='civicrm/admin/options' q="group="|cat:$gName|cat:"&action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no Gender entered. You can <a href="%1">add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
