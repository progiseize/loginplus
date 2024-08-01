<?php
/* 
 * Copyright (C) 2024 ProgiSeize <contact@progiseize.fr>
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

class LoginPlus {

	public $templates =  array(
        'template_one' => array(
        	'position' => 1,
        	'imgurl' => '../img/template/tpl_colsimple.png',
        	'previewclass' => 'template_one',
        	'langkey' => 'loginplus_AdminStructureModel1'
        ),
        'template_two' => array(
        	'position' => 3,
        	'imgurl' => '../img/template/tpl_sidebar.png',
        	'previewclass' => 'template_two',
        	'langkey' => 'loginplus_AdminStructureModel3'
        ),
    );

	public $shapes = array(

		// SQUARES
        'split' => array('type' => 'clip','category' => 'square'),
        'split_minus' => array('type' => 'clip','category' => 'square'),

        // DIAGONALS
        'corner_tl' => array('type' => 'clip','category' => 'diagonals'),
        'corner_bl' => array('type' => 'clip','category' => 'diagonals'),

        // CORNERS
        'semicorner_tl' => array('type' => 'clip','category' => 'corners'),
        'semicorner_bl' => array('type' => 'clip','category' => 'corners'),

        // ASC - DESC
        'diagonal_asc' => array('type' => 'clip','category' => 'diagside'),
        'diagonal_desc' => array('type' => 'clip','category' => 'diagside'),

        // WAVES - NEW
        'wave-1' => array('type' => 'svg','category' => 'waves'),
        'wave-2' => array('type' => 'svg','category' => 'waves'),
        'wave-3' => array('type' => 'svg','category' => 'waves'),

        // BARS - NEW
        'bar-1' => array('type' => 'svg','category' => 'bars'),
        'bar-2' => array('type' => 'svg','category' => 'bars'),

        // BLOBS
        'blob' => array('type' => 'svg','category' => 'blobs'),
        'circle' => array('type' => 'clip','category' => 'blobs'),
	);

	public $db;

	public function __construct($db){$this->db = $db;}

	/*****************************************************************/
	// TEMPLATE (PREVIEW)
	/*****************************************************************/
	public function preview($templatekey,$mysoc,$mask = 0){

		global $conf, $langs;

		$preview = '<div class="doladmin-preview">';

		// BACKGROUND IMAGE
		if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))):
			$preview .= '<div class="preview-background" style="background-image: url(\''.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_BG_IMAGEKEY')).'\');" ></div>';
		endif;

		// BACKGROUND SHAPE
		$path_name = getDolGlobalString('LOGINPLUS_SHAPE_PATH');
		$path_shape = $this->getShapes($path_name);
		$path_css = 'preview-shape';
		$path_content = '';

		if(!empty($path_shape)):
			$path_css .= ' shape-'.$path_shape['type'].' '.$path_name;
			if(getDolGlobalInt('LOGINPLUS_SHAPE_ALT')):
				$path_css .= ' alternate';
			endif;

			if($path_shape['type'] == 'svg'):
				$shape_folder = dol_buildpath('loginplus/svg');
				if(file_exists($shape_folder.'/'.$path_name.'.svg')):
                	$path_content .= file_get_contents($shape_folder.'/'.$path_name.'.svg');
                endif;
            endif;
		endif;

		$preview .= '<div class="'.$path_css.'">';	
			$preview .= $path_content;
		$preview .= '</div>';

		/*******************************************/
		// BOXES
		$preview_wrapperclass = 'preview-global-wrapper '.$templatekey;
		$preview_wrapperclass .= ' box-'.getDolGlobalString('LOGINPLUS_BOX_ALIGN');
		if(getDolGlobalInt('LOGINPLUS_SHOW_SECONDARYBOX')):
			$preview_wrapperclass .= ' show-secondary';
		endif;
		$preview .= '<div class="'.$preview_wrapperclass.'">';
		$preview .= '<div class="preview-wrapper '.(getDolGlobalInt('LOGINPLUS_BOX_WIDTH')?'w2':'').'">';

		// LOGO
		$urllogo = '';
		if(!empty(getDolGlobalString('LOGINPLUS_LOGOALT'))):
			$urllogo = DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_LOGOALT'));
		else:
			if (!empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo)):
				$urllogo = DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/'.$mysoc->logo);
			elseif (!empty($mysoc->logo_squarred_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_squarred_small)):
	       		$urllogo = DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=mycompany&amp;file='.urlencode('logos/thumbs/'.$mysoc->logo_squarred_small);
	     	elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/dolibarr_logo.svg')):
	     		$urllogo = DOL_URL_ROOT.'/theme/dolibarr_logo.svg';
	     	endif;
	    endif;

		//BOXLOGIN
		$boxlogin = '';
		if(!$mask):

			// LOGO
			if(!empty($urllogo)): $boxlogin .= '<div class="preview-logo"><img src="'.$urllogo.'"></div>';endif;
			
			// ICON, INPUT + LABEL
			$boxlogin .= '<form class="preview-fields '.(getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?'loginplus-viewlabel':'').'" method="post" action="" autocomplete="off">';
				$boxlogin .= '<input autocomplete="false" name="hidden" type="text" style="display:none;">';
				$boxlogin .= '<div class="preview-fieldrow">';
					$boxlogin .= '<label for="previewfield-a">';
						$boxlogin .= '<i class="fa fa-user"></i> ';
						$boxlogin .= '<span class="label-txt">Login</span>';
					$boxlogin .= '</label>';
					$boxlogin .= '<input id="previewfield-a" type="text" name="previewlogin" value="Userlogin" autocomplete="off">';
				$boxlogin .= '</div>';
				$boxlogin .= '<div class="preview-fieldrow">';
					$boxlogin .= '<label for="previewfield-b">';
						$boxlogin .= '<i class="fa fa-key"></i> ';
						$boxlogin .= '<span class="label-txt">Password</span>';
					$boxlogin .= '</label>';
					$boxlogin .= '<input id="previewfield-b" type="password" name="previewpassword" value="userpass" autocomplete="new-password">';
				$boxlogin .= '</div>';
			$boxlogin .= '</form>';

			//
			$boxlogin .= '<div class="preview-submit">'.$langs->trans('Connection').'</div>';

			//
			$boxlogin .= '<div class="preview-links"><a href="#">Mot de passe oublié</a></div>';
		endif;

		$boxside = '';
		if(!$mask):
			// BACKGROUND IMAGE
			if(!empty(getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY'))):
				$boxside .=  '<div class="preview-boximage" style="background-image: url(\''.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY')).'\');" ></div>';
			endif;
			$boxside .= '<div class="preview-boxtxt">';
			$boxside .= '<div class="preview-title">'.getDolGlobalString('LOGINPLUS_TXT_TITLE').'</div class="preview-title">';
			$boxside .= '<div class="preview-content">'.getDolGlobalString('LOGINPLUS_TXT_CONTENT').'</div>';
			$boxside .= '</div>';
		endif;

		$previewdivclass = '';
		if($mask): $previewdivclass = 'mask'; endif;

			$preview .= '<div class="prevdiv preview-boxside '.($mask?'mask':'').'">'.$boxside.'</div>';
			$preview .= '<div class="prevdiv preview-boxlogin '.(getDolGlobalInt('LOGINPLUS_SECONDARYBOX_SHADOW')?'with-shadow':'').' '.($mask?'mask':'').'">'.$boxlogin.'</div>';

		$preview .= '</div>';
		$preview .= '</div>';

		/*******************************************/

		$preview .= '</div>';

		return $preview;
	}

	/*****************************************************************/
	// RECUPERER LA LISTE DES FORMES DISPONIBLES
	/*****************************************************************/
	public function getShapes($key = ''){

		global $langs;

		$results = array();
		if(!$key): return $this->shapes;
		elseif($key && isset($this->shapes[$key])): return $this->shapes[$key];
		else: return array(); endif;
	}

	/*****************************************************************/
	// RECUPERER LA LISTE DES FORMES DISPONIBLES PAR CATEGORIE
	/*****************************************************************/
	public function getShapesBy($by = 'category'){

		global $langs;

		$results = array();

		if(!empty($this->shapes)):
			foreach($this->shapes as $keyshape => $shapeinfos):
                if(!isset($results[$shapeinfos[$by]])):
                    $results[$shapeinfos[$by]] = array();
                endif;
                $results[$shapeinfos[$by]][$keyshape] = $shapeinfos;
            endforeach;
        endif; 
        return $results;
	}

	
}