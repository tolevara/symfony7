<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Autores; //<- ESTA ES LA ENTIDAD DE AUTORES//  
use Doctrine\Persistence\ManagerRegistry; //<-AÑADIMOS LA BIBLIOTECA DE GESTION DE REGISTRO (IMPORTANTE!!)
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

final class AutoresController extends AbstractController
{
    #[Route('/autores', name: 'app_autores')]
    public function index(): Response
    {
        return $this->render('autores/index.html.twig', [
            'controller_name' => 'AutoresController',
        ]);
    }

    //1º EJEMPLO: INSERCIÓN CON DATOS EN EL CÓDIGO
    //C1:->INSERTAR UN REGISTRO POR CODIGO
    #[Route('/insertar-autor', name: 'app_autores_insertar_autor')]
    public function insertarAutor1(ManagerRegistry $doctrine): Response
    {
        //SACAMOS EL GESTOR DE ENTIDADES DEL MANAGER REGISTRY//
        $entityManager = $doctrine->getManager();

        //CREO UN AUTOR Y LO METO EN LA TABALA//
        $autor = new Autores();
        $autor-> setNif("12345678A");
        $autor->setNombre("María");
        $autor->setEdad("52");
        $autor->setSueldoHora(15.95);

        //INSERTAMOS EN LA TABLA Y ACTUALIZAMOS//
        $entityManager->persist($autor);
        $entityManager->flush();

        return new Response("<h2> Autor metido con Nif" . $autor->getNif() ."<h2>");
    }
    
    //2º EJEMPLO: INSERCIÓN CON DATOS EN LA URL (PÁRAMETROS)
    //C2:->INSERTAR UN REGISTRO POR PÁRAMETROS
    #[Route('/insertar-autor/{nif}/{nombre}/{edad}/{sueldo}', name: 'app_autores_insertar_autor')]
    public function insertarAutor2(ManagerRegistry $doctrine,
    string $nif, string $nombre, int $edad, string $sueldo): Response
    {
        //SACAMOS EL GESTOR DE ENTIDADES DEL MANAGER REGISTRY//
        $entityManager = $doctrine->getManager();

        //CREO UN AUTOR Y LO METO EN LA TABALA//
        $autor = new Autores();
        $autor-> setNif($nif);
        $autor->setNombre($nombre);
        $autor->setEdad($edad);
        $autor->setSueldoHora($sueldo);

        //INSERTAMOS EN LA TABLA Y ACTUALIZAMOS//
        $entityManager->persist($autor);
        $entityManager->flush();

        return new Response("<h2> Autor metido con Nif" . $autor->getNif() ."<h2>");
    }

 // R1 ->CONSULTAR COMPLETO CON TABLA BOOTSTRAP
    #[Route('/ver-autores', name: 'app_autores_ver')]
    public function verAutores(ManagerRegistry $doctrine): Response
    {
        // Sacamos de la biblioteca de gestión de Registros
        // ManagerRegistry el repositorio de Autores
        $repoAutores = $doctrine->getRepository(Autores::class);

        //SACAMOS TODOS LOS REGISTROS//
        $autores = $repoAutores->findAll(); //<-SACA TODOS LOS AUTORES//
        
        return $this->render('autores/autores.html.twig', [
            'controller_name' => 'AutoresController','autores' => $autores,
        ]);
    }

    //U1 ACTUALIZACIÓN POR ID Y POR PARÁMETRO
    #[Route('/cambiar-autor/{nif}/{nombre}/{edad}', 
    name: 'app_autores_actualizar')]
    public function cambiarAutor(ManagerRegistry $doctrine, string $nif, string $nombre, int $edad): Response
    {
        // Sacamos de la biblioteca de gestión de Registros
        // ManagerRegistry el repositorio de Autores
        $repoAutores = $doctrine->getRepository(Autores::class);

        // BUSCAMOS EL AUTOR A CAMBIAR //
        $autor = $repoAutores->find($nif); //<-SACA UN SÓLO AUTOR//

        if($autor == null) {          //<- SI NO ENCUENTRA EL AUTOR
            echo "Autor NO encontrado"; //<- DA ESTE MENSAJE
        } else {
        $autor->setNombre($nombre);
        $autor->setEdad($edad);
        } 

        //<- GUARDO EL AUTOR MODIFICADO
        $entityManager = $doctrine->getManager();
        $entityManager->flush();


        // OJO!! REDIRECCIONAMOS LA SALIDA //
        // redirectToRoute -> Redireccina a la ruta (name)
        // render-> Renderizar un twig (vista) //

        return $this->redirectToRoute('app_autores_ver',[
            'controler_name' =>'Autor acutualizado!!',
        ]);

        /* EJEMPLO DE LA OTRA FORMA:
        return $this->render('autores/autores.html.twig', [
            'controller_name' => 'AutoresController','autores' => $autores,
        ]);*/
    }

    //F2 -> FORMULARIO COMPLETO TABLA PRINCIPAL
    #[Route('/autores-form',  name: 'app_autores_form')]
     public function autoreForm(ManagerRegistry $doctrine, Request $envio): Response 
    {
        $autor = new Autores();
        $formulario = $this->createFormBuilder($autor)
        ->add('nif', TextType::class, [
            'label' => 'Nif Autor'
        ])
        ->add('nombre', TextType::class, [
            'label' => 'Nombre'
        ])
        ->add('edad', IntegerType::class, [
            'label' => 'Edad',
            'attr' => [
                'min' => 18,
                'max' => 70,
            ]
        ])
        ->add('sueldoHora', NumberType::class, [
            'label' => 'Sueldo por Hora',
            'html5' => true,    //<- HASTA EL html5 NO COJE LOS DECIMALES//
            'scale' => 2,       //NUMERO DE DECIMALES
            'attr' => [
                'min' => 9.95,
                'max' => 49.95,
                'step' => 0.05,  //<-SALTO CADA VEZ QUE SE PULSA
            ]
        ])
        ->add('Guardar', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-danger mt-3'
            ]
        ])
        ->getForm();

        $formulario->handleRequest($envio);
        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($autor);
            $entityManager->flush();

            //REDIRECCIONAMOS
            return $this->redirectToRoute('app_autores_ver');
        }

        //PINTAMOS EL FORMULARIO
        return $this->render('autores/form.autores.html.twig', [
            'controller_name' => 'Formulario de Autores',
            'formulario' => $formulario->createView(),
        ]);

    }
}

