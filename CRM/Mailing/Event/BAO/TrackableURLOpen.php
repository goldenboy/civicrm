<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */


require_once 'CRM/Mailing/Event/DAO/TrackableURLOpen.php';

class CRM_Mailing_Event_BAO_TrackableURLOpen extends CRM_Mailing_Event_DAO_TrackableURLOpen {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Track a click-through and return the URL to redirect.  If the numbers
     * don't match up, return the base url.
     *
     * @param int $queue_id     The Queue Event ID of the clicker
     * @param int $url_id       The ID of the trackable URL
     * @return string $url      The redirection url, or base url on failure.
     * @access public
     * @static
     */
    public static function track($queue_id, $url_id) {
        require_once 'CRM/Mailing/BAO/TrackableURL.php';
        require_once 'CRM/Mailing/Event/BAO/Queue.php';
        require_once 'CRM/Mailing/BAO/Job.php';

        $search =& new CRM_Mailing_BAO_TrackableURL();
        
        /* To find the url, we also join on the queue and job tables.  This
         * prevents foreign key violations. */
       
        $job = CRM_Mailing_BAO_Job::getTableName();
        $eq = CRM_Mailing_Event_BAO_Queue::getTableName();
        $turl = CRM_Mailing_BAO_TrackableURL::getTableName();
        
        $search->query("SELECT $turl.url as url from $turl
                    INNER JOIN $job ON $turl.mailing_id = $job.mailing_id
                    INNER JOIN $eq ON $job.id = $eq.job_id
                    WHERE $eq.id = " 
                        . CRM_Utils_Type::escape($queue_id, 'Integer') 
                . " AND $turl.id = " 
                        . CRM_Utils_Type::escape($url_id, 'Integer')
        );
        
        if (! $search->fetch()) {
            /* Whoops, error, don't track it.  Return the base url. */
            return CRM_Utils_System::baseURL();
        }

        $open =& new CRM_Mailing_Event_BAO_TrackableURLOpen();
        $open->event_queue_id = $queue_id;
        $open->trackable_url_id = $url_id;
        $open->time_stamp = date('YmdHis');
        $open->save();

        return $search->url;
    }


  /**
     * Get row count for the event selector
     *
     * @param int $mailing_id       ID of the mailing
     * @param int $job_id           Optional ID of a job to filter on
     * @param boolean $is_distinct  Group by queue ID?
     * @param int $url_id           Optional ID of a url to filter on
     * @return int                  Number of rows in result set
     * @access public
     * @static
     */
    public static function getTotalCount($mailing_id, $job_id = null,
                                        $is_distinct = false, $url_id = null) {
        $dao =& new CRM_Core_DAO();
        
        $click      = self::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();

        $query = "
            SELECT      COUNT($click.id) as opened
            FROM        $click
            INNER JOIN  $queue
                    ON  $click.event_queue_id = $queue.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
                    AND $job.is_test = 0
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');

        if (!empty($job_id)) {
            $query  .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }
        
        if (!empty($url_id)) {
            $query  .= " AND $click.trackable_url_id = " 
                    . CRM_Utils_Type::escape($url_id, 'Integer');
        }
        
        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        if ( $dao->fetch() ) {
            return $dao->opened;
        }
        
        return null;
    }



    /**
     * Get rows for the event browser
     *
     * @param int $mailing_id       ID of the mailing
     * @param int $job_id           optional ID of the job
     * @param boolean $is_distinct  Group by queue id?
     * @param int $url_id           optional ID of a trackable URL to filter on
     * @param int $offset           Offset
     * @param int $rowCount         Number of rows
     * @param array $sort           sort array
     * @return array                Result set
     * @access public
     * @static
     */
    public static function &getRows($mailing_id, $job_id = null, 
        $is_distinct = false, $url_id, 
        $offset = null, $rowCount = null, $sort = null) {
        
        $dao =& new CRM_Core_Dao();
        
        $click      = self::getTableName();
        $url        = CRM_Mailing_BAO_TrackableURL::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $contact    = CRM_Contact_BAO_Contact::getTableName();
        $email      = CRM_Core_BAO_Email::getTableName();

        $query =    "
            SELECT      $contact.display_name as display_name,
                        $contact.id as contact_id,
                        $email.email as email,
                        $click.time_stamp as date,
                        $url.url as url
            FROM        $contact
            INNER JOIN  $queue
                    ON  $queue.contact_id = $contact.id
            INNER JOIN  $email
                    ON  $queue.email_id = $email.id
            INNER JOIN  $click
                    ON  $click.event_queue_id = $queue.id
            INNER JOIN  $url
                    ON  $click.trackable_url_id = $url.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
                    AND $job.is_test = 0
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');
    
        if (!empty($job_id)) {
            $query .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }

        if (!empty($url_id)) {
            $query .= " AND $url.id = " 
                    . CRM_Utils_Type::escape($url_id, 'Integer');
        }

        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        $query .= " ORDER BY $contact.sort_name, $url.id, $click.time_stamp DESC ";

        if ($offset) {
            $query .= ' LIMIT ' 
                    . CRM_Utils_Type::escape($offset, 'Integer') . ', ' 
                    . CRM_Utils_Type::escape($rowCount, 'Integer');
        }

        $dao->query($query);
        
        $results = array();

        while ($dao->fetch()) {
            $url = CRM_Utils_System::url('civicrm/contact/view',
                                "reset=1&cid={$dao->contact_id}");
            $results[] = array(
                'name'      => "<a href=\"$url\">{$dao->display_name}</a>",
                'email'     => $dao->email,
                'url'       => $dao->url,
                'date'      => CRM_Utils_Date::customFormat($dao->date)
            );
        }
        return $results;
    }






}


