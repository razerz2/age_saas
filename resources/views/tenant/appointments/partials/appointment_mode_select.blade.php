<div class="col-md-6">
    <div class="form-group">
        <label class="fw-semibold">
            <i class="mdi mdi-video-account me-1"></i>
            Modo de Consulta <span class="text-danger">*</span>
        </label>
        <select name="appointment_mode" class="form-control @error('appointment_mode') is-invalid @enderror" required>
            <option value="presencial" {{ old('appointment_mode', $appointment->appointment_mode ?? 'presencial') == 'presencial' ? 'selected' : '' }}>Presencial</option>
            <option value="online" {{ old('appointment_mode', $appointment->appointment_mode ?? 'presencial') == 'online' ? 'selected' : '' }}>Online</option>
        </select>
        @error('appointment_mode')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

