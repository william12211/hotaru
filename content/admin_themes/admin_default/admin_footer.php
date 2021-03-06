<?php 
/**
 * Theme name: admin_default
 * Template name: footer.php
 * Template author: shibuya246
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    shibuya246 <admin@hotarucms.org>
 * @copyright Copyright (c) 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */
    
?>
<?php if ($h->currentUser->adminAccess) { ?>
	<div id="ft" role="contentinfo">
            <div class="">
                <hr/>
		<?php
			$h->pluginHook('admin_footer');
			
			// Link to forums...
			echo "<p>" . $h->lang("admin_theme_footer_having_trouble_vist_forums") . "</p>";
			
			if ($h->isDebug) {
				$h->showQueriesAndTime();
			}
		?>
            </div>
	</div>
<?php } ?>


<?php $h->pluginHook('pre_close_body'); ?>

<script type='text/javascript' src='//cdnjs.cloudflare.com/ajax/libs/knockout/3.2.0/knockout-min.js'></script>
<script type='text/javascript' src='http://code.jquery.com/ui/1.11.1/jquery-ui.js'></script>
<?php $h->doIncludes('js'); ?>

