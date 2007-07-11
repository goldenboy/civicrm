<p>

{if $rows } 
<div class="form-item">
     <span class="element-right">{$form.buttons.html}</span>
</div>
<div class="spacer"></div>
<br />
<p>
<table>
  <tr class="columnheader">
    <th>{ts}Name{/ts}</th>
    <th>{ts}Status{/ts}</th>
    <th>{ts}Type{/ts}</th>
    <th>{ts}Total Amount{/ts}</th>
    <th>{ts}Application Recieved Date{/ts}</th>
  </tr>
{foreach from=$rows item=row}
    <tr class="{cycle values="odd-row,even-row"}">
        <td>{$row.sort_name}</td>
        <td>{$row.grant_status}</td>  
        <td>{$row.grant_type}</td> 
        <td>{$row.grant_amount_total|crmMoney}</td>
           
        <td>{$row.grant_application_received_date |truncate:10:''|crmDate}
       
    </tr>
{/foreach}
</table>

<div class="form-item">
     <span class="element-right">{$form.buttons.html}</span>
</div>

{else}
   <div class="messages status">
    <dl>
    <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
    <dd>
        {ts}There are no records selected for Print.{/ts}
    </dd>
    </dl>
   </div>
{/if}
