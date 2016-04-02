<?php

/**
 * @package WF MediaElement
 * @copyright Copyright (C) 2014 Ryan Demmer. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see licence.txt
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * A Joomla Extension wrapper for the MediaElement.js library - http://mediaelementjs.com/
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * WF MediaElement Plugin
 *
 * @package 	WF MediaElement
 * @subpackage	System
 */
class plgSystemWfmediaelement extends JPlugin {

    private $version = '@@version@@';

    /**
     * onAfterDispatch function
     * @return Boolean true
     */
    public function onAfterDispatch() {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            return;
        }

        $document = JFactory::getDocument();
        $docType = $document->getType();

        // only in html pages
        if ($docType != 'html') {
            return;
        }

        // Causes issue in Safari??
        $pop    = JRequest::getInt('pop');
        $print  = JRequest::getInt('print');
        $task   = JRequest::getVar('task');
        $tmpl   = JRequest::getWord('tmpl');

        // don't load mediaelement on certain pages
        if ($pop || $print || $tmpl == 'component' || $task == 'new' || $task == 'edit') {
            return;
        }

        $components = $this->params->get('components', '');

        if (!empty($components)) {
            $excluded = explode(',', $components);
            $option = JRequest::getVar('option', '');
            foreach ($excluded as $exclude) {
                if ($option == 'com_' . $exclude || $option == $exclude) {
                    return;
                }
            }
        }

        // get menu items from parameter
        $menuitems = (array) $this->params->get('menu');

        // is there a menu assignment?
        if (!empty($menuitems) && !empty($menuitems[0])) {
            // get active menu
            $menus = JSite::getMenu();
            $menu = $menus->getActive();

            if (is_string($menuitems)) {
                $menuitems = explode(',', $menuitems);
            }

            if ($menu) {
                if (!in_array($menu->id, (array) $menuitems)) {
                    return;
                }
            }
        }

        if ($this->params->get('jquery', 1)) {
            $version = new JVersion;

            if ($version->isCompatible('3.0')) {
                // Include jQuery
                JHtml::_('jquery.framework');
            } else {
                // check if loaded
                if (!$app->get('jquery')) {
                    // load from CDN
                    $document->addScript('https://code.jquery.com/jquery-1.12.0.min.js');
                    $document->addScriptDeclaration('jQuery.noConflict();');
                    // flag as loaded
                    $app->set('jquery', true);
                }
            }
        }

        $selector = $this->params->get('selector', 'audio,video');
        $options  = $this->params->get('options', '');

        $document->addScript(JURI::root(true) . '/plugins/system/wfmediaelement/js/mediaelement-and-player.min.js');
        $document->addStyleSheet(JURI::root(true) . '/plugins/system/wfmediaelement/css/mediaelementplayer.min.css');
        $document->addScriptDeclaration('jQuery(document).ready(function($){$("' . $selector . '").mediaelementplayer(' . $options .');});');

        return true;
    }
}

?>
