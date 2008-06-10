<div class="form-item">
    <div id="help">
        {ts}Please verify the information below. Click <strong>Go Back</strong> if you need to make changes.{/ts}
    {if $contributeMode EQ 'notify' and $paymentProcessor.payment_processor_type EQ 'Google_Checkout' and !$is_pay_later } 
        {ts}Click the <strong>Google Checkout</strong> button to checkout to Google, where you will select your payment method and complete the registration.{/ts}
    {else}
	{ts}Otherwise, click the <strong>Continue</strong> button below to complete your registration.{/ts}
    {/if}
    </div>

    {if $eventPage.confirm_text}
        <div id="intro_text">
        <p>{$eventPage.confirm_text}</p>
        </div>
    {/if}
    {if $is_pay_later}
        <div class="bold">{$pay_later_receipt}</div>
    {/if}
    
    <div class="header-dark">
        {ts}Event Information{/ts}
    </div>
    <div class="display-block">
         {include file="CRM/Event/Form/Registration/EventInfoBlock.tpl"}
    </div>
    {if $paidEvent} 
    <div class="header-dark">
        {$event.fee_label}
    </div>
    <div class="display-block">
        {if $lineItem}
            {include file="CRM/Event/Form/Registration/LineItem.tpl}
        {elseif $amount || $amount == 0}
        {foreach from= $amount item=amount key=level}  
          <strong>{$amount|crmMoney} &nbsp;&nbsp; {$level}</strong><br />	
        {/foreach}
        {if $totalAmount}
        <br /><strong>{ts}Total Amount{/ts}:</strong>&nbsp;&nbsp;{$totalAmount|crmMoney}
        {/if}	 		
        {/if}
    </div>
    {/if}
	
    <div class="header-dark">
    	{ts}Registered Email{/ts}
    </div>
    <div class="display-block">
        {$email}
    </div>

    {if $customPre}
         {foreach from=$customPre item=field key=cname}
              {if $field.groupTitle}
                {assign var=groupTitlePre  value=$field.groupTitle} 
              {/if}
         {/foreach}
        <div class="header-dark">
          {ts}{$groupTitlePre}{/ts}
         </div>  
         {include file="CRM/UF/Form/Block.tpl" fields=$customPre}
    {/if}
    {if $customPost}
         {foreach from=$customPost item=field key=cname}
              {if $field.groupTitle}
                {assign var=groupTitlePost  value=$field.groupTitle} 
              {/if}
         {/foreach}
        <div class="header-dark">
          {ts}{$groupTitlePost}{/ts}
         </div>  
         {include file="CRM/UF/Form/Block.tpl" fields=$customPost}
    {/if}
{*diaplay Additional Participant customPre profile Info*}
{if $customPre_addParticipants}
<div class="header-dark">
    	{ts}Additional Participants{/ts} : {$customPre_addParticipants_groupName}
</div>

<div id="id-addParticipantsPre-show" class="section-hidden section-hidden-border" style="clear: both;">
        <a href="#" onclick="hide('id-addParticipantsPre-show'); show('id-addParticipantsPre'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Additional Participants{/ts}</label><br />
</div>
<div id="id-addParticipantsPre" class="section-shown">
   <fieldset>
     <legend><a href="#" onclick="hide('id-addParticipantsPre'); show('id-addParticipantsPre-show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Additional Participants{/ts}</legend>
<table class="form-layout-compressed">
{foreach from=$customPre_addParticipants item=participant key=participantNum}
<tr><td class="right font-size10pt bold">{ts}Participant : {$participantNum} {/ts}&nbsp;&nbsp;</td>
   <tr>
      {foreach from=$participant item=value key=field}
          <tr>
             <td class="labels">{$field}</td> <td>{$value}</td>
          </tr>
      {/foreach}
   </tr>
{/foreach}
</table>
</fieldset>
</div>  
{/if}

{*diaplay Additional Participant customPost profile Info*}
{if $customPost_addParticipants}
<div class="header-dark">
    	{ts}Additional Participants{/ts} : {$customPost_addParticipants_groupName} 
</div>
<div id="id-addParticipantsPost-show" class="section-hidden section-hidden-border" style="clear: both;">
        <a href="#" onclick="hide('id-addParticipantsPost-show'); show('id-addParticipantsPost'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Additional Participants{/ts}</label><br />
</div>
<div id="id-addParticipantsPost" class="section-shown">
   <fieldset>
     <legend><a href="#" onclick="hide('id-addParticipantsPost'); show('id-addParticipantsPost-show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Additional Participants{/ts}</legend>
<table class="form-layout-compressed">
{foreach from=$customPost_addParticipants item=participant key=participantNum}
<tr><td class="right font-size10pt bold">{ts}Participant : {$participantNum} {/ts}&nbsp;&nbsp;</td>
   <tr>
      {foreach from=$participant item=value key=field}
          <tr>
             <td class="labels">{$field}</td> <td>{$value}</td>
          </tr>
      {/foreach}
   </tr>
{/foreach}
</table>
</fieldset>
</div> 
{/if}

    {if $contributeMode ne 'notify' and
        ! $is_pay_later             and
        $paidEvent                  and
	! $isAmountzero 
	}
    <div class="header-dark">
        {ts}Billing Name and Address{/ts}
    </div>
    <div class="display-block">
        <strong>{$name}</strong><br />
        {$address|nl2br}
    </div>
    {/if}
    
    {if $contributeMode eq 'direct' and
        ! $is_pay_later and !$isAmountzero}
    <div class="header-dark">
        {ts}Credit or Debit Card Information{/ts}
    </div>
    <div class="display-block">
        {$credit_card_type}<br />
        {$credit_card_number}<br />
        {ts}Expires{/ts}: {$credit_card_exp_date|truncate:7:''|crmDate}<br />
    </div>
    {/if}
    
    {if $contributeMode NEQ 'notify'} {* In 'notify mode, contributor is taken to processor payment forms next *}
    <div class="messages status">
        <p>
        {ts}Your registration will not be completed until you click the <strong>Continue</strong> button. Please click the button one time only.{/ts}
        </p>
    </div>
    {/if}    
   
    {if $paymentProcessor.payment_processor_type EQ 'Google_Checkout' and $paidEvent and !$is_pay_later}
        <fieldset><legend>{ts}Checkout with Google{/ts}</legend>
         <table class="form-layout-compressed">
          <tr><td class="description">{ts}Click the Google Checkout button to continue.{/ts}</td></tr>
          <tr><td>{$form._qf_Confirm_next_checkout.html} <span style="font-size:11px; font-family: Arial, Verdana;">Checkout securely.  Pay without sharing your financial information. </span></td></tr>
         </table>
        </fieldset>    
    {/if}

    <div id="crm-submit-buttons">
     {$form.buttons.html}
    </div>

    {if $eventPage.confirm_footer_text}
        <div id="footer_text">
            <p>{$eventPage.confirm_footer_text}</p>
        </div>
    {/if}
</div>
{include file="CRM/common/showHide.tpl"}
