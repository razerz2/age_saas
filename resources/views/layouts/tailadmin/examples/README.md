# Páginas de Exemplo - Template Laravel

Este diretório contém páginas HTML de exemplo que demonstram o frontend do template Laravel.

## Estrutura

```
examples/
├── README.md              # Este arquivo
├── index.html             # Página inicial de apresentação
├── login.html             # Página de login/autenticação
├── dashboard.html          # Dashboard principal com gráficos
├── users.html             # Gerenciamento de usuários
├── products.html          # Catálogo de produtos
├── reports.html           # Relatórios e análises
└── settings.html          # Configurações do sistema
```

## Recursos Utilizados

### Assets Locais
- **CSS**: `../public/assets/css/style.css` - Estilos compilados do projeto
- **JavaScript**: `../public/assets/js/bundle.js` - Scripts compilados do projeto
- **Imagens de Usuários**: `../public/assets/images/user/` - Avatares e fotos de perfil
- **Imagens de Produtos**: `../public/assets/images/product/` - Imens de produtos

### Recursos Externos
- **Chart.js**: `https://cdn.jsdelivr.net/npm/chart.js` - Biblioteca para gráficos (usada em dashboard.html e reports.html)

## Páginas Disponíveis

### 1. index.html
- **Finalidade**: Página de apresentação do template
- **Recursos**: Hero section, cards de recursos, showcase de páginas, stack tecnológico
- **Navegação**: Links para todas as outras páginas

### 2. login.html
- **Finalidade**: Autenticação de usuários
- **Recursos**: Formulário de login, opções de login social, link para cadastro
- **Redirecionamento**: Formulário aponta para dashboard.html

### 3. dashboard.html
- **Finalidade**: Painel administrativo principal
- **Recursos**: Sidebar navegação, cards de métricas, gráficos interativos, atividade recente
- **Gráficos**: Chart.js para visualização de dados

### 4. users.html
- **Finalidade**: Gerenciamento de usuários
- **Recursos**: Tabela de usuários, filtros, paginação, modal para novo usuário
- **Funcionalidades**: Editar, excluir, busca

### 5. products.html
- **Finalidade**: Catálogo de produtos
- **Recursos**: Grid visual de produtos, cards de estatísticas, modal para novo produto
- **Visualização**: Layout responsivo com imagens dos produtos

### 6. reports.html
- **Finalidade**: Relatórios e análises
- **Recursos**: Múltiplos gráficos, tabelas de dados, filtros de período, exportação
- **Análises**: Vendas, produtos, regiões, métricas de conversão

### 7. settings.html
- **Finalidade**: Configurações do sistema e perfil
- **Recursos**: Múltiplas abas (perfil, segurança, notificações, aparência, sistema)
- **Funcionalidades**: Formulários de configuração, preferências do usuário

## Navegação

Todas as páginas estão interconectadas através de:
- **Sidebar**: Navegação principal presente em dashboard, users, products, reports, settings
- **Header**: Links para login e dashboard
- **Links diretos**: Botões e links específicos entre páginas

## Design e Estilos

- **Framework**: Tailwind CSS (compilado no style.css do projeto)
- **Cores**: Paleta baseada em indigo/purple com variantes
- **Responsividade**: Design adaptável para mobile, tablet e desktop
- **Ícones**: Font Awesome (incluído no bundle.js do projeto)

## Como Usar

1. **Abra qualquer página** diretamente no navegador para visualização
2. **Navegue** entre as páginas usando os links e menus
3. **Teste** as funcionalidades interativas (modais, filtros, etc.)
4. **Integre** com o backend Laravel conforme necessário

## Integração com Laravel

Para integrar estas páginas com o projeto Laravel:

1. **Mova os arquivos** para `resources/views/`
2. **Converta para Blade**: Adicione sintaxe Blade onde necessário
3. **Ajuste os caminhos**: Use `asset()` helper para CSS/JS/imagens
4. **Adicione rotas**: Configure as rotas no arquivo `routes/web.php`
5. **Conecte com controllers**: Implemente a lógica do backend

## Personalização

- **Cores**: Modifique as classes Tailwind no style.css
- **Layout**: Ajuste a estrutura HTML conforme necessário
- **Componentes**: Reutilize seções com Blade @include
- **Dados**: Substitua os dados estáticos por dados dinâmicos do banco

## Notas

- As páginas usam dados mock/demonstração
- Imagens são placeholders do projeto (pasta public/assets/images/)
- Funcionalidades JavaScript são demonstrativas
- Formulários não estão conectados ao backend
