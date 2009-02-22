<?php

defined('_JEXEC') or die('No direct access allowed'); 

function com_install() {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'configure.php';
    
    $liveSite = substr_replace(JURI::root(), '', -1, 1);
    $configTaskUrl = $liveSite . '/administrator/index2.php?option=com_civicrm&task=civicrm/admin/configtask&reset=1';
    // Show installation result to user
?>

<center>
  <table width="100%" border="0">
    <tr>
      <td><strong>Files uploaded <font color="green">succesfully</font></strong>. Use the <a href="<?php echo $configTaskUrl?>"><strong>Configuration Checklist</strong></a> to review and configure settings for your new site.<br/>
      </td>
    </tr>
    <tr>
      <td><p>If this is a <strong>new installation</strong> of CiviCRM, please review the <a target="_blank" href="http://wiki.civicrm.org/confluence/display/CRMDOC/Install+2.2+for+Joomla">online installation documentation</a>. </p>
        <p>If you are <strong>upgrading</strong> an existing installation of CiviCRM, please review the <a target="_blank" href="http://wiki.civicrm.org/confluence/display/CRMDOC/Upgrade+Joomla+Sites+to+2.2">upgrade documentation</a>. </p>
        <p>CiviCRM includes the ability to expose Profile forms and listings, as well as Online Contribution and Event Registration pages, to users and visitors of the 'front-end' of your Joomla! site.
           Review <a target="_blank" href="http://wiki.civicrm.org/confluence//x/6Bk">this document to learn about configuring Profiles as custom front-end forms and search pages</a>. Review
           <a target="_blank" href="http://wiki.civicrm.org/confluence/x/QBo">this document to learn about configuring Online Contribution Pages</a>, and review <a target="_blank" href="http://wiki.civicrm.org/confluence/x/ezg">this document to learn about Event Registration pages</a>.</p></td>
    </tr>
  </table>
</center>

<?php
}

