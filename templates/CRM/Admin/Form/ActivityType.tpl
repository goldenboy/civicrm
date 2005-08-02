{* this template is used for adding/editing activity type  *}
<div class="form-item">
<fieldset><legend>{if $action eq 1}{ts}New Activity Type{/ts}{else}{ts}Edit Activity Type{/ts}{/if}</legend>
  <dl>
	<dt>{$form.name.label}</dt><dd>{$form.name.html}</dd>
	<dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
        <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>
	<dt>{$form.is_default.label}</dt><dd>{$form.is_default.html}</dd>
        <dt></dt><dd>{$form.buttons.html}</dd>
  </dl>
</fieldset>
</div>
