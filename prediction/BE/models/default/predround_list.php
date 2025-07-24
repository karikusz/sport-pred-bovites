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

class predround_listJSModel extends JSPRO_Models
{
    public $_data = null;
    public $_lists = null;
    public $_total = null;

    public $_pagination = null;
    public $limit = null;
    public $limitstart = null;

    public function __construct()
    {
        parent::__construct();


        //alter
        $this->db->setQuery("SHOW COLUMNS FROM `#__bl_predround` LIKE 'complete'");
        $is_col = $this->db->loadResult();
        if (!$is_col) {
            $this->db->setQuery('ALTER TABLE `#__bl_predround` ADD `complete` VARCHAR(1) NOT NULL DEFAULT "0", ADD `first_match_date` DATETIME DEFAULT "0000-00-00 00:00:00", ADD `last_match_date` DATETIME DEFAULT "0000-00-00 00:00:00"');
            $this->db->query();
        }

        $this->db->setQuery("SHOW COLUMNS FROM `#__bl_predround_users` LIKE 'winner_side'");
        $is_col = $this->db->loadResult();
        if (!$is_col) {
            $this->db->setQuery("ALTER TABLE `#__bl_predround_users` ADD `winner_side` SMALLINT NOT NULL DEFAULT '0' , ADD `score_diff` SMALLINT NOT NULL DEFAULT '0'");
            $this->db->query();
        }


        $mainframe = JFactory::getApplication();

        // Get the pagination request variables
        $this->limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart = 0;
        if (!$mainframe->input->getInt('is_search')) {
            $this->limitstart = $mainframe->getUserStateFromRequest('com_joomsport.limitstart_predround', 'limitstart', 0, 'int');
        }
        
        // In case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ($this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0);
        if ($this->getTotal() <= $this->limitstart) {
            $this->limitstart = 0;
        }
        $this->getPagination();

        $this->getData();
        
        $query_add = "SELECT *"
                     .' FROM #__bl_predleague'
                     .' ORDER BY name';

        $this->db->setQuery($query_add);
        $leagues = $this->_lists['leaguesC'] = $this->db->loadObjectList();
        
        $leagues_bulk[] = JHTML::_('select.option', 0, JText::_('BLBE_SELECTIONNO'),"id","name");
        if($leagues){
            $leagues_bulk = array_merge($leagues_bulk,$leagues);
        }
        
        $this->_lists['leagues'] = JHTML::_('select.genericlist',   $leagues_bulk, 'league_id', ' size="1" ', 'id', 'name', 0);
        


        $query = 'SELECT DISTINCT (p.id)
					FROM #__bl_predround AS p';
        $this->db->setQuery($query);
        $this->_lists['totplayer'] = $this->db->loadResult();
    }

    public function getData()
    {
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query);
            $error = $this->db->getErrorMsg();
            if ($error) {
                return JError::raiseError(500, $error);
            }
        }

        return $this->_data;
    }

    public function getTotal()
    {
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }
    public function _getListCount($query)
    {
        $this->db->setQuery($query);
        $tot = $this->db->loadObjectList();

        return count($tot);
    }

    public function _getList($query)
    {
        $this->db->setQuery($query, $this->limitstart, $this->limit);
        $tot = $this->db->loadObjectList();

        return $tot;
    }

    public function getPagination()
    {
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->limitstart, $this->limit);
        }

        return $this->_pagination;
    }

    public function _buildQuery()
    {
        $orderby = $this->_buildContentOrderBy();
        $mainframe = JFactory::getApplication();

        $this->_lists['js_filter_search'] = $mainframe->getUserStateFromRequest('com_joomsport.predround_list_filter', 'js_filter_search', '', 'string');

        
        $query = "SELECT p.*,l.name
                            FROM 
                            #__bl_predround AS p
                            JOIN #__bl_predleague as l ON l.id = p.league_id";
                            
        if ($this->_lists['js_filter_search']) {
            $query .= " WHERE (p.rname LIKE '%".addslashes($this->_lists['js_filter_search'])."%' OR p.rname LIKE '%".addslashes($this->_lists['js_filter_search'])."%')";
        }

        $query .= $orderby;

        return $query;
    }

    public function _buildContentOrderBy()
    {
        $mainframe = JFactory::getApplication();

        $this->_lists['sortfield'] = $mainframe->getUserStateFromRequest('com_joomsport.predround_list_field', 'sortfield', 'rname', 'string');
        $this->_lists['sortway'] = $mainframe->getUserStateFromRequest('com_joomsport.predround_list_way', 'sortway', 'ASC', 'string');

        $sort = ($this->_lists['sortfield'] == 'rname') ? 'rname '.$this->_lists['sortway'] : ($this->_lists['sortfield'].' '.$this->_lists['sortway']);

        $orderby = ' ORDER BY '.$sort;

        return $orderby;
    }

    
    //
    public function delPredRound()
    {
        if (!JFactory::getUser()->authorise('core.delete', 'com_joomsport')) {
            return JError::raiseError(303, '');
        }
        $cid = JRequest::getVar('cid', array(0), '', 'array');
        JArrayHelper::toInteger($cid, array(0));
        if (count($cid)) {
            $cids = implode(',', $cid);
            $query = 'DELETE FROM `#__bl_predround` WHERE id IN ('.$cids.')';
            $this->db->setQuery($query);
            $this->db->query();

            
        }
    }
}
