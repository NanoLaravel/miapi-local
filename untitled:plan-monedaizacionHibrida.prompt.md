# Plan ejecutable para monetización híbrida y leads

## 1. Objetivo general

Implementar un modelo de negocio escalable sobre la API actual para cubrir dos escenarios distintos:

1. Lugares / hospedaje / cabañas
   - flujo híbrido con intervención del dueño
   - monetización por leads, reservas y visibilidad

2. Productos locales
   - flujo de leads simples
   - monetización por suscripción y venta de leads
   - plan gratuito con restricciones claras

La app consumidora manejará el tratamiento especial para cabañas, por lo que este backend no necesita definir lógica adicional específica para ese caso más allá de exponer los datos y permitir el flujo de negocio.

---

## 2. Reglas de negocio por tipo de entidad

### 2.1 Lugares y hospedaje

Reglas base:

- el usuario puede ver el detalle del lugar
- el usuario puede contactar o solicitar disponibilidad
- el owner del lugar puede recibir, aceptar o rechazar la solicitud
- si la solicitud es aceptada, se puede crear una reserva
- la reserva puede pasar por estados de pago externo y confirmación final

Restricciones del plan gratuito:

- publicación básica
- hasta 5 fotos visibles
- sin contacto comercial avanzado
- sin leads ni reservas
- sin prioridad de visibilidad

### 2.2 Cabañas

Reglas base:

- el usuario puede ver el detalle de la cabaña
- el flujo de negocio es híbrido, con intervención del dueño
- el owner puede aceptar o rechazar la solicitud de disponibilidad o reserva
- se debe permitir la generación de una reserva pendiente

Restricciones del plan gratuito:

- sin botón de contacto
- sin leads
- sin reservas
- visibilidad limitada

### 2.3 Productos locales

Reglas base:

- el usuario puede ver el detalle del producto
- el usuario puede enviar un interés a través de contacto directo
- el lead queda registrado para el dueño del producto
- el owner solo puede gestionar leads si tiene una suscripción activa

Restricciones del plan gratuito:

- solo se permiten 2 fotos del producto
- solo se muestra un botón de WhatsApp
- no se habilitan leads ni gestión de solicitudes
- no se habilitan promociones ni métricas

---

## 3. Estructura de migraciones propuesta

### 3.1 Migraciones nuevas

1. create_leads_table
2. create_reservations_table
3. create_subscription_plans_table
4. create_owner_subscriptions_table
5. create_promotions_table
6. create_analytics_events_table
7. create_owner_interactions_table
8. alter_advertisements_table_for_business_features

### 3.2 Estructura recomendada por tabla

#### leads

```php
Schema::create('leads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
    $table->string('leadable_type');
    $table->unsignedBigInteger('leadable_id');
    $table->string('contact_type')->default('whatsapp');
    $table->text('message')->nullable();
    $table->string('status')->default('pending');
    $table->string('source')->default('app');
    $table->string('priority')->default('medium');
    $table->timestamps();
});
```

#### reservations

```php
Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
    $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
    $table->string('reservation_code')->unique();
    $table->date('check_in_date')->nullable();
    $table->date('check_out_date')->nullable();
    $table->integer('nights')->default(1);
    $table->decimal('total_amount', 10, 2)->default(0);
    $table->string('currency', 3)->default('COP');
    $table->string('status')->default('pending');
    $table->string('payment_method')->nullable();
    $table->string('payment_proof_path')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

#### subscription_plans

```php
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('target_type');
    $table->decimal('price_monthly', 10, 2)->default(0);
    $table->decimal('price_yearly', 10, 2)->default(0);
    $table->integer('lead_limit')->default(0);
    $table->integer('featured_limit')->default(0);
    $table->boolean('analytics_enabled')->default(false);
    $table->boolean('promotions_enabled')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### owner_subscriptions

```php
Schema::create('owner_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
    $table->string('status')->default('active');
    $table->dateTime('started_at')->nullable();
    $table->dateTime('ends_at')->nullable();
    $table->boolean('auto_renew')->default(false);
    $table->string('payment_status')->default('pending');
    $table->timestamps();
});
```

#### promotions

```php
Schema::create('promotions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
    $table->string('target_type');
    $table->unsignedBigInteger('target_id');
    $table->string('title');
    $table->text('description')->nullable();
    $table->decimal('discount_percent', 5, 2)->nullable();
    $table->dateTime('starts_at');
    $table->dateTime('ends_at');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### analytics_events

```php
Schema::create('analytics_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('event_type');
    $table->string('entity_type')->nullable();
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

#### owner_interactions

```php
Schema::create('owner_interactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
    $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
    $table->string('action');
    $table->text('note')->nullable();
    $table->timestamps();
});
```

---

## 4. Reglas por plan

### 4.1 Plan gratuito para productos locales

Permisos:

- mostrar hasta 2 fotos
- mostrar un botón de WhatsApp
- no generar leads
- no recibir solicitudes de contacto
- no usar promociones
- no ver métricas

### 4.2 Plan gratuito para lugares

Permisos:

- mostrar hasta 5 fotos
- sin contacto comercial avanzado
- sin leads
- sin reservas
- sin prioridad de visibilidad

### 4.3 Plan gratuito para cabañas

Permisos:

- sin botón de contacto
- sin leads
- sin reservas
- visibilidad limitada

### 4.4 Planes de pago

- Basic
  - leads básicos
  - contacto habilitado
  - dashboard simple

- Pro
  - leads prioritarios
  - reservas habilitadas
  - métricas básicas

- Premium
  - mejor visibilidad
  - promociones activas
  - analítica avanzada

---

## 5. Endpoints concretos recomendados

### 5.1 Leads

- POST /api/leads
  - crea un lead desde la app
  - valida si el owner tiene permisos para recibir leads

- GET /api/leads
  - lista los leads del owner autenticado

- GET /api/leads/{id}
  - obtiene el detalle del lead

- PATCH /api/leads/{id}/status
  - cambia estado: pending, contacted, interested, rejected, converted

### 5.2 Reservas

- POST /api/reservations
  - crea una reserva desde un lead aceptado

- GET /api/reservations
  - lista reservas del owner o del usuario

- GET /api/reservations/{id}
  - detalle completo de la reserva

- PATCH /api/reservations/{id}/confirm
  - confirma la reserva

- PATCH /api/reservations/{id}/reject
  - rechaza la reserva

- POST /api/reservations/{id}/payment-proof
  - sube comprobante de pago

### 5.3 Suscripciones

- GET /api/subscription-plans
- POST /api/owner-subscriptions
- PATCH /api/owner-subscriptions/{id}/cancel

### 5.4 Promociones y visibilidad

- GET /api/promotions/active
- POST /api/promotions
- GET /api/advertisements/featured

### 5.5 Analítica

- POST /api/analytics/events
- GET /api/analytics/summary

---

## 6. Lógica de implementación recomendada

### 6.1 Flujo para productos locales

1. El usuario envía interés desde la app.
2. El backend valida si el owner tiene un plan que permita leads.
3. Si no, devuelve una limitación de negocio.
4. Si sí, crea un lead con estado pending.
5. El owner puede ver y gestionar el lead.

### 6.2 Flujo para lugares y cabañas

1. El usuario solicita contacto o disponibilidad.
2. El backend crea un lead asociado al lugar o cabaña.
3. El owner puede aceptar o rechazar.
4. Si acepta, se crea una reservation con código único.
5. El usuario puede adjuntar comprobante y el owner confirma o rechaza.

### 6.3 Flujo para anuncios y visibilidad

1. Un owner activa un plan con promoción habilitada.
2. Se crea un registro en promotions o se usa advertisements como recurso promocional.
3. El anuncio entra en la lógica de ranking y visibilidad de la app.

---

## 7. Propuesta de orden de implementación por sprints

### Sprint 1: base de leads y permisos

Objetivo:

- implementar la lógica base para leads
- habilitar restricciones por plan gratuito

Tareas:

- crear migraciones de leads y subscription_plans
- crear modelos Lead, SubscriptionPlan y OwnerSubscription
- crear controladores y endpoints básicos de leads
- aplicar reglas por tipo de entidad y plan gratuito

### Sprint 2: reservas y flujo híbrido

Objetivo:

- habilitar reservas para lugares y cabañas

Tareas:

- crear migración de reservations
- crear modelo Reservation y flujo de aceptación/rechazo
- generar código de reserva
- permitir subir comprobante de pago

### Sprint 3: suscripciones y planes

Objetivo:

- monetizar mediante suscripciones

Tareas:

- crear endpoints de planes y activación de suscripciones
- aplicar límites por plan
- exponer información del estado del plan al owner

### Sprint 4: promociones y visibilidad

Objetivo:

- integrar advertisements como mecanismo de visibilidad pagada

Tareas:

- extender advertisements con campos de negocio
- crear promociones y asociación con owners o recursos
- integrar ranking y prioridad de visualización

### Sprint 5: analítica y dashboards

Objetivo:

- medir resultados de negocio

Tareas:

- crear analytics_events
- registrar vistas, clics, leads y reservas
- exponer resumen básico para owner y admin

---

## 8. Recomendación de implementación inicial

Para no sobrecargar el proyecto, se recomienda empezar por este orden:

1. Leads para productos locales
2. Leads y reservas para lugares y cabañas
3. Suscripciones y límites por plan
4. Promociones y visibilidad
5. Analítica

Ese orden permite lanzar algo útil rápido, validar el negocio y luego escalar con mejores métricas y capacidades comerciales.
