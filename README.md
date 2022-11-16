# _WEB 2 - TP Especial - Parte 2_
# API RESTful

## Endpoints:
- books
Referencia a libros

- authors
Referencia a autores de libros

- genres
Referencia a géneros de libros

Cada vez que aparezca escrito $endpoint, significará que se podrá elegir cualquiera de los valores listados, acorde a la consulta que se necesite ejecutar.

# UTILIZAR LA API
## OBTENER ENTIDAD POR ID
- GET /api/$endpoint/:id

Devuelve el objeto con id :id de la entidad seleccionada en formato JSON.

Código de respuesta HTTP:
- 200 si se ejecutó la consulta exitosamente
- 404 si no existe

## OBTENER LISTADO DE ENTIDADES
- GET /api/$endpoint

Devuelve arreglo de objetos de la entidad seleccionada en formato JSON. 
Ver sección PARÁMETROS GET para personalizar la búsqueda

Códigos de respuesta HTTP:
- 200 si se ejecutó la consulta exitosamente
- 400 si se envió algún parámetro incorrecto

## EDITAR UNA ENTIDAD EXISTENTE
- PUT /api/$endpoint/:id

Edita objeto en $endpoint con id :id

Enviar en el body un JSON con la entidad a agregar respetando las estructuras mencionadas en sección EJEMPLO BODY A UTILIZAR CON POST Y PUT.

Códigos de respuesta HTTP:
- 200 si se ejecutó la operación exitosamente
- 400 si no se respeta la estructura de la entidad
- 404 si no existe el objeto con id :id

## CREAR ENTIDAD
- POST /api/$endpoint
Debe enviarse en el body un JSON con la entidad a agregar respetando las estructuras mencionadas en sección ESTRUCTURA ENTIDADES A UTILIZAR CON POST Y PUT.

Códigos de respuesta HTTP:
- 201 si se ejecutó la operación exitosamente
- 400 si no se respeta la estructura de la entidad

## ELIMINAR ENTIDAD
- DELETE /api/$endpoint/:id

Elimina objeto en $endpoint con id :id

Códigos de respuesta HTTP:
- 200 si se ejecutó la operación exitosamente
- 400 si se pretende eliminar un elemento que contiene otros elementos referenciados.
- 404 si no existe el objeto con id :id

# PARÁMETROS GET
Para personalizar el listado a obtener puede enviarse por GET un listado de duplas CLAVE-VALOR.
Los posibles valores para CLAVE y VALOR no son sensibles a mayúsculas y minúsculas (no case sensitive).

Se pueden definir varias duplas en la consulta respetando la forma:

GET api/$endpoint?clave1=valor1&clave2=valor2& ... &claveX=valorX

## ORDENAR
CLAVE: orderby | order_by | sortby | sort_by

POSIBLES VALORES para books:
id | isbn | title | id_author | id_genre | price | url_cover | genre | author 

POSIBLES VALORES para authors:
id | author | note

POSIBLES VALORES para genres:
id | genre | note

### ORDEN ASCENDENTE O DESCENDENTE
CLAVE: ASC | DESC

VALOR: no se tiene en cuenta, puede ser omitido

Determina que se pretende un orden ascendente (ASC) o descendente (DESC). 
Por defecto el orden es ASC

Ejemplo: 

GET api/books?orderby=author&desc

devolverá un listado de libros ordenado descendentemente por autor.

## PAGINADO
CLAVE: limit | l

VALOR: número entre 1 y 1000

Determina la máxima cantidad de objetos a devolver en el arreglo.
Si no se especifica, el valor por defecto es de 30.


CLAVE: page | p

VALOR: número de página

Determina desde qué página se pretende obtener los resultados.


Ejemplo: ?l=100&p=3 devolverá desde el objeto 301 hasta el 400, siempre que éstos existan.

En caso de no existir los objetos solicitados no se producirá error, sólo recibirá un arreglo vacío: [].

## BÚSQUEDA CON FILTRO
Para realizar una búsqueda se debe elegir como CLAVE una o varios campos válidos con el correspondiente valor a buscar.

CLAVE: campo válido

VALOR: valor a buscar

Posibles campos para books:
id | isbn | title | id_author | id_genre | price | url_cover | genre | author 

Posibles campos para authors:
id | author | note

Posibles campos para genres:
id | genre | note


CLAVE: operator | o

VALOR: like | equal | = | > | >= | < | <=

Especifica el tipo de comparador a utilizar, si no se especifica por defecto se utiliza =.

El operador like permite utilizar carácteres especiales de SQL como '%'.

Si se realizan varios filtros en la consulta, todos utilizarán el mismo operador.

## EJEMPLO BODY A UTILIZAR CON POST Y PUT
### books
{

    "isbn": "9789501532203",

    "title": "CURAME",

    "id_author": 1197,

    "id_genre": 10,

    "price": "2880.00",

    "url_cover": ""
}

Para que se pueda completar la transcción id_author y id_genre deben referenciar a un un autor o género válido, respectivamente.

### authors
{
    
    "author": "autor",

    "note": "creadO desde POSTMAN por API"
 }
### genres
{
    
    "genre": "Autor de prueba por API",

    "note": "creadO desde POSTMAN por API"
 }
