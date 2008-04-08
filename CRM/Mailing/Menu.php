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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

class CRM_Mailing_Menu {

    static function permissioned( ) {
        $items = array(
                       'civicrm/mailing' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('CiviMail'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::NORMAL_ITEM,  
                             'weight'  => 600,
                             ),
                       );
        return $items;
    }

    static function &main( ) {
        $items = array(
                       'civicrm/admin/mail' => 
                       array(
                             'title'   => ts('Mailer Settings'),
                             'desc'    => ts('Configure spool period, throttling and other mailer settings.'), 
                             'query'  => 'reset=1',
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::LOCAL_TASK,
                             'adminGroup' => 'CiviMail',
                             'icon'    => 'admin/small/07.png',
                             'weight'  => 400
                             ),

                       'civicrm/admin/component' => 
                       array(
                             'title'   => ts('Headers, Footers, and Automated Messages'),
                             'desc'    => ts('Configure the header and footer used for mailings. Customize the content of automated Subscribe, Unsubscribe, Resubscribe and Opt-out messages.'), 
                             'query'   => 'reset=1',
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::LOCAL_TASK,  
                             'adminGroup' => 'CiviMail',
                             'icon'    => 'admin/small/Profile.png',
                             'weight'  => 410,
                             ),

                       'civicrm/mailing/send' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Send Mailing'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::NORMAL_ITEM,  
                             'weight'  => 610,
                             ),
                       
                       'civicrm/mailing/browse' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts( 'Browse Sent Mailings' ),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail' ),
                             'type'    => CRM_Core_Menu::CALLBACK, 
                             'crm_type' => CRM_Core_Menu::NORMAL_ITEM,  
                             'weight'  => 620, 
                             ),
                       
                       'civicrm/mailing/component' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Headers, Footers, and Automated Messages'),
                             'access_arguments'  => CRM_Core_Permission::check( 'administer CiviCRM' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::NORMAL_ITEM,  
                             'weight'  => 630,
                             ),

                       'civicrm/mailing/unsubscribe' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Unsubscribe'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail subscribe/unsubscribe pages' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::CALLBACK,
                             'weight'  => 640,
                             ),
                       
                       'civicrm/mailing/resubscribe' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Resubscribe'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail subscribe/unsubscribe pages' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::CALLBACK,
                             'weight'  => 645,
                             ),

                       'civicrm/mailing/optout' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Optout'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail subscribe/unsubscribe pages' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::CALLBACK,
                             'weight'  => 650,
                             ),

                       'civicrm/mailing/confirm' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Confirm'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail subscribe/unsubscribe pages' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::CALLBACK,
                             'weight'  => 660,
                             ),

                       'civicrm/mailing/subscribe' => 
                       array(
                             'query'   => 'reset=1',
                             'title'   => ts('Subscribe'),
                             'access_arguments'  => CRM_Core_Permission::check( 'access CiviMail subscribe/unsubscribe pages' ),
                             'type'    => CRM_Core_Menu::CALLBACK,
                             'crm_type' => CRM_Core_Menu::CALLBACK,
                             'weight'  => 660,
                             ),
                       );

        return $items;
    }
}


