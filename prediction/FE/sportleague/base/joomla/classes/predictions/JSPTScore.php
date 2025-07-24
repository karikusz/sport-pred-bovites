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
defined('_JEXEC') or die;


class JSPTScore {
    public $row = null;
    public $value = '';
    public function __construct() {
        
        
    }
    public function getTitle(){
        return JText::_($this->row->name);
    }
    public function setValue($val = ''){
        $this->value = $val;
    }
    public function getAdminView(){
        return '<input type="number" value="'.$this->value.'" name="pred['.$this->row->id.']" />';
    }
    public function getView($match_id, $round_id, $user_id, $canEdit){
        $scoreval = $this->getPrediction($match_id, $round_id, $user_id);
        
        if($canEdit){
            $html = '<input type="number" value="'.(isset($scoreval['score1'])?$scoreval['score1']:'').'" name="pred_home['.$match_id.']" />';
            $html .= '&nbsp;:&nbsp;';
            $html .= '<input type="number" value="'.(isset($scoreval['score2'])?$scoreval['score2']:'').'" name="pred_away['.$match_id.']" />';
        }else{
            $html = '';
            if(isset($scoreval['score1']) && isset($scoreval['score2'])){
                $html = $scoreval['score1'];
                $html .= '&nbsp;:&nbsp;';
                $html .= $scoreval['score2'];
            }
        }
        return $html;
    }
    public function getPrediction($match_id, $round_id, $user_id){
        global $jsDatabase;
        $query = "SELECT prediction"
                . " FROM #__bl_predround_users"
                . " WHERE user_id={$user_id}"
                . " AND round_id={$round_id}";
        $pred = $jsDatabase->selectValue($query);        
        $pred = json_decode($pred, true);
        if(isset($pred['score'][$match_id])){
            return $pred['score'][$match_id];
        }
        
    }
    
    public function validateData($user_id, $round_id){
        global $jsDatabase;
        $pred_home = classJsportRequest::get('pred_home');
        $pred_away = classJsportRequest::get('pred_away');
        $match_res = array();
        $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$round_id}");

        $pred = json_decode($prediction, true);
        if(isset($pred['score'])){
            $match_res = $pred['score'];
        }
        if(count($pred_home)){
            
            foreach ($pred_home as $key => $value) {
                
                if($value != '' && intval($key) && $pred_away[$key] != ''){
                    if($this->canEditMatch($key)){
                        $match_res[$key]["score1"] = (int) $value;
                        $match_res[$key]["score2"] = (int) $pred_away[$key];
                    }
                }
            }
        }
        return $match_res;
    }
    public function getScore($match, $results){
      
    }
    private function canEditMatch($match_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if(!$user_id){
            return FALSE;
        }
        
        $match = $jsDatabase->selectObject("SELECT * FROM #__bl_match WHERE id=".$match_id);
        $m_date = $match->m_date;//classJsportDate::getDate($match->m_date, $match->m_time, "Y-m-d");
        $m_time = $match->m_time;//classJsportDate::getDate($match->m_date, $match->m_time, "H:i");
        
        $config = JFactory::getConfig();
        $offset = $config->get('offset');
        date_default_timezone_set($offset);


        $date = new DateTime(date("Y-m-d H:i"), new DateTimeZone($offset));
        $cur_date =  $date->format('Y-m-d');
        $cur_time =  $date->format('H:i');
        
        if($match->m_played == '1'){
            return false;
        }
        if(($m_date > $cur_date) || ($m_date == $cur_date && $m_time > $cur_time)){
            return true;
        }
        return false;
    }
    
}
