export function init() {
	const tenantSlug = window.tenantSlug || (window.tenant && window.tenant.slug) || null;
	if (!tenantSlug) {
		return;
	}

	const doctorSelect = document.getElementById('doctor_id');
	const appointmentTypeSelect = document.getElementById('appointment_type');
	const specialtySelect = document.getElementById('specialty_id');
	const dateInput = document.getElementById('appointment_date');
	const timeSelect = document.getElementById('appointment_time');
	const startsAtInput = document.getElementById('starts_at');
	const endsAtInput = document.getElementById('ends_at');
	const calendarIdInput = document.getElementById('calendar_id');
	const businessHoursModal = document.getElementById('businessHoursModal');
	const btnShowBusinessHours = document.getElementById('btn-show-business-hours');

	if (!doctorSelect || !dateInput || !timeSelect || !startsAtInput || !endsAtInput) {
		return;
	}

	// Helpers compartilhados
	function resetDependentFields() {
		if (appointmentTypeSelect) {
			appointmentTypeSelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
			appointmentTypeSelect.disabled = true;
		}

		if (specialtySelect) {
			specialtySelect.innerHTML = '<option value="">Primeiro selecione um médico</option>';
			specialtySelect.disabled = true;
		}

		timeSelect.innerHTML = '<option value="">Primeiro selecione a data</option>';
		timeSelect.disabled = true;
	}

	function loadAppointmentTypes(doctorId, currentTypeId = null) {
		if (!appointmentTypeSelect) return;

		appointmentTypeSelect.disabled = true;
		appointmentTypeSelect.innerHTML = '<option value="">Carregando tipos...</option>';

		fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/appointment-types`)
			.then((response) => response.json())
			.then((data) => {
				appointmentTypeSelect.innerHTML = '<option value="">Selecione um tipo</option>';

				let currentTypeFound = false;
				(data || []).forEach((type) => {
					const option = document.createElement('option');
					option.value = type.id;
					option.textContent = `${type.name} (${type.duration_min} min)`;
					option.dataset.duration = type.duration_min;
					if (currentTypeId && String(currentTypeId) === String(type.id)) {
						option.selected = true;
						currentTypeFound = true;
					}
					appointmentTypeSelect.appendChild(option);
				});

				if (currentTypeId && !currentTypeFound) {
					const opt = document.createElement('option');
					opt.value = currentTypeId;
					opt.textContent = 'Tipo atual (não encontrado na lista)';
					opt.selected = true;
					appointmentTypeSelect.appendChild(opt);
				}

				appointmentTypeSelect.disabled = false;
			})
			.catch(() => {
				appointmentTypeSelect.innerHTML = '<option value="">Erro ao carregar tipos</option>';
			});
	}

	function loadSpecialties(doctorId, currentSpecialtyId = null) {
		if (!specialtySelect) return;

		fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/specialties`)
			.then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then((data) => {
				specialtySelect.innerHTML = '<option value="">Selecione uma especialidade</option>';

				if (data && data.length > 0) {
					data.forEach((specialty) => {
						const option = document.createElement('option');
						option.value = specialty.id;
						option.textContent = specialty.name;
						if (currentSpecialtyId && String(currentSpecialtyId) === String(specialty.id)) {
							option.selected = true;
						}
						specialtySelect.appendChild(option);
					});
				} else {
					specialtySelect.innerHTML = '<option value="">Nenhuma especialidade cadastrada para este médico</option>';
				}

				specialtySelect.disabled = false;
			})
			.catch(() => {
				specialtySelect.innerHTML = '<option value="">Erro ao carregar especialidades</option>';
				specialtySelect.disabled = false;
			});
	}

	function loadAvailableSlots(doctorId, appointmentTypeId, initial = false, initialTimeStart = null, initialTimeEnd = null) {
		const date = dateInput.value;
		if (!doctorId || !date) {
			timeSelect.innerHTML = '<option value="">Primeiro selecione médico e data</option>';
			timeSelect.disabled = true;
			return;
		}

		timeSelect.disabled = true;
		timeSelect.innerHTML = '<option value="">Carregando horários...</option>';

		const baseUrl = `/workspace/${tenantSlug}/api/doctors/${doctorId}/available-slots?date=${date}`;
		const finalUrl = appointmentTypeId ? `${baseUrl}&appointment_type_id=${appointmentTypeId}` : baseUrl;

		fetch(finalUrl)
			.then((response) => response.json())
			.then((data) => {
				timeSelect.innerHTML = '<option value="">Selecione um horário</option>';

				if (!data || data.length === 0) {
					timeSelect.innerHTML = '<option value="">Nenhum horário disponível para esta data</option>';
					timeSelect.disabled = true;
					return;
				}

				let selectedFound = false;
				data.forEach((slot) => {
					const option = document.createElement('option');
					option.value = slot.label || slot.start;
					option.textContent = slot.label || `${slot.start_time} - ${slot.end_time}`;
					option.dataset.start = slot.start;
					option.dataset.end = slot.end;

					if (
						initial &&
						initialTimeStart &&
						initialTimeEnd &&
						((slot.start_time && slot.end_time && slot.start_time === initialTimeStart && slot.end_time === initialTimeEnd) ||
							(slot.start && slot.end && slot.start.includes(initialTimeStart)))
					) {
						option.selected = true;
						startsAtInput.value = slot.start;
						endsAtInput.value = slot.end;
						selectedFound = true;
					}

					timeSelect.appendChild(option);
				});

				timeSelect.disabled = false;

				if (!selectedFound && !initial) {
					startsAtInput.value = '';
					endsAtInput.value = '';
				}
			})
			.catch(() => {
				timeSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
				timeSelect.disabled = true;
			});
	}

	// Eventos comuns (create/edit)
	doctorSelect.addEventListener('change', () => {
		const doctorId = doctorSelect.value;
		if (!doctorId) {
			resetDependentFields();
			if (btnShowBusinessHours) btnShowBusinessHours.disabled = true;
			if (calendarIdInput) calendarIdInput.value = '';
			return;
		}

		if (btnShowBusinessHours) btnShowBusinessHours.disabled = false;

		if (calendarIdInput) {
			fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/calendars`)
				.then((response) => response.json())
				.then((data) => {
					calendarIdInput.value = data && data.length > 0 ? data[0].id : '';
				})
				.catch(() => {
					calendarIdInput.value = '';
				});
		}

		loadAppointmentTypes(doctorId);
		loadSpecialties(doctorId);

		if (dateInput.value) {
			const typeId = appointmentTypeSelect ? appointmentTypeSelect.value : null;
			loadAvailableSlots(doctorId, typeId);
		}
	});

	dateInput.addEventListener('change', () => {
		const doctorId = doctorSelect.value;
		const typeId = appointmentTypeSelect ? appointmentTypeSelect.value : null;
		loadAvailableSlots(doctorId, typeId);
	});

	if (appointmentTypeSelect) {
		appointmentTypeSelect.addEventListener('change', () => {
			const doctorId = doctorSelect.value;
			const typeId = appointmentTypeSelect.value;
			if (doctorId && dateInput.value) {
				loadAvailableSlots(doctorId, typeId);
			}
		});
	}

	timeSelect.addEventListener('change', () => {
		const selectedOption = timeSelect.options[timeSelect.selectedIndex];
		if (selectedOption && selectedOption.value) {
			startsAtInput.value = selectedOption.dataset.start || '';
			endsAtInput.value = selectedOption.dataset.end || '';
		} else {
			startsAtInput.value = '';
			endsAtInput.value = '';
		}
	});

	const form = document.querySelector('form');
	if (form) {
		form.addEventListener('submit', (e) => {
			if (!startsAtInput.value || !endsAtInput.value) {
				e.preventDefault();
				if (typeof window.showAlert === 'function') {
					window.showAlert({
						type: 'warning',
						title: 'Atenção',
						message: 'Por favor, selecione um horário disponível.',
					});
				}
			}
		});
	}

	// Fechar modal de dias trabalhados (create: Tailwind overlay, edit: Bootstrap modal)
	document.querySelectorAll('.js-close-business-hours-modal').forEach((btn) => {
		btn.addEventListener('click', () => {
			const modal = document.getElementById('businessHoursModal');
			if (!modal) return;

			// Tenta via Bootstrap (edit) se disponível
			if (window.bootstrap && typeof window.bootstrap.Modal !== 'undefined') {
				const bsModal = window.bootstrap.Modal.getInstance(modal) || new window.bootstrap.Modal(modal);
				bsModal.hide();
			} else {
				// Fallback Tailwind (create)
				modal.classList.add('hidden');
			}
		});
	});

	// Se houver valor inicial de médico na create, dispara change para carregar dependentes
	if (doctorSelect && doctorSelect.dataset.initialValue) {
		if (!doctorSelect.value) {
			doctorSelect.value = doctorSelect.dataset.initialValue;
		}
		doctorSelect.dispatchEvent(new Event('change'));
	}

	// Modal de dias trabalhados (Tailwind ou Bootstrap, dependendo da view)
	if (businessHoursModal) {
		window.loadBusinessHours = function loadBusinessHours(doctorId) {
			const loadingEl = document.getElementById('business-hours-loading');
			const contentEl = document.getElementById('business-hours-content');
			const errorEl = document.getElementById('business-hours-error');
			const emptyEl = document.getElementById('business-hours-empty');
			const listEl = document.getElementById('business-hours-list');
			const doctorNameEl = document.getElementById('business-hours-doctor-name');

			if (loadingEl) {
				loadingEl.classList.remove('d-none');
				loadingEl.style.display = 'block';
			}
			if (contentEl) {
				contentEl.classList.add('d-none');
				contentEl.style.display = 'none';
			}
			if (errorEl) {
				errorEl.classList.add('d-none');
				errorEl.style.display = 'none';
			}
			if (emptyEl) {
				emptyEl.classList.add('d-none');
				emptyEl.style.display = 'none';
			}

			fetch(`/workspace/${tenantSlug}/api/doctors/${doctorId}/business-hours`)
				.then((response) => {
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					return response.json();
				})
				.then((data) => {
					if (loadingEl) loadingEl.style.display = 'none';

					let businessHoursArray = null;
					let doctorInfo = null;

					if (Array.isArray(data)) {
						businessHoursArray = data;
						const selectedOption = doctorSelect.options[doctorSelect.selectedIndex];
						const doctorName = selectedOption ? selectedOption.textContent.trim() : 'N/A';
						doctorInfo = { name: doctorName };
					} else if (data && typeof data === 'object') {
						if (data.error) {
							if (errorEl) {
								errorEl.classList.remove('d-none');
								errorEl.style.display = 'block';
								const msgEl = document.getElementById('business-hours-error-message');
								if (msgEl) msgEl.textContent = data.error;
							}
							return;
						}
						businessHoursArray = data.business_hours;
						doctorInfo = data.doctor;
					} else {
						if (errorEl) {
							errorEl.classList.remove('d-none');
							errorEl.style.display = 'block';
							const msgEl = document.getElementById('business-hours-error-message');
							if (msgEl) msgEl.textContent = 'Formato de dados inválido recebido da API.';
						}
						return;
					}

					if (!businessHoursArray || businessHoursArray.length === 0) {
						if (emptyEl) {
							emptyEl.classList.remove('d-none');
							emptyEl.style.display = 'block';
						}
						return;
					}

					if (doctorNameEl && doctorInfo) {
						doctorNameEl.textContent = doctorInfo.name || 'N/A';
					}

					let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
					html += '<thead class="table-light"><tr><th>Dia da Semana</th><th>Horários</th></tr></thead>';
					html += '<tbody>';

					businessHoursArray.forEach((day) => {
						html += '<tr>';
						html += `<td><strong>${day.weekday_name || 'N/A'}</strong></td>`;
						html += '<td>';

						if (day.hours && Array.isArray(day.hours) && day.hours.length > 0) {
							day.hours.forEach((hour, index) => {
								if (index > 0) html += '<br>';
								html += `<span class="badge bg-primary me-1">${hour.start_time || 'N/A'}</span> até <span class="badge bg-primary">${hour.end_time || 'N/A'}</span>`;
								if (hour.break_start_time && hour.break_end_time) {
									html += ` <small class="text-muted">(Intervalo: ${hour.break_start_time} - ${hour.break_end_time})</small>`;
								}
							});
						} else {
							html += '<span class="text-muted">Não trabalha neste dia</span>';
						}

						html += '</td>';
						html += '</tr>';
					});

					html += '</tbody></table></div>';

					if (listEl) {
						listEl.innerHTML = html;
					}

					if (contentEl) {
						contentEl.classList.remove('d-none');
						contentEl.style.display = 'block';
					}
				})
				.catch((error) => {
					if (errorEl) {
						errorEl.classList.remove('d-none');
						errorEl.style.display = 'block';
						const msgEl = document.getElementById('business-hours-error-message');
						if (msgEl) msgEl.textContent = `Erro ao carregar informações: ${error.message}`;
					}
				});
		};
	}
}

