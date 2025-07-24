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
?>
<div>
    <form action="" method="post" name="jspRound" id="jspRound">
    <div class="table-responsive">
        <?php
        if(!classJsportUser::getUserId()){
        ?>
        <div class="jspred_message_login"><?php echo JText::_("JSPL_PLEASE_LOGIN");?></div>
        <?php
        }
        ?>
        <div class="jstable">
            <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php echo JText::_('JSPL_FE_DATEANDTIME');?>

                    </div>
                    <div class="jstable-cell">
                        <?php echo JText::_('JSPL_FE_HOMEPARTICIPIANT');?>

                    </div>
                    <div class="jstable-cell">
                        <?php echo JText::_('JSPL_FE_HOMEPARTICIPIANT');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo JText::_('JSPL_FE_PREDICTION');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo JText::_('JSPL_FE_RESULT');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo JText::_('JSPL_FE_POINTS');?>

                    </div>

                </div>
            <?php
            for ($intA = 0; $intA < count($lists['matches']); ++$intA) {
                $match = $lists['matches'][$intA];
                ?>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php
                        $match_date = classJsportDate::getDate($match->object->m_date, $match->object->m_time);
                        echo $match_date;
                        ?>
                    </div>
                    <div class="jstable-cell">
                        <div class="jsDivLineEmbl">
                            <?php
                            $partic_home = $match->getParticipantHome();
                            echo $partic_home->getEmblem(true, 0, '');
                            echo jsHelper::nameHTML($partic_home->getName(true));
                            ?>
                        </div>    
                    </div>
                    <div class="jstable-cell">
                        <div class="jsDivLineEmbl">
                        <?php
                        $partic_away = $match->getParticipantAway();
                        echo $partic_away->getEmblem(true, 0, '');
                        echo jsHelper::nameHTML($partic_away->getName(true));
                        ?>
                        </div>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        echo $rows->getPredict($match->object->id);
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        echo jsHelper::getScore($match, '', '', 0, true);
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        echo $rows->getMatchPoint($match->object->id);
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>
    </div>  
    <?php
    if($rows->canSave()){
    ?>
    <div>
        <input type="button" class="btn btn-default pull-right button" id="jspRoundSave" value="<?php echo JText::_('BLFA_SAVE');?>" />
    </div>
    <?php
    }
    ?>
        <input type="hidden" name="jspAction" value="saveRound" />
    </form>    
</div>
<?php
classJsportAddtag::addJS(JS_LIVE_ASSETS.'js/jsprediction.js');
classJsportAddtag::addCSS(JS_LIVE_ASSETS.'css/prediction.css');
?>