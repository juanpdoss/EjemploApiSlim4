<?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    require_once("IBionicle.php");
    require_once("AccesoDatos.php");

    class Bionicle implements IBionicle
    {
        public $id;
        public $nombre;
        public $clave;
        public $modelo;

       
         public function TraerTodos(Request $request, Response $response,array $args):Response
        {

            $bionicles=self::TraerBionicles();

            $newResponse=$response->withStatus(200,"Ok");
            $newResponse->getBody()->write(json_encode($bionicles));
            return $newResponse->withHeader("Content-Type","application/json");

        }

        public function TraerUno(Request $request,Response $response,array $args):Response
        {
            $id=$args['id'];    
        
            $resultado=self::TraerUnBionicle($id);
        
            $response->getBody()->write(json_encode($resultado));
        
            return $response->withHeader("Content-Typer","application/json");
        }
               
       
        public function AgregarUno(Request $request,Response $response,array $args):Response
        {

            $datos=$request->getParsedBody();

            $json=json_decode($datos["json"]);

            if(Bionicle::InsertarBionicle($json)!=0)
            {
                $response->withStatus(200,"Ok");
                $response->getBody()->write("Bionicle insertado en base de datos.");
            }
            else
            {
                $response->withStatus(400,"Error");
                $response->getBody()->write("No se pudo realizar la insercion");
            }


           return $response;
        
        
        }


	public function ModificarUno(Request $request,Response $response,array $args):Response
    {
        $datos=$args["parametros"];//mando por url una cadena json

        $json=json_decode($datos,true);

        

        if(isset($json))
        {
            if(Bionicle::ModificarUnBionicle($json)!=0)
            {
                $response->withStatus(200,"Exito");
                $response->getBody()->write("Modificacion exitosa");
            }
            else
            {
                $response->withStatus(400,"Error");
                $response->getBody()->write("No se pudo realizar la modificacion");
                
            }
        }
			
		return $response;
	}
       
    public function BorrarUno(Request $request,Response $response,array $args):Response
    {   
        if(isset($args))
        {
            $id=$args["id"];
            if(Bionicle::EliminarUnBionicle($id)!=0)
            {
                $response->withStatus(200,"ok");
                $response->getBody()->write("Eliminacion exitosa");
            }

        }


        return $response;
    }




        /*funciones propias de la clase, luego las usaran las funciones definidas en la interfaz IBioniclee*/

        public static function TraerBionicles()
        {
            $arrayBionicles=[];
            $accesoDatos=AccesoDatos::dameUnObjetoAcceso();
            $sql="SELECT * FROM bionicles";
            $consulta=$accesoDatos->RetornarConsulta($sql);
            $consulta->execute();

            
            return $consulta->fetchAll(PDO::FETCH_CLASS,"Bionicle");    

        }



        public static function TraerUnBionicle($id)
        {
            $accesoDatos=AccesoDatos::dameUnObjetoAcceso();
            $sql="SELECT * FROM bionicles WHERE id=$id";
            $consulta=$accesoDatos->RetornarConsulta($sql);
          
            $consulta->execute();
            $bionicle= $consulta->fetchObject("Bionicle");

            return $bionicle;

        }



        public static function ModificarUnBionicle($parametros)
        {
            $accesoDatos=AccesoDatos::dameUnObjetoAcceso();
            $sql="UPDATE bionicles SET nombre=:nombre,clave=:clave,modelo=:modelo WHERE id=:id";
            $consulta=$accesoDatos->RetornarConsulta($sql);
            $consulta->bindValue(":nombre",$parametros["nombre"]);
            $consulta->bindValue(":clave",$parametros["clave"]);
            $consulta->bindValue(":modelo",$parametros["modelo"]);
            $consulta->bindValue(":id",$parametros["id"]);

            $consulta->execute();
            return $consulta->rowCount();

        }

        
        public static function EliminarUnBionicle($id)
        {
            $accesoDatos=AccesoDatos::dameUnObjetoAcceso();
            $sql="DELETE FROM bionicles where id=:id";
            $consulta=$accesoDatos->RetornarConsulta($sql);
            $consulta->bindValue(":id",$id);
            
            $consulta->execute();            

            return $consulta->rowCount();

        }



        public static function InsertarBionicle($parametros)
        {
            $accesoDatos=AccesoDatos::dameUnObjetoAcceso();
            $sql="INSERT INTO bionicles (nombre,modelo,clave) VALUES(:nombre,:modelo,:clave)";
            $consulta=$accesoDatos->RetornarConsulta($sql);
            $consulta->bindValue(":nombre",$parametros->nombre);
            $consulta->bindValue(":modelo",$parametros->modelo);
            $consulta->bindValue(":clave",$parametros->clave);

            $consulta->execute();

            return $consulta->rowCount();

        }


    }






?>
