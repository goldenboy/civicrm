{* this template is used for adding/editing/deleting premium  *}
<div class="form-item">
<fieldset><legend>{if $action eq 1}{ts}New Premium {/ts}{elseif $action eq 2}{ts}Edit Preimum{/ts}{else}{ts}Delete Contribution Type{/ts}{/if}</legend>
  
   {if $action eq 8}
      <div class="messages status">
        <dl>
          <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"></dt>
          <dd>    
          {ts}Are you sure you want to delete this premium. This action cannot be undone. This will also remove the premium from any contribution pages that currently include it.?{/ts}
          </dd>
       </dl>
      </div>
     {else}
      <dl>
 	<dt>{$form.name.label}</dt><dd>{$form.name.html}</dd>
    	<dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
    	<dt>{$form.image.label}</dt><dd>{$form.image.image.html}&nbsp;&nbsp;{$form.imageFile.html}</dd>
	<dt></dt><dd>{$form.image.thumbnail.html}</dd>
	<dt></dt><dd>{$form.image.defalut.html}</dd>
	<dt></dt><dd>{$form.image.noImage.html}</dd>
	<dt>{$form.price.label}</dt><dd>{$form.price.html}</dd>
	<dt>&nbsp;</dt><dd class="description">{ts}Sell price / market value for premiums. For tax-deductible contributions, this will be stored as non_deductible_amount in the contribution record.{/ts}</dt>
	<dt>{$form.cost.label}</dt><dd>{$form.cost.html}</dd>
	<dt>&nbsp;</dt><dd class="description">{ts}Actual cost of this product. Useful to determine net return from sale or using this as an incentive.{/ts}</dt>
	<dt>{$form.min_contribution.label}</dt><dd>{$form.min_contribution.html}</dd>
	<dt>&nbsp;</dt><dd class="description">{ts}Minimum contribution required to be eligible to select this premium.{/ts}</dt>
	<dt>{$form.option.label}</dt><dd>{$form.option.html}</dd>
	<dt>&nbsp;</dt><dd class="description">{ts}Comma-delimited list of color, size, etc. options for the product.{/ts}</dt>
        <dt>{$form.is_active.label}</dt><dd>{$form.is_active.html}</dd>

	<div id="time-delimited[show]" class="data-group">
		    <a href="#" onclick="hide('time-delimited[show]'); show('time-delimited'); return false;">&raquo; {ts}For Subscriptions, Memberships, Services{/ts}</a>
	</div>	

	<div id="time-delimited" class="data-group">

	 <a href="#" onclick="hide('time-delimited'); show('time-delimited[show]'); return false;">&raquo; {ts}For Subscriptions, Memberships, Services{/ts}</a>
	
	<dt>{$form.period_type.label}</dt><dd>{$form.period_type.html}</dd>
	<dt>&nbsp;</dt><dd class="description">{ts}Rolling means we set start/end based on current day, Fixed means we set start/end for current year or month(e.g. 1 year + fixed -> we would set start/end for 1/1/06 thru 12/31/06 for any premium chosen in 2006) {/ts}</dt>
 
	<dt>{$form.fixed_period_start_day.label}</dt><dd>{$form.fixed_period_start_day.html}</dd>
        <dt>&nbsp;</dt><dd class="description">{ts}Month and day (MMDD) that fixed period type subscription or membership starts.{/ts}</dt>

	<dt>{$form.duration_unit.label}</dt><dd>{$form.duration_unit.html}</dd>
	
	<dt>{$form.duration_interval.label}</dt><dd>{$form.duration_interval.html}</dd>
        <dt>&nbsp;</dt><dd class="description">{ts}Number of units for total duration of subscription, service, membership (e.g. 12 Months).{/ts}</dt>

	<dt>{$form.frequency_unit.label}</dt><dd>{$form.frequency_unit.html}</dd>
        <dt>&nbsp;</dt><dd class="description">{ts}Frequency unit and interval allow option to store actual delivery frequency for a subscription or service.{/ts}</dt>

	<dt>{$form.frequency_interval.label}</dt><dd>{$form.frequency_interval.html}</dd>
        <dt>&nbsp;</dt><dd class="description">{ts}Number of units for delivery frequency of subscription, service, membership (e.g. every 3 Months).{/ts}</dt>

	</div>
      </dl> 
     {/if}
    <dl>   
      <dt></dt><dd>{$form.buttons.html}</dd>
    </dl>
</fieldset>
</div>

<script type="text/javascript">
hide('time-delimited');
</script>
