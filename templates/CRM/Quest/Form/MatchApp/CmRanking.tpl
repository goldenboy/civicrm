{* Quest Pre-application:  College Match Ranking Information section *}
{include file="CRM/Quest/Form/MatchApp/AppContainer.tpl" context="begin"}

<table cellpadding=0 cellspacing=1 border=1 width="90%" class="app">
<tr>
    <td colspan=2 id="category">{$wizard.currentStepRootTitle}{$wizard.currentStepTitle}</td>
</tr>
<tr>
    <td colspan="2" class="grouplabel">
      The College Match program uses a ranking system for matching students with our partner colleges. Our process consists of two rankings, a preliminary ranking and a final ranking. The preliminary ranking is not binding, the final ranking is binding. With this application you will be asked to enter your preliminary ranking. Two weeks after the application deadline, you will be asked for your final ranking. <br />
      <br />
      You can learn more about the <a href="" target="_blank">College Match Process</u></a>
      <br />
      <br />
      Please enter your preliminary ranking of colleges and universities you are interested in attending. If you are not interested in a college, select 'Not Interested' as the ranking. We recommend you research the colleges before you select a ranking for them. You can learn more about the colleges and universities by clicking on the link next to each college name. <span class="marker" title="This field is required.">*</span>
    </td>
</tr>
<tr>
    <td class="grouplabel"><strong>Colleges</strong></td>
    <td class = "nowrap"><strong>Ranking</strong></td>
</tr>
{section name=rowLoop start=1 loop=15}
      {assign var=i value=$smarty.section.rowLoop.index}
      {assign var=collegeTypes value=$collegeType}
      {assign var=collegeRank value="college_ranking_"|cat:$i}   
      {assign var=urlLink value=$url_link}   
      <tr>
        <td class="grouplabel">{$collegeTypes[$i]}&nbsp;&nbsp;<a href={$urlLink[$i]}>(<u>learn more</u>)</a></td>
        <td class="nowrap">{$form.$collegeRank.html}</td>
      </tr>
{/section} 
</table>

{include file="CRM/Quest/Form/MatchApp/AppContainer.tpl" context="end"}
