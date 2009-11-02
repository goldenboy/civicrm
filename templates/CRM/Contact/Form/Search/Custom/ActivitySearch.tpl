{* Template for "Sample" custom search component. *}
{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show','searchForm_hide'"}

<div id="searchForm_show" class="form-item">
  <a href="#" onclick="hide('searchForm_show'); show('searchForm'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}" /></a>
  <label>{ts}Edit Search Criteria{/ts}</label>
</div>

<div id="searchForm" class="form-item">
<fieldset>
    <legend><span id="searchForm_hide"><a href="#" onclick="hide('searchForm','searchForm_hide'); show('searchForm_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}" /></a></span>{ts}Search Criteria{/ts}</legend>

<table class="form-layout-compressed">
    {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
    {foreach from=$elements item=element}
    <tr>
        <td class="label">{$form.$element.label}</td>
        <td>{if $element eq 'start_date' OR $element eq 'end_date'}
                {include file="CRM/common/jcalendar.tpl" elementName=$element}
            {else}
                {$form.$element.html}
            {/if}
        </td>
    </tr>
    {/foreach}
    <tr>
        <td>&nbsp;</td><td>{$form.buttons.html}</td>
    </tr>
</table>
</fieldset>
</div>

{if $rowsEmpty}
    {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {assign var="showBlock" value="'searchForm_show'"}
    {assign var="hideBlock" value="'searchForm'"}
    
    <fieldset>
    
       {* This section handles form elements for action task select and submit *}
       {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}

       {* This section displays the rows along and includes the paging controls *}
       <p>

{include file="CRM/common/pager.tpl" location="top"}

{include file="CRM/common/pagerAToZ.tpl"}

{strip}
<table summary="{ts}Search results listings.{/ts}">
 <thead class="sticky">
  <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
  {foreach from=$columnHeaders item=header}
   {if ($header.sort eq 'activity_id') or ($header.sort eq 'activity_type_id') or ($header.sort eq 'case_id') }
    {else}
            <th scope="col">
            {if $header.sort}
              {assign var='key' value=$header.sort}
              {$sort->_response.$key.link}
            {else}
              {$header.name}
            {/if}
            </th>
    {/if}
    </th>
  {/foreach}
  <th>&nbsp;</th>
  </thead>

  {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
        <tr id='rowid{counter}' class="{cycle values="odd-row,even-row"}">
          {assign var=cbName value=$row.checkbox}
          <td>{$form.$cbName.html}</td>
          {foreach from=$columnHeaders item=header}
            {assign var=fName value=$header.sort}
            {if $fName eq 'sort_name'}
            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
            {elseif $fName eq 'activity_subject'}
            <td>
                {if $row.case_id }
                    <a href="{crmURL p='civicrm/case/activity/view' q="reset=1&aid=`$row.activity_id`&cid=`$row.contact_id`&caseID=`$row.case_id`"}">
                {else}
                    <a href="{crmURL p='civicrm/contact/view/activity' q="atype=`$row.activity_type_id`&action=view&reset=1&id=`$row.activity_id`&cid=`$row.contact_id`"}">
                {/if}
                {$row.activity_subject}</a>
            </td>
           {elseif ($fName eq 'activity_id') or ($fName eq 'activity_type_id') or ($fName eq 'case_id')}   
           {else}
            <td>{$row.$fName}</td>
            {/if}
          {/foreach}
          <td>{$row.action}</td>
        </tr>
     {/foreach}
</table>
{/strip}

 <script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 </script>

{include file="CRM/common/pager.tpl" location="bottom"}

       </p>

    </fieldset>
    {* END Actions/Results section *}

{/if}

<script type="text/javascript">
    var showBlock = new Array({$showBlock});
    var hideBlock = new Array({$hideBlock});

{* hide and display the appropriate blocks *}
    on_load_init_blocks( showBlock, hideBlock );
</script>

