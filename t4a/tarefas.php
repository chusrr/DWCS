<?php
session_start();

require_once 'Google/Client.php';
require_once 'Google/Service/Tasks.php';

// Parámetros para a autorización da aplicación
$ID_cliente = 'qchphp.apps.googleusercontent.com';
$clave_secreta = '690967246016-45pra8caq401vh3hv419gupc81';
$URL_redireccion = 'http://www.dominio.local/t4a/oauth2callback.php';

// Creamos o cliente dos servizos de Google
$cliente_google = new Google_Client();
$cliente_google->setClientId($ID_cliente);
$cliente_google->setClientSecret($clave_secreta);
$cliente_google->setRedirectUri($URL_redireccion);

// E o servizo de tarefas
$cliente_google->addScope(Google_Service_Tasks::TASKS);
$servizo_tarefas = new Google_Service_Tasks($cliente_google);
$num_tarefas = 0;

// Cando se pulsa no botón de logout, borramos o código de acceso da sesión
if(isset($_GET['logout'])) {
  unset($_SESSION['codigo_acceso']);
}

// Cando se obtén o código de autorización
if (isset($_GET['code'])) {
  // O empregamos para obter o código de acceso, que gardamos na sesión
  $cliente_google->authenticate($_GET['code']);
  $_SESSION['codigo_acceso'] = $cliente_google->getAccessToken();
  // E recargamos a páxina
  $url_recarga = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($url_recarga, FILTER_SANITIZE_URL));
}

// Se temos o código de acceso na sesión, o empregamos
if (isset($_SESSION['codigo_acceso']) && $_SESSION['codigo_acceso']) {
  $cliente_google->setAccessToken($_SESSION['codigo_acceso']);
}
else {
  // E se non o temos, xeramos a URL para o enlace de Login
  $URL_autentificacion = $cliente_google->createAuthUrl();
}

// Cando temos o código de acceso
if ($cliente_google->getAccessToken()) {
  // Se está expirado, forzamos a redirección á páxina de autorización
  if($cliente_google->isAccessTokenExpired()) {
    header('Location:'.$cliente_google->createAuthUrl());
    exit();
  }

  // Se recibimos o parámetro GET novalista, creamos unha lista nova
  if(isset($_GET['novalista']) && $_GET['novalista']) {
    $nova_lista = new Google_Service_Tasks_TaskList();
    $nova_lista->setTitle($_GET['novalista']);
    $servizo_tarefas->tasklists->insert($nova_lista, array());
  }

  // Se recibimos o parámetro GET borralista, borramos a lista co ID indicado
  if(isset($_GET['borralista']) && $_GET['borralista']) {
    $servizo_tarefas->tasklists->delete($_GET['borralista'], array());
  }

  // Se recibimos o parámetro GET novatarefa, creamos unha tarefa nova
  if(isset($_GET['novatarefa']) && $_GET['novatarefa']) {
    $nova_tarefa = new Google_Service_Tasks_Task();
    $nova_tarefa->setTitle($_GET['novatarefa']);
    $servizo_tarefas->tasks->insert($_GET['lista'], $nova_tarefa, array());
  }

  // Se recibimos o parámetro GET borratarefa, borramos a tarefa co ID indicado
  if(isset($_GET['borratarefa']) && $_GET['borratarefa']) {
    $servizo_tarefas->tasks->delete($_GET['lista'], $_GET['borratarefa'], array());
  }

  // Se recibimos o parámetro GET lista, obtemos as tarefas da lista co ID indicado
  if(isset($_GET['lista']) && $_GET['lista']) {
    $tarefas_lista = $servizo_tarefas->tasks->listTasks($_GET['lista'], array());
    $nome_lista = $servizo_tarefas->tasklists->get($_GET['lista'], array())->getTitle();
    @$num_tarefas = $tarefas_lista->count();
  }

  // Creamos a lista de tarefas
  $listas_tarefas = $servizo_tarefas->tasklists->listTasklists();
  $_SESSION['codigo_acceso'] = $cliente_google->getAccessToken();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Xestión de listas de tarefas</title>
<style>
input[type='text'] {
  width: 80px;
  text-align: center;
}
a.pequeno {
  font-size: x-small;
}
</style>
</head>
<body>
  <h2>Xestión de listas de tarefas</h2>
  <?php if (isset($URL_autentificacion)): ?>
  <a href='<?php echo $URL_autentificacion; ?>'>Iniciar sesión</a>
  <?php else: ?>
  <a href='?logout'>Logout</a>
  <hr>

  <div>
    <h3>Listas de tarefas</h3>
    <?php
      if(isset($listas_tarefas)):
        echo '<ul>';
        foreach($listas_tarefas as $l) {
          $id_lista = urlencode($l->getId());
          echo "<li><a href='?lista=$id_lista'>" . $l->getTitle() . "</a>";
          echo " <a href='?borralista=$id_lista' class='pequeno'>(Borrar)</a></li>";
        }
        echo '</ul>';
    ?>
      <form method='get'>
        <label for='novalista'>Engadir unha nova lista con nome:</label>
        <input type='text' name='novalista' />
        <input type='submit' value='Crear'>
      </form>
    <?php endif; ?>
  </div>
  <hr>
  <div>
   <?php if($num_tarefas): ?>
    <h3>Listado de tarefas na lista "<?php echo $nome_lista; ?>"</h3>
    <ul>
      <?php
        foreach($tarefas_lista as $t) {
          $id_tarefa = urlencode($t->getId());
          $id_lista = urlencode($_GET['lista']);
          echo "<li>" . $t->getTitle();
          echo " <a href='?borratarefa=$id_tarefa&lista=$id_lista' class='pequeno'>(Borrar)</a></li>";
        }
      ?>
    </ul>
    <?php endif; ?>
    <?php if(!empty($_GET['lista'])): ?>
      <h3>A lista de tarefas "<?php echo $nome_lista; ?>" está baleira</h3>
    <form method='get'>
      <label for='novatarefa'>Engadir unha nova tarefa co texto:</label>
      <input type='text' name='novatarefa' />
      <input type='hidden' name='lista' value='<?php echo urlencode($_GET['lista']); ?>' />
      <input type='submit' value='Crear'>
    </form>
    <?php endif;  ?>
  </div>
  <?php endif ?>
</body>
</html>
