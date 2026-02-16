<div class="w-full">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i class="mdi mdi-video-account mr-1"></i>
            Modo de Consulta <span class="text-red-500">*</span>
        </label>
        <select name="appointment_mode" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('appointment_mode') border-red-500 @enderror" required>
            <option value="presencial" {{ old('appointment_mode', $appointment->appointment_mode ?? 'presencial') == 'presencial' ? 'selected' : '' }}>Presencial</option>
            <option value="online" {{ old('appointment_mode', $appointment->appointment_mode ?? 'presencial') == 'online' ? 'selected' : '' }}>Online</option>
        </select>
        @error('appointment_mode')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

