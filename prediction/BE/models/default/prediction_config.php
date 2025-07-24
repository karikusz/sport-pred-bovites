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

class prediction_configJSModel extends JSPRO_Models
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
        
        $query = "SELECT cfg_value FROM `#__bl_config` WHERE cfg_name='prediction_sortfield'";

        $this->db->setQuery($query);
        //0 =>desc, 1=>asc
        $sort = array("pts" => 0,
            "filled" => 1 ,
            "succavg" => 0);

        if (!$this->db->loadResult()) {
            $this->db->SetQuery("INSERT INTO `#__bl_config` (cfg_name,cfg_value) VALUES ('prediction_sortfield', '".json_encode($sort)."')");

            $this->db->query();
        }
        
        $query = "SELECT cfg_value FROM `#__bl_config` WHERE cfg_name='pred_livecalc'";

        $this->db->setQuery($query);

        if ($this->db->loadResult() === null) {
            $this->db->SetQuery("INSERT INTO `#__bl_config` (cfg_name,cfg_value) VALUES ('pred_livecalc', '0')");

            $this->db->query();
        }
        $this->_lists['pred_livecalc'] = $this->getJS_Config('pred_livecalc');
        $this->_lists['sortfields'] = json_decode($this->getJS_Config('prediction_sortfield'),true);


        $query = "SELECT cfg_value FROM `#__bl_config` WHERE cfg_name='privateleague_link'";

        $this->db->setQuery($query);

        if ($this->db->loadResult() === null) {
            $this->db->SetQuery("INSERT INTO `#__bl_config` (cfg_name,cfg_value) VALUES ('privateleague_link', '')");

            $this->db->query();
        }
        $this->_lists['privateleague_link'] = $this->getJS_Config('privateleague_link');

    }
    
    public function savePredictionConfig(){
        $sort_columns = JRequest::getVar('sort_columns', array(0), '', 'array');
        $pred_livecalc = JRequest::getVar('pred_livecalc', 0, 'post', 'int');

        $privateleague_link = JRequest::getVar('privateleague_link', 0, 'post', 'string');

       
        $sort = array();

        for($intA = 0; $intA < count($sort_columns); $intA ++){
            $sort[$sort_columns[$intA]] = JRequest::getVar($sort_columns[$intA].'_way', 0, 'post', 'int');
        }
        
        $this->db->SetQuery("UPDATE `#__bl_config` SET cfg_value='".json_encode($sort)."' WHERE cfg_name='prediction_sortfield'");
        $this->db->query();
        
        $this->db->SetQuery("UPDATE `#__bl_config` SET cfg_value='".$pred_livecalc."' WHERE cfg_name='pred_livecalc'");
        $this->db->query();

        $this->db->SetQuery("UPDATE `#__bl_config` SET cfg_value='".$privateleague_link."' WHERE cfg_name='privateleague_link'");
        $this->db->query();


    }
    
}
