export function init() {
    const select = document.getElementById('requested_plan_id');
    const descriptionDiv = document.getElementById('plan-description');
    if (!select || !descriptionDiv) {
        return;
    }

    const updateDescription = () => {
        const selectedOption = select.options[select.selectedIndex];
        const description = selectedOption?.getAttribute('data-description') || '';
        descriptionDiv.textContent = description || '';
    };

    select.addEventListener('change', updateDescription);
    updateDescription();
}
