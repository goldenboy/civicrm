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

require_once 'Mail/mime.php';
require_once 'CRM/Core/DAO/MessageTemplates.php';


class CRM_Core_BAO_MessageTemplates extends CRM_Core_DAO_MessageTemplates 
{
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_MessageTemplates object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $messageTemplates =& new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        if ( $messageTemplates->find( true ) ) {
            CRM_Core_DAO::storeValues( $messageTemplates, $defaults );
            return $messageTemplates;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_MessageTemplates', $id, 'is_active', $is_active );
    }

    /**
     * function to add the Message Templates
     *
     * @param array $params reference array contains the values submitted by the form
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( &$params ) 
    {
        $params['is_active']            =  CRM_Utils_Array::value( 'is_active', $params, false );

        $messageTemplates               =& new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        
        $messageTemplates->save( );
        return $messageTemplates;
    }

    /**
     * function to delete the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function del( $messageTemplatesID ) 
    {
        // make sure messageTemplatesID is an integer
        if ( ! CRM_Utils_Rule::positiveInteger( $messageTemplatesID ) ) {
            CRM_Core_Error::fatal( ts( 'Invalid Message template' ) );
        }
        
        // set membership_type to null
        $query = "UPDATE civicrm_membership_type
                  SET renewal_msg_id = NULL
                  WHERE renewal_msg_id = %1";
        $params = array( 1 => array( $messageTemplatesID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $query = "UPDATE civicrm_mailing
                  SET msg_template_id = NULL
                  WHERE msg_template_id = %1";
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $messageTemplates =& new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplatesID;
        $messageTemplates->delete();
        CRM_Core_Session::setStatus( ts('Selected message templates has been deleted.') );
    }
    
    /**
     * function to get the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function getMessageTemplates() {
        $msgTpls =array();

        $messageTemplates =& new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->is_active = 1;
        $messageTemplates->find();
        while ( $messageTemplates->fetch() ) {
            $msgTpls[$messageTemplates->id] = $messageTemplates->msg_title;
        }
        return $msgTpls;
    }

    static function sendReminder( $contactId, $email, $messageTemplateID ,$from) {
        require_once "CRM/Core/BAO/Domain.php";
        require_once "CRM/Utils/String.php";
        require_once "CRM/Utils/Token.php";

        $messageTemplates =& new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplateID;

        $domain = CRM_Core_BAO_Domain::getDomain( );
        $result = null;

        if ( $messageTemplates->find(true) ) {
            $body_text = $messageTemplates->msg_text;
            $body_html = $messageTemplates->msg_html;
            $body_subject = $messageTemplates->msg_subject;
            if (!$body_text) {
                $body_text = CRM_Utils_String::htmlToText($body_html);
            }
            
            $params  = array( 'contact_id' => $contactId );
            require_once 'api/v2/Contact.php';
            $contact =& civicrm_contact_get( $params );

            //CRM-4524
            $contact = reset( $contact );
            
            if ( !$contact || is_a( $contact, 'CRM_Core_Error' ) ) {
                return null;
            }
            
            $type = array('html', 'text');
            
            foreach( $type as $key => $value ) {
                require_once 'CRM/Mailing/BAO/Mailing.php';
                $dummy_mail = new CRM_Mailing_BAO_Mailing();
                $bodyType = "body_{$value}";
                $dummy_mail->$bodyType = $$bodyType;
                $tokens = $dummy_mail->getTokens();
                
                if ( $$bodyType ) {
                    $$bodyType = CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, true, $tokens[$value] );
                    $$bodyType = CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, false, $tokens[$value] );
                }
            }
            $html = $body_html;
            $text = $body_text;
            
            $message =& new Mail_Mime("\n");
            
            /* Do contact-specific token replacement in text mode, and add to the
             * message if necessary */
            if ( !$html || $contact['preferred_mail_format'] == 'Text' ||
                 $contact['preferred_mail_format'] == 'Both') 
                {
                    // render the &amp; entities in text mode, so that the links work
                    $text = str_replace('&amp;', '&', $text);
                    $message->setTxtBody($text);
                    
                    unset( $text );
                }
            
            if ($html && ( $contact['preferred_mail_format'] == 'HTML' ||
                           $contact['preferred_mail_format'] == 'Both'))
                {
                    $message->setHTMLBody($html);
                    
                    unset( $html );
                }
            $recipient = "\"{$contact['display_name']}\" <$email>";
            
            $matches = array();
            preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                            $body_subject,
                            $matches,
                            PREG_PATTERN_ORDER);
            
            $subjectToken = null;
            if ( $matches[1] ) {
                foreach ( $matches[1] as $token ) {
                    list($type,$name) = split( '\.', $token, 2 );
                    if ( $name ) {
                        if ( ! isset( $subjectToken['contact'] ) ) {
                            $subjectToken['contact'] = array( );
                        }
                        $subjectToken['contact'][] = $name;
                    }
                }
            }
            
            $messageSubject = CRM_Utils_Token::replaceContactTokens($body_subject, $contact, false, $subjectToken);
            $headers = array(
                             'From'      => $from,
                             'Subject'   => $messageSubject,
                             );
            $headers['To'] = $recipient;
            
            $mailMimeParams = array(
                                    'text_encoding' => '8bit',
                                    'html_encoding' => '8bit',
                                    'head_charset'  => 'utf-8',
                                    'text_charset'  => 'utf-8',
                                    'html_charset'  => 'utf-8',
                                    );
            $message->get($mailMimeParams);
            $message->headers($headers);

            $config =& CRM_Core_Config::singleton();
            $mailer =& $config->getMailer();
            
            $body = $message->get();
            $headers = $message->headers();
            
            CRM_Core_Error::ignoreException( );
            $result = $mailer->send($recipient, $headers, $body);
            CRM_Core_Error::setCallback();
        }
        
        return $result;
    }

    /**
     * Revert a message template to its default subject+text+HTML state
     *
     * @param integer id  id of the template
     *
     * @return void
     */
    static function revert($id)
    {
        $diverted = new self;
        $diverted->id = (int) $id;
        $diverted->find(1);

        if ($diverted->N != 1) {
            CRM_Core_Error::fatal(ts('Did not find a message template with id of %1.', array(1 => $id)));
        }

        $orig = new self;
        $orig->workflow_id = $diverted->workflow_id;
        $orig->is_reserved = 1;
        $orig->find(1);

        if ($orig->N != 1) {
            CRM_Core_Error::fatal(ts('Message template with id of %1 does not have a default to revert to.', array(1 => $id)));
        }

        $diverted->msg_subject = $orig->msg_subject;
        $diverted->msg_text    = $orig->msg_text;
        $diverted->msg_html    = $orig->msg_html;
        $diverted->save();
    }

    /**
     * Send an email from the specified template based on an array of params
     *
     * @param array $params  a string-keyed array of function params, see function body for details
     *
     * @return array  of four parameters: a boolean whether the email was sent, and the subject, text and HTML templates
     */
    static function sendTemplate($params)
    {
        $defaults = array(
            'groupName'   => null,    // option group name of the template
            'valueName'   => null,    // option value name of the template
            'contactId'   => null,    // contact id if contact tokens are to be replaced
            'tplParams'   => array(), // additional template params (other than the ones already set in the template singleton)
            'from'        => null,    // the From: header
            'toName'      => null,    // the recipient’s name
            'toEmail'     => null,    // the recipient’s email - mail is sent only if set
            'cc'          => null,    // the Cc: header
            'bcc'         => null,    // the Bcc: header
            'replyTo'     => null,    // the Reply-To: header
            'attachments' => null,    // email attachments
        );
        $params = array_merge($defaults, $params);

        if (!$params['groupName'] or !$params['valueName']) {
            CRM_Core_Error::fatal(ts("Message template's option group and/or option value missing."));
        }

        // fetch the three elements from the db based on option_group and option_value names
        $query = 'SELECT msg_subject subject, msg_text text, msg_html html
                  FROM civicrm_msg_template mt
                  JOIN civicrm_option_value ov ON workflow_id = ov.id
                  JOIN civicrm_option_group og ON ov.option_group_id = og.id
                  WHERE og.name = %1 AND ov.name = %2 AND mt.is_default = 1';
        $sqlParams = array(1 => array($params['groupName'], 'String'), 2 => array($params['valueName'], 'String'));
        $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);
        $dao->fetch();

        if (!$dao->N) {
            CRM_Core_Error::fatal(ts('No such message template: option group %1, option value %2.', array(1 => $params['groupName'], 2 => $params['valueName'])));
        }

        // replace tokens in the three elements
        require_once 'CRM/Utils/Token.php';
        require_once 'CRM/Core/BAO/Domain.php';
        require_once 'api/v2/Contact.php';
        require_once 'CRM/Mailing/BAO/Mailing.php';

        $domain = CRM_Core_BAO_Domain::getDomain();
        if ($params['contactId']) {
            $contactParams = array('contact_id' => $params['contactId']);
            $contact =& civicrm_contact_get($contactParams);
        }

        // replace tokens in subject as if it was the text body
        foreach(array('subject' => 'text', 'text' => 'text', 'html' => 'html') as $type => $tokenType) {
            if (!$dao->$type) continue; // skip all of the below if the given part is missing
            $bodyType = "body_$tokenType";
            $mailing = new CRM_Mailing_BAO_Mailing;
            $mailing->$bodyType = $dao->$type;
            $tokens = $mailing->getTokens();
            $dao->$type = CRM_Utils_Token::replaceDomainTokens($dao->$type, $domain, true, $tokens[$tokenType]);
            if ($params['contactId']) {
                $dao->$type = CRM_Utils_Token::replaceContactTokens($dao->$type, $contact, false, $tokens[$tokenType]);
            }
        }

        // parse the three elements with Smarty
        require_once 'CRM/Core/Smarty/resources/String.php';
        civicrm_smarty_register_string_resource();
        $smarty =& CRM_Core_Smarty::singleton();
        // FIXME: we should clear the template variables, but this would break 
        // way too much existing code which shares the singleton Smarty object 
        // for both web and email templates; clearing assigns here would mean 
        // things like CRM_Event_BAO_Event::buildCustomDisplay() would need to 
        // set template variables *and* set array keys for $tplParams
        // $smarty->clear_all_assign();
        foreach ($params['tplParams'] as $name => $value) {
            $smarty->assign($name, $value);
        }
        foreach (array('subject', 'text', 'html') as $elem) {
            $dao->$elem = $smarty->fetch("string:{$dao->$elem}");
        }

        // in most cases leading/trailing whitespace in the subject is unwanted
        $dao->subject = trim($dao->subject);

        // send the template
        $sent = false;
        if ($params['toEmail']) {
            require_once 'CRM/Utils/Mail.php';
            $sent = CRM_Utils_Mail::send($params['from'], $params['toName'], $params['toEmail'], $dao->subject, $dao->text, $params['cc'], $params['bcc'], $params['replyTo'], $dao->html, $params['attachments']);
        }

        return array($sent, $dao->subject, $dao->text, $dao->html);
    }
}
