{* this template is used for adding/editing location type  *}
<div class="form-item">
<fieldset><legend>{if $action eq 1}{ts}New Location Type{/ts}{else}{ts}Edit Location Type{/ts}{/if}</legend>
  <dl>
	<dt>{$form.name.label}</dt><dd>{$form.name.html}</dd>
	<dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
    <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
    <dt></dt><dd>{$form.buttons.html}</dd>
  </dl>
</fieldset>
</div>
