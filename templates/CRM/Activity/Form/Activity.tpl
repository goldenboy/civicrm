 {* this template is used for adding/editing other (custom) activities. *}
{if $cdType }
   {include file="CRM/Custom/Form/CustomData.tpl"}
{else}
{* added onload javascript for source contact*}
{literal}
<script type="text/javascript">
var target_contact = assignee_contact = '';

{/literal}
{foreach from=$target_contact key=id item=name}
     {literal} target_contact += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
{literal} eval( 'target_contact = [' + target_contact + ']'); {/literal}

{if $assigneeContactCount}
{foreach from=$assignee_contact key=id item=name}
     {literal} assignee_contact += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
{literal} eval( 'assignee_contact = [' + assignee_contact + ']'); {/literal}
{/if}
{literal}

var target_contact_id = assignee_contact_id = null;
//loop to set the value of cc and bcc if form rule.
var toDataUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' q='id=1&noemail=1' h=0 }{literal}"; {/literal}
{foreach from=","|explode:"target,assignee" key=key item=element}
  {assign var=currentElement value=`$element`_contact_id}
  {if $form.$currentElement.value }
     {literal} var {/literal}{$currentElement}{literal} = cj.ajax({ url: toDataUrl + "&cid={/literal}{$form.$currentElement.value}{literal}", async: false }).responseText;{/literal}
  {/if}
{/foreach}
{literal}
if ( target_contact_id ) {
  eval( 'target_contact = ' + target_contact_id );
}
if ( assignee_contact_id ) {
  eval( 'assignee_contact = ' + assignee_contact_id );
}
cj(document).ready( function( ) {
{/literal}
{if $source_contact and $admin and $action neq 4} 
{literal} cj( '#source_contact_id' ).val( "{/literal}{$source_contact}{literal}");{/literal}
{/if}
{literal}

eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');

var sourceDataUrl = "{/literal}{$dataUrl}{literal}";
var hintText = "{/literal}{ts}Type in a partial or complete name or email{/ts}{literal}";
cj( "#target_contact_id"  ).tokenInput( sourceDataUrl, { prePopulate: target_contact,   classes: tokenClass, hintText: hintText });
cj( "#assignee_contact_id").tokenInput( sourceDataUrl, { prePopulate: assignee_contact, classes: tokenClass, hintText: hintText });
cj( 'ul.token-input-list-facebook, div.token-input-dropdown-facebook' ).css( 'width', '450px' );
cj('#source_contact_id').autocomplete( sourceDataUrl, { width : 180, selectFirst : false, hintText: hintText, matchContains: true
                            }).result( function(event, data, formatted) { cj( "#source_contact_qid" ).val( data[1] );
                            }).bind( 'click', function( ) { cj( "#source_contact_qid" ).val(''); });
});
</script>
{/literal}

    <fieldset>
    <legend>
       {if $single eq false}
          {ts}New Activity{/ts}
       {elseif $action eq 1}
          {ts}New{/ts} 
       {elseif $action eq 2}
          {ts}Edit{/ts} 
       {elseif $action eq 8}
          {ts}Delete{/ts}
       {elseif $action eq 4}
          {ts}View{/ts}
       {elseif $action eq 32768}
          {ts}Detach{/ts}
       {/if}
       {$activityTypeName}
    </legend>
      
        {if $action eq 8} {* Delete action. *}
            <table class="form-layout">
             <tr>
                <td colspan="2">
                    <div class="status">{ts 1=$delName}Are you sure you want to delete '%1'?{/ts}</div>
                </td>
             </tr>
               
        {elseif $action eq 1 or $action eq 2  or $action eq 4 or $context eq 'search' or $context eq 'smog'}
            { if $activityTypeDescription }  
                <div id="help">{$activityTypeDescription}</div>
            {/if}

            <table class="{if $action eq 4}view-layout{else}form-layout{/if}">
             {if $context eq 'standalone' or $context eq 'search' or $context eq 'smog'}
                <tr>
                   <td class="label">{$form.activity_type_id.label}</td><td class="view-value">{$form.activity_type_id.html}</td>
                </tr>
             {/if}
             <tr>
                <td class="label">{$form.source_contact_id.label}</td>
                <td class="view-value">
                    {if $admin and $action neq 4}{$form.source_contact_id.html} {else} 
			{$source_contact_value} 
		   {/if}
                </td>
             </tr>
             
             {if $single eq false}
             <tr>
                <td class="label">{ts}With Contact(s){/ts}</td>
                <td class="view-value" style="white-space: normal">{$with|escape}</td>
             </tr>
             {elseif $action neq 4}
             <tr>
                <td class="label">{ts}With Contact{/ts}</td>
                <td>{$form.target_contact_id.html}</td>
             </tr>
		     {else}
             <tr>
                <td class="label">{ts}With Contact{/ts}</td>
                <td class="view-value" style="white-space: normal">
			{foreach from=$target_contact key=id item=name}
			  <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$id"}">{$name}</a>;&nbsp;
			{/foreach}
		</td>
             </tr>
             {/if}
             
             <tr>
             {if $action eq 4}
                <td class="label">{ts}Assigned To {/ts}</td><td class="view-value">
			{foreach from=$assignee_contact key=id item=name}
			  <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$id"}">{$name}</a>;&nbsp;
			{/foreach}

		</td>
             {else}
                <td class="label">{ts}Assigned To {/ts}</td>
                <td>{$form.assignee_contact_id.html}
                   {edit}<span class="description">{ts}You can optionally assign this activity to someone. Assigned activities will appear in their Activities listing at CiviCRM Home.{/ts}<br />{ts}A copy of this activity will be emailed to each Assignee.{/ts}</span>
                   {/edit}
                </td>
             {/if}
             </tr>

            {if $activityTypeFile}
                {include file="CRM/$crmDir/Form/Activity/$activityTypeFile.tpl"}
            {/if}

             <tr>
                <td class="label">{$form.subject.label}</td><td class="view-value">{$form.subject.html}</td>
             </tr>
             <tr>
                <td class="label">{$form.location.label}</td><td class="view-value">{$form.location.html}</td>
             </tr> 
             <tr>
                <td class="label">{$form.activity_date_time.label}</td>
                {if $action neq 4}
                    <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=activity_date_time}</td>
                {else}
                    <td class="view-value">{$form.activity_date_time.html|crmDate}</td>
                {/if}
             </tr>
             <tr>
                <td class="label">{$form.duration.label}</td>
                <td class="view-value">
                    {$form.duration.html}
                    {if $action neq 4}<span class="description">{ts}Total time spent on this activity (in minutes).{/ts}{/if}
                </td>
             </tr> 
             <tr>
                <td class="label">{$form.status_id.label}</td><td class="view-value">{$form.status_id.html}</td>
             </tr> 
             <tr>
                <td class="label">{$form.details.label}</td><td class="view-value">{$form.details.html|crmReplace:class:huge}</td>
             </tr> 
             <tr>
                <td class="label">{$form.priority_id.label}</td><td class="view-value">{$form.priority_id.html}</td>
             </tr>
             <tr>
                <td colspan="2">
	            {if $action eq 4} 
                    {include file="CRM/Custom/Page/CustomDataView.tpl"}
                {else}
                    <div id="customData"></div>
                {/if} 
                </td>
             </tr> 

             <tr>
                <td colspan="2">
                    {include file="CRM/Form/attachment.tpl"}
                </td>
             </tr>

             {if $action neq 4} {* Don't include "Schedule Follow-up" section in View mode. *}
                 <tr>
                    <td colspan="2">
                     <div id="follow-up_show" class="section-hidden section-hidden-border">
                      <a href="#" onclick="hide('follow-up_show'); show('follow-up'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="open section"/></a><label>{ts}Schedule Follow-up{/ts}</label><br />
                     </div>
                          
                     <div id="follow-up" class="section-shown">
                       <fieldset><legend><a href="#" onclick="hide('follow-up'); show('follow-up_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="close section"/></a>{ts}Schedule Follow-up{/ts}</legend>
                        <table class="form-layout-compressed">
                           <tr><td class="label">{ts}Schedule Follow-up Activity{/ts}</td>
                               <td>{$form.followup_activity_type_id.html}&nbsp;{$form.interval.label}&nbsp;{$form.interval.html}&nbsp;{$form.interval_unit.html}                          </td>
                           </tr>
                           <tr>
                              <td class="label">{$form.followup_activity_subject.label}</td>
                              <td>{$form.followup_activity_subject.html}</td>
                           </tr>
                        </table>
                       </fieldset>
                     </div>
                    </td>
                 </tr>
             {/if}
        {/if} {* End Delete vs. Add / Edit action *}
        <tr>
            <td>{$form.buttons.html}</td><td>
       {if $action eq 4}
                        <a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&atype=$atype&action=update&reset=1&id=$entityID&cid=$contactId&context=home"}" class="edit button" title="{ts}Edit{/ts}">
                        <span><div class="icon edit-icon"></div>{ts}Edit{/ts}</span>
                        </a>
                        <a href="{crmURL p='civicrm/contact/view/activity' q="reset=1&atype=$atype&action=delete&reset=1&id=$entityID&cid=$contactId&context=home"}" class="delete button" title="{ts}Delete{/ts}">
                        <span><div class="icon delete-icon"></div>{ts}Delete{/ts}</span>
                        </a>
	{/if}
		</td>
        </tr> 
        </table>   
      </fieldset> 

{if $form.case_select}
<div id="fileOnCaseDialog">
{$form.case_select.label}<br /><br />
{$form.case_select.html}<br /><br />
{$form.case_subject.label}<br /><br />
{$form.case_subject.html}
</div>

{literal}
<script type="text/javascript">

cj("#fileOnCaseDialog").hide( );
function fileOnCase() {
    cj("#fileOnCaseDialog").show( );

	cj("#fileOnCaseDialog").dialog({
		title: "File on case",
		modal: true,
		bgiframe: true,
		width: 450,
		height: 300,
		overlay: { 
			opacity: 0.5, 
			background: "black"
		},

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

		open:function() {
		},

		buttons: { 
			"Ok": function() { 	    
				var v1 = cj("#case_select").val();
				if ( ! v1 ) {
					alert('{/literal}{ts}Please select a case from the list{/ts}{literal}.');
					return false;
				}
				var v2 = cj("#case_subject").val();
				
				var destUrl = {/literal}"{crmURL p='civicrm/contact/view/case' q='reset=1&action=view&id=' h=0 }"{literal}; 
 				var activityID = {/literal}"{$entityID}"{literal};
 				var underscore_pos = v1.indexOf('_');
 				if (underscore_pos < 1) {
 					alert('{/literal}{ts}A problem occurred during case selection{/ts}{literal}.');
 					return false;
 				}
 				var case_id = v1.substring(0, underscore_pos);
 				var contact_id = v1.substring(underscore_pos+1);
 				
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");

				var postUrl = {/literal}"{crmURL p='civicrm/ajax/activity/convert' h=0 }"{literal};
                cj.post( postUrl, { activityID: activityID, caseID: v1, newSubject: v2 },
                    function( data ) {
                    		if (data.error_msg == "") {
                            	window.location.href = destUrl + case_id + '&cid=' + contact_id;
                            } else {
                            	alert("{/literal}{ts}Unable to file on case{/ts}{literal}.\n\n" + data.error_msg);
                            	return false;
                            } 
                        }, 'json' 
                    );
			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	});
}

</script>
{/literal}
{/if}

{if $action eq 1 or $action eq 2 or $context eq 'search' or $context eq 'smog'}
   {*include custom data js file*}
   {include file="CRM/common/customData.tpl"}
    {literal}
    <script type="text/javascript">
   	cj(document).ready(function() {
		{/literal}
		buildCustomData( '{$customDataType}' );
		{if $customDataSubType}
			buildCustomData( '{$customDataType}', {$customDataSubType} );
		{else}
		    {literal}
		    if ( cj("#activity_type_id").val( ) ) {
		        buildCustomData( '{/literal}{$customDataType}{literal}', cj("#activity_type_id").val( ) );
	        }
	        {/literal}
		{/if}
		{literal}
	});

  hide('follow-up');
  show('follow-up_show');
    </script>
    {/literal}
{/if}

{literal}
<script type="text/javascript">
function verify( ) {
    var d = new Date();
    var currentDateTime = d.getTime();
    d.setFullYear(cj("select#activity_date_time\\[Y\\]").val());
    d.setMonth(cj("select#activity_date_time\\[M\\]").val() - 1);
    d.setDate(cj("select#activity_date_time\\[d\\]").val());
    var hours = cj("select#activity_date_time\\[h\\]").val();
    var ampm = cj("select#activity_date_time\\[A\\]").val();
    if (ampm == "PM" && hours != 0 && hours != 12) {
        // force arithmetic instead of string concatenation
        hours = hours*1 + 12;
    } else if (ampm == "AM" && hours == 12) {
        hours = 0;
    }
    d.setHours(hours);
    d.setMinutes(cj("select#activity_date_time\\[i\\]").val());

    var activity_date_time = d.getTime();

    var activityStatusId = cj('#status_id').val();

    if ( activityStatusId == 2 && currentDateTime < activity_date_time ) {
        var okMessage = confirm( '{/literal}{ts}Are you sure? This is a COMPLETED activity with the DATE in the FUTURE. Click Cancel to change the date / status. Otherwise, click OK to save.{/ts}{literal}' ); 
        if (!okMessage ) {
            return false;
        }
    } else if ( activity_date_time && activityStatusId == 1 && currentDateTime >= activity_date_time ) {
        var ok = confirm( '{/literal}{ts}Are you sure? This is a SCHEDULED activity with the DATE in the PAST. Click Cancel to change the date / status. Otherwise, click OK to save.{/ts}{literal}' ); 
        if (!ok ) {
            return false;
        }
    }
}

</script>
{/literal}

{include file="CRM/common/formNavigate.tpl"}

{/if} {* end of snippet if*}	
