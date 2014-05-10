<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** Developers:                                                           **
**                                                                       **
** SQL            ( http://en.wikipedia.org/User:SQL )                   **
** Cobi           ( http://en.wikipedia.org/User:Cobi )                  **
** Cmelbye        ( http://en.wikipedia.org/User:cmelbye )               **
** FastLizard4    ( http://en.wikipedia.org/User:FastLizard4 )           **
** Stwalkerster   ( http://en.wikipedia.org/User:Stwalkerster )          **
** Soxred93       ( http://en.wikipedia.org/User:Soxred93)               **
** Alexfusco5     ( http://en.wikipedia.org/User:Alexfusco5)             **
** OverlordQ      ( http://en.wikipedia.org/wiki/User:OverlordQ )        **
** Prodego        ( http://en.wikipedia.org/wiki/User:Prodego )          **
** FunPika        ( http://en.wikipedia.org/wiki/User:FunPika )          **
** PRom3th3an     ( http://en.wikipedia.org/wiki/User:Promethean )       **
** Chris_G        ( http://en.wikipedia.org/wiki/User:Chris_G )          **
** LouriePieterse ( http://en.wikipedia.org/wiki/User:LouriePieterse )   **
** EdoDodo        ( http://en.wikipedia.org/wiki/User:EdoDodo )          **
** DeltaQuad      ( http://en.wikipedia.org/wiki/User:DeltaQuad )        **
** 1234r00t       ( http://en.wikipedia.org/wiki/User:1234r00t )         **
** Manishearth	  ( http://en.wikipedia.org/wiki/User:Manishearth )      **
** MacMed		  ( http://en.wikipedia.org/wiki/User:MacMed )   		 **
** John F. Lewis  ( http://en.wikipedia.org/wiki/User:John_F._Lewis)     **
***************************************************************************/

// Redirect user away from the current directory.
require_once('config.inc.php');
global $baseurl;
header("Location: $baseurl/team.php");
die();
