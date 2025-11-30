<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    <i class="mdi mdi-filter"></i> Filtros
                </h4>
                <form id="filter-form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Período Rápido</label>
                                <select name="period" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="today">Hoje</option>
                                    <option value="week">Esta Semana</option>
                                    <option value="month">Este Mês</option>
                                    <option value="last_30_days">Últimos 30 dias</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Data Inicial</label>
                                <input type="date" name="date_from" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Data Final</label>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Médico</label>
                                <select name="doctor_id" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Especialidade</label>
                                <select name="specialty_id" class="form-control">
                                    <option value="">Todas</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de Consulta</label>
                                <select name="appointment_type" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($appointmentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Modo</label>
                                <select name="appointment_mode" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="online">Online</option>
                                    <option value="presencial">Presencial</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="scheduled">Agendado</option>
                                    <option value="confirmed">Confirmado</option>
                                    <option value="attended">Atendido</option>
                                    <option value="canceled">Cancelado</option>
                                    <option value="no_show">Não Compareceu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" onclick="loadData()">
                                <i class="mdi mdi-filter"></i> Aplicar Filtros
                            </button>
                            <button type="reset" class="btn btn-secondary" onclick="$('#filter-form')[0].reset(); loadData();">
                                <i class="mdi mdi-refresh"></i> Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

