{if $title}
    <h3 class="head"> 
	<span class="ui-icon ui-icon-triangle-1-e"></span><a href="#">{$title}</a>
    </h3>
    <div id="tagGroup" class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom">
    <fieldset>
{/if}
    <table class="form-layout-compressed" style="width:98%">
	<tr>
	    {foreach key=key item=item from=$tagGroup}
		{* $type assigned from dynamic.tpl *}
		{if !$type || $type eq $key }
		<td width={cycle name=tdWidth values="70%","30%"}><span class="label">{if $title}{$form.$key.label}{/if}</span>
		    <table>
			{foreach key=k item=it from=$form.$key}
			    {if $k|is_numeric}
				<tr class={cycle values="'odd-row','even-row'" name=$key}>
				    <td>
					<strong>{$it.html}</strong><br/>
					{if $item.$k.description}
					    <div style="font-size:10px;padding-left:20px;">
						{$item.$k.description}
					    </div>
					{/if}
				    </td>
				</tr>
			    {/if}
			{/foreach}   
		    </table>
		</td>
		{/if}
	    {/foreach}
	</tr>
    </table>   
{if $title}
    </fieldset>
    </div>
{/if}