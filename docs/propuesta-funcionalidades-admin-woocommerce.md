# Propuesta: Mejorar la búsqueda en la edición rápida y masiva de productos

## Resumen

Esta propuesta plantea mejorar dos flujos de administración dentro de WooCommerce:

1. Añadir búsqueda a los checklists de categorías, marcas, etiquetas y otras taxonomías.
2. Añadir búsqueda a la lista de productos seleccionados para la edición masiva.
3. Añadir una herramienta para limpiar la categoría por defecto de WooCommerce.

Estas funciones mejorarían la administración diaria de catálogos grandes y permitirían corregir asignaciones de categorías de forma controlada.

## Problema

WooCommerce muestra listas de productos y taxonomías que pueden crecer mucho con el tiempo. Cuando esas listas contienen cientos o miles de elementos, localizar un producto o término concreto requiere desplazarse manualmente por contenido largo.

### Checklists de taxonomías

Los checklists de categorías, marcas, etiquetas y taxonomías personalizadas pueden contener listas extensas y jerarquías profundas. La falta de una búsqueda integrada dificulta encontrar rápidamente el término que se quiere marcar o revisar.

Esto afecta especialmente a:

- Categorías con varios niveles de subcategorías.
- Tiendas con muchas marcas o etiquetas.
- Taxonomías registradas por extensiones de WooCommerce.
- Edición rápida de un producto concreto.
- Edición masiva de varios productos.

### Productos seleccionados para edición masiva

La edición masiva muestra los productos seleccionados en una lista propia. Cuando se seleccionan muchos productos, revisar esa lista o confirmar si un producto concreto forma parte de la selección requiere desplazarse manualmente.

La lista debería permitir filtrar los productos seleccionados por nombre sin cambiar la selección real ni interferir con los controles nativos para eliminar productos.

### Categoría por defecto de WooCommerce

WooCommerce asigna una categoría por defecto a los productos. Con el tiempo, esa categoría puede permanecer asignada junto con categorías más específicas, aunque ya no sea necesaria para esos productos.

La limpieza manual de esta categoría requiere revisar productos individualmente y comprobar que cada producto conserve al menos otra categoría. El administrador necesita una herramienta que permita simular la operación antes de modificar datos y procesar los productos de forma controlada.

## Funcionalidades propuestas

### 1. Búsqueda en checklists de taxonomías

Añadir un campo de búsqueda sobre cada checklist de taxonomía que WooCommerce muestra en edición rápida y edición masiva.

La búsqueda debería:

- Filtrar términos en tiempo real con un debounce breve.
- Ignorar diferencias entre mayúsculas y minúsculas.
- Funcionar correctamente con acentos y caracteres localizados.
- Mantener visibles los padres de un término coincidente.
- Mantener visibles los hijos relacionados cuando corresponda.
- Funcionar con taxonomías jerárquicas y no jerárquicas.
- Mostrar un estado claro cuando no existan coincidencias.
- Incluir una acción para limpiar la búsqueda.
- Mantener intactos los valores seleccionados.

La solución no debería utilizar una lista fija de nombres. Debe funcionar con las taxonomías que el editor de productos ya renderiza, incluyendo taxonomías añadidas por extensiones.

### 2. Búsqueda de productos seleccionados

Añadir un campo de búsqueda encima de la lista de productos seleccionados en la edición masiva.

La búsqueda debería:

- Filtrar los productos seleccionados por nombre.
- Ocultar temporalmente los productos que no coincidan.
- Mantener todos los productos seleccionados en el formulario.
- Mantener funcionales los botones para eliminar productos.
- Mostrar un estado claro cuando no haya coincidencias.
- Restaurar todos los productos al limpiar el campo.
- Seguir funcionando cuando WordPress reconstruya la fila de edición masiva.

Este filtro debe ser local y no necesita realizar peticiones AJAX ni consultar nuevamente todo el catálogo.

### 3. Limpiador de la categoría por defecto

Añadir una herramienta bajo la sección de productos que permita eliminar la categoría por defecto de WooCommerce de los productos simples que también tengan asignadas otras categorías.

La herramienta debería:

- Detectar y mostrar la categoría por defecto configurada en WooCommerce.
- Simular la operación antes de realizar cambios.
- Informar cuántos productos tienen la categoría por defecto.
- Informar cuántos productos se pueden limpiar y cuántos se conservarán sin cambios.
- Procesar los productos limpiables en lotes.
- Mostrar el progreso y los resultados del procesamiento.
- Omitir productos que no tengan otra categoría asignada.
- Nunca dejar un producto sin categoría.
- Requerir permisos de administración de WooCommerce y una confirmación explícita.

El procesamiento debe invalidar las cachés de términos afectadas y gestionar correctamente los errores de cada lote.

## Por qué estas funciones deberían formar parte del core de WooCommerce

### Flujo habitual de administración

Buscar productos y términos es una acción básica de cualquier administrador de catálogo. No es una función especializada ni depende de una configuración particular de una tienda.

### Mejor experiencia para catálogos grandes

La navegación manual por listas largas consume tiempo y aumenta la posibilidad de seleccionar el término equivocado. Un filtro visible permite encontrar rápidamente el elemento necesario sin abandonar el formulario.

### Comportamiento consistente

Una implementación nativa ofrecería el mismo comportamiento en las tiendas WooCommerce, en lugar de depender de pequeños plugins con interfaces y niveles de compatibilidad diferentes.

### Bajo riesgo de implementación

Estas mejoras se limitan a la interfaz y a tareas controladas de administración. No requieren cambios en el checkout, los pedidos ni las plantillas públicas.

### Mantenimiento seguro del catálogo

El limpiador evita una operación destructiva sin revisión previa mediante una simulación, una confirmación y un procesamiento por lotes. La regla de conservar productos que no tengan categorías adicionales reduce el riesgo de dejar productos sin clasificación.

### Compatibilidad con extensiones

La búsqueda puede operar sobre los elementos que ya están presentes en el DOM. De esta manera, las extensiones que registran marcas u otras taxonomías pueden beneficiarse sin tener que integrar una lista fija de taxonomías.

## Experiencia de usuario propuesta

### Edición rápida y masiva: taxonomías

1. El administrador abre la edición rápida o masiva de productos.
2. Cada checklist de taxonomía muestra un campo de búsqueda.
3. El administrador escribe parte del nombre del término.
4. La lista muestra solo las coincidencias y sus relaciones jerárquicas relevantes.
5. El administrador marca o revisa el término necesario.
6. Al limpiar la búsqueda, se restaura el checklist completo.

### Edición masiva: productos seleccionados

1. El administrador selecciona varios productos desde el listado.
2. Abre la edición masiva.
3. Escribe parte del nombre de un producto en el nuevo filtro.
4. La lista muestra únicamente las coincidencias.
5. La selección real permanece intacta.
6. Al limpiar la búsqueda, vuelven a mostrarse todos los productos seleccionados.

### Limpiador de la categoría por defecto

1. El administrador abre la herramienta desde el menú de productos.
2. La pantalla muestra la categoría por defecto detectada.
3. El administrador ejecuta una simulación.
4. La pantalla muestra los productos encontrados, los limpiables y los que se conservarán sin cambios.
5. El administrador confirma la operación.
6. La herramienta procesa los productos limpiables por lotes y muestra el progreso.
7. Al finalizar, muestra los totales procesados, modificados y omitidos.

## Requisitos de accesibilidad

- Cada campo de búsqueda debe tener una etiqueta accesible.
- Los botones para limpiar la búsqueda deben poder utilizarse con teclado.
- Los estados “Sin resultados” deben comunicarse mediante un mensaje de estado apropiado.
- El foco debe mantenerse predecible al limpiar un filtro.
- La simulación y la confirmación del limpiador deben explicar claramente qué cambios se realizarán.
- El progreso y el resultado final del limpiador deben comunicarse de forma comprensible.
- La interfaz no debe depender únicamente del color para comunicar resultados.
- Todos los textos deben poder traducirse.

## Consideraciones de rendimiento y seguridad

- El filtrado debe ejecutarse sobre los elementos ya renderizados.
- La búsqueda debe utilizar debounce para evitar trabajo innecesario durante la escritura.
- No deben añadirse consultas sobre todo el catálogo para filtrar productos seleccionados.
- No se deben modificar datos de productos durante la búsqueda.
- Deben conservarse los permisos y controles de seguridad nativos de WooCommerce.
- Los eventos deben funcionar aunque WordPress reconstruya dinámicamente las filas de edición.
- Las acciones del limpiador deben protegerse con capacidades y nonce de WordPress.
- El limpiador debe procesar los cambios en lotes para limitar el uso de memoria y tiempo de ejecución.
- La categoría por defecto solo debe eliminarse cuando el producto conserve al menos otra categoría.

## Alcance

Esta propuesta incluye:

- Búsqueda de taxonomías en edición rápida.
- Búsqueda de taxonomías en edición masiva.
- Búsqueda dentro de los productos seleccionados para edición masiva.
- Limpieza controlada de la categoría por defecto de WooCommerce.
- Compatibilidad con taxonomías jerárquicas, no jerárquicas y personalizadas.
- Estados vacíos, limpieza del filtro, accesibilidad y traducciones.
- Simulación, confirmación, procesamiento por lotes y resultados del limpiador.

## Evidencia del prototipo

Existe un prototipo funcional implementado como plugin independiente para la administración. El prototipo demuestra:

- Búsqueda en checklists de taxonomías durante la edición rápida y masiva.
- Búsqueda dentro de la lista de productos seleccionados.
- Conservación de padres e hijos relacionados en taxonomías jerárquicas.
- Estados vacíos cuando una búsqueda no produce coincidencias.
- Limpieza del filtro sin cambiar los valores seleccionados.
- Eventos delegados que siguen funcionando cuando WordPress reconstruye las filas.
- Simulación y limpieza por lotes de la categoría por defecto en productos simples.
- Protección para conservar productos que tienen únicamente la categoría por defecto.

El prototipo puede servir como punto de partida para revisar la interacción y analizar los casos límite antes de seleccionar la implementación definitiva en WooCommerce.

## Espacios para capturas de pantalla

> **Captura 1: Búsqueda de taxonomías en edición rápida**  
> Reemplaza este bloque con una imagen del buscador funcionando dentro de la edición rápida.

> **Captura 2: Búsqueda de taxonomías en edición masiva**  
> Reemplaza este bloque con una imagen del buscador funcionando en categorías, marcas o etiquetas durante la edición masiva.

> **Captura 3: Búsqueda de productos seleccionados**  
> Reemplaza este bloque con una imagen del filtro aplicado a la lista de productos seleccionados.

> **Captura 4: Estado vacío y limpieza del filtro**  
> Reemplaza este bloque con una imagen del mensaje sin resultados y de la lista restaurada después de limpiar la búsqueda.

> **Captura 5: Simulación y progreso del limpiador**
> Reemplaza este bloque con una imagen de la categoría detectada, el resultado de la simulación y el progreso del procesamiento por lotes.

## Criterios de aceptación

- Los checklists de taxonomías tienen un campo de búsqueda en edición rápida y masiva.
- La búsqueda funciona con categorías, marcas, etiquetas y taxonomías personalizadas renderizadas por el editor.
- Las coincidencias jerárquicas mantienen visibles los padres o hijos relacionados necesarios.
- El filtro ignora mayúsculas, minúsculas y diferencias de acentuación.
- La búsqueda muestra un estado claro cuando no existen coincidencias.
- El botón de limpiar restaura el checklist completo.
- La edición masiva permite filtrar los productos ya seleccionados por nombre.
- Filtrar productos seleccionados no elimina ni modifica ninguna selección real.
- Los botones nativos para eliminar productos siguen funcionando.
- Los filtros siguen funcionando después de que WordPress cree o reemplace una fila de edición.
- El limpiador detecta la categoría por defecto configurada en WooCommerce.
- La simulación diferencia los productos limpiables de los que se conservarán sin cambios.
- Solo se procesan productos simples con la categoría por defecto y al menos otra categoría.
- El limpiador procesa los productos por lotes y muestra el progreso y los resultados finales.
- La operación requiere permisos adecuados, nonce y confirmación explícita.
- Ningún producto queda sin categoría como resultado de la limpieza.
- La interfaz es accesible mediante teclado y completamente traducible.
- La implementación no añade consultas sobre todo el catálogo durante el uso normal.

## Preguntas para los mantenedores

1. ¿Existe algún componente de búsqueda del administrador de WooCommerce que deba reutilizarse?
2. ¿Debería el buscador de taxonomías mostrarse siempre o solo cuando una lista supera cierto número de términos?
3. ¿Qué comportamiento jerárquico consideran más adecuado cuando coincide un término padre o un término hijo?
4. ¿Debería la búsqueda de productos seleccionados filtrar únicamente por nombre o también por SKU?
5. ¿Existe algún evento o API recomendada para integrarse con extensiones que reconstruyen los checklists?
6. ¿Debería el limpiador ofrecer una opción para seleccionar una categoría de destino antes de eliminar la categoría por defecto?
7. ¿Debería existir una opción para revisar o exportar la lista de productos que se conservarán sin cambios?

## Conclusión

La búsqueda en taxonomías, la búsqueda de productos seleccionados y la limpieza controlada de la categoría por defecto son mejoras concretas para administrar catálogos WooCommerce grandes. Las búsquedas no modifican datos y el limpiador aplica cambios únicamente después de una simulación, confirmación y validación de categorías adicionales.

El prototipo independiente puede servir como punto de partida para revisar la interacción y determinar la implementación nativa más adecuada para WooCommerce.
