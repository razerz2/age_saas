# 📋 Guia Passo a Passo: Como Criar um Formulário

Este guia explica como criar e configurar um formulário completo no sistema, desde a criação básica até adicionar seções, perguntas e opções de resposta.

---

## 📍 **PASSO 1: Acessar a Lista de Formulários**

1. No menu lateral, localize a seção **"Formulários"**
2. Clique em **"Formulários"** para expandir o submenu
3. Clique em **"Listar"** para ver todos os formulários existentes

---

## 📍 **PASSO 2: Criar um Novo Formulário**

1. Na página de listagem de formulários, clique no botão **"+ Novo"** (ou acesse diretamente pelo menu: **Formulários → Novo Formulário**)

2. Preencha os campos obrigatórios:

   **Informações do Formulário:**
   - **Nome** * (obrigatório): Digite um nome descritivo para o formulário
     - Exemplo: "Formulário de Pré-Consulta", "Anamnese Inicial", etc.
   - **Descrição** (opcional): Adicione uma descrição sobre o propósito do formulário

   **Associação:**
   - **Médico** * (obrigatório): Selecione o médico para o qual o formulário será criado
     - ⚠️ **Importante**: O formulário é vinculado ao médico, não à especialidade
   - **Especialidade** (opcional): Após selecionar o médico, você pode escolher uma especialidade relacionada a ele
     - ⚠️ **Nota**: As especialidades só aparecem após selecionar um médico

   **Status:**
   - **Status do Formulário**: Escolha entre "Ativo" ou "Inativo"
     - Use "Ativo" para formulários que estão em uso
     - Use "Inativo" para formulários desativados temporariamente

3. Clique no botão **"Salvar Formulário"**

4. Você será redirecionado para a lista de formulários com uma mensagem de sucesso

---

## 📍 **PASSO 3: Acessar o Construtor do Formulário**

1. Na lista de formulários, localize o formulário que você acabou de criar
2. Clique no botão **"Ver"** (ícone de olho) na linha do formulário
3. Na página de detalhes do formulário, clique no botão **"Construir Formulário"** (botão azul com ícone de lápis)

---

## 📍 **PASSO 4: Adicionar Seções (Opcional, mas Recomendado)**

As seções ajudam a organizar as perguntas do formulário em grupos lógicos.

1. Na página do construtor, clique no botão **"Adicionar Seção"**
2. No modal que abrir:
   - **Título da Seção**: Digite um título descritivo
     - Exemplos: "Dados Pessoais", "Sintomas", "Histórico Médico", "Exame Físico", etc.
     - ⚠️ **Nota**: O título é opcional, mas recomendado para melhor organização
3. Clique em **"Adicionar"**
4. A seção será criada e aparecerá na página

**💡 Dica**: Você pode criar múltiplas seções para organizar melhor o formulário.

---

## 📍 **PASSO 5: Adicionar Perguntas**

### 5.1. Adicionar uma Pergunta Geral (sem seção)

1. Clique no botão **"Adicionar Pergunta"**
2. No modal que abrir:
   - **Seção**: Deixe como "Pergunta Geral (sem seção)" ou selecione uma seção criada
   - **Pergunta** *: Digite o texto da pergunta
     - Exemplo: "Qual é o seu nome completo?"
   - **Texto de Ajuda** (opcional): Adicione um texto explicativo que aparecerá abaixo da pergunta
   - **Tipo de Resposta** *: Selecione o tipo de resposta esperada:
     - **Texto**: Resposta livre em texto
     - **Número**: Apenas números
     - **Data**: Seleção de data
     - **Sim/Não**: Resposta booleana (Sim ou Não)
     - **Escolha Única**: Uma única opção (radio buttons)
     - **Escolha Múltipla**: Múltiplas opções (checkboxes)
   - **Campo obrigatório**: Marque esta opção se a pergunta for obrigatória

3. **Se você escolheu "Escolha Única" ou "Escolha Múltipla"**:
   - Uma seção de "Opções de Resposta" aparecerá
   - Clique em **"Adicionar Opção"** para cada opção desejada
   - Para cada opção, preencha:
     - **Rótulo**: O texto que será exibido (ex: "Sim", "Não", "Dor de cabeça")
     - **Valor**: O valor interno (geralmente em minúsculas, sem espaços: "sim", "nao", "dor_cabeca")
   - Continue adicionando opções conforme necessário

4. Clique em **"Adicionar"** para salvar a pergunta

### 5.2. Adicionar uma Pergunta em uma Seção

1. Clique no botão **"Adicionar Pergunta"**
2. No campo **"Seção"**, selecione a seção desejada
3. Preencha os demais campos conforme descrito acima
4. Clique em **"Adicionar"**

**💡 Dica**: Você pode adicionar quantas perguntas quiser em cada seção.

---

## 📍 **PASSO 6: Gerenciar Perguntas Existentes**

### 6.1. Editar uma Pergunta

1. Localize a pergunta que deseja editar
2. Clique no botão de **editar** (ícone de lápis) ao lado da pergunta
3. No modal que abrir, faça as alterações necessárias
4. Clique em **"Salvar"**

### 6.2. Deletar uma Pergunta

1. Localize a pergunta que deseja deletar
2. Clique no botão de **deletar** (ícone de lixeira) ao lado da pergunta
3. Confirme a exclusão no diálogo que aparecer
4. A pergunta será removida permanentemente

---

## 📍 **PASSO 7: Gerenciar Opções de Resposta**

### 7.1. Adicionar Opções a uma Pergunta Existente

1. Localize a pergunta do tipo "Escolha Única" ou "Escolha Múltipla"
2. Clique no botão **"Adicionar Opção"** abaixo da lista de opções
3. No modal que abrir:
   - **Rótulo** *: Digite o texto da opção (ex: "Sim", "Não", "Dor de cabeça")
   - **Valor** *: Digite o valor interno (ex: "sim", "nao", "dor_cabeca")
4. Clique em **"Adicionar"**

### 7.2. Deletar uma Opção

1. Localize a opção que deseja deletar
2. Clique no ícone de **lixeira** ao lado da opção
3. Confirme a exclusão
4. A opção será removida

---

## 📍 **PASSO 8: Gerenciar Seções**

### 8.1. Editar uma Seção

1. Localize a seção que deseja editar
2. Clique no botão de **editar** (ícone de lápis) no cabeçalho da seção
3. No modal que abrir, altere o título
4. Clique em **"Salvar"**

### 8.2. Deletar uma Seção

1. Localize a seção que deseja deletar
2. Clique no botão de **deletar** (ícone de lixeira) no cabeçalho da seção
3. Confirme a exclusão
   - ⚠️ **Atenção**: Ao deletar uma seção, todas as perguntas dentro dela serão movidas para "Perguntas Gerais"
4. A seção será removida

---

## 📍 **PASSO 9: Visualizar o Formulário Finalizado**

1. Após adicionar todas as seções e perguntas, você pode visualizar o formulário:
   - As seções aparecem como cards organizados
   - As perguntas aparecem dentro de suas respectivas seções
   - Perguntas sem seção aparecem em "Perguntas Gerais"

2. Para testar o formulário, você pode:
   - Clicar em **"Voltar"** para retornar à página de detalhes
   - Na página de detalhes, clique em **"Preencher Formulário"** para testar o preenchimento

---

## 📍 **PASSO 10: Editar Informações Básicas do Formulário**

Se precisar alterar o nome, descrição, médico ou status do formulário:

1. Na lista de formulários, clique em **"Editar"** (ícone de lápis)
2. Faça as alterações necessárias
3. Clique em **"Atualizar Formulário"**

---

## ✅ **Checklist de Criação de Formulário**

Use este checklist para garantir que seu formulário está completo:

- [ ] Formulário criado com nome descritivo
- [ ] Médico selecionado (obrigatório)
- [ ] Especialidade selecionada (opcional, mas recomendado)
- [ ] Status definido (Ativo/Inativo)
- [ ] Seções criadas (se necessário para organização)
- [ ] Todas as perguntas adicionadas
- [ ] Tipos de resposta corretos definidos
- [ ] Opções de resposta adicionadas (para perguntas de escolha)
- [ ] Campos obrigatórios marcados corretamente
- [ ] Formulário testado através do botão "Preencher Formulário"

---

## 💡 **Dicas e Boas Práticas**

1. **Organização**: Use seções para agrupar perguntas relacionadas
   - Exemplo: "Dados Pessoais", "Sintomas Atuais", "Histórico Médico"

2. **Nomenclatura**: Use nomes claros e descritivos para perguntas e opções

3. **Tipos de Resposta**:
   - Use "Texto" para respostas livres
   - Use "Número" para idades, valores, etc.
   - Use "Data" para datas de nascimento, consultas, etc.
   - Use "Sim/Não" para perguntas binárias simples
   - Use "Escolha Única" quando apenas uma opção é permitida
   - Use "Escolha Múltipla" quando múltiplas opções são permitidas

4. **Campos Obrigatórios**: Marque como obrigatórios apenas os campos realmente necessários

5. **Texto de Ajuda**: Use o campo "Texto de Ajuda" para orientar o preenchimento

6. **Valores das Opções**: Use valores em minúsculas, sem espaços, para facilitar processamento
   - Exemplo: "dor_cabeca" ao invés de "Dor de Cabeça"

---

## 🆘 **Solução de Problemas**

**Problema**: Não consigo ver especialidades ao selecionar um médico
- **Solução**: Verifique se o médico tem especialidades cadastradas. Acesse a página de edição do médico para adicionar especialidades.

**Problema**: Não consigo adicionar opções a uma pergunta
- **Solução**: Certifique-se de que o tipo de pergunta é "Escolha Única" ou "Escolha Múltipla". Outros tipos não permitem opções.

**Problema**: Ao deletar uma seção, as perguntas desapareceram
- **Solução**: As perguntas não desaparecem, elas são movidas automaticamente para "Perguntas Gerais". Verifique essa seção.

**Problema**: O botão "Adicionar Pergunta" está desabilitado
- **Solução**: Crie pelo menos uma seção primeiro, ou o sistema permitirá adicionar perguntas gerais.

---

## 📞 **Próximos Passos**

Após criar o formulário:

1. **Preencher Formulário**: Teste o formulário usando o botão "Preencher Formulário"
2. **Visualizar Respostas**: Acesse "Respostas" no menu para ver as respostas coletadas
3. **Editar Formulário**: Volte ao construtor sempre que precisar fazer alterações

---

**Fim do Guia** 🎉

