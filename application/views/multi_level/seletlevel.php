<?php
/**
 * This view displays the organization tree and allows to attach and detach employees from sub-entities.
 * @copyright  Copyright (c) 2014-2019 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */
?>
<div class="row-fluid">
    <div class="span12">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="employees" width="100%">
    <thead>
        <tr>
	<!-- the first heading should be changed according to language -->
	   <th></th>
            <th><?php echo "Level";?></th>
            <th><?php echo lang('users_employees_thead_id');?></th>
            <th><?php echo lang('users_employees_thead_firstname');?></th>
            <th><?php echo lang('users_employees_thead_lastname');?></th>
            <th><?php echo lang('users_employees_thead_email');?></th>
        </tr>
    </thead>
    <tbody id="employeeTbody">


    </tbody>
</table>
</div>
</div>
<!---->

   <hr style="border-top: 1px solid #000000" />
<div class="row-fluid">
    <div class="span4">
        <div class="input-append">
            <input type="text" class="input-medium" placeholder="<?php echo lang('organization_index_field_search_placeholder');?>" id="txtSearch" />
            <button id="cmdClearSearch" class="btn btn-primary"><i class="mdi mdi-close"></i></button>
            <button id="cmdSearch" class="btn btn-primary"><i class="mdi mdi-magnify"></i>&nbsp;<?php echo lang('organization_index_button_search');?></button>
        </div>
        <div style="text-align: left;" id="organization"></div>
    </div>
    <div class="span8">
        <h4><?php echo lang('organization_index_title_employees');?></h4>
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="collaborators" width="100%">
            <thead>
		<tr>
		   <th></th>
                    <th><?php echo lang('organization_index_thead_id');?></th>
                    <th><?php echo lang('organization_index_thead_firstname');?></th>
                    <th><?php echo lang('organization_index_thead_lastname');?></th>
                    <th><?php echo lang('organization_index_thead_email');?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
	<br />


     </div>
</div>
<div id="frmError" class="modal hide fade">
    <div class="modal-header">
        <a href="#" onclick="$('#frmError').modal('hide');" class="close">&times;</a>
         <h3><?php echo lang('organization_index_popup_error_title');?></h3>
    </div>
    <div class="modal-body" id="lblError"></div>
    <div class="modal-footer">
        <a href="#" onclick="$('#frmError').modal('hide');" class="btn"><?php echo lang('organization_index_popup_error_button_ok');?></a>
    </div>
</div>

<div class="modal hide" id="frmModalAjaxWait" data-backdrop="static" data-keyboard="false">
    <div class="modal-header">
        <h1><?php echo lang('global_msg_wait');?></h1>
    </div>
    <div class="modal-body">
        <img src="<?php echo base_url();?>assets/images/loading.gif"  align="middle">
    </div>
 </div>

<link href="<?php echo base_url();?>assets/datatable/DataTables-1.10.11/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="<?php echo base_url();?>assets/datatable/Select-1.1.2/css/select.dataTables.min.css" rel="stylesheet">
`
<link rel="stylesheet" href='<?php echo base_url(); ?>assets/jsTree-3.3.7/themes/default/style.css' type="text/css" media="screen, projection" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/jsTree-3.3.7/jstree.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/datatable/DataTables-1.10.11/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/datatable/Select-1.1.2/js/dataTables.select.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/bootbox.min.js"></script>
<!--
<script type="text/javascript">
-->
<script type="text/javascript">
var eTable;
//$(document).ready(function() {
    //Transform the HTML table in a fancy datatable
    eTable=$('#employees').DataTable({
    columns: [
	        {
                "className":      'removeLevel',
                "orderable":      false,
                "data":           null,
                "defaultContent": '<a href="#" id="removeLevelLink"><i class="mdi  mdi-close-circle nolink" title="Remove Level"></i></a>'
                },
		{ data: "level"},
                { data: "id" },
                { data: "firstname" },
                { data: "lastname" },
                { data: "email" }
	],
	ordering:false,
        searching :false, //to save space
       // select: 'single',
        pageLength: 5,
        language: {
            decimal:            "<?php echo lang('datatable_sInfoThousands');?>",
            processing:       "<?php echo lang('datatable_sProcessing');?>",
            search:              "<?php echo lang('datatable_sSearch');?>",
            lengthMenu:     "<?php echo lang('datatable_sLengthMenu');?>",
            info:                   "<?php echo lang('datatable_sInfo');?>",
            infoEmpty:          "<?php echo lang('datatable_sInfoEmpty');?>",
            infoFiltered:       "<?php echo lang('datatable_sInfoFiltered');?>",
            infoPostFix:        "<?php echo lang('datatable_sInfoPostFix');?>",
            loadingRecords: "<?php echo lang('datatable_sLoadingRecords');?>",
            zeroRecords:    "<?php echo lang('datatable_sZeroRecords');?>",
            emptyTable:     "<?php echo lang('datatable_sEmptyTable');?>",
            paginate: {
                first:          "<?php echo lang('datatable_sFirst');?>",
                previous:   "<?php echo lang('datatable_sPrevious');?>",
                next:           "<?php echo lang('datatable_sNext');?>",
                last:           "<?php echo lang('datatable_sLast');?>"
            },

  "aoColumnDefs": [
        { "bSortable": false, "aTargets": [ 0, 1, 2, 3 ] }] 

       }
    })
    //Hide pagination select box in order to save space
    $('.dataTables_length').css("display", "none");
//});

              
    //In order to manipulate datable object
    var oTable;
    //Mutex to prevent rename the root node
    var createMtx = false;

   $(function () {
        //Global Ajax error handling mainly used for session expiration
        $( document ).ajaxError(function(event, jqXHR, settings, errorThrown) {
            $('#frmModalAjaxWait').modal('hide');
            if (jqXHR.status == 401) {
                bootbox.alert("<?php echo lang('global_ajax_timeout');?>", function() {
                    //After the login page, we'll be redirected to the current page
                   location.reload();
                });
            } else { //Oups
                //Deal with mysterious error triggered by jsTree
                if (jqXHR.status != 200) {
                    bootbox.alert("<?php echo lang('global_ajax_error');?>");
                }
            }
          });

        //On confirm the deletion of the node, launch heavy cascade deletion
        $("#lnkDeleteEntity").click(function() {
            $.ajax({
                type: "GET",
                url: "<?php echo base_url(); ?>organization/delete",
                data: { 'entity': $('#frmConfirmDelete').data('id') }
              })
              .done(function(msg) {
                $("#organization").jstree("select_node", "0");
                $("#organization").jstree("refresh");
                $("#frmConfirmDelete").modal('hide');
              });
        });
       
	//Search in the treeview
        $("#cmdSearch").click(function () {
            $("#organization").jstree("search", $("#txtSearch").val(), true, true);
        });
        $("#txtSearch").keyup(function(e) {
            if (e.keyCode == 13) { $("#organization").jstree("search", $("#txtSearch").val(), true, true); }   // enter key
        });

        //Clear the Search option in the treeview
        $("#cmdClearSearch").click(function () {
            $("#organization").jstree("clear_search");
        });
        $(document).keyup(function(e) {
            if (e.keyCode == 27) { $("#organization").jstree("clear_search"); }   // escape key
        });
    
	$('#collaborators').on('init.dt',function(e,settings,json){
	    console.log('sdvs');
    });

        //Transform the HTML table in a fancy datatable
        oTable = $('#collaborators').DataTable({
	columns: [
	        {
                "className":      'addLevel',
                "orderable":      false,
                "data":           null,
                "defaultContent": '<a href="#" id="addLevelLink"><i class="mdi mdi-plus-circle nolink" title="Add Level"></i></a>'
                },

                { data: "id" },
                { data: "firstname" },
                { data: "lastname" },
                { data: "email" }
            ],
//	"initComplete": function(settings, json) {checkid();  },
	    // select: 'single',
            language: {
                decimal:            "<?php echo lang('datatable_sInfoThousands');?>",
                processing:       "<?php echo lang('datatable_sProcessing');?>",
                search:              "<?php echo lang('datatable_sSearch');?>",
                lengthMenu:     "<?php echo lang('datatable_sLengthMenu');?>",
                info:                   "<?php echo lang('datatable_sInfo');?>",
                infoEmpty:          "<?php echo lang('datatable_sInfoEmpty');?>",
                infoFiltered:       "<?php echo lang('datatable_sInfoFiltered');?>",
                infoPostFix:        "<?php echo lang('datatable_sInfoPostFix');?>",
                loadingRecords: "<?php echo lang('datatable_sLoadingRecords');?>",
                zeroRecords:    "<?php echo lang('datatable_sZeroRecords');?>",
                emptyTable:     "<?php echo lang('datatable_sEmptyTable');?>",
                paginate: {
                    first:          "<?php echo lang('datatable_sFirst');?>",
                    previous:   "<?php echo lang('datatable_sPrevious');?>",
                    next:           "<?php echo lang('datatable_sNext');?>",
                    last:           "<?php echo lang('datatable_sLast');?>"
                },
                aria: {
                    sortAscending:  "<?php echo lang('datatable_sSortAscending');?>",
                    sortDescending: "<?php echo lang('datatable_sSortDescending');?>"
                }
            }
        });

        //Initialize the tree of the organization
        $('#organization').jstree({
            contextmenu: {
                items: function(n) {
                    var tmp = $.jstree.defaults.contextmenu.items();
                    tmp.create.label = '<?php echo lang('treeview_context_menu_create');?>';
                    tmp.rename.label = '<?php echo lang('treeview_context_menu_rename');?>';
                    tmp.remove.label = '<?php echo lang('treeview_context_menu_remove');?>';
                    tmp.ccp.label = '<?php echo lang('treeview_context_menu_edit');?>';
                    tmp.ccp.submenu.copy.label = '<?php echo lang('treeview_context_menu_copy');?>';
                    tmp.ccp.submenu.cut.label = '<?php echo lang('treeview_context_menu_cut');?>';
                    tmp.ccp.submenu.paste.label = '<?php echo lang('treeview_context_menu_paste');?>';
                    return tmp;
                }
            },
            rules: {
                deletable  : [ "folder" ],
                creatable  : [ "folder" ],
                draggable  : [ "folder" ],
                dragrules  : [ "folder * folder", "folder inside root" ],
                renameable : "all"
              },
            core: {
              multiple : false,
              data: {
                url: function (node) {
                  return node.id === '#' ?
                    '<?php echo base_url(); ?>organization/root' :
                    '<?php echo base_url(); ?>organization/children';
                },
                data: function (node) {
                  return { 'id' : node.id };
                }
              },
              check_callback : true
            },
            plugins: ["contextmenu", "dnd", "search", "state", "sort", "unique"]
        })
        .on('delete_node.jstree', function (e, data) {
            var id = data.node.id;
            if (id == 0) {
                $("#lblError").text("<?php echo lang('organization_index_error_msg_delete_root');?>");
                $("#frmError").modal('show');
                $("#organization").jstree("refresh");
            } else {
                $('#frmConfirmDelete').data('id', id).modal('show');
            }
        })
        .on('create_node.jstree', function (e, data) {
            createMtx = true;
            bootbox.prompt("<?php echo lang('organization_index_prompt_entity_name');?>",
                "<?php echo lang('organization_index_popup_node_button_cancel');?>",
                "<?php echo lang('organization_index_popup_node_button_ok');?>", function(result) {
                if ((result === null) || (result == '')) {  //NULL or empty string has no effect
                    data.instance.refresh();
                } else {
                    $.get('organization/create', { 'id' : data.node.parent, 'position' : data.position, 'text' : result })
                    .done(function (d) {
                        data.instance.set_id(data.node, d.id);
                        createMtx = false;
                    })
                    .fail(function() {
                        data.instance.refresh();
                        createMtx = false;
                    });
                }
              });
        })
        .on('rename_node.jstree', function(e, data) {
            if (!createMtx) {
                $.get('organization/rename', {'id': data.node.id, 'text': data.text})
                    .fail(function() {
                        data.instance.refresh();
                    });
            }
        })
        .on('move_node.jstree', function(e, data) {
            e.preventDefault();
            $.get('organization/move', {'id': data.node.id, 'parent': data.parent, 'position': data.position})
                .fail(function() {
                    data.instance.refresh();
                });
        })
        .on('copy_node.jstree', function(e, data) {
            e.preventDefault();
            $.get('organization/copy', {'id': data.original.id, 'parent': data.parent, 'position': data.position})
                .always(function() {
                    data.instance.refresh();
                });
        })
        .on('changed.jstree', function(e, data) {
            if (data && data.selected && data.selected.length) {
                $('#frmModalAjaxWait').modal('show');
                var isTableLoaded = false;
                oTable.ajax.url("<?php echo base_url(); ?>organization/employees?id=" + data.selected.join(':'))
                    .load(function() {
			    isTableLoaded = true;
			    checkid(); //this function remove record already added in eTable that show manager level.
                        }, true);
                $.ajax({
                    type: "GET",
                    url: "<?php echo base_url(); ?>organization/getsupervisor",
                        data: { 'entity': data.selected.join(':') }
                      })
                    .done(function(data) {
                        //Update field with the name of employee (the supervisor)
                        if (data != null && typeof data === 'object') {
                            $('#txtSupervisor').val(data.username);
                        } else {
                            $('#txtSupervisor').val("");
	    }
                        $.when(isTableLoaded, isTableLoaded).done(function() {
                            $("#frmModalAjaxWait").modal('hide');
                        });
                  });
            }
        });
   });
    
    //Added by Shiv Charan
    $('#employees tbody').on('click', 'td.removeLevel', function () {
        var tr = $(this).closest('tr');
	var row = eTable.row( tr ).data();
	 eTable.row(tr).remove().draw(false);
	 oTable.row.add(row).draw(false);
	 eTable.column(1).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );

    } );
    
    
 //added by Shiv charan    
    $('#collaborators tbody').on('click', 'td.addLevel', function () {
        var tr = $(this).closest('tr');
	var row = oTable.row( tr ).data();
	 oTable.row(tr).remove().draw(false);
         row.level=null;
         eTable.row.add(row).draw(false);
	 eTable.column(1).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    });
//added by Shiv charan
    /* This function deal with removing record of employee from oTable (having id collaborators)
	    * that are already added in eTable (having id employee and show level of managers)
	    * it prevent duplicate entry in eTable.
	    * */
    function checkid(){
	     for(var j=0;j< oTable.rows().data().length;j++){

	    var oTableRow=oTable.row(j);

	for(var i=0;i< eTable.rows().data().length;i++){
		if(eTable.row(i).data()['id'] == oTableRow.data()['id']){
		console.log(oTableRow.data()['id']);
			oTableRow.remove();
			j--;	//As we remove row so datatable will decrease index of other row by one to hendel it we need to decrease our counter.	
			break;
		}
	}
	}
	oTable.draw(false);
	   } 
</script>
