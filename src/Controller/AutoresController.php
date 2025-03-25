<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Autores; //<- ESTA ES LA ENTIDAD DE AUTORES//  
use Doctrine\Persistence\ManagerRegistry; //<-AÑADIMOS LA BIBLIOTECA DE GESTION DE REGISTRO (IMPORTANTE!!)



final class AutoresController extends AbstractController
{
    #[Route('/autores', name: 'app_autores')]
    public function index(): Response
    {
        return $this->render('autores/index.html.twig', [
            'controller_name' => 'AutoresController',
        ]);
    }
    //1º EJEMPLO: INSERCION CON DATOS EN EL CÓDIGO
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
    
    //2º INSERCIÓN CON DATOS EN LA URL (PÁRAMETROS)
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
}

