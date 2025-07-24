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

class jsPredictionLeagueRow{
    private $leagueID;
    private $db;
    private $user;

    public function __construct($leagueID){
        $this->leagueID = $leagueID;
        $this->db = JFactory::getDBO();
        $this->user = JFactory::getUser();
    }
    public function getUsersCount(){
        $sql = "SELECT COUNT(pu.userID) "
            . " FROM #__bl_private_users as pu"
            . " JOIN #__users as u ON u.id = pu.userID"
            . " WHERE pu.privateID=".$this->leagueID
            . " AND pu.confirmed = '1'"
            . " ORDER BY u.username";
        $this->db->setQuery($sql);
        return (int) $this->db->loadResult();
    }
    public function getOwner(){
        $sql = "SELECT u.username "
            . " FROM #__bl_private_league as p"
            . " JOIN #__users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1";
        $this->db->setQuery($sql);;
        $uname =  $this->db->loadResult();
        if($this->getOwnerID() == $this->user->id){
            $uname = "<b>".$uname." (me)</b>";
        }
         return $uname;
    }
    public function getOwnerID(){
        $sql = "SELECT u.id "
            . " FROM #__bl_private_league as p"
            . " JOIN #__users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $this->db->setQuery($sql);
        return $this->db->loadResult();
    }
    public function getTitle(){
        $sql = "SELECT p.leagueName "
            . " FROM #__bl_private_league as p"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $this->db->setQuery($sql);
        return $this->db->loadResult();
    }
    public function getTitleLinked(){
        $title = $this->getTitle();
        $sql = "SELECT b.leagueID "
            . " FROM #__bl_private_league as p"
            . " JOIN #__bl_private_based as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $this->db->setQuery($sql);
        $based = (int) $this->db->loadResult();

        $link = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($based).'&prl='.$this->leagueID;

        return '<a href="'.$link.'">'.stripslashes($title).'</a>';
    }
    public function getLink(){

        $title = $this->getTitle();
        $sql = "SELECT b.leagueID "
            . " FROM #__bl_private_league as p"
            . " JOIN #__bl_private_based as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $this->db->setQuery($sql);
        $based = (int) $this->db->loadResult();
        $link = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($based).'&prl='.$this->leagueID;

        return $link;
    }
    public function getBasedLeague(){
        $sql = "SELECT p.leagueName "
            . " FROM #__bl_private_league as p"
            . " JOIN #__bl_private_based as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $this->db->setQuery($sql);
        $leagueName = $this->db->loadResult();
        if($leagueName){
            return ($leagueName);
        }

    }
    public function getActionsList(){
        $html = array();
        if($this->getOwnerID() == $this->user->id){
            $html = $this->getActionsListOwner();
        }else{
            $html = $this->getActionsListLeave();
        }
        return $html;
    }
    public function getActionsListPending(){
        $html = array();
        //join
        $html["join"] = '<input class="btn btn-success jpbtn-pos jpBtnJoin" type="button" value="'.JText::_('JSPL_FE_JOIN').'" />';
        //reject
        $html["reject"] = '<input class="btn btn-default jpbtn-neg jpBtnReject" type="button" value="'.JText::_('JSPL_FE_REJECT').'" />';
        return $html;
    }
    public function getActionsListOwner(){
        $html = array();
        //edit league
        $html["edit"] = '<input class="btn btn-default jpBtnEdit" type="button" value="'.JText::_('JSPL_FE_EDIT').'" />';

        //invite
        $html["invite"] = '<input class="btn btn-success jpbtn-pos jpBtnInvite" type="button" value="'.JText::_('JSPL_FE_INVITE').'" />';
        return $html;
    }
    public function getActionsListLeave(){
        $html = array();
        //leave
        $html["leave"] = '<input class="btn btn-primary jpbtn-neut jpBtnLeave" type="button" value="'.JText::_('JSPL_FE_LEAVE').'" />';
        return $html;
    }
}