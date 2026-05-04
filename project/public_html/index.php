<?php 
//security
  session_start();
  
  //only allow user enter by index.php
  define('is_allowed',true);
  define('private_path', __DIR__ .'/../private');

  if(!defined('private_path')){
    http_response_code(403);
    //die = exit
    die('path not finded');
  }

  //copy and paste for functions
  //current features(work in progress): create selector and calculator
  require_once private_path . '\processor\createCustomElement.php';






//----------------------------------------------main logic------------------------------------------------------------------------
?>




<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*Nome da empresa*</title>
    <link rel="stylesheet" href="finalesqueleto.css"/>
  </head>
  <body>

    <main>
        <!--menu-->
        <div class="css-menu-container">
          <nav class="css-menu" data-type="extendedBox" data-for="menu" data-targetboxid="menu">
            <div class="menu-header"></div>
            <div class="instrucao i8">texto aqui(login)</div>
            <div class="menu-divider m1"></div>
            <a href="esqueleto.html" class="test">test</a>
            <a href="https://github.com/ClaudioJian/CalculadoraFinanceira.git">teste-Github-Link</a>
            <div class="instrucao i9">outras páginas(outras páginas)</div>
            <div class="menu-divider m2"></div>
            <form>
              <input placeholder="test" name="test">
            </form>
            <div class="instrucao i10">texto aqui(histórico)</div>
            <div class="menu-divider m3"></div>
            <div class="resizer" data-direction="e"></div>
          </nav>
          <!--button for open menu-->
          <button style="height: fit-content;" class="img-button">
            <img class="css-img-menu" data-action="open-menu" data-type="extendable" src="image/img_placeholder.jpg" data-openboxid="menu">
          </button>
          <!--button for open calculator-->
          <button class="btn-open-calculator-out">
            <img src="image/img_placeholder.jpg" data-action="open-calculator" data-type="extendable" data-openboxid="calc">
          </button>
        </div>

        <!--calculator-->
        <?php
          create_custom_element('calculator',1);
        ?>

        
          

        
        <div class="instrucoes">
          <div class="instrucao i1">O que seria?</div>
          <div class="instrucao i2">Tipo?</div>
          <div class="instrucao i3">Valor depositado</div>
          <div class="instrucao i4">Prazo</div>
          <div class="instrucao i5">Gráfico:</div>
          <div class="instrucao i6">Valores posteriores seguintes</div>
          <div class="instrucao i7">Porcentual do valor</div>
        </div>

        <!--selector-->
        <?php
          create_custom_element('selector',3);
        ?>

        <div class="insert">
          <input type="number" class="quantidade" placeholder="R$1000" required></input>
          <input type="number" class="q_posterior" placeholder="R$1000" required></input>
          <input type="number" class="q_porcentual" placeholder="101,5%" required ></input>
        </div>

        <!--selector-->
        <?php
          //loadQuant tell how many element you want to load from there, data for element must placed in order
          create_custom_element('selector',2);
        ?>

      
      <?php
          create_custom_element('selector',4);
        ?>

        <?php
          create_custom_element('selector',5);
        ?>

        <!--buttons-->
        <div class="agrupment_button">
          <button type="button" class="button calc">Calcular</button>
          <button type="button" class="button clear">Limpar tudo</button>
          <button type="button" class="button reset">Resetar</button>
        </div>

      </main>
      <script src="main.js"></script>
  </body>
</html>

