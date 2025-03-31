<!-- README.md  -->
# symfony7  

## COMANDOS
-symfony serve
-php bin/console doctrine:database:drop --force
-php bin/console doctrine:database:create
-php bin/console doctrine:migrations:migrate

## ENDPOINTS
- http://localhost:8000/
  
ALEATORIOS:
- http://localhost:8000/num-aleatorio       
- http://localhost:8000/otro-aleatorio
- http://localhost:8000/aleatorio
- http://localhost:8000/mi_aleatorio_44
  
- http://localhost:8000/autores
- http://localhost:8000/insertar-autor
  
AUTORES:
- [C1]http://localhost:8000/insertar-autor/{nif}/{nombre}/{edad}/{sueldo}
- [C2]http://localhost:8000/insertar-autor/12345678A/Maria/52/15.50

ARTICULOS:
- [C3]http://localhost:8000/crear-articulos
- [C4]http://localhost:8000/crea-articulo/{titulo}/{publicado}/{nif}
- http://localhost:8000/crea-articulo/"PRL Acabado!"/1/12345678C

## ENDPOINTS DE CONSULTA

- Métodos por defecto de los Repositorios
  - findAll() -> SELECT * FROM "tabla" => array registros
    Ej: SELECT * FROM autores;

  - findBy($criterios, $ordenacion, $limite, $salto)
    - $criterios -> Conjunto de filtros (WHERE)
    - $ordenación -> Conjunto de ordenaciones
    - $limite -> Nº de registros a mostrar
    - $salto -> Registros por intervalos
      - Ejemplo: Tenemos 500 autores 
  ({nif}/{nombre}/{edad}/{sueldo})
  SELECT * FROM autores
  WHERE edad > 40      -> $criterio1
  AND sueldo > 20      -> $criterio2
  LIMIT 10             -> $limite
  OFFSET 40            -> $salto
  ORDER BY sueldo ASC; -> $ordenación
        
  - find($id) -> SELECT * FROM "tabla" WHERE clave = "" -> 1 registro
    Ej: SELECT * FROM autores WHERE nif = 12345678B

  - findOneBy ($criterios, $ordenacion) -> 1 registro
    Ej: SELECT * FROM autores
    WHERE name = "pepito"
    AND edad = 78;

AUTORES (tabla principal):
- [R1]http://localhost:8000/ver-autores

ARTICULOS (tabla relacionada):

- [R2]http://localhost:8000/ver-articulos
- [R3a]http://localhost:8000/ver-articulo/{id}
- [R3b]http://localhost:8000/mostrar-articulo/{id}
- [R4]http://localhost:8000/mostrar-articulos/{nifAutor}/{publicado}
- [R5]http://localhost:8000/mostrar-articulo/{nifAutor}

### ENDPOINTS DE ACTUALIZACIÓN

- [U1] http://localhost:8000/
