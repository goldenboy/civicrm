{if $action eq 1024}{include file="CRM/Contribute/Form/Contribution/ReceiptPreviewHeader.tpl"}{/if}
{if $module eq 'Membership'}
{if $formValues.receipt_text_signup}
{$formValues.receipt_text_signup}
{elseif $formValues.receipt_text_renewal}
{$formValues.receipt_text_renewal}
{else}{ts}Thanks for your support.{/ts}{/if}

{ts}Please print this receipt for your records.{/ts}

===========================================================
{ts}Membership Information{/ts}

===========================================================
{ts}Membership Type{/ts}: {$membership_name}
{ts}Membership Start Date{/ts}: {$mem_start_date}
{ts}Membership End Date{/ts}: {$mem_end_date}

===========================================================
{ts}Membership Fee{/ts}

===========================================================
{ts}Amount{/ts}: {$formValues.total_amount|crmMoney}
{if $receive_date}
{ts}Received Date{/ts}: {$receive_date|truncate:10:''|crmDate}
{/if}
{if $formValues.paidBy}
{ts}Paid By{/ts}: {$formValues.paidBy}
{/if}

{else if $module eq 'Event Registration'}
{if $receipt_text}
{$receipt_text}
{/if}

{ts}Please print this confirmation for your records.{/ts}

===========================================================
{ts}Event Information{/ts}

===========================================================
{ts}Event{/ts}: {$event}
{if $role neq 'Attendee'}{ts}Role{/ts}: {$role}
{/if}
{ts}Registration Date{/ts}: {$register_date}
{ts}Participant Status{/ts}: {$status}

{if $paid}
===========================================================
{ts}Registration Fee{/ts}

===========================================================
{ts}Amount{/ts}: {$total_amount|crmMoney}
{if $receive_date}
{ts}Received Date{/ts}: {$receive_date|truncate:10:''|crmDate}
{/if}
{if $paidBy}
{ts}Paid By{/ts}: {$paidBy}
{/if}
{/if}
{/if}

{if $customValues}
===========================================================
{$module}{ts} Options{/ts}

===========================================================
{foreach from=$customValues item=value key=name}
 {$name} : {$value}
{/foreach}
{/if}