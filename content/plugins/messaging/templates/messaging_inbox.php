<?php
/**
 * Messaging inbox
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
 * @author    Hotaru CMS Team
 * @copyright Copyright (c) 2009 - 2013, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

?>

<div id="messaging_inbox" class="col-md-9">

    <div class="message-box-header">
        <h2><?php echo $h->lang["messaging_inbox"]; ?></h2>
        <?php echo $h->showMessages(); ?>
    </div>


    <div class="">

    <form role="form" name="inbox_form" action="<?php echo BASEURL; ?>index.php?page=inbox" method="post">
        <table class="table table-hover table-bordered">
            <thead>
                <tr class="messaging_list_headers info">
                    <td class="messaging_fom"><?php echo $h->lang['messaging_to']; ?></td>
                    <td class="messaging_subject"><?php echo $h->lang['messaging_subject']; ?></td>
                    <td class="messaging_date"><?php echo $h->lang['messaging_date']; ?></td>
                    <td class="messaging_delete"></td>
                </tr>
            </thead>
            <tbody>

        <?php if (isset($h->vars['messages_list']->items)) { ?>

            <?php foreach ($h->vars['messages_list']->items as $msg) { ?>

                <?php $read = (!$msg->message_read) ? "unread" : ""; ?>

                <tr id="<?php echo $msg->message_id; ?>" class='<?php echo $read; ?>'>

                    <?php $name = $h->getUserNameFromId($msg->message_from); ?>
                    <td class="messaging_from"><a href="<?php echo $h->url(array('user'=>$name)); ?>"><?php echo $name; ?></a></td>

                    <td class="messaging_subject">
                        <a href="<?php echo BASEURL; ?>index.php?page=show_message&amp;id=<?php echo $msg->message_id; ?>">
                            <?php echo sanitize(urldecode($msg->message_subject), 'all'); ?>
                        </a>
                    </td>

                    <td class="messaging_date"><?php echo date('j M \'y', strtotime($msg->message_date)); ?></td>

                    <td class="messaging_delete"><center><input type="checkbox" name="message[<?php echo $msg->message_id; ?>]" id="message-<?php echo $msg->message_id; ?>" value="delete"></center></td>

                </tr>
            <?php } ?>

        <?php } else { ?>
            <tr><td colspan='4'><center><?php echo $h->lang['messaging_no_messages']; ?></center></td></tr>
        <?php } ?>
        </tbody>
        </table>

        <?php //echo $h->pageBar($h->vars['messages_list']); ?> 

        <p align="right"><input type="submit" class="btn btn-danger" name="delete_selected" value="<?php echo $h->lang['messaging_delete_selected']; ?>" /></p>

    </form>
</div>

</div>