<?php

/**
 * Plugin Name: Taxus Advanced Search
 * Plugin URI: https://taxus.ir/wordpress?utm_source=wordpress-repo
 * Description: Taxus advanced search
 * Author: taxus
 * Author URI: https://taxus.ir/?utm_source=wordpress-repo
 * Text Domain: wp-taxus
 * Domain Path: /languages/
 * License: GPL v3
 * Version: 1.1.1
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'taxus_load_textdomain');

function taxus_load_textdomain() {
    load_plugin_textdomain('wp-taxus', false, basename(dirname(__FILE__)) . '/languages');
}

define('tax_dir', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('tax_lib', tax_dir . 'libs' . DIRECTORY_SEPARATOR);
include_once(tax_lib . 'functions.php');

register_activation_hook(__FILE__, 'taxus_construct');

function taxus_construct() {

    $uid = get_option('taxus_unique_id');
    if (empty($uid)) {
        update_option('taxus_unique_id', time());
    }
}

register_activation_hook(__FILE__, 'taxus_plugin_activate');
add_action('admin_init', 'taxus_plugin_redirect');

function taxus_plugin_activate() {
    add_option('taxus_plugin_do_activation_redirect', true);
}

function taxus_plugin_redirect() {
    if (get_option('taxus_plugin_do_activation_redirect', false)) {
        delete_option('taxus_plugin_do_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_redirect("admin.php?page=taxus_config_page");
            exit();
        }
    }
}

add_action('admin_menu', 'taxus_admin_menu');

function taxus_admin_menu() {
    add_menu_page(__('Taxus Settings', 'wp-taxus'), __('Taxus Settings', 'wp-taxus'), 'administrator', 'taxus_config_page', 'taxus_config_page', 'dashicons-search');
    add_submenu_page('taxus_config_page', __('Sync', 'wp-taxus'), __('Sync', 'wp-taxus'), 'administrator', 'taxus_send_data_page', 'taxus_send_data_page');
}

function taxus_config_page() {
    include_once(tax_dir . 'admin' . DIRECTORY_SEPARATOR . 'config.php');
}

function taxus_send_data_page() {
    include_once(tax_dir . 'admin' . DIRECTORY_SEPARATOR . 'send-data.php');
}

//
//=================================================== hooks for CRUD =====================================================

function taxus_save_post_send($post_id, $post, $update) {
    $uid = get_option('taxus_unique_id', true) . '-';
    if (($post->post_type == 'page' or $post->post_type == 'post') and $post->post_status == 'publish' and empty($post->post_password)) {

        $param = array(
            'id' => $uid . $post->ID,
            'title' => trim($post->post_title),
            'text' => trim(wp_strip_all_tags(strip_shortcodes($post->post_content))),
            'url' => urldecode(wp_get_shortlink($post->ID)),
            'category' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'keywords' => wp_get_post_tags($post->ID, array('fields' => 'names')),
            'published_date' => strtotime($post->post_date));

        if ($img = get_the_post_thumbnail_url($post->ID)) {
            $param['image'] = $img;
        }
        $res = taxus_get_item($uid . $post->ID);
        if ($res->code == '404') {
            $res = taxus_add_item($param);
        } else {
            $res = taxus_upd_item($param);
        }
    } else {
        taxus_del_item($post->ID);
    }
}

add_action('save_post', 'taxus_save_post_send', 10, 3);

function taxus_delete_post_send($post_id) {
    $uid = get_option('taxus_unique_id', true) . '-';
    $res = taxus_del_item($uid . $post_id);
}

add_action('delete_post', 'taxus_delete_post_send');

//===================================================== hook add search script =============================================================

if (!is_admin()) {
    add_action('wp_footer', 'taxus_search_assets');
    add_filter('script_loader_tag', 'taxus_add_asyncdefer_attribute', 10, 2);
}

function taxus_add_asyncdefer_attribute($tag, $handle) {
    // if the unique handle/name of the registered script has 'async' in it
    if (strpos($handle, 'async') !== false) {
        // return the tag with the async attribute
        return str_replace('<script ', '<script async ', $tag);
    }
    // if the unique handle/name of the registered script has 'defer' in it
    else if (strpos($handle, 'defer') !== false) {
        // return the tag with the defer attribute
        return str_replace('<script ', '<script defer ', $tag);
    }
    // otherwise skip
    else {
        return $tag;
    }
}

function taxus_search_assets() {

    $opt = get_option('taxus_options');
    wp_enqueue_script('taxus-search-defer', "https://cdn.taxus.ir/script/search-wp.js#{$opt['apisearch']}", array('jquery'), null, false);
}

//===================================================== hook add search script =============================================================
if (is_admin()) {
    add_action('wp_ajax_taxus_insert_all', 'taxus_insert_all_ajax');
}

function taxus_insert_all_ajax() {
    taxus_send_multy_all();
}

?>
