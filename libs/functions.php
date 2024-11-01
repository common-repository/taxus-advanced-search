<?php

if (!defined('ABSPATH')) {
    exit;
}

function taxus_send_request($url, $type, $param) {

    $opt = get_option('taxus_options');

    if (empty($opt['apikey'])) {
        return (object) array('code' => 401, 'response' => '');
    }

    $args = array(
        'method' => $type,
        'headers' => array(
            'Authorization' => $opt['apikey'],
            'accept' => 'application/json',
            'Content-Type' => 'application/json'
        ),
        'body' => ($type == 'GET') ? $param : json_encode($param),
    );
    if ($type == 'GET') {
        $response = wp_remote_get("https://api.taxus.ir/v1$url", $args);
    } else {
        $response = wp_remote_request("https://api.taxus.ir/v1$url", $args);
    }
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    return (object) array('code' => $code, 'response' => json_decode($body));
}

function taxus_get_item($id) {

    $uid = get_option('taxus_unique_id', true) . '-';
    return taxus_send_request("/items/$uid$id/", 'GET', array());
}

function taxus_add_item($param) {

    return taxus_send_request('/items', 'POST', $param);
}

function taxus_upd_item($param) {

    return taxus_send_request('/items', 'PUT', $param);
}

function taxus_del_item($id = '') {

    if (empty($id)) {
        update_option('taxus_current_id', '');
    } else {
        $id = get_option('taxus_unique_id', true) . "-$id";
    }
    return taxus_send_request("/items/$id", 'DELETE', array());
}

function taxus_send_multy_all() {

    set_time_limit(0);
    global $wpdb;
    $last_id = get_option('taxus_current_id', '');
    $last_id = empty($last_id) ? '' : " and ID<'$last_id'";
    $rows = $wpdb->get_results("select ID,post_title,post_content,post_date from $wpdb->posts where post_status='publish' and post_password='' and (post_type='page' OR post_type='post')$last_id order by ID desc limit 25", ARRAY_A);
    $uid = get_option('taxus_unique_id', true) . '-';
    $posts = array();
    $post = null;
    foreach ($rows as $i => $post) {

        $param = array(
            'id' => $uid . $post['ID'],
            'title' => trim($post['post_title']),
            'text' => trim(wp_strip_all_tags(strip_shortcodes($post['post_content']))),
            //'url'            => urldecode(get_permalink($post['ID'])),
            'url' => urldecode(wp_get_shortlink($post['ID'])),
            'category' => wp_get_post_categories($post['ID'], array('fields' => 'names')),
            'keywords' => wp_get_post_tags($post['ID'], array('fields' => 'names')),
            'published_date' => strtotime($post['post_date']));

        if ($img = get_the_post_thumbnail_url($post['ID'])) {
            $param['image'] = $img;
        }
        array_push($posts, $param);
    }

    $ret = taxus_send_request('/items/bulk', 'POST', $posts);
//    echo $ret->code;
//    echo $ret->response;
    if ($ret->code == 201) {
        $count = $wpdb->get_col("(select count(ID) from $wpdb->posts where post_status='publish' and post_password='' and (post_type='page' OR post_type='post'))
            union all
            (select count(ID) from $wpdb->posts where post_status='publish' and post_password='' and (post_type='page' OR post_type='post') and ID<'{$post['ID']}')");
        if (!empty($post['ID'])) {
            update_option('taxus_current_id', $post['ID']);
        }
        echo 100 - (int) (($count[1] / $count[0]) * 100);
        die();
    }
    echo $ret->code;
    die();
}

function taxus_percent($max, $new) {

    return 100 - (int) (($new / $max) * 100);
}

function taxus_search($item = '*') {

    $opt = get_option('taxus_options');
    return taxus_send_request("/search/{$opt['apisearch']}?query=$item", 'GET', array());
}

function taxus_get_info() {

    return taxus_send_request('/analytics/stats/', 'GET', array());
}

function taxus_get_search_key() {
    return taxus_send_request('/search-uis/', 'GET', array());
}

function taxus_set_search_key() {
    $key = taxus_get_search_key();
    if (empty($key->response[0]))
        return;

    $key = $key->response[0]->key;
    $opt = get_option('taxus_options');
    $opt['apisearch'] = $key;
    update_option('taxus_options', $opt);
}

function taxus_utf_correction($str) {
    $str_hex = bin2hex($str);
    $str = hex2bin($str_hex);
    $fixed = preg_replace_callback(
            '/\\P{Arabic}+/u', function (array $m) {
        return iconv('UTF-8', 'ISO-8859-1', $m[0]);
    }, $str
    );
    return $fixed;
}

function taxus_sanitize_array($input) {
    return array_map(function( $val ) {
        return sanitize_text_field($val);
    }, $input);
}
