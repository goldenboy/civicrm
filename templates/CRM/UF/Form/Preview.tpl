{if $previewField }
{capture assign=infoMessage}<strong>{ts}Profile Field Preview{/ts}</strong>{/capture}
{else}
{capture assign=infoMessage}<strong>{ts}Profile Preview{/ts}</strong>{/capture}
{/if}
{include file="CRM/common/info.tpl"}
{if ! empty( $fields )}
{if $viewOnly }
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container-inner" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
 {include file="CRM/common/CMSUser.tpl"}      
    {strip} 
    {if $help_pre && $action neq 4}<div class="messages help">{$help_pre}</div>{/if}
    {assign var=zeroField value="Initial Non Existent Fieldset"}
    {assign var=fieldset  value=$zeroField}
    {foreach from=$fields item=field key=fieldName}
    {if $field.groupTitle != $fieldset}
        {if $fieldset != $zeroField}
           </table> 
           {if $groupHelpPost}
              <div class="messages help">{$groupHelpPost}</div>
           {/if}
           {if $mode ne 8}
              </fieldset>
           {/if}
        {/if}   
       {if $mode ne 8}
            <fieldset><legend>{$field.groupTitle}</legend>
       {/if}
        {assign var=fieldset  value=`$field.groupTitle`}
        {assign var=groupHelpPost  value=`$field.groupHelpPost`}
        {if $field.groupHelpPre}
            <div class="messages help">{$field.groupHelpPre}</div>
        {/if}
        <table class="form-layout-compressed" id="table-1">
    {/if}
    {assign var=n value=$field.name}
    {if $field.options_per_line }
	<tr>
        <td class="option-label">{$form.$n.label}</td>
        <td>
	    {assign var="count" value="1"}
        {strip}
        <table class="form-layout-compressed">
       
         <tr>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {assign var="index" value="1"}
          {foreach name=outer key=key item=item from=$form.$n}
          {if $index < 10}
            {assign var="index" value=`$index+1`}
          {else}
            <td class="labels font-light">{$form.$n.$key.html}</td>
              {if $count == $field.options_per_line}
         </tr>         
                   {assign var="count" value="1"}
              {else}
          	       {assign var="count" value=`$count+1`}
              {/if}
          {/if}
          {/foreach}
        </table>
	{if $field.html_type eq 'Radio' and $form.formName eq 'Preview'}
            &nbsp;&nbsp;(&nbsp;<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}unselect{/ts}</a>&nbsp;)
	{/if}
        {/strip}
        </td>
    </tr>
	{else}
        <tr><td class="label">{$form.$n.label}</td>
	<td>
           {if $n|substr:0:3 eq 'im-'}
             {assign var="provider" value=$n|cat:"-provider_id"}
             {$form.$provider.html}&nbsp;
           {/if}
	{if $n eq 'group' && $form.group}
		<table id="selector" class="selector" style="width:auto;">
		<tr><td>{$form.$n.html}{* quickform add closing </td> </tr>*}
		</table>
    {elseif $n eq 'email_greeting'}
          <table class="form-layout-compressed">
             <tr>
                <td>{$form.$n.html|crmReplace:class:big}</td>
                <td id='emailCustomGreeting'>
                   {$form.email_greeting_custom.label}&nbsp;&nbsp;&nbsp;
                   {$form.email_greeting_custom.html|crmReplace:class:big}
                </td>
             </tr>
          </table>
    {elseif $n eq 'postal_greeting'}
          <table class="form-layout-compressed">
             <tr>
                <td>{$form.$n.html|crmReplace:class:big}</td>
                <td id='postalCustomGreeting'>
                   {$form.postal_greeting_custom.label}&nbsp;&nbsp;&nbsp;
                   {$form.postal_greeting_custom.html|crmReplace:class:big}
                </td>
             </tr>
          </table>   
    {elseif $n eq 'addressee'}
          <table class="form-layout-compressed">
             <tr>
                <td>{$form.$n.html|crmReplace:class:big}</td>
                <td id='addresseeCustom'>
                   {$form.addressee_custom.label}&nbsp;&nbsp;&nbsp;
                   {$form.addressee_custom.html|crmReplace:class:big}
                </td>
             </tr>
          </table>       
	{else}
	   {$form.$n.html}
	   {if $field.is_view eq 0}
	       {if ( $field.html_type eq 'Radio' or  $n eq 'gender') and $form.formName eq 'Preview'}
               	   &nbsp;&nbsp;(&nbsp;<a href="#" title="unselect" onclick="unselectRadio('{$n}', '{$form.formName}'); return false;">{ts}unselect{/ts}</a>&nbsp;)
	       {elseif $field.data_type eq 'Date' AND $element.skip_calendar NEQ true } 
                   <span>
			{include file="CRM/common/calendar/desc.tpl" trigger="$form.$n.name"}
		    	{include file="CRM/common/calendar/body.tpl" dateVar=$form.$n.name startDate=1905 endDate=2010 doTime=1  trigger="$form.$n.name"}
		   </span>
	       {/if}
	   {/if}
	{/if}
        </td>
	{/if}
        {* Show explanatory text for field if not in 'view' mode *}
        {if $field.help_post && $action neq 4}
            <tr><td>&nbsp;</td><td class="description">{$field.help_post}</td></tr>
        {/if}
    {/foreach}  
     
    {if $addCAPTCHA }
        {include file='CRM/common/ReCAPTCHA.tpl'}
    {/if}   
    </table></fieldset>
    {if $field.groupHelpPost}
    <div class="messages help">{$field.groupHelpPost}</div>
    {/if}
    {/strip}
</div> {* end crm-container div *}
{else}
	{capture assign=infoMessage}{ts}This CiviCRM profile field is view only.{/ts}{/capture}
	{include file="CRM/common/info.tpl"}
{/if}
{/if} {* fields array is not empty *}


<div class=" horizontal-center "> 
	{$form.buttons.html}
</div>
{if $form.email_greeting or $form.postal_greeting or $form.addressee}
  {literal}
    <script type="text/javascript">
      window.onload = function() {
        showEmailGreeting();
        showPostalGreeting();
        showAddressee();
      }
    </script>
  {/literal}
{/if}
{literal}
<script type="text/javascript">
  function showEmailGreeting() {
      if( document.getElementById("email_greeting").value == 4 ) {
           show('emailCustomGreeting');                   
      } else {
           hide('emailCustomGreeting');      
      }     
  }
  function showPostalGreeting() {
      if( document.getElementById("postal_greeting").value == 4 ) {
           show('postalCustomGreeting');                   
      } else {
           hide('postalCustomGreeting');      
      }     
  }
  function showAddressee() {
      if( document.getElementById("addressee").value == 4 ) {
           show('addresseeCustom');                   
      } else {
           hide('addresseeCustom');      
      }     
  }
cj(document).ready(function(){ 
	cj('#selector tr:even').addClass('odd-row ');
	cj('#selector tr:odd ').addClass('even-row');

    // Initialise the table
    cj("#table-1").tableDnD();
    
    cj("#table-5 tr").hover(function() {
        cj(this.cells[0]).addClass('showDragHandle');
    }, function() {
        cj(this.cells[0]).removeClass('showDragHandle');
    });
    
});
</script>
{/literal}

