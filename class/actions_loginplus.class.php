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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('./loginplus/class/loginmsg.class.php');

class ActionsLoginPlus
{ 
	
	function printLeftBlock($parameters, &$object, &$action, $hookmanager){
		global $db, $langs, $user, $conf;

		
		if($_GET['action'] == 'setReadLoginMsg'):

			$loginmsg = new LoginMsg($db);
			$reads_id = explode(',', $_GET['msgsids']);

			if(GETPOST('token') == $_SESSION['token']):
				foreach ($reads_id as $readid):
					$loginmsg->setRead($readid,$user->id);
				endforeach;
			endif;

		endif;

		// PAGE APRES LOGIN
		if(in_array('login', explode(':', $parameters['context']))):

			$form = new Form($db);
			$msg_user = new User($db);
			$loginmsg = new LoginMsg($db);

			$user_msgs = $loginmsg->getUserMsgs($user);
			$nb_msgs = count($user_msgs);
			$user_msgs_ids = array();

			if($nb_msgs > 0):

				$title_msgs = "Vous avez ".$nb_msgs;
				$title_msgs .= ($nb_msgs > 1)?' messages':' message';
				$view_form = '<div id="loginplus-dialog" title="'.$title_msgs.'" style="display: none;">';

				foreach ($user_msgs as $msg): 
					array_push($user_msgs_ids, $msg->rowid);
					
					$msg_user->fetch($msg->author);
					$author_name = $msg_user->getFullName($langs, 0, -1);

					if($msg->author_maj > 0 && $msg->author_maj != $msg->author):
						$msg_user->fetch($msg->author_maj);
						$author_name = $author_name.' (Modifié par: '. $msg_user->getFullName($langs, 0, -1).')';
					endif;

					$view_form.= '<div class="loginplus-msg">';
					$view_form.= '<div class="loginplus-msglabel">'.$msg->label.'</div>';
					$view_form.= '<div class="loginplus-msgcontent">'.$msg->message.'</div>';
					$view_form.= '<div class="loginplus-msgauthor">'.$author_name.'</div>';
					$view_form.= '</div>';
				endforeach;		
				
				$view_form.= '</div>';

				// ON AFFICHE LE FORMULAIRE
				echo $view_form;

				$url = $_SERVER['PHP_SELF'];
				$url .= '?action=setReadLoginMsg';
				$url .= '&msgsids='.implode(',', $user_msgs_ids);
				$url .= '&token='.urlencode(newToken());

				?>

				<script type="text/javascript">
				jQuery(document).ready(function() {
					$( "#loginplus-dialog" ).dialog({
						autoOpen:true,resizable: false,modal: true,closeOnEscape: false,height:320,width:500,buttons: {
							"Marquer comme lu" : function(){location.href = "<?php echo $url; ?>";$(this).dialog("close");},
							"Ok" : function(){$(this).dialog("close");}
						}
					});
				})
				</script>

			<?php

			endif;
		endif;

		return 0;

	}


}

?>