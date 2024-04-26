<?php
/* Copyright (C) 2009-2015 Regis Houssin <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2013 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2024 Progiseize <contact@progiseize.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Need global variable $title to be defined by caller (like dol_loginfunction)
// Caller can also set 	$morelogincontent = array(['options']=>array('js'=>..., 'table'=>...);
// $titletruedolibarrversion must be defined

if (!defined('NOBROWSERNOTIF')) {define('NOBROWSERNOTIF', 1);}

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf)) { print "Error, template page can't be called as URL"; exit;}

if(getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL')):

	// DDOS protection
	$size = (empty($_SERVER['CONTENT_LENGTH']) ? 0 : (int) $_SERVER['CONTENT_LENGTH']);
	if ($size > 10000) {
		$langs->loadLangs(array("errors", "install"));
		httponly_accessforbidden('<center>'.$langs->trans("ErrorRequestTooLarge").'.<br><a href="'.DOL_URL_ROOT.'">'.$langs->trans("ClickHereToGoToApp").'</a></center>', 413, 1);
	}

	//
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	dol_include_once('./loginplus/class/loginplus.class.php');

	$loginplus_static = new Loginplus($db);

	//
	$langs->load('loginplus@loginplus');

	// VIDE LE CACHE
	header('Cache-Control: Public, must-revalidate');
	header("Content-type: text/html; charset=".$conf->file->character_set_client);

	//
	if (GETPOST('dol_hide_topmenu')) $conf->dol_hide_topmenu = 1;
	if (GETPOST('dol_hide_leftmenu')) $conf->dol_hide_leftmenu = 1;
	if (GETPOST('dol_optimize_smallscreen')) $conf->dol_optimize_smallscreen = 1;
	if (GETPOST('dol_no_mouse_hover')) $conf->dol_no_mouse_hover = 1;
	if (GETPOST('dol_use_jmobile')) $conf->dol_use_jmobile = 1;

	// If we force to use jmobile, then we reenable javascript
	if (!empty($conf->dol_use_jmobile)) $conf->use_javascript_ajax = 1;

	$php_self = empty($php_self) ? dol_escape_htmltag($_SERVER['PHP_SELF']) : $php_self;
	$php_self .= dol_escape_htmltag($_SERVER["QUERY_STRING"]) ? '?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]) : '';
	if (!preg_match('/mainmenu=/', $php_self)) {
		$php_self .= (preg_match('/\?/', $php_self) ? '&' : '?').'mainmenu=home';
	}
	if (preg_match('/'.preg_quote('core/modules/oauth', '/').'/', $php_self)) {
		$php_self = DOL_URL_ROOT.'/index.php?mainmenu=home';
	}
	$php_self = preg_replace('/(\?|&amp;|&)action=[^&]+/', '\1', $php_self);
	$php_self = preg_replace('/(\?|&amp;|&)username=[^&]*/', '\1', $php_self);
	$php_self = preg_replace('/(\?|&amp;|&)entity=\d+/', '\1', $php_self);
	$php_self = preg_replace('/(\?|&amp;|&)massaction=[^&]+/', '\1', $php_self);
	$php_self = preg_replace('/(\?|&amp;|&)token=[^&]+/', '\1', $php_self);

	// Javascript code on logon page only to detect user tz, dst_observed, dst_first, dst_second
	$arrayofjs = array(
		'/includes/jstz/jstz.min.js'.(empty($conf->dol_use_jmobile) ? '' : '?version='.urlencode(DOL_VERSION)),
		'/core/js/dst.js'.(empty($conf->dol_use_jmobile) ? '' : '?version='.urlencode(DOL_VERSION))
	);
	$titleofloginpage = $langs->trans('Login').' @ '.$titletruedolibarrversion; // $titletruedolibarrversion is defined by dol_loginfunction in security2.lib.php. We must keep the @, some tools use it to know it is login page and find true dolibarr version.

	$disablenofollow = 1;
	if (!preg_match('/'.constant('DOL_APPLICATION_TITLE').'/', $title)) $disablenofollow = 0;
	if (!empty(getDolGlobalString('MAIN_OPTIMIZEFORTEXTBROWSER'))) $disablenofollow = 0;

	// CSS
	if(!isset($arrayofcss) || empty($arrayofcss)): $arrayofcss = array('/loginplus/css/newloginplus.css');
	else: $arrayofcss[] = '/loginplus/css/newloginplus.css';
	endif;
	print top_htmlhead('', $titleofloginpage, 0, 0, $arrayofjs, $arrayofcss, 0, $disablenofollow);

	/***********************************************************************************************************************************/
	?>
	<style type="text/css">
	:root {
		--loginplus-bg-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BG_COLOR')); ?>;
	    --loginplus-bg-imageopacity: <?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY') / 100; ?>;
	    --loginplus-shape-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_SHAPE_COLOR')); ?>;
	    --loginplus-box-background: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_BACKGROUND')); ?>;
	    --loginplus-box-radius: <?php echo getDolGlobalString('LOGINPLUS_BOX_RADIUS').'px'; ?>;
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
	}

	</style>

	<!-- BEGIN PHP CUSTOM TEMPLATE loginplus! LOGIN.TPL.PHP -->
	<body id="loginplus" class="<?php echo getDolGlobalString('LOGINPLUS_TEMPLATE').' loginbox-align-'.getDolGlobalString('LOGINPLUS_BOX_ALIGN'); ?>">

		<?php if(getDolGlobalInt('LOGINPLUS_SHOW_DOLILINK')): ?>
		<div class="loginplus-doliversion"><?php echo dol_escape_htmltag($title); ?></div>
		<?php endif; ?>

		<?php
		// BACKGROUND IMAGE
		if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))):
			echo '<div class="loginplus-background" style="background-image: url(\''.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_BG_IMAGEKEY')).'\');" ></div>';
		endif; 

		// BACKGROUND SHAPE
		$path_name = getDolGlobalString('LOGINPLUS_SHAPE_PATH');
		$path_shape = $loginplus_static->getShapes($path_name);
		if(!empty($path_shape)):
			echo '<div class="loginplus-shape shape-'.$path_shape['type'].' '.$path_name.' '.(getDolGlobalInt('LOGINPLUS_SHAPE_ALT')?'alternate':'').'">';
				if($path_shape['type'] == 'svg'):
					$shape_folder = dol_buildpath('loginplus/svg');
					if(file_exists($shape_folder.'/'.$path_name.'.svg')):
	                	echo file_get_contents($shape_folder.'/'.$path_name.'.svg');
	                endif;
	            endif;
			echo '</div>';
		endif; ?>

		<?php // MODE MAINTENANCE
		if (getDolGlobalInt('LOGINPLUS_ISMAINTENANCE')): ?>
			<div class="loginplus-maintenance-msg">
				<?php if(getDolGlobalString('LOGINPLUS_MAINTENANCETEXT')): echo getDolGlobalString('LOGINPLUS_MAINTENANCETEXT');
				else: echo $langs->trans('loginplus_option_maintenance_activated');
				endif;?>
			</div>
		<?php endif; ?>

		<div class="loginplus-global-wrapper <?php echo 'box-'.getDolGlobalString('LOGINPLUS_BOX_ALIGN'); ?>">
			<div class="loginplus-wrapper">

				<?php if(getDolGlobalString('LOGINPLUS_TEMPLATE') == 'template_two'): ?>
					<div class="loginplus-box loginplus-boxside">
						<?php 
						// SIDE IMAGE
						if(!empty(getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY'))):
							echo '<div class="loginplus-boximage" style="background-image: url(\''.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_SIDEBG_IMAGEKEY')).'\');" ></div>';
						endif; ?>

						<div class="loginplus-boxtxt">
							<div class="loginplus-title"><?php echo getDolGlobalString('LOGINPLUS_TXT_TITLE'); ?></div>
							<div class="loginplus-content"><?php echo getDolGlobalString('LOGINPLUS_TXT_CONTENT'); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<div class="loginplus-box loginplus-boxlogin">

					<?php 
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
			     	if(!empty($urllogo)): 
			     		echo '<div class="loginplus-boxlogin-logo"><img src="'.$urllogo.'"></div>';
			     	endif; ?>

			     	<form id="login" name="login" method="post" action="<?php echo $php_self; ?>">
						<input type="hidden" name="token" value="<?php echo newToken(); ?>" />
						<input type="hidden" name="actionlogin" value="login">
						<input type="hidden" name="loginfunction" value="loginfunction" />
						<input type="hidden" name="backtopage" value="<?php echo GETPOST('backtopage'); ?>" />

						<input type="hidden" name="tz" id="tz" value="" />
						<input type="hidden" name="tz_string" id="tz_string" value="" />

						<input type="hidden" name="dst_observed" id="dst_observed" value="" />
						<input type="hidden" name="dst_first" id="dst_first" value="" />
						<input type="hidden" name="dst_second" id="dst_second" value="" />
						<input type="hidden" name="screenwidth" id="screenwidth" value="" />
						<input type="hidden" name="screenheight" id="screenheight" value="" />

						<input type="hidden" name="dol_hide_topmenu" id="dol_hide_topmenu" value="<?php echo $dol_hide_topmenu; ?>" />
						<input type="hidden" name="dol_hide_leftmenu" id="dol_hide_leftmenu" value="<?php echo $dol_hide_leftmenu; ?>" />
						<input type="hidden" name="dol_optimize_smallscreen" id="dol_optimize_smallscreen" value="<?php echo $dol_optimize_smallscreen; ?>" />
						<input type="hidden" name="dol_no_mouse_hover" id="dol_no_mouse_hover" value="<?php echo $dol_no_mouse_hover; ?>" />
						<input type="hidden" name="dol_use_jmobile" id="dol_use_jmobile" value="<?php echo $dol_use_jmobile; ?>" />

						<div id="login_line1" class="loginplus-fields <?php echo (getDolGlobalInt('LOGINPLUS_TWOFACTOR_DARKTHEME')?'dark-theme ':''); echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?'loginplus-viewlabel':''); ?>">
							<div id="login_right">
								<div class="loginplus-fieldrow tagtable">
									<label for="username" class="paddingright">
										<i class="fa fa-user"></i>
										<?php echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?' '.$langs->trans("Login"):''); ?>
									</label>
									<input type="text" id="username" name="username" placeholder="<?php echo $langs->trans("Login"); ?>" class="" value="<?php echo dol_escape_htmltag($login); ?>" tabindex="1" autofocus="autofocus" />
								</div>
								<div class="loginplus-fieldrow tagtable">
									<label for="password">
										<i class="fa fa-key"></i>
										<?php echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?' '.$langs->trans("Password"):''); ?>
									</label>
									<input id="password" placeholder="<?php echo $langs->trans("Password"); ?>" name="password" class="" type="password" value="<?php echo dol_escape_htmltag($password); ?>" tabindex="2" autocomplete="<?php echo empty(getDolGlobalInt('MAIN_LOGIN_ENABLE_PASSWORD_AUTOCOMPLETE')) ? 'off' : 'on'; ?>" />
								</div>

								<?php if ($captcha):
									$php_self = preg_replace('/[&\?]time=(\d+)/', '', $php_self); // Remove param time
									if (preg_match('/\?/', $php_self)): $php_self .= '&time='.dol_print_date(dol_now(), 'dayhourlog');
									else: $php_self .= '?time='.dol_print_date(dol_now(), 'dayhourlog'); endif; ?>

									<div class="loginplus-fieldrow row-captcha tagtable">
										<label for="securitycode">
											<i class="fa fa-unlock"></i>
											<?php echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?' '.$langs->trans("SecurityCode"):''); ?>
										</label>
										
										<div class="loginplus-captcha">
											<input id="securitycode" placeholder="<?php echo $langs->trans("SecurityCode"); ?>" class="" type="text" maxlength="5" name="code" tabindex="3" />											
											<img class="inline-block valignmiddle" src="<?php echo DOL_URL_ROOT ?>/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />
											<a class="inline-block valignmiddle captcha-link" href="<?php echo $php_self; ?>" tabindex="4" data-role="button"><?php echo $captcha_refresh; ?></a>
										</div>
									</div>									
								<?php endif; ?>

								<?php // MORELOGINCONTENT ?>
								<?php if (!empty($morelogincontent)): 
									if (is_array($morelogincontent)):
										foreach ($morelogincontent as $format => $option):
											if ($format == 'table'):
												echo '<!-- Option by hook -->';
												echo $option;
											endif;
										endforeach;
									else:
										echo '<!-- Option by hook -->';
										echo $morelogincontent;
									endif;
								endif; ?>
							</div>
						</div>

						<div id="login_line2" class="loginplus-submit">
							<div class="field-row align-center">
								<input type="submit" id="" name="" value="<?php echo $langs->trans('Connection'); ?>" tabindex="5">
							</div>
							<div class="field-row align-center center"></div>
						</div>

					</form>

					<?php // AFFICHAGE DES MESSAGES D'ERREURS
					if (GETPOST('maintenance') && GETPOST('noadmin')): ?>
						<div class="loginplus-error-msg">
							<?php echo $langs->trans('loginplus_option_maintenance_activated_nologin'); ?>
						</div>
					<?php endif; ?>

					<?php // AFFICHAGE DES MESSAGES D'ERREURS
					if (!empty($_SESSION['dol_loginmesg'])): ?>
						<div class="loginplus-error-msg">
							<?php echo $_SESSION['dol_loginmesg']; ?>
						</div>
					<?php endif; ?>

					<div class="loginplus-helplinks">	
						<?php if ($forgetpasslink): $url = DOL_URL_ROOT.'/user/passwordforgotten.php'.$moreparam;
							if (!empty(getDolGlobalString('MAIN_PASSWORD_FORGOTLINK'))): $url = getDolGlobalString('MAIN_PASSWORD_FORGOTLINK'); endif;
							echo '<a class="alogin" href="'.dol_escape_htmltag($url).'">'.$langs->trans('PasswordForgotten').'</a>';
						endif; ?>
						<?php if ($forgetpasslink && $helpcenterlink): echo ' - '; endif; ?>
						<?php if ($helpcenterlink): $url = DOL_URL_ROOT.'/support/index.php'.$moreparam;
							if (!empty(getDolGlobalString('MAIN_HELPCENTER_LINKTOUSE'))) $url = getDolGlobalString('MAIN_HELPCENTER_LINKTOUSE');
							echo '<a class="alogin" href="'.dol_escape_htmltag($url).'" target="_blank">'.$langs->trans('NeedHelpCenter').'</a>';
						endif; ?>
					</div>

					<?php //if (!empty(getDolGlobalString('MAIN_HTML_FOOTER'))): print getDolGlobalString('MAIN_HTML_FOOTER'); endif; ?>

				</div>
			</div>			
		</div>

		<?php // MORELOGINCONTENT JS
		if (!empty($morelogincontent) && is_array($morelogincontent)):
			foreach ($morelogincontent as $format => $option):
				if ($format == 'js'): echo "\n".'<!-- Javascript by hook -->'; echo $option."\n"; endif;
			endforeach;
		elseif (!empty($moreloginextracontent)):
			echo '<!-- Javascript by hook -->'; echo $moreloginextracontent;
		endif; ?>

		<?php // Google Analytics
		if (!empty($conf->google->enabled) && !empty(getDolGlobalString('MAIN_GOOGLE_AN_ID'))):

			$tmptagarray = explode(',', getDolGlobalString('MAIN_GOOGLE_AN_ID'));
			foreach ($tmptagarray as $tmptag):
				print "\n";
				print "<!-- JS CODE TO ENABLE for google analtics tag -->\n";
				print "
					<!-- Global site tag (gtag.js) - Google Analytics -->
					<script async src=\"https://www.googletagmanager.com/gtag/js?id=".trim($tmptag)."\"></script>
					<script>
					window.dataLayer = window.dataLayer || [];
					function gtag(){dataLayer.push(arguments);}
					gtag('js', new Date());

					gtag('config', '".trim($tmptag)."');
					</script>";
				print "\n";
			endforeach;
		endif; ?>

		<script type="text/javascript">
			let lp_loginform = document.querySelector('#login');
			let lp_formsubmit = lp_loginform.querySelector('input[type=submit]');
			lp_formsubmit.addEventListener('click', function (a){
				setTimeout(function(e){
					const element = document.querySelector('#totp');
					if (element) {element.focus();}
				},300);	
			});
		</script>
	</body>	
	</html>
	<!-- END PHP TEMPLATE -->

<?php else: include_once(DOL_DOCUMENT_ROOT.'/core/tpl/login.tpl.php'); ?>
<?php endif; ?>