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
$app = JFactory::getApplication();
$Itemid = $app->input->get('Itemid', 0, 'int');

?>
<div>
    <div class="table-responsive">
        <div class="jstable">
            <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php echo JText::_('JSPL_FE_ROUNDS');?>

                    </div>
                    <div class="jstable-cell">
                        <?php echo JText::_('JSPL_FE_START_DATE');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo JText::_('JSPL_FE_FILLING');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo JText::_('JSPL_FE_POINTS');?>

                    </div>

                </div>
            <?php
            for ($intA = 0; $intA < count($rows->object); ++$intA) {
                $round = $rows->object[$intA];
                $link = JRoute::_('index.php?option=com_joomsport&task=userround&id='.$round->id.'&usrid='.$rows->usrid.(isset($_REQUEST["prl"])?'&prl='.intval($_REQUEST["prl"]):'').'&Itemid='.$Itemid);
                ?>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <a href="<?php echo $link;?>"><?php echo ($round->rname);?></a>
                    </div>
                    <div class="jstable-cell">
                        <?php
                        $res = $rows->getRoundStatus($round->id);
                        switch($res){
                            case '0':
                                echo JText::_('JSPL_FE_PREDICTION_CLOSED');
                                break;

                            default:
                                echo $round->startdate;
                                break;
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        echo $rows->getFilling($round->id);

                        switch($res){

                            case '1':
                                echo '  <a href="'.$link.'"><input type="button" class="btn btn-success" value="'.JText::_('JSPL_FE_PREDICT').'" /></a>';
                                break;
                            case '2':
                                echo '  <a href="'.$link.'"><input type="button" class="btn btn-default" value="'.JText::_('JSPL_FE_CHANGE').'" /></a>';
                                break;
                            default:

                                break;
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo $rows->getPoints($round->id);?>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>
    </div>    
</div>
