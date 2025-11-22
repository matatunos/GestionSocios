# API REST - Gestión Socios

API REST para gestión de asociaciones con autenticación JWT y operaciones CRUD completas.

## Base URL

```
http://192.168.1.7/index.php?page=api
```

## Autenticación

La API utiliza tokens JWT (JSON Web Tokens) para la autenticación. Todas las peticiones (excepto `/auth`) requieren el header:

```
Authorization: Bearer {token}
```

### Obtener Token

**Endpoint:** `POST /index.php?page=api&resource=auth`

**Body:**
```json
{
  "email": "admin@example.com",
  "password": "tu_contraseña"
}
```

**Respuesta exitosa (200):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "admin@example.com",
    "name": "Administrator",
    "role": "admin"
  }
}
```

**Token válido por:** 24 horas

---

## Endpoints

### 1. Socios (Members)

#### Listar todos los socios
```http
GET /index.php?page=api&resource=members
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "dni": "12345678A",
      "email": "juan@example.com",
      "phone": "123456789",
      "address": "Calle Principal 123",
      "join_date": "2024-01-15",
      "status": "active"
    }
  ],
  "total": 1
}
```

#### Obtener un socio específico
```http
GET /index.php?page=api&resource=members&id=1
Authorization: Bearer {token}
```

#### Crear nuevo socio
```http
POST /index.php?page=api&resource=members
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "María García",
  "dni": "87654321B",
  "email": "maria@example.com",
  "phone": "987654321",
  "address": "Avenida Central 456",
  "join_date": "2024-11-22",
  "category_id": 1
}
```

**Respuesta (201):**
```json
{
  "id": 2,
  "message": "Socio creado exitosamente"
}
```

#### Actualizar socio
```http
PUT /index.php?page=api&resource=members&id=1
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Juan Pérez Actualizado",
  "phone": "111222333"
}
```

#### Eliminar socio
```http
DELETE /index.php?page=api&resource=members&id=1
Authorization: Bearer {token}
```

---

### 2. Eventos (Events)

#### Listar todos los eventos
```http
GET /index.php?page=api&resource=events
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Asamblea General 2024",
      "description": "Asamblea anual de socios",
      "date": "2024-12-15",
      "time": "18:00:00",
      "location": "Salón Principal",
      "max_attendees": 100,
      "registration_required": 1
    }
  ],
  "total": 1
}
```

#### Obtener un evento específico
```http
GET /index.php?page=api&resource=events&id=1
Authorization: Bearer {token}
```

#### Crear nuevo evento
```http
POST /index.php?page=api&resource=events
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Cena de Navidad",
  "description": "Cena anual navideña",
  "date": "2024-12-20",
  "time": "20:00:00",
  "location": "Restaurante El Pino",
  "max_attendees": 50,
  "registration_required": 1,
  "price": 25.00
}
```

#### Actualizar evento
```http
PUT /index.php?page=api&resource=events&id=1
Authorization: Bearer {token}
Content-Type: application/json

{
  "max_attendees": 120
}
```

#### Eliminar evento
```http
DELETE /index.php?page=api&resource=events&id=1
Authorization: Bearer {token}
```

---

### 3. Donaciones (Donations)

#### Listar todas las donaciones
```http
GET /index.php?page=api&resource=donations
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "data": [
    {
      "id": 1,
      "donor_id": 1,
      "donor_name": "Empresa XYZ",
      "amount": 500.00,
      "date": "2024-11-01",
      "type": "monetary",
      "description": "Donación anual"
    }
  ],
  "total": 1
}
```

#### Obtener una donación específica
```http
GET /index.php?page=api&resource=donations&id=1
Authorization: Bearer {token}
```

#### Registrar nueva donación
```http
POST /index.php?page=api&resource=donations
Authorization: Bearer {token}
Content-Type: application/json

{
  "donor_id": 2,
  "amount": 250.00,
  "date": "2024-11-22",
  "type": "monetary",
  "description": "Patrocinio evento"
}
```

#### Actualizar donación
```http
PUT /index.php?page=api&resource=donations&id=1
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 600.00
}
```

#### Eliminar donación
```http
DELETE /index.php?page=api&resource=donations&id=1
Authorization: Bearer {token}
```

---

### 4. Cuotas (Fees)

#### Listar todas las cuotas
```http
GET /index.php?page=api&resource=fees
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "data": [
    {
      "id": 1,
      "member_id": 1,
      "member_name": "Juan Pérez",
      "fee_id": 1,
      "year": 2024,
      "amount": 50.00,
      "payment_date": "2024-01-10",
      "payment_method": "transfer"
    }
  ],
  "total": 1
}
```

#### Obtener una cuota específica
```http
GET /index.php?page=api&resource=fees&id=1
Authorization: Bearer {token}
```

#### Registrar nuevo pago de cuota
```http
POST /index.php?page=api&resource=fees
Authorization: Bearer {token}
Content-Type: application/json

{
  "member_id": 2,
  "fee_id": 1,
  "amount": 50.00,
  "payment_date": "2024-11-22",
  "payment_method": "cash"
}
```

---

## Códigos de Estado HTTP

| Código | Significado |
|--------|-------------|
| 200 | OK - Petición exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Datos inválidos |
| 401 | Unauthorized - Token inválido o expirado |
| 404 | Not Found - Recurso no encontrado |
| 405 | Method Not Allowed - Método HTTP no permitido |
| 500 | Internal Server Error - Error del servidor |

---

## Ejemplos de Uso

### JavaScript (Fetch API)

```javascript
// 1. Obtener token
const loginResponse = await fetch('http://192.168.1.7/index.php?page=api&resource=auth', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'password123'
  })
});
const { token } = await loginResponse.json();

// 2. Listar socios
const membersResponse = await fetch('http://192.168.1.7/index.php?page=api&resource=members', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
const members = await membersResponse.json();
console.log(members);

// 3. Crear nuevo socio
const createResponse = await fetch('http://192.168.1.7/index.php?page=api&resource=members', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Pedro López',
    dni: '11223344C',
    email: 'pedro@example.com',
    phone: '666777888',
    address: 'Plaza Mayor 10',
    join_date: '2024-11-22',
    category_id: 1
  })
});
const result = await createResponse.json();
console.log(result);
```

### cURL

```bash
# 1. Obtener token
curl -X POST http://192.168.1.7/index.php?page=api&resource=auth \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}'

# 2. Listar eventos (reemplazar TOKEN con el token obtenido)
curl -X GET "http://192.168.1.7/index.php?page=api&resource=events" \
  -H "Authorization: Bearer TOKEN"

# 3. Crear evento
curl -X POST "http://192.168.1.7/index.php?page=api&resource=events" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Excursión Sierra",
    "description": "Excursión a la sierra",
    "date": "2024-12-10",
    "time": "09:00:00",
    "location": "Punto encuentro Plaza",
    "max_attendees": 30,
    "registration_required": 1,
    "price": 15.00
  }'
```

### Python (requests)

```python
import requests

# Base URL
BASE_URL = 'http://192.168.1.7/index.php?page=api'

# 1. Obtener token
auth_response = requests.post(
    f'{BASE_URL}&resource=auth',
    json={'email': 'admin@example.com', 'password': 'password123'}
)
token = auth_response.json()['token']

# Headers con autenticación
headers = {'Authorization': f'Bearer {token}'}

# 2. Listar donaciones
donations = requests.get(f'{BASE_URL}&resource=donations', headers=headers)
print(donations.json())

# 3. Crear donación
new_donation = {
    'donor_id': 1,
    'amount': 300.00,
    'date': '2024-11-22',
    'type': 'monetary',
    'description': 'Donación test'
}
response = requests.post(
    f'{BASE_URL}&resource=donations',
    headers=headers,
    json=new_donation
)
print(response.json())
```

---

## Seguridad

- Todos los endpoints (excepto `/auth`) requieren autenticación
- Los tokens JWT expiran después de 24 horas
- Se recomienda usar HTTPS en producción
- Cambiar `$secretKey` en `ApiController.php` a un valor único y seguro
- Implementar rate limiting para prevenir abuso

---

## Notas

- La API devuelve todas las respuestas en formato JSON
- Las fechas deben estar en formato `YYYY-MM-DD`
- Las horas en formato `HH:MM:SS`
- Los montos deben ser números decimales (ej: 50.00)
