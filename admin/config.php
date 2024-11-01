<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if(isset($_POST['save'])){
	if ( ! isset( $_POST['taxus_info_nonce'] ) || ! wp_verify_nonce( $_POST['taxus_info_nonce'], 'taxus_info_nonce_save_form' ) ) die( 'Security check failed!' );
}
?>
<div class="wrap">
<?php
	if(isset($_POST['save'])){
	   update_option('taxus_options',taxus_sanitize_array($_POST));
       echo '<div class="notice notice-success"><p>'.__("Settings saved successfully", "wp-taxus").'</p></div>';
	}
    if(isset($_POST['delete_all'])){
        $ret = taxus_del_item();
        if($ret->code==200)
            echo '<div class="notice notice-success"><p>'.__("Data deletion operation was successful", "wp-taxus").'</p></div>';
        else
            echo '<div class="notice notice-warning"><p>'.__("Error in complete data deletion", "wp-taxus").'</p></div>';
    }

    $opt = get_option('taxus_options',array());
    if(!$opt['apisearch'] || !$opt['apisearch']=='')
    {
        taxus_set_search_key();
    }
?>
<h2><?php _e('Taxus Settings', 'wp-taxus'); ?></h2>
<?php
$xx  = taxus_get_info();
if(empty($opt) or $xx->code!='200'){
?>
<div class="notice notice-info">
	<!--a href="https://taxus.ir/"><img style="width: 100px;float: left;margin: 10px 0 0 0;" src="<?php echo plugins_url( 'taxus-logo.png', dirname(__FILE__) ); ?>" ></a-->
	<p style="margin-top:20px;">
	<?php _e('With this plugin, your site search is done by the Taxus service.', 'wp-taxus'); ?><br />
	<?php _e('This requires one <a href="https://app.taxus.ir/" target="_blank">account</a> In Taxus service.', 'wp-taxus'); ?> 
    <?php _e('After entering the account, enter the API Key and enter in the form below.', 'wp-taxus'); ?><br />
	</p>
</div>
<?php
}
?>
<form method="post" enctype="multipart/form-data">
<?php

if(empty($opt) or $xx->code=='401'){

    if($xx->code=='401' and !empty($opt)){
        echo '<div class="notice notice-error"><p>'.__("The API Key is not valid.", "wp-taxus").'</p></div>';
    }
?>
<table class="widefat">
<thead>
<tr>
	<th colspan="2"><?php _e('Plugin settings', 'wp-taxus'); ?></th>
</tr>
</thead>
<tr>
	<td>API Key</td>
	<td><input type="text" dir="ltr" size="102" name="apikey" value="<?php echo isset($opt['apikey'])?$opt['apikey']:'' ?>" required="" /></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="<?php _e('Connect to the Taxus service', 'wp-taxus'); ?>" name="save" class="button-primary" /></td>
</tr>
</table>
<?php
}else{

//$act = isset($opt['active'])?$opt['active']:0;
$xcode = $xx->code;
$xx    = $xx->response;

if($xx->maxAllowedItems <= $xx->totalItems){
    echo '<div class="notice notice-error"><p>'.__('The number of pages you search for has reached the maximum number allowed. Sync for new pages is not done. For more information, see the <a href="https://app.taxus.ir/" class="button button-secondary" target="_blank"> Taxus user panel </a>.', 'wp-taxus').'</p></div>';
}
if($xx->totalItems==0){
    echo '<div class="notice notice-warning" ><p style="font-size:1rem;text-align:center"><span class="dashicons dashicons-warning" style="margin-bottom:20px;font-size:2.5rem;color:#ffb900"></span><br/>'.__("To enable the search engine, <a href=\"admin.php?page=taxus_send_data_page\"> sync your pages </a>.", "wp-taxus").'</p></div>';
}
?>
<style type="text/css">
.info{
    display: block;
    background: #FFFFFF;
    padding: 1%;
	box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
.info-col{
    width:24%;
    display: block;
    float:right;
    text-align: center;
	margin: .3%;
	box-shadow: 0 0 0 1px #bbbbbb;
}
.txdash.dashicons{
    float: right;
    margin: 3px 0 0px 3px;
}

.button-red {
    background: #f00 !important;
    border-color: #d21010 #b61033 #c92c3c !important;
    box-shadow: 0 1px 0 #900 !important;
    color: #fff !important;
    text-shadow: 0 -1px 1px #900,-1px 0 1px #d11528,0 1px 1px #cf2424,1px 0 1px #99001b !important;
}

.button-red:hover{
    background: #b90000 !important;
    border-color:#b90000 !important;
    color:#fff !important
}
</style>
    <div class="info">
        <div class="info-col"><p><?php echo "<span style='color:#002060'><strong>$xx->totalItems</strong></span><br />".__('Number of pages synced', 'wp-taxus');  ?></p></div>
        <div class="info-col"><p><?php echo "<span style='color:#C00000'><strong>$xx->maxAllowedItems</strong></span><br />".__('Maximum number of pages allowed', 'wp-taxus');  ?></p></div>
        <div class="info-col"><p><?php echo "<span style='color:#538135'><strong>$xx->todaySearches</strong></span><br />".__('Searches done today', 'wp-taxus');  ?></p></div>
        <div class="info-col"><p><?php echo "<span style='color:#FFC000'><strong>$xx->todayNoResultSearches</strong></span><br />".__('Searches without results today', 'wp-taxus');  ?></p></div>
        <div style="clear:both;padding:5px;">
             <p>
                <i>
                <b>
                    <?php _e('Synced pages', 'wp-taxus'); ?>
                    </b>
                    <?php _e('The number of pages on your site that has been activated for the search of the taxus.', 'wp-taxus'); ?>
                </i>
            </p>
            <p>
                <i>
                <b>
                    <?php _e('Maximum allowed pages', 'wp-taxus'); ?>
                    </b>
                    <?php _e('The number of pages that you can use for your account with the the taxus service to search.', 'wp-taxus'); ?>
                </i>
            </p>
            <p style="margin-top:30px;"><?php _e('See the <a href="https://app.taxus.ir/" target="_blank"> user panel </a> for more reports.', 'wp-taxus'); ?></p>
        </div>
    </div>
<br />
<table class="widefat">
<?php


if(empty($opt) or empty($xx) or $xcode=='401'){
    ?>
<thead>
<tr>
	<th colspan="2"><?php _e('Plugin settings', 'wp-taxus'); ?></th>
</tr>
</thead>
<tr>
	<td>API Key</td>
	<td><input type="text" dir="ltr" size="102" name="apikey" value="<?php echo isset($opt['apikey'])?$opt['apikey']:'' ?>" required="" /></td>
</tr>
<tr style="display:none;">
	<td><?php _e('Search API Key', 'wp-taxus'); ?></td>
	<td><textarea cols="100" dir="ltr" rows="3" wrap="virtual" name="apisearch" ><?php echo taxus_get_search_key()->response[0]->key; ?></textarea></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="<?php _e('Update settings', 'wp-taxus'); ?>" name="save" class="button-primary" /></td>
</tr>

<tr>
    <td colspan="2"><hr /></td>
</tr>
<?php
}
?>
<tr>
    <td colspan="2"><?php _e('Delete all pages from the search service', 'wp-taxus'); ?></td>
</tr>
<tr>
	<td colspan="2"><button type="submit" name="delete_all" class="button button-red" onclick="return confirm('<?php _e('Do you want to delete all pages from the search service?', 'wp-taxus'); ?>')"><span class="txdash dashicons dashicons-trash"></span> <?php _e('Delete all information', 'wp-taxus'); ?></button></td>
</tr>
</table>
<?php } ?>
</div>
<?php wp_nonce_field( 'taxus_info_nonce_save_form', 'taxus_info_nonce' ); ?>
</form>