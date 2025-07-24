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

class jsPredictionHelper
{

    public static function getActiveMainLeaguesList(){

        $db = JFactory::getDBO();
        $sql = "SELECT p.name,p.id "
            . " FROM #__bl_predleague as p"
            . " GROUP BY p.id"
            . " ORDER BY p.name";
        $db->setQuery($sql);
        return $db->loadObjectList()?$db->loadObjectList():array();
    }
    public static function getArchiveMainLeaguesList(){


        return array();
    }

    public static function addUserToPrivateLeague($LeagueId, $userId, $confirmed=1){
        if(intval($LeagueId) && intval($userId) && jsPredictionHelper::checkUserExist($userId)){
            $db = JFactory::getDBO();
            $db->setQuery("SELECT COUNT(id) FROM #__bl_private_users WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));
            $curIDcount = $db->loadResult();
            if($curIDcount > 1){

                $db->setQuery("DELETE FROM #__bl_private_users WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));
                $db->query();
            }

            $db->setQuery("SELECT id FROM #__bl_private_users WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));

            $curID = $db->loadResult();

            if($curID){
                $db->setQuery("UPDATE #__bl_private_users SET confirmed=".intval($confirmed)." WHERE id=".$curID);
                $db->query();
            }else{
                $db->setQuery("INSERT IGNORE INTO #__bl_private_users(privateID,userID,confirmed)"
                    . " VALUES(".intval($LeagueId).",".intval($userId).",".intval($confirmed).")"
                    . " ON DUPLICATE KEY UPDATE confirmed=".intval($confirmed));
                $db->query();
            }

            return true;
        }
        return false;
    }
    public static function removeUserToPrivateLeague($LeagueId, $userId){
        $db = JFactory::getDBO();
        $sql = "SELECT u.id "
            . " FROM #__bl_private_league as p"
            . " JOIN #__users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$LeagueId
            . " LIMIT 1";
        $db->setQuery($sql);
        $creatorID =  $db->loadResult();

        if($creatorID == $userId){
            return false;
        }
        if(intval($LeagueId) && intval($userId)){

            $db->setQuery("DELETE FROM #__bl_private_users WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));
            $db->query();
        }
    }

    public static function checkUserExist($userID){
        $db = JFactory::getDBO();
        $db->setQuery("SELECT id FROM #__users WHERE id=".$userID);
        return (bool) $db->loadResult();
    }

    public static function getLeagueInviteKey($LeagueId){
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__bl_private_league WHERE id='".intval($LeagueId)."'";
        $db->setQuery($query);

        return $db->loadResult($query);
    }

}
