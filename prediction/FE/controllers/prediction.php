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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

$mainframe = JFactory::getApplication();


$task = JRequest::getVar('task', null, 'default', 'cmd');
require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsport/sportleague/classes/class-jsprediction-myleague_actions.php';
require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsport/sportleague/classes/class-jsprediction-league-row.php';

class JoomsportControllerPrediction extends JControllerLegacy
{
    protected $js_prefix = '';
    protected $mainframe = null;
    protected $option = 'com_joomsport';
    protected $db = null;
    protected $user = null;

    public function __construct()
    {
        parent::__construct();
        $this->mainframe = JFactory::getApplication();
        $this->db = JFactory::getDBO();
        $this->user = JFactory::getUser();

    }

    public function jspred_private_league_add(){

        $return = array("error"=>0,"msg"=>"","leagueid"=>0,"invite" => '',"partic"=>array(), "tdaction" => '');
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{
            if(!addslashes($_POST["league_name"]) || !intval($_POST["base_league"])){
                $return["error"] = 1;
                $return["msg"] = JText::_("JSPL_FE_LEAGUE_NOT_SPECIFIED");
                echo json_encode($return);
                exit();
            }

            $token = $this->generateToken();


            $query = "INSERT INTO #__bl_private_league(id,leagueName,is_private,creatorID,invitekey)"
            ." VALUES(0,'".addslashes($_POST["league_name"])."',0,".intval($this->user->id).", '".$token."')";
            $this->db->setQuery($query);
            $this->db->query();

            $leagueID = $this->db->insertid();
            if($leagueID){
                $return["leagueid"] = $leagueID;
                if(intval($_POST["base_league"])){
                    $this->db->setQuery("INSERT INTO #__bl_private_based(leagueID,privateID) VALUES(".intval($_POST["base_league"]).",".intval($leagueID).")");
                    $this->db->query();
                }

                //add users
                if(isset($_POST["import_from"]) && intval($_POST["import_from"])){
                    $this->db->setQuery("INSERT INTO #__bl_private_users(privateID,userID,confirmed) SELECT {$leagueID}, userID, confirmed FROM #__private_users WHERE confirmed=1 AND privateID = ".intval($_POST["import_from"]));
                    $this->db->query();

                }

                $url = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($_POST["base_league"]).'&invitekey='.$token;

                $return["invite"] = $url;

                $obj = new jsPredictionMyLeagueActions($leagueID);
                $partic = $obj->getParticipants();
                jsPredictionHelper::addUserToPrivateLeague($leagueID,$this->user->id,1);

                $row = new jsPredictionLeagueRow($leagueID);
                $actions = $row->getActionsList();

                $return["edit"] = $actions["edit"];
                $return["delete"] = '';
                $return["invitation"] = $actions["invite"];
                $return["title_url"] = $row->getLink();
                $return["title"] = $row->getTitle();
                $return["based"] = $row->getBasedLeague();
                $return["owner"] = $row->getOwner();
                $return["users"] = $row->getUsersCount();

                $return["partic"] = $partic;

                $sql = "SELECT p.name"
                    . " FROM #__bl_predleague as p"
                    . " WHERE p.id = ".intval($_POST["base_league"]);
                $this->db->setQuery($sql);


                $competition = $this->db->loadResult();


                $email_subject = jsPredictionHelperMail::getMailSubject();
                $args = array(
                    "league_name" => addslashes($_POST["league_name"]),
                    "based_on" => $competition,
                    "site_name" => JFactory::getConfig()->get('sitename'),
                    "invite_link" => "%0D%0A%0D%0A" . $url
                );
                $email_body = jsPredictionHelperMail::replaceMailText($args);


                $return["emaillink"] = "mailto:user@example.com?subject=".($email_subject)."&body=".($email_body);

            }
        }
        echo json_encode($return);
        exit();
    }
    public function generateToken(){
        //Generate a random string.
        $token = openssl_random_pseudo_bytes(16);

        //Convert the binary data into hexadecimal representation.
        $token = bin2hex($token);

        $this->checkTokenPL($token);

        //Print it out for example purposes.
        return $token;
    }

    public function checkTokenPL($token){
        $query = "SELECT id FROM #__bl_private_league WHERE invitekey='".addslashes($token)."'";
        $this->db->setQuery($query);

        if($this->db->loadResult()){
            $this->generateToken();
        }

    }

    public function jspred_private_league_invite(){
        $return = array("error"=>0,"msg"=>"");
        parse_str($_POST["form"], $formadata);
        $leagueid = intval($_POST["leagueid"]);
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);

            if(isset($formadata["user_invited"]) && count($formadata["user_invited"])){

                $obj->inviteSiteUsers($formadata["user_invited"]);

            }

            if(isset($formadata["invbyemail_name"]) && isset($formadata["invbyemail_email"])){
                for($intA=0;$intA<count($formadata["invbyemail_name"]);$intA++){
                    if($formadata["invbyemail_name"][$intA] && $formadata["invbyemail_email"][$intA]){
                        $res = $obj->inviteUsersByEmail($formadata["invbyemail_name"][$intA], $formadata["invbyemail_email"][$intA]);
                        if($res !== true){
                            $return["error"] = 1;
                            $return["msg"] .= JText::_("JSPL_FE_CANTSEND_EMAIL").$formadata["invbyemail_email"][$intA];
                        }
                    }
                }
            }
        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_remove_part(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        $pid = intval($_POST["pid"]);
        $obj = new jsPredictionMyLeagueActions($leagueid);
        $obj->removeFromLeague(array($pid));

        $row = new jsPredictionLeagueRow($leagueid);

        $return["users"] = $row->getUsersCount();

        echo json_encode($return);
        exit();
    }

    public function jspred_private_remove_league(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);

        $obj = new jsPredictionMyLeagueActions($leagueid);
        $return = $obj->removeLeague();


        echo json_encode($return);
        exit();
    }


    public function jspred_private_join(){
        $return = array("error"=>0,"msg"=>"","tdaction"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->joinLeague();
            $row = new jsPredictionLeagueRow($leagueid);
            $actions = $row->getActionsList();

            $return["leave"] = $actions["leave"];
            $return["users"] = $row->getUsersCount();
            $return["owner"] = $row->getOwner();
        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_reject(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->rejectLeague();
        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_leave(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->leaveLeague();
        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_update_league(){
        $return = array("error"=>0,"msg"=>"","title"=>"");
        $leagueid = intval($_POST["leagueid"]);
        $import_from = intval($_POST["import_from"]);

        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }elseif(addslashes($_POST['league_name'])){

            $query = "UPDATE #__bl_private_league SET leagueName = '".addslashes($_POST['league_name'])."' WHERE id=".$leagueid." AND creatorID = ".$this->user->id;
            $this->db->setQuery($query);
            $this->db->query();
            $return["title"] = stripslashes($_POST['league_name']);
            if($import_from){
                $this->db->setQuery("INSERT IGNORE INTO #__bl_private_users(privateID,userID,confirmed) SELECT {$leagueid}, userID, confirmed FROM #__bl_private_users WHERE confirmed=1 AND privateID = ".intval($import_from));
                $this->db->query();
                $row = new jsPredictionLeagueRow($leagueid);

                $return["users"] = $row->getUsersCount();
            }
        }else{
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_LEAGUE_NOT_SPECIFIED");

        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_load_league(){


        $leagueid = intval($_POST["leagueid"]);
        $return = array("error"=>0,"msg"=>"","leagueid"=>$leagueid,"invite" => '',"partic"=>array());
        if(!$this->user->id){
            $return["error"] = 1;
            $return["msg"] = JText::_("JSPL_FE_PLEASE_LOGIN");
        }else{

            $this->db->setQuery("SELECT p.*,b.leagueID FROM #__bl_private_league as p"
                ." JOIN #__bl_private_based as b ON p.id=b.privateID"
                ." WHERE p.id={$leagueid} AND p.creatorID=".$this->user->id);
            $leagueRow = $this->db->loadObjec();

            if($leagueRow){


                $url = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($leagueRow->leagueID).'&invitekey='.$leagueRow->invitekey;

                $return["invite"] = $url;

                $obj = new jsPredictionMyLeagueActions($leagueid);
                $partic = $obj->getParticipants();

                $return["partic"] = $partic;

                $sql = "SELECT p.leagueName "
                    . " FROM #__bl_private_league as p"
                    . " WHERE p.id=".$leagueRow->leagueID
                    . " LIMIT 1"        ;
                $this->db->setQuery($sql);
                $competition =  $this->db->loadResult();

                $email_subject = "Invite";
                $email_body = "You invited to league %s based on %s.";
                $email_body = sprintf($email_body, $leagueRow->leagueName, $competition);
                $email_body .= "%0D%0A%0D%0A" . $url;
                $return["emaillink"] = "mailto:user@example.com?subject=".($email_subject)."&body=".($email_body);

            }
        }
        echo json_encode($return);
        exit();
    }

    public function jspred_private_users(){

        $q = addslashes($_POST["q"]);
        $leagueid = intval($_POST["leagueid"]);
        $return = array("results" => array());

        $sql = "SELECT pu.userID"
            . " FROM #__bl_private_users as pu"
            . " JOIN #__users as u ON u.id = pu.userID"
            . " WHERE pu.privateID=".$leagueid
            . " AND pu.confirmed IN (0,1)"
            . " ORDER BY u.username";
        $this->db->setQuery($sql);
        $usrs = $this->db->loadColumn();

        $query = "SELECT id, username as text FROM #__users"
            ." WHERE username LIKE '%".$q."%'"
            .(count($usrs)?" AND ID NOT IN (".implode(",",$usrs).")":"")
            ." ORDER BY username LIMIT 25";
        $this->db->setQuery($query);
        $return["results"] = $this->db->loadObjectList();
        echo json_encode($return);
        exit();
    }

}
?>