jQuery(document).ready(function(){
    jQuery('#jspRoundSave').on('click',function(){
        jQuery('#jspRound').submit();
    });
    
    if(!jQuery("input[name^='pred_home']").length){
        jQuery('#jspRoundSave').hide();
    }

    jQuery( function() {
        var ajaxurl = jsLiveSiteLink + "index.php?option=com_joomsport&tmpl=component&no_html=1&controller=prediction";

        let leagueID = 0;
        function manageParticipants() {
            jQuery("#JSparticList").html("");
            saveLeague();

        }
        function updateLeague(){
            if(jQuery('#edit_leaguename').val() != ''){
                var data = {
                    'task': 'jspred_private_update_league',
                    'leagueid': leagueID,
                    'league_name': jQuery('#edit_leaguename').val(),
                    'import_from' : jQuery('select[name="import_from_edit"]').val()
                };

                    jQuery.post(ajaxurl, data, function(response) {
                    var obj = JSON.parse(response);
                    if(obj && obj.error == 0){
                        var parent = jQuery("div[data-league='"+leagueID+"']").closest(".table-responsive");
                        var leagueTitile = parent.find(".jsPrivHeaderBlock div");

                        leagueTitile.html(obj.title);
                        jQuery('#edit_leaguename').val("");

                        parent.find(".plUserCntTd").html(obj.users);

                        dialogCNLEdit.dialog( "close" );
                    }else{
                        alert(obj.msg);
                    }
                    //jQuery(":button:contains('Save')").prop("disabled", false).removeClass("ui-state-disabled");
                    jQuery(".ui-dialog-buttonset").find("button").prop("disabled", false).removeClass("ui-state-disabled");
                });
            }else{
                alert(Joomla.JText._('JSPL_FE_LEAGUE_NOT_SPECIFIED'));
                jQuery(".ui-dialog-buttonset").find("button").prop("disabled", false).removeClass("ui-state-disabled");
                // jQuery(":button:contains('Save')").prop("disabled", false).removeClass("ui-state-disabled");
            }

        }
        function sendInvites(){


            //return '';
            var data = {
                'task': 'jspred_private_league_invite',
                'form': jQuery('#formJSPNewLeaguePartic').serialize(),
                'leagueid': leagueID,
            };

            jQuery.post(ajaxurl, data, function(response) {
                var obj = JSON.parse(response);
                if(obj && obj.error == 0){
                    jQuery("#formJSPNewLeaguePartic")[0].reset();
                    jQuery('.jswf-data-users-ajax').trigger('change.select2');
                    dialogCNLPartic.dialog( "close" );
                }else{
                    alert(obj.msg);
                }
                jQuery(".ui-dialog-buttonset").find("button").prop("disabled", false).removeClass("ui-state-disabled");
            });
        }
        function saveLeague(){
            var data = {
                'task': 'jspred_private_league_add',
                'league_name': jQuery('#leaguename').val(),
                'base_league': jQuery('select[name="base_league"]').val(),
                'import_from': jQuery('select[name="import_from"]').val(),
                'leagueid': leagueID
            };

            jQuery.post(ajaxurl, data, function(response) {
                var obj = JSON.parse(response);
                if(obj && obj.error == 0){
                    jQuery('<span class="spanInviteCopy">'+obj.invite+'</span>').appendTo("#jsprInviteLink");
                    if(!leagueID){
                        var leaguebody = jQuery("#activeLeagues");
                        leaguebody.append('<div class="col-lg-4 col-sm-6 col-xs-12"><div class="table-responsive"><div class="jsPrivHeaderBlock"><a href="'+obj.title_url+'"><div>'+obj.title+'</div><span>'+obj.based+'</span></a></div><div class="jsPrivMainBlock" data-league="'+obj.leagueid+'"><div class="jsPrivUsers"><div class="row"><div class="col-xs-7 plUserCntTd"><i class="js-users" aria-hidden="true"></i>'+obj.users+'</div><div class="col-xs-5 jsright">'+obj.invitation+'</div></div></div><div class="jsPrivOwner"><div class="row"><div class="col-xs-7"><i class="js-owner" aria-hidden="true"></i>'+obj.owner+'</div><div class="col-xs-5 jsright">'+obj.edit+'</div></div></div></div></div></div>');
                    }
                    leagueID = obj.leagueid;
                    if(obj.partic){
                        for(var i=0; i<obj.partic.length; i++){

                            jQuery("#JSparticList").append(particTemplate(obj.partic[i]));
                        }
                    }
                    jQuery("#raadFBlink").attr("href",obj.fblink);
                    jQuery("#raadWAlink").attr("href",obj.walink);
                    jQuery("#raadTWlink").attr("href",obj.twlink);
                    jQuery("#raadEmaillink").attr("href",obj.emaillink);

                    dialogCNLPartic.dialog( "open" );
                    dialogCNL.dialog( "close" );
                }else{
                    alert(obj.msg);
                }
                jQuery(".ui-dialog-buttonset").find("button").prop("disabled", false).removeClass("ui-state-disabled");
                //jQuery(":button:contains('Next')").prop("disabled", false).removeClass("ui-state-disabled");

            });
        }

        jQuery('body').on('blur','.tblInviteEmail input[type=email]', function () {
            let email = jQuery(this).val();

            if (email.length > 0
                && (email.match(/.+?\@.+/g) || []).length !== 1) {

                alert('Incorrect email');
            }
        });

        dialogCNL = jQuery( "#dialogJSnewLeague" ).dialog({
            autoOpen: false,
            height: 500,
            width: 650,
            modal: true,
            buttons: [
                {
                    text: "NEXT",
                    class: "jsp-btn jsp-success",
                    click: function (evt) {
                        var buttonDomElement = evt.target;
                        jQuery(buttonDomElement).prop('disabled', true).addClass("ui-state-disabled");
                        manageParticipants();
                    }
                }
            ],
            close: function() {
                jQuery("#formJSPNewLeague")[0].reset();
                jQuery("#plTabsContainerdiv").show();
                var defleague = jQuery('input[name="defleague"]').val();
                jQuery("select [name='base_league']").filter('[value='+defleague+']').attr('selected', true);
                jQuery("select [name='base_league']").trigger('change.select2');

                //form[ 0 ].reset();
                //allFields.removeClass( "ui-state-error" );
            }
        });

        dialogCNLPartic = jQuery( "#dialogJSnewLeagueParticipants" ).dialog({
            autoOpen: false,
            height: 500,
            width: 650,
            modal: true,
            buttons: [
                {
                    text: "Send invites",
                    class: "jsp-btn jsp-success",
                    click: function (evt) {
                        var buttonDomElement = evt.target;
                        jQuery(buttonDomElement).prop('disabled', true).addClass("ui-state-disabled");
                        //jQuery(":button:contains('Send Invites')").prop("disabled", true).addClass("ui-state-disabled");
                        sendInvites();
                    }
                }
            ],
            close: function() {
                //form[ 0 ].reset();
                //allFields.removeClass( "ui-state-error" );
            }
        });

        dialogCNLEdit = jQuery( "#dialogJSeditLeague" ).dialog({
            autoOpen: false,
            height: 500,
            width: 650,
            modal: true,
            buttons: [
                {
                    text: "Delete league",
                    class: "jsp-btn jsp-danger",
                    click: function() {
                        if(confirm(Joomla.JText._('JSPL_FE_ALERT_DELETE').replace("%s",jQuery("#edit_leaguename").val()))){
                            var data = {
                                'task': 'jspred_private_remove_league',
                                'leagueid': leagueID
                            };

                            jQuery.post(ajaxurl, data, function(response) {
                                var obj = JSON.parse(response);
                                if(obj && obj.error == 0){
                                    var parent = jQuery("div[data-league='"+leagueID+"']").closest(".table-responsive");

                                    parent.remove();

                                }else{
                                    alert(obj.msg);
                                }
                                dialogCNLEdit.dialog( "close" );
                            });
                        }
                    }
                },
                {
                    text: "Save",
                    class: "jsp-btn jsp-success",
                    click: function (evt) {
                        var buttonDomElement = evt.target;
                        jQuery(buttonDomElement).prop('disabled', true).addClass("ui-state-disabled");
                        //jQuery(":button:contains('Save')").prop("disabled", true).addClass("ui-state-disabled");
                        updateLeague();
                    }
                }
            ],
            close: function() {
                //form[ 0 ].reset();
                //allFields.removeClass( "ui-state-error" );
            }
        });

        jQuery('.ui-dialog').addClass('jsp-dialog');
        jQuery('.ui-dialog-titlebar').addClass('jsp-dialog-titlebar');
        jQuery('.ui-dialog-titlebar-close').html('<i class="fa fa-times" aria-hidden="true"></i>').addClass('jsp-btn jsp-default');
        jQuery('.ui-dialog-content').addClass('jsp-dialog-content');
        jQuery('.ui-dialog-buttonpane').addClass('jsp-dialog-buttonpane');


        jQuery( "#jspNewLeague" ).on( "click", function() {
            leagueID = 0;
            dialogCNL.dialog( "open" );
        });

        function particTemplate(partic){
            var html = '<tr class="particDivName" data-partic="'+partic.userID+'">';

            html += '<td>'+partic.user_login+'</td>';
            html += '<td>'+statusPartic(partic.confirmed)+'</td>';
            html += '<tr>';

            return html;
        }
        function statusPartic(status){
            switch (status) {
                case '1':
                    return '<input type="button" class="jsBtnRemPart" value="Remove" />';
                    break;
                case '0':
                    return '<span class="particPending">'+Joomla.JText._("JSPL_FE_PENDING")+'</span>';
                    break;
                case '2':
                    return '<span class="particRejected">'+Joomla.JText._("JSPL_FE_REJECTED")+'</span>';
                    break;
                default:
                    return '';
            }
            return '';
        }
        jQuery("body").on("click",".jsBtnRemPart",function(){
            var particA = jQuery(this).closest("tr");

            var partic = particA.attr("data-partic");
            var pname = particA.find("td").html();
            if(confirm(Joomla.JText._('JSPL_FE_ALERT_DELETE_PARTIC').replace("%s", pname))){


                var data = {
                    'task': 'jspred_private_remove_part',
                    'pid': partic,
                    'leagueid': leagueID
                };

                jQuery.post(ajaxurl, data, function(response) {
                    var obj = JSON.parse(response);
                    if(obj && obj.error == 0){
                        particA.remove();
                        var tr = jQuery("td[data-league='"+leagueID+"']").closest("tr");

                        tr.find(".plUserCntTd").html(obj.users);

                    }else{
                        alert(obj.msg);
                    }

                });
            }

        });
        jQuery("body").on("click","#jsprInviteLink",function(){
            copyToClipboard(jQuery(this).children('span'));
        });
        function copyToClipboard(element) {
            var $temp = jQuery("<input>");
            jQuery("#formJSPNewLeaguePartic").append($temp);
            $temp.val(jQuery(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
            jQuery('.jsprDivCopied').show();
            jQuery('.jsprDivCopied').fadeOut(800);
        }

        jQuery("body").on("click",".jpBtnJoin",function(){
            var tdAction = jQuery(this).closest(".jsPrivMainBlock");
            var league = tdAction.attr("data-league");

            var data = {
                'task': 'jspred_private_join',
                'leagueid': league
            };

            jQuery.post(ajaxurl, data, function(response) {
                var obj = JSON.parse(response);
                if(obj && obj.error == 0){
                    tdAction.html('<div class="jsPrivUsers"><div class="row"><div class="col-xs-7 plUserCntTd"><i class="js-users" aria-hidden="true"></i>'+obj.users+'</div><div class="col-xs-5 jsright">'+obj.leave+'</div></div></div><div class="jsPrivOwner"><div class="row"><div class="col-xs-7"><i class="js-owner" aria-hidden="true"></i>'+obj.owner+'</div></div></div>');
                    var parent = jQuery("div[data-league='"+leagueID+"']").closest(".table-responsive");

                    parent.find(".plUserCntTd").html(obj.users);
                }else{
                    alert(obj.msg);
                }

            });

        });
        jQuery("body").on("click",".jpBtnReject",function(){
            var tdAction = jQuery(this).closest(".jsPrivMainBlock");
            var league = tdAction.attr("data-league");
            var data = {
                'task': 'jspred_private_reject',
                'leagueid': league
            };

            jQuery.post(ajaxurl, data, function(response) {
                var obj = JSON.parse(response);
                if(obj && obj.error == 0){
                    var parent = tdAction.closest(".table-responsive");
                    parent.remove();
                }else{
                    alert(obj.msg);
                }

            });
        });
        jQuery("body").on("click",".jpBtnInvite",function(){
            var tdAction = jQuery(this).closest(".jsPrivMainBlock");
            var league = tdAction.attr("data-league");

            var data = {
                'task': 'jspred_private_load_league',
                'leagueid': league
            };
            // jQuery("#jsprInviteLink").html("");
            jQuery("#JSparticList").html("");
            jQuery.post(ajaxurl, data, function(response) {
                var obj = JSON.parse(response);
                if(obj && obj.error == 0){
                    jQuery('<span class="spanInviteCopy">'+obj.invite+'</span>').appendTo("#jsprInviteLink");
                    leagueID = obj.leagueid;
                    if(obj.partic){
                        for(var i=0; i<obj.partic.length; i++){

                            jQuery("#JSparticList").append(particTemplate(obj.partic[i]));
                        }
                    }

                    jQuery("#raadFBlink").attr("href",obj.fblink);
                    jQuery("#raadWAlink").attr("href",obj.walink);
                    jQuery("#raadTWlink").attr("href",obj.twlink);
                    jQuery("#raadEmaillink").attr("href",obj.emaillink);
                    dialogCNLPartic.dialog( "open" );
                    dialogCNL.dialog( "close" );
                }else{
                    alert(obj.msg);
                }

            });


        });
        jQuery("body").on("click",".jpBtnLeave",function(){
            if(confirm(Joomla.JText._('JSPL_FE_ALERT_LEAVE'))){
                var tdAction = jQuery(this).closest(".jsPrivMainBlock");
                var league = tdAction.attr("data-league");
                var data = {
                    'task': 'jspred_private_leave',
                    'leagueid': league
                };

                jQuery.post(ajaxurl, data, function(response) {
                    var obj = JSON.parse(response);
                    if(obj && obj.error == 0){
                        var parent = tdAction.closest(".table-responsive");
                        parent.remove();
                    }else{
                        alert(obj.msg);
                    }

                });
            }

        });
        jQuery("body").on("click",".jpBtnEdit",function(){
            var tdAction = jQuery(this).closest(".jsPrivMainBlock");
            var parent = jQuery(this).closest(".table-responsive");
            var leagueTitile = parent.find(".jsPrivHeaderBlock div");

            leagueID = tdAction.attr("data-league");
            jQuery("#edit_leaguename").val(leagueTitile.html());
            jQuery("select [name='import_from_edit']").val("0");
            dialogCNLEdit.dialog( "open" );

        });
        jQuery("body").on("click",".jpBtnDelete",function(){
            var tdAction = jQuery(this).closest(".jsPrivMainBlock");
            var parent = jQuery(this).closest(".table-responsive");
            var leagueTitile = parent.find(".jsPrivHeaderBlock div");

            leagueID = tdAction.attr("data-league");

            if(confirm(Joomla.JText._('JSPL_FE_ALERT_DELETE').replace("%", leagueTitile.html()))){


                var data = {
                    'task': 'jspred_private_remove_league',
                    'leagueid': leagueID
                };

                jQuery.post(ajaxurl, data, function(response) {
                    var obj = JSON.parse(response);
                    if(obj && obj.error == 0){
                        var parent = tdAction.closest(".table-responsive");
                        parent.remove();

                    }else{
                        alert(obj.msg);
                    }

                });
            }
        });



        function beforeLoading(el){
            //el.fadeTo( "slow", 0.33 );
        }
        function afterLoading(el){

        }

        jQuery('.jswf-data-users-ajax').select2({
            ajax: {
                url: ajaxurl,
                type: 'POST',
                data : function (params) {
                    return {
                        'task': 'jspred_private_users',
                        leagueid: leagueID,
                        q: params.term, // search term
                        page: params.page
                    };
                },
                delay: 250,
                dataType: 'json'
                // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
            }
        });

        jQuery(".btnAddEmails").on("click", function(){
            jQuery(".tblInviteEmail").append('<tr><td><input type="text" name="invbyemail_name[]" placeholder="Name" /></td>\n' +
                '                                <td><input type="email" name="invbyemail_email[]" placeholder="Email" /></td></tr>');
        });

    });
});