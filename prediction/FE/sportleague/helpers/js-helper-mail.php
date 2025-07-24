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

class jsPredictionHelperMail
{
    public static function sendInviteByEmail($leagueID, $name, $email){
        $db = JFactory::getDBO();

        $subject = self::getMailSubject();

        $db->setQuery("SELECT p.*,b.leagueID FROM #__bl_private_league as p"
            ." JOIN #__bl_private_based as b ON p.id=b.privateID"
            ." WHERE p.id={$leagueID}");
        $leagueRow = $db->loadObject();

        if($leagueRow) {
            $url = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($leagueRow->leagueID).'&invitekey='.$leagueRow->invitekey;


            $url = '<a href="'.$url.'">';

            $args = array(
                "league_name" => $leagueRow->leagueName,
                "based_on" => $name,
                "site_name" => JFactory::getConfig()->get('sitename'),
                "invite_link" => $url
            );
            $message = self::replaceMailText($args);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            return jsPredictionHelperMail::sendEmail( $email, $subject, $message, $headers );

        }
        return false;

    }
    public static function sendInviteSiteUser($leagueID, $userID){
        $db = JFactory::getDBO();
        $user_info = JFactory::getUser($userID);

        $user_email = $user_info->email;

        $user_name = $user_info->name?$user_info->name:$user_info->user_login;

        $subject = self::getMailSubject();

        $db->setQuery("SELECT p.*,b.leagueID FROM #__bl_private_league as p"
            ." JOIN #__bl_private_based as b ON p.id=b.privateID"
            ." WHERE p.id={$leagueID}");
        $leagueRow = $db->loadObject();

        if($leagueRow) {

            $url = JUri::base().'/index.php?option=com_joomsport&view=userleague&id='.intval($leagueRow->leagueID).'&invitekey='.$leagueRow->invitekey;

            $url = '<a href="'.$url.'">';


            $sql = "SELECT p.leagueName "
                . " FROM #__bl_private_league as p"
                . " WHERE p.id=".$leagueRow->leagueID
                . " LIMIT 1"        ;
            $db->setQuery($sql);
            $competition =  $db->loadResult();

            $args = array(
                "league_name" => $leagueRow->leagueName,
                "based_on" => $competition,
                "site_name" => JFactory::getConfig()->get('sitename'),
                "invite_link" => $url
            );
            $message = self::replaceMailText($args);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            return jsPredictionHelperMail::sendEmail( $user_email, $subject, $message, $headers );

        }
        return false;

    }


    public static function sendEmail($to, $subject, $body, $headers){
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array(
            $config->get( 'mailfrom' ),
            $config->get( 'fromname' )
        );

        $mailer->setSender($sender);
        $mailer->addRecipient($to);
        $mailer->setSubject($subject);
        $mailer->isHtml(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        return $send = $mailer->Send();
    }

    public static function getMailText(){
        //$mail_settings = get_option("joomsport_prediction_mail_settings","");

        return isset($mail_settings["invText"])?$mail_settings["invText"]:JText::_("JSPL_FE_MAIL_BODY");
    }
    public static function getMailSubject(){
        //$mail_settings = get_option("joomsport_prediction_mail_settings","");
        return isset($mail_settings["invSubject"])?$mail_settings["invSubject"]:JText::_("JSPL_FE_MAIL_SUBJECT");
    }

    public static function replaceMailText($args){
        $text = self::getMailText();
        $preVars = array("league_name","based_on","site_name","invite_link");
        foreach($args as $key => $val){
            $text = str_replace("{".$key."}",$val,$text);
        }
        return $text;

    }

}
