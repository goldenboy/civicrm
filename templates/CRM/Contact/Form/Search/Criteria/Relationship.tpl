<div id="relationship" class="form-item">
    <table class="form-layout">
         <tr>
            <td>
               {$form.relation_type_id.label}<br />
               {$form.relation_type_id.html}
            </td>
            <td>
               {$form.relation_target_name.label}<br />
               {$form.relation_target_name.html|crmReplace:class:huge}
                <div class="description font-italic">
                    {ts}Complete OR partial contact name.{/ts}
                </div>
            </td>    
            <td>
               {$form.relation_status.label}&nbsp; <a href="#" title="unselect" onclick="unselectRadio('relation_status', 'Advanced'); return false;" >unselect</a><br />
               {$form.relation_status.html}
            </td>
         </tr>
         {if $relationshipGroupTree}
         <tr>
	        <td colspan="3">
	        {include file="CRM/Custom/Form/Search.tpl" groupTree=$relationshipGroupTree showHideLinks=false}
            </td>
         </tr>
         {/if}
    </table>         
</div>