{if $pager and $pager->_response}
    {if $pager->_response.numPages >= 1}
        <div class="crm-pager">
          <span class="crm-pager-nav">
          {$pager->_response.first}&nbsp;
          {$pager->_response.back}&nbsp;
          {$pager->_response.status}&nbsp;
          {$pager->_response.next}&nbsp;
          {$pager->_response.last}&nbsp;
          </span>
          <span class="element-right">
          {if $location eq 'top'}
            {$pager->_response.titleTop}&nbsp;<input name="{$pager->_response.buttonTop}" value="Go" type="submit"/>
          {else}
            {$pager->_response.titleBottom}&nbsp;<input name="{$pager->_response.buttonBottom}" value="Go" type="submit"/>
          {/if}
          </span>
        </div>
    {/if}
    
    {* Controller for 'Rows Per Page' *}
    {if $location eq 'bottom'}
     <div class="form-item">
        <span class="element-right">
           <label>Rows per page:</label> &nbsp; 
           {$pager->_response.twentyfive}&nbsp; | &nbsp;
           {$pager->_response.fifty}&nbsp; | &nbsp;
           {$pager->_response.onehundred}&nbsp; 
         </span>
     </div>
     <div class="spacer"></div>
    {/if}

{/if}
