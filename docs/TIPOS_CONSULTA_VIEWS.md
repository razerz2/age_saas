# Views que utilizam "Tipo de Consulta" / "Tipo de Atendimento"

Este documento lista todas as views que fazem referência a tipos de consulta ou tipos de atendimento no sistema.

## 📋 Views de Gerenciamento de Tipos de Consulta

### 1. **resources/views/tenant/appointment-types/index.blade.php**
- **Uso**: Lista todos os tipos de consulta
- **Funcionalidade**: 
  - Exibe tabela com tipos de consulta
  - Mostra médico, nome, duração e status
  - Permite filtrar por médico
  - Links para ver/editar cada tipo

### 2. **resources/views/tenant/appointment-types/create.blade.php**
- **Uso**: Formulário para criar novo tipo de consulta
- **Funcionalidade**:
  - Campo de seleção de médico (obrigatório)
  - Campo de nome do tipo
  - Campo de duração em minutos
  - Campo de status (Ativo/Inativo)

### 3. **resources/views/tenant/appointment-types/edit.blade.php**
- **Uso**: Formulário para editar tipo de consulta existente
- **Funcionalidade**: Mesmas funcionalidades do create, mas pré-preenchido com dados existentes

### 4. **resources/views/tenant/appointment-types/show.blade.php**
- **Uso**: Visualização detalhada de um tipo de consulta
- **Funcionalidade**: Exibe todas as informações do tipo de consulta (médico, nome, duração, status, datas)

---

## 📅 Views de Agendamentos

### 5. **resources/views/tenant/appointments/create.blade.php**
- **Uso**: Criar novo agendamento (painel administrativo)
- **Funcionalidade**:
  - Select dinâmico de tipo de consulta
  - Carrega tipos via AJAX: `/tenant/api/doctors/{doctorId}/appointment-types`
  - Select desabilitado até selecionar médico
  - Usado para calcular duração do agendamento

### 6. **resources/views/tenant/appointments/edit.blade.php**
- **Uso**: Editar agendamento existente
- **Funcionalidade**:
  - Select estático com todos os tipos de consulta
  - Carregado via `$appointmentTypes` do controller
  - Permite alterar o tipo de consulta do agendamento

### 7. **resources/views/tenant/appointments/show.blade.php**
- **Uso**: Visualizar detalhes de um agendamento
- **Funcionalidade**:
  - Exibe o tipo de consulta do agendamento: `{{ $appointment->type->name ?? 'N/A' }}`

### 8. **resources/views/tenant/appointments/index.blade.php**
- **Uso**: Lista de agendamentos
- **Funcionalidade**:
  - Coluna "Tipo" na tabela exibindo: `{{ $appointment->type->name ?? 'N/A' }}`

---

## 🔄 Views de Agendamentos Recorrentes

### 9. **resources/views/tenant/appointments/recurring/create.blade.php**
- **Uso**: Criar agendamento recorrente
- **Funcionalidade**:
  - Select dinâmico de tipo de consulta (`appointment_type_id`)
  - Carrega via AJAX: `/tenant/api/doctors/{doctorId}/appointment-types`
  - Campo obrigatório
  - Select desabilitado até selecionar especialidade/médico
  - Usado para calcular horários disponíveis

### 10. **resources/views/tenant/appointments/recurring/edit.blade.php**
- **Uso**: Editar agendamento recorrente
- **Funcionalidade**:
  - Select estático com todos os tipos de consulta
  - Carregado via `$appointmentTypes` do controller
  - Permite alterar o tipo de consulta

### 11. **resources/views/tenant/appointments/recurring/show.blade.php**
- **Uso**: Visualizar detalhes de agendamento recorrente
- **Funcionalidade**:
  - Exibe o tipo de consulta: `{{ $recurringAppointment->appointmentType->name ?? 'N/A' }}`

### 12. **resources/views/tenant/appointments/recurring/index.blade.php**
- **Uso**: Lista de agendamentos recorrentes
- **Funcionalidade**:
  - Exibe tipo de consulta na tabela: `{{ $recurring->appointmentType->name ?? 'N/A' }}`

---

## 🌐 Views Públicas

### 13. **resources/views/tenant/public/appointment-create.blade.php**
- **Uso**: Formulário público de agendamento (para pacientes)
- **Funcionalidade**:
  - Select dinâmico de tipo de consulta
  - Carrega via AJAX: `/t/{tenant}/agendamento/api/doctors/{doctorId}/appointment-types`
  - Exibe nome e duração: `${type.name} (${type.duration_min} min)`
  - Select desabilitado até selecionar médico
  - Usado para buscar horários disponíveis

---

## 📊 Outras Views

### 14. **resources/views/tenant/dashboard/index.blade.php**
- **Uso**: Dashboard do tenant
- **Funcionalidade**:
  - Exibe badge com tipo de consulta em agendamentos recentes: `{{ $appointment->type->name ?? 'N/A' }}`

### 15. **resources/views/layouts/connect_plus/navigation.blade.php**
- **Uso**: Menu de navegação
- **Funcionalidade**:
  - Menu "Tipos de Atendimento" com subitens:
    - Listar
    - Novo Tipo

---

## 🔌 APIs / Endpoints Utilizados

### 1. **GET /tenant/api/doctors/{doctorId}/appointment-types**
- **Controller**: `AppointmentController::getAppointmentTypesByDoctor()`
- **Uso**: Retorna tipos de consulta de um médico específico
- **Retorno JSON**: `[{id, name, duration_min}]`
- **Usado em**:
  - `appointments/create.blade.php`
  - `appointments/recurring/create.blade.php`

### 2. **GET /t/{tenant}/agendamento/api/doctors/{doctorId}/appointment-types**
- **Uso**: Versão pública do endpoint acima
- **Usado em**: `public/appointment-create.blade.php`

---

## ⚠️ Observações Importantes

### Views que precisam de atenção após a mudança para `doctor_id` obrigatório:

1. ✅ **appointments/create.blade.php** - Já usa endpoint correto que filtra por médico
2. ✅ **appointments/recurring/create.blade.php** - Já usa endpoint correto que filtra por médico  
3. ✅ **public/appointment-create.blade.php** - Já usa endpoint correto que filtra por médico
4. ⚠️ **appointments/edit.blade.php** - **PRECISA AJUSTE**
   - **Controller**: `AppointmentController::edit()` linha 109
   - **Problema**: Carrega TODOS os tipos: `AppointmentType::orderBy('name')->get()`
   - **Solução**: Filtrar pelo médico do calendário do agendamento: `AppointmentType::where('doctor_id', $appointment->calendar->doctor_id)->orderBy('name')->get()`
5. ⚠️ **appointments/recurring/edit.blade.php** - **PRECISA AJUSTE**
   - **Controller**: `RecurringAppointmentController::edit()` linha 174
   - **Problema**: Carrega TODOS os tipos: `AppointmentType::where('is_active', true)->orderBy('name')->get()`
   - **Solução**: Filtrar pelo médico do agendamento recorrente: `AppointmentType::where('doctor_id', $recurringAppointment->doctor_id)->where('is_active', true)->orderBy('name')->get()`
6. ⚠️ **appointments/recurring/create.blade.php** - **PRECISA AJUSTE (controller)**
   - **Controller**: `RecurringAppointmentController::create()` linha 42
   - **Problema**: Carrega TODOS os tipos: `AppointmentType::where('is_active', true)->orderBy('name')->get()`
   - **Nota**: A view já usa AJAX, mas o controller carrega tipos desnecessários
   - **Solução**: Remover do controller (a view já carrega via AJAX)

### Views que apenas EXIBEM (não precisam ajuste):
- ✅ Todas as views `show.blade.php`
- ✅ Todas as views `index.blade.php`
- ✅ `dashboard/index.blade.php`

---

## 📝 Recomendações de Ajustes

### 1. **AppointmentController::edit()** (linha 103-120)
```php
public function edit($id)
{
    $appointment = Appointment::findOrFail($id);
    $appointment->load(['calendar', 'patient', 'specialty', 'type']);
    
    // Filtrar tipos de consulta pelo médico do calendário do agendamento
    $doctorId = $appointment->calendar->doctor_id ?? null;
    $appointmentTypes = $doctorId 
        ? AppointmentType::where('doctor_id', $doctorId)->orderBy('name')->get()
        : collect(); // Se não tiver calendário, retornar vazio
    
    // ... resto do código
}
```

### 2. **RecurringAppointmentController::edit()** (linha 162-182)
```php
public function edit($id)
{
    $recurringAppointment = RecurringAppointment::with('rules')->findOrFail($id);
    
    // Filtrar tipos de consulta pelo médico do agendamento recorrente
    $appointmentTypes = AppointmentType::where('doctor_id', $recurringAppointment->doctor_id)
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
    
    // ... resto do código
}
```

### 3. **RecurringAppointmentController::create()** (linha 32-48)
```php
public function create()
{
    // Remover a linha que carrega todos os tipos, pois a view usa AJAX
    // $appointmentTypes = AppointmentType::where('is_active', true)->orderBy('name')->get();
    
    return view('tenant.appointments.recurring.create', compact(
        'doctors',
        'patients'
        // Remover 'appointmentTypes' daqui
    ));
}
```

### 4. Todos os endpoints já estão funcionando corretamente com a nova estrutura ✅

