# OpenShopping — Instruções Técnicas para Desenvolvimento

> **Para entender o projeto em alto nível**, leia [README.md](README.md).
> Este documento é referência para devs e operação.

## Stack

- **Backend**: Laravel 12, Eloquent ORM, Form Requests (validação server-side)
- **Frontend**: Livewire 3, Blade, Alpine.js (v3), Tailwind CSS (v3)
- **Database**: SQLite (testes/dev), migrations-ready para PostgreSQL/MySQL
- **OCR**: Python FastAPI + Tesseract, microserviço separado (`ocr-service/`)
- **Fila**: Queue Laravel, driver `database` (tabela `jobs`)
- **Testes**: PHPUnit 11.5, 113 testes (100% passing)
- **Assets**: Vite 7.0, npm scripts

## Requisitos de Sistema

### Linux/Mac

```bash
# PHP 8.2+, Node 18+, Python 3.10+
php -v
node -v
python3 --version

# Tesseract OCR
brew install tesseract tesseract-lang  # Mac
# ou
apt-get install tesseract-ocr tesseract-ocr-por  # Ubuntu/Debian

# Git
git --version
```

### Windows

```powershell
# PHP 8.2+, Node 18+, Python 3.10+
php -v
node -v
python --version

# Tesseract: https://github.com/UB-Mannheim/tesseract/wiki
# Instale em C:\Program Files\Tesseract-OCR

# Git
git --version
```

## Como Rodar

### Setup Inicial

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate

cd ocr-service
python -m venv .venv
.venv/Scripts/python -m pip install -r requirements.txt
cd ..
```

### Desenvolvimento (Recomendado)

```bash
composer dev
```

Levanta em paralelo: server (8000), queue worker, vite, OCR service (8100).

### Testes

```bash
# Todos os testes
php artisan test

# Com coverage
php artisan test --coverage

# Feature tests apenas
php artisan test --testsuite=Feature

# Específico
php artisan test --filter=pode_cadastrar_novo_estabelecimento

# Paralelo (mais rápido em multi-core)
php artisan test --parallel
```

Testes usam **SQLite in-memory** (ver `phpunit.xml`), sem setup de banco.

## Estrutura de Testes

```
tests/
├── Unit/                          # Model tests
│   ├── EstabelecimentoTest.php     # attrs, relationships, categorias()
│   ├── CompraTest.php              # attrs, relations, parcelamento, categoria accessor
│   ├── ItemCompraTest.php          # attrs, belongs_to relations
│   └── ProdutoTest.php             # attrs, unique constraint, hasMany itens
└── Feature/                        # CRUD, validation, workflow tests
    ├── EstabelecimentoCrudTest.php # index/create/store/show/edit/update/destroy
    ├── CompraCrudTest.php          # + consulta por estabelecimento
    ├── ItemCompraCrudTest.php      # + produto novo inline
    ├── ProdutoCrudTest.php         # + histórico (products.show)
    ├── ValidacoesTest.php          # estabelecimento, produto, compra, item
    ├── ConsultasTest.php           # histórico, grouped queries, ordering
    └── ExtracaoOcrTest.php         # upload, job, revisar, confirmar, discard
```

**Total: 113 tests, 224 assertions.**

## Microserviço OCR

### Localização

`ocr-service/` — isolado do Laravel. Python FastAPI.

### Arquivos Principais

- **main.py**: Servidor FastAPI, endpoints `GET /health` e `POST /ocr`
- **parser.py**: Extrator heurístico de cupons BR (3 layouts)
  - NFC-e: `qtd UN unit total` (sem "x")
  - ECF antigo: item em 2 linhas (desc / valores)
  - Cupom com "x": `desc qtd UN x unit total`
- **test_parser.py**: 13 testes pytest
- **tessdata/**: Pacote `por.traineddata` (português, já incluso)
- **requirements.txt**: fastapi, uvicorn, pytesseract, pillow

### Setup Microserviço

```bash
cd ocr-service

# Primeira vez
python -m venv .venv
.venv/Scripts/python -m pip install -r requirements.txt

# Testar parser
.venv/Scripts/python -m pytest test_parser.py

# Rodar server (avulso)
.venv/Scripts/python.exe -m uvicorn main:app --port 8100 --app-dir ocr-service
```

### Endpoints

#### `GET /health`
```json
{
  "status": "ok",
  "lang": "por"
}
```

#### `POST /ocr` (multipart form-data)
Request: `arquivo` (image file)
```json
{
  "texto": "SUPERMERCADO...",
  "itens": [
    {
      "descricao": "ARROZ BRANCO 5KG",
      "quantidade": 1,
      "preco": 25.99
    }
  ]
}
```

### Heurísticas do Parser

Detecta e corrige erros típicos do OCR:
- `x` → `%` (OCR confunde)
- Vírgula perdida em qtd (`10000` → qtd = 1, `30000` → qtd = 3 via total/unitário)
- Qtd comida (`sx2,29` → qtd calculada como `total/unitário`)
- Número do item: trocas `0→o`, `1→l/I`, `5→s`

### Configuração

Via `.env`:
```env
OCR_SERVICE_URL=http://127.0.0.1:8100
```

Ou env var `TESSERACT_CMD` se Tesseract em caminho não-padrão.

## Fluxo OCR Completo

1. **Upload**: User posta imagem em `POST /compras/{compra}/ocr`
   - Validação: `image/*`, max 10 MB
   - Grava `ExtracaoOcr` com status `processando`, salva arquivo em `storage/ocr/`
   - Dispara job `ProcessarExtracaoOcr` na fila

2. **Processamento** (job async):
   - Lê arquivo de `storage/ocr/`
   - Chama `HTTP POST http://127.0.0.1:8100/ocr` (o microserviço)
   - Grava `itens` JSON em `extracoes_ocr.itens` (staging area)
   - Status → `concluida` (ou `falhou` se erro)
   - Job precisa fila rodando: `php artisan queue:listen`

3. **Revisão**: User acessa `GET /compras/{compra}/ocr/{extracao}/revisar`
   - Mostra tabela editável com itens extraídos
   - Nome, categoria, qtd, preço editáveis
   - Checkbox incluir/excluir por item
   - Datalist autocomplete de produtos existentes

4. **Confirmação**: User posta `POST /compras/{compra}/ocr/{extracao}/confirmar`
   - Para cada item marcado incluir:
     - `Produto::firstOrCreate(['nome' => ...])` (reusa existente)
     - `ItemCompra::create()` (liga à compra)
   - Status → `confirmada`
   - Redireciona para compra (mostra itens adicionados)

## Convenções do Codebase

### Models (App\Models)

- Nenhum model toca `user_id` (single-user)
- Relacionamentos sempre tipados (returntype hints)
- Accessors para dados calculados (não colunas)
- Casts para tipos (float, boolean, date)
- Factories geradas via `artisan make:factory`

### Controllers

- Uma responsabilidade por controller (7 controllers)
- Form Requests fora do controller (validação cleanroom)
- Eager loading via `->load()` ou constructor relation
- Redirecionamento com `with('success', ...)` para flash message
- Sem lógica complexa (move para models/services)

### Form Requests (App\Http\Requests)

- Todas validações aqui (server-side)
- Usar `Rule::` helpers (unique, in, exists, etc)
- `withValidator()` para validações cross-field (ex: parcelado requer crédito)
- Sem autorização (single-user, `authorize()` return true)

### Views

- Layout principal: `layouts/main.blade.php` (nav, footer, flash messages)
- Naming: `recurso/index`, `recurso/create`, `recurso/edit`, `recurso/show`
- Partials com `_` prefix (`_form.blade.php`, `_table.blade.php`)
- Alpine.js para interatividade (toggle, datalist, disabling fields)
- Tailwind: classes diretas, sem CSS customizado (exceto vars de cor)

### Testes

- Sempre `use RefreshDatabase` (SQLite fresh per test)
- Feature tests via `$this->get()`, `$this->post()`, assertions `->assert*()`
- Factories em `database/factories/`
- Nenhum mock de IO (filesystem/HTTP são testados na prática)
- Nomes descritivos: `pode_...`, `teste_...`, validação com `_valida_`

## Fluxo de Desenvolvimento

### Adicionar Novo Recurso

1. Escrever **tests/Feature/NovoRecursoTest.php** (TDD)
2. Escrever **migration** (schema)
3. Escrever **Model** (relationships, accessors)
4. Escrever **Factory** (para testes)
5. Escrever **Form Request** (validação)
6. Escrever **Controller** (CRUD actions)
7. Adicionar **routes** em `routes/web.php`
8. Criar **views** (index, create, edit, show, _form)
9. Rodar `php artisan test` → todos verde

### Adicionar Validação

1. Abrir **Form Request** da ação
2. Adicionar rule no `rules()` array
3. Se validação complexa (cross-field): `withValidator()` callback
4. Escrever teste em **ValidacoesTest**
5. Testar: `php artisan test --filter=validacao_nome`

### Adicionar Query Complexa

1. Método no **Model** ou dedicated QueryBuilder
2. Eager load relations se necessário (→ load())
3. Testar em **ConsultasTest**
4. Controller usa o método (não query inline)

## Troubleshooting Desenvolvimento

### Testes falham com "no such table"

**Causa**: Migrations não rodaram.  
**Fix**: `php artisan migrate` (testes usam `:memory:`, migra a cada run automaticamente)

### Parser OCR não extrai nada

**Causa**: Layout da nota não reconhecido pelo regex.  
**Fix**: 
1. Rodear cupom no terminal: `ocr-service/.venv/Scripts/python.exe -c "from parser import parse_cupom; print(parse_cupom(open('cupom.txt').read()))"`
2. Ajustar regex em `parser.py`
3. Adicionar teste em `test_parser.py`

### Fila não processa

**Causa**: Worker parado.  
**Fix**: `php artisan queue:listen` em terminal separado, ou use `composer dev`

### Assets não compilam

**Causa**: Vite não rodando.  
**Fix**: `npm run build` (uma vez) ou `npm run dev` (watch mode)

## Scripts Úteis

```bash
composer test         # rodar testes
composer dev          # dev completo (server+queue+vite+ocr)
npm run build         # compilar assets
php artisan tinker    # REPL interativo
php artisan migrate   # aplicar migrations
php artisan migrate:rollback  # desfazer última migration
```

## Padrões de Código

### Relacionamentos

```php
// Em models
public function compras(): HasMany { return $this->hasMany(Compra::class); }
public function estabelecimento(): BelongsTo { return $this->belongsTo(Estabelecimento::class); }
```

### Validação

```php
// Em Form Requests
public function rules(): array {
    return [
        'nome' => ['required', 'string', 'min:2', 'max:255'],
        'categoria' => ['required', Rule::in(Estabelecimento::categorias())],
    ];
}
```

### Queries Eficientes

```php
// Controller
$estabelecimentos = Estabelecimento::withCount('compras')->orderBy('nome')->get();
// ou
$compra->load(['estabelecimento', 'itens.produto']);
```

### Views Minimalistas

```blade
<div>
    <label for="campo" class="block text-xs uppercase tracking-widest">Label</label>
    <input type="text" name="campo" value="{{ old('campo', $modelo?->campo) }}" class="w-full border-black focus:border-black focus:ring-black">
    @error('campo')
        <p class="mt-1 text-xs text-neutral-600">{{ $message }}</p>
    @enderror
</div>
```

## Deployment

No .env (produção):

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql  # ou postgresql
DB_DATABASE=openshopping
DB_USERNAME=user
DB_PASSWORD=...
OCR_SERVICE_URL=http://ocr-service:8100
```

Migrations: `php artisan migrate --force`

Queue supervisor: Recomendado usar [Supervisor](http://supervisord.org/) para manter `queue:work` rodando.

## Referências

- Laravel docs: https://laravel.com/docs/12.x
- Livewire: https://livewire.laravel.com
- Tailwind CSS: https://tailwindcss.com/docs
- Alpine.js: https://alpinejs.dev
- FastAPI: https://fastapi.tiangolo.com
- Tesseract OCR: https://github.com/tesseract-ocr/tesseract
