{foreach from=$essays item=essay}
{assign var=name value=$essay.name}
<tr>
    <td class="grouplabel">
        {$form.essay.$name.label}<br />
	{if $name eq 'personal'}
           <br/>{$form.personalStat_quests.label}{$form.personalStat_quests.html}<br/><br/>
	   {$form.upload_photo.label}&nbsp;{$form.upload_photo.html}<br/>
	   {ts}(The file should be of type GIF or JPEG. The file size should be at most 2MB.){/ts}<br/><br/>
	{/if }
        {$form.essay.$name.html} &nbsp;<br /><br />
        {$form.word_count.$name.label} &nbsp;&nbsp;{$form.word_count.$name.html}
    </td> 
</tr>
{/foreach}
</table>

{include file="CRM/Quest/Form/MatchApp/AppContainer.tpl" context="end"}

{edit}
{literal}
    <script type="text/javascript">
        function countit(essay_id,wc){
            //alert("ID : " + essay_id + " Count : " + wc); 
            var text_area     = document.getElementById("essay_" + essay_id);
            var count_element = document.getElementById("word_count_" + essay_id);
            var count=0;
            var text_area_value = text_area.value; 
            var essay = text_area_value.replace('\n', ' ');
            words = essay.split(' ');
            for (z=0; z<words.length; z++){
                if (words[z].length>0){
                    count++;
                }
            }
            count_element.value     = count;
            if (count>wc) {
                text_area.value     = text_area_value;
                count_element.value = count;
                alert("You have reached the "+ wc +" character limit.");
            }
            
        }
   </script>  
{/literal}
{/edit}

