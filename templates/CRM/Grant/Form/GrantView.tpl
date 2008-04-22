{* this template is used for viewing grant *} 
<div class="form-item">
<fieldset>
     <legend>{ts}View Grant{/ts}</legend>
     <dl class="html-adjust">
          <dt class="font-size12pt">{ts}Name{/ts}</dt><dd class="font-size12pt"><strong>{$displayName}</strong>&nbsp;</dd>    
          <dt>{$form.status_id.label}</dt><dd>{$form.status_id.html}</dd>
          <dt>{$form.grant_type_id.label}</dt><dd>{$form.grant_type_id.html}</dd>
          <dt>{$form.application_received_date.label}</dt><dd>{$application_received_date|crmDate}</dd>
          <dt>{$form.decision_date.label}</dt><dd>{$decision_date|crmDate}</dd>
          <dt>{$form.money_transfer_date.label}</dt><dd>{$money_transfer_date|crmDate}</dd>
          <dt>{$form.grant_due_date.label}</dt><dd>{$grant_due_date|crmDate}</dd>
          <dt>{$form.amount_total.label}</dt><dd>{$form.amount_total.html}</dd>
	      <dt>{$form.amount_requested.label}</dt><dd>{$form.amount_requested.html}</dd>
          <dt>{$form.amount_granted.label}</dt><dd>{$form.amount_granted.html}</dd>
          <dt>{$form.grant_report_received.label}</dt><dd>{$form.grant_report_received.html}</dd>  
          <dt>{$form.rationale.label}</dt><dd>{$form.rationale.html}</dd>
          <dt>{$form.note.label}</dt><dd>{$form.note.html}</dd>
          {include file="CRM/Contact/Page/View/InlineCustomData.tpl" } 
     </dl>
    <div class="spacer"></div>  
    <dl class="html-adjust">
         <dt></dt><dd>{$form.buttons.html}</dd>
    </dl>
</fieldset>
</div>        
    
