<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-citypay-paylink" data-toggle="tooltip" title="<?php echo $button_label_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_label_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-citypay-paylink" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_merchant_id"><?php echo $entry_label_merchant_id; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="citypay_paylink_merchant_id" value="<?php echo $citypay_paylink_merchant_id; ?>" placeholder="<?php echo $entry_label_merchant_id; ?>" id="citypay_paylink_merchant_id" class="form-control" />
                            <?php if ($error_merchant_id) { ?>
                                <div class="text-danger"><?php echo $error_merchant_id; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_licence_key"><?php echo $entry_label_licence_key; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="citypay_paylink_licence_key" value="<?php echo $citypay_paylink_licence_key; ?>" placeholder="<?php echo $entry_label_licence_key; ?>" id="citypay_paylink_licence_key" class="form-control" />
                            <?php if ($error_licence_key) { ?>
                                <div class="text-danger"><?php echo $error_licence_key; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_postback_url"><?php echo $entry_label_postback_url; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="citypay_paylink_postback_url" value="<?php echo $citypay_paylink_postback_url; ?>" placeholder="<?php echo $entry_label_postback_url; ?> is optional" id="citypay_paylink_postback_url" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_merchant_currency_id"><?php echo $entry_label_merchant_currency; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_merchant_currency_id" id="citypay_paylink_merchant_currency_id" class="form-control">
                                <?php foreach ($values_currencies as $merchant_currency_id => $merchant_currency_description) { ?>
                                    <?php if ($merchant_currency_id == $citypay_paylink_merchant_currency_id) { ?>
                                        <option value="<?php echo $merchant_currency_id; ?>" selected="selected"><?php echo $merchant_currency_description; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $merchant_currency_id; ?>"><?php echo $merchant_currency_description; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_status"><?php echo $entry_label_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_status" id="citypay_paylink_status" class="form-control">
                                <?php if ($citypay_paylink_status) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_new_order_status_id"><?php echo $entry_label_new_order_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_new_order_status_id" id="citypay_paylink_new_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $citypay_paylink_new_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_completed_order_status_id"><?php echo $entry_label_completed_order_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_completed_order_status_id" id="citypay_paylink_completed_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $citypay_paylink_completed_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_cancelled_order_status_id"><?php echo $entry_label_cancelled_order_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_cancelled_order_status_id" id="citypay_paylink_cancelled_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $citypay_paylink_cancelled_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_expired_order_status_id"><?php echo $entry_label_expired_order_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_expired_order_status_id" id="citypay_paylink_expired_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $citypay_paylink_expired_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_failed_order_status_id"><?php echo $entry_label_failed_order_status; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_failed_order_status_id" id="citypay_paylink_failed_order_status_id" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $citypay_paylink_failed_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_geo_zone_id"><?php echo $entry_label_geo_zone; ?></label>
                        <div class="col-sm-10">
                            <select name="citypay_paylink_geo_zone_id" id="citypay_paylink_geo_zone_id" class="form-control">
                                <option value="0"><?php echo $text_all_zones; ?></option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                    <?php if ($geo_zone['geo_zone_id'] == $citypay_paylink_geo_zone_id) { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="citypay_paylink_sort_order"><?php echo $entry_label_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="citypay_paylink_sort_order" value="<?php echo $citypay_paylink_sort_order; ?>" placeholder="<?php echo $citypay_paylink_sort_order; ?>" id="citypay_paylink_sort_order" class="form-control" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>