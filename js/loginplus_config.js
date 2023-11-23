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

jQuery(document).ready(function($){

	let pop_image = jQuery('[data-remodal-id=pgsz-pop-image]').remodal();

	// AU CLIC SUR LE BOUTON DE CHOIX D'IMAGE
	jQuery(document).on('click','button.loginplus-btn',function(){
		
		//ON RECUPERE NOM DU CHAMP QU'IL FAUDRA REMPLIR
		let ld_target = jQuery(this).data('ldtarget');
		let ld_id = jQuery(this).data('ldparent');

		// ON INSERE LE NOM DU CHAMP DANS LE REMODAL
		jQuery('input[name="pgsz-target-name"]').val(ld_target);
		jQuery('input[name="pgsz-target-div"]').val(ld_id);

	});

	// AU CLIC SUR UNE IMAGE
	jQuery('.loginplus-remodal').on('click','.pgsz-flex-remodal-img',function(){

		//ON RECUPERE NOM DU CHAMP QU'IL FAUDRA REMPLIR et l clé à utiliser
		let ld_targeted = jQuery('input[name="pgsz-target-name"]').val();
		let ld_targetkey = jQuery(this).data('ldkey');
		let ld_targetid = jQuery('input[name="pgsz-target-div"]').val();

		console.log(ld_targeted,ld_targetkey,ld_targetid);

		let ld_imgurl = jQuery('input[name="pgsz-target-config"]').val();

		// ON REMPLIT LE CHAMP
		jQuery('input[name="'+ld_targeted+'"]').val(ld_targetkey);

		// ON PLACE L'IMAGE
		jQuery('#'+ld_targetid+'.pgsz-img-statut').html('<img src="'+ld_imgurl+ld_targetkey+'" style="max-height: 42px; max-width: 100px;border:1px solid #ccc;padding:3px;vertical-align:middle;display:inline;" />');

		pop_image.close();
	});

	jQuery(document).on('cancellation', '.loginplus-remodal', function () {

		let ld_targeted = jQuery('input[name="pgsz-target-name"]').val();
		let ld_targetid = jQuery('input[name="pgsz-target-div"]').val();

		jQuery('input[name="'+ld_targeted+'"]').val('');
		jQuery('#'+ld_targetid+'.pgsz-img-statut').html('<span class="loginplus-no-img">Aucune image selectionnée</span>');
	  
	});

	jQuery('.pgsz-slct2-simple').select2();

	jQuery('input[name="newmsg_forceview"]').on('change',function(){
		if(jQuery(this).prop('checked')){ jQuery('#loginplus-addmsg-datexp').show();
		} else { jQuery('#loginplus-addmsg-datexp').hide();}		
	});

	jQuery('input[name="editmsg_forceview"]').on('change',function(){
		if(jQuery(this).prop('checked')){ jQuery('#loginplus-editmsg-datexp').show();
		} else { jQuery('#loginplus-editmsg-datexp').hide();}		
	});
	

});