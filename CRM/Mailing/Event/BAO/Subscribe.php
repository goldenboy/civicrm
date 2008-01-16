<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */


require_once 'Mail/mime.php';
require_once 'CRM/Utils/Verp.php';

require_once 'CRM/Mailing/Event/DAO/Subscribe.php';

class CRM_Mailing_Event_BAO_Subscribe extends CRM_Mailing_Event_DAO_Subscribe {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Register a subscription event.  Create a new contact if one does not
     * already exist.
     *
     * @param int $domain_id        The domain id of the new subscription
     * @param int $group_id         The group id to subscribe to
     * @param string $email         The email address of the (new) contact
     * @return int|null $se_id      The id of the subscription event, null on failure
     * @access public
     * @static
     */
    public static function &subscribe($domain_id, $group_id, $email) {
        // CRM-1797 - allow subscription only to public groups
        $params = array('id' => (int) $group_id);
        $defaults = array();
        require_once 'CRM/Contact/BAO/Group.php';
        $bao = CRM_Contact_BAO_Group::retrieve($params, $defaults);
        if (substr($bao->visibility, 0, 6) != 'Public') {
            return null;
        }
        /* First, find out if the contact already exists */  
        $query = "
   SELECT DISTINCT contact_a.id as contact_id 
     FROM civicrm_contact contact_a 
LEFT JOIN civicrm_email      ON contact_a.id = civicrm_email.contact_id
    WHERE LOWER(civicrm_email.email) = %1";

        $params = array( 1 => array( $email, 'String' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        $id = array( );
        // lets just use the first contact id we got
        if ( $dao->fetch( ) ) {
            $contact_id = $dao->contact_id;
        }
        $dao->free( );

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        if ( ! $contact_id ) {
            require_once 'api/v2/Contact.php';
            require_once 'CRM/Core/BAO/LocationType.php';
            /* If the contact does not exist, create one. */
            $formatted = array('contact_type' => 'Individual');
            $locationType = CRM_Core_BAO_LocationType::getDefault( );
            $value = array('email' => $email,
                           'location_type_id' => $locationType->id );
            _civicrm_add_formatted_param($value, $formatted);
            require_once 'api/Contact.php';
            require_once 'CRM/Import/Parser.php';
            $formatted['onDuplicate'] = CRM_Import_Parser::DUPLICATE_SKIP;
            $formatted['fixAddress'] = true;
            $contact =& civicrm_contact_format_create($formatted);
            if (civicrm_error($contact, CRM_Core_Error)) {
                return null;
            }
            $contact_id = $contact['id'];
        } else if ( ! is_numeric( $contact_id ) &&
                    (int ) $contact_id > 0 ) {
            // make sure contact_id is numeric
            return null;
        }

        require_once 'CRM/Core/BAO/Email.php';
        require_once 'CRM/Core/BAO/Location.php';
        require_once 'CRM/Contact/BAO/Contact.php';

        /* Get the primary email id from the contact to use as a hash input */
        $dao =& new CRM_Core_DAO();

        $query = "
SELECT     civicrm_email.id as email_id
  FROM     civicrm_email
     WHERE LOWER(civicrm_email.email) = %1
       AND civicrm_email.contact_id = %2";
        $params = array( 1 => array( $email     , 'String'  ),
                         2 => array( $contact_id, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        if ( ! $dao->fetch( ) ) {
            CRM_Core_Error::fatal( 'Please file an issue with the backtrace' );
            return null;
        }

        $se =& new CRM_Mailing_Event_BAO_Subscribe();
        $se->group_id = $group_id;
        $se->contact_id = $contact_id;
        $se->time_stamp = date('YmdHis');
        $se->hash = sha1("{$group_id}:{$contact_id}:{$dao->email_id}");
        $se->save();
        
        $contacts = array($contact_id);
        require_once 'CRM/Contact/BAO/GroupContact.php'; 
        CRM_Contact_BAO_GroupContact::addContactsToGroup($contacts, $group_id,
                                                             'Email', 'Pending', $se->id);

        $transaction->commit( );
        return $se;
    }

    /**
     * Verify the hash of a subscription event
     * 
     * @param int $contact_id       ID of the contact
     * @param int $subscribe_id     ID of the subscription event
     * @param string $hash          Hash to verify
     *
     * @return object|null          The subscribe event object, or null on failure
     * @access public
     * @static
     */
    public static function &verify($contact_id, $subscribe_id, $hash) {
        $se =& new CRM_Mailing_Event_BAO_Subscribe();
        $se->contact_id = $contact_id;
        $se->id = $subscribe_id;
        $se->hash = $hash;
        if ($se->find(true)) {
            return $se;
        }
        return null;
    }

    /**
     * Ask a contact for subscription confirmation (opt-in)
     *
     * @param string $email         The email address
     * @return void
     * @access public
     */
    public function send_confirm_request($email) {
        $config =& CRM_Core_Config::singleton();
        $this->domain_id = CRM_Core_Config::domainID();

        require_once 'CRM/Core/BAO/Domain.php';
        $domain =& CRM_Core_BAO_Domain::getCurrentDomain();
        
        require_once 'CRM/Utils/Verp.php';
        $confirm = CRM_Utils_Verp::encode( implode( $config->verpSeparator,
                                                    array( 'confirm',
                                                           $this->domain_id,
                                                           $this->contact_id,
                                                           $this->id,
                                                           $this->hash )
                                                    ) . "@{$domain->email_domain}",
                                           $email);
        
        require_once 'CRM/Contact/BAO/Group.php';
        $group =& new CRM_Contact_BAO_Group();
        $group->id = $this->group_id;
        $group->find(true);
        
        require_once 'CRM/Mailing/BAO/Component.php';
        $component =& new CRM_Mailing_BAO_Component();
        $component->domain_id = $domain->id;
        $component->is_default = 1;
        $component->is_active = 1;
        $component->component_type = 'Subscribe';

        $component->find(true);

        $headers = array(
            'Subject'   => $component->subject,
            'From'      => "\"{$domain->email_name}\" <{$domain->email_address}>",
            'To'        => $email,
            'Reply-To'  => $confirm,
            'Return-Path'   => "do-not-reply@{$domain->email_domain}"
        );

        $url = CRM_Utils_System::url( 'civicrm/mailing/confirm',
                                      "reset=1&cid={$this->contact_id}&sid={$this->id}&h={$this->hash}" );

        $html = $component->body_html;

        if ($component->body_text) {
            $text = $component->body_text;
        } else {
            $text = CRM_Utils_String::htmlToText($component->body_html);
        }

        require_once 'CRM/Mailing/BAO/Mailing.php';
        $bao =& new CRM_Mailing_BAO_Mailing();
        $bao->body_text = $text;
        $bao->body_html = $html;
        $tokens = $bao->getTokens();

        require_once 'CRM/Utils/Token.php';
        $html = CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html'] );
        $html = CRM_Utils_Token::replaceSubscribeTokens($html, 
                                                        $group->title,
                                                        $url, true);
        
        $text = CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text'] );
        $text = CRM_Utils_Token::replaceSubscribeTokens($text, 
                                                        $group->title,
                                                        $url, false);
        // render the &amp; entities in text mode, so that the links work
        $text = str_replace('&amp;', '&', $text);

        $message =& new Mail_Mime("\n");
        $message->setHTMLBody($html);
        $message->setTxtBody($text);
        $b = $message->get();
        $h = $message->headers($headers);
        $mailer =& $config->getMailer();

        require_once 'CRM/Mailing/BAO/Mailing.php';
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,
                               array('CRM_Mailing_BAO_Mailing', 'catchSMTP'));
        $mailer->send($email, $h, $b);
        CRM_Core_Error::setCallback();
    }

    /**
     * Get the domain object given a subscribe event
     * 
     * @param int $subscribe_id     ID of the subscribe event
     * @return object $domain       The domain owning the event
     * @access public
     * @static
     */
    public static function &getDomain($subscribe_id) {
        $dao =& new  CRM_Core_Dao();

        $subscribe  = self::getTableName();

        require_once 'CRM/Contact/BAO/Group.php';
        $group      = CRM_Contact_BAO_Group::getTableName();
        
        $dao->query("SELECT     $group.domain_id as domain_id
                        FROM    $group
                    INNER JOIN  $subscribe
                            ON  $subscribe.group_id = $group.id
                        WHERE   $subscribe.id = " .
                        CRM_Utils_Type::escape($subscribe_id, 'Integer'));
        $dao->fetch();
        if (empty($dao->domain_id)) {
            return null;
        }

        require_once 'CRM/Core/BAO/Domain.php';
        return CRM_Core_BAO_Domain::getDomainById($dao->domain_id);
    }
}

?>
