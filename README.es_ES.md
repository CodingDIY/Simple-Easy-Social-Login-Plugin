# Simple Easy Social Login

Simple Easy Social Login es un plugin de WordPress ligero y fácil de usar que permite añadir una funcionalidad de inicio de sesión social rápida y fluida a tu sitio web.

Es compatible con **Google, Facebook y LinkedIn (Gratis)**, así como con **Naver, Kakao y Line (Premium)**,  
y está diseñado para funcionar especialmente bien en sitios dirigidos a usuarios de Asia (Corea, Japón, China), además de Europa y América del Sur.

El plugin se integra perfectamente con las páginas de inicio de sesión y registro predeterminadas de WordPress,  
y también es compatible con los formularios de inicio de sesión y registro de WooCommerce.  
Las imágenes de perfil de las redes sociales pueden sincronizarse automáticamente con los perfiles de usuario de WordPress.

El plugin está construido sobre una **arquitectura de proveedores (Providers) extensible**,  
lo que permite añadir nuevos proveedores OAuth en el futuro como plugins Add-on independientes, si es necesario.

---

## ✨ Características

- Inicio de sesión con Google (Gratis)
- Inicio de sesión con Facebook (Gratis)
- Inicio de sesión con LinkedIn (Gratis)
- Inicio de sesión con Naver (Premium)
- Inicio de sesión con Kakao (Premium)
- Inicio de sesión con Line (Premium)
- Sincronización automática de avatares de usuario
- Vinculación automática de usuarios existentes de WordPress mediante el correo electrónico
- URLs de redirección personalizadas después del inicio de sesión, cierre de sesión y registro
- Interfaz de administración simple y clara para la configuración de proveedores
- Soporte de shortcode: [se_social_login]
- Visualización automática en los formularios de inicio de sesión y registro de WordPress
- Soporte para formularios de inicio de sesión y registro de WooCommerce (opcional)
- Estructura ligera que sigue los estándares de codificación de WordPress
- No se crean tablas innecesarias en la base de datos
- Sistema de proveedores extensible que permite añadir nuevos proveedores OAuth mediante plugins Add-on

---

## 🐞 Registro de depuración

SESLP incluye un sistema de registro de depuración integrado para diagnosticar problemas de OAuth e inicio de sesión social.

Puede consultar explicaciones detalladas directamente en el panel de administración de WordPress:
**SESLP → Guides → Debug Log & Troubleshooting**

Los archivos de registro se generan en:

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (cuando `WP_DEBUG_LOG` está habilitado)

---

## 🚀 Instalación

1. Sube el plugin al directorio `/wp-content/plugins/simple-easy-social-login/`.
2. Activa el plugin desde **Plugins → Plugins instalados** en el panel de administración de WordPress.
3. Ve a **Ajustes → Simple Easy Social Login**.
4. Introduce el Client ID y el Client Secret de cada proveedor de inicio de sesión social.
5. Guarda los cambios.
6. Verifica que los botones de inicio de sesión social se muestren correctamente en el frontend.

---

## ❓ Preguntas frecuentes

### ¿Este plugin funciona con WooCommerce?

Sí. Se integra con los formularios de inicio de sesión y registro de WooCommerce.

### El inicio de sesión con WooCommerce funciona, pero el comportamiento de redirección es diferente. ¿Es normal?

Sí. Cuando WooCommerce está activo, los usuarios suelen ser redirigidos a la página **Mi cuenta** después de iniciar sesión.  
La URL de redirección puede personalizarse desde la configuración del plugin o mediante los filtros disponibles.

### ¿Qué debo comprobar si el inicio de sesión social no funciona en un sitio WooCommerce?

Por favor, verifica lo siguiente:

- WooCommerce está actualizado a una versión estable reciente
- El proveedor de inicio de sesión social está habilitado en la configuración del plugin
- El Client ID y el Client Secret son correctos
- Las URLs de redirección / callback están registradas correctamente en la consola del desarrollador del proveedor
- Las plantillas personalizadas de inicio de sesión o checkout no eliminan los hooks predeterminados de WooCommerce
- El registro de depuración está habilitado y se ha revisado `/wp-content/SESLP-debug.log`

### ¿Puedo usar solo el inicio de sesión con Google?

Sí. Cada proveedor se puede activar o desactivar de forma individual.

### ¿Cuándo necesito una licencia Premium?

Se requiere una licencia Premium para usar los inicios de sesión de **Naver, Kakao y Line**.  
Google, Facebook y LinkedIn están disponibles de forma gratuita.

### ¿Hay disponible un shortcode?

Sí. Puedes insertar los botones de inicio de sesión social en cualquier lugar usando el siguiente shortcode:
[se_social_login]

### ¿Se importan automáticamente los avatares de los usuarios?

Sí. Para proveedores compatibles como Google y Facebook, las imágenes de perfil pueden importarse automáticamente y sincronizarse como avatares de WordPress.

---

## 🖼 Capturas de pantalla

1. Botones de inicio de sesión social mostrados en la página de inicio de sesión de WordPress (diseño en lista).
2. Diseño de solo iconos para los botones de inicio de sesión social en la pantalla de acceso.
3. Opciones de redirección después del inicio de sesión (escritorio, perfil, página principal o URL personalizada).
4. Registro de depuración, opciones de diseño de la interfaz, shortcode y ajustes de desinstalación.
5. Guía de configuración integrada que explica las reglas de redirección OAuth y los requisitos comunes.
6. Guía paso a paso para configurar la pantalla de consentimiento y el cliente OAuth de Google.
7. Ajustes de administrador para las credenciales de inicio de sesión de Google, Facebook y LinkedIn.
8. Ajustes de administrador para los proveedores de inicio de sesión Naver, Kakao y LINE.
9. Reglas unificadas de URI de redirección utilizadas en todos los proveedores compatibles.
10. Ubicación del archivo de registro de depuración y vista general de solución de problemas.
11. Errores comunes de OAuth, soluciones recomendadas y ubicación de los registros de depuración.

---

## 📝 Registro de cambios (Changelog)

### 1.9.9

- Finalización de capturas de pantalla y documentación para el lanzamiento público
- Se añadieron descripciones completas de capturas de pantalla que cubren el flujo de inicio de sesión, ajustes, guías y solución de problemas
- Pequeñas mejoras de limpieza y coherencia en la documentación

### 1.9.8

- Corregido un error fatal de tipo en `SESLP_Avatar::resolve_user()` asegurando un valor de retorno `WP_User|null`
- Mejora en el manejo del avatar de respaldo:
  - Uso seguro del avatar predeterminado de WordPress cuando la imagen del perfil social falta o es inválida
  - Prevención de imágenes de avatar rotas (por ejemplo, problemas con imágenes de perfil de LinkedIn)
- Pequeñas mejoras de estabilidad relacionadas con la visualización de avatares

### 1.9.7

- Se añadió la sección de registro de depuración a la README
- Guía detallada de logs integrada en las guías del administrador (multilingüe)
- Unificación de la ruta del archivo de logs (`/wp-content/SESLP-debug.log`)
- Limpieza y mejora de la coherencia de la documentación

### 1.9.6

- Mejora de la usabilidad de la página de ajustes
- Añadido un interruptor para mostrar/ocultar las claves secretas
- Corrección de conflictos con los estilos del núcleo de WordPress
- Mejora de la detección de los planes Pro / Max

### 1.9.5

- Refactorización mayor
- Unificación de helpers y mejora de la arquitectura de proveedores
- Limpieza de la interfaz de ajustes
- Mejora de la estabilidad y el mantenimiento

### 1.9.3

- Actualización de las traducciones de las guías
- Añadida la visualización del shortcode en la página de ajustes

### 1.9.2

- Limpieza de la estructura interna
- Añadida la clase cargadora de guías
- Reestructuración de las plantillas
- Mejora de la estabilidad del cargador de ajustes y CSS

### 1.9.1

- Añadida la página de guía de administración
- Renderizado de documentación multilingüe basado en Markdown (Parsedown)
- Mejora del estilo de la interfaz de usuario

### 1.9.0

- Fase de preparación para una refactorización a gran escala
- Ampliación de los helpers i18n
- Mejora del formateo seguro y del sistema de registros

### 1.7.23

- Actualización de traducciones

### 1.7.22

- Mejora de los mensajes de depuración para mostrar el proveedor utilizado anteriormente

### 1.7.21

- Mostrar el nombre del proveedor en los mensajes de error cuando se detectan correos electrónicos duplicados
- Ocultar automáticamente los mensajes de error después de 10 segundos mediante JavaScript

### 1.7.19

- Prevención de la creación de cuentas duplicadas con el mismo correo electrónico
- Mejora del flujo OAuth:
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Eliminación de los tooltips de los campos Google Client ID / Secret
- Limpieza de la estructura del código
- Eliminación del texto “(Email required)” del botón de inicio de sesión de Line

### 1.7.17

- Corrección de problemas relacionados con el inicio de sesión de Line:
  - Prevención de usuarios duplicados al volver a iniciar sesión
  - Corrección de la reaparición de la página `/complete-profile`
  - Permitir la actualización del correo electrónico para corregir el error “Invalid request”
- Unificación de los registros de depuración con `SESLP_Logger`

### 1.7.16

- Enmascarado de claves de licencia en los registros de depuración (ej.: abc\*\*\*\*123)
- Añadida una guía para comprobar `wp_options` durante la depuración
- Añadida una notificación de administrador cuando falla la escritura de registros

### 1.7.15

- Corrección de errores al escribir los registros de depuración
- Aplicación de la zona horaria local de WordPress a las marcas de tiempo
- Añadidos registros de depuración al guardar los ajustes

### 1.7.5

- Aplicación de los últimos parches de seguridad
- Optimización del rendimiento y mejora de la experiencia de usuario

### 1.7.0

- Mejora de la sincronización de los botones de inicio de sesión social
- Refuerzo de la seguridad y corrección de errores

### 1.7.3

- Mejora del sistema de depuración
- Añadido un directorio debug dedicado

### 1.6.0

- Restauración de la visualización de la sección de clave de licencia al seleccionar Plus / Premium

### 1.5.0

- Registro de la opción `seslp_license_type`
- Corrección del problema por el cual el tipo de licencia se restablecía a Free al guardar

### 1.4.0

- Corrección del problema de carga de `style.css` en el área de administración mediante `admin_enqueue_scripts`

### 1.3.0

- Mejora de la interfaz de los botones de opción
- Movimiento del CSS inline a `style.css`

### 1.2.0

- Añadida la selección del tipo de licencia (Free / Plus / Premium)
- Mejora de la alineación de la interfaz de ajustes

### 1.1.0

- Añadido soporte multilingüe y carga de archivos de traducción
- Mejora de la lógica de autenticación

### 1.0.0

- Lanzamiento inicial
- Añadidos inicios de sesión sociales con Google, Facebook, Naver, Kakao, Line y Weibo

---

## 📄 Licencia

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
