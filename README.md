# Warehouse

## Getting started

Warehouse is an open source project which has been developed to learn Zend Framework 2. It is composed with five main functionalities:
* an inventory with available articles
* a manual in/out stock management
* a shopping list
* a recipes list with the ingredients based on articles referenced in the database
* a web service to download the shopping list on a mobile device. The apps (iOS) is in development and will be coming soon.

The user is invited to connect with credentials:

![authentication](/images/login.png)

Then he is able to navigate on the site using the menu on the top bar:

![welcome](/images/welcome.png)

## Build with

* Php using Zend Framework 2
* CSS
* jQuery
* MySQL

## Technical details

The application implements:

* Doctrine module for data persistence 
* Authentication service (to authenticate users)
* Service manager (to send emails and for tracability)
* PDF generator

All the explanation about these implementations are written in the [Wiki](https://github.com/FlorenceColas/warehouse/wiki) part.

## Contributors

If you are having any good suggestions, just drop me a line [:email:](http://nostradomus.ddns.net/contactform.html).
If feasible, I'll be happy to implement proposed improvements.
And if you are having lots of time, I'll be happy to share the work with you ;-).

When you create your own version, don't forget to send us a nice screenshot, or a link to your implementation. We'll be happy to publish it in the :confetti_ball:Hall of Fame:confetti_ball:.

## License

This project is under MIT License. 

## PDF generator
wkhtmltopdf. based on QT which is very powerful to generate PDF.
install from
[https://wkhtmltopdf.org/downloads.html](https://wkhtmltopdf.org/downloads.html)

create a config entry ['path']['wkhtmltopdf'] with the path of the installation
i.e on mac
```
return [
    'path' => [
        'wkhtmltopdf' => '/usr/local/bin/wkhtmltopdf',
    ],
```

In the module "Common", there are the classes requires to use the pdf adapter/renderer, with its own configuration file.
```
return [
    'service_manager' => [
        'factories' => [
            PdfAdapter::class  => PdfAdapterFactory::class,
            PdfRenderer::class => PdfRendererFactory::class,
        ],
    ],
];
```

Paths to the templates, in global.php:
to store the pdf file and the temporary folder to generate the html to convert
```
return [
    'path' => [
        'recipe_public_pdf'          => APPLICATION_PATH . '/public/recipes',
        'tmp'                        => APPLICATION_PATH . '/data/tmp',
    ],
],
```

Path to the html templates, in module.config.php:
```
return [
    'templates'       => [
        'extension' => 'phtml',
        'paths'     => [
            'recipe' => [__DIR__ . '/../templates/recipe'],
        ],
    ],
];
```

Config fot the template renderer class, in module.config.php:
```
return [
    'service_manager' => [
        'factories' => [
            TemplateRendererInterface::class => PlatesRendererFactory::class,
        ],
    ],
```

To use it:
The controller need dependances injections for:
Factory:
```
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MyControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        $pdfAdapter       = $container->get(PdfAdapter::class);
        ...
    }
}
```

In the MyController.php:
```
// read the needed data
$this->data = [
    'id' => 123,
    'description' => 'my recipe',
];

// generate the html
$html = $this->templateRenderer
    ->render(
        'template_path::template_name', [
        'data' => $this->data,
    ]);

// generate the pdf content
$this->data = $this->pdfAdapter
    ->setContent($html, $this->config['path']['tmp'])
    ->render();

// create the pdf file
$file = $this->config['path']['recipe_public_pdf'] . '/' . 'recipe.pdf';
$result = file_put_contents($file, $this->data);

 ```