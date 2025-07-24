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
class JFormFieldModseas extends JFormField

{


    /**
	 * Element name.
	 *
	 * @var		string
	 */
    protected $type = 'Season';

    protected function getInput()

    {

        $db = JFactory::getDBO();

        $doc = JFactory::getDocument();


        $lang_val = JText::_('MOD_JS_TT_SELSEASGROUPS');

        $lang_val2 = JText::_('MOD_JS_TT_SELALLSEAS');

        $lang_val3 = JText::_('MOD_JS_TT_NOSEAS');


        $query = "SELECT CONCAT(t.name,' ',s.s_name) as name,s.s_id as id FROM #__bl_tournament as t, #__bl_seasons as s WHERE s.t_id = t.id AND t.published=1 AND s.published=1";

        $db->setQuery($query);


        $rows = $db->loadObjectList();
        
        $defval = (isset($this->required) && $this->required)?"":0;

        $selectbox = array();

        if (!empty($rows)) {

            for ($i = 0;$i < count($rows);++$i) {

                $row = $rows[$i];

                $selectbox[] = JHTML::_('select.optgroup',  $row->name, 'id', 'name');

                $query = 'SELECT group_name,id FROM #__bl_groups  WHERE s_id = '.$row->id;

                $db->setQuery($query);

                $gr = $db->loadObjectList();

                $selectbox[] = JHTML::_('select.option',  $row->id.'|0', $lang_val, 'id', 'name');

                for ($j = 0;$j < count($gr);++$j) {

                    $selectbox[] = JHTML::_('select.option',  $row->id.'|'.$gr[$j]->id, $gr[$j]->group_name, 'id', 'name');


                }


            }

            $javascript = 'onchange="javascript:  jQuery.post( \'index.php?tmpl=component&option=com_joomsport&task=getparcip&sid=\'+jQuery(\'#playerzdf_id\').val(), function( data ) {jQuery(\'#jformparamsteam_id\').html( data );jQuery(\'#jformparamsteam_id\').trigger(\'liszt:updated\');});"';


            $jqre = '<select name="jform[params][sidgid]" id="playerzdf_id" class="chzn-done" size="1" '.$javascript.' style="display:block !important;">';

            $jqre .= '<option value="'.$defval.'">'.$lang_val2.'</option>';

            $selectbox = array();


            for ($i = 0;$i < count($rows);++$i) {

                $row = $rows[$i];

                $jqre .= '<optgroup label="'.htmlspecialchars($row->name).'">';

                $query = 'SELECT group_name,id FROM #__bl_groups  WHERE s_id = '.$row->id;

                $db->setQuery($query);

                $gr = $db->loadObjectList();

                $jqre .= '<option value="'.$row->id.'|0" '.(($this->value == ($row->id.'|0')) ? 'selected' : '').'>All '.$row->name.' groups</option>';

                for ($j = 0;$j < count($gr);++$j) {
                    //$selectbox[] = JHTML::_('select.option',  $row->id.'|'.$gr[$j]->id,$gr[$j]->group_name, 'id', 'name' ); 
                    $jqre .= '<option value="'.$row->id.'|'.$gr[$j]->id.'" '.(($this->value == ($row->id.'|'.$gr[$j]->id)) ? 'selected' : '').'>'.$gr[$j]->group_name.'</option>';


                }

                $jqre .= '</optgroup>';


            }

            $jqre .= '</select>';

            $html = JHTML::_('select.genericlist',   $selectbox, 'jform[params][sidgid]', 'class="inputbox" size="1" '.$javascript, 'id', 'name', $this->value);


        } else {

            $jqre = '<select name="jform[params][sidgid]" id="playerzdf_id" class="chzn-done" size="1" style="display:block !important;">';

            $jqre .= '<option value="'.$defval.'">'.$lang_val.'</option>';


            $jqre .= '</select>';


        }

        //$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.$value.'" />';

        return $jqre;


    }


}
