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
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';

dol_include_once('./loginplus/lib/loginplus.lib.php');


// Change this following line to use the correct relative path from htdocs
dol_include_once('/module/class/skeleton_class.class.php');

// Protection if external user
if ($user->societe_id > 0): accessforbidden(); endif;
if (!$user->rights->loginplus->configurer): accessforbidden(); endif;


/*******************************************************************
* VARIABLES
********************************************************************/
$tab_img = loginplusGetShareImages();
$tab_img = array_column($tab_img, NULL, 'share');

$tab_shapes = loginplusGetShapes();
$themes = loginplusGetThemes();
$dir_loginplus = loginplusGetFolder();


/*******************************************************************
* ACTIONS
********************************************************************/

$action = GETPOST('action');

if ($action == 'set_options'): 

    if(GETPOST('token') == $_SESSION['token']):

        if(!dolibarr_set_const($db, "LOGINPLUS_ACTIVELOGINTPL",GETPOST('ldo-activatetpl'),'chaine',0,'',$conf->entity)): $error++; endif;
        
        if ($user->rights->loginplus->maintenancemode):
            if(!dolibarr_set_const($db, "LOGINPLUS_ISMAINTENANCE",GETPOST('ldo-ismaintenance'),'chaine',0,'',$conf->entity)): $error++; endif;
            if(!dolibarr_set_const($db, "LOGINPLUS_MAINTENANCETEXT",GETPOST('ldo-maintenancetxt','alphanum'),'chaine',0,'',$conf->entity)): $error++; endif;
        endif;
        
        if(GETPOSTISSET('ldo-activatetpl')):

            // DUPLICATA DU TPL
            $file_to_copy = '../core/tpl/passwordforgotten.tpl.php';
            $copy_file = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/tpl/passwordforgotten.tpl.php';
            
            // SI LE FICHIER N'EXISTE PAS DANS LE THEME // ON LE COPIE
            if(!file_exists($copy_file)):             
                if (!copy($file_to_copy, $copy_file)):
                    setEventMessages($langs->trans('loginplus_optionp_copytpl_error'), null, 'errors');
                endif;
            endif;

        else:
            // SUPPRESSION DU TPL
            $file_to_del = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/tpl/passwordforgotten.tpl.php';

            if(file_exists($file_to_del)): unlink($file_to_del); endif;

        endif;

        // VERIFICATION ARRIERE PLAN
        if(!empty(GETPOST('ldo-bg-color'))): $bg_color = GETPOST('ldo-bg-color'); else: $bg_color = '#ffffff'; endif;
        if(!empty(GETPOST('ldo-bg-imageopacity'))): $bgimage_opacity = GETPOST('ldo-bg-imageopacity'); else: $bgimage_opacity = 0; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_BG_COLOR",$bg_color,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEKEY",GETPOST('ldo-bg-imagekey'),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEOPACITY",$bgimage_opacity,'chaine',0,'',$conf->entity)): $error++; endif;

        // VERIFICATION FORME
        if(!empty(GETPOST('ldo-shape-path'))): $shape_path = GETPOST('ldo-shape-path'); else: $shape_path = 'no'; endif;
        if(!empty(GETPOST('ldo-shape-color'))): $shape_color = GETPOST('ldo-shape-color'); else: $shape_color = '#000000'; endif;
        if(!empty(GETPOST('ldo-shape-opacity'))): $shape_opacity = GETPOST('ldo-shape-opacity'); else: $shape_opacity = 0; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_PATH",$shape_path,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_COLOR",$shape_color,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_OPACITY",$shape_opacity,'chaine',0,'',$conf->entity)): $error++; endif;


        // BOX LOGIN
        if(!dolibarr_set_const($db, "LOGINPLUS_MAIN_COLOR",GETPOST('ldo-main-color'),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SECOND_COLOR",GETPOST('ldo-second-color'),'chaine',0,'',$conf->entity)): $error++; endif;

        // BOX TXT IMG
        if(!empty(GETPOST('ldo-img-opacity'))): $image_opacity = GETPOST('ldo-img-opacity'); else: $image_opacity = 0; endif;
        if(!empty(GETPOST('ldo-txt-titlecolor'))): $title_color = GETPOST('ldo-txt-titlecolor'); else: $title_color = '#000000'; endif;
        if(!empty(GETPOST('ldo-txt-contentcolor'))): $content_color = GETPOST('ldo-txt-contentcolor'); else: $content_color = '#000000'; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_TWOSIDES",GETPOST('ldo-twosides'),'chaine',0,'',$conf->entity)): $error++; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_COLOR",GETPOST('ldo-img-color'),'chaine',0,'',$conf->entity)): $error++; endif;    
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_KEY",GETPOST('ldo-img-key'),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_OPACITY",$image_opacity,'chaine',0,'',$conf->entity)): $error++; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLE",trim(GETPOST('ldo-txt-title')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLECOLOR",trim($title_color),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENT",trim(GETPOST('ldo-txt-content')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENTCOLOR",trim($content_color),'chaine',0,'',$conf->entity)): $error++; endif;

        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT",trim(GETPOST('ldo-copyright')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT_LINK",GETPOST('ldo-copyright-link'),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT_COLOR",GETPOST('ldo-copyright-color'),'chaine',0,'',$conf->entity)): $error++; endif;

        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); setEventMessages($langs->trans('loginplus_optionp_error'), null, 'errors');
        endif;
    else:
        setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), null, 'warnings');
    endif;
elseif ($action == 'apply_mod'): 

    if(GETPOST('token') == $_SESSION['token']):

        $db->begin();
        $error = 0;

        $ecm_dir = new EcmDirectory($db);
        $ecm_dir->fetch($dir_loginplus);    

        // ON RECUPERE LES INFOS DU THEME
        $apply_theme = $themes[GETPOST('ld_theme')];

        // ON RECUPERE LES PARAMETRES DE BASE DES THEMES
        $params = loginplusGetThemesParams(GETPOST('ld_theme'));

        // SI IL Y A UNE IMAGE BACKGROUND
        if($apply_theme['background']):

            $ecm_file = new EcmFiles($db);

            //ON DEFINIT LE CHEMIN DE L'IMAGE A COPIER
            $background_path = '../img/themes/'.GETPOST('ld_theme').'/'.$apply_theme['background'];

            // ON DEFINIT LE CHEMIN DE LA COPIE DE L'IMAGE
            $background_copy = DOL_DATA_ROOT.'/ecm/loginplus/'.$apply_theme['background'];

            // SI L'IMAGE EXISTE DEJA
            if (file_exists($background_copy)): 

                // ON RECUPERE SES INFOS
                $ecm_file->fetch('','','ecm/loginplus/'.$apply_theme['background']);

                // ON VERIFIE LE SHARE ID
                if(!$ecm_file->share): $ecm_file->share = getRandomPassword(true); $ecm_file->update($user); endif;

            // SI L'IMAGE N'EXISTE PAS
            else:

                // ON COPIE L'IMAGE
                if (copy($background_path, $background_copy)):

                    // ON RENSEIGNE LES INFOS
                    $ecm_file->filepath = 'ecm/loginplus';
                    $ecm_file->filename = $apply_theme['background'];
                    $ecm_file->fullpath_orig = $apply_theme['background'];
                    $ecm_file->entity = $conf->entity;
                    $ecm_file->label = md5_file(dol_osencode($background_copy));
                    $ecm_file->gen_or_uploaded = 'uploaded';
                    $ecm_file->share = getRandomPassword(true);

                    if(!$ecm_file->create($user)): $error++; else: $ecm_dir->changeNbOfFiles('+'); endif;

                else: $error++;
                endif;

            endif;

            $params['LOGINPLUS_BG_IMAGEKEY'] = $ecm_file->share;

        endif;

        // SI IL Y A UNE IMAGE SIDEGROUND
        if($apply_theme['sideground']):

            $ecm_file = new EcmFiles($db);

            //ON DEFINIT LE CHEMIN DE L'IMAGE A COPIER
            $sideground_path = '../img/themes/'.GETPOST('ld_theme').'/'.$apply_theme['sideground'];

            // ON DEFINIT LE CHEMIN DE LA COPIE DE L'IMAGE
            $sideground_copy = DOL_DATA_ROOT.'/ecm/loginplus/'.$apply_theme['sideground'];

            // SI L'IMAGE EXISTE DEJA
            if (file_exists($sideground_copy)): 

                // ON RECUPERE SES INFOS
                $ecm_file->fetch('','','ecm/loginplus/'.$apply_theme['sideground']);

                // ON VERIFIE LE SHARE ID
                if(!$ecm_file->share): $ecm_file->share = getRandomPassword(true); $ecm_file->update($user); endif;

            // SI L'IMAGE N'EXISTE PAS
            else:

                // ON COPIE L'IMAGE
                if (copy($sideground_path, $sideground_copy)): 

                    // ON RENSEIGNE LES INFOS
                    $ecm_file->filepath = 'ecm/loginplus';
                    $ecm_file->filename = $apply_theme['sideground'];
                    $ecm_file->fullpath_orig = $apply_theme['sideground'];
                    $ecm_file->entity = $conf->entity;
                    $ecm_file->label = md5_file(dol_osencode($sideground_copy));
                    $ecm_file->gen_or_uploaded = 'uploaded';
                    $ecm_file->share = getRandomPassword(true);

                    if(!$ecm_file->create($user)): $error++; else: $ecm_dir->changeNbOfFiles('+'); endif;

                else: $error++;
                endif;

            endif;

            $params['LOGINPLUS_IMAGE_KEY'] = $ecm_file->share;

        endif;

        if(!$error): loginplusApplyTheme($params); $db->commit();
        else: $db->rollback(); setEventMessages('Une erreur est survenue', null, 'errors');
        endif;
    else:
        setEventMessages($langs->trans('SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry'), null, 'warnings');
    endif;
endif;

// $form=new Form($db);

/***************************************************
* VIEW
****************************************************/
llxHeader('',$langs->transnoentities('loginplus_optionp_title').' :: '.$langs->transnoentities('Module300316Name'),'','','','',array("/loginplus/js/remodal.js","/loginplus/js/loginplus_config.js"),array("/loginplus/css/remodal.css","/loginplus/css/loginplus.css")); ?>

<div id="pgsz-option" class="loginplus_adm pgsz-theme-<?php echo $conf->theme; ?>">

    <div class="remodal pgsz-remodal" data-remodal-id="pgsz-pop-image">

        <button data-remodal-action="close" class="remodal-close"></button>
        <input type="hidden" name="pgsz-target-name" value="">
        <input type="hidden" name="pgsz-target-div" value="">
        <input type="hidden" name="pgsz-target-config" value="<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=">
        
        <h1><?php echo $langs->transnoentities('loginplus_option_image_title'); ?></h1>
        <div class="pgsz-flex-wrapper">

        <?php foreach ($tab_img as $ld_img): ?>
            <div class="pgsz-flex-remodal">
                <div class="pgsz-flex-remodal-img" data-ldkey="<?php echo $ld_img->share; ?>" style="background: url('<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $ld_img->share; ?>') center no-repeat;background-size: cover;"></div>
            </div>
        <?php endforeach; ?>
        </div>
      <button data-remodal-action="cancel" class="remodal-cancel"><i class="fas fa-times" style="color:#fff"></i> <?php echo $langs->transnoentities('loginplus_option_image_no_use'); ?></button>
    </div>

    <?php if(in_array('progiseize', $conf->modules)): ?>
        <h1><?php echo $langs->transnoentities('loginplus_optionp_title'); ?></h1>
    <?php else : ?>
        <table class="centpercent notopnoleftnoright table-fiche-title"><tbody><tr class="titre"><td class="nobordernopadding widthpictotitle valignmiddle col-picto"><span class="fas fa-tools valignmiddle widthpictotitle pictotitle" style=""></span></td><td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block"><?php echo $langs->transnoentities('loginplus_optionp_title'); ?></div></td></tr></tbody></table>
    <?php endif; ?>

    <?php $head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'setup','loginplus', 0,'progiseize@progiseize'); ?>

    <?php if ($user->rights->loginplus->configurer): ?>

    <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">
        <input type="hidden" name="action" value="set_options">
        <input type="hidden" name="token" value="<?php echo newtoken(); ?>">

        <?php //var_dump($tab_img); ?>

        <table class="noborder centpercent pgsz-option-table" style="border-top:none;">
            <tbody>

                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_activatelogintpl'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_activatelogintpl_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="checkbox" name="ldo-activatetpl" value="1" <?php if($conf->global->LOGINPLUS_ACTIVELOGINTPL): echo 'checked="checked"';endif; ?>></td>
                </tr>
                <?php if ($user->rights->loginplus->maintenancemode): ?>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_maintenance'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_maintenance_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="checkbox" name="ldo-ismaintenance" value="1" <?php if($conf->global->LOGINPLUS_ISMAINTENANCE): echo 'checked="checked"';endif; ?>></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_maintenance_msg'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_maintenance_msg_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-maintenancetxt" class="minwidth400" value="<?php echo $conf->global->LOGINPLUS_MAINTENANCETEXT; ?>" ></td>
                </tr>
                <?php endif; ?>
                <?php // ARRIERE PLAN ?>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                        <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('loginplus_option_background'); ?></div>
                    </td>
                </tr>
                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>

                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_color'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc "><?php echo $langs->trans('loginplus_option_background_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field "><input type="color" name="ldo-bg-color" value="<?php echo ($conf->global->LOGINPLUS_BG_COLOR)?$conf->global->LOGINPLUS_BG_COLOR:'#cccccc'; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_image'); ?></td>                
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_background_image_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <div class="pgsz-img-statut" id="ldo_bgkey">
                            <?php if($conf->global->LOGINPLUS_BG_IMAGEKEY): ?>
                                <img src="<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $conf->global->LOGINPLUS_BG_IMAGEKEY; ?>" style="max-width: 120px;height: auto;" />
                            <?php else: ?><?php echo $langs->trans('loginplus_option_background_image_none'); ?>
                            <?php endif; ?>
                        </div>
                        
                        <button data-remodal-target="pgsz-pop-image" class="pgsz-slct-img" data-ldtarget="ldo-bg-imagekey" data-ldparent="ldo_bgkey"><?php echo $langs->trans('loginplus_option_background_image_choose'); ?></button>
                        <input type="hidden" name="ldo-bg-imagekey" value="<?php echo $conf->global->LOGINPLUS_BG_IMAGEKEY; ?>">
                    </td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_image_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_background_image_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="number" name="ldo-bg-imageopacity" min="0" max="100" step="1" value="<?php echo $conf->global->LOGINPLUS_BG_IMAGEOPACITY; ?>">
                    </td>
                </tr>

                <?php // SHAPE ?>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                        <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('loginplus_option_shape'); ?></div>
                    </td>
                </tr>
                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_path'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_path_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">

                        <select name="ldo-shape-path" class="pgsz-slct2-simple">

                            <option value="no" <?php if($conf->global->LOGINPLUS_SHAPE_PATH == 'no'): echo 'selected'; endif; ?>><?php echo $langs->trans('loginplus_shape_none'); ?></option>
                            <?php foreach($tab_shapes as $id_group => $shapenames): ?>
                                <optgroup label="<?php echo $langs->trans('loginplus_shape_'.$id_group); ?>">
                                    <?php foreach($shapenames as $shape): ?>
                                        <option value="<?php echo $shape; ?>" <?php if($conf->global->LOGINPLUS_SHAPE_PATH == $shape): echo 'selected'; endif; ?>><?php echo $langs->trans('loginplus_shape_'.$shape); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        
                    </td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-shape-color" value="<?php echo ($conf->global->LOGINPLUS_SHAPE_COLOR)?$conf->global->LOGINPLUS_SHAPE_COLOR:'#263c5c'; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="number" name="ldo-shape-opacity" min="0" max="100" step="1" value="<?php echo $conf->global->LOGINPLUS_SHAPE_OPACITY; ?>">
                    </td>
                </tr>

                <?php // BOX LOGIN :: PAGE LOGIN ?>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                        <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('loginplus_option_loginbox'); ?></div>
                    </td>
                </tr>
                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_loginbox_maincolor'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_loginbox_maincolor_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-main-color" value="<?php echo ($conf->global->LOGINPLUS_MAIN_COLOR)?$conf->global->LOGINPLUS_MAIN_COLOR:'#007b8c'; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_loginbox_secondcolor'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_loginbox_secondcolor_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-second-color" value="<?php echo ($conf->global->LOGINPLUS_SECOND_COLOR)?$conf->global->LOGINPLUS_SECOND_COLOR:'#263c5c'; ?>"></td>
                </tr>




                <?php // COULEUR PRINCIPALE :: PAGE LOGIN ?>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                        <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('loginplus_option_sidebox'); ?></div>
                    </td>
                </tr>
                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_show'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_show_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="checkbox" name="ldo-twosides" value="1" <?php if($conf->global->LOGINPLUS_TWOSIDES): echo 'checked="checked"';endif; ?>></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-img-color" value="<?php echo $conf->global->LOGINPLUS_IMAGE_COLOR; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname "><?php echo $langs->trans('loginplus_option_sidebox_image'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_image_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <div class="pgsz-img-statut" id="ldo_ikey">
                        <?php if($conf->global->LOGINPLUS_IMAGE_KEY): ?>
                                <img src="<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $conf->global->LOGINPLUS_IMAGE_KEY; ?>" style="max-width: 120px;height: auto;" />
                            <?php else:  ?><?php echo $langs->trans('loginplus_option_background_image_none'); ?>
                            <?php endif; ?>
                        </div>
                        <button data-remodal-target="pgsz-pop-image" class="pgsz-slct-img" data-ldtarget="ldo-img-key" data-ldparent="ldo_ikey">Choisir une image</button>
                        <input type="hidden" name="ldo-img-key" value="<?php echo $conf->global->LOGINPLUS_IMAGE_KEY; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_image_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_image_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="number" name="ldo-img-opacity" min="0" max="100" step="1" value="<?php echo $conf->global->LOGINPLUS_IMAGE_OPACITY; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_title'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_contentempty'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-txt-title" value="<?php echo $conf->global->LOGINPLUS_TXT_TITLE; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_title_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_title_color'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-txt-titlecolor" value="<?php echo $conf->global->LOGINPLUS_TXT_TITLECOLOR; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_content'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_contentempty'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-txt-content" value="<?php echo $conf->global->LOGINPLUS_TXT_CONTENT; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-mask'; endif; ?>">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_content_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_content_color'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-txt-contentcolor" value="<?php echo $conf->global->LOGINPLUS_TXT_CONTENTCOLOR; ?>"></td>
                </tr>

                <?php // COPYRIGHT :: PAGE LOGIN ?>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="3">
                        <div class="titre inline-block" style="padding:16px 0"><?php echo $langs->trans('loginplus_option_copyright'); ?></div>
                    </td>
                </tr>
                <tr class="liste_titre pgsz-optiontable-coltitle" >
                    <th><?php echo $langs->trans('Parameter'); ?></th>
                    <th><?php echo $langs->trans('Description'); ?></th>
                    <th class="right"><?php echo $langs->trans('Value'); ?></th>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_title'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_title_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-copyright" value="<?php echo $conf->global->LOGINPLUS_COPYRIGHT; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_link'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_link_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-copyright-link" value="<?php echo $conf->global->LOGINPLUS_COPYRIGHT_LINK; ?>"></td>
                </tr>
                <tr class="oddeven pgsz-optiontable-tr">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="color" name="ldo-copyright-color" value="<?php echo $conf->global->LOGINPLUS_COPYRIGHT_COLOR; ?>"></td>
                </tr>


            </tbody>
        </table>
        <div class="right" style="padding:16px 0;"><input type="submit" class="button" name="" value="<?php echo $langs->trans('Save'); ?>"></div>
    </form>

    <h2 class="pgsz-title-option"><?php echo $langs->trans('loginplus_option_themes'); ?></h2>
    <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">
        <input type="hidden" name="action" value="apply_mod">
        <input type="hidden" name="token" value="<?php echo newtoken(); ?>">

        <ul id="pgsz-loginplus-themelist">
            <?php foreach ($themes as $theme_key => $theme): ?>
            <li>
                <div class="ld-themepreview">
                    <img src="../img/themes/<?php echo $theme_key.'/'.$theme['preview']; ?>" >
                    <h3 class="ld-themename"><?php echo $theme['label']; ?></h3>
                    <button class="ld-themeaction" name="ld_theme" type="submit" value="<?php echo $theme_key; ?>"><?php echo $langs->trans('Apply'); ?></button>
                </div>            
            </li>
            <?php endforeach; ?>
        </ul>

    </form>

    <?php endif; ?>
</div>

<?php llxFooter(); $db->close(); ?>