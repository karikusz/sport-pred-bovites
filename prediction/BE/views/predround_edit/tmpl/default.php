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

                        if(pressbutton == 'predround_apply' || pressbutton == 'predround_save' || pressbutton == 'predround_save_new'){

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
                
                function bl_jsadd_matches(){
                    var pred_selmatches = [];
                    
                    jQuery("input[name^='pred_selmatches']").each(function(){
                        pred_selmatches.push(jQuery(this).val());
                     
                    });
                    //console.log(pred_selmatches);
                    jQuery('#select_match option:selected').each(function(){
                        //console.log(jQuery(this).val());
                        //console.log(pred_selmatches.indexOf(jQuery(this).val()));
                        if(pred_selmatches.indexOf(jQuery(this).val()) == '-1'){
                            jQuery('#jsNewPredMatches').append("<tr><td><a href='javascript: void(0);' onClick='javascript:Delete_tbl_row(this); return false;' title='<?php echo JText::_('BLBE_DELETE');?>'><img src='components/com_joomsport/img/publish_x.png'  border='0' alt='Delete'></a><input type='hidden' value='"+jQuery(this).val()+"' name='pred_selmatches[]' /></td><td>"+jQuery(this).text()+"</td></tr>");
                        }
                    });
                    
                }
		function Delete_tbl_row(element) {
			var del_index = element.parentNode.parentNode.sectionRowIndex;
			var tbl_id = element.parentNode.parentNode.parentNode.parentNode.id;
			element.parentNode.parentNode.parentNode.deleteRow(del_index);
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
					<?php echo JText::_('JSPL_PREDICTION_ROUND_NAME'); ?>
                                </td>
				<td>
					<input type="text" maxlength="255" size="60" name="plname" value="<?php echo htmlspecialchars($row->rname)?>" />
				</td>
			</tr>
                        
                        <?php
                        if(isset($lists['matches'])){
                        ?>
                        <tr>
                            <td>
                                
                            </td>
                            <td>
                                <?php echo $lists['matches'];?>
                                <input class="btn btn-small" type="button" style="cursor:pointer;"  value="<?php echo JText::_('BLBE_ADD');?>" onClick="bl_jsadd_matches();" />
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
		</table>
                <table id="jsNewPredMatches">
                    <?php
                    if(isset($lists['matches_allready'])){
                        foreach ($lists['matches_allready'] as $m){
                            echo '<tr>';
                            echo "<td><a href='javascript: void(0);' onClick='javascript:Delete_tbl_row(this); return false;' title='".JText::_('BLBE_DELETE')."'><img src='components/com_joomsport/img/publish_x.png'  border='0' alt='Delete'></a><input type='hidden' value='".$m->id."' name='pred_selmatches[]' /></td>";
                            echo "<td>".$m->name."</td>";
                            echo '</tr>';
                        }
                    }
                    ?>
                </table>    
            </div>
        </div>

    </div>    
		<input type="hidden" name="option" value="com_joomsport" />
		<input type="hidden" name="task" value="predround_edit" />
                <input type="hidden" name="controller" value="predleague" />
		<input type="hidden" name="id" value="<?php echo $row->id?>" />
		<input type="hidden" name="cid[]" value="<?php echo $row->id?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="jscurtab" id="jscurtab" value="" />
                <input type="hidden" name="jsgallery" value="" />
                <input type="hidden" name="league_id" value="<?php echo $lists['leagues'];?>" />
                <input type="hidden" name="league_id_chzn" value="<?php echo $lists['leagues'];?>" />

                
		<?php echo JHTML::_('form.token'); ?>
	</form>