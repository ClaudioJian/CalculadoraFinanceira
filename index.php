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
        
          

        


        <!--selector-->
        

        <?php
          //loadQuant tell how many element you want to load from there, data for element must placed in order
          create_custom_element('selector',4);
        ?>




        <div class="instrucoes">
          <div class="instrucao i1">O que seria?</div>
          <div class="instrucao i2">Tipo?</div>
          <div class="instrucao i3">Valores investidos<br> sequintes</div>
          <div class="instrucao i4">Prazo</div>
          <div class="instrucao i5">Tipo de gráfico</div>
        </div>

        <!-- Instrução para criar selector
          1. somente elementos interno do selector e interno do css-container-itens com data-type iguais serão visível
          2. data-depencityID: busca o data-type e mostra somente o itens com mesmo data-type
          3. data-filterID: deixa os itens com mesma data-depencity mostrar itens com mesmo data-type
          4. data-depencitytype: busca no selector indicado por data-filterID o data-type para indentificação
          5. se tem data-depencityID, deve colocar <div></div> no selector e colocar data-depencitytype sem nenhum conteúdo
          6. se selector tem data-type="extendable" -> só pode existir um elemento abaixo dele, coloque <div></div> vazio
          7. para selector sem depedêcia com outro, não coloca data-depencity
          8. data-order deve ser diferente para mesmo selector(para diferentes selector, pode repetir)
          9. data-type="all" mostra todos, can only exist if it has data-type
          10.data-type só precisa existir se tem data-filterID
          11. data-order ordena os items conforme número
        -->
        <div class="selector-container s1">
          <div class="selector" data-action="open-selector" data-type="extendable" data-filterID="0">
            <div class="investimento" data-type="investimento" data-order="0">Investimento</div>
          </div>
          <!--items-->
          <div class="css-container-itens" data-type="extendedBox">
            <div class="imposto" data-type="imposto" data-order="1">Imposto</div>
          </div>
        </div>

        <div class="selector-container s2">
          <div class= "selector" data-action="open-selector" data-type="extendable" data-filterID="1">
            <div></div>
          </div>
          <!--items-->
          <div class="css-container-itens" data-type="extendedBox" data-depencityID="0">
            <div data-type="all" data-order="0">ALL</div>
            <div class="investimento_B" data-depencitytype="investimento" data-type="investimento_B" data-order="1">investimento tipo B-need investimento</div>
            <div class="investimento_C" data-depencitytype="investimento" data-type="investimento_C" data-order="2">investimento tipo C-need investimento</div>
            <div class="imposto_A" data-depencitytype="imposto" data-type="imposto_A" data-order="3">Imposto tipo A-need imposto</div>
            <div class="imposto_B" data-depencitytype="imposto" data-type="imposto_B" data-order="4">Imposto tipo B-need imposto</div>
          </div>
        </div>

        <div class="selector-container s3">
          <div class= "selector" data-action="open-selector" data-type="extendable">
            <div></div>
          </div>
          <!--items-->
          <div class="css-container-itens" data-type="extendedBox" data-depencityID="1">
            <div class= "imposto_B" data-depencitytype="imposto_B" data-order="1">imposto tipo A-need imposto tipoB</div>
            <div class= "imposto_B" data-depencitytype="imposto_A" data-order="2">imposto tipo B-need imposto tipoA</div>
            <div class= "investimento_C" data-depencitytype="investimento_B" data-order="3">imposto tipo C-need investimento tipoB</div>
            <div class="investimento_A" data-depencitytype="investimento_C" data-order="4">imposto tipo A-need investimento tipoC</div>
          </div>
        </div>

        <div class="insert">
          <div class="prazo"></div>
        </div>

        <div class="selector-container s4">
          <div class="selector" data-action="open-selector" data-type="extendable">
            <div class="gráfico g1" data-order="0">Test0</div>
          </div>
          <!--items-->
          <div class="css-container-itens" data-type="extendedBox">
            <div class="gráfico g2" data-order="1">Test1</div>
            <div class="gráfico g3" data-order="2">Test2</div>
          </div>
        </div>

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


