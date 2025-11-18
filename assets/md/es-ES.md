> Este documento explica cómo configurar cada proveedor de inicio de sesión  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) en el plugin **Simple Easy Social Login (SESLP)**.  
> Todos los inicios de sesión se basan en **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Debe crear una aplicación (cliente) en la consola de cada proveedor e introducir el **Client ID / Client Secret** en SESLP.

---

## 🔧 Guía de configuración común

#### 1) **Regla de la Redirect URI:**

`https://{tu-dominio}/?social_login={provider}`

Ejemplos:

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS obligatorio**

La mayoría de los proveedores requieren HTTPS y rechazan redirecciones `http://`.

#### 3) **Coincidencia exacta**

La Redirect URI registrada en la consola debe coincidir al **100 %** con la que envía SESLP  
 (protocolo, subdominio, ruta, barra final y cadena de consulta).

#### 4) **El email puede no estar disponible**

Algunos proveedores permiten que el usuario niegue compartir su correo. SESLP puede vincular cuentas usando IDs estables del proveedor.

##### 5) **Dónde revisar los logs**

- `/wp-content/seslp-logs/seslp-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guías por proveedor

> Despliegue cada proveedor a continuación. En Google incluimos la guía completa como referencia del formato. Para los demás, puede pegar su guía en español cuando esté lista.

---

<details open>
  <summary><strong>Google</strong></summary>

> - **Scopes recomendados:** `openid email profile`
> - **Regla de Redirect URI:** `https://{dominio}/?social_login=google`

---

#### 1) Preparación (Lista obligatoria)

(1) **HTTPS obligatorio/recomendado** (Use certificados confiables).

(2) La Redirect URI debe coincidir **exactamente al 100 %**.

(3) En modo de prueba, solo los **usuarios de prueba** pueden iniciar sesión (máx. 100).

(4) Si usa URLs de política o términos, agregue **Authorized domains** y verifique el dominio.

#### 2) Configurar proyecto y pantalla de consentimiento

(1) Ingrese al **Google Cloud Console**
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) Cree o seleccione un proyecto.

(3) Barra lateral: **APIs & Services → OAuth consent screen**.

(4) Tipo de usuario: **External**.

(5) Complete la **información del app**.

(6) **App domain:**

- Agregue URLs, dominio raíz y guarde.
- Verifique propiedad del dominio si se requiere.

(7) Configure **Scopes** (`openid`, `email`, `profile`).

(8) Agregue **usuarios de prueba** → Guarde.

> Usar solo los scopes básicos suele permitir publicar sin revisión.

#### 3) Crear cliente OAuth (Aplicación web)

(1) Barra lateral: **APIs & Services → Credentials**.

(2) Haga clic en **+ Create Credentials → OAuth client ID**.

(3) Tipo: `Web application`.

(4) Nombre: `SESLP – Front`.

(5) **Authorized redirect URIs:**

- `https://{dominio}/?social_login=google`

(6) Haz clic en **Create** y luego copia el **Client ID / Client Secret** que se muestran.

> (Opcional) Normalmente no es necesario configurar **Authorized JavaScript origins** para este plugin, ya que utiliza el flujo de autorización por código.

#### 4) Configurar en WordPress

(1) Admin WP → **SESLP Settings → Google**.

(2) Pegue **Client ID / Secret** → **Guardar**.

(3) Pruebe el botón de Google.

#### 5) Cambiar de modo de prueba a producción

(1) Revise **OAuth consent screen → Publishing status**.

(2) Para cambiar de prueba a producción:

- Verifique que la información de la app (logo / dominio / políticas / términos) sea correcta.
- Elimine los scopes innecesarios y mantenga solo los requeridos.
- Envíe la solicitud de revisión si utiliza scopes sensibles.

(3) Después de cambiar a producción, cualquier cuenta de Google podrá iniciar sesión.

#### 6) Errores comunes y soluciones

(1) **redirect_uri_mismatch**

→ Ocurre cuando la Redirect URI registrada en la consola y la URI real de la solicitud difieren incluso mínimamente (protocolo, subdominio, barra, cadena de consulta).  
Corrige la URI para que coincida **al 100 %**.

(2) **access_denied / disallowed_useragent**

→ Restricciones del navegador o del entorno dentro de una app.  
Intente nuevamente en un navegador normal.

(3) **invalid_client / unauthorized_client**

→ Error en el Client ID/Secret o estado incorrecto de la app (eliminada/desactivada).  
Vuelva a generarlos o verifíquelos.

(4) **Email vacío**

→ Compruebe si el scope `email` está incluido, si aparece correctamente en la pantalla de consentimiento y la configuración de privacidad/visibilidad del correo de la cuenta.  
Explique claramente en la pantalla de consentimiento para qué se solicita el permiso de email.

> **Revisar logs:**
>
> - `wp-content/seslp-logs/seslp-debug.log` (debug del plugin activado)
> - `wp-content/debug.log` (WP_DEBUG, WP_DEBUG_LOG = true)

#### 7) Lista de verificación (resumen)

- [ ] Pantalla de consentimiento OAuth: configurar información de la app / dominio / políticas / términos / scopes / usuarios de prueba
- [ ] Credenciales: crear cliente de tipo **Web Application**
- [ ] Registrar la Redirect URI: `https://{dominio}/?social_login=google`
- [ ] SESLP: guardar Client ID/Secret y probar el inicio de sesión
- [ ] Cambiar el estado de publicación al pasar a producción (enviar a revisión si es necesario)

</details>

---

<details>
  <summary><strong>Facebook</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=facebook`
> - **Permisos recomendados:** `public_profile`, `email`
> - Facebook no utiliza `openid`.

---

#### 1) Crear aplicación y agregar producto

(1) Ingrese a **Meta for Developers** → Inicie sesión
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) Haga clic en **Crear aplicación** → Tipo general (por ejemplo, Consumidor) → Crear

(3) En la barra lateral izquierda, agregue **Facebook Login** desde **Productos**

(4) En **Configuración**, revise:

- **Client OAuth Login:** Activado
- **Web OAuth Login:** Activado
- **Valid OAuth Redirect URIs:**
  - Agregue `https://{dominio}/?social_login=facebook`
- (Opcional) **Aplicar HTTPS:** Recomendado

#### 2) Configuración básica (App Settings → Basic)

(1) **App Domains:** `example.com` (dominio de políticas/términos/página de inicio)

(2) **Privacy Policy URL:** Página pública

(3) **Terms of Service URL:** Página pública

(4) **User Data Deletion:** URL o endpoint de eliminación de datos

(5) **Categoría / Icono:** Configure y guarde

#### 3) Permisos y revisión

(1) Permisos básicos: **`public_profile`**, opcional: **`email`**

(2) Normalmente **`email` no requiere revisión**, salvo excepciones

(3) Permisos avanzados (páginas/anuncios) requieren **App Review** y **Business Verification**

#### 4) Cambiar modo (Desarrollo → Producción)

- En la parte superior, cambie **Development → Live**

#### 5) Antes de activar:

- [ ] Políticas / Términos / Eliminación listos
- [ ] URI exacta ingresada
- [ ] Solo permisos necesarios
- [ ] Revisión y verificación completadas

#### 6) Configurar en WordPress (SESLP)

(1) WP Admin → **SESLP Settings → Facebook**

(2) Introduzca **App ID / Secret** → Guarde

(3) Pruebe el botón de Facebook en el frontend

#### 7) Solución de problemas

(1) **Can't Load URL / redirect_uri error**

→ Asegúrate de que la **misma URI exacta** esté registrada en **Valid OAuth Redirect URIs** (incluyendo protocolo, subdominio, barra final y cadena de consulta).

(2) **email null**

→ El usuario no tiene un correo registrado en Facebook o lo tiene como privado. Prepara una **lógica de vinculación basada en ID** y explica claramente en la pantalla de consentimiento para qué se usará el permiso de correo.

(3) **Errores relacionados con permisos**

→ Si el scope solicitado excede el conjunto básico, se requiere **App Review / Business Verification**.

(4) **No se puede cambiar a Live**

→ Ocurre cuando la URL de política/términos/guía de eliminación de datos **falta o no es pública**. Debes proporcionar una URL accesible públicamente.

</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=linkedin`
> - **Configuración requerida:** Habilitar OpenID Connect (OIDC)
> - **Scopes recomendados:** `openid`, `profile`, `email`

---

#### 1) Crear una aplicación

(1) Ir a **LinkedIn Developers Console**

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) Iniciar sesión con la cuenta de LinkedIn

(3) Hacer clic en **Create app**

(4) Completar los campos requeridos:

- **Nombre de la aplicación:** p. ej., `MySite LinkedIn Login`
- **Página de LinkedIn:** Seleccionar o “None”
- **Logotipo de la app:** PNG/JPG de 100×100 o superior
- **Privacy Policy URL / Business Email:** Válidos y públicos

(5) Hacer clic en **Create app**

> **Development Mode** activado por defecto → permite probar el inicio de sesión con `openid`, `profile`, `email` **sin necesidad de publicar**

#### 2) Activar OpenID Connect (OIDC)

(1) Ir a la pestaña **Products**

(2) Buscar **Sign In with LinkedIn using OpenID Connect**

(3) Hacer clic en **Add product** → Aprobación casi inmediata

(4) La configuración de OIDC aparecerá en la pestaña **Auth**

> **Scopes OIDC requeridos**
>
> - `openid` → ID token
> - `profile` → Nombre, foto, titular
> - `email` → Dirección de correo electrónico

#### 3) Configuración de OAuth 2.0 (pestaña Auth)

(1) Ir a **Auth → OAuth 2.0 settings**

(2) Añadir en **Redirect URLs**:

→ `https://{dominio}/?social_login=linkedin`

(3) Se requiere **coincidencia exacta** (protocolo, subdominio, barra final, cadena de consulta)

(4) Registra varias si es necesario:

- Local: `https://localhost:3000/?social_login=linkedin`
- Staging: `https://staging.example.com/?social_login=linkedin`
- Producción: `https://example.com/?social_login=linkedin`

(5) Hacer clic en **Save**

#### 4) Obtener el Client ID / Client Secret

(1) En la pestaña **Auth**, localizar:

- **Client ID**
- **Client Secret**

(2) En WordPress Admin → **SESLP Settings → LinkedIn**

(3) Pegar ambos valores → **Guardar**

(4) Probar con el **botón de inicio de sesión de LinkedIn** en el frontend

> **Seguridad:**
>
> - Nunca expongas el Client Secret
> - Usa **Regenerate secret** si se ve comprometido

### 5) Scopes

| Scope     | Descripción           | Nota          |
| --------- | --------------------- | ------------- |
| `openid`  | Token ID              | **Requerido** |
| `profile` | Nombre, foto, titular | **Requerido** |
| `email`   | Correo electrónico    | **Requerido** |

> **Scopes heredados (`r_liteprofile`, `r_emailaddress`)**
>
> - **Obsoletos a partir de 2024**
> - **No disponibles para aplicaciones nuevas**

#### 6) Solución de problemas

(1) **redirect_uri_mismatch**

→ Las URIs difieren aunque sea mínimamente → asegúrate de una **coincidencia del 100 %**

(2) **invalid_client**

→ ID/Secret incorrectos o aplicación inactiva → revisar o regenerar credenciales

(3) **email NULL**

→ El usuario denegó el permiso o falta el scope `email` → explica el uso del correo en la pantalla de consentimiento

(4) **insufficient_scope**

→ El scope solicitado no está aprobado → verifica que OIDC esté activado correctamente

(5) **OIDC no habilitado**

→ Falta **Sign In with LinkedIn using OpenID Connect** en la sección Products

> **Logs:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Lista de verificación (resumen)

- [ ] App creada
- [ ] Producto **OpenID Connect** añadido
- [ ] Redirect URI registrada con coincidencia exacta
- [ ] Client ID/Secret guardados en SESLP
- [ ] Scopes: `openid profile email` (sin scopes heredados)
- [ ] Prueba de inicio de sesión en frontend con HTTPS completada

---

> **Nota:**
>
> - SESLP admite completamente el **flujo OIDC**.
> - El OAuth 2.0 heredado **ya no es compatible**.
> - Utiliza siempre **OpenID Connect** para nuevas integraciones.

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=naver`
> - **Scopes recomendados:** `name`, `email`
> - Naver usa **Naver Login (네아로)**, **HTTPS obligatorio**

---

#### 1) Registro de la aplicación

(1) Ir al **Naver Developer Center**

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) Iniciar sesión con la cuenta de Naver

(3) Hacer clic en **Application → Register Application**

(4) Completar los campos requeridos:

- **Application Name:** p. ej., `MySite Naver Login`
- **API Usage:** Seleccionar `Naver Login (네아로)`
- **Add Environment → Web**
- **Service URL:** `https://example.com`
- **Callback URL:** `https://example.com/?social_login=naver`

(5) Aceptar los términos → **Register**

> **Nota:**
>
> - **HTTPS obligatorio** → HTTP no está permitido
> - **Cada subdominio debe registrarse por separado**

#### 2) Obtener el Client ID / Client Secret

(1) Ir a **My Applications**

(2) Hacer clic en la aplicación → copiar **Client ID** y **Client Secret**

#### 3) Configuración en WordPress (Plugin)

(1) WP Admin → **SESLP Settings → Naver**

(2) Pegar **Client ID / Client Secret**

(3) Asegurarse de que la **Redirect URI** coincida exactamente:  
`https://{dominio}/?social_login=naver`

(4) **Guardar** → Probar con el **botón de inicio de sesión de Naver** en el frontend

### 4) Permisos

| Dato               | Scope    | Nota                   |
| ------------------ | -------- | ---------------------- |
| Nombre             | `name`   | Predeterminado         |
| Email              | `email`  | Predeterminado         |
| Género, cumpleaños | Separado | **Revisión requerida** |

> - Los usuarios pueden **aceptar o rechazar** en la pantalla de consentimiento.
> - Si el usuario rechaza el correo → `email = null` → usar **vinculación basada en ID**.
> - Los datos sensibles requieren **revisión de la aplicación de Naver**.

#### 5) Solución de problemas

(1) **Redirect URI mismatch**

→ Incluso una pequeña diferencia provoca error → asegúrate de una **coincidencia del 100 %**

(2) **HTTP error**

→ Debe utilizarse **HTTPS**

(3) **Subdomain error**

→ Registra cada subdominio por separado

(4) **email NULL**

→ El usuario rechazó el correo o lo tiene como privado → prepara una lógica de vinculación basada en ID

(5) **Review needed**

→ Inicio de sesión básico: **sin revisión**  
→ Datos adicionales: **revisión requerida**

#### 6) Lista de verificación (resumen)

- [ ] App registrada en Naver Developer Center
- [ ] **Callback URL** registrada exactamente
- [ ] Uso de **HTTPS** verificado
- [ ] Subdominios registrados por separado (si es necesario)
- [ ] Client ID/Secret guardados en SESLP
- [ ] Probado el comportamiento de aceptación/rechazo de email
- [ ] Prueba de inicio de sesión en el frontend completada

---

> **Nota:**
>
> - SESLP admite completamente **Naver Login (네아로)**.
> - El inicio de sesión básico (`name`, `email`) está **disponible sin revisión**.

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=kakao`
> - **Scopes recomendados:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email` solo tras **verificación de identidad o registro empresarial**
> - **HTTPS obligatorio**, **Client Secret debe activarse**

---

#### 1) Crear aplicación

(1) Ir a **Kakao Developers**

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) Iniciar sesión → **Mis aplicaciones → Añadir app**

(3) Completar:

- Nombre de app, empresa
- Categoría
- Aceptar política de operación

(4) **Guardar**

#### 2) Activar Kakao Login

(1) **Configuración de producto > Kakao Login**

(2) **Activar Kakao Login** → **ON**

(3) **Registrar URI de redirección**

- `https://{dominio}/?social_login=kakao`
- **Guardar**

(4) El dominio debe coincidir **con el dominio de plataforma**

#### 3) Elementos de consentimiento (Scopes)

(1) **Elementos de consentimiento**

(2) Añadir y configurar:

| Scope              | Descripción        | Tipo de consentimiento | Nota                       |
| ------------------ | ------------------ | ---------------------- | -------------------------- |
| `profile_nickname` | Apodo              | Obligatorio/Opcional   | Básico                     |
| `profile_image`    | Imagen de perfil   | Obligatorio/Opcional   | Básico                     |
| `account_email`    | Correo electrónico | **Opcional**           | **Verificación requerida** |

(3) Indicar **propósito** claramente

(4) **Guardar**

> Scopes sensibles requieren **verificación**

#### 4) Registrar plataforma Web

(1) **Configuración de app > Plataforma**

(2) **Registrar plataforma Web**

(3) Dominio del sitio: `https://{dominio}`

(4) **Guardar** → Debe coincidir con URI de redirección

#### 5) Seguridad – Generar y activar Client Secret

(1) **Configuración de producto > Seguridad**

(2) **Usar Client Secret** → **ON**

(3) **Generar Secret** → Copiar valor

(4) **Estado de activación** → **Activo**

(5) **Guardar**

> **Obligatorio activar tras generar**

#### 6) Obtener clave REST API (Client ID)

(1) **Claves de la app**

(2) Copiar **Clave REST API** → Usar como **Client ID**

#### 7) Configuración en WordPress

(1) WP Admin → **SESLP Settings → Kakao**

(2) **Client ID** = Clave REST API  
 **Client Secret** = Secret generado

(3) **Guardar**

(4) Probar con **botón Kakao Login**

#### 8) Solución de problemas

(1) **redirect_uri_mismatch** → Coincidencia 100 %

(2) **invalid_client** → Secret no activado o error

(3) **email vacío** → Rechazado por usuario o no verificado

(4) **Dominio no coincide** → Plataforma vs URI
(5) **HTTP prohibido** → **Solo HTTPS**

> **Registros:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 9) Lista de verificación

- [ ] Kakao Login activado
- [ ] URI de redirección registrada
- [ ] Dominio de plataforma Web registrado
- [ ] Consentimientos configurados
- [ ] Client Secret generado + activado
- [ ] Clave REST API / Secret en SESLP
- [ ] Probado en frontend HTTPS

</details>

---

<details>
  <summary><strong>LINE</strong></summary>

> - **Redirect URI:** `https://{dominio}/?social_login=line`
> - **Requerido:** Habilitar OpenID Connect, **solicitar y obtener aprobación para permiso de correo**
> - **Scopes recomendados:** `openid`, `profile`, `email`
> - **HTTPS obligatorio**, **correo requiere aprobación previa**

---

#### 1) Crear Provider y Canal

(1) Acceder a **LINE Developers Console**

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) Iniciar sesión con **cuenta LINE Business** (cuenta personal no permitida)

(3) Hacer clic en **Crear nuevo provider** → Ingresar nombre → **Create**

(4) Bajo el provider → pestaña **Channels**

(5) Seleccionar **Crear canal LINE Login**

(6) Configurar:

- **Tipo de canal:** `Línea Login`
- **Provider:** Seleccionar creado
- **Región:** País objetivo (ej. `South Korea`, `Japan`)
- **Nombre / descripción / ícono:** Mostrado en pantalla de consentimiento

(7) Aceptar términos → **Create**

#### 2) Habilitar OpenID Connect y solicitar permiso de email

(1) Menú **OpenID Connect**

(2) Hacer clic en **Apply** junto a **Email address permission**

(3) Completar solicitud:

- **URL de política de privacidad** (debe ser pública)
- **Captura de pantalla de la política**
- Aceptar → **Submit**

(4) **El scope `email` solo funciona tras aprobación**  
 → Aprobación: 1–3 días hábiles

#### 3) Registrar Callback URL y publicar canal

(1) Menú **LINE Login**

(2) Ingresar **Callback URL**:

→ `https://{dominio}/?social_login=line`

(3) **Coincidencia exacta requerida**:

- Protocolo: `https://` (**HTTP no permitido**)
- Dominio, ruta, query string **100% iguales**

(4) **Guardar**

(5) Cambiar estado del canal a **Published**

- **Development:** solo pruebas
- **Published:** en producción

#### 4) Obtener Channel ID / Secret

(1) Parte superior del canal o **Basic settings**

(2) **Channel ID** → SESLP **Client ID**  
 **Channel Secret** → SESLP **Client Secret**

#### 5) Configuración en WordPress

(1) WP Admin → **SESLP Settings → LINE**

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **Guardar**

(4) Probar con **botón LINE Login** en frontend

#### 6) Solución de problemas

(1) **redirect_uri_mismatch** → Cualquier diferencia → error → **100% igual**

(2) **invalid_client** → Secret incorrecto o **no publicado**

(3) **email NULL** → **Permiso de email no aprobado** o rechazo del usuario

(4) **HTTP prohibido** → **Solo HTTPS** (localhost HTTPS permitido)

(5) **Modo Development** → Solo cuentas de prueba pueden iniciar sesión

> **Registros:**
>
> - `/wp-content/seslp-logs/seslp-debug.log`
> - `/wp-content/debug.log`

#### 7) Lista de verificación

- [ ] Provider + canal LINE Login creado con cuenta Business
- [ ] Permiso de email **solicitado y aprobado**
- [ ] **Callback URL** registrada exactamente
- [ ] **HTTPS usado**, estado **Published**
- [ ] Channel ID/Secret guardados en SESLP
- [ ] Prueba de inicio de sesión en frontend completada

> **Nota:** SESLP soporta completamente
>
> - **LINE Login v2.1 + OpenID Connect**.
> - **La recolección de correos requiere aprobación previa**.

</details>
