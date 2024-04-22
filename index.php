<?php
/* 
 * Copyright (C) 2022 ProgiSeize <contact@progiseize.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU Affero General Public License (AGPL) as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AGPL for more details.
 *
 * You should have received a copy of the GNU AGPL
 * along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.
 */


$res=0;
if (! $res && file_exists("../main.inc.php")): $res=@include '../main.inc.php'; endif;
if (! $res && file_exists("../../main.inc.php")): $res=@include '../../main.inc.php'; endif;

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

dol_include_once('./loginplus/class/loginmsg.class.php');

// Load traductions files requiredby by page
//$langs->load("companies");

// Protection if external user
if ($user->socid > 0): accessforbidden(); endif;


/*******************************************************************
* FONCTIONS
********************************************************************/


/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action');


/*******************************************************************
* ACTIONS
********************************************************************/
dolibarr_set_const($db, 'TEST__A','','chaine',0,'',$conf->entity);
dolibarr_set_const($db, 'TEST__B','','chaine',0,'',$conf->entity);

/***************************************************
* VIEW
****************************************************/

llxHeader('','LoginPlus','');?>

<!-- CONTENEUR GENERAL -->

<?php
// End of page
llxFooter();
$db->close();

?>