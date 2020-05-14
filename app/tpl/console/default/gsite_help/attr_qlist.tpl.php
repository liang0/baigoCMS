                    <div class="modal-header">
                        <div class="modal-title"><?php echo $lang->get('Optional as follows'); ?></div>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <dl>
                            <?php foreach ($attrQlist as $_key=>$_value) { ?>
                                <dt>
                                    <?php echo $lang->get($_value['title']); ?>
                                </dt>
                                <dd>
                                    <?php echo $lang->get($_value['note']); ?>
                                </dd>
                            <?php } ?>
                        </dl>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                            <?php echo $lang->get('Close'); ?>
                        </button>
                    </div>
                </div>
