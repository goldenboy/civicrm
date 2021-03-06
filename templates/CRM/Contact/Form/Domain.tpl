{* this template is used for viewing and editing Domain information (for system-generated emails CiviMail-related values) *}
{if !($action eq 4)}
   {$form.buttons.html}
{/if}
<div class="form-item">
<fieldset>
    <table class="form-layout-compressed">
	<tr>
        <td>
			{$form.name.label}{help id="domain-name"}<br />
			{$form.name.html} 
			<br /><span class="description">{ts}The name of the organization or entity which owns this CiviCRM domain.{/ts}</span>
		</td>
	</tr>
	<tr>
		<td>
			{$form.description.label}<br />
			{$form.description.html}
			<br /><span class="description">{ts}Optional description of this domain (useful for sites with multiple domains).{/ts}</span>
        </td>
    </tr>
    </table>   
	<fieldset><legend>{ts}System-generated Mail Settings{/ts}</legend>
		<table class="form-layout-compressed">
		<tr>
			<td>
				{$form.email_name.label} {help id="from-name"}<br />
				{$form.email_name.html}
			</td>
			<td class="">
				{$form.email_address.label} {help id="from-email"}<br />
				{$form.email_address.html} 
				   <br /><span class="description">(info@example.org)</span>
			</td>
		</tr>
		</table>
	</fieldset>
    
    <fieldset><legend>{ts}Domain Address{/ts}</legend>
        <div class="description">{ts}CiviMail mailings must include the sending organization's address. This is done by putting the {ldelim}domain.address{rdelim} token in either the body or footer of the mailing. This token may also be used in regular "Send Email to Contacts" messages and in other Message Templates. The token is replaced by the address entered below when the message is sent.{/ts}</div>
        {include file="CRM/Contact/Form/Edit/Address.tpl"} 
    </fieldset>
    <fieldset><legend>{ts}Additional Domain Contact Information{/ts}</legend>
        <div class="description">{ts}You can also include general email and/or phone contact information in mailings.{/ts} {help id="additional-contact"}</div>
        <table class="form-layout-compressed">
            {* Display the email block *}  
            {include file="CRM/Contact/Form/Edit/Email.tpl"}

            {* Display the phone block *}
            {include file="CRM/Contact/Form/Edit/Phone.tpl"} 
        </table>
    </fieldset>
    
    <div class="spacer"></div>
    
    {if ($action eq 4)}
    <div class="action-link">
    <a href="{crmURL q="action=update&reset=1"}" id="editDomainInfo">&raquo; {ts}Edit Domain Information{/ts}</a>
    </div>
    {/if}
</fieldset>
{if !($action eq 4)}
  {$form.buttons.html}
{/if}
</div>

{* phone_2 a email_2 only included in form if CiviMail enabled. *}
{if array_search('CiviMail', $config->enableComponents)}
    <script type="text/javascript">
    cj('a#addEmail,a#addPhone').hide();
    </script>
{/if}
