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
if (!$user->hasRight('loginplus','configurer')): accessforbidden(); endif;

$langs->load('admin');
$langs->load('loginplus@loginplus');

/*******************************************************************
* VARIABLES
********************************************************************/
$action = GETPOST('action','aZ09');
$optiontype = GETPOST('optiontype','aZ09')?:'box';
$adminmenukey = 'setup'; 

/******/
$tab_img = loginplusGetShareImages();
$tab_img = array_column($tab_img, NULL, 'share');
$tab_shapes = loginplusGetShapes();
/******/

$form = new Form($db);
$formother = new FormOther($db);
$loginplus_static = new LoginPlus($db);

$template_forgetpwd_loginplus = '../core/tpl/passwordforgotten.tpl.php';
$template_forgetpwd_themefolder = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/tpl';
$template_forgetpwd_theme = $template_forgetpwd_themefolder.'/passwordforgotten.tpl.php';

/*******************************************************************
* ACTIONS
********************************************************************/

$error = 0;
switch($action):

    // BACKGROUND PARAMS
    case 'set_background':

        // ARRIERE PLAN
        if(!dolibarr_set_const($db, 'LOGINPLUS_BG_COLOR',GETPOST('LOGINPLUS_BG_COLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEOPACITY",GETPOST('LOGINPLUS_BG_IMAGEOPACITY')?:100,'chaine',0,'',$conf->entity)): $error++; endif;
        $background_image = $_FILES['ldo-bg-image'];
        if($background_image['size'] > 0):
            if ($background_image['name'] && !preg_match('/(\.jpeg|\.jpg|\.png)$/i', $background_image['name'])):
                $error++;
                setEventMessages($langs->trans("ErrorBadFormat"), null, 'errors');
                break;
            endif;

            if (preg_match('/([^\\/:]+)$/i', $background_image['name'], $reg)):
                $dirforimage = $conf->medias->multidir_output[$conf->entity].'/loginplus';
                $nfilename = 'bg_'.str_replace(' ', '_', $reg[1]);
                if (!is_dir($dirforimage)): dol_mkdir($dirforimage); endif;
                $result = dol_move_uploaded_file($background_image['tmp_name'], $dirforimage.'/'.$nfilename, 1, 0, $background_image['error']);
                if($result):
                    if(!dolibarr_set_const($db, "LOGINPLUS_BG_IMAGEKEY",$nfilename,'chaine',0,'',$conf->entity)): $error++; endif;
                else:
                    $error++;
                    setEventMessages($langs->trans("Error"), null, 'errors');
                    break;
                endif;
            endif;
        endif;

        //SHAPE
        if(!dolibarr_set_const($db, 'LOGINPLUS_SHAPE_COLOR',GETPOST('LOGINPLUS_SHAPE_COLOR')?:'#263c5c','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_OPACITY",GETPOST('LOGINPLUS_SHAPE_OPACITY')?:0,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_PATH",(!empty(GETPOST('LOGINPLUS_SHAPE_PATH'))?GETPOST('LOGINPLUS_SHAPE_PATH'):'no'),'chaine',0,'',$conf->entity)): $error++; endif;
        $shape_path_alternate = (GETPOSTISSET('LOGINPLUS_SHAPE_ALT') && GETPOST('LOGINPLUS_SHAPE_ALT','aZ09') == 'on')?true:false;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHAPE_ALT",$shape_path_alternate,'chaine',0,'',$conf->entity)): $error++; endif;

        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); endif;
    break;

    // TEMPLATE PARAMS
    case 'set_template':

        if(!dolibarr_set_const($db, 'LOGINPLUS_TEMPLATE',GETPOST('LOGINPLUS_TEMPLATE')?:'template_one','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_RADIUS',GETPOST('LOGINPLUS_BOX_RADIUS')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
        
        $showsidebox = (GETPOSTISSET('LOGINPLUS_SHOW_SECONDARYBOX') && GETPOST('LOGINPLUS_SHOW_SECONDARYBOX','aZ09') == 'on')?1:0;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHOW_SECONDARYBOX",$showsidebox,'chaine',0,'',$conf->entity)): $error++; endif;

        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_MARGIN',GETPOST('LOGINPLUS_BOX_MARGIN')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
            if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_ALIGN',GETPOST('LOGINPLUS_BOX_ALIGN')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
            if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_WIDTH',GETPOST('LOGINPLUS_BOX_WIDTH')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
        
        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); setEventMessages($langs->trans('loginplus_optionp_error'), null, 'errors');
        endif;
    break;

    // LOGINBOX PARAMS
    case 'set_loginbox':

        $logo_alt = $_FILES['ldo-logo-alt'];
        if($logo_alt['size'] > 0):
            if ($logo_alt['name'] && !preg_match('/(\.jpeg|\.jpg|\.png)$/i', $logo_alt['name'])):
                $error++;
                setEventMessages($langs->trans("ErrorBadFormat"), null, 'errors');
                break;
            endif;
            if (preg_match('/([^\\/:]+)$/i', $logo_alt['name'], $reg)):
                $dirforimage = $conf->medias->multidir_output[$conf->entity].'/loginplus';
                $nfilename = 'logo_'.str_replace(' ', '_', $reg[1]);
                if (!is_dir($dirforimage)): dol_mkdir($dirforimage); endif;
                $result = dol_move_uploaded_file($logo_alt['tmp_name'], $dirforimage.'/'.$nfilename, 1, 0, $logo_alt['error']);
                if($result):
                    if(!dolibarr_set_const($db, "LOGINPLUS_LOGOALT",$nfilename,'chaine',0,'',$conf->entity)): $error++; endif;
                else:
                    $error++;
                    setEventMessages($langs->trans("Error"), null, 'errors');
                    break;
                endif;
            endif;
        endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_BACKGROUND',GETPOST('LOGINPLUS_BOX_BACKGROUND')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_ICONCOLOR',GETPOST('LOGINPLUS_BOX_ICONCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_LABELCOLOR',GETPOST('LOGINPLUS_BOX_LABELCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_INPUTCOLOR',GETPOST('LOGINPLUS_BOX_INPUTCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_INPUTCOLORFOCUS',GETPOST('LOGINPLUS_BOX_INPUTCOLORFOCUS')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_INPUTBORDERCOLOR',GETPOST('LOGINPLUS_BOX_INPUTBORDERCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_INPUTBORDERCOLORFOCUS',GETPOST('LOGINPLUS_BOX_INPUTBORDERCOLORFOCUS')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_SUBMITBACKGROUND',GETPOST('LOGINPLUS_BOX_SUBMITBACKGROUND')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_SUBMITBACKGROUNDHOVER',GETPOST('LOGINPLUS_BOX_SUBMITBACKGROUNDHOVER')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_SUBMITCOLOR',GETPOST('LOGINPLUS_BOX_SUBMITCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;        
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_LINKSCOLOR',GETPOST('LOGINPLUS_BOX_LINKSCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_SHOW_FORMLABELS',GETPOST('LOGINPLUS_SHOW_FORMLABELS')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_TWOFACTOR_DARKTHEME',GETPOST('LOGINPLUS_TWOFACTOR_DARKTHEME')?:'0','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_EXTERNALBACKGROUND',GETPOST('LOGINPLUS_BOX_EXTERNALBACKGROUND')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER',GETPOST('LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, 'LOGINPLUS_BOX_EXTERNALCOLOR',GETPOST('LOGINPLUS_BOX_EXTERNALCOLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;

        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); endif;        
    break;

    // SECONDARY BOX PARAMS
    case 'set_boxinfo':
        $showsidebox = (GETPOSTISSET('LOGINPLUS_SHOW_SECONDARYBOX') && GETPOST('LOGINPLUS_SHOW_SECONDARYBOX','aZ09') == 'on')?1:0;
        if(!dolibarr_set_const($db, "LOGINPLUS_SHOW_SECONDARYBOX",$showsidebox,'chaine',0,'',$conf->entity)): $error++; endif;
        
        $showsideboxshadow = (GETPOSTISSET('LOGINPLUS_SECONDARYBOX_SHADOW') && GETPOST('LOGINPLUS_SECONDARYBOX_SHADOW','aZ09') == 'on')?1:0;
        if(!dolibarr_set_const($db, "LOGINPLUS_SECONDARYBOX_SHADOW",$showsideboxshadow,'chaine',0,'',$conf->entity)): $error++; endif;

        if(!dolibarr_set_const($db, 'LOGINPLUS_IMAGE_COLOR',GETPOST('LOGINPLUS_IMAGE_COLOR')?:'#ffffff','chaine',0,'',$conf->entity)): $error++; endif;
        $background_image = $_FILES['ldo-imageside'];
        if($background_image['size'] > 0):
            if ($background_image['name'] && !preg_match('/(\.jpeg|\.jpg|\.png)$/i', $background_image['name'])):
                $error++;
                setEventMessages($langs->trans("ErrorBadFormat"), null, 'errors');
                break;
            endif;
            if (preg_match('/([^\\/:]+)$/i', $background_image['name'], $reg)):
                $dirforimage = $conf->medias->multidir_output[$conf->entity].'/loginplus';
                $nfilename = 'side_'.str_replace(' ', '_', $reg[1]);
                if (!is_dir($dirforimage)): dol_mkdir($dirforimage); endif;
                $result = dol_move_uploaded_file($background_image['tmp_name'], $dirforimage.'/'.$nfilename, 1, 0, $background_image['error']);
                if($result):
                    if(!dolibarr_set_const($db, "LOGINPLUS_SIDEBG_IMAGEKEY",$nfilename,'chaine',0,'',$conf->entity)): $error++; endif;
                else:
                    $error++;
                    setEventMessages($langs->trans("Error"), null, 'errors');
                    break;
                endif;
            endif;
        endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_IMAGE_OPACITY",GETPOST('LOGINPLUS_IMAGE_OPACITY')?:0,'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLE",trim(GETPOST('LOGINPLUS_TXT_TITLE')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_TITLECOLOR",GETPOST('LOGINPLUS_TXT_TITLECOLOR')?:'#000000','chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENT",trim(GETPOST('LOGINPLUS_TXT_CONTENT')),'chaine',0,'',$conf->entity)): $error++; endif;
        if(!dolibarr_set_const($db, "LOGINPLUS_TXT_CONTENTCOLOR",GETPOST('LOGINPLUS_TXT_CONTENTCOLOR')?:'#000000','chaine',0,'',$conf->entity)): $error++; endif;

        if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
        else: $db->rollback(); endif;
    break;

    // REMOVE IMAGE
    case 'removeimage':
        $imagetype = GETPOST('key','aZ09');
        if(!empty($imagetype) && !empty(getDolGlobalString($imagetype))):

            $filetodel = $conf->medias->multidir_output[$conf->entity].'/loginplus/'.getDolGlobalString($imagetype);
            if(file_exists($filetodel)):
                dol_delete_file($filetodel);
            endif;
            if(!dolibarr_set_const($db, $imagetype,'','chaine',0,'',$conf->entity)): $error++; endif;

            if(!$error):$db->commit(); setEventMessages($langs->trans('loginplus_optionp_success'), null, 'mesgs');
            else: $db->rollback(); 
            endif;
        endif;
    break;

    // ADD FORGOTTENPASSWORD TPL
    case 'copyforgetpwdtpl':

        // CHMOD
        dolChmod($template_forgetpwd_themefolder,'755');
        if(file_exists($template_forgetpwd_theme)):
            dolChmod($template_forgetpwd_theme,'644');
        endif;

        if (copy($template_forgetpwd_loginplus, $template_forgetpwd_theme)):
            setEventMessages($langs->trans('loginplus_AdminOtherCopyTemplateOk'), null, 'mesgs');
        else:
            setEventMessages($langs->trans('loginplus_AdminOtherCopyTemplateError'), null, 'errors');
        endif;
    break;

    // REMOVE FORGOTTENPASSWORD TPL
    case 'removeforgetpwdtpl':
        // CHMOD
        dolChmod($template_forgetpwd_themefolder,'755');
        if(file_exists($template_forgetpwd_theme)):
            dolChmod($template_forgetpwd_theme,'644');
        endif;

        if(unlink($template_forgetpwd_theme)): setEventMessages($langs->trans('loginplus_AdminOtherRemoveTemplateOk'), null, 'mesgs');
        else: setEventMessages($langs->trans('loginplus_AdminOtherRemoveTemplateError'), null, 'errors');
        endif;
    break;

endswitch;

// $form=new Form($db);
/***************************************************
* VIEW
****************************************************/

$array_js = array(
    '/loginplus/js/coloris.min.js',
);
$array_css = array(
    '/loginplus/css/coloris.min.css',
    '/loginplus/css/dolpgs.css',
);

llxHeader('',$langs->transnoentities('loginplus_optionp_title').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus setup'); ?>

<?php 
// FOR PREVIEW ONLY
$calc_radius = intval(getDolGlobalInt('LOGINPLUS_BOX_RADIUS')) / 2;
?>
<style type="text/css" id="loginplus-styles">
:root {
    --loginplus-bg-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BG_COLOR')); ?>;
    --loginplus-bg-imageopacity: <?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY') / 100; ?>;
    --loginplus-shape-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_SHAPE_COLOR')); ?>;
    --loginplus-box-background: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_BACKGROUND')); ?>;
    --loginplus-box-radius: <?php echo $calc_radius.'px'; ?>;
    --loginplus-box-iconcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_ICONCOLOR')); ?>;
    --loginplus-box-labelcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_LABELCOLOR')); ?>;
    --loginplus-box-inputcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_INPUTCOLOR')); ?>;
    --loginplus-box-inputcolorfocus: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_INPUTCOLORFOCUS')); ?>;
    --loginplus-box-submitbackground: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_SUBMITBACKGROUND')); ?>;
    --loginplus-box-submitcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_SUBMITCOLOR')); ?>;   
    --loginplus-box-submitbackgroundhover: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_SUBMITBACKGROUNDHOVER')); ?>;
    --loginplus-box-inputbackground: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_INPUTBACKGROUND')); ?>; 
    --loginplus-box-inputborder: <?php echo getDolGlobalInt('LOGINPLUS_BOX_INPUTBORDER').'px'; ?>;
    --loginplus-box-inputbordercolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_INPUTBORDERCOLOR')); ?>; 
    --loginplus-box-inputbordercolorfocus: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_INPUTBORDERCOLORFOCUS')); ?>; 
    --loginplus-box-linkscolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_LINKSCOLOR')); ?>;
    --loginplus-box-margin: <?php echo getDolGlobalInt('LOGINPLUS_BOX_MARGIN').'px'; ?>;
    --loginplus-image-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_IMAGE_COLOR')); ?>;
    --loginplus-txt-titlecolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_TXT_TITLECOLOR')); ?>;
    --loginplus-txt-contentcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_TXT_CONTENTCOLOR')); ?>;
    --loginplus-image-opacity: <?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY') / 100; ?>;
    --loginplus-box-externalbackground: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUND')); ?>;
    --loginplus-box-externalcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALCOLOR')); ?>;   
    --loginplus-box-externalbackgroundhover: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER')); ?>;
</style>

<div class="doladmin">

    <!--  -->
    <div id="doladmin-content">
        <?php 
        $linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
        print load_fiche_titre($langs->trans("loginplus_optionp_title"), $linkback, 'title_setup'); ?>

        <div class="doladmin-flex-wrapper" id="loginplusadmin-content">

            <!-- COL FOR MENU -->
            <div class="doladmin-col-menu">
                <?php echo lp_showAdminMenu('setup', $user); ?>
            </div>

            <!-- COL FOR PARAMS -->
            <div class="doladmin-col-params">

                <div class="doladmin-card with-topmenu">

                    <?php $loginmenu = array(
                        array('optiontype' => 'background', 'icon' => 'fas fa-fill-drip','title' => $langs->trans('loginplus_AdminBackgroundStepTitle'),'description' => $langs->trans('loginplus_AdminBackgroundStepDesc')),
                        array('optiontype' => 'template', 'icon' => 'fas fa-columns','title' => $langs->trans('loginplus_AdminTemplateStepTitle'),'description' => $langs->trans('loginplus_AdminTemplateStepDesc')),
                        array('optiontype' => 'box', 'icon' => 'fas fa-unlock-alt','title' => $langs->trans('loginplus_AdminLoginBoxStepTitle'),'description' => $langs->trans('loginplus_AdminLoginBoxStepDesc')),
                        array('optiontype' => 'boxinfo', 'icon' => 'fas fa-info-circle','title' => $langs->trans('loginplus_AdminBoxInfoStepTitle'),'description' => $langs->trans('loginplus_AdminBoxInfoStepDesc')),
                        array('optiontype' => 'other', 'icon' => 'fas fa-cog','title' => $langs->trans('loginplus_AdminOtherStepTitle'),'description' => $langs->trans('loginplus_AdminOtherStepDesc')),
                    ); ?>
                    <nav class="doladmin-card-topmenu">
                        <ul>
                            <?php foreach ($loginmenu as $menukey => $menudet): ?>
                                <li class="<?php echo ($optiontype == $menudet['optiontype'])?'active':''; ?>">
                                    <a href="<?php echo dol_buildpath('loginplus/admin/setup.php?optiontype='.$menudet['optiontype'],1); ?>">
                                        <i class="<?php echo $menudet['icon']; ?>"></i> <?php echo $menudet['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>

                    <?php // PARAMS FOR BACKGROUND - OK ?>
                    <?php if($optiontype == 'background'): ?>
                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminBackgroundStepTitle'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminBackgroundStepLongDesc'); ?></p>
                    <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">

                        <!-- PREVIEW BACKGROUND -->
                        <?php echo $loginplus_static->preview(getDolGlobalString('LOGINPLUS_TEMPLATE'),$mysoc,1); ?> 

                        <!-- FORM BACKGROUND -->
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">

                            <input type="hidden" name="action" value="set_background">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                            <input type="hidden" name="optiontype" value="background">

                            <table class="doladmin-table-simple">                                
                                <tbody>
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminBackgroundStepTitle'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_background_color').' '.img_info($langs->trans('loginplus_option_background_color_desc')); ?></td>
                                        <td class="right">
                                            <input type="text" class="preview-input coloris" data-property="--loginplus-bg-color" name="LOGINPLUS_BG_COLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BG_COLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_background_image').' '.img_info($langs->trans('loginplus_option_background_image_desc')); ?></td>
                                        <td class="right">                                            
                                            <?php if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))): ?>
                                                <span class="doladmin-selectedfile paddingright" >
                                                    <span class="sf-label paddingright"><?php echo getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'); ?></span>
                                                    <a class="sf-action" href="<?php echo $_SERVER["PHP_SELF"].'?action=removeimage&key=LOGINPLUS_BG_IMAGEKEY&optiontype='.$optiontype.'&token='.newToken(); ?>"><i class="fas fa-trash"></i></a>
                                                </span>
                                            <?php else: ?>
                                                <input type="hidden" name="LOGINPLUS_BG_IMAGEOPACITY" value="<?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY'); ?>">
                                            <?php endif; ?>
                                            <input type="file" name="ldo-bg-image" accept="image/*">
                                        </td>
                                    </tr>
                                    <?php if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))): ?>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_background_image_opacity').' '.img_info($langs->trans('loginplus_option_background_image_opacity_desc')); ?></td>
                                        <td class="right">
                                            <input type="range" class="loginplus-rangeslider preview-input" name="LOGINPLUS_BG_IMAGEOPACITY" min="0" max="100" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY'); ?>" data-slidervalue="#ldo-bg-imageopacity" data-unit="%" data-slideroption="divide|100" data-property="--loginplus-bg-imageopacity">
                                            <span class="loginplus-rangevalue" id="ldo-bg-imageopacity"><?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY'); ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;"><i class="fas fa-cog paddingright"></i> Forme d'arrière-plan</td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_shape_path'); ?></td>
                                        <td class="right parentonrightofpage">
                                            <?php 
                                            $shapelist = array();
                                            foreach($loginplus_static->shapes as $shapekey => $shapeinfos): 
                                                $shapelist[$shapekey] = array(
                                                    'label' => $langs->trans('loginplus_shape_'.$shapekey),
                                                    'data-type' => $shapeinfos['type'],
                                                );
                                            endforeach;
                                            echo $form->selectarray('LOGINPLUS_SHAPE_PATH',$shapelist,getDolGlobalString('LOGINPLUS_SHAPE_PATH'),1,0,0,'',0,0,0,'','minwidth300');
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_OptionModeAlt'); ?></td>
                                        <td class="right">
                                            <input type="checkbox" name="LOGINPLUS_SHAPE_ALT" value="on" <?php if(getDolGlobalInt('LOGINPLUS_SHAPE_ALT')): echo 'checked="checked"'; endif; ?>>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_option_shape_color'); ?></td>
                                        <td class="right">
                                            <input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-shape-color" name="LOGINPLUS_SHAPE_COLOR" value="<?php echo getDolGlobalString('LOGINPLUS_SHAPE_COLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="doladmin-form-buttons right">
                                <input type="submit" name="">
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php // PARAMS FOR TEMPLATE ?>
                    <?php if($optiontype == 'template'): ?>
                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminTemplateStepTitle'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminTemplateStepLongDesc'); ?></p>
                    <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">

                        <!-- PREVIEW BACKGROUND -->
                        <?php echo $loginplus_static->preview(getDolGlobalString('LOGINPLUS_TEMPLATE'),$mysoc); ?> 

                        <!-- FORM BACKGROUND -->
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">

                            <input type="hidden" name="action" value="set_template">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                            <input type="hidden" name="optiontype" value="template">

                            <table class="doladmin-table-simple">                                
                                <tbody>
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2" ><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminTemplateStepTitle'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminStructureStepTitle'); ?></td>
                                        <td class="right">
                                            <?php
                                            $array_templates = array();
                                            foreach($loginplus_static->templates as $tpl_key => $tpl_infos) :
                                                $array_templates[$tpl_key] = $langs->transnoentities($tpl_infos['langkey']);
                                            endforeach; 
                                            echo $form->selectarray('LOGINPLUS_TEMPLATE',$array_templates,getDolGlobalString('LOGINPLUS_TEMPLATE'),0,0,0,'',0,0,0,'','minwidth200'); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateThreeSide'); ?></td>
                                        <td class="right">
                                            <?php 
                                            $array_align = array('left' => $langs->trans('Left'),'center' => $langs->trans('loginplus_AdminMiddle'),'right' => $langs->trans('Right'));
                                            echo $form->selectarray('LOGINPLUS_BOX_ALIGN',$array_align,getDolGlobalString('LOGINPLUS_BOX_ALIGN'),0,0,0,'',0,0,0,'','minwidth200'); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxRadius'); ?></td>
                                        <td class="right">
                                            <input type="range" class="loginplus-rangeslider preview-input" name="LOGINPLUS_BOX_RADIUS" min="0" max="42" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_BOX_RADIUS'); ?>" data-slidervalue="#ldo-box-radius" data-unit="px" data-suffix="px" data-slideroption="divide|2" data-property="--loginplus-box-radius">
                                            <span class="loginplus-rangevalue" id="ldo-box-radius"><?php echo getDolGlobalInt('LOGINPLUS_BOX_RADIUS'); ?>px</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminShowSecondaryBox'); ?></td>
                                        <td class="right"><input type="checkbox" name="LOGINPLUS_SHOW_SECONDARYBOX" value="on" <?php if(getDolGlobalInt('LOGINPLUS_SHOW_SECONDARYBOX')): echo 'checked="checked"'; endif; ?>></td>
                                    </tr>
                                </tbody>
                                <tbody class="params-for-template params-template_two">
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2" style="padding-top:36px;"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminLoginBoxTemplateParams'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateThreeWidth'); ?></td>
                                        <td class="right">
                                            <?php
                                            $array_boxsize = array( 0 => $langs->trans('loginplus_AdminLoginBoxSize0'), 1 => $langs->trans('loginplus_AdminLoginBoxSize1'));
                                            echo $form->selectarray('LOGINPLUS_BOX_WIDTH',$array_boxsize,getDolGlobalInt('LOGINPLUS_BOX_WIDTH'),0,0,0,'',0,0,0,'','minwidth200'); ?>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateThreeMargin'); ?></td>
                                        <td class="right">
                                            <input type="range" class="loginplus-rangeslider preview-input" name="LOGINPLUS_BOX_MARGIN" min="0" max="42" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_BOX_MARGIN'); ?>" data-slidervalue="#ldo-box-margin" data-unit="px" data-suffix="px" data-property="--loginplus-box-margin">
                                            <span class="loginplus-rangevalue" id="ldo-box-margin"><?php echo getDolGlobalInt('LOGINPLUS_BOX_MARGIN'); ?>px</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="doladmin-form-buttons right">
                                <input type="submit" name="">
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    

                    <?php // PARAMS FOR LOGIN BOX ?>
                    <?php if($optiontype == 'box'): ?>
                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminLoginBoxStepTitle'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminLoginBoxStepLongDesc'); ?></p>
                    <div class="doladmin-card-content">

                        <!-- PREVIEW LOGIN BOX -->
                        <?php echo $loginplus_static->preview(getDolGlobalString('LOGINPLUS_TEMPLATE'),$mysoc,0); ?>
                        <p class="opacitymedium"><?php echo $langs->trans('loginplus_AdminLoginBoxStepLongDesc'); ?></p>
                        
                        <!-- FORM LOGIN BOX -->
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">

                            <input type="hidden" name="action" value="set_loginbox">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                            <input type="hidden" name="optiontype" value="box">

                            <table class="doladmin-table-simple" id="loginbox-table">
                                
                                <tbody>
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminLoginBoxStepTitle'); ?></td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxBackground'); ?></td>
                                        <td class="right">
                                            <input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-box-background" name="LOGINPLUS_BOX_BACKGROUND" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_BACKGROUND'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                    <?php if(isset($conf->modules['twofactorauth'])): ?>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTwoFactorTheme'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <input type="radio" id="lightheme" name="LOGINPLUS_TWOFACTOR_DARKTHEME" value="0" <?php if(!getDolGlobalInt('LOGINPLUS_TWOFACTOR_DARKTHEME')): echo 'checked="checked"'; endif; ?>>
                                                <label for="lightheme"><?php echo $langs->trans('loginplus_AdminLoginBoxTwoFactorLightTheme'); ?></label>
                                                <span class="paddingright"></span>
                                                <input type="radio" id="darktheme" name="LOGINPLUS_TWOFACTOR_DARKTHEME" value="1" <?php if(getDolGlobalInt('LOGINPLUS_TWOFACTOR_DARKTHEME')): echo 'checked="checked"'; endif; ?>>
                                                <label for="darktheme"><?php echo $langs->trans('loginplus_AdminLoginBoxTwoFactorDarkTheme'); ?></label>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminLoginBoxForm'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxLogoAlt').' '.img_info($langs->trans('loginplus_AdminLoginBoxLogoAltDesc')); ?></td>
                                        <td class="right">
                                            <?php if(!empty(getDolGlobalString('LOGINPLUS_LOGOALT'))): ?>
                                                <span class="doladmin-selectedfile paddingright" >
                                                    <span class="sf-label paddingright"><?php echo getDolGlobalString('LOGINPLUS_LOGOALT'); ?></span>
                                                    <a class="sf-action" href="<?php echo $_SERVER["PHP_SELF"].'?action=removeimage&key=LOGINPLUS_LOGOALT&optiontype='.$optiontype.'&token='.newToken(); ?>"><i class="fas fa-trash"></i></a>
                                                </span>
                                            <?php endif; ?>
                                            <input type="file" name="ldo-logo-alt" accept="image/*">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormShowLabel'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <?php 
                                                $array_showlabel = array(0 => $langs->trans('loginplus_AdminLoginBoxFormShowLabelIcon'),1 => $langs->trans('loginplus_AdminLoginBoxFormShowLabelFull'));
                                                echo $form->selectarray('LOGINPLUS_SHOW_FORMLABELS',$array_showlabel,getDolGlobalString('LOGINPLUS_SHOW_FORMLABELS'),0,0,0,'',0,0,0,'','minwidth200'); ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormLabelColor'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormLabelColorIcon')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-iconcolor" name="LOGINPLUS_BOX_ICONCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_ICONCOLOR'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormLabelColorText')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-labelcolor" name="LOGINPLUS_BOX_LABELCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_LABELCOLOR'); ?>" data-coloris></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormInputColor'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormInputColorOut')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-inputcolor" name="LOGINPLUS_BOX_INPUTCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_INPUTCOLOR'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormInputColorFocus')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-inputcolorfocus" name="LOGINPLUS_BOX_INPUTCOLORFOCUS" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_INPUTCOLORFOCUS'); ?>" data-coloris></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormInputBorderColor'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormInputBorderColorOut')); ?></span><input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-box-inputbordercolor" name="LOGINPLUS_BOX_INPUTBORDERCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_INPUTBORDERCOLOR'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormInputBorderColorFocus')); ?></span><input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-box-inputbordercolorfocus" name="LOGINPLUS_BOX_INPUTBORDERCOLORFOCUS" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_INPUTBORDERCOLORFOCUS'); ?>" data-coloris></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- <tr>
                                        <td class="bold">Taille border</td>
                                        <td class="right">
                                            <input type="range" class="loginplus-rangeslider preview-input" name="LOGINPLUS_BOX_INPUTBORDER" min="0" max="2" step="1" value="<?php //echo getDolGlobalInt('LOGINPLUS_BOX_INPUTBORDER'); ?>" data-slidervalue="#ldo-box-inputborder" data-unit="px" data-suffix="px" data-property="--loginplus-box-inputborder">
                                            <span class="loginplus-rangevalue" id="ldo-box-inputborder"><?php // echo getDolGlobalInt('LOGINPLUS_BOX_INPUTBORDER'); ?>px</span>
                                        </td>
                                    </tr> -->                                    
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormSubmitColor'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorBackground')); ?></span><input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-box-submitbackground" name="LOGINPLUS_BOX_SUBMITBACKGROUND" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_SUBMITBACKGROUND'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorText')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-submitcolor" name="LOGINPLUS_BOX_SUBMITCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_SUBMITCOLOR'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorHover')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-submitbackgroundhover" name="LOGINPLUS_BOX_SUBMITBACKGROUNDHOVER" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_SUBMITBACKGROUNDHOVER'); ?>" data-coloris></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxFormExternalColor'); ?></td>
                                        <td class="right">
                                            <div class="doladmin-flex-wrapper wrap end">
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorBackground')); ?></span><input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-box-externalbackground" name="LOGINPLUS_BOX_EXTERNALBACKGROUND" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUND'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorText')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-externalcolor" name="LOGINPLUS_BOX_EXTERNALCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_EXTERNALCOLOR'); ?>" data-coloris></div>
                                                <div><span class="color-label"><?php echo img_info($langs->trans('loginplus_AdminLoginBoxFormSubmitColorHover')); ?></span><input type="text" class="preview-input coloris" data-property="--loginplus-box-externalbackgroundhover" name="LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER'); ?>" data-coloris></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxLinksColor'); ?></td>
                                        <td class="right">
                                            <input type="text" class="preview-input coloris" data-property="--loginplus-box-linkscolor" name="LOGINPLUS_BOX_LINKSCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_BOX_LINKSCOLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                </tbody>
                                
                            </table>

                            <div class="doladmin-form-buttons right">
                                <input type="submit" name="" >
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php // PARAMS FOR BOXINFO ?>
                    <?php if($optiontype == 'boxinfo'): ?>
                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminBoxInfoStepTitle'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminBoxInfoStepLongDesc'); ?></p>
                    <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">

                        <!-- PREVIEW BOXINFO -->
                        <?php echo $loginplus_static->preview(getDolGlobalString('LOGINPLUS_TEMPLATE'),$mysoc); ?> 

                        <!-- FORM BOXINFO -->
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">

                            <input type="hidden" name="action" value="set_boxinfo">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                            <input type="hidden" name="optiontype" value="boxinfo">

                            <table class="doladmin-table-simple">                               
                                
                                <tbody>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminShowSecondaryBox'); ?></td>
                                        <td class="right"><input type="checkbox" name="LOGINPLUS_SHOW_SECONDARYBOX" value="on" <?php if(getDolGlobalInt('LOGINPLUS_SHOW_SECONDARYBOX')): echo 'checked="checked"'; endif; ?>></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateTwoBackgroundSide'); ?></td>
                                        <td class="right">
                                            <input type="text" class="preview-input coloris color-alpha" data-property="--loginplus-image-color" name="LOGINPLUS_IMAGE_COLOR" value="<?php echo getDolGlobalString('LOGINPLUS_IMAGE_COLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateTwoImageSide'); ?></td>
                                        <td class="right">
                                            <?php if(!empty(getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY'))): ?>
                                                <span class="doladmin-selectedfile paddingright" >
                                                    <span class="sf-label paddingright"><?php echo getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY'); ?></span>
                                                    <a class="sf-action" href="<?php echo $_SERVER["PHP_SELF"].'?action=removeimage&key=LOGINPLUS_SIDEBG_IMAGEKEY&optiontype='.$optiontype.'&token='.newToken(); ?>"><i class="fas fa-trash"></i></a>
                                                </span>
                                            <?php else: ?>
                                                <input type="hidden" name="LOGINPLUS_IMAGE_OPACITY" value="<?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY'); ?>" >
                                            <?php endif; ?>
                                            <input type="file" name="ldo-imageside" accept="image/*">
                                        </td>
                                    </tr>
                                    <?php if(!empty(getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY'))): ?>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateTwoImageSideOpacity'); ?></td>
                                        <td class="right">
                                            <input type="range" class="loginplus-rangeslider preview-input" name="LOGINPLUS_IMAGE_OPACITY" min="0" max="100" step="1" value="<?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY'); ?>" data-slidervalue="#ldo-bg-sideimageopacity" data-unit="%" data-slideroption="divide|100" data-property="--loginplus-image-opacity">
                                            <span class="loginplus-rangevalue" id="ldo-bg-sideimageopacity"><?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY'); ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminSecondaryBoxShadow'); ?></td>
                                        <td class="right"><input type="checkbox" name="LOGINPLUS_SECONDARYBOX_SHADOW" value="on" <?php if(getDolGlobalInt('LOGINPLUS_SECONDARYBOX_SHADOW')): echo 'checked="checked"'; endif; ?>></td>
                                    </tr>

                                    <tr>
                                        <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminSecondaryTextParams'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateTwoTitle'); ?></td>
                                        <td class="right">
                                            <input type="text" name="LOGINPLUS_TXT_TITLE" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_TITLE'); ?>" class="minwidth300 textpreview" data-texttarget=".preview-title">
                                            <input type="text" class="preview-input color-alpha coloris" data-property="--loginplus-txt-titlecolor" name="LOGINPLUS_TXT_TITLECOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_TITLECOLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo $langs->trans('loginplus_AdminLoginBoxTemplateTwoContent'); ?></td>
                                        <td class="right">
                                            <input type="text" name="LOGINPLUS_TXT_CONTENT" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_CONTENT'); ?>" class="minwidth300 textpreview" data-texttarget=".preview-content">
                                            <input type="text" class="preview-input color-alpha coloris" data-property="--loginplus-txt-contentcolor" name="LOGINPLUS_TXT_CONTENTCOLOR" value="<?php echo getDolGlobalString('LOGINPLUS_TXT_CONTENTCOLOR'); ?>" data-coloris>
                                        </td>
                                    </tr>
                                </tbody>
                                
                            </table>
                            <div class="doladmin-form-buttons right">
                                <input type="submit" name="">
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php // PARAMS FOR OTHERS ?>
                    <?php if($optiontype == 'other'): ?>
                    <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminOtherStepTitle'); ?></div>
                    <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminOtherStepLongDesc'); ?></p>
                    <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">

                        <!-- FORM OTHERS -->
                        <table class="doladmin-table-simple doladmin-form">                                
                            <tbody>
                                <tr>
                                    <td class="doladmin-table-subtitle" colspan="2"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_AdminOtherForcePasswordForgottenTplTitle'); ?></td>
                                </tr>
                                <tr>
                                    <td class="bold"><?php echo $langs->trans('loginplus_AdminOtherForcePasswordForgottenTpl',$conf->theme); ?></td>
                                    <td class="right doladmin-form-buttons">
                                        <a class="" href="<?php echo $_SERVER['PHP_SELF'].'?optiontype=other&action=copyforgetpwdtpl&token='.newToken(); ?>"><?php echo $langs->trans('loginplus_AdminOtherForcePasswordForgottenTplLink'); ?></a>
                                    </td>
                                </tr>
                                <?php if(file_exists($template_forgetpwd_theme)): ?>
                                <tr>
                                    <td class="bold"><?php echo $langs->trans('loginplus_AdminOtherUnlinkPasswordForgottenTpl',$conf->theme); ?></td>
                                    <td class="right doladmin-form-buttons">
                                        <a class="" href="<?php echo $_SERVER['PHP_SELF'].'?optiontype=other&action=removeforgetpwdtpl&token='.newToken(); ?>"><?php echo $langs->trans('loginplus_AdminOtherUnlinkPasswordForgottenTplLink'); ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!--  -->
    <!-- <div id="doladmin-sidebar" class="side-hidden">
        <div class="sidebar-header">        
            <div class="sidebar-title"><i class="fas fa-home paddingright"></i> Title</div>
            <div class="sidebar-close"><i class="fas fa-times"></i></div>
        </div>
        <div class="opacitymedium" style="">Pellentesque habitant morbi tristique senectus et netus. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper.</div>
    </div> -->

</div>
<script>
    
    /* SLIDERS*/
    class doladminSlider {
        constructor(slider){

            // VARS
            this.slider = slider; 
            this.carousel = this.slider.querySelector('.doladmin-slides');
            this.step = parseInt(this.carousel.dataset.step);
            this.item = this.slider.querySelector('.doladmin-slide');
            this.nb_items = this.carousel.getElementsByClassName('doladmin-slide').length;
            this.control_prev = this.slider.querySelector('.control-prev');
            this.control_next = this.slider.querySelector('.control-next');
            this.bullets = this.slider.querySelector('.control-bullets');
            this.bullets_list = this.bullets.querySelectorAll('li');
            this.inputfield = ('inputfield' in this.slider.dataset)?document.querySelector('input[name="' + this.slider.dataset.inputfield + '"]'):'';
            this.calcwidth = this.item.clientWidth;
            if(slider.classList.contains('with-gap')){
                this.calcwidth += parseFloat(getComputedStyle(this.carousel).gap.replace(/\D/g, ""));
            }

            // On place correctement le slider
            this.carousel.scrollLeft = (this.step - 1) * this.calcwidth;

            // Bullets Listener
            this.bullets.addEventListener('click', function (event) {
                if(this.step != parseInt(event.target.dataset.slide) && event.target.nodeName == 'LI'){
                    var x = parseInt(event.target.dataset.slide) - 1;
                    this.bullets_list.forEach(function (li){
                       li.classList.remove('active');
                    });
                    this.bullets_list[x].classList.add('active');
                    this.carousel.scrollLeft = x * this.calcwidth;
                    this.step = parseInt(event.target.dataset.slide);
                    this.carousel.dataset.step = this.step;

                    //
                    var oldslide = this.carousel.querySelector('.doladmin-slide.active');
                    if(oldslide !== null){oldslide.classList.remove('active');}
                    var activeslide = this.carousel.querySelector('.doladmin-slide[data-step="'+this.step+'"]');
                    activeslide.classList.add('active');

                    if(this.inputfield.length !== 0){
                        this.inputfield.value = activeslide.dataset.inputvalue;
                    }
                }       
            }.bind(this));
            // PREV Control listener
            this.control_prev.addEventListener('click', function (e) {        
                if(this.step > 1){

                    var currbullet = parseInt(this.step) - 1;
                    var nextbullet = currbullet - 1;
                    this.bullets_list[currbullet].classList.remove('active');
                    this.bullets_list[nextbullet].classList.add('active');
                    this.carousel.scrollLeft -= this.calcwidth;
                    this.step -= 1;
                    this.carousel.dataset.step = this.step;

                    var oldslide = this.carousel.querySelector('.doladmin-slide.active');
                    if(oldslide !== null){oldslide.classList.remove('active');}
                    var activeslide = this.carousel.querySelector('.doladmin-slide[data-step="'+this.step+'"]');
                    activeslide.classList.add('active');
                    if(this.inputfield.length !== 0){
                        this.inputfield.value = activeslide.dataset.inputvalue;
                    }

                }
            }.bind(this));
            // NEXT Control listener
            this.control_next.addEventListener('click', function (e) {        
                if(this.step < this.nb_items){
                    var currbullet = parseInt(this.step) - 1;
                    var nextbullet = parseInt(this.step);
                    this.bullets_list[currbullet].classList.remove('active');
                    this.bullets_list[nextbullet].classList.add('active');
                    this.carousel.scrollLeft += this.calcwidth;
                    this.step += 1;
                    this.carousel.dataset.step = this.step;
                    var oldslide = this.carousel.querySelector('.doladmin-slide.active');
                    if(oldslide !== null){oldslide.classList.remove('active');}
                    var activeslide = this.carousel.querySelector('.doladmin-slide[data-step="'+this.step+'"]');
                    activeslide.classList.add('active');
                    if(this.inputfield.length !== 0){
                        this.inputfield.value = activeslide.dataset.inputvalue;
                    }
                }
            }.bind(this));
        }
    }

    let doladminSliders = document.querySelectorAll('.doladmin-slider');
    if(doladminSliders.length > 0){
        doladminSliders.forEach(function (slider){
            new doladminSlider(slider);
        });
    }
    
    /* PREVIEW */
    let preview_inputs = document.querySelectorAll('.preview-input');
    if(preview_inputs.length > 0){
        preview_inputs.forEach(function (previewInput){
            previewInput.addEventListener('input', function (a) {
            
                // value                
                var v = previewInput.value;
                /*
                var source = previewInput.dataset.source;
                if(source !== undefined){
                    var sourceinput = document.querySelector('input[name="'+ source +'"]');
                    var v = sourceinput.value;
                }
                */

                var t = previewInput.dataset.target;
                var p = previewInput.dataset.property;

                if(p !== undefined){
                    if(t !== undefined){
                        var zz = document.querySelector(t);
                        zz.style.setProperty(p,v);
                    } else {

                        var lpstyle = document.getElementById('loginplus-styles');
                        var newstyle = '\n';

                        var lpdata = lpstyle.childNodes[0].data;
                        var lpdata_list = lpdata.split('\n');

                        lpdata_list.forEach(function (data_element){

                            data_element =  data_element.replace(/\s/g, '');
                            var elem = data_element.split(':');

                            if(elem[0] == p){

                                var slideroption = previewInput.dataset.slideroption;
                                if(slideroption !== undefined && slideroption !== ''){

                                    var splitoption = slideroption.split('|');
                                    if(splitoption[0] == 'divide'){
                                        v = parseInt(v) / parseInt(splitoption[1]);
                                    } else if (splitoption[0] == 'multiply'){
                                        v = parseInt(v) * parseInt(splitoption[1]);
                                    }                                    
                                }
                                newstyle += elem[0]+':'+v;

                                var suffix = previewInput.dataset.suffix;
                                if(suffix !== undefined){
                                    newstyle += suffix;
                                }

                                newstyle += ';\n';


                            } else {
                                if(elem[0] !== '' && elem[0] !== '}'){
                                    newstyle += elem[0]+':'+elem[1]+'\n';
                                }
                            }

                        });

                        lpstyle.innerHTML = ':root{'+newstyle+'}';

                        // fallbackid - MUST BE AN ID
                        var fallbackid = previewInput.dataset.fallbackid;
                        if(fallbackid !== undefined){
                            var setvalueradio = document.querySelector('#'+fallbackid);
                            setvalueradio.value = v;
                        }
                    }
                }
            });            
        });
    }

    /**/
    let inputrangelist = document.querySelectorAll('input[type="range"]');
    if(inputrangelist.length > 0){
        inputrangelist.forEach(function (inputrange){
            inputrange.addEventListener('input', function (a){
                var xx = document.querySelector(inputrange.dataset.slidervalue);
                var ea = inputrange.value;
                xx.innerHTML = ea+inputrange.dataset.unit;
            });
        });
    }
    

    /***************/    
    let previewglobalwrapper = document.querySelector('.preview-global-wrapper');
    let previewwrapper = document.querySelector('.preview-wrapper');
    let shapewrapper = document.querySelector('.preview-shape');
    let previewlogin = document.querySelector('.preview-boxlogin');

    // Mode alternatif
    let shapealt_checkbox = document.querySelector('input[name="LOGINPLUS_SHAPE_ALT"]');
    if(shapealt_checkbox !== null && shapealt_checkbox !== undefined){
        shapealt_checkbox.addEventListener('change', function (a){
            if(shapealt_checkbox.checked){
                shapewrapper.classList.add('alternate');
            } else {
                shapewrapper.classList.remove('alternate');
            }
        });
    }

    // BOITE SECONDAIRE
    let secondbox_checkbox = document.querySelector('input[name="LOGINPLUS_SHOW_SECONDARYBOX"]');
    if(secondbox_checkbox !== null && secondbox_checkbox !== undefined){
        secondbox_checkbox.addEventListener('change', function (a){
            if(secondbox_checkbox.checked){
                previewglobalwrapper.classList.add('show-secondary');
            } else {
                previewglobalwrapper.classList.remove('show-secondary');
            }
        });
    }

    // SHADOW
    let loginshadow = document.querySelector('input[name="LOGINPLUS_SECONDARYBOX_SHADOW"]');
    if(loginshadow !== null && loginshadow !== undefined){
        loginshadow.addEventListener('change', function (a){

            console.log('red');
            if(loginshadow.checked){
                previewlogin.classList.add('with-shadow');
            } else {
                previewlogin.classList.remove('with-shadow');
            }
        });
    }


    // TEXT PREVIEW
    let inputtextlist = document.querySelectorAll('.textpreview');
    if(inputtextlist.length > 0){
        inputtextlist.forEach(function (inputtext){
            inputtext.addEventListener('input', function (a){
                var itarget = document.querySelector(inputtext.dataset.texttarget);
                itarget.innerHTML = inputtext.value;
            });
        });
    }

    // SELECT2 NEEDS JQUERY
    $(function() {

        var jpreviewglobalwrapper = $('.preview-global-wrapper');
        var jpreviewwrapper = $('.preview-wrapper');

        // Modification preview SELECT2 - SHAPE
        var is_shapeselect = $('select[name="LOGINPLUS_SHAPE_PATH"]');
        if(is_shapeselect.length > 0){
            let currentshapeinfo = $('select[name="LOGINPLUS_SHAPE_PATH"]').select2('data');
            let currentshapetype = currentshapeinfo[0].element.dataset.type;
            $('select[name="LOGINPLUS_SHAPE_PATH"]').on('change', function (e) {

                var newshapeclass = $(this).val();
                var newshapeinfo = $(this).select2('data');
                var newshapetype = newshapeinfo[0].element.dataset.type;
                

                //
                shapewrapper.innerHTML = '';
                shapewrapper.classList.remove(currentshapeinfo[0].id);
                if(currentshapetype === 'clip'){shapewrapper.classList.remove('shape-clip');} 
                else if(currentshapetype === 'svg'){shapewrapper.classList.remove('shape-svg');}

                if (newshapetype !== undefined && newshapetype === 'clip'){

                    shapewrapper.classList.add('shape-clip');
                    shapewrapper.classList.add(newshapeclass);
                    currentshapeinfo = newshapeinfo;
                    currentshapetype = newshapetype;

                } else if (newshapetype !== undefined && newshapetype === 'svg'){                
                    shapewrapper.classList.add('shape-svg');
                    shapewrapper.classList.add(newshapeclass);
                    currentshapeinfo = newshapeinfo;
                    currentshapetype = newshapetype;

                    fetch('../svg/'+newshapeclass+'.svg')
                    .then(response => response.text())
                    .then(data => {shapewrapper.innerHTML = data;})
                    .catch(error => {console.error('Error : ', error);});
                } else {}
            });
        }

        // Modification preview SELECT2 - BOX ALIGN
        var loginboxAlign = $('select[name="LOGINPLUS_BOX_ALIGN"]');
        if(loginboxAlign.length > 0){
            loginboxAlign.on('change', function (e) {

                if(jpreviewglobalwrapper.hasClass('box-left')){jpreviewglobalwrapper.removeClass('box-left');}
                if(jpreviewglobalwrapper.hasClass('box-center')){jpreviewglobalwrapper.removeClass('box-center');}
                if(jpreviewglobalwrapper.hasClass('box-right')){jpreviewglobalwrapper.removeClass('box-right');}
                jpreviewglobalwrapper.addClass('box-'+loginboxAlign.val());
            });
        }

        // Modification preview SELECT2 - BOX ALIGN
        var loginboxWidth = $('select[name="LOGINPLUS_BOX_WIDTH"]');
        if(loginboxWidth.length > 0){
            loginboxWidth.on('change', function (e) {

                if(parseInt(loginboxWidth.val()) == 0){
                    jpreviewwrapper.removeClass('w2');
                } else {
                    jpreviewwrapper.addClass('w2');
                }

            });
        }

        // Modification preview SELECT2 - LABELS
        var is_labels = $('select[name="LOGINPLUS_SHOW_FORMLABELS"]');
        if(is_labels.length > 0){
            is_labels.on('change', function (e) {

                if(parseInt(is_labels.val()) == 0){
                    $('.preview-fields').removeClass('loginplus-viewlabel');
                } else if (parseInt(is_labels.val()) == 1){
                    $('.preview-fields').addClass('loginplus-viewlabel');
                }
            });
        }

        // Modification preview SELECT2 - TEMPLATE
        var loginTpl = $('select[name="LOGINPLUS_TEMPLATE"]');
        if(loginTpl.length > 0){

            $('.params-'+loginTpl.val()).css('display','table-row-group');
            loginTpl.on('change', function (e) {
                if(jpreviewglobalwrapper.hasClass('template_one')){ jpreviewglobalwrapper.removeClass('template_one');}
                if(jpreviewglobalwrapper.hasClass('template_two')){ jpreviewglobalwrapper.removeClass('template_two');}
                $('.params-for-template').css('display','none');
                jpreviewglobalwrapper.addClass(loginTpl.val());                
                $('.params-'+loginTpl.val()).css('display','table-row-group');
            });
        }
    });

    // COLORIS
    Coloris({
        format: 'hex',
        alpha: false,
        theme: 'polaroid',
    });
    Coloris.setInstance('.color-alpha', { alpha: true });

    //
    



</script>

<?php llxFooter(); $db->close(); ?>