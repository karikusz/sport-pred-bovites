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

class predround_editJSModel extends JSPRO_Models
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
        $prlfilt_id = $mainframe->getUserStateFromRequest('com_joomsport.prlfilt_id', 'prlfilt_id', 0, 'int');
        $is_id = $cid[0];
        $row = new JTablePredround($this->db);
        $row->load($is_id);
        $error = $this->db->getErrorMsg();
        if ($error) {
            return JError::raiseError(500, $error);
        }
        
        $query_add = "SELECT *"
                     .' FROM #__bl_predleague'
                     .' ORDER BY name';

        $this->db->setQuery($query_add);
        $leagues = $this->db->loadObjectList();
        
        $leagues_bulk[] = JHTML::_('select.option', 0, JText::_('BLBE_SELECTIONNO'),"id","name");
        if($leagues){
            $leagues_bulk = array_merge($leagues_bulk,$leagues);
        }
        
        $this->_lists['leagues'] = $row->league_id?$row->league_id:$prlfilt_id;
        
        if((int) $this->_lists['leagues']){
            $this->db->setQuery("SELECT * FROM #__bl_predleague WHERE id=".intval($this->_lists['leagues']));
            $leagueObj = $this->db->loadObject();
            $jsoptions = json_decode($leagueObj->seasons, true);

            $seasons_include_str = '';
            $seasons_include = $seasonin = array();
            if(isset($jsoptions) && $jsoptions){
                $seasons_include = $jsoptions;
                $seasons_include_str = implode(',', $seasons_include);
            }
            
            
            $query_add = "SELECT CONCAT(t.name,' ',s.s_name) as s_name,s.s_id as s_id, m.*,t.t_single"
                    .' FROM #__bl_seasons as s'
                    ." JOIN #__bl_tournament as t ON s.t_id = t.id"
                    ." JOIN #__bl_matchday as md ON md.s_id = s.s_id"
                    ." JOIN #__bl_match as m ON md.id = m.m_id"
                    ." WHERE m.published=1 AND m.m_played = 0"
                    . " AND m.team1_id > 0 AND m.team2_id > 0"
                    .($seasons_include_str?" AND s.s_id IN (".$seasons_include_str.")":"")

                    .' ORDER BY m.m_date,m.m_time,s.s_id';
            $this->db->setQuery($query_add);
            $matches = $this->db->loadObjectList();
            
            for($intA=0;$intA<count($matches);$intA++){
                $match = $matches[$intA];
                if($match->t_single == '1'){
                    $query1 = "SELECT CONCAT(first_name,' ', last_name) FROM #__bl_players WHERE id={$match->team1_id}";
                    $query2 = "SELECT CONCAT(first_name,' ', last_name) FROM #__bl_players WHERE id={$match->team2_id}";
                }else{
                    $query1 = "SELECT t_name FROM #__bl_teams WHERE id={$match->team1_id}";
                    $query2 = "SELECT t_name FROM #__bl_teams WHERE id={$match->team2_id}";
                }
                $this->db->setQuery($query1);
                $part1 = $this->db->loadResult();
                $this->db->setQuery($query2);
                $part2 = $this->db->loadResult($query2);
                
                $matches[$intA]->name = $part1 . ' vs ' . $part2 . ', ' . $matches[$intA]->m_date . ' ' . $matches[$intA]->m_time .', ' . $matches[$intA]->s_name;
            }
            
            $this->_lists['matches'] = JHTML::_('select.genericlist',   $matches, 'select_match', ' size="10" multiple style="width:auto;height:300px;"', 'id', 'name', 0);
        
            if($row->id){
                $query = "SELECT  CONCAT(t.name,' ',s.s_name) as s_name,s.s_id as s_id, m.*,t.t_single "
                        . " FROM #__bl_predround_matches as p"
                        . " JOIN #__bl_match as m ON m.id = p.match_id"
                        . " JOIN #__bl_matchday as md ON md.id = m.m_id"
                        . " JOIN #__bl_seasons as s ON md.s_id = s.s_id"
                        . " JOIN #__bl_tournament as t ON s.t_id = t.id"
                        . " WHERE p.round_id = {$row->id}"
                        . " ORDER BY m.m_date,m.m_time,s.s_id";
                $this->db->setQuery($query);
                $matches_allready = $this->db->loadObjectList();   
            }else{
                $matches_allready = array();
            }
            for($intA=0;$intA<count($matches_allready);$intA++){
                $match = $matches_allready[$intA];
                if($match->t_single == '1'){
                    $query1 = "SELECT CONCAT(first_name,' ', last_name) FROM #__bl_players WHERE id={$match->team1_id}";
                    $query2 = "SELECT CONCAT(first_name,' ', last_name) FROM #__bl_players WHERE id={$match->team2_id}";
                }else{
                    $query1 = "SELECT t_name FROM #__bl_teams WHERE id={$match->team1_id}";
                    $query2 = "SELECT t_name FROM #__bl_teams WHERE id={$match->team2_id}";
                }
                $this->db->setQuery($query1);
                $part1 = $this->db->loadResult();
                $this->db->setQuery($query2);
                $part2 = $this->db->loadResult($query2);
                
                $matches_allready[$intA]->name = $part1 . ' vs ' . $part2 . ', ' . $matches_allready[$intA]->m_date . ' ' . $matches_allready[$intA]->m_time .', ' . $matches_allready[$intA]->s_name;
            }        
            $this->_lists['matches_allready'] = $matches_allready;
        }
        
        
        $this->_data = $row;
        
        
        
    }
    

    

    public function savePredround()
    {
        if (!JFactory::getUser()->authorise('core.edit', 'com_joomsport')) {
            return JError::raiseError(303, '');
        }
        $mainframe = JFactory::getApplication();
        $post = JRequest::get('post');
        $post['rname'] = JRequest::getVar('plname', '', 'post', 'string', JREQUEST_ALLOWRAW);
        
        
        
        
        $row = new JTablePredround($this->db);
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
        
        $pmatches = JRequest::getVar('pred_selmatches', array(0), '', 'array');
        JArrayHelper::toInteger($pmatches, array(0));
        
        $this->db->setQuery("DELETE FROM #__bl_predround_matches WHERE round_id = {$row->id}");
        $this->db->query();


        $start_date = $end_date = '';

        for($intA=0; $intA<count($pmatches); $intA++){
            $match_id = intval($pmatches[$intA]);
            $this->db->setQuery("INSERT INTO #__bl_predround_matches(round_id,match_id) VALUES({$row->id},{$match_id})");
            $this->db->query();

            $this->db->setQuery("SELECT CONCAT(m_date,' ',m_time) FROM #__bl_match WHERE id=".$match_id);
            $mdate = $this->db->loadResult();

            if($intA == 0){
                $start_date = $mdate;
                $end_date = $mdate;
            }
            if($mdate < $start_date){
                $start_date = $mdate;
            }
            if($mdate > $end_date){
                $end_date = $mdate;
            }

        }
        $this->db->setQuery("UPDATE  #__bl_predround SET first_match_date='".$start_date."', last_match_date='".$end_date."' WHERE id = {$row->id}");
        $this->db->query();
        
        
        require_once JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'sportleague.php';

        classJsportPlugins::get('onPredRoundSave', array('round_id' => $row->id));
        
        
        
        $this->_id = $row->id;
    }
}

class JTablePredround extends JTable
{
    public $id = null;
    public $rname = null;
    public $league_id = null;
    public $ordering = null;
    public function __construct(&$db)
    {
        parent::__construct('#__bl_predround', 'id', $db);
    }
}