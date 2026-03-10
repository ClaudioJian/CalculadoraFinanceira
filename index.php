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
          <nav class="css-menu" data-type="extendedBox" data-for="menu" data-targetboxid="menu">
            <a href="esqueleto.html">test</a>
            <a href="https://github.com/ClaudioJian/CalculadoraFinanceira.git">teste-Github-Link</a>
            <div class="resizer js-right"></div>
          </nav>
          <!--button for open menu-->
          <button style="height: fit-content;">
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
          <div class="instrucao i3">Valores investidos<br> sequintes</div>
          <div class="instrucao i4">Prazo</div>
          <div class="instrucao i5">Tipo de gráfico</div>
        </div>

        <!--selector-->
        <?php
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
      <script src="main.js"></script>
  </body>
</html>


