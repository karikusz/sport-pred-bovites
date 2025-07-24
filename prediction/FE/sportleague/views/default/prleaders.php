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
$offset = isset($rows->lists['pagination']->offset)?$rows->lists['pagination']->offset:0;

?>
<div>
    <form role="form" method="post" lpformnum="1">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th rowspan="2" class="jsalcenter">
                        #
                    </th>
                    <th rowspan="2" style="text-align:left;">
                        <?php echo JText::_('JSPL_FE_USER');?>

                    </th>

                    <th rowspan="2" class="jsalcenter">
                        <?php echo JText::_('JSPL_FE_POINTS');?>

                    </th>
                    <th rowspan="2" class="jsalcenter">
                        <?php echo JText::_('JSPL_FE_PREDICTIONNUM');?>

                    </th>
                    <th colspan="4" class="jsalcenter">
                        <?php echo JText::_('JSPL_FE_EXACTSUCC');?>

                    </th>
                </tr>
                <tr>
                    <th><?php echo JText::_('JSPL_FE_EXACT');?></th>
                    <th><?php echo JText::_('JSPL_FE_SCORE_DIFF');?></th>
                    <th><?php echo JText::_('JSPL_FE_CORECT_WINNER');?></th>

                    <th><?php echo JText::_('JSPL_FE_FAILED');?></th>

                </tr>
            </thead>
            <tbody>
            <?php
            for ($intA = 0; $intA < count($rows->object); ++$intA) {
                $round = $rows->object[$intA];
                
                ?>
                <tr>
                    <td style="text-align:left;" nowrap="nowrap">
                        <?php
                        $curPlace = $intA + 1 + $offset;
                        echo $curPlace;
                        ?>
                        <?php
                        /*<!--jsonlyinproPHP-->*/
                        if(count($rows->lists['previuos_places'])){
                            if(isset($rows->lists['previuos_places'][$round->user_id])){
                                if($rows->lists['previuos_places'][$round->user_id] > ($curPlace)){
                                    echo '<span class="jspred_position_up" title="+'.($rows->lists['previuos_places'][$round->user_id] - ($curPlace)).'"></span>';
                                    echo '<span class="jspred_position_up_text">+'.($rows->lists['previuos_places'][$round->user_id] - ($curPlace)).'</span>';
                                }elseif($rows->lists['previuos_places'][$round->user_id] < ($curPlace)){
                                    echo '<span class="jspred_position_down"  title="'.($rows->lists['previuos_places'][$round->user_id] - ($curPlace)).'"></span>';
                                    echo '<span  class="jspred_position_down_text">'.($rows->lists['previuos_places'][$round->user_id] - ($curPlace)).'</span>';

                                }else{
                                    echo '<span class="jspred_position_current"></span>';
                                }
                            }
                        }
                        /*<!--/jsonlyinproPHP-->*/
                        ?>
                    </td>
                    <td style="text-align:left;">
                        <a href="<?php echo JRoute::_('index.php?option=com_joomsport&task=userleague&id='.$rows->league->id.'&usrid='.$round->user_id.(isset($_REQUEST["prl"])?'&prl='.intval($_REQUEST["prl"]):'').'&Itemid='.$Itemid)?>"><?php echo ($round->username);?></a>
                    </td>

                    <td class="jsalcenter">
                        <?php echo $round->pts;?>
                    </td>
                    <td class="jsalcenter">
                        <?php echo $round->filled;?>
                    </td>
                    <td class="jsalcenter">
                        <?php echo $round->success;
                        if($round->success){
                            echo '('.round($round->succavg*100).')';
                        }?>
                    </td>

                    <td class="jsalcenter">
                        <?php
                        echo $round->score_diff;
                        if($round->score_diff){
                            echo '('.round(100*($round->score_diff/$round->filled)).')';
                        }
                        ?>
                    </td>
                    <td class="jsalcenter">
                        <?php
                        echo $round->winner_side;
                        if($round->winner_side){
                            echo '('.round(100*($round->winner_side/$round->filled)).')';
                        }
                        ?>
                    </td>
                    <td class="jsalcenter">
                        <?php
                        $failed = $round->filled - $round->success - $round->score_diff - $round->winner_side;
                        echo $failed;
                        if($failed){
                            echo '('.round(100*($failed/$round->filled)).')';
                        }
                        ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>    
    <?php
    if (isset($rows->lists['pagination']) && $rows->lists['pagination']) {
        require_once JS_PATH_VIEWS.'elements'.DIRECTORY_SEPARATOR.'pagination.php';
        echo paginationView($rows->lists['pagination']);
    }
    ?>
    </form>
</div>
<?php
classJsportAddtag::addCSS(JS_LIVE_ASSETS.'css/prediction.css');
?>