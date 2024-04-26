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
$optiontype = GETPOST('optiontype','aZ09')?:'list';

if($optiontype == 'editmsg' && $action != 'editmsg'):
    $optiontype = 'list';
endif;

$loginmsg = new loginMsg($db);
$msg_user = new User($db);
$form = new Form($db);
$error = 0;

$groups = $loginmsg->get_usersGroups(); 
$tags = $form->select_all_categories('user', '', 'parent', 64, 0, 1); 
$listusers = $form->select_dolusers(0,'',0,'',0,'',1,$conf->entity,0,0,'',0,'','',1,1); 
$array_typeto = array(
    'all' => 'Tout le monde',
    'groups' => 'Groupe d\'utilisateurs',
    'tags' => 'Tags utilisateurs',
    'users' => 'Utilisateurs spécifiques'
);



/*******************************************************************
* ACTIONS
********************************************************************/

switch ($action):

    // AJOUTER MSG
    case 'add_newmsg':

        $type_destinataire = GETPOST('lpmsg_typeto','alpha');

        switch ($type_destinataire):
            case 'groups': if(empty(GETPOST('destlist_groups','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_groups')), null, 'errors'); endif; break;
            case 'tags': if(empty(GETPOST('destlist_tags','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_tags')), null, 'errors'); endif; break;
            case 'users': if(empty(GETPOST('destlist_listusers','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_users')), null, 'errors'); endif; break;
        endswitch;
        if(empty(GETPOST('lpmsg_label','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyTitle'), null, 'errors'); endif;
        if(empty(GETPOST('lpmsg_message','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyMsg'), null, 'errors'); endif;
        if(GETPOSTISSET('lpmsg_forceview') && empty(GETPOST('lpmsg_datexp','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDate'), null, 'errors'); endif;

        if(!$error):

            $loginmsg->label = GETPOST('lpmsg_label','alpha');
            $loginmsg->message = GETPOST('lpmsg_message','alpha');

            $dest['mode'] = $type_destinataire;
            switch ($type_destinataire):
                case 'groups': $dest['params'] = GETPOST('destlist_groups','array'); break;
                case 'tags': $dest['params'] = GETPOST('destlist_tags','array');break;
                case 'users': $dest['params'] = GETPOST('destlist_listusers','array'); break;
            endswitch;
            $loginmsg->destinataire = json_encode($dest);

            $loginmsg->force_view = 0;
            $loginmsg->date_expiration = '';

            if(GETPOSTISSET('lpmsg_forceview')):
                $postdate = GETPOSTDATE('lpmsg_datexp','00:00:00');
                $loginmsg->force_view = 1;
                $loginmsg->date_expiration = dol_print_date($postdate,'standard');
            endif;

            if($loginmsg->addNewMsg($user)): 
                setEventMessages($langs->trans('loginplus_msg_isAdded'), null, 'mesgs');
                $optiontype = 'list';
            else: setEventMessages($langs->trans('loginplus_msgerror_add'), null, 'errors'); $error++;
            endif;

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

    case 'editmsg':
        $msg_edit = new loginMsg($db);
        $msg_edit->fetch(GETPOST('msgid','int'));
    break;

    case 'editmsgconfirm':
        
        $msg_edit = new loginMsg($db);
        $msg_edit->fetch(GETPOST('msgid','int'));

        $type_destinataire = GETPOST('lpmsg_typeto','alpha');
        switch ($type_destinataire):
            case 'groups': if(empty(GETPOST('destlist_groups','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_groups')), null, 'errors'); endif; break;
            case 'tags': if(empty(GETPOST('destlist_tags','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_tags')), null, 'errors'); endif; break;
            case 'users': if(empty(GETPOST('destlist_listusers','array'))): $error++; setEventMessages($langs->trans('ErrorFieldRequired',$langs->trans('loginplus_msgTo_users')), null, 'errors'); endif; break;
        endswitch;
        if(empty(GETPOST('lpmsg_label','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyTitle'), null, 'errors'); endif;
        if(empty(GETPOST('lpmsg_message','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyMsg'), null, 'errors'); endif;
        if(GETPOSTISSET('lpmsg_forceview') && empty(GETPOST('lpmsg_datexp','alpha'))): $error++; setEventMessages($langs->trans('loginplus_msgerror_emptyDate'), null, 'errors'); endif;

        if(!$error):

            $msg_edit->label = GETPOST('lpmsg_label','alpha');
            $msg_edit->message = GETPOST('lpmsg_message','alpha');

            $dest['mode'] = $type_destinataire;
            switch ($type_destinataire):
                case 'groups': $dest['params'] = GETPOST('destlist_groups','array'); break;
                case 'tags': $dest['params'] = GETPOST('destlist_tags','array');break;
                case 'users': $dest['params'] = GETPOST('destlist_listusers','array'); break;
            endswitch;
            $msg_edit->destinataire = json_encode($dest);

            $msg_edit->force_view = 0;
            $msg_edit->date_expiration = '';

            if(GETPOSTISSET('lpmsg_forceview')):
                $postdate = GETPOSTDATE('lpmsg_datexp','00:00:00');
                $msg_edit->force_view = 1;
                $msg_edit->date_expiration = dol_print_date($postdate,'standard');
            endif;

            if($msg_edit->updateMsg($user)): setEventMessages($langs->trans('loginplus_msg_isUpdated'), null, 'mesgs');
            else: 
                setEventMessages($langs->trans('loginplus_msgerror_update'), null, 'errors'); $error++;
                $action = 'editmsg';
                $optiontype = 'editmsg';
            endif;
        else:
            $action = 'editmsg';
            $optiontype = 'editmsg';
        endif;
    break;

endswitch;


// $form=new Form($db);
$list_loginmessages = $loginmsg->list_messages();

/***************************************************
* VIEW
****************************************************/
$array_js = array();
$array_css = array('/loginplus/css/dolpgs.css');

llxHeader('',$langs->transnoentities('loginplus_head_loginmsg').' :: '.$langs->transnoentities('Module300316Name'),'','','','',$array_js,$array_css,'','loginplus setup');
// ACTIONS NECESSITANT LE HEADER
if ($action == 'delete_msg'):
    echo $form->formconfirm($_SERVER['PHP_SELF'].'?msgid='.GETPOST('msgid','int'),'Confirmation',$langs->trans('loginplus_msg_confirmDelete'),'confirm_delete_msg','','',1,0,500,0);
endif;
?>

<div class="doladmin">

    <!--  -->
    <div id="doladmin-content">
        <?php 
        $linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
        print load_fiche_titre($langs->trans("loginplus_head_loginmsg"), $linkback, 'title_setup'); ?>

        <?php //$head = loginplusAdminPrepareHead(); dol_fiche_head($head, 'setup','loginplus', 1); ?>

        <div class="doladmin-flex-wrapper" id="loginplusadmin-content">

            <!-- COL FOR MENU -->
            <div class="doladmin-col-menu">
                <?php echo lp_showAdminMenu('messages'); ?>
            </div>

            <!-- COL FOR PARAMS -->
            <div class="doladmin-col-params">

                <div class="doladmin-card with-topmenu">
                    <?php $loginmenu = array(
                        array('optiontype' => 'list', 'icon' => 'fas fa-list','title' => $langs->trans('loginplus_AdminMsgList')),
                        array('optiontype' => 'addmsg', 'icon' => 'fas fa-plus-circle','title' => $langs->trans('loginplus_AdminMsgAdd')),
                    ); ?>
                    <nav class="doladmin-card-topmenu">
                        <ul>
                            <?php foreach ($loginmenu as $menukey => $menudet): ?>
                                <li class="<?php echo ($optiontype == $menudet['optiontype'])?'active':''; ?>">
                                    <a href="<?php echo dol_buildpath('loginplus/admin/msgs.php?optiontype='.$menudet['optiontype'],1); ?>">
                                        <i class="<?php echo $menudet['icon']; ?>"></i> <?php echo $menudet['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <?php if($action == 'editmsg'): ?>
                                <li class="active">
                                    <i class="fas fa-pencil-alt paddingright"></i> <?php echo $langs->trans('loginplus_msg_update'); ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <?php if($optiontype == 'list'): ?>
                        <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_head_loginmsg'); ?></div>
                        <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_option_message_desc'); ?></p>
                        <table class="doladmin-table-simple loginmsg-table">
                            <tbody>
                                <tr class="trlight">
                                    <th class="nowrap"></th>
                                    <th class="loginmsg-table-message nowrap"><?php echo $langs->trans('loginplus_msgContent'); ?></th>
                                    <th class="nowrap"><?php echo $langs->trans('loginplus_msgTo'); ?></th>
                                    <th class="nowrap"><?php echo $langs->trans('loginplus_msgDateExpiration'); ?></th>
                                    <th class="nowrap"><?php echo $langs->trans('loginplus_msgNbView2'); ?></th>
                                    <th class="nowrap"></th>
                                </tr>
                                <?php foreach($list_loginmessages as $msg_id => $msg): 

                                    $msg_user->fetch($msg->author);
                                    $infos_destinataire = json_decode($msg->destinataire);
                                    ?>

                                    <tr valign="top">

                                        <td class="loginmsg-table-infos nowrap">
                                            <i class="fas fa-info-circle paddingright" title="<?php echo $langs->trans('loginplus_msgCreatedDateAndAuthor',dol_print_date($msg->date_creation),$msg_user->login); ?>"></i>
                                        </td>
                                        <td class="loginmsg-table-message">
                                            <b><?php echo $msg->label; ?></b>
                                            <div class="loginmsg-gray">
                                                <?php if(strlen($msg->message) > 200): echo nl2br(substr($msg->message, 0, 200)).'...';
                                                else: echo nl2br($msg->message); endif; ?>
                                            </div>
                                        </td>
                                        
                                        <td class="loginmsg-table-infos nowrap">
                                            <?php // 
                                            $label_destinataire =  '<b>'.$langs->trans('loginplus_msgToSelect_'.$infos_destinataire->mode).'</b>';
                                            $more_destinataire =  '';
                                            $tabdest = array();
                                            switch ($infos_destinataire->mode):
                                                //
                                                case 'all':
                                                    $more_destinataire =  $langs->trans('loginplus_msgTo_all2');
                                                break;                                            
                                                // GROUPES UTILISATEURS
                                                case 'groups': 
                                                    $userg = new UserGroup($db);
                                                    foreach ($infos_destinataire->params as $group_id): $userg->fetch($group_id);                               
                                                        array_push($tabdest, $userg->name);
                                                    endforeach;
                                                    $more_destinataire .= implode(', ', $tabdest);
                                                break;                        
                                                
                                                // TAGS
                                                case 'tags': 
                                                    $cat = new Categorie($db);
                                                    foreach ($infos_destinataire->params as $tag_id): $cat->fetch($tag_id);                               
                                                        array_push($tabdest, $cat->label);
                                                    endforeach;
                                                    $more_destinataire .= implode(', ', $tabdest);
                                                break;     

                                                // UTILISATEURS            
                                                case 'users': 
                                                    foreach ($infos_destinataire->params as $user_id): $destinataire = new User($db); $destinataire->fetch($user_id);                                
                                                        array_push($tabdest, $destinataire->lastname.' '.$destinataire->firstname);
                                                    endforeach;
                                                    $more_destinataire .= implode(', ', $tabdest);
                                                break;

                                                default: break;
                                            endswitch; ?>  
                                            <i class="fas fa-user paddingright"></i> <?php echo $label_destinataire; ?>
                                            <?php if(!empty($more_destinataire)): echo '<div class="loginmsg-gray">'.$more_destinataire.'</div>'; endif; ?>
                                        </td>
                                        <td class="loginmsg-table-infos nowrap">
                                            <?php if($msg->date_expiration): 
                                                $icon_date = 'far fa-calendar';
                                                if(dol_stringtotime($msg->date_expiration) < dol_now()):
                                                    $icon_date = 'fas fa-exclamation-triangle doladmin-danger';
                                                endif; ?>
                                                <i class="<?php echo $icon_date; ?> paddingright" title="<?php echo $langs->trans('loginplus_msgDateExpirationD',dol_print_date($msg->date_expiration)); ?>"></i>
                                                <b><?php echo $langs->trans('DateEnd').' :'; ?></b>
                                                <div class="loginmsg-gray"><?php echo dol_print_date($msg->date_expiration,'%d/%m/%Y'); ?></div>
                                            <?php else: ?>
                                                <i class="fas fa-infinity paddingright" title="<?php echo $langs->trans('loginplus_msgNoExpiration'); ?>"></i>
                                                <b><?php echo $langs->trans('DateEnd').':'; ?></b>
                                                <div class="loginmsg-gray"><?php echo $langs->trans('loginplus_msgUnlimited'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="loginmsg-table-infos nowrap">
                                            <i class="fas fa-eye paddingright" title="Nombre de vues"></i> <b><?php echo $msg->nb_view; ?></b>
                                        </td>
                                        <td class="loginmsg-table-actions right nowrap">
                                            <?php if($user->admin || $msg->author == $user->id && $user->rights->loginplus->gerer_messages): ?>
                                                <?php echo '<a class="reposition editfielda paddingright" href="'.$_SERVER['PHP_SELF'].'?msgid='.$msg->rowid.'&action=editmsg&optiontype=editmsg&token='.newToken().'">'.img_edit().'</a> &nbsp; '; ?>
                                                <?php echo '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?msgid='.$msg->rowid.'&action=delete_msg&token='.newToken().'">'.img_delete().'</a>'; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif($optiontype == 'editmsg'): //$msg_edit 

                        $destinfos = json_decode($msg_edit->destinataire); ?>

                        <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_msg_update'); ?></div>
                        <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">
                            <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">
                                
                                <input type="hidden" name="action" value="editmsgconfirm">
                                <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                                <input type="hidden" name="optiontype" value="editmsg">
                                <input type="hidden" name="msgid" value="<?php echo GETPOST('msgid'); ?>">

                                <table class="doladmin-table-simple">                                
                                    <tbody>
                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2"><i class="fas fa-paper-plane paddingright"></i> <?php echo $langs->trans('loginplus_msgTo'); ?></td>
                                        </tr>

                                        <tr class="dest-groups">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo'); ?></td>
                                            <td class="right">
                                                <?php echo $form->selectarray('lpmsg_typeto',$array_typeto,GETPOSTISSET('lpmsg_typeto')?GETPOST('lpmsg_typeto','alphanohtml'):$destinfos->mode,0,0,0,'',0,0,0,'','minwidth300'); ?>
                                            </td>
                                        </tr>

                                        <!-- GROUPS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-groups">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_groups'); ?></td>
                                            <td class="right">
                                                <?php

                                                $default_groups = array();
                                                if($destinfos->mode == 'groups'): $default_groups = $destinfos->params; endif;

                                                if(!empty($groups)): 
                                                    echo $form->selectarray('destlist_groups[]',$groups,GETPOSTISSET('destlist_groups')?GETPOST('destlist_groups','array'):$default_groups,0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else: echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <!-- TAGS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-tags">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_tags'); ?></td>
                                            <td class="right">
                                                <?php

                                                $default_tags = array();
                                                if($destinfos->mode == 'tags'): $default_tags = $destinfos->params; endif;

                                                if(!empty($tags)): echo $form->selectarray('destlist_tags[]',$tags,GETPOSTISSET('destlist_tags')?GETPOST('destlist_tags','array'):$default_tags,0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else: echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <!-- USERS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-users">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_users'); ?></td>
                                            <td class="right">
                                                <?php     

                                                $default_users = array();
                                                if($destinfos->mode == 'users'): $default_users = $destinfos->params; endif;

                                                if(!empty($listusers)):
                                                    echo $form->selectarray('destlist_listusers[]',$listusers,GETPOSTISSET('destlist_listusers')?GETPOST('destlist_listusers','array'):$default_users,0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else: echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;padding-bottom: 4px;"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_msgParams'); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgForceView').' '.img_info($langs->trans('loginplus_msgForceView_desc')); ?></td>
                                            <td class="right">
                                                <?php 
                                                    // First view, post after
                                                    if(!empty($_GET)): $isChecked = $msg_edit->force_view?'checked="checked"':'';
                                                    else: $isChecked = (GETPOSTISSET('lpmsg_forceview'))?'checked="checked"':'';
                                                    endif;                                                    
                                                ?>
                                                <input type="checkbox" value="a" name="lpmsg_forceview" <?php echo $isChecked; ?>>
                                            </td>
                                        </tr>
                                        <tr style="<?php if(!GETPOSTISSET('lpmsg_forceview')): echo 'display:none'; endif; ?>" id="loginplus-addmsg-datexp">
                                            <td class="bold "><?php echo $langs->trans('loginplus_msgDateExpiration'); ?> <span class="required">*</span></td>
                                            <td class="right">
                                                <?php 
                                                    if(GETPOSTISSET('lpmsg_datexp')): $slctd = dol_stringtotime(GETPOST('lpmsg_datexp'));
                                                    else:
                                                        if(!empty($msg_edit->date_expiration)): $slctd = dol_stringtotime($msg_edit->date_expiration);
                                                        else: $slctd = strtotime("+1 month", dol_now());
                                                        endif;
                                                    endif;
                                                    echo $form->selectDate($slctd,'lpmsg_datexp');
                                                ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;padding-bottom: 4px;"><i class="fas fa-comment-alt paddingright"></i> <?php echo $langs->trans('loginplus_msgContentTitle'); ?></td>
                                        </tr>
                                        <tr class="dolpgs-tbody">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTitle'); ?> <span class="required">*</span></td>
                                            <td class="right"><input type="text" class="minwidth400" name="lpmsg_label" placeholder="<?php echo $langs->trans('Label'); ?>" value="<?php echo GETPOSTISSET('lpmsg_label')?GETPOST('lpmsg_label','alphanohtml'):$msg_edit->label; ?>"></td>
                                        </tr>
                                        <tr class="dolpgs-tbody">
                                            <td class="bold" valign="top" colspan="2">
                                                <?php echo $langs->trans('loginplus_msgContent'); ?> <span class="required">*</span><br>
                                                <textarea class="" style="" id="lpmsg_message" name="lpmsg_message"><?php echo GETPOSTISSET('lpmsg_message')?GETPOST('lpmsg_message','alphanohtml'):$msg_edit->message; ?></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="doladmin-form-buttons right">
                                    <input type="submit" name="" >
                                </div>

                            </form>
                        </div>

                    <?php elseif($optiontype == 'addmsg'): ?>

                        <div class="doladmin-params-title"><?php echo $langs->trans('loginplus_AdminMsgAdd'); ?></div>
                        <p class="doladmin-params-desc opacitymedium"><?php echo $langs->trans('loginplus_AdminMsgAddDesc'); ?></p>
                        <div class="doladmin-card-content paddingtop" style="margin-top: 16px;">
                            <form enctype="multipart/form-data" action="<?php print $_SERVER["PHP_SELF"]; ?>" method="POST" class="doladmin-form">
                                
                                <input type="hidden" name="action" value="add_newmsg">
                                <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                                <input type="hidden" name="optiontype" value="addmsg">

                                <table class="doladmin-table-simple">                                
                                    <tbody>
                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2"><i class="fas fa-paper-plane paddingright"></i> <?php echo $langs->trans('loginplus_msgTo'); ?></td>
                                        </tr>

                                        <tr class="dest-groups">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo'); ?></td>
                                            <td class="right">
                                                <?php echo $form->selectarray('lpmsg_typeto',$array_typeto,GETPOST('lpmsg_typeto','alphanohtml'),0,0,0,'',0,0,0,'','minwidth300'); ?>
                                            </td>
                                        </tr>

                                        <!-- GROUPS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-groups">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_groups'); ?></td>
                                            <td class="right">
                                                <?php 
                                                if(!empty($groups)): echo $form->selectarray('destlist_groups[]',$groups,GETPOST('destlist_groups','array'),0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else:  echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <!-- TAGS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-tags">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_tags'); ?></td>
                                            <td class="right">
                                                <?php                                                 
                                                if(!empty($tags)): echo $form->selectarray('destlist_tags[]',$tags,GETPOST('destlist_tags','array'),0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else: echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <!-- USERS -->
                                        <tr class="lpmsg-typeto" id="lpmsg-typeto-users">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTo_users'); ?></td>
                                            <td class="right">
                                                <?php                                                 
                                                if(!empty($listusers)): echo $form->selectarray('destlist_listusers[]',$listusers,GETPOST('destlist_listusers','array'),0,0,0,'multiple',0,0,0,'','minwidth300'); 
                                                else: echo $langs->trans('NoData');
                                                endif; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;padding-bottom: 4px;"><i class="fas fa-cog paddingright"></i> <?php echo $langs->trans('loginplus_msgParams'); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgForceView').' '.img_info($langs->trans('loginplus_msgForceView_desc')); ?></td>
                                            <td class="right">
                                                <?php $isChecked = (GETPOSTISSET('lpmsg_forceview'))?'checked="checked"':''; ; ?>
                                                <input type="checkbox" name="lpmsg_forceview" <?php echo $isChecked; ?>>
                                            </td>
                                        </tr>
                                        <tr style="<?php if(!GETPOSTISSET('lpmsg_forceview')): echo 'display:none'; endif; ?>" id="loginplus-addmsg-datexp">
                                            <td class="bold "><?php echo $langs->trans('loginplus_msgDateExpiration'); ?> <span class="required">*</span></td>
                                            <td class="right">
                                                <?php $slctd = (GETPOSTISSET('lpmsg_datexp'))?dol_stringtotime(GETPOST('lpmsg_datexp')):strtotime("+1 month", dol_now());
                                                echo $form->selectDate($slctd,'lpmsg_datexp'); ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="doladmin-table-subtitle" colspan="2" style="padding-top: 36px;padding-bottom: 4px;"><i class="fas fa-comment-alt paddingright"></i> <?php echo $langs->trans('loginplus_msgContentTitle'); ?></td>
                                        </tr>
                                        <tr class="dolpgs-tbody">
                                            <td class="bold"><?php echo $langs->trans('loginplus_msgTitle'); ?> <span class="required">*</span></td>
                                            <td class="right"><input type="text" class="minwidth400" name="lpmsg_label" placeholder="<?php echo $langs->trans('Label'); ?>" value="<?php echo GETPOST('lpmsg_label'); ?>"></td>
                                        </tr>
                                        <tr class="dolpgs-tbody">
                                            <td class="bold" valign="top" colspan="2">
                                                <?php echo $langs->trans('loginplus_msgContent'); ?> <span class="required">*</span><br>
                                                <textarea class="" style="" id="lpmsg_message" name="lpmsg_message"><?php echo GETPOST('lpmsg_message'); ?></textarea>
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
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    
    // SELECT2 NEEDS JQUERY
    $(function() {

        //
        var s_typeto = $('select[name="lpmsg_typeto"]').val();
        if(s_typeto != 'all'){$('#lpmsg-typeto-' + s_typeto).show();}
        $('select[name="lpmsg_typeto"]').on('change', function (e) {
            var typeto = $(this).val();
            $('.lpmsg-typeto').hide();            
            if(typeto != 'all'){$('#lpmsg-typeto-' + typeto).show();}            
        });
    });

    // FORCEVIEW
    let forceview_checkbox = document.querySelector('input[name="lpmsg_forceview"]');
    let forceview_date = document.querySelector('#loginplus-addmsg-datexp');

    if(forceview_checkbox){

        if(forceview_checkbox.checked == true){
            forceview_date.style.setProperty('display','table-row');
        } else {
            forceview_date.style.setProperty('display','none');
        }

        forceview_checkbox.addEventListener('change', function (a){
            if(forceview_checkbox.checked == true){
                forceview_date.style.setProperty('display','table-row');
            } else {
                forceview_date.style.setProperty('display','none');
            }
        });
    }

</script>

<?php llxFooter(); $db->close(); ?>

