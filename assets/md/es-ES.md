# Simple Easy Social Login (SESLP) — Guía de inicio de sesión social (Español)

> Este documento explica cómo configurar cada proveedor de inicio de sesión  
> (Google, Facebook, LinkedIn, Naver, Kakao, LINE) en el plugin **Simple Easy Social Login (SESLP)**.  
> Todos los inicios de sesión se basan en **OAuth 2.0 / OpenID Connect (OIDC)**.  
> Debe crear una aplicación (cliente) en la consola de cada proveedor e introducir el **Client ID / Client Secret** en SESLP.

---

## 🔧 Guía de configuración común

- **Regla de la Redirect URI:**  
  `https://{tu-dominio}/?social_login={provider}`  
  Ejemplos:

  - Google → `https://example.com/?social_login=google`
  - Facebook → `https://example.com/?social_login=facebook`
  - LinkedIn → `https://example.com/?social_login=linkedin`
  - Naver → `https://example.com/?social_login=naver`
  - Kakao → `https://example.com/?social_login=kakao`
  - LINE → `https://example.com/?social_login=line`

- **HTTPS obligatorio**  
  La mayoría de los proveedores requieren HTTPS y rechazan redirecciones `http://`.

- **Coincidencia exacta**  
  La Redirect URI registrada en la consola debe coincidir al **100 %** con la que envía SESLP  
  (protocolo, subdominio, ruta, barra final y cadena de consulta).

- **El email puede no estar disponible**  
  Algunos proveedores permiten que el usuario niegue compartir su correo. SESLP puede vincular cuentas usando IDs estables del proveedor.

- **Dónde revisar los logs**
  - `/wp-content/seslp-logs/seslp-debug.log`
  - `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

---

## 🌍 Guías por proveedor

> Despliegue cada proveedor a continuación. En Google incluimos la guía completa como referencia del formato. Para los demás, puede pegar su guía en español cuando esté lista.

---

<details open>
  <summary><strong>Google</strong></summary>

> **Scopes recomendados:** `openid email profile`  
> **Regla de Redirect URI:** `https://{dominio}/?social_login=google`

---

### 1) Preparación (Lista obligatoria)

- **HTTPS obligatorio/recomendado** (Use certificados confiables).
- La Redirect URI debe coincidir **exactamente al 100 %**.
- En modo de prueba, solo los **usuarios de prueba** pueden iniciar sesión (máx. 100).
- Si usa URLs de política o términos, agregue **Authorized domains** y verifique el dominio.

### 2) Configurar proyecto y pantalla de consentimiento

1. Ingrese al **Google Cloud Console**
   - <https://console.cloud.google.com/apis/credentials>
2. Cree o seleccione un proyecto.
3. Barra lateral: **APIs & Services → OAuth consent screen**.
4. Tipo de usuario: **External**.
5. Complete la **información del app**.
6. **App domain:**
   - Agregue URLs, dominio raíz y guarde.
   - Verifique propiedad del dominio si se requiere.
7. Configure **Scopes** (`openid email profile`).
8. Agregue **usuarios de prueba** → Guarde.

> Usar solo los scopes básicos suele permitir publicar sin revisión.

### 3) Crear cliente OAuth (Aplicación web)

1. Barra lateral: **APIs & Services → Credentials**.
2. Haga clic en **+ Create Credentials → OAuth client ID**.
3. Tipo: `Web application`.
4. Nombre: `SESLP – Front`.
5. **Authorized redirect URIs:**
   - `https://{dominio}/?social_login=google`
6. Copie **Client ID / Client Secret**.

### 4) Configurar en WordPress

1. Admin WP → **SESLP Settings → Google**.
2. Pegue **Client ID / Secret** → **Guardar**.
3. Pruebe el botón de Google.

### 5) Cambiar a producción

1. Revise el estado de publicación.
2. Quite scopes innecesarios, revise información.
3. Envíe para revisión si usa scopes sensibles.

### 6) Errores comunes

- **redirect_uri_mismatch** – URIs diferentes → Corrija.
- **access_denied** – Restricciones del navegador → Use navegador normal.
- **invalid_client** – ID/Secret erróneos → Verifique.
- **Email vacío** – Revise scopes y privacidad.

</details>

---

<details>
  <summary><strong>Facebook (Meta)</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=facebook`  
> **Permisos recomendados:** `public_profile`, `email`  
> ※ Facebook no utiliza `openid`.

---

### 1) Crear aplicación y agregar producto

1. Ingrese a **Meta for Developers** → Inicie sesión
2. Haga clic en **Crear aplicación** → Tipo general (por ejemplo, Consumidor) → Crear
3. En la barra lateral izquierda, agregue **Facebook Login** desde **Productos**
4. En **Configuración**, revise:
   - **Client OAuth Login:** Activado
   - **Web OAuth Login:** Activado
   - **Valid OAuth Redirect URIs:**
     - Agregue `https://{dominio}/?social_login=facebook`
   - (Opcional) **Aplicar HTTPS:** Recomendado

### 2) Configuración básica (App Settings → Basic)

- **App Domains:** `example.com` (dominio de políticas/términos/página de inicio)
- **Privacy Policy URL:** Página pública
- **Terms of Service URL:** Página pública
- **User Data Deletion:** URL o endpoint de eliminación de datos
- **Categoría / Icono:** Configure y guarde

### 3) Permisos y revisión

- Permisos básicos: **`public_profile`**, opcional: **`email`**
- Normalmente **`email` no requiere revisión**, salvo excepciones
- Permisos avanzados (páginas/anuncios) requieren **App Review** y **Business Verification**

### 4) Cambiar modo (Desarrollo → Producción)

- En la parte superior, cambie **Development → Live**
- Antes de activar:
  - [ ] Políticas / Términos / Eliminación listos
  - [ ] URI exacta ingresada
  - [ ] Solo permisos necesarios
  - [ ] Revisión y verificación completadas

### 5) Configurar en WordPress (SESLP)

1. WP Admin → **SESLP Settings → Facebook**
2. Introduzca **App ID / Secret** → Guarde
3. Pruebe el botón de Facebook en el frontend

### 6) Solución de problemas

- **Can't Load URL / redirect_uri error** → Verifique URI exacta
- **email null** → Usuario sin correo o privado
- **Errores de permisos** → Requieren revisión
- **No se puede activar Live** → URLs faltantes o privadas
</details>

---

<details>
  <summary><strong>LinkedIn</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=linkedin`  
> **Configuración requerida:** Habilitar OpenID Connect (OIDC)  
> **Scopes recomendados:** `openid`, `profile`, `email`

---

### 1) Crear aplicación

1. Ir a **LinkedIn Developers Console**  
   → [Enlace](https://www.linkedin.com/developers/apps)
2. Iniciar sesión
3. **Create app**
4. Completar:
   - Nombre, página, logo, política de privacidad, email
5. Crear

> Modo desarrollo → prueba inmediata

---

### 2) Habilitar OIDC

1. **Products** → Añadir **Sign In with LinkedIn using OpenID Connect**

---

### 3) Configuración OAuth

1. **Auth → OAuth 2.0 settings**
2. Añadir: `https://{dominio}/?social_login=linkedin`
3. Coincidencia exacta
4. Guardar

---

### 4) Client ID / Secret

1. En **Auth**, copiar
2. SESLP → LinkedIn → Pegar → Guardar
3. Probar en frontend

---

### 5) Scopes

| Scope     | Descripción           | Nota          |
| --------- | --------------------- | ------------- |
| `openid`  | Token ID              | **Requerido** |
| `profile` | Nombre, foto, titular | **Requerido** |
| `email`   | Correo electrónico    | **Requerido** |

> Scopes antiguos **obsoletos**

---

### 6) Solución de problemas

- **redirect_uri_mismatch** → URI exacta
- **invalid_client** → ID/Secret incorrectos
- **email NULL** → scope faltante o denegado

---

### 7) Lista de verificación

- [ ] App creada
- [ ] OIDC habilitado
- [ ] URI de redirección registrada
- [ ] ID/Secret en SESLP
- [ ] Prueba en HTTPS

</details>

---

<details>
  <summary><strong>Naver</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=naver`  
> **Scopes recomendados:** `name`, `email`  
> ※ Naver usa **Naver Login (네아로)**, **HTTPS obligatorio**

---

### 1) Registro de aplicación

1. Ir a **Naver Developer Center**  
   → [Enlace](https://developers.naver.com/apps/)
2. Iniciar sesión
3. **Registrar aplicación**
4. Completar:
   - Nombre, API: `Naver Login`
   - Web: URL del sitio, **Callback URL**
5. **Registrar**

> HTTPS obligatorio, subdominios separados

---

### 2) Client ID / Secret

1. **Mis aplicaciones** → copiar

---

### 3) Configuración en WordPress

1. WP Admin → **SESLP → Naver**
2. Pegar ID/Secret
3. Verificar URI exacta
4. **Guardar** → Probar

---

### 4) Permisos

| Dato               | Scope    | Nota                   |
| ------------------ | -------- | ---------------------- |
| Nombre             | `name`   | Predeterminado         |
| Email              | `email`  | Predeterminado         |
| Género, cumpleaños | Separado | **Revisión requerida** |

> Email rechazado → `null`

---

### 5) Solución de problemas

- **redirect_uri_mismatch** → coincidencia exacta
- **HTTP prohibido** → solo HTTPS
- **Subdominio** → registro separado

---

### 6) Lista de verificación

- [ ] App registrada
- [ ] Callback URL exacta
- [ ] HTTPS
- [ ] ID/Secret en SESLP
- [ ] Prueba de consentimiento de email

</details>

---

<details>
  <summary><strong>Kakao</strong></summary>

> **Redirect URI:** `https://{dominio}/?social_login=kakao`  
> **Scopes recomendados:** `profile_nickname`, `profile_image`, `account_email`  
> ※ `account_email` solo tras **verificación de identidad o registro empresarial**  
> ※ **HTTPS obligatorio**, **Client Secret debe activarse**

---

### 1) Crear aplicación

1. Ir a **Kakao Developers**  
   → [https://developers.kakao.com/](https://developers.kakao.com/)
2. Iniciar sesión → **Mis aplicaciones → Añadir app**
3. Completar:
   - Nombre de app, empresa
   - Categoría
   - Aceptar política de operación
4. **Guardar**

---

### 2) Activar Kakao Login

1. **Configuración de producto > Kakao Login**
2. **Activar Kakao Login** → **ON**
3. **Registrar URI de redirección**
   - `https://{dominio}/?social_login=kakao`
   - **Guardar**
4. El dominio debe coincidir **con el dominio de plataforma**

---

### 3) Elementos de consentimiento (Scopes)

1. **Elementos de consentimiento**
2. Añadir y configurar:

| Scope              | Descripción        | Tipo de consentimiento | Nota                       |
| ------------------ | ------------------ | ---------------------- | -------------------------- |
| `profile_nickname` | Apodo              | Obligatorio/Opcional   | Básico                     |
| `profile_image`    | Imagen de perfil   | Obligatorio/Opcional   | Básico                     |
| `account_email`    | Correo electrónico | **Opcional**           | **Verificación requerida** |

3. Indicar **propósito** claramente
4. **Guardar**

> Scopes sensibles requieren **verificación**

---

### 4) Registrar plataforma Web

1. **Configuración de app > Plataforma**
2. **Registrar plataforma Web**
3. Dominio del sitio: `https://{dominio}`
4. **Guardar** → Debe coincidir con URI de redirección

---

### 5) Seguridad – Generar y activar Client Secret

1. **Configuración de producto > Seguridad**
2. **Usar Client Secret** → **ON**
3. **Generar Secret** → Copiar valor
4. **Estado de activación** → **Activo**
5. **Guardar**
   > **Obligatorio activar tras generar**

---

### 6) Obtener clave REST API (Client ID)

1. **Claves de la app**
2. Copiar **Clave REST API** → Usar como **Client ID**

---

### 7) Configuración en WordPress

1. WP Admin → **SESLP Settings → Kakao**
2. **Client ID** = Clave REST API  
   **Client Secret** = Secret generado
3. **Guardar**
4. Probar con **botón Kakao Login**

---

### 8) Solución de problemas

- **redirect_uri_mismatch** → Coincidencia 100 %
- **invalid_client** → Secret no activado o error
- **email vacío** → Rechazado por usuario o no verificado
- **Dominio no coincide** → Plataforma vs URI
- **HTTP prohibido** → **Solo HTTPS**

> **Registros:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 9) Lista de verificación

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

> **Redirect URI:** `https://{dominio}/?social_login=line`  
> **Requerido:** Habilitar OpenID Connect, **solicitar y obtener aprobación para permiso de correo**  
> **Scopes recomendados:** `openid`, `profile`, `email`  
> ※ **HTTPS obligatorio**, **correo requiere aprobación previa**

---

### 1) Crear Provider y Canal

1. Acceder a **LINE Developers Console**  
   → [https://developers.line.biz/console/](https://developers.line.biz/console/)
2. Iniciar sesión con **cuenta LINE Business** (cuenta personal no permitida)
3. Hacer clic en **Crear nuevo provider** → Ingresar nombre → **Create**
4. Bajo el provider → pestaña **Channels**
5. Seleccionar **Crear canal LINE Login**
6. Configurar:
   - **Tipo de canal:** `Línea Login`
   - **Provider:** Seleccionar creado
   - **Región:** País objetivo (ej. `South Korea`, `Japan`)
   - **Nombre / descripción / ícono:** Mostrado en pantalla de consentimiento
7. Aceptar términos → **Create**

---

### 2) Habilitar OpenID Connect y solicitar permiso de email

1. Menú **OpenID Connect**
2. Hacer clic en **Apply** junto a **Email address permission**
3. Completar solicitud:
   - **URL de política de privacidad** (debe ser pública)
   - **Captura de pantalla de la política**
   - Aceptar → **Submit**
4. **El scope `email` solo funciona tras aprobación**  
   → Aprobación: 1–3 días hábiles

---

### 3) Registrar Callback URL y publicar canal

1. Menú **LINE Login**
2. Ingresar **Callback URL**:  
   → `https://{dominio}/?social_login=line`
3. **Coincidencia exacta requerida**:
   - Protocolo: `https://` (**HTTP no permitido**)
   - Dominio, ruta, query string **100% iguales**
4. **Guardar**
5. Cambiar estado del canal a **Published**
   - **Development:** solo pruebas
   - **Published:** en producción

---

### 4) Obtener Channel ID / Secret

1. Parte superior del canal o **Basic settings**
2. **Channel ID** → SESLP **Client ID**  
   **Channel Secret** → SESLP **Client Secret**

---

### 5) Configuración en WordPress

1. WP Admin → **SESLP Settings → LINE**
2. **Client ID** ← Channel ID  
   **Client Secret** ← Channel Secret
3. **Guardar**
4. Probar con **botón LINE Login** en frontend

---

### 6) Solución de problemas

- **redirect_uri_mismatch** → Cualquier diferencia → error → **100% igual**
- **invalid_client** → Secret incorrecto o **no publicado**
- **email NULL** → **Permiso de email no aprobado** o rechazo del usuario
- **HTTP prohibido** → **Solo HTTPS** (localhost HTTPS permitido)
- **Modo Development** → Solo cuentas de prueba pueden iniciar sesión

> **Registros:**  
> `/wp-content/seslp-logs/seslp-debug.log`  
> `/wp-content/debug.log`

---

### 7) Lista de verificación

- [ ] Provider + canal LINE Login creado con cuenta Business
- [ ] Permiso de email **solicitado y aprobado**
- [ ] **Callback URL** registrada exactamente
- [ ] **HTTPS usado**, estado **Published**
- [ ] Channel ID/Secret guardados en SESLP
- [ ] Prueba de inicio de sesión en frontend completada

---

> **Nota:** SESLP soporta completamente **LINE Login v2.1 + OpenID Connect**.  
> **La recolección de correos requiere aprobación previa**.

</details>

---

## 📋 Resumen

| Plan    | Proveedor    | Scopes requeridos / recomendados                     | Ejemplo de Redirect URI                    | Notas                              |
| ------- | ------------ | ---------------------------------------------------- | ------------------------------------------ | ---------------------------------- |
| Gratis  | **Google**   | `openid email profile`                               | `https://{dominio}/?social_login=google`   | Pantalla de consentimiento externa |
| Gratis  | **Facebook** | `public_profile`, `email`                            | `https://{dominio}/?social_login=facebook` | No usa `openid`                    |
| Gratis  | **LinkedIn** | `openid profile email`                               | `https://{dominio}/?social_login=linkedin` | Migración completa a OIDC          |
| De pago | **Naver**    | `email`, `name`                                      | `https://{dominio}/?social_login=naver`    | API “Naver Login”                  |
| De pago | **Kakao**    | `profile_nickname`, `profile_image`, `account_email` | `https://{dominio}/?social_login=kakao`    | Requiere Client Secret             |
| De pago | **LINE**     | `openid profile email`                               | `https://{dominio}/?social_login=line`     | Debe estar “Published”             |
