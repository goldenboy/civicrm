<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2008
 * $Id$
 *
 */

class CRM_Utils_Mail_Incoming {

    function formatMail( $mail ) {
        $t = '';
        $t .= "From:      ". formatAddress( $mail->from ). "\n";
        $t .= "To:        ". formatAddresses( $mail->to ). "\n";
        $t .= "Cc:        ". formatAddresses( $mail->cc ). "\n";
        $t .= "Bcc:       ". formatAddresses( $mail->bcc ). "\n";
        $t .= 'Date:      '. date( DATE_RFC822, $mail->timestamp ). "\n";
        $t .= 'Subject:   '. $mail->subject . "\n";
        $t .= "MessageId: ". $mail->messageId . "\n";
        $t .= "\n";
        $t .= formatMailPart( $mail->body );
        return $t;
    }

    function formatMailPart( $part ) {

        if ( $part instanceof ezcMail ) {
            return self::formatMail( $part );
        }

        if ( $part instanceof ezcMailText ) {
            return self::formatMailText( $part );
        }

        if ( $part instanceof ezcMailFile ) {
            return self::formatMailFile( $part );
        }

        if ( $part instanceof ezcMailRfc822Digest ) {
            return self::formatMailRfc822Digest( $part );
        }

        if ( $part instanceof ezcMailMultiPart ) {
            return self::formatMailMultipart( $part );
        }

        CRM_Core_Error::fatal( ts( "No clue about the %1",
                                   array( 1 => get_class( $part ) ) ) );
    }

    function formatMailMultipart( $part ) {

        if ( $part instanceof ezcMailMultiPartAlternative ) {
            return self::formatMailMultipartAlternative( $part );
        }

        if ( $part instanceof ezcMailMultiPartDigest ) {
            return self::formatMailMultipartDigest( $part );
        }

        if ( $part instanceof ezcMailMultiPartRelated ) {
            return self::formatMailMultipartRelated( $part );
        }

        if ( $part instanceof ezcMailMultiPartMixed ) {
            return self::formatMailMultipartMixed( $part );
        }

        CRM_Core_Error::fatal( ts( "No clue about the %1",
                                   array( 1 => get_class( $part ) ) ) );
    }

    function formatMailMultipartMixed( $part ) {
        $t = '';
        foreach ( $part->getParts() as $key => $alternativePart ) {
            $t .= formatMailPart( $alternativePart );
        }
        return $t;
    }

    function formatMailMultipartRelated( $part ) {
        $t = '';
        $t .= "-RELATED MAIN PART-\n";
        $t .= formatMailPart( $part->getMainPart() );
        foreach ( $part->getRelatedParts() as $key => $alternativePart ) {
            $t .= "-RELATED PART $key-\n";
            $t .= formatMailPart( $alternativePart );
        }
        $t .= "-RELATED END-\n";
        return $t;
    }

    function formatMailMultipartDigest( $part ) {
        $t = '';
        foreach ( $part->getParts() as $key => $alternativePart ) {
            $t .= "-DIGEST-$key-\n";
            $t .= formatMailPart( $alternativePart );
        }
        $t .= "-DIGEST END---\n";
        return $t;
    }

    function formatMailRfc822Digest( $part ) {
        $t = '';
        $t .= "-DIGEST-ITEM-$key-\n";
        $t .= "Item:\n\n";
        $t .= formatMailpart( $part->mail );
        $t .= "-DIGEST ITEM END-\n";
        return $t;
    }

    function formatMailMultipartAlternative( $part ) {
        $t = '';
        foreach ( $part->getParts() as $key => $alternativePart ) {
            $t .= "-ALTERNATIVE ITEM $key-\n";
            $t .= formatMailPart( $alternativePart );
        }
        $t .= "-ALTERNATIVE END-\n";
        return $t;
    }

    function formatMailText( $part ) {
        $t = '';
        $t .= "\n{$part->text}\n";
        return $t;
    }

    function formatMailFile( $part ) {
        $t = '';
        $t .= "Disposition Type: {$part->dispositionType}\n";
        $t .= "Content Type:     {$part->contentType}\n";
        $t .= "Mime Type:        {$part->mimeType}\n";
        $t .= "Content ID:       {$part->contentId}\n";
        $t .= "Filename:         {$part->fileName}\n";
        $t .= "\n";
        return $t;
    }

    function formatAddresses( $addresses ) {
        $fa = array();
        foreach ( $addresses as $address ) {
            $fa[] = self::formatAddress( $address );
        }
        return implode( ', ', $fa );
    }

    function formatAddress( $address ) {
        $name = '';
        if ( !empty( $address->name ) ) {
            $name = "{$address->name} ";
        }
        return $name . "<{$address->email}>";    
    }

    function &parse( &$message ) {

        require_once 'CRM/Core/Config.php';
        require_once 'api/v2/Activity.php';
        require_once 'api/v2/Contact.php';
        
        $config =& CRM_Core_Config::singleton();

        require_once 'ezc/Base/src/ezc_bootstrap.php';
        require_once 'ezc/autoload/mail_autoload.php';

        // get ready for collecting data about this email
        // and put it in a standardized format
        $params = array( 'is_error' => 0 );

        // explode email to digestable format
        $set = new ezcMailFileSet( array( $message ) );
        $parser = new ezcMailParser();
        $mail = $parser->parseMail( $set );
        
        $params['from'] = array( );
        self::parseAddress( $mail[0]->from, $field, $params['from'] );

        $emailFields = array( 'to', 'cc', 'bcc' );
        foreach ( $emailFields as $field ) {
            $value = $mail[0]->$field;
            self::parseAddresses( $value, $field, $params );
            if ( $params['is_error'] ) {
                return;
            }
        }

        // define other parameters
        $params['subject'] = $mail[0]->subject;
        $params['date']    = date( "YmdHi00",
                                   strtotime( $mail[0]->getHeader( "Date" ) ) );
        $params['body']    = self::formatMailPart( $mail[0]->body );

        return $params;
    }

    function parseAddress( &$address, &$params, &$subParam ) {
        $subParam['email'] = $address->email;
        $subParam['name' ] = $address->name ;

        $subParam['id'   ] = self::getContactID( $subParam['email'],
                                                 $subParam['name' ] );
        if ( empty( $subParam['id'] ) ) {
            $params['is_error'] = 1;
            $params['error_message'] = ts( "Contact with address %1 was not found / created",
                                           array( 1 => $subParam['email'] ) );
        }
    }

    function parseAddresses( &$addresses, $token, &$params ) {
        $params[$token] = array( );
        
        foreach ( $addresses as $address ) {
            $subParam = array( );
            self::parseAddress( $address, $params, $subParam );
            $params[$token][] = $subParam;
        }
    }

    /**
     * retrieve a contact ID and if not present
     * create one with this email
     */
    function getContactID( $email, $name = null, $create = true ) {
        require_once 'CRM/Contact/BAO/Contact.php';
        $dao = CRM_Contact_BAO_Contact::matchContactOnEmail( $email, 'Individual' );
        if ( $dao ) {
            echo "Found: $email, {$dao->contact_id}<p>";
            return $dao->contact_id;
        }

        if ( ! $create ) {
            return null;
        }

        // contact does not exist, lets create it
        $params = array( 'contact_type'   => 'Individual',
                         'email-Primary'  => $email );

        require_once 'CRM/Utils/String.php';
        CRM_Utils_String::extractName( $name, $params );

        CRM_Core_Error::debug( "Creating: ", $params );
        return CRM_Contact_BAO_Contact::createProfileContact( $params,
                                                              CRM_Core_DAO::$_nullArray );
    }

}

