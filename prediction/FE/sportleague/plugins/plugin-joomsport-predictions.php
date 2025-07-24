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

class pluginJoomsportPredictions
{
    public static function onMatchSave($args)
    {
        global $jsDatabase;
        
        $match_id = (isset($args['match_id']) && $args['match_id']) ? $args['match_id'] : 0;

        if (!$match_id) {
            return;
        }
        $m_played = $jsDatabase->selectValue("SELECT m_played FROM #__bl_match WHERE id={$match_id}");
        if($m_played == 1){
            $rounds = $jsDatabase->select("SELECT * FROM #__bl_predround_matches WHERE match_id={$match_id}");
            if(!count($rounds)) { return;}
            foreach ($rounds as $round) {
                $round_id = (int)$round->round_id;
                if($round_id){
                    JSPredictionsCalc::calculateMatch($match_id, $round_id);
                    JSPredictionsCalc::calculateRound($round_id);
                }
            }
        }
    }
    
    public static function addHeaderButton($options){
        global $jsConfig;
        $app = JFactory::getApplication();
        $Itemid = $app->input->get('Itemid', 0, 'int');
        $kl = '';

        $privateleague_link = $jsConfig->get("privateleague_link");

        if (isset($options['private_leagues']) && $options['private_leagues'] && $privateleague_link) {
            $link = JUri::base().$privateleague_link;
            $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-stand"></i>'.classJsportLanguage::get('JSPL_FE_PREDICTION_PRIVATE').'</a>';
        }
        if (isset($options['prleaders']) && $options['prleaders']) {
            $link = JRoute::_('index.php?option=com_joomsport&task=prleaders&id='.intval($options['prleaders']).(isset($_REQUEST["prl"])?'&prl='.intval($_REQUEST["prl"]):'').'&Itemid='.$Itemid);
            $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-stand"></i>'.classJsportLanguage::get('JSPL_FE_PREDICTION_LBOARD').'</a>';
        }
        if (isset($options['userleague']) && $options['userleague']) {
            $link = JRoute::_('index.php?option=com_joomsport&task=userleague&id='.intval($options['userleague']).(isset($_REQUEST["prl"])?'&prl='.intval($_REQUEST["prl"]):'').'&Itemid='.$Itemid);
            $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-itlist"></i>'.classJsportLanguage::get('JSPL_FE_PREDICTION_ROUNDLIST').'</a>';
        }
        return $kl;
    }
    
}

class JSPredictionsCalc{
    public static function calculateMatch($match_id,$round_id){
        global $jsDatabase;
        
        $league_id = $jsDatabase->selectValue("SELECT league_id FROM #__bl_predround WHERE id=".$round_id);
        if(!intval($league_id)){
            return;
        }
        $rowL = $jsDatabase->selectObject("SELECT * FROM #__bl_predleague WHERE id=".$league_id);
        
        $match = JSPredictionsCalc::getMatch($match_id);

        $predictionsDB = $jsDatabase->selectKeyPair("SELECT id as name,identif as value  FROM #__bl_predtype");
        $path = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomsport'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
        
        foreach($predictionsDB as $key=>$value){
            $classN = 'JSPT'.$predictionsDB[$key];
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
            }    
        }
        $predLeague = json_decode($rowL->predictions, true);
        if(count($predLeague)){
            $results = $jsDatabase->select("SELECT * FROM #__bl_predround_users WHERE round_id=".$round_id);
            
            for($intA=0;$intA<count($results);$intA++){
                $points = NULL;
                $pred = json_decode($results[$intA]->prediction,true);
                if(isset($pred['score'][$match_id])){
                    
                    foreach ($predLeague as $key => $value) {
                        //$predictionsDB = $jsDatabase->selectObject("SELECT * FROM #__bl_predtype WHERE id={$key}");
                        
                        /*$path = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_joomsport'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
                        $classN = 'JSPT'.$predictionsDB[$key];
                        if(is_file($path . $classN.'.php')){
                            require_once $path . $classN.'.php';*/
                            $classN = 'JSPT'.$predictionsDB[$key];
                            if(class_exists($classN)){
                                //$predObject = new $classN;
                                if($points === NULL){
                                    $score_tmp = $classN::getScore($match, $pred['score'][$match_id]);
                                    if($score_tmp === true){
                                        $points = $value;
                                    }
                                }
                                
                            }
                        //}
                        
                        
                    }
                    if($points == NULL) {$points = 0;}
                    if($points !== NULL){
                        $pred['score'][$match_id]['points'] = $points;
                        //$jsDatabase->update("UPDATE #__bl_predround_users SET prediction='".addslashes(json_encode($pred))."'  WHERE id=".$results[$intA]->id);
                        if($sqlQuery){$sqlQuery.=",";};
                        $sqlQuery .= "({$results[$intA]->id},'".addslashes(json_encode($pred))."',{$results[$intA]->user_id},{$round_id},'{$results[$intA]->editdate}',{$results[$intA]->points},{$results[$intA]->place},'".$results[$intA]->options."')";
                    }
                    
                }
            }
            if($sqlQuery){
                //echo "INSERT INTO #__bl_predround_users(id, prediction,user_id,round_id,editdate,points,place,options) values".$sqlQuery." on duplicate key update prediction =values(prediction)";die();
                
                $jsDatabase->insert("INSERT INTO #__bl_predround_users(id, prediction,user_id,round_id,editdate,points,place,options) values".$sqlQuery." on duplicate key update prediction =values(prediction)");
                
            }
        }

    }
    public static function getMatch($match_id){
        global $jsDatabase;
        $match =  $jsDatabase->selectObject("SELECT * FROM #__bl_match WHERE id=".$match_id);
        if($match && $match->is_extra == "1"){
            $match->score1 -= intval($match->aet1);
            $match->score2 -= intval($match->aet2);
        }
        return $match;
    }
    public static function calculateRound($round_id){
        global $jsDatabase, $jsConfig;
        $matches_played = $jsDatabase->selectValue("SELECT COUNT(r.match_id) "
                . " FROM #__bl_predround_matches as r"
                . " JOIN #__bl_match as m ON m.id=r.match_id"
                . " WHERE r.round_id={$round_id}"
                . " AND m.m_played != '1'"
                );
        $matches = $jsDatabase->select("SELECT m.* "
                . " FROM #__bl_predround_matches as r"
                . " JOIN #__bl_match as m ON m.id=r.match_id"
                . " WHERE r.round_id={$round_id}"
                . " AND m.m_played = '1'"
                );
        if( count($matches) && (!$matches_played || $jsConfig->get('pred_livecalc') != '1')){
            $results = $jsDatabase->select("SELECT * FROM #__bl_predround_users WHERE round_id=".$round_id);
            
            for($intA=0;$intA<count($results);$intA++){
                $points = 0;
                $filled = 0;
                $success = 0;
                $winner_side = 0;
                $diff = 0;
                $pred = json_decode($results[$intA]->prediction,true);
                for($intB = 0 ; $intB < count($matches); $intB ++){
                    $match_id = $matches[$intB]->id;
                    $matches_res = JSPredictionsCalc::getMatch($match_id);
                    if(isset($pred['score'][$match_id]['points'])){
                        $points += $pred['score'][$match_id]['points'];
                        $filled++;
                        
                        if(($matches_res->score1 == $pred['score'][$match_id]['score1'])
                                && ($matches_res->score2 == $pred['score'][$match_id]['score2'])){
                            $success++;
                        }else
                        if(($matches_res->score1 - $matches_res->score2)
                            == ($pred['score'][$match_id]['score1'] - $pred['score'][$match_id]['score2'])){
                            $diff++;
                        }else
                        if(($matches_res->score1 > $matches_res->score2) && ($pred['score'][$match_id]['score1'] > $pred['score'][$match_id]['score2'])
                            || ($matches_res->score1 < $matches_res->score2) && ($pred['score'][$match_id]['score1'] < $pred['score'][$match_id]['score2'])){
                            $winner_side++;
                        }
                    }
                }
                $jsDatabase->insert("UPDATE #__bl_predround_users"
                        . " SET points='".$points."', filled = {$filled}, success = {$success}, winner_side = {$winner_side}, score_diff = {$diff}"
                        . "  WHERE id=".$results[$intA]->id);
                    
            }    
        }
        if(count($matches) && !$matches_played){
            $jsDatabase->update("UPDATE #__bl_predround SET complete='1' WHERE id=".$round_id);
        }
                
    }
    
    public function rankRound(){
        
    }
}