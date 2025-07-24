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

class predleague_listJSModel extends JSPRO_Models
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

        $mainframe = JFactory::getApplication();

        // Get the pagination request variables
        $this->limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart = 0;
        if (!$mainframe->input->getInt('is_search')) {
            $this->limitstart = $mainframe->getUserStateFromRequest('com_joomsport.limitstart_predleague', 'limitstart', 0, 'int');
        }
        
        // In case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ($this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0);
        if ($this->getTotal() <= $this->limitstart) {
            $this->limitstart = 0;
        }
        $this->getPagination();

        $this->getData();
        
        
        $query = 'SELECT COUNT(p.id)
					FROM #__bl_predleague AS p';
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

        $this->_lists['js_filter_search'] = $mainframe->getUserStateFromRequest('com_joomsport.predleague_list_filter', 'js_filter_search', '', 'string');

        
        $query = "SELECT *
                            FROM 
                            #__bl_predleague AS p";
                            
        if ($this->_lists['js_filter_search']) {
            $query .= " WHERE (p.name LIKE '%".addslashes($this->_lists['js_filter_search'])."%' OR p.name LIKE '%".addslashes($this->_lists['js_filter_search'])."%')";
        }

        $query .= $orderby;

        return $query;
    }

    public function _buildContentOrderBy()
    {
        $mainframe = JFactory::getApplication();

        $this->_lists['sortfield'] = $mainframe->getUserStateFromRequest('com_joomsport.predleague_list_field', 'sortfield', 'name', 'string');
        $this->_lists['sortway'] = $mainframe->getUserStateFromRequest('com_joomsport.predleague_list_way', 'sortway', 'ASC', 'string');

        $sort = ($this->_lists['sortfield'] == 'name') ? 'name '.$this->_lists['sortway'] : ($this->_lists['sortfield'].' '.$this->_lists['sortway']);

        $orderby = ' ORDER BY '.$sort;

        return $orderby;
    }

    
    //
    public function delPredleague()
    {
        if (!JFactory::getUser()->authorise('core.delete', 'com_joomsport')) {
            return JError::raiseError(303, '');
        }
        $cid = JRequest::getVar('cid', array(0), '', 'array');
        JArrayHelper::toInteger($cid, array(0));
        if (count($cid)) {
            $cids = implode(',', $cid);
            $query = 'DELETE FROM `#__bl_predleague` WHERE id IN ('.$cids.')';
            $this->db->setQuery($query);
            $this->db->query();

            
        }
    }
}
