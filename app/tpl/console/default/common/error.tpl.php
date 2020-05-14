<?php $cfg = array(
    'title'         => $lang->get('Error', 'console.common'),
    'noToken'       => 'true',
    'pathInclude'   => $path_tpl . 'include' . DS,
);

switch ($rstatus) {
    case 'y':
        $_str_status = 'success';
        $_str_icon   = 'check-circle';
    break;

    default:
        $_str_status = 'danger';
        $_str_icon   = 'times-circle';
    break;
}

if (isset($param['view'])) {
    switch ($param['view']) {
        case 'modal': ?>
            <div class="modal-header">
                <div class="modal-title"><?php echo $cfg['title']; ?></div>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
        <?php break;

        default:
            include($cfg['pathInclude'] . 'html_head' . GK_EXT_TPL); ?>
            <div class="m-3">
        <?php break;
    }
} else {
    include($cfg['pathInclude'] . 'console_head' . GK_EXT_TPL); ?>

    <nav class="nav mb-3">
        <a href="javascript:history.go(-1);" class="nav-link">
            <span class="fas fa-chevron-left"></span>
            <?php echo $lang->get('Back', 'console.common'); ?>
        </a>
    </nav>
<?php } ?>

            <h3 class="text-<?php echo $_str_status; ?>">
                <span class="fas fa-<?php echo $_str_icon; ?>"></span>
                <?php if (isset($msg)) {
                    echo $lang->get($msg);
                } ?>
            </h3>

            <div class="text-<?php echo $_str_status; ?> lead">
                <?php if (isset($rcode)) {
                    echo $rcode;
                } ?>
            </div>
            <?php if (isset($rcode)) { ?>
                <hr>
                <div>
                    <?php echo $lang->get($rcode, 'console.common', '', false); ?>
                </div>
            <?php } ?>

<?php if (isset($param['view'])) {
    switch ($param['view']) {
        case 'modal': ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                    <?php echo $lang->get('Close', 'console.common'); ?>
                </button>
            </div>
        <?php break;

        default: ?>
            </div>

            <?php if (isset($rcode) && ($rcode == 'y280401' || $rcode == 'y280404')) { ?>
                <script type="text/javascript">
                $(document).ready(function(){
                    $('.modal-title', parent.document).text('<?php echo $lang->get($msg); ?>');

                    $('#gather_modal #gather_modal_icon', parent.document).attr('class', 'fas fa-<?php echo $_str_icon; ?> text-<?php echo $_str_status; ?>');
                    $('#gather_modal #gather_modal_msg', parent.document).attr('class', 'text-<?php echo $_str_status; ?>');
                    $('#gather_modal #gather_modal_msg', parent.document).text('<?php echo $lang->get($msg); ?>');
                });
                </script>
            <?php }

            include($cfg['pathInclude'] . 'html_foot' . GK_EXT_TPL);
        break;
    }
} else {
    include($cfg['pathInclude'] . 'console_foot' . GK_EXT_TPL);
    include($cfg['pathInclude'] . 'html_foot' . GK_EXT_TPL);
}
