<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class jsPredictionMyLeagues{
    public static function getActiveLeaguesList(){
        global $jsDatabase;
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
            . " FROM #__bl_private_league as p"
            . " JOIN #__bl_private_based as b ON b.privateID = p.id"
            . " JOIN #__bl_private_users as pu ON pu.privateID=b.privateID  AND pu.confirmed = '1'"
            . " WHERE pu.userID=".intval(classJsportUser::getUserId())
            . " GROUP BY p.id"
            . " ORDER BY p.leagueName"        ;

        return $jsDatabase->select($sql)?$jsDatabase->select($sql):array();
        
    }
    public static function getArchiveLeaguesList(){
        return array();
    }
    public static function getMyLeagues(){
        global $jsDatabase;
        $sql = "SELECT p.leagueName as name,p.id as id "
                . " FROM #__bl_private_league as p"
                . " JOIN #__bl_private_based as b ON b.privateID = p.id"
                . " WHERE p.creatorID=".intval(classJsportUser::getUserId())
                . " ORDER BY p.leagueName"        ;
        
        return $jsDatabase->select($sql)?$jsDatabase->select($sql):array();
        
    }
    public static function getParticipateLeagues(){

    }
    public static function getInvitedLeagues(){
        global $jsDatabase;
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
            . " FROM #__bl_private_league as p"
            . " JOIN #__bl_private_based as b ON b.privateID = p.id"
            . " JOIN #__bl_private_users as pu ON pu.privateID=b.privateID AND pu.confirmed = '0'"
            . " WHERE pu.userID=".intval(classJsportUser::getUserId())
            . " ORDER BY p.leagueName"        ;

        return $jsDatabase->select($sql)?$jsDatabase->select($sql):array();
    }
}