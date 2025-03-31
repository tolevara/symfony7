<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Articulos; //<- ESTA ES LA ENTIDAD DE ARTICULOS//  
use App\Entity\Autores; //OJO!! <- HAY QUE PONER LA TABLA PRINCIPAL//
use Doctrine\Persistence\ManagerRegistry;
// use PhpParser\Node\Expr\Cast\String_;

use App\Repository\ArticulosRepository; //<-ESTO ES UNA NUEVA LIBRERIA PARA IR A LA URL DIRECTAMENTE//

final class ArticulosController extends AbstractController
{
    #[Route('/articulos', name: 'app_articulos')]
    public function index(): Response
    {
        return $this->render('articulos/index.html.twig', [
            'controller_name' => 'ArticulosController',
        ]);
    }

 //C3 -> INSERTAR VARIOS REGISTROS (array) POR CÓDIGOS
 // OJO!!HAY QUE TENER CUIDADO CON EL REGISTRIO DE LA TABLA PRINCIPAL
    #[Route('/crear-articulos', name: 'app_articulos_insertar_articulos')]
    public function crearArticulos(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        //DEFINIMOS EL ARRAY DEL ARTICULO//
        $articulos = [
            "articulo1" => [
                "titulo" => "Creador de Symfony",
                "publicado" => 1,
                "nifAutor" => "12345678A"
            ],
            "articulo2" => [
                "titulo" => "Concentración alumnos 0",
                "publicado" => 1,
                "nifAutor" => "12345678A"
            ],
            "articulo3" => [
                "titulo" => "Prácticas en Empresa: Ofú!!",
                "publicado" => 1,
                "nifAutor" => "12345678A"
            ],
        ];
        
        //USO FOREACH PARA RECORRER EL ARRAY, Y METO EN LA TABLA ARTIRCULO POR ARTICULO//
        foreach ($articulos as $articulo) {
            $nuevoArticulo = new Articulos();
            $nuevoArticulo->setTitulo($articulo['titulo']);
            $nuevoArticulo->setPublicado($articulo['publicado']);

            //TENGO QUE PONER EL DATO DE LA TABLA PRINCIPAL, USO EL REPOSITORIO DE AUTORES(tabla principal)
            $nif = $entityManager->getRepository(Autores::class)->find($articulo['nifAutor']);
            $nuevoArticulo->setNifAutor($nif);
            $entityManager->persist($nuevoArticulo);
            $entityManager->flush();
        }
        return new Response("<h2> Artículos metidos </h2>");
    }

 // C4 -> INSERTAR UN REGISTRO POR PARÁMETROS
 // VARIANTE C2. CONTROLAMOS DATOS TABLA PRINCIPAL 
    #[Route('/crea-articulo/{titulo}/{publicado}/{nif}', name: 'app_articulos_insertar_articulo')]
    public function creaArticulo(ManagerRegistry $doctrine,
    string $titulo, int $publicado, string $nif): Response
    {
        $entityManager = $doctrine->getManager();

        $nuevoArticulo = new Articulos();
        $nuevoArticulo->setTitulo($titulo);
        $nuevoArticulo->setPublicado($publicado);

        //TENGO QUE PONER EL DATO DE LA TABLA PRINCIPAL, USO EL REPOSITORIO DE AUTORES(tabla principal)
        $nif = $entityManager->getRepository(Autores::class)->find($nif);

        //VAMOS A CONTROLAR QUE EL NIF EXISTENTE//

        $mensaje = "";

        if($nif==null) {
            $mensaje = "ERROR!! No existe el autor";
        } else { 
        $nuevoArticulo->setNifAutor($nif);
        $entityManager->persist($nuevoArticulo);
        $entityManager->flush();
        $mensaje = "EXITO!! Se ha introducido el articulo.";
    }
        return new Response("<h2> $mensaje </h2>");
    }

// R2 -> CONSULTAR COMPLETO TABLA RELACIONADA CON BOOTSTRAP
// OJO!! MIRAR EL TWIG aticulos.html.twig!!

    #[Route('/ver-articulos', name: 'app_articulos_ver')]
    public function verArticulos(ArticulosRepository $repositorio): Response
    {
        $articulos = $repositorio->findAll();

        return $this->render('articulos/articulos.html.twig', [
            'controller_name' => 'ArticulosController',
            'articulos' => $articulos,//<-ESTO ES UN arry DE VARIOS ELEMENTOS
        ]);
    }

    // R3a -> CONSULTAR POR CLAVE PRINCIPAL
    // PONGO ESTA RUTA EN EL routes.yaml /mostrar-articulo/{id}
    #[Route('/ver-articulo/{id}', name: 'app_articulos_ver_articulo')]
    public function verArticulo(ArticulosRepository $repositorio, int $id): Response
    {
        $articulo = $repositorio->find($id);

        return $this->render('articulos/articulos.html.twig', [
            'controller_name' => 'ArticulosController',
            'articulos' => [$articulo],//<-ESTO ES UN array DE UN SOLO ELEMENTO
        ]);
    }

    //R4 -> CONSULTAR POR PARÁMETROS (SALIDA array JSON!!)
    #[Route('/consultar-articulos/{nifAutor}/{publicado}', 
    name:'app_articulos_consultar_articulo')]
    public function consultarArticulos(ArticulosRepository $repositorio,
    string $nifAutor, bool $publicado):JsonResponse
    {
        $articulos = $repositorio->findBy(
            [
                'nifAutor' => $nifAutor,
                'publicado' => $publicado
            ], 
            [
                'titulo' => 'DESC'// (ORGANIZACIÓN)<- DESCENDENTE | 'ASC' <- ASCENDENTE
            ]
        );

        // AQUÍ LA SALIDA NO ES TWIG, ES JSON!!!
        $miJSON = [];
        foreach ($articulos as $articulo) {
            $miJSON[] = [
                'idArticulo' => $articulo->getId(),
                'Titulo' => $articulo->getTitulo(),
                'Nif Autor' => $articulo->getNifAutor()->getNif(),
                'Nombre' => $articulo->getNifAutor()->getNombre(),
            ];
        }

        return new JsonResponse($miJSON);

        //SI QUEREMOS DEVOLVER UNA TABLA CON twig, SE PONE LO DE ABAJO COMENTADO
        //AQUÍ LA SALIDA ES (twig)
        /*return $this->render('articulos/articulos.html.twig', [
            'controller_name' => 'ArticulosController',
            'articulos' => $articulos,
        ]);*/
    }

     //R5 -> CONSULTAR POR PARÁMETROS (SALIDA UN REGISRO JSON!!)
     #[Route('/consultar-articulo/{nifAutor}', 
     name:'app_articulos_consultar_articulo')]
     public function consultarArticulo(ArticulosRepository $repositorio,
     string $nifAutor):JsonResponse
     {
         $articulo = $repositorio->findOneBy(
             [
                 'nifAutor' => $nifAutor,
                 
             ], 
             [
                 'titulo' => 'DESC'// (ORGANIZACIÓN)<- DESCENDENTE | 'ASC' <- ASCENDENTE
             ]
         );
 
         // AQUÍ LA SALIDA NO ES TWIG, ES JSON!!!
         if($articulo == null) {
            $miJSON ="Articulo no encontrado";
        } else {
             $miJSON = [
                 'idArticulo' => $articulo->getId(),
                 'Titulo' => $articulo->getTitulo(),
                 'Nif Autor' => $articulo->getNifAutor()->getNif(),
                 'Nombre' => $articulo->getNifAutor()->getNombre(),
             ];
        }
         return new JsonResponse($miJSON);

         //SI QUEREMOS DEVOLVER UNA TABLA CON twig, SE PONE LO DE ABAJO COMENTADO
         //AQUÍ LA SALIDA ES (twig)
         /*return $this->render('articulos/articulos.html.twig', [
             'controller_name' => 'ArticulosController',
             'articulos' => [$articulo],<-UN SÓLO REGISTRO
         ]);*/
     }
}
