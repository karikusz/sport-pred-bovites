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
defined('_JEXEC') or die('Restricted access');

require_once JS_PATH_MODELS.'model-jsport-player.php';
require_once JS_PATH_ENV_CLASSES.'class-jsport-getplayers.php';
require_once JS_PATH_CLASSES.'class-jsport-matches.php';
require_once JS_PATH_OBJECTS.'class-jsport-match.php';

class classJsportUserleague
{
    private $id = null;
    public $season_id = null;
    public $object = null;
    public $league = null;
    public $lists = null;
    public $usrid = null;

    public function __construct($id = 0)
    {
        if (!$id) {     
            $this->id = (int) classJsportRequest::get('id');
        } else {
            $this->id = $id;
        }
        $this->usrid = (int) classJsportRequest::get('usrid');
        if(!$this->usrid){
            $this->usrid = classJsportUser::getUserId();
        }
        if (!$this->id) {
            die('ERROR! LEAGUE ID not DEFINED');
        }
        $this->loadObject();
    }

    private function loadObject()
    {
        global $jsDatabase;
        $this->league = $jsDatabase->selectObject('SELECT * '
                .' FROM #__bl_predleague'
                .' WHERE id = '.$this->id);
        $this->object = $jsDatabase->select('SELECT * '
                .' FROM #__bl_predround'
                .' WHERE league_id = '.$this->id
                .' ORDER BY ordering,id');

        for($intA=0;$intA<count($this->object);$intA++){
            $this->object[$intA]->startdate = $this->getStartDate($this->object[$intA]->id);
        }

        $uname_str = '';
        if($this->usrid){
            $uname = $jsDatabase->selectValue("SELECT username FROM #__users WHERE id=".$this->usrid);
            $uname_str = ' ('.strtolower(JText::_('JSPL_FE_USER')).': ' . $uname . ')';
        }
        $this->lists['options']['title'] = $this->league->name . $uname_str;
        $this->lists['options']['prleaders'] = $this->league->id;

    }

    public function getRow()
    {

        return $this;
    }
    public function getRowSimple()
    {
        return $this;
    }
    
    public function getStartDate($round_id){
        global $jsDatabase;
        $match = $jsDatabase->selectObject("SELECT m_date,m_time "
                . " FROM #__bl_predround_matches as r"
                . " JOIN #__bl_match as m ON r.match_id = m.id"
                . " WHERE r.round_id={$round_id}"
                . " AND m.m_date != '0000-00-00'"
                . " ORDER BY m.m_date asc,m.m_time asc"
                        . " LIMIT 1");
                
        if(isset($match->m_date)){
            $match_date = classJsportDate::getDate($match->m_date, $match->m_time);
                        
            return $match_date;
        }
    }
    public function getFilling($round_id){
        global $jsDatabase;
        //$user_id = classJsportUser::getUserId();
        if(!$this->usrid){
            return '';
        }
        $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$this->usrid} AND round_id={$round_id}");
        $pred = json_decode($prediction, true);
        
        $matches = $jsDatabase->selectColumn("SELECT r.match_id "
                . " FROM #__bl_predround_matches as r"
                . " WHERE r.round_id={$round_id}"
                );
        
        $filled = 0;
        
        for($intA=0;$intA<count($matches);$intA++){
            $match_id = $matches[$intA];
            if(isset($pred['score'][$match_id])){
                if($pred['score'][$match_id]['score1'] !== '' && $pred['score'][$match_id]['score2'] !== ''){
                    $filled++;
                }
            }
        }
                
        return $filled .' / ' . count($matches);
    }
    public function getPoints($round_id){
        global $jsDatabase;
        //$user_id = classJsportUser::getUserId();
        if(!$this->usrid){
            return '';
        }
        return $points = $jsDatabase->selectValue("SELECT points FROM #__bl_predround_users WHERE user_id={$this->usrid} AND round_id={$round_id}");
        
    }

    public function getRoundStatus($round_id){
        global $jsDatabase;
        $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$this->usrid} AND round_id={$round_id}");
        $pred = json_decode($prediction, true);
        $filled = 0;
        $matches_count = 0;

        $stat = 0;

        $user_id = classJsportUser::getUserId();
        if($user_id && $this->usrid != $user_id){
            return '3';
        }


        $matches = $jsDatabase->select("SELECT r.*, m.m_date,m.m_time "
            . " FROM #__bl_predround_matches as r"
            . " JOIN #__bl_match as m ON m.id=r.match_id"
            . " WHERE r.round_id={$round_id}"
        );

        $matches_count = count($matches);

        $config = JFactory::getConfig();
        $offset = $config->get('offset');
        date_default_timezone_set($offset);


        $date = new DateTime(date("Y-m-d H:i"), new DateTimeZone($offset));
        $cur_date =  $date->format('Y-m-d');
        $cur_time =  $date->format('H:i');


        for($intA=0;$intA<count($matches);$intA++){
            $match_id = $matches[$intA]->match_id;
            $m_date = $matches[$intA]->m_date;
            $m_time = $matches[$intA]->m_time;
            if(($m_date > $cur_date) || ($m_date == $cur_date && $m_time > $cur_time)){
                $stat = 2;
                if(isset($pred['score'][$match_id])){
                    if($pred['score'][$match_id]['score1'] === '' && $pred['score'][$match_id]['score2'] === ''){
                        return '1';
                    }
                }
                if(!isset($pred['score'])){
                    return '1';
                }
            }

            if(isset($pred['score'][$match_id])){
                if($pred['score'][$match_id]['score1'] !== '' && $pred['score'][$match_id]['score2'] !== ''){
                    $filled++;
                }
            }
        }






        return $stat;

    }

}
