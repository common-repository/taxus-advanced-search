<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h2><?php _e('Sync all pages', 'wp-taxus'); ?></h2>
    <?php
    global $wpdb;
    $opt = get_option('taxus_options');
    $act = true;

    if (empty($opt['apikey'])) {
        echo '<div class="notice notice-error"><p>' . __('Enter APIKEY before sending', 'wp-taxus') . '</p></div>';
        $act = false;
    }
    ?>
    <style type="text/css">
        .button-green {
            background: #00ba37 !important;
            border-color: #00aa62 #00994d #009943 !important;
            box-shadow: 0 1px 0 #009943 !important;
            color: #fff !important;
            text-shadow: 0 -1px 1px #009948,-1px 0 1px #00994d,0 1px 1px #009948,1px 0 1px #009953 !important;
        }

        .button-green:hover{
            background:#00952c !important;
            border-color:#00952c !important;
            color:#fff !important
        }

        .txdash.dashicons{
            float: right;
            margin: 3px 0 0px 3px;
        }
        .meter { 
            height: 25px;  /* Can be anything */
            position: relative;
            margin: 20px 0 20px 0; /* Just for demo spacing */
            background: #555;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            border-radius: 10px;
            padding: 5px;
            -webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
            -moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
            box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
        }
        .meter > span {
            display: block;
            height: 100%;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            border-radius: 10px;
            background-color: rgb(43,194,83);
            background-image: -webkit-gradient(
                linear,
                left bottom,
                left top,
                color-stop(0, rgb(43,194,83)),
                color-stop(1, rgb(84,240,84))
                );
            background-image: -moz-linear-gradient(
                center bottom,
                rgb(43,194,83) 37%,
                rgb(84,240,84) 69%
                );
            -webkit-box-shadow: 
                inset 0 2px 9px  rgba(255,255,255,0.3),
                inset 0 -2px 6px rgba(0,0,0,0.4);
            -moz-box-shadow: 
                inset 0 2px 9px  rgba(255,255,255,0.3),
                inset 0 -2px 6px rgba(0,0,0,0.4);
            box-shadow: 
                inset 0 2px 9px  rgba(255,255,255,0.3),
                inset 0 -2px 6px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
            text-align:center;
            color:#fff;
            font-size:1rem;
        }
        .meter > span:after, .animate > span > span {
            content: "";
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background-image: 
                -webkit-gradient(linear, 0 0, 100% 100%, 
                color-stop(.25, rgba(255, 255, 255, .2)), 
                color-stop(.25, transparent), color-stop(.5, transparent), 
                color-stop(.5, rgba(255, 255, 255, .2)), 
                color-stop(.75, rgba(255, 255, 255, .2)), 
                color-stop(.75, transparent), to(transparent)
                );
            background-image: 
                -moz-linear-gradient(
                -45deg, 
                rgba(255, 255, 255, .2) 25%, 
                transparent 25%, 
                transparent 50%, 
                rgba(255, 255, 255, .2) 50%, 
                rgba(255, 255, 255, .2) 75%, 
                transparent 75%, 
                transparent
                );
            z-index: 1;
            -webkit-background-size: 50px 50px;
            -moz-background-size: 50px 50px;
            -webkit-animation: move 2s linear infinite;
            -webkit-border-top-right-radius: 8px;
            -webkit-border-bottom-right-radius: 8px;
            -moz-border-radius-topright: 8px;
            -moz-border-radius-bottomright: 8px;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            -webkit-border-top-left-radius: 20px;
            -webkit-border-bottom-left-radius: 20px;
            -moz-border-radius-topleft: 20px;
            -moz-border-radius-bottomleft: 20px;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            overflow: hidden;
        }

        .animate > span:after {
            display: none;
        }

        @-webkit-keyframes move {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 50px 50px;
            }
        }

        .orange > span {
            background-color: #f1a165;
            background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
            background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, #f36d0a));
            background-image: -webkit-linear-gradient(#f1a165, #f36d0a); 
        }

        .red > span {
            background-color: #f0a3a3;
            background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
            background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, #f42323));
            background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
        }

        .nostripes > span > span, .nostripes > span:after {
            -webkit-animation: none;
            background-image: none;
        }
        .hide{
            display:none
        }
    </style>
    <div id="tx_sync_all_notice" class="notice notice-info">
            <!--a href="https://taxus.ir/"><img style="width: 100px;float: left;margin: 10px 0 0 0;" src="<?php echo plugins_url('taxus-logo.png', dirname(__FILE__)); ?>" ></a-->
        <p style="margin:15px 0;font-size:0.9rem;">
            <?php _e('To activate the search engine on the site, you need to synchronize the current pages with Taxus.', 'wp-taxus'); ?>
            <br/>
            <?php _e('Sync for new pages will automatically be done.', 'wp-taxus'); ?>
            <br/>
            <?php _e('Click the button below to start syncing.', 'wp-taxus'); ?>
        </p>
    </div>
    <div id="tx_sync_all_completed" class="notice notice-success" style="display:none;">
            <!--a href="https://taxus.ir/"><img style="width: 100px;float: left;margin: 10px 0 0 0;" src="<?php echo plugins_url('taxus-logo.png', dirname(__FILE__)); ?>" ></a-->

        <p style="margin:15px 0;font-size:0.9rem;text-align:center;">
            <span class="dashicons dashicons-yes" style="color:#46b450;font-size:2.5rem;margin-bottom:15px;"></span>
            <br/>
            <?php _e('Synchronization was successful', 'wp-taxus'); ?>

            <br/>
            <?php _e('New pages will be automatically synced', 'wp-taxus'); ?>
            <br/>
            <a href="admin.php?page=taxus_config_page">
                <?php _e('Return to the settings page', 'wp-taxus'); ?>
            </a>
        </p>
    </div>

    <?php
    $last_id = get_option('taxus_current_id', '');
    $last_id = empty($last_id) ? '' : " and ID<'$last_id'";
    if($last_id!='')
    {
    $count = $wpdb->get_col("(select count(ID) from $wpdb->posts where post_status='publish' and post_password='' and (post_type='page' OR post_type='post'))
union all
(select count(ID) from $wpdb->posts where post_status='publish' and post_password='' and (post_type='page' OR post_type='post')$last_id)");

    $per = (100 - (int) (($count[1] / $count[0]) * 100));
    }else
    {
        $per=0;
    }
    ?>
    <button type="button" <?php echo $act ? '' : 'disabled '; ?>name="insert_all" class="button button-green send_all"><span class="txdash dashicons dashicons-update"></span><?php _e('Sync', 'wp-taxus'); ?></button>

    <div class="meter red<?php
    if ($per == 0 || $per == 100 || $per == -100) {
        echo ' hide';
    }
    ?>">
        <span class="per" style="width: <?php echo $per; ?>%"><?php echo $per; ?>%</span>
    </div>

    <script type="text/javascript">
        jQuery('button[name=insert_all]').click(function (e) {
            jQuery('.meter').show('slow');
            if (jQuery(this).text() === '<?php _e("Sync", "wp-taxus"); ?>') {
                jQuery(this).html('<span class="txdash dashicons dashicons-update"></span><?php _e("Stop the process", "wp-taxus"); ?>');
                taxus_send_data();
            } else {
                jQuery(this).html('<span class="txdash dashicons dashicons-update"></span><?php _e("Sync", "wp-taxus"); ?>');
            }
        });

        function taxus_send_data() {
            max = jQuery('.per').text();

            if (max === '100%') {
                jQuery('.send_all').html('<span class="txdash dashicons dashicons-update"></span><?php _e("Sync", "wp-taxus"); ?>');
                jQuery('.send_all').hide();
                jQuery('.meter').hide();
                jQuery('#tx_sync_all_notice').hide();
                jQuery('#tx_sync_all_completed').show('slow');
                return;
            }


            jQuery.post(ajaxurl, {action: 'taxus_insert_all'}, function (data) {

                data = parseInt(data);
                if (data > 100) {
                    if (data === 400)
                        alert('<?php _e('Input data is invalid', 'wp-taxus'); ?>');
                    if (data === 401)
                        alert('<?php _e('The programming interface key is not valid', 'wp-taxus'); ?>');
                    if (data === 403)
                        alert('<?php _e('User account is limited, please visit the admin panel.', 'wp-taxus'); ?>');
                    if (data === 413)
                        alert('<?php _e('The number of items exceeds limit set', 'wp-taxus'); ?>');
                    return;
                }
                jQuery('.per').css('width', data + '%');
                jQuery('.per').text(data + '%');
                if (jQuery('.send_all').text() === '<?php _e("Stop the process", "wp-taxus"); ?>')
                    taxus_send_data();
            });

        }

    </script>
</div>
