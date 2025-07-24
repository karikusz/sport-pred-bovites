<?php
/*
 * Private League Shortcode
 */

JText::script('JSPL_FE_LEAGUE_NOT_SPECIFIED');
JText::script('JSPL_FE_ALERT_DELETE');
JText::script('JSPL_FE_PENDING');
JText::script('JSPL_FE_REJECTED');
JText::script('JSPL_FE_ALERT_DELETE_PARTIC');
JText::script('JSPL_FE_ALERT_LEAVE');

require_once JS_PATH_CLASSES . DIRECTORY_SEPARATOR . 'class-jsprediction-myleagues.php';
require_once JS_PATH_CLASSES . DIRECTORY_SEPARATOR . 'class-jsprediction-league-row.php';
require_once JS_PATH_HELPERS . DIRECTORY_SEPARATOR . 'js-helper-prediction.php';

$activeLeagues = jsPredictionMyLeagues::getActiveLeaguesList();
$archiveLeagues = jsPredictionMyLeagues::getArchiveLeaguesList();
$invitedLeague = jsPredictionMyLeagues::getInvitedLeagues();

$isHidden = (count($archiveLeagues) || count($invitedLeague) || count($activeLeagues))?' style="display:block;"':' style="display:none;"';
?>
<div id="joomsport-container" class="plsContainer" >
    <nav class="navbar clearfix">
        <div class="nav navbar-nav pull-right">
            <input id="jspNewLeague" class="btn" type="button" value="<?php echo JText::_('Create Private League');?>" />
        </div>
    </nav>
    <div>
        <div class="tabs" id="plTabsContainerdiv" <?=$isHidden;?>>


            <div class="tab-content">
                <div class="row">
                    <div id="activeLeagues" class="tab-pane fade in active" >
                        <?php
                        for($intA=0;$intA<count($invitedLeague);$intA++){
                            $row = new jsPredictionLeagueRow($invitedLeague[$intA]->ID);
                            $actions = $row->getActionsListPending();

                            echo '<div class="col-lg-4 col-sm-6 col-xs-12">';
                            echo '<div class="table-responsive">';
                            echo '<div class="jsPrivHeaderBlock">';
                            echo '<a href="'.$row->getLink().'"><div>'.$row->getTitle().'</div><span>'.$row->getBasedLeague().'</span></a>';
                            echo '</div>';
                            echo '<div class="jsPrivMainBlock" data-league="'.$invitedLeague[$intA]->ID.'">';
                            echo '<div class="jsPrivUsers"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-users" aria-hidden="true"></i>'.$row->getUsersCount().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["join"])?$actions["join"]:"").'</div>';
                            echo '</div></div>';
                            echo '<div class="jsPrivOwner"><div class="row">';
                            echo '<div class="col-xs-7"><i class="fa fa-address-book-o" aria-hidden="true"></i>'.$row->getOwner().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["reject"])?$actions["reject"]:"").'</div>';
                            echo '</div></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>

                        <?php
                        for($intA=0;$intA<count($activeLeagues);$intA++){
                            $row = new jsPredictionLeagueRow($activeLeagues[$intA]->ID);
                            $actions = $row->getActionsList();

                            echo '<div class="col-lg-4 col-sm-6 col-xs-12">';
                            echo '<div class="table-responsive">';
                            echo '<div class="jsPrivHeaderBlock">';
                            echo '<a href="'.$row->getLink().'"><div>'.$row->getTitle().'</div><span>'.$row->getBasedLeague().'</span></a>';
                            echo '</div>';
                            echo '<div class="jsPrivMainBlock" data-league="'.$activeLeagues[$intA]->ID.'">';
                            echo '<div class="jsPrivUsers"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-users" aria-hidden="true"></i>'.$row->getUsersCount().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["invite"])?$actions["invite"]:"").(isset($actions["leave"])?$actions["leave"]:"").'</div>';
                            echo '</div></div>';
                            echo '<div class="jsPrivOwner"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-owner" aria-hidden="true"></i>'.$row->getOwner().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["edit"])?$actions["edit"]:"").'</div>';
                            echo '</div></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
<div style="display:none;" id="dialogJSnewLeague" title="<?php echo JText::_('Create New League');?>">

    <form id="formJSPNewLeague" name="formJSPNewLeague">
        <fieldset>
            <label for="leaguename"><?php echo JText::_('League name');?></label>
            <input type="text" name="leaguename" id="leaguename" value="" class="text ui-widget-content ui-corner-all">
            <label for="base_league"><?php echo JText::_('Based on Competition');?></label>
            <?php
            $list = jsPredictionHelper::getActiveMainLeaguesList();

            ?>
            <select name="base_league" id="base_league">
                <?php
                for($intA=0;$intA<count($list);$intA++){
                    echo '<option value="'.$list[$intA]->id.'">'.$list[$intA]->name.'</option>';
                }
                ?>
            </select>
            <label for="import_from"><?php echo JText::_('Import participants from');?></label>
            <?php
            $list = jsPredictionMyLeagues::getMyLeagues();
            ?>
            <select name="import_from" id="import_from">
                <?php
                echo '<option value="0">'.JText::_('Select').'</option>';
                for($intA=0;$intA<count($list);$intA++){
                    echo '<option value="'.$list[$intA]->id.'">'.$list[$intA]->name.'</option>';
                }
                ?>
            </select>

        </fieldset>
    </form>
</div>
<div style="display:none;" id="dialogJSnewLeagueParticipants" title="<?php echo JText::_('Manage participants');?>">

    <form id="formJSPNewLeaguePartic" name="formJSPNewLeaguePartic">
        <fieldset>
            <div class="jspmodalFields">
                <div class="jspsocial-media">
                    <div class="jspmodalHeader">
                        <?php echo JText::_('Invite options');?>
                    </div>
                    <div class="jspmodalMainBlock clearfix">
                        <div>
                            <a class="jsp-btn jsp-default" id="raadEmaillink" href="mailto:user@example.com?subject=Subject&amp;body=Body">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                <span class="social-media-icon__caption"><?php echo JText::_('Send invite by your email client');?></span>
                            </a>
                        </div>
                        <div>
                            <div class="jsprDivCopied"><?php echo JText::_('Copied');?></div>
                            <div id="jsprInviteLink" class="jsp-btn jsp-primary">
                                <?php echo JText::_('Copy invite link');?>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="jspmodalHeader">
                        <?php echo JText::_('Invite site users');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <?php echo '<select multiple name="user_invited[]" class="jswf-data-users-ajax" data-placeholder="'.JText::_('Invite users').'" ></select>'; ?>
                    </div>
                </div>
                <div class="jspinvitebyemail">
                    <div class="jspmodalHeader">
                        <?php echo JText::_('Send invite email via our site');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <table class="tblInviteEmail">
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=JText::_('Name');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=JText::_('Email');?>" /></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=JText::_('Name');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=JText::_('Email');?>" /></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=JText::_('Name');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=JText::_('Email');?>" /></td>
                            </tr>
                        </table>
                        <input type="button" class="btnAddEmails" value="+" />
                    </div>
                </div>
                <div>
                    <div class="jspmodalHeader">
                        <?php echo JText::_('Participants');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <div id="JSparticList"></div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div style="display:none;" id="dialogJSeditLeague" title="<?php echo JText::_('Edit league');?>">
    <form id="formJSPEditLeague" name="formJSPEditLeague">
        <label for="leaguename"><?php echo JText::_('League name');?></label>
        <input type="text" name="edit_leaguename" id="edit_leaguename" value="" class="text ui-widget-content ui-corner-all">
        <label for="import_from"><?php echo JText::_('Import participants from');?></label>
        <?php
        $list = jsPredictionMyLeagues::getMyLeagues();
        ?>
        <select name="import_from_edit" id="import_from_edit">
            <?php
            echo '<option value="0">'.JText::_('Select').'</option>';
            for($intA=0;$intA<count($list);$intA++){
                echo '<option value="'.$list[$intA]->id.'">'.$list[$intA]->name.'</option>';
            }
            ?>
        </select>
    </form>
</div>
<?php
$doc = JFactory::getDocument();
$doc->addScript('https://code.jquery.com/ui/1.12.1/jquery-ui.js');
$doc->addCustomTag('<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />');

classJsportAddtag::addJS(JS_LIVE_ASSETS.'js/jsprediction.js');
classJsportAddtag::addCSS(JS_LIVE_ASSETS.'css/prediction.css');