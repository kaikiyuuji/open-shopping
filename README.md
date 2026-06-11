# OpenShopping — Gerenciador de Compras por Estabelecimento

Sistema moderno e responsivo para controlar compras, estabelecimentos e produtos com histórico de preços. Inclui OCR automático para cupons fiscais via microserviço Python.

## 🎯 O Que É

OpenShopping é um **aplicativo web** para gerenciar:

- **Estabelecimentos**: cadastre lojas, supermercados, farmácias com endereço e categoria
- **Compras**: registre cada lançamento com data, valor, forma de pagamento e parcelamento automático
- **Produtos**: mantenha catálogo de produtos com nome único, categoria e histórico de preços
- **Itens de Compra**: vincule produtos às compras com quantidade e preço pago
- **OCR de Cupons**: anexe foto de nota fiscal → sistema extrai itens automaticamente (com revisão manual antes de salvar)

**Single-user** (sem multiusuário). Modelo de dados: Estabelecimento → Compras → Itens → Produtos.

## 🛠 Stack

- **Backend**: Laravel 12, Eloquent ORM
- **Frontend**: Livewire 3, Blade, Alpine.js, Tailwind CSS
- **Database**: SQLite (dev/testes), migrável para PostgreSQL/MySQL
- **OCR**: Python FastAPI + Tesseract (microserviço separado)
- **Fila**: Queue Laravel driver database
- **Testes**: PHPUnit (113 testes, 100% passing)

## 📋 Requisitos de Sistema

### Para o App Laravel

- **PHP** 8.2+
- **Node.js** 18+
- **SQLite** (incluso no PHP) ou PostgreSQL/MySQL
- **Composer**
- **npm**

### Para o Microserviço OCR

- **Python** 3.10+
- **Tesseract OCR** instalado em `C:\Program Files\Tesseract-OCR` (Windows) ou `/usr/bin/tesseract` (Linux)
  - Baixar: https://github.com/UB-Mannheim/tesseract/wiki
  - No projeto: pacote `por.traineddata` (português) já incluso em `ocr-service/tessdata/`

## 🚀 Instalação

### 1. Clonar e Entrar no Diretório

```bash
cd C:\Projetos\openshopping
```

### 2. Instalar Dependências PHP

```bash
composer install
```

### 3. Copiar `.env` e Gerar Chave

```bash
cp .env.example .env
php artisan key:generate
```

Verifique `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
OCR_SERVICE_URL=http://127.0.0.1:8100
```

### 4. Instalar Dependências Node

```bash
npm install
```

### 5. Preparar Microserviço OCR

```bash
cd ocr-service
python -m venv .venv
.venv/Scripts/python -m pip install -r requirements.txt
cd ..
```

### 6. Rodar Migrations (cria tabelas)

```bash
php artisan migrate
```

## ▶️ Como Rodar

### Desenvolvimento (Comando Único) — RECOMENDADO

```bash
composer dev
```

Levanta tudo em paralelo:
- **Server**: http://localhost:8000
- **Queue Worker**: processa jobs de background
- **Vite**: rebuilda assets (Tailwind/JS)
- **OCR Service**: http://localhost:8100

Acesse http://localhost:8000 no navegador.

### Terminais Separados (Alternativa)

Se preferir rodar em terminais separados:

```bash
# Terminal 1: App server
php artisan serve

# Terminal 2: Fila (OBRIGATÓRIO para OCR funcionar)
php artisan queue:listen --tries=1 --timeout=0

# Terminal 3: Vite (assets em tempo real)
npm run dev

# Terminal 4: Microserviço OCR
ocr-service\.venv\Scripts\python.exe -m uvicorn main:app --port 8100 --app-dir ocr-service
```

### Rodar Testes

```bash
# Todos os testes (113 esperados)
php artisan test

# Apenas Feature tests
php artisan test --testsuite=Feature

# Um arquivo específico
php artisan test tests/Feature/ExtracaoOcrTest.php

# Um teste pelo nome
php artisan test --filter=pode_cadastrar_novo_estabelecimento

# Em paralelo (mais rápido)
php artisan test --parallel
```

Testes usam **SQLite in-memory** — nenhuma setup de banco necessária.

## 📁 Estrutura do Projeto

```
openshopping/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── EstabelecimentoController.php
│   │   │   ├── CompraController.php
│   │   │   ├── ProdutoController.php
│   │   │   ├── ItemCompraController.php
│   │   │   └── ExtracaoOcrController.php
│   │   └── Requests/
│   │       ├── EstabelecimentoRequest.php
│   │       ├── CompraRequest.php
│   │       ├── ProdutoRequest.php
│   │       ├── ItemCompraRequest.php
│   │       ├── ExtracaoOcrRequest.php
│   │       └── ConfirmarExtracaoRequest.php
│   ├── Jobs/
│   │   └── ProcessarExtracaoOcr.php
│   └── Models/
│       ├── Estabelecimento.php
│       ├── Compra.php
│       ├── Produto.php
│       ├── ItemCompra.php
│       └── ExtracaoOcr.php
├── database/
│   ├── migrations/
│   │   ├── 2025_01_01_000001_create_estabelecimentos_table.php
│   │   ├── 2025_01_01_000002_create_produtos_table.php
│   │   ├── 2025_01_01_000003_create_compras_table.php
│   │   ├── 2025_01_01_000004_create_itens_compra_table.php
│   │   └── 2025_06_10_000001_create_extracoes_ocr_table.php
│   └── factories/
│       ├── EstabelecimentoFactory.php
│       ├── ProdutoFactory.php
│       ├── CompraFactory.php
│       ├── ItemCompraFactory.php
│       └── ExtracaoOcrFactory.php
├── resources/views/
│   ├── layouts/main.blade.php
│   ├── home.blade.php
│   ├── estabelecimentos/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   ├── _form.blade.php
│   │   └── compras.blade.php
│   ├── compras/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── _form.blade.php
│   ├── produtos/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── _form.blade.php
│   ├── itens/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── _form.blade.php
│   └── ocr/
│       └── revisar.blade.php
├── routes/web.php
├── tests/
│   ├── Unit/
│   │   ├── EstabelecimentoTest.php
│   │   ├── CompraTest.php
│   │   ├── ItemCompraTest.php
│   │   └── ProdutoTest.php
│   └── Feature/
│       ├── EstabelecimentoCrudTest.php
│       ├── CompraCrudTest.php
│       ├── ItemCompraCrudTest.php
│       ├── ProdutoCrudTest.php
│       ├── ValidacoesTest.php
│       ├── ConsultasTest.php
│       └── ExtracaoOcrTest.php
├── ocr-service/
│   ├── main.py              # FastAPI server
│   ├── parser.py            # Extractor de cupons
│   ├── test_parser.py       # Testes do parser
│   ├── requirements.txt     # pytesseract, fastapi, uvicorn, pillow
│   ├── tessdata/
│   │   └── por.traineddata  # Modelo português (4.8 MB)
│   └── .venv/               # Virtual env (gitignore)
├── public/build/            # Assets compilados (gitignore)
├── .env.example
├── CLAUDE.md                # Documentação técnica para devs
├── README.md                # Este arquivo
└── composer.json
```

## 🔄 Fluxo de Uso

### Criar Compra com OCR

1. Acesse **Compras** → **Nova Compra**
2. Preencha: estabelecimento, data (padrão: hoje), valor, forma de pagamento
3. Se crédito: marque **Compra parcelada** e defina número de parcelas (min 2)
4. Clique **Salvar** → redireciona para tela da compra
5. **Seção OCR**: anexe foto do cupom fiscal
6. Sistema envia para microserviço Python (assíncrono na fila)
7. Recarregue a página → extrações aparecem com status `concluida`
8. Clique **Revisar** → edite nomes, categorias, quantidades, preços
9. Desmarque itens que não quer incluir
10. Clique **Confirmar itens** → cria produtos (reusa existentes) e adiciona à compra

### Consultar Histórico de Preços

1. Acesse **Produtos**
2. Clique em um produto → **Histórico**
3. Veja em quais compras apareceu, data, quantidade, preço pago, estabelecimento

### Compras por Estabelecimento

1. Acesse **Estabelecimentos** → clique em um
2. Veja todas as compras naquele lugar (ordenadas por data, mais recentes primeiro)

### Adicionar Itens Manualmente

1. Na tela da compra, clique **Adicionar item**
2. Selecione produto existente OU marque **Cadastrar produto novo**
3. Se novo: preenchimento nome e categoria
4. Preencha quantidade e preço pago
5. Clique **Salvar** → item é adicionado

## 🧪 Testes e Validação

### Cobertura

- **4 Unit Tests**: Models — atributos, relacionamentos, accessors
- **7 Feature Tests**: CRUDs completos de estabelecimentos, compras, produtos, itens
- **11 Feature Tests**: Validações de todos os campos
- **1 Feature Test**: Consultas e relatórios
- **11 Feature Tests**: OCR workflow (upload, processamento, revisão, confirmação)
- **13 Parser Tests** (Python): Extração de cupons em 3 layouts diferentes

**Total: 113 testes passando.**

### Rodar com Coverage

```bash
php artisan test --coverage
```

### Validações Incluídas

- **Estabelecimento**: nome obrigatório (min 2 chars), endereço, categoria (enum)
- **Produto**: nome único, categoria, min 2 caracteres
- **Compra**: valor > 0, data válida, forma_pagamento (enum: credito/debito/dinheiro)
  - Se parcelado: requer crédito, min 2 parcelas
  - Quantidade de parcelas calculada automaticamente
- **Item**: quantidade inteira > 0, preço > 0, produto existente
- **OCR**: arquivo imagem válido, máx 10 MB, revisão obrigatória antes de confirmar

## 🎨 Design

- **Cores**: Preto e branco (minimalista e elegante)
- **Tipografia**: Tracking wide, sans-serif system
- **Interatividade**: Alpine.js para toggle de parcelamento e produto novo
- **Responsivo**: Tailwind CSS (grid, flex, responsive breakpoints)
- **Acessibilidade**: Inputs com labels, datalist de produtos, error messages claras

## 🔐 Segurança

- **Form Requests**: Validação server-side em todas as rotas
- **CSRF Protection**: Laravel middleware padrão
- **SQL Injection**: Proteção via Eloquent ORM
- **OCR Upload**: Validação de tipo (image/*) e tamanho (10 MB)
- **Single-User**: Sem autenticação necessária (setup privado)

## 📝 Logs

Logs da aplicação em `storage/logs/laravel.log`.

Para ver em tempo real durante `composer dev`:
```bash
tail -f storage/logs/laravel.log
```

## 🐛 Troubleshooting

### "Processando infinito" na extração OCR

**Problema**: Fila parada ou OCR service fora.  
**Solução**: Use `composer dev` (sobe fila + OCR); não rode `php artisan serve` solto.

### Tesseract não encontrado

**Problema**: `TesseractNotFoundError` ou `TESSDATA_PREFIX` não definido.  
**Solução**: 
- **Windows**: Instale Tesseract em `C:\Program Files\Tesseract-OCR`
  - Download: https://github.com/UB-Mannheim/tesseract/wiki
- **Linux**: `sudo apt-get install tesseract-ocr tesseract-ocr-por`
- Ou defina `TESSERACT_CMD` env var com caminho correto

### Porta 8000/8100 já em uso

**Solução**:
```bash
# Windows
netstat -ano | findstr ":8000"
taskkill /PID <PID> /F

# Linux
lsof -i :8000
kill -9 <PID>
```

### Alpine.js não funciona (parcelamento não aparece)

**Problema**: Assets não compilados.  
**Solução**: Rode `npm run build` ou use `composer dev`.

## 📚 Documentação Adicional

- **CLAUDE.md**: Instruções técnicas completas para devs (stack, requisitos, como rodar testes OCR)
- **composer.json**: Scripts (`composer test`, `composer dev`)
- **phpunit.xml**: Config de testes (SQLite in-memory)
- **tailwind.config.js**: Customização de estilos
- **vite.config.js**: Bundling de assets

## 🤝 Contribuindo

1. Rode testes: `php artisan test`
2. Escreva testes para novo recurso (TDD)
3. Siga conventions: Blade/Livewire/Alpine/Tailwind como está

## 📄 Licença

MIT

---

**Desenvolvido com Laravel 12, Livewire 3, FastAPI e ❤️**

Última atualização: Junho 2026
