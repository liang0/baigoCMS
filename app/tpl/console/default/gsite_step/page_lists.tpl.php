<?php $cfg = array(
    'title'          => $lang->get('Gathering', 'console.common') . ' &raquo; ' . $lang->get('Gathering site', 'console.common'),
    'menu_active'    => 'gather',
    'sub_active'     => 'gsite',
    'help'           => 'step_list#page_list',
    'baigoValidate' => 'true',
    'baigoSubmit'    => 'true',
    'prism'          => 'true',
    'pathInclude'    => $path_tpl . 'include' . DS,
);

include($cfg['pathInclude'] . 'console_head' . GK_EXT_TPL);
include($cfg['pathInclude'] . 'gsite_head' . GK_EXT_TPL); ?>

    <form name="gsite_form" id="gsite_form" action="<?php echo $route_console; ?>gsite_step/page-lists-submit/">
        <input type="hidden" name="__token__" value="<?php echo $token; ?>">
        <input type="hidden" name="gsite_id" id="gsite_id" value="<?php echo $gsiteRow['gsite_id']; ?>">

        <div class="row">
            <div class="col-xl-9">
                <div class="card mb-3">
                    <div class="card-header"><?php echo $lang->get('Paging list settings'); ?></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><?php echo $lang->get('List selector'); ?> <span class="text-danger">*</span></label>
                            <input type="text" name="gsite_page_list_selector" id="gsite_page_list_selector" value="<?php echo $gsiteRow['gsite_page_list_selector']; ?>" class="form-control">
                            <small class="form-text" id="msg_gsite_page_list_selector"><?php echo $lang->get('Please use the jQuery selector, which must end with the tag selector <code>a</code> and the system will automatically read the <code>href</code> attribute'); ?></small>
                        </div>

                        <div class="bg-validate-box"></div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><?php echo $lang->get('Save'); ?></button>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><?php echo $lang->get('Preview'); ?></div>
                    <div id="gsite_preview">
                        <div class="loading p-3">
                            <h4 class="text-info">
                                <span class="spinner-grow"></span>
                                Loading...
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card my-3">
                    <div class="card-header"><?php echo $lang->get('Source code'); ?></div>
                    <div id="gsite_source">
                        <div class="loading p-3">
                            <h4 class="text-info">
                                <span class="spinner-grow"></span>
                                Loading...
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <?php include($cfg['pathInclude'] . 'gsite_side' . GK_EXT_TPL); ?>
        </div>
    </form>

<?php include($cfg['pathInclude'] . 'gsite_foot' . GK_EXT_TPL);
include($cfg['pathInclude'] . 'console_foot' . GK_EXT_TPL); ?>

    <script type="text/javascript">
    var opts_validate_form = {
        rules: {
            gsite_page_list_selector: {
                length: '1,100'
            }
        },
        attr_names: {
            gsite_page_list_selector: '<?php echo $lang->get('List selector'); ?>'
        },
        type_msg: {
            length: '<?php echo $lang->get('Size of {:attr} must be {:rule}'); ?>'
        },
        msg: {
            loading: '<?php echo $lang->get('Loading'); ?>'
        },
        box: {
            msg: '<?php echo $lang->get('Input error'); ?>'
        }
    };

    var opts_submit_form = {
        modal: {
            btn_text: {
                close: '<?php echo $lang->get('Close'); ?>',
                ok: '<?php echo $lang->get('OK'); ?>'
            }
        },
        msg_text: {
            submitting: '<?php echo $lang->get('Saving'); ?>'
        }
    };

    $(document).ready(function(){
        var obj_validate_form = $('#gsite_form').baigoValidate(opts_validate_form);
        var obj_submit_form   = $('#gsite_form').baigoSubmit(opts_submit_form);
        $('#gsite_form').submit(function(){
            if (obj_validate_form.verify()) {
                obj_submit_form.formSubmit();
            }
        });

        $('#gsite_preview').html('<div class="embed-responsive embed-responsive-21by9"><iframe class="embed-responsive-item" scrolling="auto" src="<?php echo $route_console; ?>gsite_preview/page-lists/id/<?php echo $gsiteRow['gsite_id']; ?>/"></iframe></div>');

        $('#gsite_source').html('<div class="embed-responsive embed-responsive-21by9"><iframe class="embed-responsive-item" scrolling="auto" src="<?php echo $route_console; ?>gsite_source/page-lists/id/<?php echo $gsiteRow['gsite_id']; ?>/"></iframe></div>');
    });
    </script>

<?php include($cfg['pathInclude'] . 'html_foot' . GK_EXT_TPL);