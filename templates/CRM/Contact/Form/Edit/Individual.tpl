{* tpl for building Individual related fields *}
<table class="form-layout-compressed">
    <tr>
        <td>
            {if $form.prefix_id}
                {$form.prefix_id.label}<br/>
                {$form.prefix_id.html}
            {/if}
        </td>    
        <td>
            {$form.first_name.label}<br /> 
            {if $action == 2}
                {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='first_name' id=$contactId}
            {/if}
            {$form.first_name.html}
        </td>
        <td>
            {$form.middle_name.label}<br />
            {if $action == 2}
                {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='middle_name' id=$contactId}
            {/if}
            {$form.middle_name.html}
        </td>
        <td>
            {$form.last_name.label}<br />
            {if $action == 2}
                {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='last_name' id=$contactId}
            {/if}
            {$form.last_name.html}
        </td>
        <td>
            {if $form.suffix_id}
                {$form.suffix_id.label}<br/>
                {$form.suffix_id.html}
            {/if}
        </td>
    </tr>
    
    <tr>
        <td colspan="2">
            {$form.current_employer.label}<br />
            {$form.current_employer.html|crmReplace:class:twenty}
        </td>
                
        <td>
            {$form.job_title.label}<br />
            {$form.job_title.html}
        </td>
        <td colspan="2">
            {$form.nick_name.label}<br />
            {$form.nick_name.html|crmReplace:class:big}
        </td>
    </tr>
</table>
{literal}
<script type="text/javascript">
var dataUrl = "{/literal}{$employerDataURL}{literal}";
cj('#current_employer').autocomplete( dataUrl, { width : 250, selectFirst : false 
                                              }).result( function(event, data, formatted) { cj( "#current_employer_id" ).val( data[1] );
                                              });
</script>
{/literal}