  -- CRM-4932
ALTER TABLE civicrm_contact MODIFY COLUMN job_title varchar(255);

-- CRM-4906
{if $multilingual}
  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD street_address_{$locale} VARCHAR(96);
    UPDATE civicrm_address SET street_address_{$locale} = street_address;
  {/foreach}
  ALTER TABLE civicrm_address DROP street_address;

  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD supplemental_address_1_{$locale} VARCHAR(96);
    UPDATE civicrm_address SET supplemental_address_1_{$locale} = supplemental_address_1;
  {/foreach}
  ALTER TABLE civicrm_address DROP supplemental_address_1;

  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD supplemental_address_2_{$locale} VARCHAR(96);
    UPDATE civicrm_address SET supplemental_address_2_{$locale} = supplemental_address_2;
  {/foreach}
  ALTER TABLE civicrm_address DROP supplemental_address_2;

  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD supplemental_address_3_{$locale} VARCHAR(96);
    UPDATE civicrm_address SET supplemental_address_3_{$locale} = supplemental_address_3;
  {/foreach}
  ALTER TABLE civicrm_address DROP supplemental_address_3;

  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD city_{$locale} VARCHAR(64);
    UPDATE civicrm_address SET city_{$locale} = city;
  {/foreach}
  ALTER TABLE civicrm_address DROP city;

  {foreach from=$locales item=locale}
    ALTER TABLE civicrm_address ADD name_{$locale} VARCHAR(255);
    UPDATE civicrm_address SET name_{$locale} = name;
  {/foreach}
  ALTER TABLE civicrm_address DROP name;
{/if}