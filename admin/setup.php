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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmdirectory.class.php';
require_once DOL_DOCUMENT_ROOT.'/ecm/class/ecmfiles.class.php';
include_once DOL_DOCUMENT_ROOT."/core/lib/images.lib.php";

dol_include_once('./loginplus/lib/loginplus.lib.php');

// Protection if external user
if ($user->socid > 0): accessforbidden(); endif;
if (!$user->rights->loginplus->configurer): accessforbidden(); endif;


/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action','aZ09');

$tab_img = loginplusGetShareImages();
$tab_img = array_column($tab_img, NULL, 'share');

$tab_shapes = loginplusGetShapes();
$themes = loginplusGetThemes();
$dir_loginplus = loginplusGetFolder();

$formother = new FormOther($db);

/*******************************************************************
* ACTIONS
********************************************************************/

// VERIFS TEMPLATE PASSWORD
$forgetpass_tpl = '../core/tpl/passwordforgotten.tpl.php';
$theme_forgetpass_tpl = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/tpl/passwordforgotten.tpl.php';

if(getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL') > 0 && !file_exists($theme_forgetpass_tpl)):
    if (!copy($forgetpass_tpl, $theme_forgetpass_tpl)):
        setEventMessages($langs->trans('loginplus_optionp_copytpl_error'), null, 'errors');
    endif;
elseif(!getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL') && file_exists($theme_forgetpass_tpl)): 
    unlink($theme_forgetpass_tpl);
endif;

switch($action):

    //
    case 'setparams':

        // Maintenance
        if ($user->rights->loginplus->maintenancemode && GETPOSTISSET('ldo-maintenancetxt')):
            if(!dolibarr_set_const($db, "LOGINPLUS_MAINTENANCETEXT",GETPOST('ldo-maintenancetxt','alphanum'),'chaine',0,'',$conf->entity)): $error++; endif;
        endif; 

        // ARRIERE PLAN
        if(!dolibarr_set_const($db, 'LOGINPLUS_BG_COLOR',GETPOST('LOGINPLUS_BG_COLOR')?:'ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEOPACITY",GETPOST('LOGINPLUS_BG_IMAGEOPACITY')?:0,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEKEY",GETPOST('ldo-bg-imagekey'),'chaine',0,'',$conf->entity)): $error++; endif;

        // SHAPE
        if(!dolibarr_set_const($db, 'LOGINPLUS_SHAPE_COLOR',GETPOST('LOGINPLUS_SHAPE_COLOR')?:'263c5c','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_OPACITY",GETPOST('LOGINPLUS_SHAPE_OPACITY')?:0,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_PATH",(!empty(GETPOST('ldo-shape-path'))?GETPOST('ldo-shape-path'):'no'),'chaine',0,'',$conf->entity)): $error++; endif;

        // BOX LOGIN
        if(!dolibarr_set_const($db, 'LOGINPLUS_MAIN_COLOR',GETPOST('LOGINPLUS_MAIN_COLOR')?:'007b8c','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_SECOND_COLOR',GETPOST('LOGINPLUS_SECOND_COLOR')?:'263c5c','chaine',0,'',$conf->entity)): $error++; endif;

        // BOX TXT IMG
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_COLOR",GETPOST('LOGINPLUS_IMAGE_COLOR')?:'263c5c','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLE",trim(GETPOST('ldo-txt-title')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLECOLOR",GETPOST('LOGINPLUS_TXT_TITLECOLOR')?:'000000','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENT",trim(GETPOST('ldo-txt-content')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENTCOLOR",GETPOST('LOGINPLUS_TXT_CONTENTCOLOR')?:'000000','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_OPACITY",GETPOST('LOGINPLUS_IMAGE_OPACITY')?:0,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_KEY",GETPOST('ldo-img-key'),'chaine',0,'',$conf->entity)): $error++; endif;

        // COPYRIGHT
        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT_COLOR",GETPOST('LOGINPLUS_COPYRIGHT_COLOR')?:'3e3e3e','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT",trim(GETPOST('ldo-copyright')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_COPYRIGHT_LINK",GETPOST('ldo-copyright-link'),'chaine',0,'',$conf->entity)): $error++; endif;
        

        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); setEventMessages($langs->trans('loginplus_optionp_error'), null, 'errors');
        endif;

    break;

    // todo
    case 'apply_mod':

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
    break;

endswitch;

// $form=new Form($db);

/***************************************************
* VIEW
****************************************************/

$array_js = array(
    '/loginplus/js/remodal.js',
    '/loginplus/js/loginplus_config.js'
);
$array_css = array(
    '/loginplus/css/remodal.css',
    '/loginplus/css/dolpgs.css'
);

llxHeader('',$langs->transnoentities('loginplus_optionp_title').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus setup'); 

$linkback = '';
print load_fiche_titre($langs->trans("loginplus_optionp_title"), $linkback, 'title_setup'); ?>

<div class="dolpgs-main-wrapper logplus">

    <div class="remodal loginplus-remodal" data-remodal-id="pgsz-pop-image">

        <button data-remodal-action="close" class="remodal-close"></button>
        <input type="hidden" name="pgsz-target-name" value="">
        <input type="hidden" name="pgsz-target-div" value="">
        <input type="hidden" name="pgsz-target-config" value="<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=">
        
        <h1><?php echo $langs->transnoentities('loginplus_option_image_title'); ?></h1>
        <div class="pgsz-flex-wrapper" style="margin-bottom: 16px;">

        <?php if(!empty($tab_img)): ?>
            <?php foreach ($tab_img as $ld_img): ?>
                <div class="pgsz-flex-remodal">
                    <div class="pgsz-flex-remodal-img" data-ldkey="<?php echo $ld_img->share; ?>" style="background: url('<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $ld_img->share; ?>') center no-repeat;background-size: cover;"></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
                <p class="lp-modal-noimg">
                    <?php echo $langs->trans('loginplus_doc_showImages_steps'); ?><br/><br/>                    
                    <?php echo $langs->transnoentities('loginplus_doc_showImages_step_1'); ?><br/>
                    <?php echo $langs->transnoentities('loginplus_doc_showImages_step_2'); ?><br/>
                    <?php echo $langs->transnoentities('loginplus_doc_showImages_step_3'); ?><br/>
                    <?php echo $langs->transnoentities('loginplus_doc_showImages_step_4'); ?><br/>
                    <?php echo $langs->transnoentities('loginplus_doc_showImages_step_5'); ?>
                </p>
        <?php endif; ?>
        </div>
      <button data-remodal-action="cancel" class="dolpgs-btn btn-danger"><i class="fas fa-times"></i> <?php echo $langs->transnoentities('loginplus_option_image_no_use'); ?></button>
    </div>
    
    <?php $head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'setup','loginplus', 0,'fa-user-lock_fas_#fb2a52'); ?>

    <?php if ($user->rights->loginplus->configurer): ?>

    <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">
        <input type="hidden" name="action" value="setparams">
        <input type="hidden" name="token" value="<?php echo newToken(); ?>">

        <?php //var_dump($tab_img); ?>

        <h3 class="dolpgs-table-title"><?php echo $langs->trans('Configuration'); ?></h3>
        <table class="dolpgs-table">
            <tbody>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('Options'); ?></th>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_activatelogintpl'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_activatelogintpl_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><?php echo ajax_constantonoff('LOGINPLUS_ACTIVELOGINTPL',array(),$conf->entity,0,0,1); ?></td>
                </tr>
                <?php if ($user->rights->loginplus->maintenancemode && getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL') > 0): ?>                    
                    <tr class="dolpgs-tbody">
                        <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_maintenance'); ?></td>               
                        <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_maintenance_desc'); ?></td>
                        <td class="right pgsz-optiontable-field"><?php echo ajax_constantonoff('LOGINPLUS_ISMAINTENANCE',array(),$conf->entity,0,0,1); ?></td>
                    </tr>
                    <tr class="dolpgs-tbody">
                        <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_maintenance_msg'); ?></td>               
                        <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_maintenance_msg_desc'); ?></td>
                        <td class="right pgsz-optiontable-field"><input type="text" name="ldo-maintenancetxt" class="minwidth400" value="<?php echo getDolGlobalString('LOGINPLUS_MAINTENANCETEXT'); ?>" ></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tbody>
                <?php // ARRIERE PLAN ?>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('loginplus_option_background'); ?></th>
                </tr>

                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_color'); ?></td>               
                    <td class="pgsz-optiontable-fielddesc "><?php echo $langs->trans('loginplus_option_background_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field ">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_BG_COLOR'), 'LOGINPLUS_BG_COLOR','',0); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_image'); ?></td>                
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_background_image_desc'); ?></td>
                    <td class="right pgsz-optiontable-field" >

                        <div class="pgsz-img-statut" id="ldo_bgkey">

                            <?php if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))):
                                $logo_file = new EcmFiles($db);
                                $logo_file->fetch('','','','',getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'));
                                $logo_infos = pathinfo($logo_file->filepath.'/'.$logo_file->filename);

                                $imgThumbMini = str_replace('ecm/','',$logo_file->filepath.'/thumbs/'.$logo_infos['filename'].'_mini.'.$logo_infos['extension']);
                                if(!file_exists($conf->ecm->dir_output.'/'.$imgThumbMini)):
                                    $imgThumbMini = vignette($conf->ecm->dir_output.'/loginplus/'.$logo_file->filename, '160', '42', '_mini', 50);
                                endif; ?>
                                <img style="max-height: 42px; max-width: 100px;border:1px solid #ccc;padding:3px;vertical-align:middle;display:inline;" src="<?php echo DOL_URL_ROOT.'/viewimage.php?modulepart=ecm&amp;file='.urlencode('loginplus/thumbs/'.basename($imgThumbMini)); ?>">
                            <?php else: echo '<span class="loginplus-no-img">'.$langs->trans('loginplus_option_background_image_none').'</span>'; ?>
                            <?php endif; ?>
                        </div> 

                        <div style="margin-top:6px"> 
                            <input type="hidden" name="ldo-bg-imagekey" value="<?php echo getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'); ?>">
                            <button data-remodal-target="pgsz-pop-image" class="loginplus-btn" data-ldtarget="ldo-bg-imagekey" data-ldparent="ldo_bgkey"><?php echo $langs->trans('loginplus_option_background_image_choose'); ?></button>
                        </div>

                    </td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_background_image_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_background_image_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="range" class="loginplus-rangeslider" name="LOGINPLUS_BG_IMAGEOPACITY" min="0" max="100" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY'); ?>" data-slidervalue="#ldo-bg-imageopacity">
                        <span class="loginplus-rangevalue" id="ldo-bg-imageopacity"><?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY'); ?>%</span>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <?php // SHAPE ?>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('loginplus_option_shape'); ?></th>
                </tr>
                
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_path'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_path_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <select name="ldo-shape-path" class="pgsz-slct2-simple">
                            <option value="no" <?php if(getDolGlobalString('LOGINPLUS_SHAPE_PATH') == 'no'): echo 'selected'; endif; ?>><?php echo $langs->trans('loginplus_shape_none'); ?></option>
                            <?php foreach($tab_shapes as $id_group => $shapenames): ?>
                                <optgroup label="<?php echo $langs->trans('loginplus_shape_'.$id_group); ?>">
                                    <?php foreach($shapenames as $shape): ?>
                                        <option value="<?php echo $shape; ?>" <?php if(getDolGlobalString('LOGINPLUS_SHAPE_PATH') == $shape): echo 'selected'; endif; ?>><?php echo $langs->trans('loginplus_shape_'.$shape); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>                        
                    </td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_SHAPE_COLOR'), 'LOGINPLUS_SHAPE_COLOR'); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_shape_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_shape_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="range" class="loginplus-rangeslider" name="LOGINPLUS_SHAPE_OPACITY" min="0" max="100" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_SHAPE_OPACITY'); ?>" data-slidervalue="#ldo-shape-opacity">
                        <span class="loginplus-rangevalue" id="ldo-shape-opacity"><?php echo getDolGlobalInt('LOGINPLUS_SHAPE_OPACITY'); ?>%</span>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <?php // BOX LOGIN :: PAGE LOGIN ?>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('loginplus_option_loginbox'); ?></th>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_loginbox_maincolor'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_loginbox_maincolor_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_MAIN_COLOR'), 'LOGINPLUS_MAIN_COLOR'); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_loginbox_secondcolor'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_loginbox_secondcolor_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_SECOND_COLOR'), 'LOGINPLUS_SECOND_COLOR'); ?>
                    </td>
                </tr>
            </tbody>
            <tbody>

                <?php // SIDE BOX ?>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('loginplus_option_sidebox'); ?></th>
                </tr>
                
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_show'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_show_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo ajax_constantonoff('LOGINPLUS_TWOSIDES',
                            array(
                                'show' => array('.sideboxfield'),
                                'hide' => array('.sideboxfield'),
                                )
                            ); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody sideboxfield" style="position: relative;">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_IMAGE_COLOR'), 'LOGINPLUS_IMAGE_COLOR'); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody sideboxfield">
                    <td class="bold pgsz-optiontable-fieldname "><?php echo $langs->trans('loginplus_option_sidebox_image'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_image_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <div class="pgsz-img-statut" id="ldo_ikey">

                            <?php if(!empty(getDolGlobalString('LOGINPLUS_IMAGE_KEY'))):
                                $logo_file = new EcmFiles($db);
                                $logo_file->fetch('','','','',getDolGlobalString('LOGINPLUS_IMAGE_KEY'));
                                $logo_infos = pathinfo($logo_file->filepath.'/'.$logo_file->filename);

                                $imgThumbMini = str_replace('ecm/','',$logo_file->filepath.'/thumbs/'.$logo_infos['filename'].'_mini.'.$logo_infos['extension']);
                                if(!file_exists($conf->ecm->dir_output.'/'.$imgThumbMini)):
                                    $imgThumbMini = vignette($conf->ecm->dir_output.'/loginplus/'.$logo_file->filename, '160', '42', '_mini', 50);
                                endif; ?>
                                <img style="max-height: 42px; max-width: 100px;border:1px solid #ccc;padding:3px;vertical-align:middle;display:inline;" src="<?php echo DOL_URL_ROOT.'/viewimage.php?modulepart=ecm&amp;file='.urlencode('loginplus/thumbs/'.basename($imgThumbMini)); ?>">
                            <?php else: echo '<span class="loginplus-no-img">'.$langs->trans('loginplus_option_background_image_none').'</span>'; ?>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top:6px"> 
                            <input type="hidden" name="ldo-img-key" value="<?php echo getDolGlobalString('LOGINPLUS_IMAGE_KEY'); ?>">
                            <button data-remodal-target="pgsz-pop-image" class="loginplus-btn" data-ldtarget="ldo-img-key" data-ldparent="ldo_ikey"><?php echo $langs->trans('loginplus_option_background_image_choose'); ?></button>
                        </div>
                    </td>
                </tr>
                <tr class="dolpgs-tbody sideboxfield">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_image_opacity'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_image_opacity_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="range" class="loginplus-rangeslider" name="LOGINPLUS_IMAGE_OPACITY" min="0" max="100" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY'); ?>" data-slidervalue="#ldo-img-opacity">
                        <span class="loginplus-rangevalue" id="ldo-img-opacity"><?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY'); ?>%</span>
                    </td>
                </tr>
                <tr class="dolpgs-tbody sideboxfield">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_title'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_contentempty'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="text" name="ldo-txt-title" id="ldo-txt-title" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_TITLE'); ?>">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_TXT_TITLECOLOR'), 'LOGINPLUS_TXT_TITLECOLOR'); ?>
                    </td>
                </tr>
                <tr class="dolpgs-tbody sideboxfield">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_sidebox_content'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_sidebox_contentempty'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <input type="text" name="ldo-txt-content" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_CONTENT'); ?>">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_TXT_CONTENTCOLOR'), 'LOGINPLUS_TXT_CONTENTCOLOR'); ?>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <?php // COPYRIGHT :: PAGE LOGIN ?>
                <tr class="dolpgs-thead noborderside">
                    <th colspan="3"><?php echo $langs->trans('loginplus_option_copyright'); ?></th>
                </tr>
                
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_title'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_title_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-copyright" value="<?php echo getDolGlobalString('LOGINPLUS_COPYRIGHT'); ?>"></td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_link'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_link_desc'); ?></td>
                    <td class="right pgsz-optiontable-field"><input type="text" name="ldo-copyright-link" value="<?php echo getDolGlobalString('LOGINPLUS_COPYRIGHT_LINK'); ?>"></td>
                </tr>
                <tr class="dolpgs-tbody">
                    <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_option_copyright_color'); ?></td>
                    <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_option_copyright_color_desc'); ?></td>
                    <td class="right pgsz-optiontable-field">
                        <?php echo $formother->selectColor(getDolGlobalString('LOGINPLUS_COPYRIGHT_COLOR'), 'LOGINPLUS_COPYRIGHT_COLOR'); ?>
                    </td>
                </tr>

            </tbody>
        </table>
        <div class="right" ><input type="submit" class="dolpgs-btn btn-primary btn-sm" name="" value="<?php echo $langs->trans('Save'); ?>"></div>
    </form>

    <h3 class="dolpgs-table-title"><?php echo $langs->trans('loginplus_option_themes'); ?></h3>
    <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="post" id="">
        <input type="hidden" name="action" value="apply_mod">
        <input type="hidden" name="token" value="<?php echo newtoken(); ?>">

        <ul class="dolpgs-flex-wrapper" id="loginplus-themelist">
            <?php foreach ($themes as $theme_key => $theme): ?>
            <li class="dolpgs-flex-item flex-3">
                <div class="ld-themepreview">
                    <img src="../img/themes/<?php echo $theme_key.'/'.$theme['preview']; ?>" >                    
                    <div class="ld-apply-overlay">
                        <button class="dolpgs-btn" name="ld_theme" type="submit" value="<?php echo $theme_key; ?>"><?php echo $langs->trans('Apply'); ?></button>
                    </div>                    
                </div>            
            </li>
            <?php endforeach; ?>
        </ul>

    </form>

    <?php endif; ?>
</div>

<script type="text/javascript">
    
    jQuery(document).ready(function(){

        <?php if(!getDolGlobalInt('LOGINPLUS_TWOSIDES')): ?>
            jQuery('.sideboxfield').hide(100); // DELAY is required for positionning
        <?php endif; ?>

        let output;
        jQuery('input[type="range"]').on('input',function(){
            output = document.querySelector(jQuery(this).data('slidervalue'));
            output.innerHTML = jQuery(this)[0].value+'%';
        });

        /*jQuery('input[type="color"]').on('change',function(){
            console.log(jQuery(this).val());
        });*/
    });
    
</script>

<?php llxFooter(); $db->close(); ?>