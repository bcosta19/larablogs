# Larablogs 📰

Monitor pessoal de blogs e feeds RSS/Atom com notificações instantâneas por e-mail, construído com **Laravel 13**, **Vue.js 3**, **Inertia.js** e **Tailwind CSS v4**.

---

## ✨ Funcionalidades

- **Descoberta Inteligente de Feeds**: Basta colar a URL de qualquer blog (ex.: `https://laravel-news.com`, `https://overreacted.io`) e o Larablogs localiza automaticamente as tags RSS/Atom correspondentes.
- **Linha de Base Inicial (Anti-Spam)**: Postagens já publicadas são cadastradas como histórico de partida; apenas *novas* postagens publicadas após a inscrição geram notificações.
- **Design Editorial (Estilo Jornal & Boletim)**:
  - Tipografia serifada clássica inspirada na imprensa literária.
  - **Tema Escuro nativo por padrão** (*Midnight Editorial*) com botão para alternar para **Tema Claro** com persistência em `localStorage`.
  - **Selos de Alto Contraste para Favicons**: Logotipos escuros e transparentes mantêm nitidez e legibilidade perfeitas.
- **Multi-idioma (i18n)**:
  - 🇧🇷 Português
  - 🇺🇸 English
  - 🇪🇸 Español
  - 🇫🇷 Français
  - 🇩🇪 Deutsch
  - Formatação de datas e tempos relativos automáticos no idioma selecionado.
- **Notificações Síncronas**: Sem necessidade obrigatória de queues ou workers adicionais para rodar.
- **Agendamento em Segundo Plano**: Comando `php artisan blogs:check` já configurado no scheduler do Laravel.

---

## 🛠️ Stack Tecnológica

- **PHP**: 8.5.x
- **Laravel**: 13.x
- **Frontend**: Vue.js 3, Inertia.js 3, Vite, Tailwind CSS v4
- **Banco de Dados**: SQLite (zero-config)
- **Gerenciador de Versões**: `mise` (`mise.toml`)

---

## 🚀 Instalação e Execução

### 1. Clonar o repositório
```bash
git clone git@github.com:bcosta19/larablogs.git
cd larablogs
```

### 2. Instalar dependências
```bash
composer install
npm install
```

### 3. Configurar ambiente e banco de dados
```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

### 4. Executar em desenvolvimento
```bash
composer run dev
```
*(ou em terminais separados: `php artisan serve` e `npm run dev`)*

Acesse no seu navegador: `http://localhost:8000`

---

## ⏰ Verificação de Feeds em Background

Para verificar os feeds manualmente:
```bash
php artisan blogs:check
```

Para manter o agendador rodando em desenvolvimento:
```bash
php artisan schedule:work
```

Ou configure uma única entrada no cron do Linux (`crontab -e`):
```cron
* * * * * cd /caminho/para/larablogs && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Testes

```bash
php artisan test
```

---

## 📄 Licença

MIT License.
