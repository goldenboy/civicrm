{literal}
<script type="text/javascript">

function buildCustomData( type, subName, subType, cgCount, groupID, isMultiple )
{
	var dataUrl = {/literal}"{crmURL p=$urlPath h=0 q='snippet=4&type='}"{literal} + type; 

	if ( subType ) {
		// special case to handle relationship custom data
	    if ( type == 'Relationship' ) {
			subType = subType.replace( '_a_b', '' );
			subType = subType.replace( '_b_a', '' );
	    }
	    
		dataUrl = dataUrl + '&subType=' + subType;
	}

	if ( subName ) {
		dataUrl = dataUrl + '&subName=' + subName;
		cj('#customData' + subName ).show();
	} else {
		cj('#customData').show();		
	}
	
	{/literal}
		{if $urlPathVar}
			dataUrl = dataUrl + '&' + '{$urlPathVar}'
		{/if}
		{if $groupID}
			dataUrl = dataUrl + '&groupID=' + '{$groupID}'
		{/if}
		{if $qfKey}
			dataUrl = dataUrl + '&qfKey=' + '{$qfKey}'
		{/if}
		{if $entityID}
			dataUrl = dataUrl + '&entityID=' + '{$entityID}'
		{/if}
	{literal}

	if ( !cgCount ) {
		cgCount = 1;
		var prevCount = 1;		
	} else if ( cgCount >= 1 ) {
		var prevCount = cgCount;	
		cgCount++;
	}

	dataUrl = dataUrl + '&cgcount=' + cgCount;

	if ( isMultiple ) {
		cj("#custom_group_" + groupID + "_" + prevCount ).load( dataUrl);
		cj("#add-more-link-"+prevCount).hide();
	} else {
		if ( subName ) {		
			cj('#customData' + subName ).load( dataUrl);
		} else {
			cj('#customData').load( dataUrl);
		}		
	}
}

function createMultipleValues( type, subName, subType, cgcount, groupID )
{
	buildCustomData( type, subName, subType, cgcount, groupID, true );
}
</script>
{/literal}
