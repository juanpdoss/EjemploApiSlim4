<?php
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response as ResponseMW;
    require_once("bionicle.php");
    
    class Validador
    {

        public function VerificarVerbo(Request $request, RequestHandler $handler): ResponseMW
        {

            $respuesta=new ResponseMW();
            $cuerpoRespuesta="";


            //verifico que verbos recibo en el objeto Request
            if($request->getMethod() == "GET")
            {
               $cuerpoRespuesta.="middleware, antes de avanzar al verbo GET<br>";
               $cuerpoRespuesta.=$handler->handle($request)->getBody()->__toString(); //obtengo la respuesta del verbo GET
               $cuerpoRespuesta.="<br> devuelta en el middleware, se envia la peticion al cliente.";

               $respuesta->getBody()->write($cuerpoRespuesta);
                
            }else if($request->getMethod()=="POST")
            {
                $cuerpoPeticion=$request->getParsedBody();

                if($cuerpoPeticion["perfil"]=="administrador")
                {   
                    $cuerpoRespuesta.="perfil administrador, puede avanzar al verbo";
                    $cuerpoRespuesta.=$handler->handle($request)->getBody()->__toString();//obtengo respuesta del verbo
                    $cuerpoRespuesta.="devuelta en el middleware, se envia la peticion al cliente.";
                    
                    $respuesta->getBody()->write($cuerpoRespuesta);


                }
                else
                {
                    $cuerpoRespuesta.="{$cuerpoPeticion["nombre"]} es un perfil no administrador, no puede avanzar hacia el verbo";
                    
                    $respuesta->getBody()->write($cuerpoRespuesta);


                }
                

            }

            return $respuesta;

        }

        /*
            Si el usuario y clave recibidos dentro de la peticion existen en la base de datos, se avanzara hacia el verbo 
         */
        public function VerificarUsuario(Request $request, RequestHandler $handler): ResponseMW
        {
            $respuesta=new ResponseMW();    
            $bionicles=Bionicle::TraerBionicles();
            $cuerpoPeticion=$request->getParsedBody();
            $parametrosPeticion=json_decode($cuerpoPeticion["obj_json"],true); //el true determina que el retorno sera de tipo array asociativo

            $usuario=$parametrosPeticion["nombre"];
            $clave=$parametrosPeticion["clave"];
            $existe=false;

            $cuerpoRespuesta=new stdClass();
            $cuerpoRespuesta->mensaje="Error, usuario o clave incorrectos.";
            $cuerpoRespuesta->status=403;


            if(isset($bionicles))
            {
                foreach ($bionicles as $bionicle)
                {
                    if($bionicle->nombre == $usuario && $clave == $bionicle->clave)
                    {
                        $existe=true;
                        break;
                    }

                }
                if($existe)
                {

                    $cuerpoRespuesta->mensaje=$handler->handle($request)->getBody()->__toString();
                    $cuerpoRespuesta->status=200;
                }

            }

            $respuesta->getBody()->write(json_encode($cuerpoRespuesta));




            return $respuesta;
        }
        

        public function VerificarCuerpoPeticion(Request $request, RequestHandler $handler): ResponseMW
        {
            $respuesta=new ResponseMW();
            $datos=$request->getParsedBody();
            $json=json_decode($datos["obj_json"],true);


            if(isset($json))
            {
                if($json["nombre"]=="")
                {
                    $respuesta->withStatus(400,"Error, no se introdujo el nombre.");
                    $respuesta->getBody()->write("Error, no se introdujo el nombre");

                }else if($json["clave"]=="")
                {
                    $respuesta->withStatus(400,"Error, no se introdujo la clave.");
                    $respuesta->getBody()->write("Error, no se introdujo la clave");

                }
                else
                {
                    $respuesta->withStatus(200,"ok");
                    $respuesta->getBody()->write($handler->handle($request)->getBody()->__toString());
                }


            }
            else
            {
                $respuesta->withStatus(400,"Error, no se mandaron los parametros requeridos.");
                $respuesta->getBody()->write("Error, no se mandaron los parametros requeridos.");
            }
            return $respuesta;

        }





    }



?>