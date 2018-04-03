<div>
    <h3>Buscar términos</h3>
    <form method='get'>
      <label for="termino">Término a buscar:</label>
      <input type='text' name='termino' />
      <input type='submit' value='Buscar'>
    </form>
  </div>
 <?php
  if(!empty($_GET['termino'])):
    $url  = 'http://es.wikipedia.org/w/api.php';
    $url .= '?action=query';
    $url .= '&list=search';
    $url .= '&format=xml';
    $url .= '&redirects';
    $url .= '&srsearch=' . urlencode($_GET['termino']);
  	  $lista_paginas = file_get_contents($url);
  ?>
  <hr>
  <div>
    <h3>Listado de páginas con el término "<?php echo $_GET['termino']; ?>"</h3>
    <ul>
      <?php 
      $xml = new SimpleXMLElement($lista_paginas);
      foreach($xml->query->search->children() as $pag) {
        $params  = 'termino=' . $_GET['termino'];
        $params .= '&pag=' . urlencode($pag['title']);
        echo "<li><a href='?$params'>" . $pag['title'] . "</a></li>";
      }
      ?>
    </ul>
  </div>
   <?php
  if(!empty($_GET['pag'])):
    $url  = 'http://es.wikipedia.org/w/api.php';
    $url .= '?action=parse';
    $url .= '&prop=text';
    $url .= '&format=xml';
    $url .= '&redirects';
$url .= '&page=' . urlencode($_GET['pag']);
    $pagina = file_get_contents($url);
  ?>
  <hr>
  <div>
    <h3>Contenido de la página "<?php echo $_GET['pag']; ?>"</h3>
    <?php echo htmlspecialchars_decode($pagina); ?>
  </div>
  <?php endif; endif; ?>

