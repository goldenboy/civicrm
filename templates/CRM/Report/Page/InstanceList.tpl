{strip}
        {if $list}
            {foreach from=$list item=rows key=report}
	        <br>
                <div style="cursor:pointer;" onclick="toggle_visibility('{$report}');">
	            <table class="report-layout">
		        <tr>
			    <th>{if $title}{$title}{elseif $report}{$report}{else}Contact{/if} Reports</th>
			</tr>
		    </table>
	        </div>
		<div id="{$report}" style="display:block;">
		    <table class="report">
		        {foreach from=$rows item=row}
	                    <tr >
			        <td width="300"><a href="{$row.url}">&raquo; {$row.title}</a></td>
				<td width="450">{$row.description}</td>
				{if $row.deleteUrl}
	                            <td><a href="{$row.deleteUrl}" onclick="return window.confirm('Are you sure you want Delete this Instance?');">{ts}Delete{/ts}</a></td>
				{/if}
			    </tr>
	        	{/foreach}
                    </table>
                </div>
	    {/foreach}
            {if $reportUrl}
	        <a href="{$reportUrl}" class="button"><span>&raquo; {ts}View all Reports{/ts}</span></a></td>
	    {/if}
        {else}
            <div class="messages status">
            <dl>
                <dt>
                    <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/>
                </dt>
                <dd>
                    {ts 1=$templateUrl}Currently there are no ready made reports available however you could create one with the <a href=%1><strong>template</strong></a> of your own choice.{/ts}
                </dd>
            </dl>
            </div>
        {/if}
{/strip}
{literal}
<script type="text/javascript">
    function toggle_visibility(id) {
	var e = document.getElementById(id);
	if (e.style.display == 'block') {
	    e.style.display = 'none';
	} else {
	    e.style.display = 'block';
	}
    }
</script>
{/literal}