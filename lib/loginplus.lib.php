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

/*************************/

/**
 * SETUP ADMIN MENU
 */

function lp_showAdminMenu($adminmenukey, $user){

    global $db, $langs;
    dol_include_once('./loginplus/class/loginmsg.class.php');

    $adminmenu = array();
    $loginmsgstatic = new loginMsg($db);

    // SETUP
    $adminmenu['setup'] = array(
        'icon' => 'fas fa-palette',
        'title' => $langs->trans('loginplus_CustomLoginPage'),
        'description' => $langs->trans('loginplus_CustomLoginPageDesc'),
        'toggleconst' => 'LOGINPLUS_ACTIVELOGINTPL',
        'link' => dol_buildpath('loginplus/admin/setup.php',1),
        'more_content' => false,
        'user_right' => $user->hasRight('loginplus','configurer'),
    );

    // MAINTENANCE
    $adminmenu['maintenance'] = array(
        'icon' => 'fas fa-exclamation-triangle',
        'title' => $langs->trans('loginplus_option_maintenance'),
        'description' => $langs->trans('loginplus_option_maintenance_desc'),
        'toggleconst' => 'LOGINPLUS_ISMAINTENANCE',
        'link' => dol_buildpath('loginplus/admin/maintenance.php',1),
        'more_content' => false,
        'user_right' => $user->hasRight('loginplus','maintenancemode'),
    );

    // MESSAGES
    $adminmenu['messages'] = array(
        'icon' => 'fas fa-comment-alt',
        'title' => $langs->trans('loginplus_head_loginmsg'),
        'description' => $langs->trans('loginplus_AdminMsgAddDesc'),
        'toggleconst' => '',
        'link' => dol_buildpath('loginplus/admin/msgs.php',1),
        'more_content' => $loginmsgstatic->count_all(),
        'user_right' => $user->hasRight('loginplus','gerer_messages'),
    );

    $html = '';
    foreach ($adminmenu as $menukey => $menudet):
        if($menudet['user_right']):
            $html .= '<div class="doladmin-card '.(($adminmenukey == $menukey)?'card-active':'').'">';
                $html .= '<div class="card-flex">';
                    $html .= '<div class="doladmin-card-icon '.(($menukey == 'maintenance' && getDolGlobalInt('LOGINPLUS_ISMAINTENANCE'))?'icolor-danger':'').'"><i class="'.$menudet['icon'].'"></i></div>';
                    $html .= '<div class="doladmin-card-content">';
                        $html .= '<div class="doladmin-card-title">';
                        if($menudet['user_right']):
                            $html .= '<a href="'.$menudet['link'].'">'.$menudet['title'].'</a>';
                        else:
                            $html .= $menudet['title'];
                        endif;
                        $html .= '</div>';
                        $html .= '<div class="doladmin-card-desc">'.$menudet['description'].'</div>';
                    $html .= '</div>';
                    if(!empty($menudet['toggleconst']) && $menudet['user_right']):
                        $html .= '<div class="doladmin-card-right">'.ajax_constantonoff($menudet['toggleconst'],array(),null,0,0,1).'</div>';
                    elseif(!empty($menudet['more_content']) && $menudet['user_right']):
                        $html .= '<div class="doladmin-card-right"><span style="font-size: 1.15em;font-weight: 700;min-width:34px;letter-spacing: -1px;color: #ccc;text-align: center;display: inline-block;">'.$menudet['more_content'].'</span></div>';
                    endif;
                $html .= '</div>';
            $html .= '</div>';
        endif;
    endforeach;

    return $html;
}


/**
 * GET SHAPES INFORMATIONS
 */
function lp_getShapeInfos($key = ''){

    $shape_array = array(

        // SQUARES
        'split' => array('type' => 'clip_path','category' => 'square','invert' => 'split_inv'),
        'split_plus' => array('type' => 'clip_path','category' => 'square','invert' => 'split_minus'),

        // DIAGONALS
        'corner_tl' => array('type' => 'clip_path','category' => 'diagonals','invert' => 'corner_tr'),
        'corner_bl' => array('type' => 'clip_path','category' => 'diagonals','invert' => 'corner_br'),

        // CORNERS
        'semicorner_tl' => array('type' => 'clip_path','category' => 'corners','invert' => 'semicorner_tr'),
        'semicorner_bl' => array('type' => 'clip_path','category' => 'corners','invert' => 'semicorner_br'),

        // ASC - DESC
        'diagonal_desc' => array('type' => 'clip_path','category' => 'diagside','invert' => 'diagonal_desc_inv'),
        'diagonal_asc' => array('type' => 'clip_path','category' => 'diagside','invert' => 'diagonal_asc_inv'),

        // WAVES - NEW
        'wave-1' => array('type' => 'svg','category' => 'waves','invert' => 'wave-1-inv'),
        'wave-2' => array('type' => 'svg','category' => 'waves','invert' => 'wave-2-inv'),
        'wave-3' => array('type' => 'svg','category' => 'waves','invert' => 'wave-3-inv'),
    ); 

    if(!$key): return $shape_array;
    elseif(isset($shape_array[$key])): return $shape_array[$key]; 
    else: array();
    endif;
}

function loginplusGetShapes($type = ''){

    $new_shape_array = array(

        // SQUARES
        'split' => array('type' => 'clip_path','category' => 'square','invert' => 'split_inv'),
        'split_plus' => array('type' => 'clip_path','category' => 'square','invert' => 'split_minus'),

        // DIAGONALS
        'corner_tl' => array('type' => 'clip_path','category' => 'diagonals','invert' => 'corner_tr'),
        'corner_bl' => array('type' => 'clip_path','category' => 'diagonals','invert' => 'corner_br'),

        // CORNERS
        'semicorner_tl' => array('type' => 'clip_path','category' => 'corners','invert' => 'semicorner_tr'),
        'semicorner_bl' => array('type' => 'clip_path','category' => 'corners','invert' => 'semicorner_br'),

        // ASC - DESC
        'diagonal_desc' => array('type' => 'clip_path','category' => 'diagside','invert' => 'diagonal_desc_inv'),
        'diagonal_asc' => array('type' => 'clip_path','category' => 'diagside','invert' => 'diagonal_asc_inv'),

        // WAVES - NEW
        'wave-1' => array('type' => 'svg','category' => 'waves','invert' => 'wave-1-inv'),
        'wave-2' => array('type' => 'svg','category' => 'waves','invert' => 'wave-2-inv'),
        'wave-3' => array('type' => 'svg','category' => 'waves','invert' => 'wave-3-inv'),
    );

    $tab_shapes = array(
        'rectangle' => array('split','split_plus','split_minus','split_inv'),
        'corner' => array('corner_tl','corner_tr','corner_br','corner_bl'),
        'semicorner' => array('semicorner_tl','semicorner_tr','semicorner_br','semicorner_bl'),
        'diagonal' => array('diagonal_desc','diagonal_asc'),
        'rounded' => array('rounded_bot'),
        'new' => array('wave-1','wave-2')
    ); 

    if(!$type): return $tab_shapes;
    else : return $tab_shapes[$type]; 
    endif;
}

function loginplusAdminPrepareHead(){
    global $langs, $conf, $user;

    $langs->load("loginplus@loginplus");

    $h = 0;
    $head = array();

    /*$head[$h][0] = dol_buildpath("/loginplus/admin/mods.php", 1);
    $head[$h][1] = $langs->trans("Modèles prédéfinis");
    $head[$h][2] = 'mods';
    $h++;*/

    if($user->rights->loginplus->configurer):
        $head[$h][0] = dol_buildpath("/loginplus/admin/setup.php", 1);
        $head[$h][1] = $langs->trans("loginplus_head_customlogin");
        $head[$h][2] = 'setup';
        $h++;
    endif;

    if($user->rights->loginplus->gerer_messages):
        $head[$h][0] = dol_buildpath("/loginplus/admin/msgs.php", 1);
        $head[$h][1] = $langs->trans("loginplus_head_loginmsg");
        $head[$h][2] = 'msg';
        $h++;
    endif;

    $head[$h][0] = dol_buildpath("/loginplus/admin/doc.php", 1);
    $head[$h][1] = $langs->trans("loginplus_head_doc");
    $head[$h][2] = 'doc';
    $h++;

    complete_head_from_modules($conf, $langs, '', $head, $h, 'loginplus');

    return $head;
}

/*
function loginplusGetThemes($theme = ''){

    $themes = array();

    // THEME PASTEL
    $themes['pastel'] = array(
        'label' => 'Pastel',
        'preview' => 'loginplus_pastel_preview.jpg',
        'background' => 'loginplus_pastel_bg.jpg',
        'sideground' => '',
    );

    //
    $themes['coconut'] = array(
        'label' => 'Coconut',
        'preview' => 'loginplus_coconut_preview.jpg',
        'background' => 'david-gavi-Xh_yj0ZYKyA-unsplash.jpg',
        'sideground' => '',
    );

    //
    $themes['technik'] = array(
        'label' => 'Technik',
        'preview' => 'loginplus_technik_preview.jpg',
        'background' => 'loginplus_technik_bg.jpg',
        'sideground' => 'loginplus_technik_side.jpg',
    );

    //
    $themes['forest'] = array(
        'label' => 'Forest',
        'preview' => 'loginplus_forest_preview.jpg',
        'background' => 'marita-kavelashvili-ugnrXk1129g-unsplash.jpg',
        'sideground' => '',
    );

    if(!$theme): return $themes;
    else : return $themes[$theme]; 
    endif;
}

function loginplusGetThemesParams($theme = ''){

    $params = array();

    // THEME PASTEL
    $params['pastel'] = array(
        'LOGINPLUS_SHOW_FORMLABELS'   => '1', // Afficher les labels
        'LOGINPLUS_BG_COLOR'          => '#ffffff', // Couleur Background
        'LOGINPLUS_BG_IMAGEKEY'       => '', // Cle publique image, sera déterminée à l'application du theme
        'LOGINPLUS_BG_IMAGEOPACITY'   => '100',
        'LOGINPLUS_SHAPE_PATH'        => 'no',
        'LOGINPLUS_SHAPE_COLOR'       => '#ffffff',
        'LOGINPLUS_SHAPE_OPACITY'     => '0',
        'LOGINPLUS_MAIN_COLOR'        => '#56b6b6',
        'LOGINPLUS_SECOND_COLOR'      => '#ff9293',
        'LOGINPLUS_TWOSIDES'          => '0',
        'LOGINPLUS_IMAGE_COLOR'       => '',
        'LOGINPLUS_IMAGE_KEY'         => '',
        'LOGINPLUS_IMAGE_OPACITY'     => '',
        'LOGINPLUS_TXT_TITLE'         => '',
        'LOGINPLUS_TXT_TITLECOLOR'    => '',
        'LOGINPLUS_TXT_CONTENT'       => '',
        'LOGINPLUS_TXT_CONTENTCOLOR'  => '',
        'LOGINPLUS_COPYRIGHT'         => 'Inspired by [Starline / Freepik]',
        'LOGINPLUS_COPYRIGHT_COLOR'   => '#000000',
        'LOGINPLUS_COPYRIGHT_LINK'   => 'https://www.freepik.com',
    );

    // THEME VOYAGE
    $params['coconut'] = array(
        'LOGINPLUS_SHOW_FORMLABELS'   => '1', // Afficher les labels
        'LOGINPLUS_BG_COLOR'          => '#ffffff', // Couleur Background
        'LOGINPLUS_BG_IMAGEKEY'       => '', // Cle publique image, sera déterminée à l'application du theme
        'LOGINPLUS_BG_IMAGEOPACITY'   => '100',
        'LOGINPLUS_SHAPE_PATH'        => 'split',
        'LOGINPLUS_SHAPE_COLOR'       => '#3b2b2c',
        'LOGINPLUS_SHAPE_OPACITY'     => '75',
        'LOGINPLUS_MAIN_COLOR'        => '#a27570',
        'LOGINPLUS_SECOND_COLOR'      => '#765550',
        'LOGINPLUS_TWOSIDES'          => '0',
        'LOGINPLUS_IMAGE_COLOR'       => '',
        'LOGINPLUS_IMAGE_KEY'         => '',
        'LOGINPLUS_IMAGE_OPACITY'     => '',
        'LOGINPLUS_TXT_TITLE'         => '',
        'LOGINPLUS_TXT_TITLECOLOR'    => '',
        'LOGINPLUS_TXT_CONTENT'       => '',
        'LOGINPLUS_TXT_CONTENTCOLOR'  => '',
        'LOGINPLUS_COPYRIGHT'         => 'Crédits photo [Unsplash]',
        'LOGINPLUS_COPYRIGHT_COLOR'   => '#a27570',
        'LOGINPLUS_COPYRIGHT_LINK'    => 'https://unsplash.com/photos/Xh_yj0ZYKyA',
    );

    // THEME BLUE
    $params['technik'] = array(
        'LOGINPLUS_SHOW_FORMLABELS'   => '1', // Afficher les labels
        'LOGINPLUS_BG_COLOR'          => '#032a47', // Couleur Background
        'LOGINPLUS_BG_IMAGEKEY'       => '', // Cle publique image, sera déterminée à l'application du theme
        'LOGINPLUS_BG_IMAGEOPACITY'   => '100',
        'LOGINPLUS_SHAPE_PATH'        => 'rounded_bot',
        'LOGINPLUS_SHAPE_COLOR'       => '#ffffff',
        'LOGINPLUS_SHAPE_OPACITY'     => '100',
        'LOGINPLUS_MAIN_COLOR'        => '#033963',
        'LOGINPLUS_SECOND_COLOR'      => '#032844',
        'LOGINPLUS_TWOSIDES'          => '1',
        'LOGINPLUS_IMAGE_COLOR'       => '#032844',
        'LOGINPLUS_IMAGE_KEY'         => '',
        'LOGINPLUS_IMAGE_OPACITY'     => '100',
        'LOGINPLUS_TXT_TITLE'         => '',
        'LOGINPLUS_TXT_TITLECOLOR'    => '#ffffff',
        'LOGINPLUS_TXT_CONTENT'       => '',
        'LOGINPLUS_TXT_CONTENTCOLOR'  => '#ffffff',
        'LOGINPLUS_COPYRIGHT'         => 'Inspired by [fullvector / Freepik]',
        'LOGINPLUS_COPYRIGHT_COLOR'   => '#a2a2a2',
        'LOGINPLUS_COPYRIGHT_LINK'    => 'https://www.freepik.com',
    );

    // THEME GREEN
    $params['forest'] = array(
        'LOGINPLUS_SHOW_FORMLABELS'   => '1', // Afficher les labels
        'LOGINPLUS_BG_COLOR'          => '#ffffff', // Couleur Background
        'LOGINPLUS_BG_IMAGEKEY'       => '', // Cle publique image, sera déterminée à l'application du theme
        'LOGINPLUS_BG_IMAGEOPACITY'   => '100', // Opacité de l'image de fond
        'LOGINPLUS_SHAPE_PATH'        => 'diagonal_asc', // Type de forme
        'LOGINPLUS_SHAPE_COLOR'       => '#072c20', // Couleur de forme
        'LOGINPLUS_SHAPE_OPACITY'     => '90', // Opacité de forme
        'LOGINPLUS_MAIN_COLOR'        => '#5c874b', // Couleur principale icones - bouton
        'LOGINPLUS_SECOND_COLOR'      => '#072c20', // Couleur survol sumbit
        'LOGINPLUS_TWOSIDES'          => '1', // Boite double
        'LOGINPLUS_IMAGE_COLOR'       => '#072c20', // Arriere plan sidebox
        'LOGINPLUS_IMAGE_KEY'         => '', // Image sidebox
        'LOGINPLUS_IMAGE_OPACITY'     => '', // Opacité del'image sidebox
        'LOGINPLUS_TXT_TITLE'         => 'Lorem ipsum dolor sit', // Titre
        'LOGINPLUS_TXT_TITLECOLOR'    => '#aab265', // Couleur du titre
        'LOGINPLUS_TXT_CONTENT'       => 'Alii summum decus in carruchis solito altioribus et ambitioso vestium cultu ponentes sudant sub ponderibus lacernarum, quas in collis insertas cingulis ipsis adnectunt nimia subtegminum tenuitate perflabiles',
        'LOGINPLUS_TXT_CONTENTCOLOR'  => '#ffffff',        
        'LOGINPLUS_COPYRIGHT'         => 'Crédits photo [Unsplash]',
        'LOGINPLUS_COPYRIGHT_COLOR'   => '#5c874b',
        'LOGINPLUS_COPYRIGHT_LINK'   => 'https://unsplash.com/photos/ugnrXk1129g',
    );

    if(!$theme): return $params;
    else : return $params[$theme]; 
    endif;
}

function loginplusApplyTheme($params){

    global $conf, $db, $user;
    $error = 0;

    foreach($params as $param_key => $param_value):
        if(!dolibarr_set_const($db, $param_key,$param_value,'chaine',0,'',$conf->entity)): $error++; endif;
    endforeach;

    if(!$error):$db->commit(); setEventMessages('Configuration sauvegardée.', null, 'mesgs');
    else: $db->rollback(); setEventMessages('Une erreur est survenue', null, 'errors');
    endif;
}*/




function loginplusGetShareImages(){

    global $conf, $db;

    $sql = "SELECT rowid, ref, label, share, filepath, filename FROM ".MAIN_DB_PREFIX."ecm_files WHERE share IS NOT NULL AND filepath='ecm/loginplus' AND entity = ".$conf->entity;

    $tab_img = array();

    $results_sql = $db->query($sql);
    if($results_sql): $nb_images = $db->num_rows($results_sql); $i = 0;
        while ($i < $nb_images): $img = $db->fetch_object($results_sql);
            array_push($tab_img, $img);
            $i++;
        endwhile;
    endif;

    return $tab_img;
}