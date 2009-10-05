{capture assign=docLink}{docURL page="Tags Admin"}{/capture}

<div id="help">
    {ts 1=$docLink}Tags can be assigned to any contact record, and are a convenient way to find contacts. You can create as many tags as needed to organize and segment your records.{/ts} {$docLink}
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Tag.tpl"}	
{/if}

{if $rows}
<div id="cat">
<p></p>
    <div class="form-item">
        {strip}
        <table id="options" class="display">
	    <thead>
        <tr>
	        <th>{ts}Parent ID{/ts}</th>
	        <th>{ts}Tag{/ts}</th>
            <th id="sortable">{ts}ID{/ts}</th>
	        <th>{ts}Description{/ts}</th>
	        <th></th>
        </tr>
        </thead>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}">
            <td>{$row.parent_id}</td>
            <td>{$row.name}</td>
            <td>{$row.id}</td>	
            <td>{$row.description} </td>
            <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}
        
        {if !($action eq 1 and $action eq 2)}
	    <div class="action-link">
        <a href="{crmURL q="action=add&reset=1"}" id="newTag" class="button"><span>&raquo; {ts}New Tag{/ts}</span></a>
        </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        {capture assign=crmURL}{crmURL p='civicrm/admin/tag' q="action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no Tags present. You can <a href='%1'>add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}

{literal}
<script type="text/javascript">
    cj( function( ) {
        var id = count = 0;
        cj('#options th').each(function(){ if( cj(this).attr('id') == 'sortable') { id = count; } count++; });
        cj('#options').dataTable( {
            "aaSorting": [[ id, "asc" ]],
            "bPaginate": false,
    		"bLengthChange": false,
    		"bFilter": false,
    		"bInfo": false,
    		"bAutoWidth": false,
    		"aoColumns": [
    		            null,
    		            null,
    		            null,
            			{ "bSortable": false },
            			{ "bSortable": false }
            		]
        } );        
    });
</script>
{/literal}