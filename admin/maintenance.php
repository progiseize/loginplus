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
if (! $res && file_exists("../../../main.inc.php")): $res=@include '../../../main.inc.php'; endif;

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once DOL_DOCUMENT_ROOT."/core/lib/ajax.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/files.lib.php";

dol_include_once('./loginplus/class/loginplus.class.php');
dol_include_once('./loginplus/lib/loginplus.lib.php');

// Protection if external user
if ($user->socid > 0): accessforbidden(); endif;
if (!$user->hasRight('loginplus','maintenancemode')): accessforbidden(); endif;

$langs->load('admin');
$langs->load('loginplus@loginplus');

/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action','aZ09');
$adminmenukey = 'maintenance';

/******/

$form = new Form($db);
$formother = new FormOther($db);
$loginplus_static = new LoginPlus($db);

/*******************************************************************
* ACTIONS
********************************************************************/


//
//
$error = 0;
switch($action):
    case 'set_maintenanceoptions':
        if(!dolibarr_set_const($db, 'LOGINPLUS_MAINTENANCETEXT',GETPOST('LOGINPLUS_MAINTENANCETEXT')?:'','chaine',0,'',$conf->entity)): $error++; endif;
        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); setEventMessages($langs->trans('loginplus_optionp_error'), null, 'errors');
        endif;
    break;
endswitch;

// $form=new Form($db);
/***************************************************
* VIEW
****************************************************/

$array_js = array();
$array_css = array('/loginplus/css/dolpgs.css');

llxHeader('',$langs->transnoentities('loginplus_optionp_title').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus setup'); ?>

<div class="doladmin">

    <!--  -->
    <div id="doladmin-content">
        <?php 
        $linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
        print load_fiche_titre($langs->trans("loginplus_option_maintenance"), $linkback, 'title_setup'); ?>

        <?php //$head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'setup','loginplus', 1); ?>

        <div class="doladmin-flex-wrapper" id="loginplusadmin-content">

            <!-- COL FOR MENU -->
            <div class="doladmin-col-menu">
                <?php echo lp_showAdminMenu('maintenance', $user); ?>
            </div>

            <!-- COL FOR PARAMS -->
            <div class="doladmin-col-params">

                <div class="doladmin-card">

                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_option_maintenance'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_option_maintenance_desc'); ?></p>
                    <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">

                        <!-- FORM MAINTENANCE -->
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">

                            <input type="hidden" name="action" value="set_maintenanceoptions">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">

                            <table class="doladmin-table-simple">                                
                                <tbody>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_maintenance_msg').' '.img_info($langs->trans('loginplus_option_maintenance_msg_desc')); ?></td>
                                        <td class="right">
                                            <input type="text" class="width500" name="LOGINPLUS_MAINTENANCETEXT" value="<?php echo getDolGlobalString('LOGINPLUS_MAINTENANCETEXT'); ?>">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="doladmin-form-buttons right">
                                <input type="submit" name="">
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<?php llxFooter(); $db->close(); ?>