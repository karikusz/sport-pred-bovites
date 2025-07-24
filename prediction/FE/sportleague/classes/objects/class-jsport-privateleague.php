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


class classJsportPrivateleague
{

    public $usrid = null;
    public $lists = null;

    public function __construct($id = 0)
    {
        $this->usrid = classJsportUser::getUserId();
        $this->loadObject();
    }

    private function loadObject()
    {
        global $jsDatabase;


    }

    public function getRow()
    {

        return $this;
    }
    public function getRowSimple()
    {
        return $this;
    }
    

    
}
