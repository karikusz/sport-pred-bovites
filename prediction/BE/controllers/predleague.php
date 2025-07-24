<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class predleagueController extends JControllerLegacy{
    protected $option = 'com_joomsport';
    protected $mainframe = null;
    public function __construct()
    {

        parent::__construct();
    }
    
    public  function predleague_apply(){
        $vName = 'predleague_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->savePredleague();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predleague_edit&cid[]='.$model->_id);
    }
    public  function predleague_save(){
        $vName = 'predleague_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $id = $model->savePredleague();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predleague_list');
    } 
    public  function predleague_save_new(){
        $vName = 'predleague_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->savePredleague();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predleague_edit&cid[]=0');
    }
    public  function predleague_del(){
        $vName = 'predleague_list';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->delPredleague();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predleague_list');
    }
    
    public  function predround_apply(){
        $vName = 'predround_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->savePredround();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predround_edit&cid[]='.$model->_id);
    }
    public  function predround_save(){
        $vName = 'predround_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $id = $model->savePredround();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predround_list');
    } 
    public  function predround_save_new(){
        $vName = 'predround_edit';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->savePredround();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predround_edit&cid[]=0');
    }
    public  function predround_del(){
        $vName = 'predround_list';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->delPredround();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predround_list');
    }
    private function js_Model($name)
    {
        $newclass = false;
        $path = dirname(__FILE__).'/../models/';
        
        if (file_exists($path.'default/'.$name.'.php')) {
            require $path.'default/'.$name.'.php';
        }
        

        return $newclass;
    }
    public function predleague_list(){
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predleague_list');
    }
    public function predround_list(){
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=predround_list');
    }
    
     public function save_prediction_config(){
        
        $vName = 'prediction_config';
        $this->js_Model($vName);
        $classname = $vName.'JSModel';
        $model = new $classname();
        $model->savePredictionConfig();
        JFactory::getApplication()->redirect('index.php?option='.$this->option.'&task=prediction_config');
    }
}