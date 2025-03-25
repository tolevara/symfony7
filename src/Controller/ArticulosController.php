<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Articulos; //<- ESTA ES LA ENTIDAD DE ARTICULOS//  
use App\Entity\Autores; //OJO!! <- HAY QUE PONER LA TABLA PRINCIPAL//
use Doctrine\Persistence\ManagerRegistry;


final class ArticulosController extends AbstractController
{
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
        }



        return $this->render('articulos/index.html.twig', [
            'controller_name' => 'ArticulosController',
        ]);
    }
}
