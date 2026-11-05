# Sistema de Almacenamiento | AdmonDB

## Tecnologias

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-336791?style=flat-square&logo=postgresql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=flat-square&logo=git&logoColor=white)

Aplicación web para un sistema de almacén desarrollada con **PHP** y **PostgreSQL**.

> Proyecto final para la materia de **Administración de Bases de Datos**

## Arranque del proyecto

``` bash
git clone https://github.com/rvzorblvde/admondb-almacenamiento-pgsql <carpeta>

cd <carpeta>
cp .env.example .env        # Llenar con tus credenciales

# Crear la BD en pgAdmin y ejecutar el script de sql/ en orden
php -S localhost:8000 -t public
```

Abrir el proyecto en <http://localhost:8000>

> La guía completa, incluyendo la configuración de `php.ini` y la solución a problemas frecuentes se encuentra en [docs/instalacion.md](docs/instalacion.md).

## Estructura del proyecto

```text
admondb-almacenamiento-pgsql/
├── config/         # Lectura del .env y conexión a PostgreSQL
├── modelos/        # Acceso a datos (solo SQL)
├── controladores/  # Lógica y validaciones
├── vistas/         # Solo HTML
├── public/         # Puntos de entrada y CSS
├── sql/            # Scripts numerados de la BD
└── docs/           # Documentación detallada
```

## Integrantes e información

| Integrantes                        |
| ---                                |
| _Guillermo Jair Muñoz Amaro_       |
| _Jonathan Alexis Martínez de León_ |
| _Brenda Nathali Palomo Silva_      |
