<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2017 Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Prevent direct access
defined('_JEXEC') or die;

$layoutPath = JPluginHelper::getLayoutPath('loginguard', 'u2f', 'error');
include $layoutPath;

?>
<div id="loginguard-u2f-controls">
    <input name="code" value="" id="loginGuardCode" class="form-control input-lg" type="hidden">

    <div class="akeeba-form-group--pull-right" id="loginguard-u2f-button">
        <div class="akeeba-form-group--actions">
            <a class="akeeba-btn--primary--large"
               onclick="akeebaLoginGuardU2FOnClick();">
                <span class="icon icon-lock glyphicon glyphicon-lock"></span>
                <?php echo JText::_('PLG_LOGINGUARD_U2F_LBL_VALIDATEKEY'); ?>
            </a>
        </div>
    </div>
</div>
