# Albe Timeline

Plugin jQuery para timeline, simples, mas altamente personalizável. Vai ajudar você a renderizar uma linha de tempo responsiva (CSS e HTML) a partir de dados JSON. 

As informações serão automaticamente agrupadas por ano e a série de eventos serão classificados por data. 

Além disso, este plugin é capaz de lidar com praticamente qualquer tipo de conteúdo, tais como imagens, vídeos, áudios e muito mais.

## Licença de uso
O plugin é de código aberto e liberado para uso comercial sem custo. Peço somente que [me comunique] (http://albertino.eti.br "contato") caso implementá-lo em algum lugar, para que eu possa dar uma olhada ou adicioná-lo aqui como demostração.

## Demostração
| HORIZONTAL  | VERTICAL |
| ------------- | ------------- |
| [Template 1](http://timeline.albertino.eti.br/templates/horizontal/index.html "Template Horizontal")  | [Template 1](http://timeline.albertino.eti.br/templates/vertical/index.html "Template Vertical")  |
|   | [Template 2](http://timeline.albertino.eti.br/templates/simple/index.html "Template Simples")  |
|   | [Template 3](http://timeline.albertino.eti.br/templates/audain/index.html "Template Audain Designs")  |

## Requisitos
* Necessário
[Jquery](https://jquery.com)
* Opcional
[Animate CSS](https://daneden.github.io/animate.css)

## Como usar
#### Carregue o plugin e dependências
```html
<link rel="stylesheet" href="style-albe-timeline.css" />

<script src="https://cdn.jsdelivr.net/jquery/1.11.1/jquery.min.js"></script>
<script src="jquery-albe-timeline-1.1.2.min.js"></script>
```
#### Crie a lista de dados
```js
<script type="text/javascript">

   //Json Object
   var data = [{
         time: '2015-03-29',
         header: 'Sample of header',
         body: [{
               tag: 'h1',
               content: 'Lorem ipsum'
            },
            {
               tag: 'p',
               content: 'Lorem ipsum dolor sit amet, nisl lorem.'
         }],
         footer: 'Sample of footer'
      },
      {
         time: '2016-01-20',
         body: [{
               tag: 'h2',
               content: 'Sample with link'
            },
            {
               tag: 'a',
               content: 'MY LINK',
               attr: {
                  href: 'http://albertino.eti.br',
                  target: '_blank',
                  title: 'Albertino Júnior'
               }
         }]
      }
   ];

</script>
```
#### Realize a chamada
```html
<div id="myTimeline"></div>
```
```js
<script type="text/javascript">

  //Json Object
  var data = [{...}];
   
  $(document).ready(function () {
      $('#myTimeline').albeTimeline(data);
  });
  
</script>
```
* Instancie com opções:
```js
  //**myTimeline**, define o identificador do elemento que irá receber toda a linha de tempo (por exemplo, uma DIV) e deve ser único para cada timeline na página.
  //**data**, define o objeto Json contendo a lista de dados a serem exibidos.
  
  $("#myTimeline").albeTimeline(data, {
    effect: "fadeInUp",
    //Efeito de apresentação dos itens
    //"fadeInUp", "bounceIn", "fadeInUp", etc
    showMenu: true,
    //Define a exibição de um menu com ancora para os agrupamentos de anos
    language: "pt-br",
    //Especifica a linguagem de exibição dos textos
    //"pt-br", "en-us", "es-es"
    formatDate : 1,
    //Define o formato de exibição da data
    //1:"dd MMMM"
    //2:"dd/MM/aaaaa"
    //3:"dd de MMMM de aaaaa"
    //4:"DD, dd de MMMM de aaaaa"
    sortDesc: true,
    //Especifica se os dados serão ordenados pela data ou exibidos exatamente como estão
  });
```
#### Estrutura Html
Usando os padrões do plugin, teremos um resultado parecido com isso:
```html
  <div id="myTimeline">
    <section id="timeline">
      <div id="year2016" class="group2016">2016</div>
      <article class="animated fadeInUp">
        <div class="panel">
          <div class="badge">20 Jan</div>
          <div class="panel-body">
            <img src="../img/qrcode.png" width="150px" class="img-responsive">
            <h2>Sample with image</h2>
            <p>Lorem ipsum dolor sit amet, nisl lorem.</p>
          </div>
        </div>
      </article>
      <div id="year2015" class="group2015">2015</div>
      <article class="animated fadeInUp inverted">
        <div class="panel">
          <div class="badge">29 Mar</div>
          <div class="panel-heading">
            <h4 class="panel-title">Sample of header</h4>
          </div>
          <div class="panel-body">
            <h1>Lorem ipsum</h1>
            <p>Lorem ipsum dolor sit amet, nisl lorem.</p>
          </div>
          <div class="panel-footer">Sample of footer</div>
        </div>
      </article>
      <article class="animated fadeInUp">
        <div class="panel">
          <div class="badge">&nbsp;</div>
        </div>
      </article>
      <div class="clearfix" style="float: none;"></div>
    </section>
  </div>
```
## Notas
* O objeto Json também é aceito no formato de string. Por exemplo:
```js
$('#myTimeline').albeTimeline('[{"time": "2016-01-20", "body": [{ "tag": "h1", "content": "Lorem ipsum" }, { "tag": "p", "content": "massa, cursus quisque leo quisque dui." }]}]');
```

* Você pode acessar o console de depuração do navegador para verificar o json ordenado.
  
* O elemento **time** deve atender ao padrão ISO 8601 sempre no formato ano-mês-dia 

  "yyyy-mm-dd"

* Caso haja a necessidade de passar uma classe CSS como atributo do elemento, utilize o nome da prorpiedade **cssclass**. Por exemplo:
```js
  body: [{
    tag: 'img',
    attr: {
      src: '../img/qrcode.png',
      width: '150px',
      cssclass: 'img-responsive'
    }
  }
```
