<?php
/* Copyright (C) 2009-2015 Regis Houssin <regis.houssin@inodbox.com>
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

// Need global variable $title to be defined by caller (like dol_loginfunction)
// Caller can also set 	$morelogincontent = array(['options']=>array('js'=>..., 'table'=>...);


// Protection to avoid direct call of template
if (empty($conf) || !is_object($conf)) { print "Error, template page can't be called as URL"; exit;}

if(getDolGlobalInt('LOGINPLUS_ACTIVELOGINTPL')):

	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

	$langs->load('loginplus@loginplus');

	header('Cache-Control: Public, must-revalidate');
	header("Content-type: text/html; charset=".$conf->file->character_set_client);

	if (GETPOST('dol_hide_topmenu')) $conf->dol_hide_topmenu = 1;
	if (GETPOST('dol_hide_leftmenu')) $conf->dol_hide_leftmenu = 1;
	if (GETPOST('dol_optimize_smallscreen')) $conf->dol_optimize_smallscreen = 1;
	if (GETPOST('dol_no_mouse_hover')) $conf->dol_no_mouse_hover = 1;
	if (GETPOST('dol_use_jmobile')) $conf->dol_use_jmobile = 1;

	// If we force to use jmobile, then we reenable javascript
	if (!empty($conf->dol_use_jmobile)) $conf->use_javascript_ajax = 1;

	$php_self = dol_escape_htmltag($_SERVER['PHP_SELF']);
	$php_self .= dol_escape_htmltag($_SERVER["QUERY_STRING"]) ? '?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]) : '';
	if (!preg_match('/mainmenu=/', $php_self)) $php_self .= (preg_match('/\?/', $php_self) ? '&' : '?').'mainmenu=home';

	// Javascript code on logon page only to detect user tz, dst_observed, dst_first, dst_second
	$arrayofjs = array(
		'/includes/jstz/jstz.min.js'.(empty($conf->dol_use_jmobile) ? '' : '?version='.urlencode(DOL_VERSION)),
		'/core/js/dst.js'.(empty($conf->dol_use_jmobile) ? '' : '?version='.urlencode(DOL_VERSION))
	);
	$titleofloginpage = $langs->trans('Login').' @ '.$titletruedolibarrversion; // $titletruedolibarrversion is defined by dol_loginfunction in security2.lib.php. We must keep the @, some tools use it to know it is login page and find true dolibarr version.

	$disablenofollow = 1;
	if (!preg_match('/'.constant('DOL_APPLICATION_TITLE').'/', $title)) $disablenofollow = 0;
	if (!empty(getDolGlobalString('MAIN_OPTIMIZEFORTEXTBROWSER'))) $disablenofollow = 0;

	print top_htmlhead('', $titleofloginpage, 0, 0, $arrayofjs, $arrayofcss, 0, $disablenofollow);

	/***********************************************************************************************************************************/
	?>
	<style type="text/css">
	:root {

		--bg-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_BG_COLOR')); ?>;
		--shape-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_SHAPE_COLOR')); ?>;
		--main-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_MAIN_COLOR')); ?>;
		--second-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_SECOND_COLOR')); ?>;
		--image-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_IMAGE_COLOR')); ?>;
		--txt-titlecolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_TXT_TITLECOLOR')); ?>;
		--txt-contentcolor: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_TXT_CONTENTCOLOR')); ?>;
		--copyright-color: #<?php echo str_replace('#', '', getDolGlobalString('LOGINPLUS_COPYRIGHT_COLOR')); ?>;

		--shape-opacity: <?php echo getDolGlobalInt('LOGINPLUS_SHAPE_OPACITY') / 100; ?>;
		--bg-imageopacity: <?php echo getDolGlobalInt('LOGINPLUS_BG_IMAGEOPACITY') / 100; ?>;		
		--image-opacity: <?php echo getDolGlobalInt('LOGINPLUS_IMAGE_OPACITY') / 100; ?>;	
		
	}

	</style>

	<!-- <?php echo dol_escape_htmltag($title); ?> -->

	<!-- BEGIN PHP CUSTOM TEMPLATE loginplus! LOGIN.TPL.PHP -->
	<body id="loginplus" class="tpl-1" >

		<?php if(!empty(getDolGlobalString('LOGINPLUS_BG_IMAGEKEY'))): ?>
			<div class="loginplus-bgimage" style="background-image: url('<?php echo DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.urlencode('loginplus/'.getDolGlobalString('LOGINPLUS_BG_IMAGEKEY')); ?>');background-position: center;"></div>
		<?php endif; ?>

		<?php // INCLUDE SVG
        /*if(getDolGlobalString('LOGINPLUS_SHAPE_PATH') != 'no'): ?>
            <div class="loginplus-shape">
                <?php dol_include_once('./loginplus/svg/blob-2.svg'); ?>
            </div>
        <?php endif;*/ ?>

		<div class="loginplus-wrapper <?php if(!empty(getDolGlobalString('LOGINPLUS_SHAPE_PATH'))): echo getDolGlobalString('LOGINPLUS_SHAPE_PATH'); endif; ?>">
			<div class="loginplus-wrapperbox <?php if(!getDolGlobalInt('LOGINPLUS_TWOSIDES')): echo 'ld-one-side';endif;?>">

				<div class="loginplus-wrapperbox-side image-side <?php if(!getDolGlobalInt('LOGINPLUS_TWOSIDES')): echo 'ld-hide';endif;?>">
					<?php if(!empty(getDolGlobalString('LOGINPLUS_IMAGE_KEY'))): ?>
						<div class="loginplus-img" style="background-image: url('<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo getDolGlobalString('LOGINPLUS_IMAGE_KEY'); ?>');background-size: cover;background-position: center;"></div>
					<?php endif; ?>
						<div class="loginplus-txt">
							<?php if(!empty(getDolGlobalString('LOGINPLUS_TXT_TITLE'))): echo '<h2>'.getDolGlobalString('LOGINPLUS_TXT_TITLE').'</h2>'; endif; ?>
							<?php if(!empty(getDolGlobalString('LOGINPLUS_TXT_CONTENT'))): echo '<p>'.getDolGlobalString('LOGINPLUS_TXT_CONTENT').'</p>'; endif; ?>
						</div>
				</div>

				<div class="loginplus-wrapperbox-side content-side <?php if(!getDolGlobalInt('LOGINPLUS_TWOSIDES')): echo 'ld-extend';endif;?>">
					<img alt="" src="<?php echo $urllogo; ?>" id="loginplus-imglogo" />

					<form id="login" name="login" method="post" action="<?php echo $php_self; ?>">
						<input type="hidden" name="token" value="<?php echo newToken(); ?>" />
						<input type="hidden" name="actionlogin" value="login">
						<input type="hidden" name="loginfunction" value="loginfunction" />

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
						
						<div class="fields-group" id="login_line1">
							<div id="login_right">
								<div class="field-row tagtable">
									<?php if(getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')): ?><label for="username"><i class="fa fa-user"></i> <?php echo $langs->trans("Login"); ?></label><?php endif; ?>
									<input type="text" id="username" name="username" placeholder="<?php echo $langs->trans("Login"); ?>" class="" value="<?php echo dol_escape_htmltag($login); ?>" tabindex="1" autofocus="autofocus" />
								</div>

								<div class="field-row tagtable">
									<?php if(getDolGlobalInt('LOGINPLUS_SHOW_FORMLABELS')): ?><label for="password"><i class="fa fa-key"></i> <?php echo $langs->trans("Password"); ?></label><?php endif; ?>
									<input id="password" placeholder="<?php echo $langs->trans("Password"); ?>" name="password" class="" type="password" value="<?php echo dol_escape_htmltag($password); ?>" tabindex="2" autocomplete="<?php echo empty(getDolGlobalInt('MAIN_LOGIN_ENABLE_PASSWORD_AUTOCOMPLETE')) ? 'off' : 'on'; ?>" />
								</div>

								<?php if ($captcha):
									$php_self = preg_replace('/[&\?]time=(\d+)/', '', $php_self); // Remove param time
									if (preg_match('/\?/', $php_self)): $php_self .= '&time='.dol_print_date(dol_now(), 'dayhourlog');
									else: $php_self .= '?time='.dol_print_date(dol_now(), 'dayhourlog'); endif; ?>

									<div class="field-row tagtable">
										<label for="securitycode"><i class="fa fa-unlock"></i> <?php echo $langs->trans("SecurityCode"); ?></label>
										<div class="lgp-flex">
											<span class="span-icon-security inline-block">
											<input id="securitycode" placeholder="<?php echo $langs->trans("SecurityCode"); ?>" class="flat input-icon-security width150" type="text" maxlength="5" name="code" tabindex="3" />
										</span>
										<span class="nowrap inline-block">
											<img class="inline-block valignmiddle" src="<?php echo DOL_URL_ROOT ?>/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />
											<a class="inline-block valignmiddle captcha-link" href="<?php echo $php_self; ?>" tabindex="4" data-role="button"><?php echo $captcha_refresh; ?></a>
										</span>
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

						<div class="fields-group" id="login_line2">
							<div class="field-row align-center">
								<input type="submit" class="button" value="&nbsp; <?php echo $langs->trans('Connection'); ?> &nbsp;" tabindex="5" />
							</div>
							<div class="field-row align-center center"></div>
						</div>
					</form>

					<?php // MODE MAINTENANCE
					if (getDolGlobalInt('LOGINPLUS_ISMAINTENANCE')): ?>
						<div class="loginplus-maintenance-msg">
							<?php if(getDolGlobalString('LOGINPLUS_MAINTENANCETEXT')): echo getDolGlobalString('LOGINPLUS_MAINTENANCETEXT');
							else: echo $langs->trans('loginplus_option_maintenance_activated');
							endif;?>
						</div>
					<?php endif; ?>

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

					<div class="loginplus-helplinks align-center">	
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

				</div>
			</div>

			<?php if (!empty(getDolGlobalString('MAIN_HTML_FOOTER'))): print getDolGlobalString('MAIN_HTML_FOOTER'); endif; ?>

			<?php if(getDolGlobalString('LOGINPLUS_COPYRIGHT')): 
				$copyright_text = getDolGlobalString('LOGINPLUS_COPYRIGHT');
				if(getDolGlobalString('LOGINPLUS_COPYRIGHT_LINK')):
					$copyright_link = '<a href="'.getDolGlobalString('LOGINPLUS_COPYRIGHT_LINK').'" target="_blank">';
					$copyright_text = str_replace('[', $copyright_link, $copyright_text);
					$copyright_text = str_replace(']', '</a>', $copyright_text);
				endif; ?>
				<div id="loginplus-copyright"><?php echo $copyright_text; ?></div>
			<?php endif; ?>
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

	</body>
	</html>
	<!-- END PHP TEMPLATE -->

<?php else: include_once(DOL_DOCUMENT_ROOT.'/core/tpl/login.tpl.php'); ?>
<?php endif; ?>