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
$rows = $this->rows;
$sort_way = $this->lists['sortway'];
$sort_field = $this->lists['sortfield'];
require_once 'components/com_joomsport/helpers/jshtml.php';
    JHTML::_('behavior.tooltip');
        ?>

    <script type="text/javascript">
		<!--
		Joomla.submitbutton = function(task) {
			submitbutton(task);
		}
		function submitbutton(pressbutton) {
			var form = document.adminForm;

                        if(pressbutton == 'predleague_del'){

                            form.controller.value="predleague";
                                submitform( pressbutton );
                                       return;
                            
                    
                        
                        }else{
                              submitform( pressbutton );
                                       return;
                        }		
		}	
		
		//-->
		</script>


<form action="index.php?option=com_joomsport" method="post" name="adminForm" id="adminForm">
		<?php
        if (!($lists['totplayer'])) {
            JhtmlJshtml::createmess('predleague_add');
        } else {
            ?>
		
		<div id="filter-bar" class="btn-toolbar">
            <div class="pull-left">
                <div class="filter-search btn-group pull-left" style="float:left !important;">
                    <label for="js_filter_search" class="element-invisible"><?php echo JText::_('Filter');
            ?></label>
                    <input type="text" name="js_filter_search" id="js_filter_search" value="<?php echo $this->lists['js_filter_search'];
            ?>" title="<?php echo JText::_('Search');
            ?>" />
                    <input type="hidden" name="is_search" value="0" />
                </div>
                <div class="btn-group pull-left" style="float:left !important;">
                    <button type="submit" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT');
            ?>"><i class="icon-search"></i></button>
                    <button type="button" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR');
            ?>" onclick="document.id('js_filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                </div>
            </div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');
            ?></label>
				<?php echo $pagination->getLimitBox();
            ?>
			</div>
            
		</div>

		<div style="clear:both;"></div>


		<table class="table table-striped">
		<thead>
			<tr>
				<th width="2%" align="left">
					<?php echo JText::_('#');
            ?>
				</th>
				<!--th width="2%" align="left">
					<?php //echo JText::_( 'ID' ); ?>
				</th-->
				<th width="2%">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
				<th class="title" width="50%">
					<?php 
                        $sort_way_on = ($sort_field == 'name' && $sort_way == 'ASC') ? 'DESC' : 'ASC';

            ?>
					<a href="#" onclick="javascript:JSPRO_order('name','<?php echo $sort_way_on;
            ?>');" >
						<?php echo JText::_('JSPL_PREDICTION_LEAGUE');
            ?>
                        <?php
                        if ($sort_field == 'name') {
                            $sort_img = $sort_way == 'ASC' ? 'icon-arrow-down' : 'icon-arrow-up';
                            echo '<i class="'.$sort_img.'"/></i>';
                        }
            ?>
					</a>
				</th>
				
				
			</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="13">
				<?php echo $this->page->getListFooter();
            ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
        $k = 0;
        //print_r($rows);
        if (count($rows)) {
            for ($i = 0, $n = count($rows); $i < $n; ++$i) {
                $row = $rows[$i];
                JFilterOutput::objectHtmlSafe($row);
                $link = JRoute::_('index.php?option=com_joomsport&task=predleague_edit&cid[]='.$row->id);
                $checked = @JHTML::_('grid.checkedout',   $row, $i);
            //$published 	= JHTML::_('grid.published', $row, $i);
            ?>
			<tr class="<?php echo "row$k";
                ?>">
				<td>
					<?php echo $this->page->getRowOffset($i);
                ?>
				</td>
				<!--td>
					<?php //echo $row->id; ?>
				</td-->
				<td>
					<?php echo $checked;
                ?>
				</td>
				<td>
					<?php

                        echo '<a href="'.$link.'">'.$row->name.'</a>';
                ?>
				</td>
				
				
			</tr>
			<?php

            }
        }
            ?>
		</tbody>
		</table>
                <?php

        }
                ?>
		<input type="hidden" name="option" value="com_joomsport" />
		<input type="hidden" name="task" value="predleague_list" />
                <input type="hidden" name="controller" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="sortfield" value="<?php echo $sort_field; ?>" />
		<input type="hidden" name="sortway" value="<?php echo $sort_way; ?>" />
		<?php echo JHTML::_('form.token'); ?>
	</form>