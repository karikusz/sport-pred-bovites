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

class classJsportPrleaders
{
    private $id = null;
    public $season_id = null;
    public $object = null;
    public $league = null;
    public $lists = null;
    public $round_id = null;
    public $privateID = null;
    
    public function __construct($id = 0)
    {
        if (!$id) {     
            $this->id = (int) classJsportRequest::get('id');
        } else {
            $this->id = $id;
        }
        if (!$this->id) {
            die('ERROR! LEAGUE ID not DEFINED');
        }
        $this->round_id = (int) classJsportRequest::get('round_id');

        $this->privateID = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
        
        $this->loadObject();
    }

    private function loadObject()
    {
        global $jsDatabase, $jsConfig;
        $sort = json_decode($jsConfig->get('prediction_sortfield'),true);
        $sort_str = '';
        if(count($sort)){
            foreach ($sort as $key => $value) {
                if($sort_str){
                    $sort_str .= ',';
                }
                $sort_str .= $key . ' ' . ($value?'asc':'desc');
            }
        }
        if(!$sort_str){
            $sort_str = 'pts desc, filled asc, succavg desc';
        }

        $this->league = $jsDatabase->selectObject('SELECT * '
                .' FROM #__bl_predleague'
                .' WHERE id = '.$this->id);


        $private_users = null;
        if($this->privateID){
            $query = "SELECT u.ID FROM #__users as u"
                . " JOIN #__bl_private_users as pm ON pm.userID = u.ID"
                . " WHERE pm.privateID = ".$this->privateID;
            $private_users = $jsDatabase->selectColumn($query);
            if(!count($private_users)){
                $private_users = array(0);
            }

        }
        
        //get last round
        $complete_rounds = $jsDatabase->selectColumn("SELECT p.id "
            . " FROM #__bl_predround as p"
            . " WHERE p.league_id = ".$this->id
            . " AND complete = '1'"
            . " ORDER BY p.first_match_date desc");

        $previous_places = array();

        if($complete_rounds && count($complete_rounds) > 1){
            $prev = $jsDatabase->select('SELECT SUM(u.points) as pts, usr.username,'
                . 'usr.id as user_id,SUM(u.filled) as filled, SUM(u.success) as success,  SUM(u.success)/SUM(u.filled) as succavg'
                . ' ,SUM(u.winner_side) as winner_side,'
                . ' SUM(u.score_diff) as score_diff'
                .' FROM #__bl_predround as p'
                .' JOIN #__bl_predround_users as u ON p.id=u.round_id'
                .' JOIN #__users as usr ON u.user_id=usr.id'
                .' WHERE p.league_id = '.$this->id
                .' AND u.round_id !='.intval($complete_rounds[0])
                .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                .' GROUP BY u.user_id'
                .' ORDER BY '.$sort_str.', u.user_id');
            for($intR=0;$intR<count($prev);$intR++){
                $previous_places[$prev[$intR]->user_id] = $intR+1;
            }
        }
        $this->lists['previuos_places'] = $previous_places;
        
        $round_str = $this->round_id?'&round_id='.$this->round_id:'';
        $link = JRoute::_(JUri::base().'index.php?option=com_joomsport&task=prleaders&id='.$this->id.$round_str);
        $pagination = new classJsportPagination($link);
        $limit = $pagination->getLimit();
        $offset = $pagination->getOffset();
        $this->object = $jsDatabase->select('SELECT SUM(u.points) as pts, usr.username,'
                . 'usr.id as user_id,SUM(u.filled) as filled, SUM(u.success) as success,  SUM(u.success)/SUM(u.filled) as succavg'
                . ' ,SUM(u.winner_side) as winner_side,'
                . ' SUM(u.score_diff) as score_diff'
                .' FROM #__bl_predround as p'
                .' JOIN #__bl_predround_users as u ON p.id=u.round_id'
                .' JOIN #__users as usr ON u.user_id=usr.id'
                .' WHERE p.league_id = '.$this->id
                .($this->round_id ? ' AND u.round_id='.$this->round_id : '')
                .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")

                .' GROUP BY u.user_id'
                .' ORDER BY '.$sort_str.', u.user_id'
                . ($limit ? ' LIMIT '.$offset.','.$limit:''));

        $user_id = classJsportUser::getUserId();
        if($user_id){
            $userRow = $jsDatabase->selectObject('SELECT SUM(u.points) as pts, '
                . 'u.user_id,SUM(u.filled) as filled, SUM(u.success) as success,'
                . '  SUM(u.success)/SUM(u.filled) as succavg, SUM(u.winner_side) as winner_side,'
                . ' SUM(u.score_diff) as score_diff'
                .' FROM #__bl_predround as p'
                .' JOIN #__bl_predround_users as u ON p.id=u.round_id'
                ." JOIN #__users as usr ON usr.ID = u.user_id"
                .' WHERE p.league_id = '.$this->id.' AND u.user_id = '.$user_id
                .($this->round_id ? ' AND u.round_id='.$this->round_id : '')
                .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                .' GROUP BY u.user_id');
            if($userRow){

                $user_position = $jsDatabase->select('SELECT COUNT(*) as cnt, SUM(u.points) as pts, SUM(u.filled) as filled, SUM(u.success)/SUM(u.filled) as succavg'
                    .' FROM #__bl_predround as p'
                    .' JOIN #__bl_predround_users as u ON p.id=u.round_id'
                    ." JOIN #__users as usr ON usr.ID = u.user_id"
                    .' WHERE p.league_id = '.$this->id.' AND u.user_id != '.$user_id
                    .($this->round_id ? ' AND u.round_id='.$this->round_id : '')
                    .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                    .' GROUP BY u.user_id'
                    .' HAVING pts>'.$userRow->pts.' OR (pts='.$userRow->pts.' AND (filled<'.$userRow->filled.' OR (filled='.$userRow->filled.' AND (succavg>'.floatval($userRow->succavg).' OR ('.($userRow->succavg==null?"succavg IS NULL":"succavg=".floatval($userRow->succavg)."").' AND u.user_id>'.$user_id.')))) ) '
                    .' ORDER BY '.$sort_str
                );


                $userRow->position = count($user_position)+1;

            }

        }

        $all = $jsDatabase->select('SELECT COUNT(u.user_id)'
                .' FROM #__bl_predround as p'
                .' JOIN #__bl_predround_users as u ON p.id=u.round_id'
                .' JOIN #__users as usr ON u.user_id=usr.id'
                .' WHERE p.league_id = '.$this->id
                .' GROUP BY u.user_id');

        $pagination->setPages(count($all));
        $this->lists['pagination'] = $pagination;
        
        
        $rounds = $jsDatabase->select("SELECT * FROM #__bl_predround"
                . " WHERE league_id={$this->id}"
                . " ORDER BY ordering,rname");
        $leagues_bulk[] = JHTML::_('select.option', 0, JText::_('BLFA_ALL'),"id","rname");
        if($rounds){
            $leagues_bulk = array_merge($leagues_bulk,$rounds);
        }
        $javascript = ' onchange="this.form.submit();"';
        $this->lists['options']['tourn'] = '<div class="jspred_filterround">' . Jtext::_('JSPL_FE_ROUNDS_FILTER') . ': ' . JHTML::_('select.genericlist',   $leagues_bulk, 'round_id', ' size="1" '.$javascript, 'id', 'rname', $this->round_id) . "</div>";
        

        
        $this->lists['options']['title'] = $this->league->name;
        $this->lists['options']['userleague'] = $this->league->id;
        $this->lists['options']['private_leagues'] = true;

        $this->lists['mypos'] = isset($userRow)?$userRow:null;
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
            return $match->m_date.' '.$match->m_time;
        }
    }
    public function getFilling($round_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if(!$user_id){
            return '';
        }
        $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$round_id}");
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
        $user_id = classJsportUser::getUserId();
        if(!$user_id){
            return '';
        }
        return $points = $jsDatabase->selectValue("SELECT points FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$round_id}");
        
    }

}
