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
$row = $this->row;
$lists = $this->lists;
JHTML::_('behavior.tooltip');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

        ?>
		<script type="text/javascript">
		<!--
		Joomla.submitbutton = function(task) {
			submitbutton(task);
		}
		function submitbutton(pressbutton) {
			var form = document.adminForm;

                        if(pressbutton == 'predleague_apply' || pressbutton == 'predleague_save' || pressbutton == 'predleague_save_new'){

                            if(form.plname.value != ''){
                                var srcListName = 'seas_all_add';
					var srcList = eval( 'form.' + srcListName );
					if(srcList){
						var srcLen = srcList.length;
					
						for (var i=0; i < srcLen; i++) {
								srcList.options[i].selected = true;
						}
					}
                                submitform( pressbutton );
                                       return;
                            }
                    
                        
                        }else{
                              submitform( pressbutton );
                                       return;
                        }		
		}	
		
		//-->
		</script>
		<?php
        if (!($row)) {
            echo "<div id='system-message'>".JText::_('BLBE_NOITEMS').'</div>';
        }
        ?>
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
					<?php echo JText::_('BLBE_NAME'); ?>
                                </td>
				<td>
					<input type="text" maxlength="255" size="60" name="plname" value="<?php echo htmlspecialchars($row->name)?>" />
				</td>
			</tr>
			
                        <tr>
                            <td colspan="2">
                                <table  border="0">
			<tr>
				<td width="200">
					<?php echo JText::_('BLBE_ADD_SEASON');
            ?>
					
				</td>
				<td width="150">
                                    <div class="selectedlistdescr"><?php echo JText::_('BLBE_AVAILABLE')?></div>
					<?php echo $this->lists['seasons'];
            ?>
				</td>
				<td valign="middle" width="60" align="center">
					<input class="btn" type="button" style="cursor:pointer;" value=">>" onClick="javascript:JS_addSelectedToList('adminForm','seas_all','seas_all_add');" /><br />
					<input class="btn" type="button" style="cursor:pointer;" value="<<" onClick="javascript:JS_delSelectedFromList('adminForm','seas_all_add','seas_all');" />
				</td>
				<td >
                                    <div class="selectedlistdescr"><?php echo JText::_('BLBE_SELECTED')?></div>
					<?php echo $this->lists['seasons_add'];
            ?>
				</td>
			</tr>
		</table>
                            </td>
                        </tr>
		</table>
            </div>
        </div>
        <div class="jsBepanel">
            <div class="jsBEheader">
                <?php echo JText::_('JSPL_PREDICTION_LEAGUE_POINTS'); ?>
            </div>
            <div class="jsBEsettings">
                <table class="jsbetable">
                    <?php
                        for($intA = 0; $intA < count($lists['predictions']); $intA++){
                        ?>
                        <tr>
                            <td>
                                <?php echo $lists['predictions'][$intA]['object']->getTitle();?>
                            </td>
                            <td>
                                <?php echo $lists['predictions'][$intA]['object']->getAdminView();?>
                                
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                </table>
            </div>
        </div>    

    </div>    
		<input type="hidden" name="option" value="com_joomsport" />
		<input type="hidden" name="task" value="predleague_edit" />
                <input type="hidden" name="controller" value="predleague" />
		<input type="hidden" name="id" value="<?php echo $row->id?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="jscurtab" id="jscurtab" value="" />
                <input type="hidden" name="jsgallery" value="" />
		<?php echo JHTML::_('form.token'); ?>
	</form>