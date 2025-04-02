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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; //<-COMPONENTES DEL FORMULARIO A PARTIR DE AQUÍ
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;


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
    public function creaArticulo(
        ManagerRegistry $doctrine,
        string $titulo,
        int $publicado,
        string $nif
    ): Response {
        $entityManager = $doctrine->getManager();

        $nuevoArticulo = new Articulos();
        $nuevoArticulo->setTitulo($titulo);
        $nuevoArticulo->setPublicado($publicado);

        //TENGO QUE PONER EL DATO DE LA TABLA PRINCIPAL, USO EL REPOSITORIO DE AUTORES(tabla principal)
        $nif = $entityManager->getRepository(Autores::class)->find($nif);

        //VAMOS A CONTROLAR QUE EL NIF EXISTENTE//

        $mensaje = "";

        if ($nif == null) {
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
            'articulos' => $articulos, //<-ESTO ES UN arry DE VARIOS ELEMENTOS
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
            'articulos' => [$articulo], //<-ESTO ES UN array DE UN SOLO ELEMENTO
        ]);
    }

    //R4 -> CONSULTAR POR PARÁMETROS (SALIDA array JSON!!)
    #[Route(
        '/consultar-articulos/{nifAutor}/{publicado}',
        name: 'app_articulos_consultar_articulo'
    )]
    public function consultarArticulos(
        ArticulosRepository $repositorio,
        string $nifAutor,
        bool $publicado
    ): JsonResponse {
        $articulos = $repositorio->findBy(
            [
                'nifAutor' => $nifAutor,
                'publicado' => $publicado
            ],
            [
                'titulo' => 'DESC' // (ORGANIZACIÓN)<- DESCENDENTE | 'ASC' <- ASCENDENTE
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
    #[Route(
        '/consultar-articulo/{nifAutor}',
        name: 'app_articulos_consultar_articulo'
    )]
    public function consultarArticulo(
        ArticulosRepository $repositorio,
        string $nifAutor
    ): JsonResponse {
        $articulo = $repositorio->findOneBy(
            [
                'nifAutor' => $nifAutor,

            ],
            [
                'titulo' => 'DESC' // (ORGANIZACIÓN)<- DESCENDENTE | 'ASC' <- ASCENDENTE
            ]
        );

        // AQUÍ LA SALIDA NO ES TWIG, ES JSON!!!
        if ($articulo == null) {
            $miJSON = "Articulo no encontrado";
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

    //  U2 -> ACTUALIZAR POR ID, CON PARÁMETROS Y CAMBIO DEL FOREN KEY!!
    //    SI SE CAMBIA EL FK, EL TIPO NO ES string, ES EL OBJETO!!
    #[Route(
        '/cambiar-articulo/{id}/{titulo}/{nifAutor}',/*<-COPIANDO ESTA LINEA Y PEGANDO                                                   CUALQUIER COSA LA TABLA FINAL*/
        name: 'app_articulos_actualizar'            /*EN LA WEB PODEMOS MODIFICAR CUALQUIER DATO DE LA TABLA FINAL*/
    )]
    public function cambiarArticulo(
        ManagerRegistry $doctrine,
        int $id,
        string $titulo,
        Autores $nifAutor
    ): Response {
        // SACAMOS EL ENTITYMANAGER
        $entityManager = $doctrine->getManager();
        $repositorioArticulos = $entityManager->getRepository(Articulos::class);
        $repositorioAutores = $entityManager->getRepository(Autores::class);

        $articulo = $repositorioArticulos->find($id);
        $autor = $repositorioAutores->find($nifAutor);

        // CONTROLAMOS EL FALLO
        if ($articulo == null || $autor == null) {
            return new Response("<h1> Articulo/Autor No existe </h1>");
        } else {
            $articulo->setTitulo($titulo);
            $articulo->setNifAutor($nifAutor);
            $entityManager->flush();  //<-ACTUALIZAMOS LA BASE DE DATOS//    
        }

        return $this->redirectToRoute('app_articulos_ver', [
            'controller_name' => 'Articulo Actualizado',
        ]);

        /*
         return $this->render('articulos/index.html.twig', [
             'controller_name' => 'ArticulosController',
         ]);
         */
    }

    // D1 ELIMINAR POR ID (TABLA RELACIONADA)
    #[Route(
        '/articulos-borrar/{id}',
        name: 'app_articulos_borrar'
    )]
    public function borrarArticulo(
        EntityManagerInterface $entityManager,
        int $id
    ): Response {
        // BUSCO EL ARTICULO
        $repositorioArticulos = $entityManager->getRepository(Articulos::class);
        $articulo = $repositorioArticulos->find($id);

        if ($articulo == null) {
            return new Response("<h1> Articulo No encontrado <h1>");
        } else {
            $entityManager->remove($articulo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_articulos_ver', [
            'controller_name' => 'Articulo Actualizado',
        ]);
    }

    // F1 FORMULARIO COMPLETO AMBAS TABLAS
    // AÑADIMOS UN ARTICULO CON EL SELECT PARA AUTORES
    // EN EL REQUEST TIENE QUE SER ESTE -> (use Symfony\Component\HttpFoundation\Request;)

    #[Route('/articulos-form', name: 'app_articulos_form')]
    public function articulosForm(
        ManagerRegistry $doctrine,
        Request $envio
    ): Response //<-PONER EL CURSOR EN EL (Request) PARA VER QUE ESTÁ BIEN!!
    {
        $articulo = new Articulos();
        $formulario = $this->createFormBuilder($articulo)

            //1º CAMPO
            ->add('titulo', TextType::class, [ //<- ESTO EQUIVALE AL <input type="text" name="titulo">
                'label' => 'Titulo'
            ])
            //2º CAMPO 
            ->add('publicado', RadioType::class, [
                'label' => '¿Está publicado?',
                'required' => false, //<-ESTO EVITA EL ERROR DEL SÍ, NÓ
                'value' => false,   //<-VALOR POR DEFECTO SI NO SE MARCA
            ])
            // CON EL RADIO SÍ (x) | NÓ () //
            /**/

            //3º CAMPO
            ->add('nifAutor', EntityType::class, [
                'label' => 'Elige Autor',
                'placeholder' => 'Elija opción',
                'class' => Autores::class,
                'choice_label' => 'nombre'
            ])

            ->getForm();

        /*UNA VEZ HEMOS PINTADO EL FORMULARIO, LO MANDAMOS Y PREPARAMOS 
            LA RECEPCIÓN DE SUS DATOS*/

        $formulario->handleRequest($envio);
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($articulo);
            $entityManager->flush();

            //REDIRECCIONAMOS
            return $this->redirectToRoute('app_articulos_ver');
        }



        //PINTAMOS EL FORMULARIO
        return $this->render('articulos/form.articulos.html.twig', [
            'controller_name' => 'Formulario de Articulos',
            'formulario' => $formulario->createView(),


        ]);
    }
}
