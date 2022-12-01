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


require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

header('Cache-Control: Public, must-revalidate');
header("Content-type: text/html; charset=".$conf->file->character_set_client);

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

print top_htmlhead('', $titleofpage);


/***********************************************************************************************************************************/
?>
<style type="text/css">
:root {

  --shape-color: <?php echo $conf->global->LOGINPLUS_SHAPE_COLOR; ?>;
  --shape-opacity: <?php echo $conf->global->LOGINPLUS_SHAPE_OPACITY / 100; ?>;
  --bg-color: <?php echo $conf->global->LOGINPLUS_BG_COLOR; ?>;
  --bg-imageopacity: <?php echo $conf->global->LOGINPLUS_BG_IMAGEOPACITY / 100; ?>;
  --main-color: <?php echo $conf->global->LOGINPLUS_MAIN_COLOR; ?>;
  --second-color: <?php echo $conf->global->LOGINPLUS_SECOND_COLOR; ?>;
  --image-opacity: <?php echo $conf->global->LOGINPLUS_IMAGE_OPACITY / 100; ?>;
  --image-color: <?php echo $conf->global->LOGINPLUS_IMAGE_COLOR; ?>;
  --txt-titlecolor: <?php echo $conf->global->LOGINPLUS_TXT_TITLECOLOR; ?>;
  --txt-contentcolor: <?php echo $conf->global->LOGINPLUS_TXT_CONTENTCOLOR; ?>;
  --copyright-color: <?php echo $conf->global->LOGINPLUS_COPYRIGHT_COLOR; ?>;
}

</style>

<!-- BEGIN PHP CUSTOM TEMPLATE LOGINplus! LOGIN.TPL.PHP -->
<body id="loginplus" class="tpl-1">

	<?php if(!empty($conf->global->LOGINPLUS_BG_IMAGEKEY)): ?>
		<div class="loginplus-bgimage" style="background-image: url('<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $conf->global->LOGINPLUS_BG_IMAGEKEY; ?>');background-position: center;"></div>
	<?php endif; ?>

	<div class="loginplus-wrapper <?php if(!empty($conf->global->LOGINPLUS_SHAPE_PATH)): echo $conf->global->LOGINPLUS_SHAPE_PATH; endif; ?>">
		<div class="loginplus-wrapperbox <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-one-side';endif;?>">
			<div class="loginplus-wrapperbox-side image-side <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-hide'; endif;?>">
				<?php if(!empty($conf->global->LOGINPLUS_IMAGE_KEY)): ?>
					<div class="loginplus-img" style="background-image: url('<?php echo $conf->file->dol_url_root['main']; ?>/document.php?hashp=<?php echo $conf->global->LOGINPLUS_IMAGE_KEY; ?>');"></div>
				<?php endif; ?>
					<div class="loginplus-txt">
						<?php if(!empty($conf->global->LOGINPLUS_TXT_TITLE)): echo '<h2>'.$conf->global->LOGINPLUS_TXT_TITLE.'</h2>'; endif; ?>
						<?php if(!empty($conf->global->LOGINPLUS_TXT_CONTENT)): echo '<p>'.$conf->global->LOGINPLUS_TXT_CONTENT.'</p>'; endif; ?>
					</div>
			</div>

			<div class="loginplus-wrapperbox-side content-side <?php if(!$conf->global->LOGINPLUS_TWOSIDES): echo 'ld-extend';endif;?>">
				<img alt="" src="<?php echo $urllogo; ?>" id="loginplus-imglogo" />

				<form id="login" name="login" method="POST" action="<?php echo $php_self; ?>">
					<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
					<input type="hidden" name="action" value="buildnewpassword">
					
					<div class="fields-group" id="login_line1">
						<div class="field-row">
							<?php if($conf->global->LOGINPLUS_SHOW_FORMLABELS): ?><label for="username" class="hidden"><i class="fa fa-user"></i> <?php echo $langs->trans("Login"); ?></label><?php endif; ?>
							<input type="text" id="username" name="username" class="" value="<?php echo dol_escape_htmltag($login); ?>" tabindex="1" autofocus="autofocus" />
						</div>

						<?php if($captcha): 

							$php_self = preg_replace('/[&\?]time=(\d+)/', '', $php_self); // Remove param time
							if (preg_match('/\?/', $php_self)) $php_self .= '&time='.dol_print_date(dol_now(), 'dayhourlog');
							else $php_self .= '?time='.dol_print_date(dol_now(), 'dayhourlog'); ?>
							
							<div class="field-row">
								<?php if($conf->global->LOGINPLUS_SHOW_FORMLABELS): ?><label for="securitycode" class="hidden"><i class="fa fa-unlock"></i> <?php echo $langs->trans("SecurityCode"); ?></label><?php endif; ?>
								<input type="text" id="securitycode" name="code" class=""  maxlength="5" tabindex="1" autofocus="autofocus" autocomplete="off" />
							</div>

							<div class="loginplus-captcha">
									<img class="" src="<?php echo DOL_URL_ROOT; ?>/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />
									<a href="<?php echo $php_self; ?>" ><?php echo $captcha_refresh; ?></a>
							</div>

						<?php endif; ?>
						
						<div class="field-row align-center">
							<input type="submit" class="button" <?php echo $disabled; ?> name="button_password" value="<?php echo $langs->trans('SendNewPassword'); ?>" tabindex="4" />
						</div>					
					</div>
				</form>

				<?php // AFFICHAGE DES MESSAGES D'ERREURS
				if ($message): $message = str_replace('<div class="error">', '', $message); $message = str_replace('</div>', '', $message); ?>
					<div class="loginplus-error-msg">
						<?php echo $message; ?>
					</div>
				<?php endif; ?>

				<div class="loginplus-helplinks align-center">	
					<?php echo '<a class="alogin" href="'.$dol_url_root.'/index.php'.$moreparam.'">'.$langs->trans('BackToLoginPage').'</a>'; ?>
				</div>
			</div>
		</div>
	
	<?php if($conf->global->LOGINPLUS_COPYRIGHT): 

			$copyright_text = $conf->global->LOGINPLUS_COPYRIGHT;
			if($conf->global->LOGINPLUS_COPYRIGHT_LINK):
				$copyright_link = '<a href="'.$conf->global->LOGINPLUS_COPYRIGHT_LINK.'" target="_blank">';
				$copyright_text = str_replace('[', $copyright_link, $copyright_text);
				$copyright_text = str_replace(']', '</a>', $copyright_text);
			endif; ?>

			<div id="loginplus-copyright"><?php echo $copyright_text; ?></div>
		<?php endif; ?>
	</div>


<?php
if (!empty($conf->global->MAIN_HTML_FOOTER)) {
	print $conf->global->MAIN_HTML_FOOTER;
}

if (!empty($morelogincontent) && is_array($morelogincontent)) {
	foreach ($morelogincontent as $format => $option) {
		if ($format == 'js') {
			echo "\n".'<!-- Javascript by hook -->';
			echo $option."\n";
		}
	}
} elseif (!empty($moreloginextracontent)) {
	echo '<!-- Javascript by hook -->';
	echo $moreloginextracontent;
}

// Google Analytics
// TODO Add a hook here
if (!empty($conf->google->enabled) && !empty($conf->global->MAIN_GOOGLE_AN_ID)) {
	$tmptagarray = explode(',', $conf->global->MAIN_GOOGLE_AN_ID);
	foreach ($tmptagarray as $tmptag) {
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
	}
}

// TODO Replace this with a hook
// Google Adsense (need Google module)
if (!empty($conf->google->enabled) && !empty($conf->global->MAIN_GOOGLE_AD_CLIENT) && !empty($conf->global->MAIN_GOOGLE_AD_SLOT)) {
	if (empty($conf->dol_use_jmobile)) {
		?>
	<div class="center"><br>
		<script><!--
			google_ad_client = "<?php echo $conf->global->MAIN_GOOGLE_AD_CLIENT; ?>";
			google_ad_slot = "<?php echo $conf->global->MAIN_GOOGLE_AD_SLOT; ?>";
			google_ad_width = <?php echo $conf->global->MAIN_GOOGLE_AD_WIDTH; ?>;
			google_ad_height = <?php echo $conf->global->MAIN_GOOGLE_AD_HEIGHT; ?>;
			//-->
		</script>
		<script	src="//pagead2.googlesyndication.com/pagead/show_ads.js"></script>
	</div>
		<?php
	}
}
?>
</body>