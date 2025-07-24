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

require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsport/sportleague/helpers/js-helper-prediction.php';
require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsport/sportleague/helpers/js-helper-mail.php';

require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsport/sportleague/classes/class-jsprediction-league-row.php';

class jsPredictionMyLeagueActions{
    public $leagueID;
    private $user;
    private $db;
    
    public function __construct($id){
        $this->leagueID = (int) $id;
        $this->user = JFactory::getUser();
        $this->db = JFactory::getDBO();
    }
    
    public function inviteUsersByEmail($name, $email){
        return jsPredictionHelperMail::sendInviteByEmail($this->leagueID, $name, $email);
    }
    public function loadFromLeague($leagueID){

        $this->db->setQuery("SELECT userID FROM #__bl_private_users WHERE privateID = ".$this->leagueID." AND confirmed = '1'");
        $users = $this->db->loadObjectList();
        foreach($users as $userID){
            jsPredictionHelper::addUserToPrivateLeague($this->leagueID,$userID,1);
        }
    }
    public function removeFromLeague($usersArray){
        foreach($usersArray as $userID){
            jsPredictionHelper::removeUserToPrivateLeague($this->leagueID,$userID);
        }
        
    }
    public function inviteSiteUsers($usersArray){
        foreach($usersArray as $userID){
            if(jsPredictionHelper::addUserToPrivateLeague($this->leagueID,$userID,0)){
                jsPredictionHelperMail::sendInviteSiteUser($this->leagueID,$userID);
            }
        }
    }
    public function getParticipants(){

        $sql = "SELECT u.username, pu.userID, pu.confirmed "
                . " FROM #__bl_private_users as pu"
                . " JOIN #__users as u ON u.id = pu.userID"
                . " WHERE pu.privateID=".$this->leagueID
                . " AND pu.userID != ".$this->user->id
                . " ORDER BY u.username";
        $this->db->setQuery($sql);
        
        return $this->db->loadObjectList()?$this->db->loadObjectList():array();
    }
    public function joinLeague(){
        $sql = "UPDATE #__bl_private_users "
            . " SET confirmed = '1'"
            . " WHERE userID = ".$this->user->id
            . " AND privateID=".$this->leagueID;
        $this->db->setQuery($sql);
        return $this->db->query();

    }
    public function rejectLeague(){
        $sql = "UPDATE #__bl_private_users "
            . " SET confirmed = '2'"
            . " WHERE userID = ".$this->user->id
            . " AND privateID=".$this->leagueID;
        $this->db->setQuery($sql);
        return $this->db->query();

    }
    public function leaveLeague(){
        $this->removeFromLeague(array($this->user->id));


    }
    public function removeLeague(){
        $json = array("error"=>"1", "msg"=>"");
        $row = new jsPredictionLeagueRow($this->leagueID);

        if($row->getOwnerID() == $this->user->id){

            $this->db->setQuery(" DELETE FROM #__bl_private_league WHERE id = ".$this->leagueID);
            $this->db->query();

            $this->db->setQuery(" DELETE FROM #__bl_private_based WHERE privateID = ".$this->leagueID);
            $this->db->query();

            $this->db->setQuery(" DELETE FROM #__bl_private_users WHERE privateID = ".$this->leagueID);
            $this->db->query();

            $json = array("error"=>"0", "msg"=>"");
        }else{
            $json = array("error"=>"1", "msg"=>JText::_( 'JSPL_FE_NO_PERMISSIONS'));
        }
        return ($json);

    }
}