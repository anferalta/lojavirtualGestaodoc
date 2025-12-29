<?php

namespace App\Suporte;

use Twig\Lexer;
use App\Core\Helpers;
use App\Controllers\UsuarioController;

class Template 
{
   private \Twig\Environment $twig;
    
    public function __construct(string $diretorio)
    {
        $loader = new \Twig\Loader\FilesystemLoader($diretorio);
        $this->twig = new \Twig\Environment($loader);
        
        $lexer = new Lexer($this->twig, array(
        $this->helpers()
        ));
        $this->twig->setLexer($lexer);
                
    }
    
    public function renderizar(string $view, array $dados): string
    {
        return $this->twig->render($view, $dados);
    }
    
    private function helpers(): void
    {
        array(
            $this->twig->addFunction(
                    new \Twig\TwigFunction('url', function(string $url = null){
                                return Helpers::url($url);
                    })
            ),
            
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('saudacao', function(){
                                return Helpers::saudacao();
                    })
            ),
            
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('resumirTexto', function (string $texto, int $limite) {
                                return Helpers::resumirTexto($texto, $limite);
                    })
            ),
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('flash', function () {
                                return Helpers::flash();
                    })
            ),
            
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('dataAtual', function () {
                                return Helpers::dataAtual();
                    })
        ),
                            
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('usuario', function () {
                                return UsuarioControlador::usuario();
                    })
            ),
                            
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('contarTempo', function (string $data) {
                                return Helpers::contarTempo($data);
                    })
            ),
                            $this->twig->addFunction(
                    new \Twig\TwigFunction('formatarNumero', function (int $numero) {
                                return Helpers::formatarNumero($numero);
                    })
            ),
                                $this->twig->addFunction(
                    new \Twig\TwigFunction('tempoCarregamento', function () {
                                
                        $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
                        return number_format($time, 4);
                    })
            )
      );
    } 
}
