/**
 * Gerador de senha forte seguindo as regras de segurança
 * - Mínimo 8 caracteres
 * - Pelo menos uma letra maiúscula
 * - Pelo menos uma letra minúscula
 * - Pelo menos um número
 * - Pelo menos um caractere especial
 */
function generateStrongPassword() {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    // Garante pelo menos um de cada tipo
    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];
    
    // Completa até 12 caracteres com caracteres aleatórios
    const all = uppercase + lowercase + numbers + special;
    for (let i = password.length; i < 12; i++) {
        password += all[Math.floor(Math.random() * all.length)];
    }
    
    // Embaralha os caracteres
    return password.split('').sort(() => Math.random() - 0.5).join('');
}

/**
 * Inicializa o botão de gerar senha
 * @param {string} passwordFieldId - ID do campo de senha
 * @param {string} confirmFieldId - ID do campo de confirmação (opcional)
 */
function initPasswordGenerator(passwordFieldId, confirmFieldId = null) {
    const passwordField = document.getElementById(passwordFieldId);
    if (!passwordField) return;
    
    // Cria o botão se não existir
    const existingBtn = passwordField.parentElement.querySelector('.btn-generate-password');
    if (existingBtn) return; // Já existe
    
    // Cria container com input-group se não existir
    let inputGroup = passwordField.parentElement.querySelector('.input-group');
    if (!inputGroup) {
        // Cria wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';
        
        // Move o input para dentro do wrapper
        passwordField.parentElement.insertBefore(wrapper, passwordField);
        wrapper.appendChild(passwordField);
        
        inputGroup = wrapper;
    }
    
    // Cria o botão
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-secondary btn-generate-password';
    btn.innerHTML = '<i class="mdi mdi-refresh me-1"></i> Gerar';
    btn.title = 'Gerar senha forte automaticamente';
    
    // Adiciona evento de clique
    btn.addEventListener('click', function() {
        const generatedPassword = generateStrongPassword();
        passwordField.value = generatedPassword;
        passwordField.type = 'text'; // Mostra temporariamente
        passwordField.select(); // Seleciona o texto
        
        // Se houver campo de confirmação, preenche também
        if (confirmFieldId) {
            const confirmField = document.getElementById(confirmFieldId);
            if (confirmField) {
                confirmField.value = generatedPassword;
                confirmField.type = 'text'; // Mostra temporariamente
            }
        }
        
        // Volta para password após 3 segundos
        setTimeout(() => {
            passwordField.type = 'password';
            if (confirmFieldId) {
                const confirmField = document.getElementById(confirmFieldId);
                if (confirmField) {
                    confirmField.type = 'password';
                }
            }
        }, 3000);
        
        // Feedback visual
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Gerada!';
        setTimeout(() => {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
            btn.innerHTML = '<i class="mdi mdi-refresh me-1"></i> Gerar';
        }, 2000);
    });
    
    // Adiciona o botão ao input-group
    const appendDiv = document.createElement('div');
    appendDiv.appendChild(btn);
    inputGroup.appendChild(appendDiv);
}

