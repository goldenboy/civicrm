{* this template is used for adding/editing Contact Type  *}
<div class="form-item">
<fieldset><legend>{if $action eq 1}{ts}New Contact Type{/ts}{elseif $action eq 2}{ts}Edit Contact Type{/ts}{else}{ts}Delete Contact Type{/ts}{/if}</legend>
{if $action eq 8}
  <div class="messages status">
    <dl>
      <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
      <dd>    
        {ts}WARNING: {ts}This action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}{/ts}
      </dd>
    </dl>
 </div>
{else}
  <dl>
    <dt>{$form.label.label}</dt><dd>{$form.label.html}</dd>
    <dt>{$form.parent_id.label}</dt><dd>&nbsp;{$form.parent_id.html}</dd>
    <dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
    <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
  </dl>
{/if}
  <dl> 
    <dt></dt><dd>{$form.buttons.html}</dd>
  </dl> 
</fieldset>
</div>
