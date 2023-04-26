<?php
/*
Plugin Name: Dados Do Mercado API 2
Description: A plugin to get data from Dados do Mercado
Author: Anderson Bassan
Version: 1.1.0
*/

function create_the_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'api_database';

    $columns = "
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        value FLOAT NOT NULL,
        percentage FLOAT NOT NULL,
        PRIMARY KEY (id)
        ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( "CREATE TABLE $table_name ( $columns )" );
}

function get_ibov_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);


    $data = json_decode($body, true);
    $last_item = end($data);


    $name = 'IBOV';
    $date = date('Y-m-d');
    $value = $last_item['close'];
    $new_value = $last_item['close'];
    $old_value = $last_item['open'];

    $percentage_variation = round((($new_value - $old_value) / $old_value) * 100, 2);

    $ibov_values = array($name, $date, $value, $percentage_variation);

    return $ibov_values;
}

function get_selic_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $last_item = $data[0];
    $second_to_last_item = $data[1];

    $name = 'SELIC';
    $date = date('Y-m-d');
    $value = $last_item['value'];
    $new_value = $last_item['value'];
    $old_value = $second_to_last_item['value'];
    $percentage_variation = round((($new_value - $old_value) / $old_value) * 100, 2);

    $selic_values = array($name, $date, $value, $percentage_variation);

    return $selic_values;
}

function get_ipca_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $last_item = $data[0];

    $sum = 0;

    for ($i = 0; $i < 12; $i++) {
        $sum += $data[$i]['value'];
    }

    $name = 'IPCA';
    $date = date('Y-m-d');
    $value = $last_item['value'];
    $new_value = $last_item['value'];
    $percentage_variation = round($sum, 2);

    $ipca_values = array($name, $date, $value, $percentage_variation);

    return $ipca_values;
}

function get_inpc_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);


    $last_item = $data[0];

    $sum = 0;

    for ($i = 0; $i < 12; $i++) {
        $sum += $data[$i]['value'];
    }

    $name = 'INPC';
    $date = date('Y-m-d');
    $value = $last_item['value'];
    $new_value = $last_item['value'];
    $percentage_variation = round($sum, 2);

    $inpc_values = array($name, $date, $value, $percentage_variation);

    return $inpc_values;
}

function get_inccm_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $last_item = $data[0];


    $sum = 0;

    for ($i = 0; $i < 12; $i++) {
        $sum += $data[$i]['value'];
    }

    $name = 'INCC-M';
    $date = date('Y-m-d');
    $value = number_format($last_item['value'], 2);
    $new_value = $last_item['value'];
	$percentage_variation = round($sum, 2);

    $inccm_values = array($name, $date, $value, $percentage_variation);

    return $inccm_values;
}

function get_inccdi_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $last_item = $data[0];


    $sum = 0;

    for ($i = 0; $i < 12; $i++) {
        $sum += $data[$i]['value'];
    }
	
    $name = 'INCC-DI';
    $date = date('Y-m-d');
    $value = number_format($last_item['value'], 2);
    $new_value = $last_item['value'];
    $percentage_variation = round($sum, 2);

    $inccdi_values = array($name, $date, $value, $percentage_variation);

    return $inccdi_values;
}

function get_cdi_values($api_url, $authorization_header) {
    $args = array(
        'headers' => array(
            'Authorization' => $authorization_header,
        ),
    );

    $response = wp_remote_get($api_url, $args);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $last_item = $data[0];

    $new_value = $data[0]['value']; // Get the value of the first item

    $name = 'CDI';
    $date = date('Y-m-d');
    $value = $last_item['value'];

    $first_value = $data[0]['value'];
    foreach ($data as $item) {
      if ($item['value'] != $first_value) { // Check if the value is different from the first value
        $old_value = $item['value']; // Save the different value
        break; // Exit the loop since we have found the different value
      }
    }

    $percentage_variation = round((($new_value - $old_value) / $old_value) * 100, 2);

    $cdi_values = array($name, $date, $value, $percentage_variation);

    return $cdi_values;
}

function get_api_items() {
    $ibov_api_url = 'https://api.dadosdemercado.com.br/v1/tickers/IBOV/quotes';
    $selic_api_url = 'https://api.dadosdemercado.com.br/v1/macro/selic';
    $ipca_api_url = 'https://api.dadosdemercado.com.br/v1/macro/ipca';
    $inpc_api_url = 'https://api.dadosdemercado.com.br/v1/macro/inpc';
    $inccm_api_url = 'https://api.dadosdemercado.com.br/v1/macro/incc-m';
    $inccdi_api_url = 'https://api.dadosdemercado.com.br/v1/macro/incc-di';
    $cdi_api_url = 'https://api.dadosdemercado.com.br/v1/macro/cdi';



    $authorization_header = 'Bearer a1472a8ea263aa0655185e9deff9e035';

    $ibov = get_ibov_values($ibov_api_url, $authorization_header);

    sleep(2);

    $selic = get_selic_values($selic_api_url, $authorization_header);

    sleep(2);

    $ipca = get_ipca_values($ipca_api_url, $authorization_header);

    sleep(2);

    $inpc = get_inpc_values($inpc_api_url, $authorization_header);

    sleep(2);

    $inccm = get_inccm_values($inccm_api_url, $authorization_header);

    sleep(2);

    $inccdi = get_inccdi_values($inccdi_api_url, $authorization_header);

    sleep(2);
	
    $cdi = get_cdi_values($cdi_api_url, $authorization_header);

    $api_items = array($ibov, $selic, $ipca, $inpc, $cdi, $inccm, $inccdi);
    return $api_items;
}

function get_api_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'api_database';

    $query1 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'IBOV');

    $results1 = $wpdb->get_results($query1);

    $query2 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'SELIC');

    $results2 = $wpdb->get_results($query2);

    $query3 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'IPCA');

    $results3 = $wpdb->get_results($query3);

    $query4 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'INPC');

    $results4 = $wpdb->get_results($query4);

    $query5 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'CDI');

    $results5 = $wpdb->get_results($query5);

    $query6 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'INCC-M');

    $results6 = $wpdb->get_results($query6);


    $query7 = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s ORDER BY id DESC LIMIT 1", 'INCC-DI');

    $results7 = $wpdb->get_results($query7);
	
    $results = array_merge($results1, $results2, $results3, $results4, $results5, $results6, $results7);


    return $results;
}

function update_api_database() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'api_database';

    $api_items = get_api_items();

    forEach($api_items as $item) {
        $wpdb->insert(
        $table_name,
            array(
                'name' => $item[0],
                'date' => $item[1],
                'value' => $item[2],
                'percentage' => $item[3]
            )
        );
    }
}

function test_page() {
    if (is_page('api-test-page')) {
    }
}

add_action( 'wp', 'schedule_update_database' );
function schedule_update_database() {
    if ( ! wp_next_scheduled( 'update_database_api_event' ) ) {
        wp_schedule_event( time(), 'daily', 'update_database_api_event' );
    }
}

function register_api_route() {
    register_rest_route('dados-mercado-api', 'data', [
        'methods' => 'GET',
        'callback' => 'get_api_data'
    ]);
}

add_action( 'update_database_api_event', 'update_api_database');

register_activation_hook(__FILE__, 'create_the_custom_table');
add_action('template_redirect', 'test_page');
add_action('rest_api_init', 'register_api_route');
