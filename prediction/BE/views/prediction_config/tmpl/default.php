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
defined('_JEXEC') or die;

$lists = $this->lists;
JHTML::_('behavior.tooltip');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$sort = array("pts" => JText::_('JSPL_PREDICTION_PTS'),
            "filled" => JText::_('JSPL_PREDICTION_SORTNUM') ,
            "succavg" => JText::_('JSPL_PREDICTION_SORTRATE'));
        ?>
		<script type="text/javascript">
		<!--
		Joomla.submitbutton = function(task) {
			submitbutton(task);
		}
		function submitbutton(pressbutton) {
			var form = document.adminForm;

                        
                              submitform( pressbutton );
                                       return;
                        		
		}
                
                jQuery( document ).ready(function() {
                    
                    jQuery("#jspred_config_sort").sortable(

                    );
                    jQuery( "#jspred_config_sort" ).disableSelection();


                });
		//-->
		</script>

<form action="index.php?option=com_joomsport" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    

        <div class="jsrespdiv12">
            <div class="jsBepanel">
                <div class="jsBEheader">
                    <?php echo JText::_('BLBE_GENERAL'); ?>
                </div>
                <div class="jsBEsettings">
                    <table class="jsbetable">
			
			<tr>
				<td width="200">
					<?php echo JText::_('JSPL_SORTBY'); ?>
                                </td>
				<td>
                                    <table>
                                        <tbody id="jspred_config_sort">
                                        <?php 
                                        if(count($lists['sortfields'])){
                                        foreach($lists['sortfields'] as $key => $value){

                                        ?>
                                        <tr class="ui-state-default">
                                                <td width="30">
                                                    <span class="sortable-handler" style="cursor: move;">
                                                        <span class="icon-menu"></span>
                                                    </span>
                                                </td>
                                                <td style="padding-right:20px;"><?php echo $sort[$key]?></td>
                                                <td align="right">
                                                    <div class="controls">
                                                        <fieldset class="radio btn-group">
                                                            <?php echo JHTML::_('select.booleanlist',  $key.'_way', 'class="inputbox" ', $value, 'ASC', 'DESC');
                                    ?>
                                                        </fieldset>
                                                    </div>
                                                    <input type="hidden" name="sort_columns[]" value="<?php echo $key?>" />
                                                </td>	
                                        </tr>
                                           
                                        <?php 
                                        
                                        } 
                                        }
                                        ?>
                                    </tbody>
                                    </table>
				</td>
			</tr>
                        <tr>
                            <td>
                                <?php echo JText::_('JSPL_LIVECALCULATE');?>
                            </td>
                            <td>
                                <div class="controls">
                                    <fieldset class="radio btn-group">
                                        <?php echo JHTML::_('select.booleanlist',  'pred_livecalc', 'class="inputbox" ', $lists['pred_livecalc']);?>
                                    </fieldset>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo JText::_('JSPL_PRIVATELEAGUE_LINK');?>
                            </td>
                            <td>
                                <input type="text" name="privateleague_link" value="<?php echo addslashes($lists["privateleague_link"]);?>" />
                            </td>
                        </tr>

                        
                    </table>

                </div>
            </div>

        </div>    
		<input type="hidden" name="option" value="com_joomsport" />
		<input type="hidden" name="task" value="prediction_config" />
                <input type="hidden" name="controller" value="predleague" />

                
		<?php echo JHTML::_('form.token'); ?>
	</form>