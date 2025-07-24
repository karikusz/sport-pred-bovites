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


require_once __DIR__.DIRECTORY_SEPARATOR.'JSPTScore.php';

/*
 *  When score exact
 */

class JSPTScoreExact extends JSPTScore{
    public function __construct() {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__bl_predtype WHERE identif='ScoreExact'");
        $this->row = $db->loadObject();
    }
    public function getScore($match, $results) {
        if($match->score1 == $results['score1'] && $match->score2 == $results['score2']){
            return true;
        }else{
            return false;
        }
    }
}
