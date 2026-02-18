export function init() {
    bindAppointmentCreate();
    bindFormResponseReadonly();
    bindMasks();
}

function bindAppointmentCreate() {
    const config = document.getElementById('public-appointment-create-config');
    if (!config) {
        return;
    }

    const doctorSelect = document.getElementById('doctor_id');
    const calendarIdInput = document.getElementById('calendar_id');
    const appointmentTypeSelect = document.getElementById('appointment_type');
    const specialtySelect = document.getElementById('specialty_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const startsAtInput = document.getElementById('starts_at');
    const endsAtInput = document.getElementById('ends_at');
    const businessHoursBtn = document.getElementById('btn-show-business-hours');
    const businessHoursModal = document.getElementById('businessHoursModal');
    const appointmentTypeWrapper = appointmentTypeSelect?.closest('[data-appointment-type-wrapper]') || null;

    if (!doctorSelect || !calendarIdInput || !appointmentTypeSelect || !specialtySelect || !dateInput || !timeSelect) {
        return;
    }

    const oldDoctorId = config.dataset.oldDoctorId || '';
    const oldDate = config.dataset.oldDate || '';
    const oldAppointmentType = config.dataset.oldAppointmentType || '';
    const oldSpecialty = config.dataset.oldSpecialty || '';

    const urlTemplate = {
        calendars: config.dataset.calendarsUrlTemplate || '',
        appointmentTypes: config.dataset.appointmentTypesUrlTemplate || '',
        specialties: config.dataset.specialtiesUrlTemplate || '',
        availableSlots: config.dataset.availableSlotsUrlTemplate || '',
        businessHours: config.dataset.businessHoursUrlTemplate || '',
    };

    function formatLocalISODate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    const today = formatLocalISODate(new Date());
    dateInput.setAttribute('min', today);
    if (!dateInput.value) {
        dateInput.value = today;
    }

    const resetDependentFields = () => {
        calendarIdInput.value = '';
        appointmentTypeSelect.innerHTML = '<option value=\"\">Primeiro selecione um médico</option>';
        appointmentTypeSelect.disabled = true;
        specialtySelect.innerHTML = '<option value=\"\">Primeiro selecione um médico</option>';
        specialtySelect.disabled = true;
        timeSelect.innerHTML = '<option value=\"\">Primeiro selecione a data</option>';
        timeSelect.disabled = true;
    };

    const toggleBusinessHoursButton = () => {
        if (!businessHoursBtn) return;
        businessHoursBtn.disabled = !doctorSelect.value;
    };

    // Tipo de consulta nunca deve aparecer na UI (mantém no DOM para contrato com JS/back-end).
    if (appointmentTypeWrapper) {
        appointmentTypeWrapper.classList.add('hidden');
    }

    toggleBusinessHoursButton();

    const loadCalendarAuto = async (doctorId) => {
        if (!urlTemplate.calendars) return;
        try {
            const response = await fetch(urlTemplate.calendars.replace('__ID__', doctorId));
            const data = await response.json();
            if (data && data.length > 0) {
                calendarIdInput.value = data[0].id;
            } else {
                calendarIdInput.value = '';
            }
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar calendário:', error);
            calendarIdInput.value = '';
        }
    };

    const loadAppointmentTypes = async (doctorId) => {
        if (!urlTemplate.appointmentTypes) return;
        try {
            const response = await fetch(urlTemplate.appointmentTypes.replace('__ID__', doctorId));
            const data = await response.json();
            const types = Array.isArray(data) ? data : [];

            if (types.length === 0) {
                appointmentTypeSelect.innerHTML = '<option value=\"\">Nenhum tipo de consulta disponível</option>';
                appointmentTypeSelect.disabled = true;
                return;
            }

            appointmentTypeSelect.innerHTML = '<option value=\"\">Selecione um tipo</option>';
            types.forEach((type) => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = `${type.name} (${type.duration_min} min)`;
                option.dataset.duration = type.duration_min;
                appointmentTypeSelect.appendChild(option);
            });
            appointmentTypeSelect.value = String(types[0].id);
            appointmentTypeSelect.disabled = false;

            if (dateInput.value) {
                await loadAvailableSlots(doctorId, appointmentTypeSelect.value);
            }
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar tipos de consulta:', error);
            appointmentTypeSelect.innerHTML = '<option value=\"\">Erro ao carregar tipos</option>';
        }
    };

    const loadSpecialties = async (doctorId) => {
        if (!urlTemplate.specialties) return;
        try {
            const response = await fetch(urlTemplate.specialties.replace('__ID__', doctorId));
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            specialtySelect.innerHTML = '<option value=\"\">Selecione uma especialidade</option>';
            if (data && data.length > 0) {
                data.forEach((specialty) => {
                    const option = document.createElement('option');
                    option.value = specialty.id;
                    option.textContent = specialty.name;
                    if (oldSpecialty && String(oldSpecialty) === String(specialty.id)) {
                        option.selected = true;
                    }
                    specialtySelect.appendChild(option);
                });
            } else {
                specialtySelect.innerHTML =
                    '<option value=\"\">Nenhuma especialidade cadastrada para este médico</option>';
            }
            specialtySelect.disabled = false;
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar especialidades:', error);
            specialtySelect.innerHTML = '<option value=\"\">Erro ao carregar especialidades</option>';
            specialtySelect.disabled = false;
        }
    };

    const loadAvailableSlots = async () => {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        const appointmentTypeId = appointmentTypeSelect.value;

        if (!doctorId || !date) {
            timeSelect.innerHTML = '<option value=\"\">Primeiro selecione médico e data</option>';
            timeSelect.disabled = true;
            return;
        }

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value=\"\">Carregando horários...</option>';

        const baseUrl = urlTemplate.availableSlots
            .replace('__ID__', doctorId)
            .replace('__DATE__', date);
        const finalUrl = appointmentTypeId ? `${baseUrl}&appointment_type_id=${appointmentTypeId}` : baseUrl;

        try {
            const response = await fetch(finalUrl);
            const data = await response.json();
            timeSelect.innerHTML = '<option value=\"\">Selecione um horário</option>';
            if (!data || data.length === 0) {
                timeSelect.innerHTML = '<option value=\"\">Nenhum horário disponível para esta data</option>';
            } else {
                data.forEach((slot) => {
                    const option = document.createElement('option');
                    option.value = `${slot.start}-${slot.end}`;
                    option.textContent = `${slot.start} - ${slot.end}`;
                    option.dataset.start = slot.datetime_start;
                    option.dataset.end = slot.datetime_end;
                    timeSelect.appendChild(option);
                });
            }
            timeSelect.disabled = false;
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Erro ao carregar horários disponíveis:', error);
            timeSelect.innerHTML = '<option value=\"\">Erro ao carregar horários</option>';
        }
    };

    const loadBusinessHours = async (doctorId) => {
        const loadingEl = document.getElementById('business-hours-loading');
        const contentEl = document.getElementById('business-hours-content');
        const errorEl = document.getElementById('business-hours-error');
        const emptyEl = document.getElementById('business-hours-empty');
        const listEl = document.getElementById('business-hours-list');
        const doctorNameEl = document.getElementById('business-hours-doctor-name');

        const setHidden = (el, hidden) => {
            if (!el) return;
            el.classList.toggle('hidden', hidden);
        };

        setHidden(loadingEl, false);
        setHidden(contentEl, true);
        setHidden(errorEl, true);
        setHidden(emptyEl, true);

        try {
            const response = await fetch(urlTemplate.businessHours.replace('__ID__', doctorId));
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            setHidden(loadingEl, true);

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
                        setHidden(errorEl, false);
                        const msg = document.getElementById('business-hours-error-message');
                        if (msg) msg.textContent = data.error;
                    }
                    return;
                }
                businessHoursArray = data.business_hours;
                doctorInfo = data.doctor;
            } else {
                if (errorEl) {
                    setHidden(errorEl, false);
                    const msg = document.getElementById('business-hours-error-message');
                    if (msg) msg.textContent = 'Formato de dados inválido recebido da API.';
                }
                return;
            }

            if (!businessHoursArray || businessHoursArray.length === 0) {
                if (emptyEl) {
                    setHidden(emptyEl, false);
                }
                return;
            }

            if (doctorNameEl && doctorInfo) {
                doctorNameEl.textContent = doctorInfo.name || 'N/A';
            }

            let html =
                '<div class=\"mx-auto w-full max-w-[720px]\">' +
                '<table class=\"w-full table-fixed border-collapse overflow-hidden rounded-xl border border-slate-200\">' +
                '<thead class=\"bg-slate-50\">' +
                '<tr>' +
                '<th class=\"px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600\">Dia da Semana</th>' +
                '<th class=\"px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600\">Horários</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';

            businessHoursArray.forEach((day) => {
                html += '<tr class=\"border-t border-slate-200\">';
                html += `<td class=\"px-4 py-3 text-center text-sm font-normal text-slate-900\">${day.weekday_name || 'N/A'}</td>`;
                html += '<td class=\"px-4 py-3 text-center text-sm text-slate-900\">';

                if (day.hours && Array.isArray(day.hours) && day.hours.length > 0) {
                    day.hours.forEach((hour, index) => {
                        if (index > 0) html += '<br>';
                        html += `<span class=\"inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700\">${hour.start_time || 'N/A'}</span> ` +
                            `<span class=\"text-slate-500\">até</span> ` +
                            `<span class=\"inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700\">${hour.end_time || 'N/A'}</span>`;
                        if (hour.break_start_time && hour.break_end_time) {
                            html += ` <span class=\"ml-2 text-xs text-slate-500\">(Intervalo: ${hour.break_start_time} - ${hour.break_end_time})</span>`;
                        }
                    });
                } else {
                    html += '<span class=\"text-sm text-slate-500\">Não trabalha neste dia</span>';
                }
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';

            if (listEl) {
                listEl.innerHTML = html;
            }
            if (contentEl) {
                setHidden(contentEl, false);
            }
        } catch (error) {
            setHidden(loadingEl, true);
            if (errorEl) {
                setHidden(errorEl, false);
                const msg = document.getElementById('business-hours-error-message');
                if (msg) msg.textContent = `Erro ao carregar informações: ${error.message}`;
            }
        }
    };

    doctorSelect.addEventListener('change', async function onDoctorChange() {
        const doctorId = this.value;
        toggleBusinessHoursButton();
        if (!doctorId) {
            resetDependentFields();
            return;
        }

        await loadCalendarAuto(doctorId);
        await loadAppointmentTypes(doctorId);
        await loadSpecialties(doctorId);
        if (dateInput.value) {
            await loadAvailableSlots();
        }
    });

    dateInput.addEventListener('change', loadAvailableSlots);
    appointmentTypeSelect.addEventListener('change', loadAvailableSlots);

    timeSelect.addEventListener('change', function onTimeChange() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            startsAtInput.value = selectedOption.dataset.start || '';
            endsAtInput.value = selectedOption.dataset.end || '';
        } else {
            startsAtInput.value = '';
            endsAtInput.value = '';
        }
    });

    const form = document.querySelector('form.forms-sample');
    form?.addEventListener('submit', (event) => {
        if (!calendarIdInput.value) {
            event.preventDefault();
            showAlert({
                type: 'error',
                title: 'Erro',
                message: 'Calendário não foi selecionado. Por favor, selecione um médico novamente.',
            });
            return;
        }
        if (!startsAtInput.value || !endsAtInput.value) {
            event.preventDefault();
            showAlert({
                type: 'warning',
                title: 'Atenção',
                message: 'Por favor, selecione um horário disponível.',
            });
        }
    });

    const closeBusinessHoursModal = () => {
        if (!businessHoursModal) return;
        businessHoursModal.classList.add('hidden');
        businessHoursModal.setAttribute('aria-hidden', 'true');
    };

    const openBusinessHoursModal = async () => {
        if (!businessHoursModal) return;
        const doctorId = doctorSelect.value;
        if (!doctorId) {
            showAlert({
                type: 'warning',
                title: 'Atenção',
                message: 'Selecione um médico para visualizar os dias trabalhados.',
            });
            return;
        }

        businessHoursModal.classList.remove('hidden');
        businessHoursModal.setAttribute('aria-hidden', 'false');
        await loadBusinessHours(doctorId);
    };

    businessHoursBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openBusinessHoursModal();
    });

    businessHoursModal?.querySelectorAll('[data-modal-dismiss=\"businessHoursModal\"]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            closeBusinessHoursModal();
        });
    });

    if (oldDoctorId) {
        doctorSelect.value = oldDoctorId;
        doctorSelect.dispatchEvent(new Event('change'));
    }
    if (oldDate) {
        dateInput.value = oldDate;
    }
}

function bindFormResponseReadonly() {
    const form = document.getElementById('formResponseForm');
    if (!form || form.dataset.readonlyForm !== 'true') {
        return;
    }
    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });
}

function bindMasks() {
    const cpfInputs = document.querySelectorAll('[data-mask=\"cpf\"]');
    cpfInputs.forEach((input) => {
        input.addEventListener('input', (event) => {
            let value = event.target.value.replace(/\\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\\d{3})(\\d)/, '$1.$2');
                value = value.replace(/(\\d{3})(\\d)/, '$1.$2');
                value = value.replace(/(\\d{3})(\\d{1,2})$/, '$1-$2');
                event.target.value = value;
            }
        });
    });

    const phoneInputs = document.querySelectorAll('[data-mask=\"phone\"]');
    phoneInputs.forEach((input) => {
        input.addEventListener('input', (event) => {
            let value = event.target.value.replace(/\\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/(\\d{2})(\\d)/, '($1) $2');
                    value = value.replace(/(\\d{4})(\\d)/, '$1-$2');
                } else {
                    value = value.replace(/(\\d{2})(\\d)/, '($1) $2');
                    value = value.replace(/(\\d{5})(\\d)/, '$1-$2');
                }
                event.target.value = value;
            }
        });
    });
}
