{* Contact Summary template to print contact information *}
{literal}
<style type="text/css" media="screen, print">
<!--
  #crm-container div {
    font-size: 12px;
  }
-->
</style>
{/literal}
<form action="{crmURL p='civicrm/contact/view' q="&cid=`$contactId`&reset=1"}" method="post" id="Print1" >
  <div class="form-item">
       <span class="element-right"><input onclick="window.print()" class="form-submit default" name="_qf_Print_next" value="{ts}Print{/ts}" type="submit" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="form-submit" name="_qf_Print_back" value="{ts}Done{/ts}" type="submit" /></span>
  </div>
</form>
{include file="CRM/Contact/Page/View/Summary.tpl"}
<form action="{crmURL p='civicrm/contact/view' q="&cid=`$contactId`&reset=1"}" method="post" id="Print2" >
  <div class="form-item">
       <span class="element-right"><input onclick="window.print()" class="form-submit default" name="_qf_Print_next" value="{ts}Print{/ts}" type="submit" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="form-submit" name="_qf_Print_back" value="{ts}Done{/ts}" type="submit" /></span>
  </div>
</form>
</div>
{literal}
<script type="text/javascript">
cj('#mainTabContainer').children(':first').remove();
cj('#contact-summary' ).children(':first').remove();

</script>
{/literal}