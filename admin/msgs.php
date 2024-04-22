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

dol_include_once('./loginplus/class/loginmsg.class.php');
dol_include_once('./loginplus/lib/loginplus.lib.php');

// Protection if external user
if ($user->socid > 0): accessforbidden(); endif;
if (!$user->rights->loginplus->gerer_messages): accessforbidden(); endif;

/*******************************************************************
* VARIABLES
********************************************************************/

$action = GETPOST('action');
$loginmsg = new loginMsg($db);
$msg_user = new User($db);

$form = new Form($db);
$error = 0;

/*******************************************************************
* ACTIONS
********************************************************************/

switch ($action):

    // PREPARER MSG
    case 'prepare_newmsg':
        
        // VERIFS
        if(GETPOST('token') == $_SESSION['token']):        
            $type_destinataire = GETPOST('newmsg_typeto','alpha');
            if(empty($type_destinataire)): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyType'), null, 'errors'); 
            else:
                switch ($type_destinataire):
                    case 'all': $destlist = $type_destinataire; break;
                    case 'groups': $destlist = $loginmsg->get_usersGroups();break;
                    case 'tags': $destlist = $form->select_all_categories('user', '', 'parent', 64, 0, 1); break;
                    case 'users': $destlist = $form->select_dolusers(0,'',0,'',0,'',1,$conf->entity,0,0,'',0,'','',1,1);break;
                endswitch;
            endif;       

        else: $error++;setEventMessages("SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry", null, 'warnings');
        endif;
    break;

    // AJOUTER MSG
    case 'add_newmsg':

        if(GETPOST('token') == $_SESSION['token']):
        
            $type_destinataire = GETPOST('newmsg_typeto','alpha');
            if(empty(GETPOST('newmsg_label','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyTitle'), null, 'errors'); endif;
            if(empty(GETPOST('newmsg_message','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyMsg'), null, 'errors'); endif;
            if(GETPOSTISSET('newmsg_forceview') && empty(GETPOST('newmsg_datexp','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDate'), null, 'errors'); endif;
            if(in_array($type_destinataire, array('groups','users','tags'))):
                if(!GETPOSTISSET('newmsg_destinataire')): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDest'), null, 'errors'); endif;
            endif;
            
            switch ($type_destinataire):
                case 'all': $destlist = $type_destinataire; break;
                case 'groups': $destlist = $loginmsg->get_usersGroups();break;
                case 'tags': $destlist = $form->select_all_categories('user', '', 'parent', 64, 0, 1); break;
                case 'users': $destlist = $form->select_dolusers(0,'',0,'',0,'',1,$conf->entity,0,0,'',0,'','',1,1);break;
            endswitch;

            if(!$error):

                $loginmsg->label = GETPOST('newmsg_label','alpha');
                $loginmsg->message = GETPOST('newmsg_message','alpha');

                $dest['mode'] = $type_destinataire;
                if(in_array($type_destinataire, array('groups','users','tags'))): $dest['params'] = GETPOST('newmsg_destinataire'); endif;
                $loginmsg->destinataire = json_encode($dest);

                $loginmsg->force_view = 0;
                $loginmsg->date_expiration = '';

                if(GETPOSTISSET('newmsg_forceview')):
                    $loginmsg->force_view = 1;
                    $loginmsg->date_expiration = GETPOST('newmsg_datexpyear').'-'.GETPOST('newmsg_datexpmonth').'-'.GETPOST('newmsg_datexpday');
                    $loginmsg->date_expiration .= ' 00:00:00';
                endif;

                if($loginmsg->addNewMsg($user)): setEventMessages($langs->trans('loginplus_msg_isAdded'), null, 'mesgs');
                else: setEventMessages($langs->trans('loginplus_msgerror_add'), null, 'errors'); $error++;
                endif;

            endif;
        else:
            setEventMessages("SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry", null, 'warnings');
        endif;
    break;

    case 'confirm_delete_msg':

        if(GETPOST('token') == $_SESSION['token']):

            // IDENTIFIANT DU MSG
            $msg_id = GETPOST('msgid','int');

            if(empty($msg_id)): $error++; setEventMessages($langs->trans('loginplus_msgerror_unknownId'), null, 'warnings'); endif;
            if(!$error):

                $check_delete = $loginmsg->deleteMsg($msg_id,$user);

                switch($check_delete):
                    case 1: setEventMessages($langs->trans('loginplus_msg_isDelete'), null, 'mesgs'); break;
                    case -1: echo setEventMessages($langs->trans('loginplus_optionp_norights'), null, 'errors'); $error++; break;
                    case -2: echo setEventMessages($langs->trans('loginplus_optionp_error'), null, 'errors'); $error++; break;
                    case -3: echo setEventMessages($langs->trans('loginplus_optionp_nodeladmin'), null, 'errors'); $error++; break;
                endswitch;

            endif;

        else:
            setEventMessages("SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry", null, 'warnings');
        endif;
    break;

    case 'edit':
        
        if(GETPOST('token') == $_SESSION['token']):

            $msg_id = GETPOST('msgid','int');
            $loginmsg->fetch($msg_id);

            $type_destinataire = json_decode($loginmsg->destinataire);

            switch ($type_destinataire->mode):
                case 'all': $destlist = $type_destinataire->mode; break;
                case 'groups': $destlist = $loginmsg->get_usersGroups();break;
                case 'tags': $destlist = $form->select_all_categories('user', '', 'parent', 64, 0, 1); break;
                case 'users': $destlist = $form->select_dolusers(0,'',0,'',0,'',1,$conf->entity,0,0,'',0,'','',1,1);break;
            endswitch;
        else:
            setEventMessages("SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry", null, 'warnings');
            $error++;
        endif;

    break;

    case 'edit_msg':        
        
        if(GETPOST('token') == $_SESSION['token']):

            $msg_id = GETPOST('msgid','int');
            $loginmsg->fetch($msg_id);
            //var_dump($loginmsg);

            $type_destinataire = json_decode($loginmsg->destinataire);

            if(empty(GETPOST('editmsg_label','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyTitle'), null, 'errors'); endif;
            if(empty(GETPOST('editmsg_message','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyMsg'), null, 'errors'); endif;
            if(GETPOSTISSET('editmsg_forceview') && empty(GETPOST('editmsg_datexp','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDate'), null, 'errors'); endif;
            if(in_array($type_destinataire->mode, array('groups','users','tags'))):
                if(!GETPOSTISSET('editmsg_destinataire')): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDest'), null, 'errors'); endif;
            endif;

            if(!$error):
                $loginmsg->label = GETPOST('editmsg_label','alpha');
                $loginmsg->message = GETPOST('editmsg_message','alpha');
                if(in_array($type_destinataire->mode, array('groups','users','tags'))): $type_destinataire->params = GETPOST('editmsg_destinataire'); endif;
                $loginmsg->destinataire = json_encode($type_destinataire);

                if(GETPOSTISSET('editmsg_forceview')):
                    $loginmsg->force_view = 1;
                    $loginmsg->date_expiration = GETPOST('editmsg_datexpyear').'-'.GETPOST('editmsg_datexpmonth').'-'.GETPOST('editmsg_datexpday');
                    $loginmsg->date_expiration .= ' 00:00:00';
                else:
                    $loginmsg->force_view = 0; $loginmsg->date_expiration = '';
                endif;

                if($loginmsg->updateMsg($user)): setEventMessages($langs->trans('loginplus_msg_isUpdated'), null, 'mesgs');
                else: setEventMessages($langs->trans('loginplus_msgerror_update'), null, 'errors'); $error++;
                endif;
            else:
                switch ($type_destinataire->mode):
                    case 'all': $destlist = $type_destinataire->mode; break;
                    case 'groups': $destlist = $loginmsg->get_usersGroups();break;
                    case 'tags': $destlist = $form->select_all_categories('user', '', 'parent', 64, 0, 1); break;
                    case 'users': $destlist = $form->select_dolusers(0,'',0,'',0,'',1,$conf->entity,0,0,'',0,'','',1,1); break;
                endswitch;
            endif;

        else:
            setEventMessages("SecurityTokenHasExpiredSoActionHasBeenCanceledPleaseRetry", null, 'warnings');
        endif;

    break;

endswitch;


// $form=new Form($db);
$list_loginmessages = $loginmsg->list_messages();

/***************************************************
* VIEW
****************************************************/
$array_js = array(
    '/loginplus/js/remodal.js',
    '/loginplus/js/loginplus_config.js'
);
$array_css = array(
    '/loginplus/css/dolpgs.css',
);

llxHeader('',$langs->transnoentities('loginplus_head_loginmsg').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus setup');
// ACTIONS NECESSITANT LE HEADER
if ($action == 'delete_msg'):
    echo $form->formconfirm($_SERVER['PHP_SELF'].'?msgid='.GETPOST('msgid','int'),'Confirmation',$langs->trans('loginplus_msg_confirmDelete'),'confirm_delete_msg','','',1,0,500,0);
endif;
?>

<div class="dolpgs-main-wrapper">

    <?php if(in_array('progiseize', $conf->modules)): ?>
        <h1 class="has-before"><?php echo $langs->transnoentities('loginplus_head_loginmsg'); ?></h1>
    <?php else : ?>
        <table class="centpercent notopnoleftnoright table-fiche-title"><tbody><tr class="titre"><td class="nobordernopadding widthpictotitle valignmiddle col-picto"><span class="fas fa-tools valignmiddle widthpictotitle pictotitle" style=""></span></td><td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block"><?php echo $langs->transnoentities('loginplus_head_loginmsg'); ?></div></td></tr></tbody></table>
    <?php endif; ?>
    
    <?php $head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'msg','loginplus', 0,'fa-user-lock_fas_#fb2a52'); ?>

    <?php if ($user->rights->loginplus->gerer_messages): ?>

        <table class="dolpgs-table" style="border-top:none;">
            <tbody>
                <tr class="">
                    <td class="nobordernopadding valignmiddle col-title" style="" colspan="4">
                        <div class="titre inline-block" style="">                            
                            <h3 class="dolpgs-table-title"><?php echo $langs->trans("Messages d'accueil"); ?></h3>
                        </div>
                    </td>
                    <td colspan="4" class="right">
                        <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" id="loginplus_msg">
                            <input type="hidden" name="action" value="prepare_newmsg">
                            <input type="hidden" name="token" value="<?php echo newToken(); ?>">

                            <?php 
                                $array_typeto = array(
                                    'all' => 'Tout le monde',
                                    'groups' => 'Groupe d\'utilisateurs',
                                    'tags' => 'Tags utilisateurs',
                                    'users' => 'Utilisateurs spécifiques',
                                );
                                echo $form->selectarray('newmsg_typeto',$array_typeto,'',0);
                            ?>                            
                            <button type="submit" class="dolpgs-btn btn-primary" style=""><i class="fas fa-plus"></i> </button>
                        </form>
                    </td>
                </tr>
                <tr class="dolpgs-thead noborderside">
                    <th><?php echo $langs->trans('loginplus_msgTitle'); ?></th>
                    <th><?php echo $langs->trans('loginplus_msgContent'); ?></th>
                    <th><?php echo $langs->trans('loginplus_msgTo'); ?></th>
                    <th><?php echo $langs->trans('loginplus_msgDate'); ?></th>
                    <th><?php echo $langs->trans('loginplus_msgDateExpiration'); ?></th>
                    <th><?php echo $langs->trans('loginplus_msgAuthor'); ?></th>
                    <th class="center"><?php echo $langs->trans('loginplus_msgNbView'); ?></th>
                    <th></th>
                </tr>

                <?php foreach($list_loginmessages as $msg_id => $msg): 

                    $msg_user->fetch($msg->author); $author_label = $msg_user->getFullName($langs, 0, -1); ?>

                    <tr class="dolpgs-tbody">
                        <td class="bold pgsz-optiontable-fieldname"><?php echo $msg->label; ?></td>               
                        <td class="pgsz-optiontable-fielddesc"><?php echo $msg->message; ?></td>
                        <?php // 

                            $infos_destinataire = json_decode($msg->destinataire);

                            $label_destinataire =  '<span style="font-weight:500;">'.$langs->trans('loginplus_msgTo_'.$infos_destinataire->mode).'</span>';
                            $tabdest = array();

                            switch ($infos_destinataire->mode):
                                
                                // GROUPES UTILISATEURS
                                case 'groups': 
                                    $label_destinataire .= "<br>";
                                    $userg = new UserGroup($db);
                                    foreach ($infos_destinataire->params as $group_id): $userg->fetch($group_id);                               
                                        array_push($tabdest, $userg->name);
                                    endforeach;
                                    $label_destinataire .= implode(', ', $tabdest);
                                break;                        
                                
                                // TAGS
                                case 'tags': 
                                    $label_destinataire .= "<br>";
                                    $cat = new Categorie($db);
                                    foreach ($infos_destinataire->params as $tag_id): $cat->fetch($tag_id);                               
                                        array_push($tabdest, $cat->label);
                                    endforeach;
                                    $label_destinataire .= implode(', ', $tabdest);
                                break;     

                                // UTILISATEURS            
                                case 'users': 
                                    $label_destinataire .= "<br>";                            
                                    foreach ($infos_destinataire->params as $user_id): $destinataire = new User($db); $destinataire->fetch($user_id);                                
                                        array_push($tabdest, $destinataire->lastname.' '.$destinataire->firstname);
                                    endforeach;
                                    $label_destinataire .= implode(', ', $tabdest);
                                break;

                                default: break;
                            endswitch;

                        ?>               
                        <td class="pgsz-optiontable-fielddesc"><?php echo $label_destinataire; ?></td>
                        <td class="pgsz-optiontable-fielddesc"><?php echo date('d/m/Y H:i',strtotime($msg->date_creation)); ?></td>
                        <td class="pgsz-optiontable-fielddesc"><?php if($msg->force_view): echo date('d/m/Y',strtotime($msg->date_expiration)); else : echo '--'; endif; ?></td>
                        <td class="pgsz-optiontable-fielddesc"><?php echo $author_label; ?></td>
                        <td class="center pgsz-optiontable-field "><?php echo $msg->nb_view; ?></td>
                        <td width="120" class="center">
                            <?php if($user->admin || $msg->author == $user->id && $user->rights->loginplus->gerer_messages): ?>
                                <?php echo '<a class="reposition editfielda paddingrightonly" href="'.$_SERVER['PHP_SELF'].'?msgid='.$msg->rowid.'&action=edit&token='.newToken().'">'.img_edit().'</a> &nbsp; '; ?>
                                <?php echo '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?msgid='.$msg->rowid.'&action=delete_msg&token='.newToken().'">'.img_delete().'</a>'; ?>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>
                

            </tbody>
        </table>

        <?php if($action == 'prepare_newmsg' && !$error || $action == 'add_newmsg' && $error): ?>

            <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" style="margin-top: 42px;">
                <input type="hidden" name="action" value="add_newmsg">
                <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                <input type="hidden" name="newmsg_typeto" value="<?php echo GETPOST('newmsg_typeto'); ?>">

                <h3 class="dolpgs-table-title"><?php echo $langs->trans('loginplus_msgAdd_'.GETPOST('newmsg_typeto')); ?></h3>
                <table class="dolpgs-table">
                    <tbody>
                        
                        <tr class="dolpgs-thead noborderside">
                            <th><?php echo $langs->trans('Parameter'); ?></th>
                            <th><?php echo $langs->trans('Description'); ?></th>
                            <th class="soixantepercent right"><?php echo $langs->trans('Value'); ?></th>
                        </tr>
                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgTitle'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgTitle_desc'); ?></td>
                            <td class="right pgsz-optiontable-field"><input class="quatrevingtpercent" type="text" name="newmsg_label" placeholder="<?php echo $langs->trans('Label'); ?>" value="<?php echo GETPOST('newmsg_label'); ?>"></td>
                        </tr>
                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname" valign="top"><?php echo $langs->trans('loginplus_msgContent'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc" valign="top"><?php echo $langs->trans('loginplus_msgContent_desc'); ?></td>
                            <td class="right pgsz-optiontable-field"><textarea class="quatrevingtpercent" style="resize: none;min-height: 64px" name="newmsg_message"><?php echo GETPOST('newmsg_message'); ?></textarea></td>
                        </tr>
                        <?php if(in_array(GETPOST('newmsg_typeto'), array('groups','users','tags'))): ?>
                            <tr class="dolpgs-tbody">
                                <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgTo'); ?> <span class="required">*</span></td>
                                <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msg_dest'.GETPOST('newmsg_typeto')); ?></td>
                                <td class="right pgsz-optiontable-field">
                                    <?php if(!empty($destlist)): ?>
                                        <select name="newmsg_destinataire[]" class="pgsz-slct2-simple quatrevingtpercent" multiple>
                                        <?php foreach ($destlist as $param_id => $param): ?>
                                            <option value="<?php echo $param_id; ?>" <?php if(GETPOSTISSET('newmsg_destinataire') && in_array($param_id, GETPOST('newmsg_destinataire')) ): echo 'selected'; endif; ?>><?php echo $param; ?></option>
                                        <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <?php echo 'Aucune donnée trouvée'; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgForceView'); ?></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgForceView_desc'); ?></td>
                            <td class="right pgsz-optiontable-field">
                                <?php $isChecked = (GETPOSTISSET('newmsg_forceview'))?'checked="checked"':''; ; ?>
                                <input type="checkbox" name="newmsg_forceview" <?php echo $isChecked; ?>>
                            </td>
                        </tr>
                        <tr class="dolpgs-tbody" style="<?php if(!GETPOSTISSET('newmsg_forceview')): echo 'display:none'; endif; ?>" id="loginplus-addmsg-datexp">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgDateExpiration'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgDateExpiration_desc'); ?></td>
                            <td class="right pgsz-optiontable-field">
                                <?php $slctd = (GETPOSTISSET('newmsg_datexp'))?strtotime(str_replace('/', '-', GETPOST('newmsg_datexp'))):strtotime("+1 month", strtotime(date('Y-m-d'))); ?>
                                <?php echo $form->selectDate($slctd,'newmsg_datexp'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="right">
                    <input type="submit" name="" class="dolpgs-btn btn-sm btn-primary" value="Ajouter">
                </div>
                

            </form>

        <?php elseif($action == 'edit' && !$error || $action == 'edit_msg' && $error): ?>

            <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST"  style="margin-top: 42px;">
                <input type="hidden" name="action" value="edit_msg">
                <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                <input type="hidden" name="msgid" value="<?php echo $loginmsg->rowid; ?>">

                <h3 class="dolpgs-table-title"><?php echo $langs->trans('loginplus_msg_update'); ?></h3>
                <table class="dolpgs-table" style="border-top:none;">
                    <tbody>
                        <tr class="dolpgs-thead noborderside">
                            <th><?php echo $langs->trans('Parameter'); ?></th>
                            <th><?php echo $langs->trans('Description'); ?></th>
                            <th class="soixantepercent right"><?php echo $langs->trans('Value'); ?></th>
                        </tr>
                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgTitle'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgTitle_desc'); ?></td>
                            <td class="right pgsz-optiontable-field"><input class="quatrevingtpercent" type="text" name="editmsg_label" placeholder="<?php echo $langs->trans('Label'); ?>" value="<?php echo (GETPOSTISSET('editmsg_label'))?GETPOST('editmsg_label'):$loginmsg->label; ?>"></td>
                        </tr>
                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname" valign="top"><?php echo $langs->trans('loginplus_msgContent'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc" valign="top"><?php echo $langs->trans('loginplus_msgContent_desc'); ?></td>
                            <td class="right pgsz-optiontable-field"><textarea class="quatrevingtpercent" style="resize: none;min-height: 64px" name="editmsg_message"><?php echo (GETPOSTISSET('editmsg_message'))?GETPOST('editmsg_message'):$loginmsg->message; ?></textarea></td>
                        </tr>                        
                        <?php if(in_array($type_destinataire->mode, array('groups','users','tags'))): ?>
                            <tr class="dolpgs-tbody">
                                <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgTo'); ?> <span class="required">*</span></td>
                                <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msg_dest'.$type_destinataire->mode); ?></td>
                                <td class="right pgsz-optiontable-field">
                                    <select name="editmsg_destinataire[]" class="pgsz-slct2-simple quatrevingtpercent" multiple>
                                    <?php foreach ($destlist as $param_id => $param): $selected = false; ?>
                                        <?php if($action == 'edit_msg' && GETPOSTISSET('editmsg_destinataire') && in_array($param_id, GETPOST('editmsg_destinataire'))): $selected = true; ?>
                                        <?php elseif(in_array($param_id, $type_destinataire->params)): $selected = true; endif; ?>
                                        <option value="<?php echo $param_id; ?>" <?php if($selected): echo 'selected'; endif; ?>><?php echo $param; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr class="dolpgs-tbody">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgForceView'); ?></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgForceView_desc'); ?></td>
                            <td class="right pgsz-optiontable-field">
                                <?php $isChecked = (GETPOSTISSET('editmsg_forceview'))?GETPOST('editmsg_forceview'):$loginmsg->force_view; ?>
                                <input type="checkbox" name="editmsg_forceview" <?php if($isChecked): echo 'checked="checked"'; endif; ?>>
                            </td>
                        </tr>
                        <tr class="dolpgs-tbody" style="<?php if(!$isChecked): echo 'display:none'; endif; ?>" id="loginplus-editmsg-datexp">
                            <td class="bold pgsz-optiontable-fieldname"><?php echo $langs->trans('loginplus_msgDateExpiration'); ?> <span class="required">*</span></td>
                            <td class="pgsz-optiontable-fielddesc"><?php echo $langs->trans('loginplus_msgDateExpiration_desc'); ?></td>
                            <td class="right pgsz-optiontable-field">
                                <?php if(empty($loginmsg->date_expiration)): $default_date = strtotime("+1 month", strtotime(date('Y-m-d'))); else: $default_date = strtotime(str_replace('/', '-', $loginmsg->date_expiration)); endif; ?>
                                <?php $slctd = (GETPOSTISSET('editmsg_datexp'))?strtotime(str_replace('/', '-', GETPOST('editmsg_datexp'))):$default_date; ?>
                                <?php echo $form->selectDate($slctd,'editmsg_datexp'); ?>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                <div class="right">
                    <input type="submit" name="" class="dolpgs-btn btn-sm btn-danger" value="Modifier">
                </div>

            </form>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php llxFooter(); $db->close(); ?>

