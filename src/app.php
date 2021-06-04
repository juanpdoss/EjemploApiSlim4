<?php
require_once (__DIR__ . "/../vendor/autoload.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//los dos use de abajo corresponden al middleware

use Psr\Http\Server\RequestHandlerInterface as RequestHandler; 
use Slim\Psr7\Response as ResponseMW;
use Slim\Factory\AppFactory;

require_once(__DIR__ ."/../entidades/bionicle.php");

$app = AppFactory::create();


use Slim\Routing\RouteCollectorProxy;

$app->group('/Bionicle', function(RouteCollectorProxy $grupo)
{
    $grupo->get('/',Bionicle::class. ':TraerTodos');
    $grupo->get('/{id}', \Bionicle::class. ':TraerUno');
    $grupo->post('/',\Bionicle::class. ':AgregarUno');
    $grupo->put('/{parametros}',Bionicle::class .':ModificarUno');
    $grupo->delete('/',\Bionicle::class.':BorrarUno');

})/* ->add(function (Request $request, RequestHandler $handler) : ResponseMW
    {
        $var=$request->getBody();
        $var.="Esto lo agregue en mi middleware";

        $respuesta=new ResponseMW();

        $respuestaDelVerbo=$handler->handle($request)->getBody();
        $respuesta->getBody()->write($var."<br> esto viene desde el verbo: " .$respuestaDelVerbo);

        return $respuesta;

    }

) */; //descomentar para testear middleware a nivel de grupo en la ruta /Bionicle

$app->group('/credenciales', function(RouteCollectorProxy $grupo)
{
    $grupo->get('/',function(Request $request, Response $response,array $args):Response
    {
        $response->getBody()->write("estoy parado dentro del verbo: GET");


        return $response;
    });
 
    $grupo->post('/', function(Request $request, Response $response,array $args):Response
    {   

        $response->getBody()->write("estoy parado dentro del verbo: POST <br>");

        return $response;
    });



})->add(function (Request $request, RequestHandler $handler) : ResponseMW
    {
        $respuesta=new ResponseMW();

        if($request->getMethod()=="GET")
        {
            $texto="Estoy parado en el middleware, no se necesitan credenciales para el get <br>";
            $textoQueVieneDelGet=(string)$handler->handle($request)->getBody();

            $texto.=$textoQueVieneDelGet.="<br> volviendo al middleware antes de salir";


            $respuesta->getBody()->write($texto);

        }else if($request->getMethod()=="POST")
        {
            $datosDelRequest=$request->getParsedBody();

            if($datosDelRequest["perfil"]=="administrador")
            {
                $textoRespuesta="nombre: {$datosDelRequest["nombre"]}, el perfil es {$datosDelRequest["perfil"]} puede seguir al verbo POST..<br>";

                $textoRespuesta.=(string)$handler->handle($request)->getBody();

                $textoRespuesta.="vuelvo al middleware antes de salir";


                $respuesta->getBody()->write($textoRespuesta);
            }


        }



        return $respuesta;

    });


require_once(__DIR__."/../entidades/Validador.php");

//ejemplo del uso de un middleware definido en una clase
$app->group("/json",function(RouteCollectorProxy $grupo)
{
    $grupo->get('/',function (Request $request,Response $response,array $args):Response
    {

        $cuerpoRespuesta=new stdClass();
        $cuerpoRespuesta->mensaje="API=>GET";
        $cuerpoRespuesta->status=200;

        $response->getBody()->write(json_encode($cuerpoRespuesta));

       return $response; 
    });

    $grupo->post('/',function (Request $request,Response $response,array $args):Response
    {
        $cuerpoPeticion=$request->getParsedBody();
        $cuerpoRespuesta=new stdClass();
        $cuerpoRespuesta->nombre=$cuerpoPeticion["nombre"];
        $cuerpoRespuesta->perfil=$cuerpoPeticion["perfil"];

        $response->getBody()->write(json_encode($cuerpoRespuesta));


        return $response;
    }); 



})->add(Validador::class . ":VerificarVerbo");

$app->group("/jsonBd",function(RouteCollectorProxy $grupo)
{
    $grupo->get('/',Bionicle::class.":TraerTodos");
    
    $grupo->post('/',function (Request $request,Response $response,array $args):Response
    {
        $cuerpoPeticion=$request->getParsedBody();

        $parametrosPeticion=json_decode($cuerpoPeticion["obj_json"],true);
        $nombre=$parametrosPeticion["nombre"];
        $clave=$parametrosPeticion["clave"];
        
        $cuerpoRespuesta="{$nombre} y {$clave}, existentes en la base de datos.";

        $response->getBody()->write($cuerpoRespuesta);

        return $response;
    })->add(Validador::class . ":VerificarUsuario"); //agrego middleware solo para el verbo posts



})->add(Validador::class .":VerificarCuerpoPeticion"); // middleware a nivel del group

  

    


$app->run();



?>