{* Quest college Match application:Forward Application  section *}


{include file="CRM/Quest/Form/MatchApp/AppContainer.tpl" context="begin"}
{strip}
<table cellpadding=0 cellspacing=1 width="90%" class="app">
<tr>
    <td id="category" class="grouplabel"> {$wizard.currentStepRootTitle}{$wizard.currentStepTitle}for Regular Admissions</td>
</tr>

<tr>
    <td class="grouplabel">
        {ts}Most of our partner colleges allow the QuestBridge application to serve as a free application for regular admission to their college. A majority of the QuestBridge applicants go through the College Match Program, although many of the applicants do not get matched. In the event you do not get matched, allowing us to forward your application will make you a candidate for regular admissions at our partner colleges. <br/><br/>{/ts}
        {ts}Select the colleges to which you would like QuestBridge to forward your application for regular admissions. <span class="marker" title="This field is required.">*</span>{/ts} 
    </td>
</tr>

<tr><td class="grouplabel">
    <table cellpadding=0 cellspacing=1  width="90%" class="app">
    {foreach from=$partner item=type key=key}
    {assign var=regular_admission value="regular_addmission_"|cat:$key}
        {if ($key eq 0) or (($key - 1)%2 eq 0) }
            <tr>
        {/if}
            <td class="optionlist">{$form.$regular_admission.html} {$form.$regular_admission.label} &nbsp;<a href={$url_link.$key}>(<u>learn more</u>)</a></td>
        {if ( ($key%2) eq 0 ) }
            </tr>
        {/if}
    {/foreach}

    {if ( ($key%2) neq 0 ) }
        
        {if ( ($key+1)%2 eq 0 ) }
            <td class="optionlist"></td>
        {/if}
        
        </tr>
    {/if}
    </table>
</td></tr>

</table>

<table cellpadding=0 cellspacing=1  width="90%" class="app">
<tr>
    <td id="category" class="grouplabel">{$wizard.currentStepRootTitle}{$wizard.currentStepTitle}for Scholarships</td>
</tr>
<tr>
    <td class="grouplabel">
    QuestBridge is forming partnerships by which select scholarships have the opportunity to receive information about our applicants. Please check any scholarships you want QuestBridge to forward your information to. (You will still need to fill out the scholarship's regular application if you are contacted by the scholarship provider).
    <br/><br/>
    <table cellpadding=0 cellspacing=1  width="90%" class="app">

    {foreach from=$partner_s item=type key=key}
    {assign var=regular_admission_s value="scholarship_addmission_"|cat:$key}
        {if ($key eq 0) or (($key - 1)%2 eq 0) }
            <tr>
        {/if}
            <td class="optionlist">{$form.$regular_admission_s.html} {$form.$regular_admission_s.label} &nbsp;<a href="{$scholarshipUrl_link.$key}">(<u>learn more</u>)</a></td>
        {if ( ($key%2) eq 0 ) }
            </tr>
        {/if}
    {/foreach}
    {if ( ($key%2) neq 0 ) }
        {if ( ($key+1)%2 eq 0 ) }
            <td class="optionlist"></td>
        {/if}
        </tr>
    {/if}
    </table>
   </td>
 </tr> 
</table>
{/strip} 
{include file="CRM/Quest/Form/MatchApp/AppContainer.tpl" context="end"}
