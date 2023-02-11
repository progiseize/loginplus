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


require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

class LoginMsg {

	
	public $table_element = 'loginplus_msg';

	public $rowid;
	public $label;
	public $message;
	public $destinataire;
	public $is_read;
	
	public $author;	
	public $date_creation;

	public $force_view;
	public $date_expiration;	
	public $nb_view;

	public $db;

	public function __construct($db){$this->db = $db;}

	/*****************************************************************/
	// RECUPERER LA LISTE DES GROUPES USERS
	/*****************************************************************/
	public function get_usersGroups(){

		$groups = array();

		$sql = "SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$sql .= " ORDER BY rowid";
		$result = $this->db->query($sql);

		if($result):
			$nb_results = $this->db->num_rows($result);
			if($nb_results): $i = 0;
				while($i < $nb_results):
					$obj = $this->db->fetch_object($result);
					$groups[$obj->rowid] = $obj->nom;
					$i++;
				endwhile;
			endif;
		else: dol_print_error($this->db); 
		endif;

		return $groups;
	}

	/*****************************************************************/
	// RECUPERER UN MESSAGE
	/*****************************************************************/
	public function fetch($rowid){

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE rowid = ".$rowid;

		$result = $this->db->query($sql);
		$item = $this->db->fetch_object($result);

		if($result->num_rows == 0): return -1;
		else:
			$this->rowid = $item->rowid;
			$this->label = $item->label;
			$this->message = $item->message;
			$this->destinataire = $item->destinataire;
			$this->is_read = $item->is_read;
			$this->author = $item->author;
			$this->date_creation = $item->date_creation;
			$this->force_view = $item->force_view;
			$this->date_expiration = $item->date_expiration;
			$this->nb_view = $item->nb_view;

			return $this->rowid;
		endif;

	}

	/*****************************************************************/
	// RECUPERER LA LISTE DE TOUS LES MESSAGES
	/*****************************************************************/
	public function list_messages(){

		global $conf, $user, $langs;

		$list_msgs = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " ORDER BY date_creation";
		$result = $this->db->query($sql);

		if($result):
			$nb_results = $this->db->num_rows($result);
			if($nb_results): $i = 0;
				while($i < $nb_results):
					$obj = $this->db->fetch_object($result);
					$list_msgs[$obj->rowid] = $obj;
					$i++;
				endwhile;
			endif;
		else: dol_print_error($this->db); 
		endif;

		return $list_msgs;
	}
	
	/*****************************************************************/
	// AJOUTER UN NOUVEAU MESSAGE
	/*****************************************************************/
	public function addNewMsg($user){

		global $conf, $langs;

		if($user->rights->loginplus->gerer_messages):

			$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element;
			$sql.= " (label,message,destinataire,is_read,author,force_view,date_expiration,entity)";
			$sql.= " VALUES (";
			$sql.= " '".$this->db->escape($this->label)."'";
			$sql.= ", '".$this->db->escape($this->message)."'";
			$sql.= ", '".$this->destinataire."'";
			$sql.= ", '[]'";
			$sql.= ", '".$user->id."'";
			$sql.= ", '".$this->force_view."'";
			if(!empty($this->date_expiration)): $sql.= ", '".$this->date_expiration."'";
			else: $sql.= ", NULL"; endif;
			$sql.= ", '".$conf->entity."'";
			$sql.= ")";

			$result = $this->db->query($sql);

			if ($result): 
				$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
				$this->author = $user->id;
				$this->entity = $conf->entity;

				$this->db->commit(); return $this->rowid;

			else: $this->db->rollback(); return false;
			endif;

		endif;
	}

	/*****************************************************************/
	// SUPPRIMER UN MESSAGE
	/*****************************************************************/
	public function deleteMsg($rowid,$user){

		global $conf, $langs;

		if($user->rights->loginplus->gerer_messages):

			// On récupère le message
			$this->fetch($rowid);

			// On check l'auteur
			$author = new User($this->db);
			$author->fetch($this->author);

			if(!$user->admin && $author->admin): return -3;
			else:

				$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
				$sql .= " WHERE rowid = ".$rowid;
				$result = $this->db->query($sql);

				if($result): $this->db->commit(); return true;
				else: $this->db->rollback(); return -2; endif;
			endif;
			
			
		else: return -1;
		endif;
	}

	/*****************************************************************/
	// UPDATE MESSAGE
	/*****************************************************************/
	public function updateMsg($user){

		global $conf, $langs;

		if($user->rights->loginplus->gerer_messages):

			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET label = '".$this->db->escape($this->label)."'";
			$sql .= ", message  = '".$this->db->escape($this->message)."'";
			$sql .= ", destinataire  = '".$this->destinataire."'";
			$sql .= ", author_maj  = '".$user->id."'";
			$sql .= ", force_view  = '".$this->force_view."'";
			if(!empty($this->date_expiration)): $sql .= ", date_expiration  = '".$this->date_expiration."'";
			else: $sql .= ", date_expiration  = NULL"; endif;
			$sql .= " WHERE rowid = ".$this->rowid;

			$result = $this->db->query($sql);
			if($result): $this->db->commit(); return true;
			else: $this->db->rollback(); return false; endif;
			
		else: return false;
		endif;
	}

	/*****************************************************************/
	// RECUPERER TOUS LES MESSAGES D'UN UTILISATEUR
	/*****************************************************************/
	public function getUserMsgs($loguser){

		global $conf;

		$messages = array();

		// ON RECUPERE LES MESSAGES DE TOUT LE MONDE
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE JSON_VALUE(destinataire, '$.mode') = 'all'";
		$sql .= " AND entity = '".$conf->entity."'";
		$sql .= " AND (";
		$sql .= "NOT JSON_CONTAINS(is_read, '{\"userid\":".$loguser->id."}')";
		$sql .= " OR (force_view = 1 AND date_expiration > '".date('Y-m-d H:i:s')."')";
		$sql .= ")";

		$result = $this->db->query($sql);
		if($result): while($obj = $this->db->fetch_object($result)): array_push($messages, $obj); endwhile; endif;

		// ON RECUPERE LES MESSAGES DE TAGS
		$user_tags = $loguser->getCategoriesCommon('user'); $i = 0;		
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE JSON_VALUE(destinataire, '$.mode') = 'tags'";
		$sql .= " AND entity = '".$conf->entity."'";
		$sql .= " AND (";
		foreach ($user_tags as $tag): $i++; if($i > 1): $sql .= " OR"; endif;
			$sql .= " JSON_CONTAINS(destinataire, '\"".$tag."\"', '$.params')";
		endforeach;
		$sql .= ")";
		$sql .= " AND (";
		$sql .= "NOT JSON_CONTAINS(is_read, '{\"userid\":".$loguser->id."}')";
		$sql .= " OR (force_view = 1 AND date_expiration > '".date('Y-m-d H:i:s')."')";
		$sql .= ")";

		$result = $this->db->query($sql);
		if($result): while($obj = $this->db->fetch_object($result)): array_push($messages, $obj); endwhile; endif;

		// ON RECUPERE LES MESSAGES DE GROUPES
		$ug = new UserGroup($this->db); $user_groups = $ug->listGroupsForUser($loguser->id); $i = 0;
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE JSON_VALUE(destinataire, '$.mode') = 'groups'";
		$sql .= " AND entity = '".$conf->entity."'";
		$sql .= " AND (";
		foreach ($user_groups as $group): $i++; if($i > 1): $sql .= " OR"; endif;
			$sql .= " JSON_CONTAINS(destinataire, '\"".$group->id."\"', '$.params')";
		endforeach;
		$sql .= ")";
		$sql .= " AND (";
		$sql .= "NOT JSON_CONTAINS(is_read, '{\"userid\":".$loguser->id."}')";
		$sql .= " OR (force_view = 1 AND date_expiration > '".date('Y-m-d H:i:s')."')";
		$sql .= ")";

		$result = $this->db->query($sql);
		if($result): while($obj = $this->db->fetch_object($result)): array_push($messages, $obj); endwhile; endif;

		// ON RECUPERE LES MESSAGES UTILISATEURS SPEC.
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE JSON_VALUE(destinataire, '$.mode') = 'users'";
		$sql .= " AND JSON_CONTAINS(destinataire, '\"".$loguser->id."\"', '$.params')";
		$sql .= " AND entity = '".$conf->entity."'";
		$sql .= " AND (";
		$sql .= "NOT JSON_CONTAINS(is_read, '{\"userid\":".$loguser->id."}')";
		$sql .= " OR (force_view = 1 AND date_expiration > '".date('Y-m-d H:i:s')."')";
		$sql .= ")";

		$result = $this->db->query($sql);
		if($result): while($obj = $this->db->fetch_object($result)): array_push($messages, $obj); endwhile; endif;

		// ON AJOUTE + 1 A L'AFFICHAGE DE CHAQUE MSG
		foreach ($messages as $msg):
			$upsql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$upsql .= " SET nb_view = nb_view + 1";
			$upsql .= " WHERE rowid = ".$msg->rowid;
			$this->db->query($upsql);
		endforeach;

		return $messages;
	}

	/*****************************************************************/
	// MARQUER COMME LU
	/*****************************************************************/
	public function setRead($msg_id, $user_id){

		$this->fetch($msg_id);
		$isread_tab = json_decode($this->is_read);

		$id_exist = false;

		foreach($isread_tab as $isread):
			if($isread->userid == $user_id): $id_exist = true; endif;
		endforeach;

		if(!$id_exist):

			array_push($isread_tab, array('userid' => intval($user_id),'read_date' => date('Y-m-d H:i:s')));

			$upsql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$upsql .= " SET is_read = '".json_encode($isread_tab)."'";
			$upsql .= " WHERE rowid = ".$msg_id;

			if($this->db->query($upsql)): return true; else: return false; endif;
		else: 
			return 0; 
		endif;

	}
}