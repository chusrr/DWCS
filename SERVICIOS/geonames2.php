<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Consulta de información geográfica</title>
<style>
input[type='text'] {
  width: 80px;
  text-align: center;
}
</style>
</head>
<body>
  <h2>Consulta de información geográfica</h2>
  <hr>
  <div>
    <h3>Buscar un lugar por topónimo</h3>
    <form method='get'>
      <label for='toponimo'>Topónimo a buscar:</label>
      <input type='text' name='toponimo' />
      <input type='submit' value='Busca'>
    </form>
  </div>
  <?php
  if(!empty($_GET['toponimo'])):
    // Construímos a URL para acceder ao servizo
    $url  = 'http://api.geonames.org/search';
    $url .= '?username=daw_fp'; // Indicar o nome de usuario rexistrado
    $url .= '&country=es';
    $url .= '&lang=es';
    $url .= '&maxRows=10';
    $url .= '&q=' . urlencode($_GET['toponimo']);
    // Buscamos lugares que coincidan co topónimo desexado
    $lista_toponimos = file_get_contents($url);
  ?>
  <hr>
  <div>
    <h3>Listado de páginas con el topónimo "<?php echo $_GET['toponimo']; ?>"</h3>
    <ul>
      <?php 
      $xml_lista = new SimpleXMLElement($lista_toponimos);
      // Facemos unha lista HTML cos topónimos obtidos na busca
      foreach($xml_lista->geoname as $geoname) {
        $params  = 'toponimo=' . $_GET['toponimo'];
        $params .= '&lugar=' . urlencode($geoname->name);
        $params .= '&id=' . urlencode($geoname->geonameId);
        echo "<li><a href='?$params'>" . $geoname->name . "</a></li>";
      }
      ?>
    </ul>
  </div>
  <?php
  if(!empty($_GET['id'])):
    // Buscamos información sobre o lugar indicado polo usuario
    $url  = 'http://api.geonames.org/get';
    $url .= '?username=daw_fp'; // Indicar o nome de usuario rexistrado
    $url .= '&lang=es';
    $url .= '&style=full';
    $url .= '&geonameId=' . urlencode($_GET['id']);
    $info = file_get_contents($url);
    $xml_info = new SimpleXMLElement($info);
  ?>
  <hr>
  <div>
    <h3>Datos de "<?php echo $_GET['lugar']; ?>"</h3>
    <ul>
      <li>País: <?php echo $xml_info->countryName; ?></li>
      <li>Rexión: <?php echo $xml_info->adminName1; ?></li>
      <li>Provincia: <?php echo $xml_info->adminName2; ?></li>
      <li>Poboación: <?php echo $xml_info->population; ?> habitantes</li>
      <li>Latitude entre <?php echo $xml_info->bbox->south; ?> e <?php echo $xml_info->bbox->north; ?></li>
      <li>Lonxitude entre <?php echo $xml_info->bbox->west; ?> e <?php echo $xml_info->bbox->east; ?></li>
    </ul>
    <?php
      // Creamos a URL para as coordenadas do lugar
      $imx  = 'http://maps.googleapis.com/maps/api/staticmap';
      $imx .= '?key=AIzaSyBIIyOyD2y6ojsuHmZNQtwKH6KT4SY3A-k'; // Sustituir coa clave de acceso obtida
      $imx .= '&zoom=13';
      $imx .= '&size=400x400';
      $imx .= '&center=' . $xml_info->lat . ',' . $xml_info->lng;
    ?>
    <img src='<?php echo $imx ?>'>
  </div>
  <?php endif; endif; ?>
</body>
</html>