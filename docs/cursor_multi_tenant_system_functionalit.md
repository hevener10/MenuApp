# Arquitetura funcional e modelo de dados do sistema multi-tenant em Laravel

## 1. Objetivo do documento

Este documento consolida a visão arquitetural inicial do sistema, separando contexto de negócio, requisitos, decisões técnicas e modelo de dados. O objetivo é servir como base comum para produto, arquitetura, desenvolvimento, QA, operação e futuras migrations em Laravel com MySQL.

## 2. Contexto do produto

O sistema proposto é uma plataforma SaaS multi-tenant para operação de restaurantes, lanchonetes e estabelecimentos similares. Cada tenant representa uma empresa com operação própria, usuários, catálogo, pedidos, financeiro, integrações externas e configuração operacional isolada.

O produto precisa suportar operação presencial e digital, incluindo balcão, mesas, delivery, cardápio online, automação de cozinha, controle de caixa, estoque, fidelização e integrações com canais externos e serviços de apoio.

## 3. Objetivos de negócio

- Permitir que múltiplos estabelecimentos usem a mesma plataforma com isolamento lógico de dados.
- Centralizar a operação de pedidos, caixa, clientes, catálogo, estoque e financeiro em uma única solução.
- Suportar canais presenciais e digitais sem duplicar regras essenciais de negócio.
- Viabilizar crescimento modular por plano, recursos habilitados e integrações opcionais.
- Reduzir retrabalho operacional com automação, auditoria, rastreabilidade e padronização de processos.

## 4. Escopo funcional do sistema

### 4.1 Capacidades incluídas

- Gestão de computadores e pontos de operação em rede local.
- Controle de pedidos nos canais delivery, mesa, balcão e canais externos.
- Controle de caixa, turnos, formas de pagamento e movimentações.
- Gestão financeira com contas a receber, contas a pagar e categorias.
- Controle de fiado e conta corrente do cliente.
- Cadastro de produtos, categorias, tamanhos, adicionais, complementos e imagens.
- Controle de estoque, entradas, ficha técnica e movimentações.
- Gestão de impressoras, setores de impressão e monitor de preparo.
- Gestão de usuários, perfis de permissão e acessos por tenant.
- Cardápio online, fidelidade, cupons, fila de atendimento, comanda mobile e marketing.
- Integrações com iFood, 99Food, Keeta, SMS, Google Maps, Google Drive, Dropbox, Bina, WhatsApp e recursos fiscais.
- Backup em nuvem, auditoria e trilha mínima de integração.

### 4.2 Fora do escopo desta versão do documento

- Definição de interfaces de usuário, wireframes e jornada visual.
- Contratos de API, payloads externos e versionamento de integrações.
- Estratégia detalhada de infraestrutura cloud, custos e topologia de ambientes.
- Regras tributárias completas por estado e particularidades fiscais de cada UF.
- Estratégia detalhada de BI, data warehouse e analytics avançado.

## 5. Stakeholders e atores operacionais

### 5.1 Stakeholders principais

- Dono do estabelecimento ou gestor da operação.
- Equipe de atendimento, caixa, cozinha, entrega e administração.
- Cliente final que consome no local, retira ou recebe em delivery.
- Equipe de produto, arquitetura, desenvolvimento, QA, suporte e operação.
- Parceiros externos de integração, meios de pagamento e emissão fiscal.

### 5.2 Atores do sistema

| Ator | Responsabilidade principal |
|---|---|
| `superadmin` | Administração global da plataforma, planos e suporte operacional avançado |
| `admin` | Configuração completa do tenant, módulos, usuários, catálogo e integrações |
| `manager` | Gestão operacional, financeira e acompanhamento de indicadores |
| `operator` | Operação geral de pedidos e cadastros permitidos |
| `waiter` | Lançamento e acompanhamento de pedidos de mesa e comanda mobile |
| `cashier` | Abertura e fechamento de caixa, recebimentos e conferência |
| `kitchen` | Produção, atualização de status e uso de impressoras ou KDS |
| Cliente final | Consulta cardápio, realiza pedido e acompanha atendimento |
| Canal externo | Origina pedido e envia eventos de integração |

## 6. Jornadas e fluxos críticos

### 6.1 Jornada de pedido presencial

1. Operador ou garçom identifica o canal de atendimento.
2. Sistema cria o pedido com vínculo ao tenant, usuário e ponto operacional.
3. Itens, adicionais e complementos são registrados.
4. Pedido segue para impressão ou KDS conforme categoria e setor.
5. Status evolui até entrega ou cancelamento.
6. Pagamento gera reflexo em caixa, financeiro, fidelidade e auditoria.

### 6.2 Jornada de delivery próprio

1. Cliente é identificado ou cadastrado.
2. Endereço e taxa são definidos pela zona de entrega.
3. Pedido entra em preparação e pode ser agrupado em rota.
4. Entrega atualiza status operacional e financeiro.

### 6.3 Jornada de pedido por integração externa

1. Canal externo envia evento ou pedido.
2. Sistema registra a integração, payload e processamento.
3. Pedido interno é criado com rastreabilidade do identificador externo.
4. Falhas precisam ficar registradas para reprocessamento e suporte.

### 6.4 Jornada de caixa

1. Usuário autorizado abre sessão de caixa.
2. Movimentações de suprimento, sangria, recebimento e pagamento são registradas.
3. Fechamento confronta saldo esperado e saldo real.
4. Divergências precisam ser auditáveis.

### 6.5 Jornada de estoque

1. Insumos entram por lançamento de entrada.
2. Ficha técnica e vendas geram consumo de estoque.
3. Ajustes, perdas e produções precisam manter rastreabilidade.
4. Estoque mínimo deve suportar alertas futuros.

## 7. Requisitos funcionais por domínio

### 7.1 Infraestrutura multi-tenant

- O sistema deve cadastrar tenants, planos e módulos habilitados.
- Cada tenant deve possuir configurações, identidade visual e período de vigência.
- O tenant deve poder ativar ou desativar módulos conforme plano e contratação.

### 7.2 Usuários e permissões

- O sistema deve permitir usuários por tenant com perfis operacionais distintos.
- O acesso deve respeitar grupos de permissão configuráveis.
- O e-mail do usuário deve ser único dentro do tenant.

### 7.3 Catálogo e cardápio

- O sistema deve permitir categorias, produtos, tamanhos, adicionais e complementos.
- Produtos devem poder ser exibidos ou ocultados no cardápio online.
- Produtos podem ou não controlar estoque.

### 7.4 Pedidos

- O sistema deve suportar pedidos de mesa, balcão, delivery próprio, online e externos.
- O pedido deve manter histórico de status.
- Itens devem preservar nome e preço do momento da venda.
- O pedido pode receber desconto, taxa de serviço, frete e cupom.

### 7.5 Caixa e pagamentos

- O sistema deve registrar sessões de caixa e movimentações financeiras operacionais.
- O pedido pode possuir um ou mais pagamentos.
- Métodos de pagamento são configuráveis por tenant.

### 7.6 Clientes, fiado e fidelização

- O sistema deve permitir cadastro de clientes, endereços, saldo de cashback e pontos.
- O tenant pode manter conta corrente de fiado por cliente.
- Cupons e programas de fidelidade devem suportar regras mínimas de elegibilidade.

### 7.7 Estoque e suprimentos

- O sistema deve controlar unidades, itens de estoque, ficha técnica e movimentações.
- O sistema deve permitir entradas de estoque vinculadas a fornecedor.
- Movimentações precisam registrar origem da operação quando aplicável.

### 7.8 Fiscal, integrações e operação auxiliar

- O sistema deve manter configuração fiscal por tenant e por estação quando necessário.
- Integrações devem registrar credenciais, status e última sincronização.
- Logs de webhook e auditoria devem suportar suporte operacional.
- O sistema deve permitir painel de senha, KDS, comanda mobile e marketing por tenant.

## 8. Regras de negócio, exceções e critérios de aceite mínimos

### 8.1 Regras de negócio essenciais

- Todo registro de negócio deve pertencer a um tenant, exceto elementos globais explicitamente administrativos.
- O número do pedido deve ser único por tenant.
- O e-mail do usuário deve ser único por tenant.
- O código do cupom deve ser único por tenant.
- Um cliente pode possuir no máximo uma conta de fiado ativa por tenant.
- O fechamento do caixa deve registrar diferença entre o esperado e o realizado.

### 8.2 Exceções que a aplicação deve tratar

- Falha de integração não pode impedir a operação manual do pedido.
- Pedido vindo de canal externo precisa ser idempotente por identificador externo.
- Cancelamento parcial de pedido deve preservar rastreabilidade de status e pagamentos.
- Ausência temporária de internet deve permitir política de contingência definida pela camada de aplicação.
- Falha de emissão fiscal deve manter o pedido íntegro e registrar motivo da falha.

### 8.3 Critérios de aceite mínimos

- Um usuário de um tenant não pode visualizar nem alterar dados de outro tenant.
- Um pedido completo deve refletir itens, pagamentos, histórico, impressão ou KDS e auditoria.
- Um fechamento de caixa deve ser reproduzível a partir dos lançamentos registrados.
- Um pedido integrado deve poder ser rastreado da origem externa até o pedido interno.
- Um backup deve possuir tenant, status, localização e histórico mínimo de execução.

## 9. Requisitos não funcionais

### 9.1 Segurança e isolamento

- O isolamento entre tenants deve ser aplicado na aplicação, na camada de consulta e nas regras de escrita.
- Credenciais de integração não devem trafegar nem ser armazenadas em texto puro na aplicação final.
- Perfis devem operar com menor privilégio possível.

### 9.2 LGPD e auditoria

- Dados pessoais de clientes e usuários devem possuir base de uso compatível com a operação.
- O sistema deve permitir rastrear alterações sensíveis por usuário e tenant.
- Deve existir política de retenção e descarte a ser detalhada em etapa posterior.

### 9.3 Disponibilidade e contingência

- O sistema deve operar com tolerância a falhas parciais de integração.
- A operação crítica de pedido, caixa e catálogo deve possuir estratégia de contingência.
- O uso em rede local exige política de reconexão e sincronização a ser detalhada na aplicação.

### 9.4 Performance e concorrência

- Consultas operacionais devem ser indexadas por `tenant_id` e chaves de acesso frequente.
- Fluxos de caixa, pedido e estoque exigem controle de concorrência para evitar dupla baixa, dupla cobrança e colisão de numeração.
- Integrações assíncronas devem suportar reprocessamento seguro.

### 9.5 Observabilidade e operação

- Logs de integração, auditoria e eventos de falha devem permitir suporte e troubleshooting.
- A solução deve prever métricas operacionais, fila de erros e monitoramento de jobs.
- Estratégias de backup, restore, RPO e RTO devem ser definidas antes da entrada em produção.

## 10. Premissas, restrições e riscos

### 10.1 Premissas

- Laravel será o framework principal da aplicação.
- MySQL será o banco relacional principal.
- O sistema será ofertado como SaaS multi-tenant com base compartilhada.
- A aplicação poderá usar módulos ativados por tenant conforme plano.

### 10.2 Restrições

- Parte do domínio depende de integrações e requisitos legais externos.
- Há operações com dependência de rede local e de dispositivos físicos.
- Módulos fiscais, TEF e canais externos possuem regras próprias fora do controle da aplicação.

### 10.3 Riscos

- Crescimento do volume por tenant pode pressionar consultas sem boa indexação.
- Integrações externas podem gerar pedidos duplicados ou falhas intermitentes.
- Operações simultâneas de caixa, estoque e pedidos podem causar inconsistências sem política transacional clara.
- Armazenamento inadequado de credenciais e dados pessoais pode gerar risco de segurança e compliance.

## 11. Estratégia de multi-tenancy

### 11.1 Alternativas consideradas

| Estratégia | Vantagens | Desvantagens | Aderência ao cenário |
|---|---|---|---|
| `database por tenant` | Maior isolamento físico e facilidade de restore individual | Custo operacional alto, complexidade de provisionamento e manutenção | Baixa no estágio atual |
| `schema por tenant` | Bom isolamento lógico e separação mais forte que coluna | Maior complexidade de migrations, compatibilidade variável e operação mais custosa | Média |
| `single database com tenant_id` | Simplicidade operacional, menor custo e melhor aderência ao Laravel | Exige disciplina rigorosa de isolamento e indexação | Alta |

### 11.2 Estratégia adotada

A estratégia adotada é **single database com `tenant_id`** em todas as tabelas de negócio. Esta abordagem é a mais aderente ao estágio atual do produto, ao uso de Laravel com Eloquent e à necessidade de evolução rápida do domínio com menor custo operacional.

### 11.3 Justificativas da escolha

- Melhor compatibilidade com `Global Scopes`, policies, observers e filtros automáticos no Laravel.
- Menor custo de operação, backup centralizado e menor complexidade de provisionamento.
- Facilidade para rollout de migrations e módulos novos sem orquestração por banco ou schema.
- Aderente a um cenário inicial com muitos módulos e necessidade de iteração rápida.

### 11.4 Condições para essa estratégia continuar válida

- Uso consistente de `tenant_id` em leitura e escrita.
- Índices compostos por tenant nas consultas críticas.
- Testes automatizados de isolamento.
- Estratégia clara de backup/restore por tenant em nível lógico.

---

## 12. Visão Geral dos Grupos de Tabelas

```
┌─────────────────────────────────────────────────────┐
│  1. Infraestrutura Multi-tenant (plans, modules,     │
│     tenants, tenant_modules)                         │
│  2. Computadores (computers)                         │
│  3. Usuários & Permissões                            │
│  4. Clientes & Endereços                             │
│  5. Produtos, Cardápio, Adicionais, Complementos     │
│  6. Estoque & Fichas Técnicas                        │
│  7. Mesas                                            │
│  8. Caixa & Turnos                                   │
│  9. Pedidos (delivery, mesa, balcão, externo)        │
│ 10. Fiado / Conta Corrente                           │
│ 11. Financeiro (A/R, A/P)                            │
│ 12. Impressoras & Setores                            │
│ 13. Fidelidade & Cupons                              │
│ 14. Integrações (iFood, 99Food, Keeta, SMS, etc.)    │
│ 15. Painel de Senha / Fila                           │
│ 16. Cupom Fiscal (NFC-e / SAT / MF-e)               │
│ 17. Comanda Mobile                                   │
│ 18. Smart Delivery                                   │
│ 19. Monitor de Preparos (KDS)                        │
│ 20. Disparador Inteligente (WhatsApp Marketing)      │
│ 21. TEF / PINPAD                                     │
│ 22. Cardápio Online (MenuDino)                       │
│ 23. Bina (Identificação de Chamadas)                 │
│ 24. Audit Log                                        │
└─────────────────────────────────────────────────────┘
```

---

### SQL Completo com Comentários de Módulo

```sql
-- ============================================================
-- 1. INFRAESTRUTURA MULTI-TENANT
-- ============================================================

CREATE TABLE plans (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    slug          VARCHAR(100) UNIQUE NOT NULL,
    description   TEXT,
    price         DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_computers INT NOT NULL DEFAULT 1,
    is_active     BOOLEAN DEFAULT TRUE,
    features      JSON,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL
);

CREATE TABLE modules (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    slug         VARCHAR(100) UNIQUE NOT NULL,
    description  TEXT,
    is_additional BOOLEAN DEFAULT FALSE,
    price        DECIMAL(10,2) DEFAULT 0,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
);

CREATE TABLE plan_modules (
    plan_id   BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (plan_id, module_id),
    FOREIGN KEY (plan_id)   REFERENCES plans(id)   ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE tenants (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid                 CHAR(36) UNIQUE NOT NULL,
    company_name         VARCHAR(200) NOT NULL,
    trade_name           VARCHAR(200),
    cnpj                 VARCHAR(18),
    cpf                  VARCHAR(14),
    email                VARCHAR(200) UNIQUE NOT NULL,
    phone                VARCHAR(20),
    address_street       VARCHAR(200),
    address_number       VARCHAR(20),
    address_complement   VARCHAR(100),
    address_neighborhood VARCHAR(100),
    address_city         VARCHAR(100),
    address_state        CHAR(2),
    address_zip_code     VARCHAR(10),
    address_latitude     DECIMAL(10,8),
    address_longitude    DECIMAL(11,8),
    plan_id              BIGINT UNSIGNED,
    plan_expires_at      TIMESTAMP NULL,
    is_active            BOOLEAN DEFAULT TRUE,
    logo_path            VARCHAR(500),
    settings             JSON,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL,
    deleted_at           TIMESTAMP NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id)
);

CREATE TABLE tenant_modules (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    module_id    BIGINT UNSIGNED NOT NULL,
    activated_at TIMESTAMP NULL,
    expires_at   TIMESTAMP NULL,
    is_active    BOOLEAN DEFAULT TRUE,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    UNIQUE (tenant_id, module_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id)
);

-- ============================================================
-- 2. COMPUTADORES
-- ============================================================

CREATE TABLE computers (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    mac_address VARCHAR(17),
    ip_address  VARCHAR(45),
    license_key VARCHAR(100),
    is_active   BOOLEAN DEFAULT TRUE,
    last_seen_at TIMESTAMP NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================================
-- 3. USUÁRIOS E PERMISSÕES
-- ============================================================

CREATE TABLE users (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NULL,
    name              VARCHAR(200) NOT NULL,
    email             VARCHAR(200) NOT NULL,
    password          VARCHAR(255) NOT NULL,
    phone             VARCHAR(20),
    role              ENUM('superadmin','admin','manager','operator','waiter','cashier','kitchen') DEFAULT 'operator',
    is_active         BOOLEAN DEFAULT TRUE,
    remember_token    VARCHAR(100),
    email_verified_at TIMESTAMP NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    deleted_at        TIMESTAMP NULL,
    UNIQUE (tenant_id, email),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE permission_groups (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE permissions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module      VARCHAR(100) NOT NULL,
    action      VARCHAR(100) NOT NULL,
    description VARCHAR(200),
    UNIQUE (module, action)
);

CREATE TABLE permission_group_permissions (
    permission_group_id BIGINT UNSIGNED NOT NULL,
    permission_id       BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_group_id, permission_id),
    FOREIGN KEY (permission_group_id) REFERENCES permission_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id)       REFERENCES permissions(id)       ON DELETE CASCADE
);

CREATE TABLE user_permission_groups (
    user_id             BIGINT UNSIGNED NOT NULL,
    permission_group_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, permission_group_id),
    FOREIGN KEY (user_id)             REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (permission_group_id) REFERENCES permission_groups(id) ON DELETE CASCADE
);

-- ============================================================
-- 4. CLIENTES E ENDEREÇOS
-- ============================================================

CREATE TABLE customers (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    name             VARCHAR(200) NOT NULL,
    cpf              VARCHAR(14),
    email            VARCHAR(200),
    phone            VARCHAR(20),
    birth_date       DATE,
    notes            TEXT,
    loyalty_points   INT DEFAULT 0,
    cashback_balance DECIMAL(10,2) DEFAULT 0,
    is_active        BOOLEAN DEFAULT TRUE,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    deleted_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE customer_addresses (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    customer_id  BIGINT UNSIGNED NOT NULL,
    label        VARCHAR(50) DEFAULT 'Casa',
    street       VARCHAR(200) NOT NULL,
    number       VARCHAR(20),
    complement   VARCHAR(100),
    neighborhood VARCHAR(100),
    city         VARCHAR(100) NOT NULL,
    state        CHAR(2) NOT NULL,
    zip_code     VARCHAR(10),
    latitude     DECIMAL(10,8),
    longitude    DECIMAL(11,8),
    is_default   BOOLEAN DEFAULT FALSE,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ============================================================
-- 5. PRODUTOS, CARDÁPIO, ADICIONAIS, COMPLEMENTOS
-- ============================================================

CREATE TABLE product_categories (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    parent_id   BIGINT UNSIGNED NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    image_path  VARCHAR(500),
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    show_online BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

CREATE TABLE products (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    category_id      BIGINT UNSIGNED NOT NULL,
    code             VARCHAR(50),
    name             VARCHAR(200) NOT NULL,
    description      TEXT,
    base_price       DECIMAL(10,2) NOT NULL DEFAULT 0,
    cost_price       DECIMAL(10,2) DEFAULT 0,
    has_sizes        BOOLEAN DEFAULT FALSE,
    has_addons       BOOLEAN DEFAULT FALSE,
    has_complements  BOOLEAN DEFAULT FALSE,
    manages_stock    BOOLEAN DEFAULT FALSE,
    is_active        BOOLEAN DEFAULT TRUE,
    show_online      BOOLEAN DEFAULT TRUE,
    sort_order       INT DEFAULT 0,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    deleted_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)            ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

CREATE TABLE product_images (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_cover   BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id)  ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE product_sizes (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) DEFAULT 0,
    is_active  BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id)  ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE addon_groups (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    min_qty     INT DEFAULT 0,
    max_qty     INT DEFAULT 1,
    is_required BOOLEAN DEFAULT FALSE,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE addons (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      BIGINT UNSIGNED NOT NULL,
    addon_group_id BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(100) NOT NULL,
    price          DECIMAL(10,2) NOT NULL DEFAULT 0,
    cost_price     DECIMAL(10,2) DEFAULT 0,
    is_active      BOOLEAN DEFAULT TRUE,
    sort_order     INT DEFAULT 0,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)      REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (addon_group_id) REFERENCES addon_groups(id) ON DELETE CASCADE
);

CREATE TABLE product_addon_groups (
    product_id     BIGINT UNSIGNED NOT NULL,
    addon_group_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, addon_group_id),
    FOREIGN KEY (product_id)     REFERENCES products(id)     ON DELETE CASCADE,
    FOREIGN KEY (addon_group_id) REFERENCES addon_groups(id) ON DELETE CASCADE
);

CREATE TABLE complement_groups (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE complements (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    complement_group_id BIGINT UNSIGNED NOT NULL,
    name                VARCHAR(100) NOT NULL,
    price               DECIMAL(10,2) DEFAULT 0,
    is_active           BOOLEAN DEFAULT TRUE,
    sort_order          INT DEFAULT 0,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)           REFERENCES tenants(id)           ON DELETE CASCADE,
    FOREIGN KEY (complement_group_id) REFERENCES complement_groups(id) ON DELETE CASCADE
);

CREATE TABLE product_complement_groups (
    product_id          BIGINT UNSIGNED NOT NULL,
    complement_group_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, complement_group_id),
    FOREIGN KEY (product_id)          REFERENCES products(id)          ON DELETE CASCADE,
    FOREIGN KEY (complement_group_id) REFERENCES complement_groups(id) ON DELETE CASCADE
);

-- ============================================================
-- 6. ESTOQUE E FICHAS TÉCNICAS
-- ============================================================

CREATE TABLE stock_units (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    name         VARCHAR(50) NOT NULL,
    abbreviation VARCHAR(10) NOT NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE stock_items (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(200) NOT NULL,
    type        ENUM('product','complement','supply') NOT NULL DEFAULT 'supply',
    product_id  BIGINT UNSIGNED NULL,
    unit_id     BIGINT UNSIGNED NULL,
    current_qty DECIMAL(12,4) DEFAULT 0,
    min_qty     DECIMAL(12,4) DEFAULT 0,
    cost_price  DECIMAL(10,2) DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE SET NULL,
    FOREIGN KEY (unit_id)    REFERENCES stock_units(id) ON DELETE SET NULL
);

CREATE TABLE technical_sheets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    product_size_id BIGINT UNSIGNED NULL,
    stock_item_id   BIGINT UNSIGNED NOT NULL,
    qty             DECIMAL(12,4) NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)       REFERENCES tenants(id)       ON DELETE CASCADE,
    FOREIGN KEY (product_id)      REFERENCES products(id)      ON DELETE CASCADE,
    FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_item_id)   REFERENCES stock_items(id)   ON DELETE CASCADE
);

CREATE TABLE stock_movements (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      BIGINT UNSIGNED NOT NULL,
    stock_item_id  BIGINT UNSIGNED NOT NULL,
    user_id        BIGINT UNSIGNED NULL,
    type           ENUM('entry','exit','adjustment','production','loss') NOT NULL,
    qty            DECIMAL(12,4) NOT NULL,
    unit_cost      DECIMAL(10,2) DEFAULT 0,
    reason         VARCHAR(200),
    reference_type VARCHAR(100),
    reference_id   BIGINT UNSIGNED NULL,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)     REFERENCES tenants(id)     ON DELETE CASCADE,
    FOREIGN KEY (stock_item_id) REFERENCES stock_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE SET NULL
);

CREATE TABLE stock_entries (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id     BIGINT UNSIGNED NOT NULL,
    supplier_id   BIGINT UNSIGNED NULL,
    user_id       BIGINT UNSIGNED NULL,
    document_no   VARCHAR(50),
    entry_date    DATE NOT NULL,
    notes         TEXT,
    total_cost    DECIMAL(12,2) DEFAULT 0,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
);

CREATE TABLE stock_entry_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_entry_id  BIGINT UNSIGNED NOT NULL,
    stock_item_id   BIGINT UNSIGNED NOT NULL,
    qty             DECIMAL(12,4) NOT NULL,
    unit_cost       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (stock_entry_id) REFERENCES stock_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_item_id)  REFERENCES stock_items(id)
);

-- ============================================================
-- 7. MESAS
-- ============================================================

CREATE TABLE table_areas (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE tables (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    area_id   BIGINT UNSIGNED NULL,
    number    VARCHAR(20) NOT NULL,
    name      VARCHAR(100),
    capacity  INT DEFAULT 4,
    status    ENUM('available','occupied','reserved','blocked') DEFAULT 'available',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id)   REFERENCES table_areas(id) ON DELETE SET NULL
);

-- ============================================================
-- 8. CAIXA / TURNO
-- ============================================================

CREATE TABLE payment_methods (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    type       ENUM('cash','credit_card','debit_card','pix','voucher','check','tab','other') NOT NULL DEFAULT 'cash',
    is_active  BOOLEAN DEFAULT TRUE,
    is_online  BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE cash_registers (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    computer_id BIGINT UNSIGNED NULL,
    name        VARCHAR(100) NOT NULL,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE SET NULL
);

CREATE TABLE cash_sessions (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    cash_register_id BIGINT UNSIGNED NOT NULL,
    user_id          BIGINT UNSIGNED NOT NULL,
    opened_at        TIMESTAMP NOT NULL,
    closed_at        TIMESTAMP NULL,
    opening_balance  DECIMAL(10,2) NOT NULL DEFAULT 0,
    closing_balance  DECIMAL(10,2) NULL,
    expected_balance DECIMAL(10,2) NULL,
    difference       DECIMAL(10,2) NULL,
    notes            TEXT,
    status           ENUM('open','closed') DEFAULT 'open',
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)        REFERENCES tenants(id)        ON DELETE CASCADE,
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id),
    FOREIGN KEY (user_id)          REFERENCES users(id)
);

CREATE TABLE cash_movements (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NOT NULL,
    cash_session_id   BIGINT UNSIGNED NOT NULL,
    user_id           BIGINT UNSIGNED NOT NULL,
    type              ENUM('supply','withdrawal','payment_in','payment_out') NOT NULL,
    payment_method_id BIGINT UNSIGNED NULL,
    amount            DECIMAL(10,2) NOT NULL,
    description       VARCHAR(200),
    reference_type    VARCHAR(100),
    reference_id      BIGINT UNSIGNED NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)        REFERENCES tenants(id)        ON DELETE CASCADE,
    FOREIGN KEY (cash_session_id)  REFERENCES cash_sessions(id),
    FOREIGN KEY (user_id)          REFERENCES users(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);

CREATE TABLE opening_hours (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,   -- 0=Dom, 1=Seg ... 6=Sab
    opens_at   TIME NOT NULL,
    closes_at  TIME NOT NULL,
    is_closed  BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================================
-- 9. PEDIDOS (delivery, mesa, balcão, externos)
-- ============================================================

CREATE TABLE orders (
    id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id             BIGINT UNSIGNED NOT NULL,
    order_number          VARCHAR(20) NOT NULL,
    channel               ENUM('delivery','table','counter','ifood','99food','keeta','online') NOT NULL,
    status                ENUM('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled','partially_cancelled') NOT NULL DEFAULT 'pending',
    customer_id           BIGINT UNSIGNED NULL,
    customer_name         VARCHAR(200),
    customer_phone        VARCHAR(20),
    table_id              BIGINT UNSIGNED NULL,
    cash_session_id       BIGINT UNSIGNED NULL,
    user_id               BIGINT UNSIGNED NULL,
    computer_id           BIGINT UNSIGNED NULL,
    subtotal              DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount       DECIMAL(10,2) DEFAULT 0,
    service_fee           DECIMAL(10,2) DEFAULT 0,
    delivery_fee          DECIMAL(10,2) DEFAULT 0,
    total                 DECIMAL(10,2) NOT NULL DEFAULT 0,
    coupon_id             BIGINT UNSIGNED NULL,
    coupon_discount       DECIMAL(10,2) DEFAULT 0,
    delivery_address_id   BIGINT UNSIGNED NULL,
    delivery_address_text TEXT,
    notes                 TEXT,
    internal_notes        TEXT,
    scheduled_at          TIMESTAMP NULL,
    confirmed_at          TIMESTAMP NULL,
    preparing_at          TIMESTAMP NULL,
    ready_at              TIMESTAMP NULL,
    delivered_at          TIMESTAMP NULL,
    cancelled_at          TIMESTAMP NULL,
    external_order_id     VARCHAR(100),
    external_data         JSON,
    created_at            TIMESTAMP NULL,
    updated_at            TIMESTAMP NULL,
    deleted_at            TIMESTAMP NULL,
    UNIQUE (tenant_id, order_number),
    FOREIGN KEY (tenant_id)           REFERENCES tenants(id)           ON DELETE CASCADE,
    FOREIGN KEY (customer_id)         REFERENCES customers(id)         ON DELETE SET NULL,
    FOREIGN KEY (table_id)            REFERENCES tables(id)            ON DELETE SET NULL,
    FOREIGN KEY (cash_session_id)     REFERENCES cash_sessions(id)     ON DELETE SET NULL,
    FOREIGN KEY (user_id)             REFERENCES users(id)             ON DELETE SET NULL,
    FOREIGN KEY (computer_id)         REFERENCES computers(id)         ON DELETE SET NULL,
    FOREIGN KEY (delivery_address_id) REFERENCES customer_addresses(id) ON DELETE SET NULL
);

CREATE TABLE order_status_history (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    order_id   BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NULL,
    from_status VARCHAR(50),
    to_status  VARCHAR(50) NOT NULL,
    notes      TEXT,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)  REFERENCES orders(id)  ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE SET NULL
);

CREATE TABLE order_items (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NOT NULL,
    order_id          BIGINT UNSIGNED NOT NULL,
    product_id        BIGINT UNSIGNED NULL,
    product_size_id   BIGINT UNSIGNED NULL,
    product_name      VARCHAR(200) NOT NULL,
    product_size_name VARCHAR(100),
    unit_price        DECIMAL(10,2) NOT NULL,
    qty               DECIMAL(10,3) NOT NULL DEFAULT 1,
    discount          DECIMAL(10,2) DEFAULT 0,
    subtotal          DECIMAL(10,2) NOT NULL,
    notes             TEXT,
    status            ENUM('pending','preparing','ready','delivered','cancelled') DEFAULT 'pending',
    printed_at        TIMESTAMP NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)       REFERENCES tenants(id)       ON DELETE CASCADE,
    FOREIGN KEY (order_id)        REFERENCES orders(id)        ON DELETE CASCADE,
    FOREIGN KEY (product_id)      REFERENCES products(id)      ON DELETE SET NULL,
    FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE SET NULL
);

CREATE TABLE order_item_addons (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL,
    addon_id      BIGINT UNSIGNED NULL,
    name          VARCHAR(100) NOT NULL,
    price         DECIMAL(10,2) NOT NULL DEFAULT 0,
    qty           INT DEFAULT 1,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (addon_id)      REFERENCES addons(id)      ON DELETE SET NULL
);

CREATE TABLE order_item_complements (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_item_id BIGINT UNSIGNED NOT NULL,
    complement_id BIGINT UNSIGNED NULL,
    name          VARCHAR(100) NOT NULL,
    price         DECIMAL(10,2) DEFAULT 0,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (complement_id) REFERENCES complements(id) ON DELETE SET NULL
);

CREATE TABLE order_payments (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    order_id            BIGINT UNSIGNED NOT NULL,
    payment_method_id   BIGINT UNSIGNED NULL,
    payment_method_name VARCHAR(100) NOT NULL,
    amount              DECIMAL(10,2) NOT NULL,
    change_amount       DECIMAL(10,2) DEFAULT 0,
    status              ENUM('pending','authorized','confirmed','refunded','cancelled') DEFAULT 'pending',
    transaction_id      VARCHAR(200),
    paid_at             TIMESTAMP NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)         REFERENCES tenants(id)        ON DELETE CASCADE,
    FOREIGN KEY (order_id)          REFERENCES orders(id)         ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);

CREATE TABLE delivery_zones (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    name         VARCHAR(100) NOT NULL,
    type         ENUM('neighborhood','radius','polygon') DEFAULT 'neighborhood',
    fee          DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order    DECIMAL(10,2) DEFAULT 0,
    estimated_minutes INT DEFAULT 45,
    is_active    BOOLEAN DEFAULT TRUE,
    geo_data     JSON,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================================
-- 10. FIADO / CONTA CORRENTE DO CLIENTE
-- ============================================================

CREATE TABLE customer_tabs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    customer_id  BIGINT UNSIGNED NOT NULL,
    balance      DECIMAL(10,2) DEFAULT 0,
    credit_limit DECIMAL(10,2) DEFAULT 0,
    is_active    BOOLEAN DEFAULT TRUE,
    notes        TEXT,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    UNIQUE (tenant_id, customer_id),
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE customer_tab_movements (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    customer_tab_id BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NULL,
    type            ENUM('debit','credit','payment') NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    balance_after   DECIMAL(10,2) NOT NULL,
    description     VARCHAR(200),
    order_id        BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)       REFERENCES tenants(id)       ON DELETE CASCADE,
    FOREIGN KEY (customer_tab_id) REFERENCES customer_tabs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)         REFERENCES users(id)         ON DELETE SET NULL,
    FOREIGN KEY (order_id)        REFERENCES orders(id)        ON DELETE SET NULL
);

-- ============================================================
-- 11. FINANCEIRO
-- ============================================================

CREATE TABLE financial_categories (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    parent_id  BIGINT UNSIGNED NULL,
    name       VARCHAR(100) NOT NULL,
    type       ENUM('income','expense') NOT NULL,
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES financial_categories(id) ON DELETE SET NULL
);

CREATE TABLE accounts_receivable (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NOT NULL,
    description       VARCHAR(200) NOT NULL,
    customer_id       BIGINT UNSIGNED NULL,
    category_id       BIGINT UNSIGNED NULL,
    amount            DECIMAL(10,2) NOT NULL,
    amount_paid       DECIMAL(10,2) DEFAULT 0,
    due_date          DATE NOT NULL,
    paid_at           DATE NULL,
    status            ENUM('pending','partial','paid','overdue','cancelled') DEFAULT 'pending',
    payment_method_id BIGINT UNSIGNED NULL,
    order_id          BIGINT UNSIGNED NULL,
    notes             TEXT,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)         REFERENCES tenants(id)             ON DELETE CASCADE,
    FOREIGN KEY (customer_id)       REFERENCES customers(id)           ON DELETE SET NULL,
    FOREIGN KEY (category_id)       REFERENCES financial_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)     ON DELETE SET NULL,
    FOREIGN KEY (order_id)          REFERENCES orders(id)              ON DELETE SET NULL
);

CREATE TABLE accounts_payable (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NOT NULL,
    description       VARCHAR(200) NOT NULL,
    supplier_name     VARCHAR(200),
    category_id       BIGINT UNSIGNED NULL,
    amount            DECIMAL(10,2) NOT NULL,
    amount_paid       DECIMAL(10,2) DEFAULT 0,
    due_date          DATE NOT NULL,
    paid_at           DATE NULL,
    status            ENUM('pending','partial','paid','overdue','cancelled') DEFAULT 'pending',
    payment_method_id BIGINT UNSIGNED NULL,
    recurrence        ENUM('none','daily','weekly','monthly','yearly') DEFAULT 'none',
    parent_id         BIGINT UNSIGNED NULL,
    notes             TEXT,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)         REFERENCES tenants(id)             ON DELETE CASCADE,
    FOREIGN KEY (category_id)       REFERENCES financial_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)     ON DELETE SET NULL,
    FOREIGN KEY (parent_id)         REFERENCES accounts_payable(id)    ON DELETE SET NULL
);

CREATE TABLE suppliers (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    name         VARCHAR(200) NOT NULL,
    cnpj         VARCHAR(18),
    email        VARCHAR(200),
    phone        VARCHAR(20),
    payment_term INT DEFAULT 30,
    notes        TEXT,
    is_active    BOOLEAN DEFAULT TRUE,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================================
-- 12. IMPRESSORAS E SETORES
-- ============================================================

CREATE TABLE print_sectors (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id  BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    type       ENUM('kitchen','bar','counter','cashier','delivery') NOT NULL DEFAULT 'kitchen',
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE printers (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    computer_id      BIGINT UNSIGNED NULL,
    print_sector_id  BIGINT UNSIGNED NULL,
    name             VARCHAR(100) NOT NULL,
    connection_type  ENUM('usb','network','bluetooth','windows') DEFAULT 'usb',
    ip_address       VARCHAR(45),
    port             INT,
    device_name      VARCHAR(200),
    paper_width      INT DEFAULT 80,
    is_active        BOOLEAN DEFAULT TRUE,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)       REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (computer_id)     REFERENCES computers(id)    ON DELETE SET NULL,
    FOREIGN KEY (print_sector_id) REFERENCES print_sectors(id) ON DELETE SET NULL
);

CREATE TABLE product_category_print_sectors (
    product_category_id BIGINT UNSIGNED NOT NULL,
    print_sector_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (product_category_id, print_sector_id),
    FOREIGN KEY (product_category_id) REFERENCES product_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (print_sector_id)     REFERENCES print_sectors(id)      ON DELETE CASCADE
);

-- ============================================================
-- 13. FIDELIDADE E CUPONS DE DESCONTO
-- ============================================================

CREATE TABLE loyalty_programs (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    name                VARCHAR(100) NOT NULL,
    type                ENUM('points','cashback') NOT NULL DEFAULT 'points',
    is_active           BOOLEAN DEFAULT TRUE,
    points_per_real     DECIMAL(10,4) DEFAULT 1,
    cashback_percent    DECIMAL(5,2) DEFAULT 0,
    min_order_amount    DECIMAL(10,2) DEFAULT 0,
    points_expiry_days  INT DEFAULT 365,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    UNIQUE (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE loyalty_transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    customer_id     BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NULL,
    type            ENUM('earn','redeem','expire','adjust') NOT NULL,
    points          INT DEFAULT 0,
    cashback_amount DECIMAL(10,2) DEFAULT 0,
    expires_at      DATE NULL,
    description     VARCHAR(200),
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)    REFERENCES orders(id)    ON DELETE SET NULL
);

CREATE TABLE coupons (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               BIGINT UNSIGNED NOT NULL,
    code                    VARCHAR(50) NOT NULL,
    description             VARCHAR(200),
    type                    ENUM('fixed','percent') NOT NULL DEFAULT 'percent',
    value                   DECIMAL(10,2) NOT NULL,
    min_order_amount        DECIMAL(10,2) DEFAULT 0,
    max_discount            DECIMAL(10,2) NULL,
    max_uses                INT NULL,
    used_count              INT DEFAULT 0,
    max_uses_per_customer   INT DEFAULT 1,
    applicable_to           ENUM('all','category','product') DEFAULT 'all',
    valid_from              DATE NULL,
    valid_until             DATE NULL,
    is_active               BOOLEAN DEFAULT TRUE,
    created_at              TIMESTAMP NULL,
    updated_at              TIMESTAMP NULL,
    UNIQUE (tenant_id, code),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE coupon_products (
    coupon_id  BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (coupon_id, product_id),
    FOREIGN KEY (coupon_id)  REFERENCES coupons(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE CASCADE
);

CREATE TABLE coupon_categories (
    coupon_id   BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (coupon_id, category_id),
    FOREIGN KEY (coupon_id)   REFERENCES coupons(id)            ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
);

CREATE TABLE coupon_usages (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id        BIGINT UNSIGNED NOT NULL,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    customer_id      BIGINT UNSIGNED NULL,
    order_id         BIGINT UNSIGNED NULL,
    discount_applied DECIMAL(10,2) NOT NULL,
    used_at          TIMESTAMP NULL,
    FOREIGN KEY (coupon_id)   REFERENCES coupons(id)    ON DELETE CASCADE,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)    ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id)  ON DELETE SET NULL,
    FOREIGN KEY (order_id)    REFERENCES orders(id)     ON DELETE SET NULL
);

-- ============================================================
-- 14. INTEGRAÇÕES EXTERNAS
-- ============================================================

CREATE TABLE integrations (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    type         ENUM('ifood','99food','keeta','sms','google_maps','google_drive','dropbox','bina','whatsapp','menudino') NOT NULL,
    name         VARCHAR(100) NOT NULL,
    is_active    BOOLEAN DEFAULT FALSE,
    credentials  JSON,
    settings     JSON,
    last_sync_at TIMESTAMP NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    UNIQUE (tenant_id, type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE webhook_logs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NULL,
    integration_id BIGINT UNSIGNED NULL,
    event        VARCHAR(100),
    payload      JSON,
    status       ENUM('received','processed','failed') DEFAULT 'received',
    error        TEXT,
    processed_at TIMESTAMP NULL,
    created_at   TIMESTAMP NULL,
    INDEX idx_webhook_tenant_status (tenant_id, status),
    FOREIGN KEY (integration_id) REFERENCES integrations(id) ON DELETE SET NULL
);

-- ============================================================
-- 15. PAINEL DE SENHA / FILA
-- ============================================================

CREATE TABLE queue_panels (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      BIGINT UNSIGNED NOT NULL,
    computer_id    BIGINT UNSIGNED NULL,
    name           VARCHAR(100) NOT NULL,
    current_number INT DEFAULT 0,
    prefix         VARCHAR(5),
    is_active      BOOLEAN DEFAULT TRUE,
    settings       JSON,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE SET NULL
);

CREATE TABLE queue_tickets (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id     BIGINT UNSIGNED NOT NULL,
    panel_id      BIGINT UNSIGNED NOT NULL,
    order_id      BIGINT UNSIGNED NULL,
    ticket_number INT NOT NULL,
    customer_name VARCHAR(200),
    status        ENUM('waiting','called','served','cancelled') DEFAULT 'waiting',
    called_at     TIMESTAMP NULL,
    served_at     TIMESTAMP NULL,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)    ON DELETE CASCADE,
    FOREIGN KEY (panel_id)  REFERENCES queue_panels(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)  REFERENCES orders(id)     ON DELETE SET NULL
);

-- ============================================================
-- 16. CUPOM FISCAL (NFC-e / SAT / MF-e)
-- ============================================================

CREATE TABLE fiscal_documents (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NOT NULL,
    type            ENUM('nfce','nfe','sat','mfe','cupom') NOT NULL,
    status          ENUM('pending','authorized','cancelled','error') NOT NULL DEFAULT 'pending',
    document_number VARCHAR(50),
    series          VARCHAR(5),
    access_key      VARCHAR(60),
    xml_path        VARCHAR(500),
    pdf_path        VARCHAR(500),
    issued_at       TIMESTAMP NULL,
    cancelled_at    TIMESTAMP NULL,
    cancel_reason   TEXT,
    error_message   TEXT,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)  REFERENCES orders(id)  ON DELETE CASCADE
);

CREATE TABLE fiscal_settings (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    computer_id         BIGINT UNSIGNED NULL,
    document_type       ENUM('nfce','nfe','sat','mfe') NOT NULL,
    environment         ENUM('homologation','production') DEFAULT 'homologation',
    series              SMALLINT DEFAULT 1,
    next_number         INT DEFAULT 1,
    csc_token           VARCHAR(36),
    csc_id              VARCHAR(10),
    certificate_path    VARCHAR(500),
    certificate_expires DATE,
    tax_regime          ENUM('simples','lucro_presumido','lucro_real') DEFAULT 'simples',
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (computer_id) REFERENCES computers(id) ON DELETE SET NULL
);

-- ============================================================
-- 17. COMANDA MOBILE
-- ============================================================

CREATE TABLE mobile_sessions (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    user_id          BIGINT UNSIGNED NOT NULL,
    device_token     VARCHAR(200),
    device_type      ENUM('tablet','phone') DEFAULT 'tablet',
    is_active        BOOLEAN DEFAULT TRUE,
    last_activity_at TIMESTAMP NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- ============================================================
-- 18. SMART DELIVERY (Logística)
-- ============================================================

CREATE TABLE delivery_routes (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NOT NULL,
    user_id      BIGINT UNSIGNED NULL,
    name         VARCHAR(100),
    status       ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
    started_at   TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE SET NULL
);

CREATE TABLE delivery_route_orders (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    route_id     BIGINT UNSIGNED NOT NULL,
    order_id     BIGINT UNSIGNED NOT NULL,
    sort_order   INT DEFAULT 0,
    status       ENUM('pending','delivered','failed') DEFAULT 'pending',
    delivered_at TIMESTAMP NULL,
    notes        TEXT,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL,
    FOREIGN KEY (route_id) REFERENCES delivery_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id)          ON DELETE CASCADE
);

-- ============================================================
-- 19. MONITOR DE PREPAROS (KDS - Kitchen Display System)
-- ============================================================

CREATE TABLE kitchen_displays (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    computer_id     BIGINT UNSIGNED NULL,
    print_sector_id BIGINT UNSIGNED NULL,
    name            VARCHAR(100) NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    settings        JSON,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)       REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (computer_id)     REFERENCES computers(id)    ON DELETE SET NULL,
    FOREIGN KEY (print_sector_id) REFERENCES print_sectors(id) ON DELETE SET NULL
);

-- ============================================================
-- 20. DISPARADOR INTELIGENTE (WhatsApp Marketing)
-- ============================================================

CREATE TABLE marketing_campaigns (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    integration_id  BIGINT UNSIGNED NULL,
    name            VARCHAR(200) NOT NULL,
    channel         ENUM('whatsapp','sms','email') NOT NULL DEFAULT 'whatsapp',
    message_template TEXT NOT NULL,
    target_type     ENUM('all','segment','custom') DEFAULT 'all',
    target_filters  JSON,
    status          ENUM('draft','scheduled','running','completed','cancelled') DEFAULT 'draft',
    scheduled_at    TIMESTAMP NULL,
    started_at      TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    total_sent      INT DEFAULT 0,
    total_failed    INT DEFAULT 0,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)      REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES integrations(id) ON DELETE SET NULL
);

CREATE TABLE campaign_contacts (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    phone       VARCHAR(20),
    status      ENUM('pending','sent','delivered','read','failed') DEFAULT 'pending',
    sent_at     TIMESTAMP NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id)           ON DELETE SET NULL
);

-- ============================================================
-- 21. TEF / PINPAD
-- ============================================================

CREATE TABLE tef_transactions (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id        BIGINT UNSIGNED NOT NULL,
    order_payment_id BIGINT UNSIGNED NULL,
    nsu              VARCHAR(50),
    authorization_code VARCHAR(50),
    card_brand       VARCHAR(50),
    card_last_digits CHAR(4),
    transaction_type ENUM('credit','debit') NOT NULL,
    installments     INT DEFAULT 1,
    amount           DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','approved','denied','cancelled','error') DEFAULT 'pending',
    response_data    JSON,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)        REFERENCES tenants(id)        ON DELETE CASCADE,
    FOREIGN KEY (order_payment_id) REFERENCES order_payments(id) ON DELETE SET NULL
);

-- ============================================================
-- 22. CARDÁPIO ONLINE (MenuDino)
-- ============================================================

CREATE TABLE online_menu_settings (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      BIGINT UNSIGNED NOT NULL,
    slug           VARCHAR(100) UNIQUE NOT NULL,
    is_active      BOOLEAN DEFAULT FALSE,
    allow_ordering BOOLEAN DEFAULT FALSE,
    banner_path    VARCHAR(500),
    description    TEXT,
    settings       JSON,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    UNIQUE (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================================
-- 23. BINA (Identificação de Chamadas)
-- ============================================================

CREATE TABLE bina_calls (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    phone       VARCHAR(20) NOT NULL,
    called_at   TIMESTAMP NOT NULL,
    answered    BOOLEAN DEFAULT FALSE,
    notes       TEXT,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- ============================================================
-- 24. BACKUP NA NUVEM
-- ============================================================

CREATE TABLE backups (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id      BIGINT UNSIGNED NOT NULL,
    integration_id BIGINT UNSIGNED NULL,
    filename       VARCHAR(500) NOT NULL,
    size           BIGINT,
    status         ENUM('pending','running','completed','failed') DEFAULT 'pending',
    storage_path   VARCHAR(1000),
    type           ENUM('manual','automatic') DEFAULT 'manual',
    notes          TEXT,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    FOREIGN KEY (tenant_id)      REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (integration_id) REFERENCES integrations(id) ON DELETE SET NULL
);

-- ============================================================
-- 25. AUDIT LOG
-- ============================================================

CREATE TABLE audit_logs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id    BIGINT UNSIGNED NULL,
    user_id      BIGINT UNSIGNED NULL,
    action       VARCHAR(100) NOT NULL,
    model_type   VARCHAR(100),
    model_id     BIGINT UNSIGNED NULL,
    old_values   JSON,
    new_values   JSON,
    ip_address   VARCHAR(45),
    user_agent   TEXT,
    created_at   TIMESTAMP NULL,
    INDEX idx_audit_tenant_model (tenant_id, model_type, model_id),
    INDEX idx_audit_tenant_user  (tenant_id, user_id)
);
```

---

### Contagem de Tabelas por Módulo

| # | Grupo | Tabelas |
|---|-------|---------|
| 1 | Infraestrutura Multi-tenant | `plans`, `modules`, `plan_modules`, `tenants`, `tenant_modules` |
| 2 | Computadores | `computers` |
| 3 | Usuários e permissões | `users`, `permission_groups`, `permissions`, `permission_group_permissions`, `user_permission_groups` |
| 4 | Clientes e endereços | `customers`, `customer_addresses` |
| 5 | Produtos e cardápio | `product_categories`, `products`, `product_images`, `product_sizes`, `addon_groups`, `addons`, `product_addon_groups`, `complement_groups`, `complements`, `product_complement_groups` |
| 6 | Estoque e fichas técnicas | `stock_units`, `stock_items`, `technical_sheets`, `stock_movements`, `stock_entries`, `stock_entry_items` |
| 7 | Mesas | `table_areas`, `tables` |
| 8 | Caixa e operação comercial | `payment_methods`, `cash_registers`, `cash_sessions`, `cash_movements`, `opening_hours` |
| 9 | Pedidos | `orders`, `order_status_history`, `order_items`, `order_item_addons`, `order_item_complements`, `order_payments`, `delivery_zones` |
| 10 | Fiado | `customer_tabs`, `customer_tab_movements` |
| 11 | Financeiro e fornecedores | `financial_categories`, `accounts_receivable`, `accounts_payable`, `suppliers` |
| 12 | Impressoras e setores | `print_sectors`, `printers`, `product_category_print_sectors` |
| 13 | Fidelidade e cupons | `loyalty_programs`, `loyalty_transactions`, `coupons`, `coupon_products`, `coupon_categories`, `coupon_usages` |
| 14 | Integrações externas | `integrations`, `webhook_logs` |
| 15 | Painel de senha | `queue_panels`, `queue_tickets` |
| 16 | Fiscal | `fiscal_documents`, `fiscal_settings` |
| 17 | Comanda mobile | `mobile_sessions` |
| 18 | Smart delivery | `delivery_routes`, `delivery_route_orders` |
| 19 | KDS | `kitchen_displays` |
| 20 | Marketing | `marketing_campaigns`, `campaign_contacts` |
| 21 | TEF | `tef_transactions` |
| 22 | Cardápio online | `online_menu_settings` |
| 23 | Bina | `bina_calls` |
| 24 | Backup | `backups` |
| 25 | Auditoria | `audit_logs` |

**Total consolidado no SQL atual: 75 tabelas**

---

### Decisões de design e aderência aos princípios SOLID

**Single Responsibility:** cada agregado principal foi separado em tabelas específicas, reduzindo acoplamento entre pedido, pagamento, item, fidelidade, estoque e caixa.

**Open/Closed:** campos JSON controlados, como `settings`, `credentials`, `external_data`, `geo_data`, `target_filters` e `response_data`, permitem evolução controlada sem remodelagem imediata do núcleo transacional.

**Liskov Substitution e Interface Segregation:** movimentos de estoque e caixa usam referências genéricas à origem da operação para acomodar múltiplos casos de negócio sem criar dependências rígidas entre todos os módulos.

**Dependency Inversion:** meios de pagamento, módulos, planos, integrações e setores operacionais são configuráveis por entidade própria e consumidos pelo fluxo transacional por relacionamento, e não por enumerações fechadas na lógica de negócio.

---

### Priorização por fases

#### Fase 1 - MVP operacional

- Infraestrutura multi-tenant, tenants, planos e módulos.
- Usuários, permissões e autenticação por tenant.
- Produtos, categorias, adicionais, complementos e imagens.
- Pedidos de balcão, mesa e delivery próprio.
- Caixa, formas de pagamento e fechamento de turno.
- Clientes, endereços, delivery zones e cardápio online básico.
- Impressoras, setores de impressão e auditoria mínima.

#### Fase 2 - Controle operacional ampliado

- Estoque, ficha técnica, entradas e fornecedores.
- Fiado, contas a receber, contas a pagar e categorias financeiras.
- Fidelidade, cashback e cupons.
- KDS, painel de senha e comanda mobile.

#### Fase 3 - Integrações e escala operacional

- Integrações com iFood, 99Food, Keeta e SMS.
- Webhooks, reprocessamento e monitoramento de falhas.
- Backup em nuvem, smart delivery, marketing e Bina.
- Fiscal avançado e TEF conforme contexto regulatório e regional.

---

### Próximos passos no Laravel

1. Transformar este documento em backlog técnico, separando migrations, seeds, policies, jobs e integrações.
2. Criar as migrations em ordem de dependência de chaves estrangeiras.
3. Implementar isolamento de tenant em models, queries, policies e testes automatizados.
4. Definir estratégia transacional para pedidos, pagamentos, caixa e estoque.
5. Definir contrato de integração, observabilidade e rotina de backup e restore antes da produção.

---

### Revisão de consistência do documento

- O documento agora separa contexto, requisito, restrições, decisão e modelo físico.
- O resumo final foi alinhado ao SQL efetivamente descrito.
- As integrações externas foram consolidadas como capacidade arquitetural, e não apenas lista comercial de recursos.
- A próxima evolução recomendada é detalhar contratos de API, eventos assíncronos e políticas de autorização por caso de uso.

