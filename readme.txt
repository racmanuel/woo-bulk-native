=== WC Bulk Edit & Category Tools ===
Contributors: rcmanuel
Tags: woocommerce, bulk edit, quick edit, categories, product categories
Requires at least: 5.2
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Herramientas nativas para WooCommerce: buscador en los checklists de taxonomías de la edición rápida/masiva y limpiador de la categoría por defecto.

== Description ==

Este plugin reúne en una sola extensión dos utilidades para el catálogo de WooCommerce.

**1. Buscador en la edición rápida y masiva**

Añade un campo de búsqueda sobre los checklists jerárquicos de taxonomías
(categorías, marcas, etiquetas y cualquier taxonomía registrada) que WooCommerce
muestra en la edición rápida y en la edición masiva de productos.

* Filtra por texto en tiempo real (con debounce) sobre la lista de términos.
* Conserva visibles los términos padre e hijo relacionados con la coincidencia.
* Muestra el contador de resultados y un botón para limpiar la búsqueda.
* Funciona con checklists anidados de cualquier profundidad.

**2. Limpiador de la categoría por defecto**

Añade una herramienta bajo *Productos → Limpiar categoría por defecto* que
elimina la categoría por defecto de WooCommerce de los productos simples que,
además, tienen asignadas otras categorías.

* Primero simula el proceso: indica cuántos productos se pueden limpiar y
  cuántos se conservarán sin cambios.
* Después ejecuta la limpieza por lotes con barra de progreso.
* Nunca deja un producto sin categoría: solo actúa cuando existen otras
  categorías adicionales.
* Añade un acceso directo en la barra superior del listado de productos.

== Installation ==

1. Sube la carpeta del plugin a `/wp-content/plugins/` o instálalo desde el
   repositorio de plugins de WordPress.
2. Activa el plugin desde el menú *Plugins*.
3. Asegúrate de que WooCommerce está instalado y activo.

== Frequently Asked Questions ==

= ¿Modifica productos que solo tienen la categoría por defecto? =

No. Solo se limpian los productos simples que tienen la categoría por defecto
junto con al menos otra categoría adicional.

= ¿Qué tipos de producto se procesan? =

Únicamente productos de tipo `simple`.

= ¿Puedo deshacer la limpieza? =

No automáticamente. Se recomienda hacer una copia de seguridad de la base de
datos antes de ejecutar la limpieza.

== Changelog ==

= 1.0.2 =
* Añadido un buscador local para filtrar los productos seleccionados en la
  edición masiva sin modificar la selección nativa de WordPress.

= 1.0.1 =
* Corregido el buscador en la edición rápida y masiva: ahora los manejadores de
  eventos están delegados, de modo que siguen funcionando cuando WordPress y
  WooCommerce clonan filas completas del listado de productos.

= 1.0.0 =
* Versión inicial unificada. Integra el buscador de taxonomías en la edición
  rápida/masiva y el limpiador de la categoría por defecto.
