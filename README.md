# PDV MVC — Ponto de Venda em PHP + MySQL

Evolução do sistema de PDV apresentado em aula (`produto/index.html`, `salvar_produto.php`, `editar_produto.php`, `pdv.php`, `db.php`), reorganizado em **arquitetura MVC** com **Design Patterns** e acrescido dos CRUDs de **Clientes**, **Carrinho de Vendas** e **Relatórios de Vendas**.

- **Linguagem/stack:** PHP 8.1+ (PDO), MySQL 5.7+ / MariaDB 10.3+, HTML + CSS puro (sem framework e sem Composer).
- **Banco:** a tabela `produtos` mantém as mesmas colunas do sistema da aula (`cod, nome, categoria, validade, quantidade, preco_unitario, preco_pacote, imagem`).

---

## 1. O que mudou em relação ao sistema da aula

| Sistema da aula | Problema | Nesta versão |
|---|---|---|
| Um arquivo PHP por tela, com HTML + SQL + regras misturados | Difícil de manter e testar | Separação em **Model / View / Controller** |
| `INSERT` montado com variáveis do `$_POST` (`salvar_produto.php`) | **SQL Injection** | Somente *prepared statements* (PDO) em todos os repositórios |
| Saída sem escape (`<?= $produto['nome'] ?>`) | **XSS** | Função `e()` em toda saída das Views |
| Upload com `move_uploaded_file` sem validação | Permite enviar arquivo `.php` | `UploadService`: confere tipo real (finfo), limite de 2 MB e nome aleatório |
| `db.php` com host, usuário e **senha fixos no código** | Credenciais expostas no repositório | Configuração por variáveis de ambiente (`app/Config/config.php`) |
| Formulários sem proteção | **CSRF** | Token CSRF em todo `POST` |
| "Vender" só subtraía a quantidade | Sem registro de venda | Carrinho + venda gravada + itens + relatório |

> ⚠️ **Atenção:** o `db.php` original contém usuário e senha reais de um servidor MySQL em texto puro. **Nunca publique esse arquivo no GitHub**; se ele já foi compartilhado, peça a troca da senha. Este projeto não usa nem copia essas credenciais.

---

## 2. Arquitetura MVC

```
Requisição HTTP
      │
      ▼
public/index.php  ── Front Controller (único ponto de entrada)
      │  autoload + sessão + Container
      ▼
app/Core/Router.php ── casa "MÉTODO + URL" (app/routes.php) com Controller::ação
      │
      ▼
CONTROLLER (app/Controllers)  ── recebe a requisição, valida CSRF/entrada,
      │                          chama Services/Repositories e escolhe a View
      ▼
MODEL (app/Models + Services + Repositories)
      │   • Models: entidades e validações (Cliente, Produto, Venda, ItemVenda)
      │   • Repositories: SQL/PDO (única camada que fala com o banco)
      │   • Services: regras de negócio (CarrinhoService, VendaService, UploadService)
      ▼
VIEW (app/Views)  ── HTML com layout único (layout/main.php); nenhuma regra de negócio
      │
      ▼
Resposta HTML / CSV
```

### Estrutura de pastas

```
pdv/
├── public/                    # ÚNICA pasta exposta ao servidor web
│   ├── index.php              # Front Controller
│   ├── .htaccess              # URLs amigáveis (Apache)
│   ├── assets/style.css
│   └── imagens/               # uploads de produtos
├── app/
│   ├── Config/config.php      # configurações (variáveis de ambiente)
│   ├── bootstrap.php          # Container de injeção de dependência
│   ├── routes.php             # tabela de rotas
│   ├── Core/                  # Router, Controller base, View, Container, Database, Config, helpers
│   ├── Controllers/           # Home, Produto, Cliente, Carrinho, Relatorio
│   ├── Models/                # Cliente, Produto, Venda, ItemVenda
│   ├── Repositories/          # ClienteRepository, ProdutoRepository, VendaRepository
│   ├── Services/              # CarrinhoService, VendaService, UploadService
│   ├── Patterns/              # Strategy, Factory, Observer
│   └── Views/                 # layout, produtos, clientes, carrinho, relatorios, errors
├── database/
│   ├── schema.sql             # cria o banco e as tabelas
│   └── seed.sql               # dados de exemplo (opcional)
└── storage/                   # logs (vendas.log)
```

Responsabilidade de cada camada:

| Camada | Pode | Não pode |
|---|---|---|
| **Model** | Representar dados, validar regras do domínio | Conhecer HTML ou `$_POST` de forma direta além de `fromInput` |
| **View** | Exibir dados (escapando com `e()`) | Acessar banco ou conter regra de negócio |
| **Controller** | Orquestrar: ler entrada, chamar Service/Repository, escolher View/redirect | Escrever SQL |

---

## 3. Design Patterns adotados

| # | Pattern | Onde está | Por que foi usado |
|---|---|---|---|
| 1 | **Front Controller** | `public/index.php` + `app/Core/Router.php` | Um único ponto de entrada trata roteamento, sessão, autoload e erros em um lugar só. |
| 2 | **Singleton** | `app/Core/Database.php` | Garante **uma única conexão PDO** por requisição, reutilizada por todos os repositórios (construtor e `__clone` privados). |
| 3 | **Repository** | `app/Repositories/*` | Isola todo o SQL. Controllers e Services trabalham com objetos (`Cliente`, `Venda`), sem saber como são gravados. |
| 4 | **Strategy** | `app/Patterns/Strategy/*` (`DescontoStrategy`, `SemDesconto`, `DescontoPercentual`, `DescontoValorFixo`) | O cálculo do desconto da venda é uma família de algoritmos intercambiáveis; novos tipos entram sem alterar o `VendaService`. |
| 5 | **Factory (Simple Factory)** | `app/Patterns/Factory/DescontoFactory.php` | Converte o tipo escolhido na tela (`nenhum`/`percentual`/`valor`) na *Strategy* correta, escondendo as classes concretas. |
| 6 | **Observer** | `app/Patterns/Observer/*` + `VendaService` (Subject) | Ao **finalizar** ou **cancelar** uma venda, os observers são notificados: `EstoqueObserver` baixa/repõe o estoque e `LogObserver` grava a auditoria em `storage/vendas.log`. Novas reações (e-mail, nota fiscal…) são só novos observers. |
| 7 | **Dependency Injection / Container** | `app/Core/Container.php` + `app/bootstrap.php` | As dependências são injetadas pelo construtor (composition root), facilitando troca e testes. |

**Fluxo de "Finalizar venda"** (junta Strategy + Factory + Observer + transação):

```
CarrinhoController::finalizar
  └─ VendaService::finalizar
       ├─ DescontoFactory::criar('percentual', 10)  → DescontoPercentual (Strategy)
       ├─ BEGIN TRANSACTION
       ├─ VendaRepository::create (venda + itens)
       ├─ notify(venda.finalizada)
       │     ├─ EstoqueObserver → baixa estoque (falha se faltar saldo)
       │     └─ LogObserver     → storage/vendas.log
       └─ COMMIT   (qualquer erro → ROLLBACK: nada é gravado pela metade)
```

---

## 4. CRUDs implementados

### 1 — Cadastro de clientes (`/clientes`)
| Operação | Rota | Observação |
|---|---|---|
| **C**reate | `GET /clientes/novo` · `POST /clientes` | Valida nome, **CPF (dígitos verificadores)**, e-mail e telefone; CPF único |
| **R**ead | `GET /clientes?q=` | Lista com busca por nome, CPF ou e-mail |
| **U**pdate | `GET /clientes/{id}/editar` · `POST /clientes/{id}/atualizar` | Mesmas validações |
| **D**elete | `POST /clientes/{id}/excluir` | Vendas do cliente são mantidas como "Consumidor" |

### 2 — Carrinho de vendas (`/carrinho`)
| Operação | Rota | Observação |
|---|---|---|
| **C**reate | `POST /carrinho/adicionar` · `POST /carrinho/finalizar` | Adiciona item (valida estoque); finaliza gerando a **venda** (cliente, pagamento, desconto) |
| **R**ead | `GET /carrinho?q=` | Busca de produtos (nome/código/categoria, como o PDV da aula) + itens + subtotal |
| **U**pdate | `POST /carrinho/{produto}/atualizar` | Altera a quantidade (0 remove) |
| **D**elete | `POST /carrinho/{produto}/remover` · `POST /carrinho/limpar` | Remove um item ou esvazia o carrinho |

### 3 — Relatórios de vendas (`/relatorios`)
| Operação | Rota | Observação |
|---|---|---|
| **R**ead | `GET /relatorios` | Filtros (período, cliente, pagamento, status), KPIs (faturamento, nº de vendas, ticket médio, descontos, canceladas), faturamento por dia e produtos mais vendidos |
| **R**ead | `GET /relatorios/{id}` · `GET /relatorios/exportar` | Detalhe da venda com itens · exportação **CSV** (compatível com Excel) |
| **U**pdate | `POST /relatorios/{id}/atualizar` · `POST /relatorios/{id}/cancelar` | Corrige cliente/pagamento; **cancela** a venda (o estoque volta via Observer) |
| **D**elete | `POST /relatorios/{id}/excluir` | Só vendas **canceladas** podem ser excluídas (preserva o histórico financeiro) |

### Extra — Produtos (`/produtos`)
O CRUD do sistema da aula (cadastro com imagem, edição, exclusão, busca) agora em MVC. Produto que já foi vendido não pode ser excluído.

---

## 5. POP — Procedimento Operacional Padrão de implantação

### 5.1 Pré-requisitos
- PHP **8.1 ou superior** com as extensões `pdo_mysql`, `fileinfo` e `mbstring`
- MySQL 5.7+ ou MariaDB 10.3+
- Git (para clonar) e, em produção, Apache 2.4 (com `mod_rewrite`) ou Nginx

### 5.2 Implantação local (desenvolvimento)

**Passo 1 — Obter o código**
```bash
git clone https://github.com/<seu-usuario>/<seu-repositorio>.git
cd <seu-repositorio>
```

**Passo 2 — Criar o banco e as tabelas**
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql     # opcional: clientes, produtos e vendas de exemplo
```
(No phpMyAdmin/XAMPP: aba **Importar** → escolher `schema.sql` e depois `seed.sql`.)

**Passo 3 — Configurar a conexão** (variáveis de ambiente; os padrões são `127.0.0.1`, banco `pdv`, usuário `root`, senha vazia)
```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=pdv
export DB_USER=root
export DB_PASS=sua_senha
```
> No XAMPP/Apache, defina com `SetEnv DB_PASS "sua_senha"` no VirtualHost/`.htaccess`, ou ajuste os padrões em `app/Config/config.php` **sem commitar a senha**.

**Passo 4 — Dar permissão de escrita** nas pastas de upload e log
```bash
chmod -R 775 public/imagens storage
```

**Passo 5 — Subir o servidor**
```bash
php -S localhost:8000 -t public
```
Acesse **http://localhost:8000**.

**Passo 6 — Validar a implantação (checklist)**
- [ ] `/produtos` lista os produtos (cadastre um com imagem)
- [ ] `/clientes/novo` cadastra um cliente (teste um CPF inválido — deve ser recusado)
- [ ] `/carrinho` → adicione itens, altere a quantidade, finalize a venda com desconto
- [ ] `/relatorios` mostra a venda, os KPIs e o botão **Exportar CSV**
- [ ] Cancelar a venda devolve o estoque (confira em `/produtos`)
- [ ] `storage/vendas.log` registrou os eventos

### 5.3 Implantação em servidor Apache / hospedagem (produção)
1. Envie o projeto e aponte o **DocumentRoot para a pasta `public/`** (nunca para a raiz do projeto, para não expor `app/` e `database/`).
2. Em hospedagem compartilhada (cPanel) onde o domínio aponta para `public_html`: coloque o conteúdo de `public/` em `public_html/` e o restante (`app/`, `storage/`, `database/`) **um nível acima**; ajuste a constante `BASE_PATH` em `public/index.php` para esse caminho.
3. Crie o banco e usuário MySQL no painel, importe `schema.sql` (remova as linhas `CREATE DATABASE`/`USE` se o painel exigir um nome de banco próprio) e defina `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Se o sistema ficar em subpasta (ex.: `/pdv`), defina `BASE_URL=/pdv`.
5. Em produção defina `APP_DEBUG=0` para não exibir mensagens de erro ao usuário.
6. Garanta escrita em `public/imagens` e `storage`.

### 5.4 Solução de problemas
| Sintoma | Causa provável | Correção |
|---|---|---|
| "Erro interno" (500) | Conexão com o banco falhou | Confira `DB_*`, se o MySQL está ativo e se o `schema.sql` foi importado (com `APP_DEBUG=1` o motivo aparece na tela) |
| 404 em todas as rotas no Apache | `mod_rewrite` desativado / `AllowOverride None` | Habilite `mod_rewrite` e `AllowOverride All` |
| "Sessão expirada" (419) ao enviar formulário | Cookie de sessão bloqueado ou token antigo | Recarregue a página e envie novamente |
| Imagem não salva | Sem permissão em `public/imagens` | `chmod -R 775 public/imagens` |
| `could not find driver` | Falta `pdo_mysql` | Ative a extensão no `php.ini` |

---

## 6. Segurança aplicada
- *Prepared statements* (PDO) em todas as consultas, sem concatenar entrada do usuário.
- Escape de saída (`e()`) nas Views e token **CSRF** em todos os `POST`.
- Preços e nomes do carrinho **sempre lidos do banco** (o navegador nunca envia preço).
- Estoque baixado de forma atômica (`UPDATE ... WHERE quantidade >= :q`) dentro de transação.
- Upload restrito a imagens reais, até 2 MB, com nome aleatório.
- Credenciais fora do código (variáveis de ambiente).

> Observação: o sistema não possui tela de login/perfis de usuário, pois isso não faz parte do escopo pedido. Para uso real em produção, acrescente autenticação.

---

## 7. Publicando no GitHub
```bash
cd pdv
git init
git add .
git commit -m "PDV em MVC com design patterns, CRUDs de clientes, carrinho e relatórios"
git branch -M main
git remote add origin https://github.com/<seu-usuario>/<seu-repositorio>.git
git push -u origin main
```
Envie o link do repositório como entrega.
