<?php
/**
 * Plugin Name: Import Amministrazione Trasparente
 * Description: Un plugin per gestire l'importazione di dati per l'Amministrazione Trasparente.
 * Version: 1.0
 * Author: Dario Vettura - Ars Digitalia
 */

// Aggiungiamo il menu di importazione
function import_at_menu_item()
{
  add_menu_page(
    'Import Amministrazione Trasparente',
    'Import AT',
    'manage_options',
    'import-at',
    'import_at_page'
  );
}
add_action('admin_menu', 'import_at_menu_item');


function import_at_register_settings()
{
  register_setting('import-at-settings', 'at_key');
}
add_action('admin_init', 'import_at_register_settings');


function import_at_styles()
{
  wp_enqueue_style('import-at-styles', plugins_url('import-at-style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'import_at_styles');

// Funzione per controllare se esiste il cookie e renderizzare il contenuto corrispondente
function import_at_page()
{
  if (isset($_COOKIE['at_token'])) {
    // Se il cookie esiste, renderizza il contenuto autorizzato
    echo '<h1>Contenuto autorizzato per l\'importazione</h1>';
    echo '<a href="?logout_at=true" style="float:right;margin-right:10px">Logout</a>';
    echo '<h2>Impostazioni</h2>';
    echo '<form id="key-form" method="post">';
    echo '<label for="at_key">Chiave:</label>';
    echo '<input type="text" id="at_key" name="at_key" value="' . esc_attr(get_option('at_key')) . '">';
    echo '<input type="submit" value="Controlla documenti da importare">';
    echo '</form>';
    if (get_option('new_document')) {
      add_action('admin_init', 'render_document_information');
      render_document_information();
    }
  } else {
    // Se il cookie non esiste, renderizza il form di login
    echo '<h1>Effettua il login</h1>';
    echo '<form id="login-form" method="post">';
    echo '<label for="email_at">Email:</label>';
    echo '<input type="email" id="email" name="email_at" required>';
    echo '<label for="password_at">Password:</label>';
    echo '<input type="password" id="password_at" name="password_at" required>';
    echo '<input type="submit" value="Login">';
    echo '</form>';
  }
}


// Gestione della richiesta di login
function handle_login_request()
{
  if (isset($_POST['email_at']) && isset($_POST['password_at'])) {
    $email = $_POST['email_at'];
    $password = $_POST['password_at'];
    $response = wp_remote_post(
      'https://reserved-area.vercel.app/api/login',
      array(
        'body' => json_encode(array('email' => $email, 'password' => $password)),
        'headers' => array('Content-Type' => 'application/json'),
      )
    );

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
      $body = json_decode($response['body']);
      if (isset($body->token)) {
        setcookie('at_token', $body->token, time() + (86400 * 30), '/');
        wp_redirect(admin_url('admin.php?page=import-at'));
        exit();
      }
    }
  }
}
add_action('admin_init', 'handle_login_request');

// Funzione per gestire il logout
function handle_logout_request()
{
  if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Cancella il cookie 'at_token'
    setcookie('at_token', '', time() - 3600, '/');
    // Redirect per evitare il reinvio del modulo
    wp_redirect(admin_url('admin.php?page=import-at'));
    exit();
  }
}
add_action('init', 'handle_logout_request');

// Funzione per gestire il salvataggio della chiave

function handle_key_save_request()
{
  if (isset($_POST['at_key'])) {
    $token = $_COOKIE['at_token'];
    $response = wp_remote_post(
      'https://reserved-area.vercel.app/api/traspa',
      array(
        'body' => json_encode(array('key' => $_POST['at_key'])),
        'headers' => array(
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $token, // Includi il cookie 'at_token' come token di autorizzazione
        ),
      )
    );

    if (!is_wp_error($response) && $response['response']['code'] === 200) {
      $body = wp_remote_retrieve_body($response);
      $data_array = json_decode($body, true);
      update_option('new_document', $data_array);

    } else {
      $error_message = is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response);
      echo $error_message;
      update_option('at_key', null);
      update_option('new_document', null);
    }
  }
}
add_action('admin_init', 'create_post_request');


// Funzione per ottenere il json
function getData()
{
  $key = get_option('at_key');
  $token = $_COOKIE['at_token'];
  $response = wp_remote_post(
    'https://reserved-area.vercel.app/api/traspa',
    array(
      'body' => json_encode(array('key' => $key)),
      'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $token, // Includi il cookie 'at_token' come token di autorizzazione
      ),
    )
  );

  if (!is_wp_error($response) && $response['response']['code'] === 200) {
    // Se la richiesta ha avuto successo, salva la chiave nelle opzioni
    //update_option('at_key', $key);
    $body = wp_remote_retrieve_body($response);
    return $body;
  } else {
    $error_message = is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response);
    return $error_message;
  }
}


// Funzione per il rendering delle informazioni sui documenti (nel backoffice plugin)
function render_document_information()
{
  $data_array = get_option('new_document');
  if ($data_array) {
    $total_documents = count($data_array);
    echo '<p class="info">Data ultimo aggiornamento: ' . date("d/m/Y H:i:s", strtotime("+1 hour")) . '</p>';
    echo '<p class="success">Numero di Documenti importati: ' . esc_html($total_documents) . '</p>';
  } else {
    echo "Nessun documento";
  }
}








// Aggiungi le regole di riscrittura
function axios_rewrite_rules()
{
  add_rewrite_rule('^amministrazione-trasparente/axios/?$', 'index.php?axios_template=1', 'top');
  add_rewrite_rule('^amministrazione-trasparente/axios/([^/]+)/?$', 'index.php?axios_template=2&dynamic_content=$matches[1]', 'top');
  add_rewrite_rule('^amministrazione-trasparente/axios/([^/]+)/([^/]+)/?$', 'index.php?axios_template=3&dynamic_text=$matches[1]&dynamic_id=$matches[2]', 'top');
}
add_action('init', 'axios_rewrite_rules');


function axios_query_vars($query_vars)
{
  $query_vars[] = 'axios_template';
  $query_vars[] = 'dynamic_content';
  $query_vars[] = 'dynamic_text';
  $query_vars[] = 'dynamic_id';
  return $query_vars;
}
add_filter('query_vars', 'axios_query_vars');


function axios_template_include($template)
{
  $axios_template = get_query_var('axios_template');
  if ($axios_template) {
    switch ($axios_template) {
      case 1:
        $template = plugin_dir_path(__FILE__) . 'at-axios-temp.php';
        break;
      case 2:
        $template = plugin_dir_path(__FILE__) . 'tax-at-axios.php';
        break;
      case 3:
        $template = plugin_dir_path(__FILE__) . 'at-axios-temp.php';
        break;
    }
  }
  return $template;
}
add_filter('template_include', 'axios_template_include');



function the_archive_title_axios()
{
  $url = $_SERVER['REQUEST_URI'];
  $parts = explode('/', rtrim($url, '/'));
  $lastPart = end($parts);
  $lastPart = ucfirst(str_replace("-", " ", $lastPart));
  echo '<h2>' . $lastPart . '</h2>';
}




