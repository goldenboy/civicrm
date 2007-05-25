{* This file provides the templating for the Location block *}
{* The phone, Email, Instant messenger and the Address blocks have been plugged in from external source files*}

{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
{* @var $locationCount contains the max number of locations to be displayed, assigned in the Location.php file*}
{* @var $index contains the current index of the location section *}
 
 {section name = locationLoop start = 1 loop = $locationCount}
 {assign var=index value=$smarty.section.locationLoop.index}

 <div id="id_location_{$index}_show" class="section-hidden section-hidden-border label">
    {$form.location.$index.show.html}{if $index EQ 1}{ts}Primary Location{/ts}{else}{ts}Additional Location{/ts}{/if}
 </div>
<div id="id_location_{$index}">
	<fieldset>
    <legend>{$form.location.$index.hide.html}
        {if $index EQ 1}{ts}Primary Location{/ts}{else}{ts}Additional Location{/ts}{/if}
    </legend>
    <div class="form-item">
        {* Location type drop-down (e.g. Home, Work...) *}
        {$form.location.$index.location_type_id.html}

        {* Checkbox for "make this the primary location" *}
        {$form.location.$index.is_primary.html}

        &nbsp; &nbsp; {$form.location.$index.name.label}
        {$form.location.$index.name.html|crmReplace:class:big} 
        {if $locationExists}
            {foreach from=$locationExists item=ltypeid}   
               {if $ltypeid == $form.location.$index.location_type_id.value[0]}
                    {capture assign=deleteLocation}{crmURL p='civicrm/contact/view/delete/location' q="reset=1&action=delete&ltypeid=$ltypeid&cid=$contactId"}{/capture}
                    &nbsp; &nbsp; {ts 1=$deleteLocation} <a href="%1">Delete</a>{/ts}
               {/if} 
            {/foreach}
        {/if}
        
    </div>

    {* Display the phone block(s) *}
    {include file="CRM/Contact/Form/Phone.tpl"}

    {* Display the email block(s) *}
    {include file="CRM/Contact/Form/Email.tpl"}

    {* Display the instant messenger block(s) *}
    {include file="CRM/Contact/Form/IM.tpl"}

    {* Display the elements for shared address *}
    {if $contact_type eq 'Individual' and $index eq 1}
        <div class="form-item">
            {$form.use_household_address.html}{$form.use_household_address.label}
        </div>
        <div class="form-item">
            <span class="labels">
                {$form.shared_household.label}
            </span>
            <span class="fields">
                {$form.shared_household.html}
            </span>
        </div>
        {* -- Spacer div contains floated elements -- *}
        <div class="spacer"></div>
    {/if}


    {* Display the address block *}
    {include file="CRM/Contact/Form/Address.tpl"} 

    </fieldset>
</div> {* End of Location block div *}
{/section}

