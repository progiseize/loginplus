<?php
/* Copyright (C) 2009-2010 Regis Houssin <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2013 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2022 Progiseize <contact@progiseize.fr>
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


if (!defined('NOBROWSERNOTIF')) define('NOBROWSERNOTIF', 1);

// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

if(getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL')):

	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

	header('Cache-Control: Public, must-revalidate');
	header("Content-type: text/html; charset=".$conf->file->character_set_client);

	dol_include_once('./loginplus/class/loginplus.class.php');
	$loginplus_static = new Loginplus($db);

	if (GETPOST('dol_hide_topmenu')) $conf->dol_hide_topmenu = 1;
	if (GETPOST('dol_hide_leftmenu')) $conf->dol_hide_leftmenu = 1;
	if (GETPOST('dol_optimize_smallscreen')) $conf->dol_optimize_smallscreen = 1;
	if (GETPOST('dol_no_mouse_hover')) $conf->dol_no_mouse_hover = 1;
	if (GETPOST('dol_use_jmobile')) $conf->dol_use_jmobile = 1;

	// If we force to use jmobile, then we reenable javascript
	if (!empty($conf->dol_use_jmobile)) $conf->use_javascript_ajax = 1;

	$php_self = $_SERVER['PHP_SELF'];
	$php_self .= dol_escape_htmltag($_SERVER["QUERY_STRING"]) ? '?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]) : '';
	$php_self = str_replace('action=validatenewpassword', '', $php_self);

	$titleofpage = $langs->trans('SendNewPassword');

	// CSS
	if(!isset($arrayofcss) || empty($arrayofcss)): $arrayofjs = array(); endif;
	if(!isset($arrayofcss) || empty($arrayofcss)): $arrayofcss = array('/loginplus/css/newloginplus.css');
	else: $arrayofcss[] = '/loginplus/css/newloginplus.css';
	endif;

	$disablenofollow = 1;
	if (!preg_match('/'.constant('DOL_APPLICATION_TITLE').'/', $title)):
		$disablenofollow = 0;
	endif;
	if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)):
		$disablenofollow = 0;
	endif;
	top_htmlhead('', $titleofpage, 0, 0, $arrayofjs, $arrayofcss, 1, $disablenofollow);


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
		    --loginplus-box-externalbackground: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUND')); ?>;
		    --loginplus-box-externalcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALCOLOR')); ?>;   
		    --loginplus-box-externalbackgroundhover: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BOX_EXTERNALBACKGROUNDHOVER')); ?>;
		}
	</style>

	<!-- BEGIN PHP CUSTOM TEMPLATE LOGINplus! LOGIN.TPL.PHP -->
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

				<div class="loginplus-box loginplus-boxlogin <?php echo (getDolGlobalInt('LOGINPLUS_SECONDARYBOX_SHADOW')?'with-shadow':''); ?>">

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
						<input type="hidden" name="token" value="<?php echo newToken(); ?>">
						<input type="hidden" name="action" value="buildnewpassword">

						<div id="login_line1" class="loginplus-fields <?php echo (!$disabled?'':'compact-mode') ?> <?php echo (getDolGlobalInt('LOGINPLUS_TWOFACTOR_DARKTHEME')?'dark-theme ':''); echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?'loginplus-viewlabel':''); ?>">
							
							<div id="login_right">
								<div class="loginplus-fieldrow tagtable">
									<label for="username" class="paddingright">
										<i class="fa fa-user"></i>
										<?php echo (getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')?' '.$langs->trans("Login"):''); ?>
									</label>
									<input type="text" id="username" name="username" placeholder="<?php echo $langs->trans("Login"); ?>" class="" value="<?php echo dol_escape_htmltag($login); ?>" tabindex="1" autofocus="autofocus" <?php echo $disabled; ?> />
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
								<input type="submit" <?php echo $disabled; ?> name="button_password" value="<?php echo $langs->trans('SendNewPassword'); ?>" tabindex="4" />
							</div>
							<div class="field-row align-center center"></div>
						</div>

					</form>

					<?php if ($mode == 'dolibarr' || !$disabled): ?>
						<?php if ($action != 'validatenewpassword' && empty($message)): ?>
							<div class="loginplus-warning-msg">
								<?php echo $langs->trans('SendNewPasswordDesc', $mode); ?>
							</div>
						<?php endif; ?>
					<?php else: ?>
						<div class="loginplus-warning-msg">
							<?php echo $langs->trans('AuthenticationDoesNotAllowSendNewPassword', $mode); ?>
						</div>
					<?php endif; ?>

					<?php // AFFICHAGE DES MESSAGES D'ERREURS
					if ($message): $message = str_replace('<div class="error">', '', $message); $message = str_replace('</div>', '', $message); ?>
						<div class="loginplus-error-msg">
							<?php echo $message; ?>
						</div>
					<?php endif; ?>

					<div class="loginplus-helplinks">
						<?php echo '<a class="alogin" href="'.$dol_url_root.'/index.php'.$moreparam.'">'.$langs->trans('BackToLoginPage').'</a>'; ?>
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
		if (isModEnabled('google') && !empty(getDolGlobalString('MAIN_GOOGLE_AN_ID'))):

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

		<?php // Google Adsense (need Google module)
		if (isModEnabled('google') && !empty($conf->global->MAIN_GOOGLE_AD_CLIENT) && !empty($conf->global->MAIN_GOOGLE_AD_SLOT)):
			if (empty($conf->dol_use_jmobile)): ?>
			<div class="center"><br>
				<script><!--
					google_ad_client = "<?php echo $conf->global->MAIN_GOOGLE_AD_CLIENT ?>";
					google_ad_slot = "<?php echo $conf->global->MAIN_GOOGLE_AD_SLOT ?>";
					google_ad_width = <?php echo $conf->global->MAIN_GOOGLE_AD_WIDTH ?>;
					google_ad_height = <?php echo $conf->global->MAIN_GOOGLE_AD_HEIGHT ?>;
					//-->
				</script>
				<script src="//pagead2.googlesyndication.com/pagead/show_ads.js"></script>
			</div> <?php
			endif;
		endif; ?>
	</body>
	</html>
<?php else: include_once(DOL_DOCUMENT_ROOT.'/core/tpl/passwordforgotten.tpl.php'); ?>
<?php endif; ?>