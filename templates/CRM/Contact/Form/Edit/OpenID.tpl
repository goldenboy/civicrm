{* tpl for building IM related fields *}
{if !$addBlock}
<tr>
    <td>{ts}Open ID{/ts}
         &nbsp;&nbsp;<a href="#" title={ts}Add{/ts} onClick="buildAdditionalBlocks( 'OpenID', '{$className}');return false;">add</a>
    </td>
	<td align="center" colspan="2">
		{if $config->userFramework eq "Standalone"}{ts}Allowed to Login?{/ts}{/if}
	</td>
    <td id="OpenID-Primary" class="hiddenElement">{ts}Primary?{/ts}</td>
</tr>
{/if}

<tr id="OpenID_Block_{$blockId}">
     <td>{$form.openid.$blockId.openid.html|crmReplace:class:twenty}&nbsp;{$form.openid.$blockId.location_type_id.html}</td>
     <td align="center" id="OpenID-Login-html" colspan="2">
		{if $config->userFramework eq "Standalone"}{$form.openid.$blockId.allowed_to_login.html}{/if}
	 </td>
	 <td align="center" id="OpenID-Primary-html" {if $blockId eq 1}class="hiddenElement"{/if}>{$form.openid.$blockId.is_primary.1.html}</td>
   {if $blockId gt 1}
    <td><a href="#" title="{ts}Delete OpenID Block{/ts}" onClick="removeBlock('OpenID','{$blockId}'); return false;">{ts}delete{/ts}</a></td>
   {/if}
</tr>