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

class classJsportUserround
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
        $user_id = classJsportUser::getUserId();
        $this->lists['matches'] = array();
        $query = "SELECT  CONCAT(t.name,' ',s.s_name) as s_name,s.s_id as s_id, m.*,t.t_single "
                    . " FROM #__bl_predround_matches as p"
                    . " JOIN #__bl_match as m ON m.id = p.match_id"
                    . " JOIN #__bl_matchday as md ON md.id = m.m_id"
                    . " JOIN #__bl_seasons as s ON md.s_id = s.s_id"
                    . " JOIN #__bl_tournament as t ON s.t_id = t.id"
                    . " WHERE p.round_id = {$this->id}"
                    . " AND m.team1_id>0 AND m.team2_id>0"
                    . " ORDER BY m.m_date,m.m_time,s.s_id";
        $matches_allready = $jsDatabase->select($query);        
        for($intA=0;$intA<count($matches_allready);$intA++){
            $match = new classJsportMatch($matches_allready[$intA]->id, false);
            $this->lists['matches'][] = $match;
        }   
        
        
        $this->league = $jsDatabase->selectObject('SELECT * '
                .' FROM #__bl_predround'
                .' WHERE id = '.$this->id);
        
        
        
        if(classJsportRequest::get('jspAction') == 'saveRound' && $this->canSave()){
            $predictionsDB = $jsDatabase->select("SELECT * FROM #__bl_predtype ORDER BY ordering");

           
            $data = array();
            $intZ = 0;
            
            $rowL = $jsDatabase->selectObject("SELECT * FROM #__bl_predleague WHERE id=".$this->league->league_id);

            $pred = json_decode($rowL->predictions, true);

            $array_pred = array();

            for($intA = 0; $intA < count($predictionsDB); $intA++){
                $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomsport'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
                $classN = 'JSPT'.$predictionsDB[$intA]->identif;
                if(is_file($path . $classN.'.php')){
                    require_once $path . $classN.'.php';
                    if(class_exists($classN)){
                        $this->_lists['predictions'][$intZ]['object'] = new $classN;
                        if(isset($pred[$predictionsDB[$intA]->id]) && !in_array($predictionsDB[$intA]->ptype, $array_pred)){
                            
                            $data[$predictionsDB[$intA]->ptype] = $this->_lists['predictions'][$intZ]['object']->validateData($user_id, $this->id);
                            
                            $array_pred[] = $predictionsDB[$intA]->ptype;
                        }
                        $intZ++;
                    }
                }
            }
            $predString = json_encode($data);
            $exist = $jsDatabase->selectValue("SELECT id FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$this->id}");
            if($exist){
                
                $jsDatabase->update("UPDATE #__bl_predround_users SET prediction='".addslashes($predString)."' WHERE id={$exist}");
            }else{
                
                $jsDatabase->insert("INSERT INTO #__bl_predround_users(user_id,round_id,prediction,filldate)"
                        . " VALUES({$user_id},{$this->id},'".$predString."','".date("Y-m-d H:i:s")."')");
            }
        }
        $uname_str = '';
        if($this->usrid){
            $uname = $jsDatabase->selectValue("SELECT username FROM #__users WHERE id=".$this->usrid);
            $uname_str = ' ('.strtolower(JText::_('JSPL_FE_USER')).': ' . $uname . ')';
        }

        $this->lists['options']['title'] = $this->league->rname . $uname_str;
        $this->lists['options']['prleaders'] = $this->league->league_id;
        $this->lists['options']['userleague'] = $this->league->league_id;
        
    }

    public function getRow()
    {

        return $this;
    }
    public function getRowSimple()
    {
        return $this;
    }
    
    public function getPredict($match_id){
        global $jsDatabase;
        
        $canEdit = $this->canEditMatch($match_id);
        $canView = $this->canViewPred($match_id);
        
        $this->_lists['predictions'] = array();
        $predictionsDB = $jsDatabase->select("SELECT * FROM #__bl_predtype ORDER BY ordering");
        $html = '';
        $intZ = 0;
        
        $rowL = $jsDatabase->selectObject("SELECT * FROM #__bl_predleague WHERE id=".$this->league->league_id);
        
        $pred = json_decode($rowL->predictions, true);
        
        $array_pred = array();
        
        for($intA = 0; $intA < count($predictionsDB); $intA++){
            $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomsport'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
            $classN = 'JSPT'.$predictionsDB[$intA]->identif;
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
                if(class_exists($classN)){
                    $this->_lists['predictions'][$intZ]['object'] = new $classN;
                    if(isset($pred[$predictionsDB[$intA]->id]) && !in_array($predictionsDB[$intA]->ptype, $array_pred)){
                        $html .= '<div class="jsp_prediction_'.$predictionsDB[$intA]->ptype.'">';
                        if($canView){
                            $html .= $this->_lists['predictions'][$intZ]['object']->getView($match_id,$this->id,$this->usrid,$canEdit);
                        }
                        $html .= '</div>';
                        $array_pred[] = $predictionsDB[$intA]->ptype;
                    }
                    $intZ++;
                }
            }
        }
        return $html;
        
    }
    
    public function getMatchPoint($match_id){
        global $jsDatabase;
        
        //$user_id = classJsportUser::getUserId();
        if($this->usrid){
            $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$this->usrid} AND round_id={$this->id}");

            $pred = json_decode($prediction, true);
            if(isset($pred['score'][$match_id]['points'])){
                return $pred['score'][$match_id]['points'];
            }else{
                return '';
            }
        }
    }    
    
    public function canSave(){
        $user_id = classJsportUser::getUserId();
        if($user_id && $this->usrid == $user_id){
            return true;
        }else{
            return FALSE;
        }
    }
    
    public function canEditMatch($match_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if(!$user_id || $this->usrid != $user_id){
            return FALSE;
        }
        $match = $jsDatabase->selectObject("SELECT * FROM #__bl_match WHERE id=".$match_id);
        
        $m_date = $match->m_date;//classJsportDate::getDate($match->m_date, $match->m_time, "Y-m-d");
        $m_time = $match->m_time;//classJsportDate::getDate($match->m_date, $match->m_time, "H:i");
        
        //echo $m_date.' '.$m_time."<br />";

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
    public function canViewPred($match_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if($user_id && $this->usrid == $user_id){
            return true;
        }
        
        $match = $jsDatabase->selectObject("SELECT * FROM #__bl_match WHERE id=".$match_id);
        if($match->m_played == '1'){
            return true;
        }
        
        return false;
    }
    
}
