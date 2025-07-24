<?php
/*------------------------------------------------------------------------
# JoomSport Professional 
# ------------------------------------------------------------------------
# BearDev development company 
# Copyright (C) 2011 JoomSport.com. All Rights Reserved.
# @license - http://joomsport.com/news/license.html GNU/GPL
# Websites: http://www.JoomSport.com 
# Technical Support:  Forum - http://joomsport.com/helpdesk/
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');
class JFormFieldPrround extends JFormField

{


    /**
	 * Element name.
	 *
	 * @var		string
	 */
    protected $type = 'prround';

    protected function getInput()

    {
        // Load the modal behavior script.
        JHtml::_('behavior.modal', 'a.modal');

        $db = JFactory::getDBO();
        
        $query_add = "SELECT *"
                     .' FROM #__bl_predround'
                     .' ORDER BY ordering,rname';

        $db->setQuery($query_add);
        $leagues = $db->loadObjectList();
        
        $leagues_bulk[] = JHTML::_('select.option', '', JText::_('BLBE_SELECTIONNO'),"id","rname");
        if($leagues){
            $leagues_bulk = array_merge($leagues_bulk,$leagues);
        }
        
        $html = JHTML::_('select.genericlist',   $leagues_bulk, $this->name, ' size="1"  required="required"', 'id', 'rname', $this->value);
        return $html;


    }


}
