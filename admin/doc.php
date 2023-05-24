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
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';

dol_include_once('./loginplus/lib/loginplus.lib.php');

// Protection if external user
if ($user->societe_id > 0): accessforbidden(); endif;
if (!$user->rights->loginplus->configurer): accessforbidden(); endif;

/*******************************************************************
* VARIABLES
********************************************************************/
$tab_shapes = loginplusGetShapes();


/*******************************************************************
* ACTIONS
********************************************************************/

$action = GETPOST('action');


// $form=new Form($db);

/***************************************************
* VIEW
****************************************************/

$array_js = array();
$array_css = array(
    '/loginplus/css/dolpgs.css'
);

llxHeader('',$langs->transnoentities('Documentation').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus doc'); ?>


<div class="dolpgs-main-wrapper">

<?php if(in_array('progiseize', $conf->modules)): ?>
    <h1 class="has-before"><?php echo $langs->transnoentities('loginplus_head_doc'); ?></h1>
<?php else : ?>
    <table class="centpercent notopnoleftnoright table-fiche-title"><tbody><tr class="titre"><td class="nobordernopadding widthpictotitle valignmiddle col-picto"><span class="fas fa-tools valignmiddle widthpictotitle pictotitle" style=""></span></td><td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block"><?php echo $langs->transnoentities('loginplus_head_doc'); ?></div></td></tr></tbody></table>
<?php endif; ?>
<?php $head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'doc','loginplus', 0,'fa-user-lock_fas_#fb2a52'); ?>

<h3 class="dolpgs-table-title"><?php echo $langs->trans('loginplus_optiontitle_doc'); ?></h3>
<table class="dolpgs-table">


<table class="dolpgs-table">
    <tbody>

        <tr class="dolpgs-thead noborderside">
            <th><?php echo $langs->trans('Parameter'); ?></th>
            <th><?php echo $langs->trans('Description'); ?></th>
        </tr>

        <tr class="dolpgs-tbody">
            <td class="bold valigntop pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_doc_modeles'); ?></td>                
            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->transnoentities('loginplus_doc_modeles_desc'); ?></td>
        </tr>
        <tr class="dolpgs-tbody">
            <td class="bold valigntop pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_doc_showImages'); ?></td>                
            <td class="pgsz-optiontable-fielddesc">
                <?php echo $langs->trans('loginplus_doc_showImages_steps'); ?>
                <ul>
                    <li><?php echo $langs->transnoentities('loginplus_doc_showImages_step_1'); ?></li>
                    <li><?php echo $langs->transnoentities('loginplus_doc_showImages_step_2'); ?></li>
                    <li><?php echo $langs->transnoentities('loginplus_doc_showImages_step_3'); ?></li>
                    <li><?php echo $langs->transnoentities('loginplus_doc_showImages_step_4'); ?></li>
                    <li><?php echo $langs->transnoentities('loginplus_doc_showImages_step_5'); ?></li>
                </ul>
            </td>
        </tr>
        <tr class="dolpgs-tbody">
            <td class="bold valigntop pgsz-optiontable-fieldname"><?php echo $langs->transnoentities('loginplus_option_shape'); ?></td>                
            <td class="pgsz-optiontable-fielddesc">
                <ul id="loginplus-shapelist">

                    <?php foreach($tab_shapes as $id_group => $shapenames): ?>
                        <li>
                            <h4><?php echo $langs->trans('loginplus_shape_'.$id_group); ?></h4>
                        </li>
                        <li>
                            <?php foreach($shapenames as $shape): ?>
                                <div>
                                    <img src="../img/shapes/<?php echo $shape; ?>.jpg" width="192px">
                                    <p><?php echo $langs->trans('loginplus_shape_'.$shape); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                </ul> 
            </td>
        </tr>

    </tbody>
</table>
</div>
<?php llxFooter(); $db->close(); ?>