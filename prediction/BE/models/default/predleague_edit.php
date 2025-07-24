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
// No direct access.
defined('_JEXEC') or die;

require dirname(__FILE__).'/../models.php';

class predleague_editJSModel extends JSPRO_Models
{
    public $_data = null;
    public $_lists = null;
    public $_mode = 1;
    public $_id = null;
    public function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication();

        $this->getData();
    }

    public function getData()
    {
        $mainframe = JFactory::getApplication();
        $cid = JRequest::getVar('cid', array(0), '', 'array');
        $is_id = $cid[0];
        $row = new JTablePredleague($this->db);
        $row->load($is_id);
        $error = $this->db->getErrorMsg();
        if ($error) {
            return JError::raiseError(500, $error);
        }
        $this->getPredictions($row);
        
        $jsoptions = json_decode($row->seasons, true);

        $seasons_include_str = '';
        $seasons_include = $seasonin = array();
        if(isset($jsoptions) && $jsoptions){
            $seasons_include = $jsoptions;
            $seasons_include_str = implode(',', $seasons_include);
        }
        
        $query_add = "SELECT CONCAT(t.name,' ',s.s_name) as name,s.s_id as id"
                    .' FROM #__bl_seasons as s'
                    ." JOIN #__bl_tournament as t ON s.t_id = t.id"
                    .($seasons_include_str?" WHERE s.s_id NOT IN (".$seasons_include_str.")":"")
                    .' GROUP BY s.s_id'
                    .' ORDER BY t.name,s.s_name';

        $this->db->setQuery($query_add);
        $seasall = $this->db->loadObjectList();
        
        if(count($seasons_include)){
        
            $query_add = "SELECT CONCAT(t.name,' ',s.s_name) as name,s.s_id as id"
                        .' FROM #__bl_seasons as s'
                        ." JOIN #__bl_tournament as t ON s.t_id = t.id AND t.t_single='0'"
                        .($seasons_include_str?" WHERE s.s_id IN (".$seasons_include_str.")":"")
                        .' GROUP BY s.s_id'
                        .' ORDER BY t.name,s.s_name';

            $this->db->setQuery($query_add);
            $seasonin = $this->db->loadObjectList();
        }
        
        $this->_lists['seasons'] = @JHTML::_('select.genericlist',   $seasall, 'seas_all', ' size="10" multiple ondblclick="javascript:JS_addSelectedToList(\'adminForm\',\'seas_all\',\'seas_all_add\');"', 'id', 'name', 0);
        $this->_lists['seasons_add'] = @JHTML::_('select.genericlist',   $seasonin, 'seas_all_add[]', ' size="10" multiple ondblclick="javascript:JS_addSelectedToList(\'adminForm\',\'seas_all_add\',\'seas_all\');"', 'id', 'name', 0);
 
        
        $this->_data = $row;
        
        
        
    }
    
    public function getPredictions($row){
        $this->_lists['predictions'] = array();
        $this->db->setQuery("SELECT * FROM #__bl_predtype ORDER BY ordering");
        $predictionsDB = $this->db->loadObjectList();
        $intZ = 0;
        
        $pred = json_decode($row->predictions, true);
        
        for($intA = 0; $intA < count($predictionsDB); $intA++){
            $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomsport'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
            $classN = 'JSPT'.$predictionsDB[$intA]->identif;
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
                if(class_exists($classN)){
                    $this->_lists['predictions'][$intZ]['object'] = new $classN;
                    if(isset($pred[$predictionsDB[$intA]->id])){
                        $this->_lists['predictions'][$intZ]['object']->setValue($pred[$predictionsDB[$intA]->id]);
                    }
                    $intZ++;
                }
            }
        }
        
        
    }
    

    public function savePredleague()
    {
        if (!JFactory::getUser()->authorise('core.edit', 'com_joomsport')) {
            return JError::raiseError(303, '');
        }
        $mainframe = JFactory::getApplication();
        $post = JRequest::get('post');
        $post['name'] = JRequest::getVar('plname', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $post['predictions'] = json_encode($_POST['pred']);
        $post['seasons'] = json_encode($_POST['seas_all_add']);
        
        /*$usr_admins = JRequest::getVar('in_teams', array(0), '', 'array');
        JArrayHelper::toInteger($usr_admins, array(0));
        */
        $row = new JTablePredleague($this->db);
        if (!$row->bind($post)) {
            JError::raiseError(500, $row->getError());
        }
        if (!$row->check()) {
            JError::raiseError(500, $row->getError());
        }
        // if new item order last in appropriate group
        if (!$row->store()) {
            JError::raiseError(500, $row->getError());
        }
        $row->checkin();

        
        $row->store();

        
        $this->_id = $row->id;
    }
}

class JTablePredleague extends JTable
{
    public $id = null;
    public $name = null;
    public $seasons = null;
    public $predictions = null;
    public $options = null;
    public function __construct(&$db)
    {
        parent::__construct('#__bl_predleague', 'id', $db);
    }
}