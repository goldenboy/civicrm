/**
   +----------------------------------------------------------------------+
   | CiviCRM version 1.0                                                  |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 Donald A. Lobo                                    |
   +----------------------------------------------------------------------+
   | This file is a part of CiviCRM.                                      |
   |                                                                      |
   | CiviCRM is free software; you can redistribute it and/or modify it   |
   | under the terms of the Affero General Public License Version 1,      |
   | March 2002.                                                          |
   |                                                                      |
   | CiviCRM is distributed in the hope that it will be useful, but       |
   | WITHOUT ANY WARRANTY; without even the implied warranty of           |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
   | See the Affero General Public License for more details at            |
   | http://www.affero.org/oagpl.html                                     |
   |                                                                      |
   | A copy of the Affero General Public License has been been            |
   | distributed along with this program (affero_gpl.txt)                 |
   +----------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

function getWord(input, evt) {

    if (input.value.length == 0) {
        return;
    }
    
    /*allow backspace to work in IE*/
    if (typeof input.selectionStart == 'undefined' && evt.keyCode == 8) { input.value = input.value.substr(0,input.value.length-1); }

    /*Ignore the following keystrokes*/
    switch (evt.keyCode) {
        case 37: //left arrow
        case 39: //right arrow
        case 33: //page up  
        case 34: //page down  
        case 36: //home  
        case 35: //end
        case 13: //enter
        case 9: //tab
        case 27: //esc
        case 16: //shift  
        case 17: //ctrl  
        case 18: //alt  
        case 20: //caps lock
        case 8: //backspace  
        case 46: //delete 
        case 38: //up arrow 
        case 40: //down arrow
        return;
        break;
    }

    /*Remember the current length to allow selection*/
    CompletionHandler.lastLength = input.value.length;
      
    /*Create the remote client*/

    var a = new crm_contact_form_statecountryserver (CompletionHandler);

    /*Set a timeout for responses which take too long*/
    a.timeout = 3000;
    
    /*Ignore timeouts*/
    a.clientErrorFunc = function(e) {
        if ( e.code == 1003 ) {
            /* Ignore...*/
        } else {
            alert(e);
        }
    }

    /*Call the remote method*/
    a.getword(input.value);
}

/*Callback handler*/
var CompletionHandler = {

    lastLength: 0,
    
    /*Callback method*/
    getword: function(result) {
        if (result.length < 1 ) {
            return;
        }        

        var input = document.getElementById('state');
        input.value = result.pop();

        var input1 = document.getElementById('state_id');
        input1.value = result.length;

        var b = new crm_contact_form_statecountryserver (CompletionHandlerCountry);
        b.getcountry(input1.value);

        try {
            input.setSelectionRange(this.lastLength, input.value.length);
        } catch(e) {
        }
    }

}

 /*Callback handler*/
var CompletionHandlerCountry = {

    lastLength: 0,

    /*Callback method*/
    getcountry: function(result) {
        
        if (result.length < 1 ) {
            return;
        }
        
        var input = document.getElementById('country');
        input.value = result.pop();

        var input1 = document.getElementById('country_id');
        input1.value = result.length;

        try {
            input.setSelectionRange(this.lastLength, input.value.length);
        } catch(e) {
        }
    }
}


