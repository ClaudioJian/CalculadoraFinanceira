<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*Nome da empresa*</title>
    <link rel="stylesheet" href="esqueleto.css"/>
  </head>
  <body>
      <?php 
        //copy and paste for functions
        //current features(work in progress): create selector and calculator
        include './processor/createCustomElement.php';
       ?>
      <main>
        <!--menu-->
        <div class="css-menu-container">
          <div class="css-menu" data-type="extendedBox" data-for="menu" data-targetboxid="menu">
            <a href="esqueleto.html">test</a>
            <a href="https://github.com/ClaudioJian/CalculadoraFinanceira.git">teste-Github-Link</a>
            <div class="resizer js-right"></div>
          </div>
          <!--button for open menu-->
          <div style="height: fit-content;">
            <img class="css-img-menu" data-action="open-menu" data-type="extendable" src="image/img_placeholder.jpg" data-openboxid="menu">
          </div>
          <!--button for open calculator-->
          <div class="btn-calculator-out">
            <img src="image/img_placeholder.jpg" data-action="open-calculator" data-type="extendable" data-openboxid="calc">
          </div>
        </div>

        <!--calculator-->
        <div class="calculator-container" data-type="extendedBox" data-for="calculator" data-targetboxid="calc">
          <!--button for hide calculator-->
          <div class="btn-calculator-inside">
            <img src="image/img_placeholder.jpg" data-action="open-calculator" data-type="extendable" data-openboxid="calc">
          </div>
          
          <!--resizer-->  
          <div>
            <div class="resizer js-top"></div>
            <div class="resizer js-left"></div>
            <div class="resizer js-right"></div>
            <div class="resizer js-bottom"></div>

            <div class="resizer js-top js-left"></div>
            <div class="resizer js-top js-right"></div>
            <div class="resizer js-bottom js-left"></div>
            <div class="resizer js-bottom js-right"></div>
          </div>
          <!--body of calculator-->
          <div class="draggable" data-for="draggable">
            <p>Calculadora</p>
          </div>

        </div>
        
          

        
        <div class="instrucoes">
          <div class="instrucao i1">O que seria?</div>
          <div class="instrucao i2">Tipo?</div>
          <div class="instrucao i3">Valores investidos<br> sequintes</div>
          <div class="instrucao i4">Prazo</div>
          <div class="instrucao i5">Tipo de gráfico</div>
        </div>

        <!--selector-->
        <?php
          //loadQuant tell how many element you want to load from there, data for element must placed in order
          create_custom_element('selector',3);
        ?>

        <div class="insert">
          <div class="prazo"></div>
        </div>

        <!--selector-->
        <?php
          //loadQuant tell how many element you want to load from there, data for element must placed in order
          create_custom_element('selector',1);
        ?>




        <!--buttons-->
        <div class="agrupment_button">
          <button type="button" class="button calc">Calcular</button>
          <button type="button" class="button clear">Limpar tudo</button>
          <button type="button" class="button reset">Resetar</button>
        </div>

      </main>
      <script src="./main.js"></script>
  </body>
</html>


