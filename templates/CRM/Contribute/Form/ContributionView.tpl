<div class="form-item">  
<fieldset>
      <legend>{ts}View Contribution{/ts}</legend>
      <dl>  
        <dt class="font-size12pt">{ts}From{/ts}</dt><dd class="font-size12pt"><strong>{$displayName}</strong>&nbsp;</dd>
        <dt>{ts}Contribution Type{/ts}</dt><dd>{$contribution_type}&nbsp;
        {if $is_test}
          {ts} (test) {/ts}
        {/if}
        </dd>
        <dt>{ts}Received{/ts}</dt><dd>{if $receive_date}{$receive_date|truncate:10:''|crmDate}{else}({ts}pending{/ts}){/if}&nbsp;</dd>
        <dt>{ts}Paid By{/ts}</dt><dd>{$payment_instrument}&nbsp;</dd>
        <dt>{ts}Source{/ts}</dt><dd>{$source}&nbsp;</dd>
        {foreach from=$note item="rec"}
		    {if $rec }
			<dt>{ts}Note:{/ts}</dt><dd>{$rec}</dd>	
	   	    {/if}
        {/foreach}
        <dt>{ts}Total Amount{/ts}</dt><dd>{$total_amount|crmMoney}&nbsp;
            {if $contribution_recur_id}
                (Recurring Contribution)
            {/if}
        </dd>
        <dt>{ts}Non-deductible Amount{/ts}</dt><dd>{$non_deductible_amount|crmMoney}&nbsp;</dd>
        <dt>{ts}Fee Amount{/ts}</dt><dd>{$fee_amount|crmMoney}&nbsp;</dd>
        <dt>{ts}Net Amount{/ts}</dt><dd>{$net_amount|crmMoney}&nbsp;</dd>
        <dt>{ts}Transaction ID{/ts}</dt><dd>{$trxn_id}&nbsp;</dd>
        <dt>{ts}Invoice ID{/ts}</dt><dd>{$invoice_id}&nbsp;</dd>
        {if $honor_display}
        <dt>{ts}In Honor Of{/ts}</dt><dd>{$honor_display}&nbsp;</dd>
        {/if}
            
{if $receipt_date}
        <dt>{ts}Receipt Sent{/ts}</dt><dd>{$receipt_date|truncate:10:''|crmDate}</dd>
{/if}
{if $thankyou_date}
        <dt>{ts}Thank-you Sent{/ts}</dt><dd>{$thankyou_date|truncate:10:''|crmDate}</dd>
{/if}
{if $cancel_date}
        <dt>{ts}Cancelled{/ts}</dt><dd>{$cancel_date|truncate:10:''|crmDate}</dd>
        {if $cancel_reason}
        <dt>{ts}Cancellation Reason{/ts}</dt><dd>{$cancel_reason}</dd>
        {/if}
{/if}
        <dt>{ts}Contribution Status{/ts}</dt><dd>{$contribution_status}</dd>
    </dl>
{if $premium}
<fieldset>
    <legend>{ts}Premium Information{/ts}</legend>
    <dl>
      <dt>{ts}Premium{/ts}</dt><dd>{$premium}&nbsp;</dd>
      <dt>{ts}Option{/ts}</dt><dd>{$option}&nbsp;</dd>
      <dt>{ts}Fulfilled{/ts}</dt><dd>{$fulfilled|truncate:10:''|crmDate}&nbsp;</dd>  
    </dl>
</fieldset>    
{/if}
{include file="CRM/Contact/Page/View/InlineCustomData.tpl" mainEditForm=1}
    <dl>
        <dt></dt><dd>{$form.buttons.html}</dd>
    </dl>
</fieldset>  
</div>  
 
