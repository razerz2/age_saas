function nextIndex(container, rowSelector, inputPrefix) {
    const rows = Array.from(container.querySelectorAll(rowSelector));
    if (rows.length === 0) {
        return 0;
    }

    let maxIndex = -1;
    rows.forEach((row) => {
        const firstField = row.querySelector(`input[name^="${inputPrefix}["], select[name^="${inputPrefix}["]`);
        if (!firstField || !firstField.name) return;

        const match = firstField.name.match(/\[(\d+)\]/);
        if (!match) return;

        const idx = parseInt(match[1], 10);
        if (!Number.isNaN(idx) && idx > maxIndex) {
            maxIndex = idx;
        }
    });

    return maxIndex + 1;
}

function appendTemplateRow(templateId, container, index) {
    const template = document.getElementById(templateId);
    if (!template) return;

    const html = template.innerHTML.replaceAll('__INDEX__', String(index));
    container.insertAdjacentHTML('beforeend', html);
}

function bindRemoveRow(container, rowSelector, buttonSelector) {
    container.addEventListener('click', (event) => {
        const button = event.target.closest(buttonSelector);
        if (!button) return;

        const rows = container.querySelectorAll(rowSelector);
        if (rows.length <= 1) {
            return;
        }

        const row = button.closest(rowSelector);
        if (row) {
            row.remove();
        }
    });
}

export function init() {
    const hoursContainer = document.getElementById('business-hours-rows');
    const typesContainer = document.getElementById('appointment-types-rows');

    if (!hoursContainer || !typesContainer) {
        return;
    }

    const addHourButton = document.getElementById('add-business-hour-row');
    const addTypeButton = document.getElementById('add-appointment-type-row');

    if (addHourButton) {
        addHourButton.addEventListener('click', () => {
            const index = nextIndex(hoursContainer, '.business-hour-row', 'business_hours');
            appendTemplateRow('business-hour-row-template', hoursContainer, index);
        });
    }

    if (addTypeButton) {
        addTypeButton.addEventListener('click', () => {
            const index = nextIndex(typesContainer, '.appointment-type-row', 'appointment_types');
            appendTemplateRow('appointment-type-row-template', typesContainer, index);
        });
    }

    bindRemoveRow(hoursContainer, '.business-hour-row', '.remove-business-hour-row');
    bindRemoveRow(typesContainer, '.appointment-type-row', '.remove-appointment-type-row');
}
