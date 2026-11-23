# Spes | Gratia Plena – E-commerce de terços católicos

Atividade Final do 4º Bimestre – Aplicação web de comércio eletrônico com fluxo completo de venda.

**Equipe:** Larah Landim (adicione aqui os outros integrantes, se houver)

## Identidade visual
Marca **Spes | Gratia Plena** ("Esperança | Cheia de Graça"), com estilo *clean* inspirado nos títulos marianos e nos santos de devoção.

| Elemento | Escolha |
|---|---|
| Cores | Creme `#F7F1E8`, Marrom `#5B4636`, Dourado `#B08D57`, Rosé `#E9D6CF` |
| Fontes | Cormorant Garamond (títulos), Pinyon Script (destaques), Lato (textos) |
| Logo | `assets/img/logo.png`, `assets/img/logo-horizontal.png` e `assets/img/favicon.png` |

Terços: Nossa Senhora de Lourdes, do Rosário, da Esperança, de Fátima, de Guadalupe e Aparecida; Santa Teresinha, São Bento, São Miguel Arcanjo e São Jorge.

## Tecnologias
- HTML5, CSS3, Bootstrap 5.3 e JavaScript
- PHP 8 (Apache/XAMPP)
- MySQL/MariaDB com a extensão **PDO**

## Como rodar no computador (XAMPP)
1. Copie a pasta do projeto para `C:\xampp\htdocs\loja-tercos`.
2. Abra o **XAMPP Control Panel** e clique em **Start** no **Apache** e no **MySQL**.
3. Acesse `http://localhost/phpmyadmin` → aba **Importar** → escolha `database/schema.sql` → **Importar**.
4. Na pasta do projeto, faça uma cópia do arquivo `.env.example` e renomeie a cópia para `.env`.
   No XAMPP padrão, os valores do exemplo já funcionam (usuário `root`, senha vazia).
5. Acesse `http://localhost/loja-tercos`.

## Contas de teste
| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@spes.com | admin123 |
| Cliente | cliente@spes.com | cliente123 |

## Funcionalidades
**Catálogo:** vitrine com produtos vindos do banco, busca por nome/descrição e filtro por categoria. Página do produto com código, preço unitário, descrição, imagem e indicador de estoque (em estoque / últimas unidades / esgotado).

**Carrinho (`$_SESSION`):** adicionar produto, alterar quantidade (não deixa passar do estoque), remover item, subtotal por item e total geral.

**Checkout:** só para usuários logados (login/cadastro). Simulação de pagamento com PIX, Boleto ou Cartão. Ao finalizar, dentro de uma **transação**:
1. cria o registro do pedido;
2. registra cada item com o preço congelado;
3. dá baixa no estoque de cada produto;
4. limpa o carrinho da sessão.

**Painel administrativo (`/admin`):** só para perfil admin. CRUD de produtos (cadastrar com imagem, listar, editar preço/estoque, ativar/desativar e excluir) e lista de pedidos com itens e valor total. Produtos que já foram vendidos são desativados em vez de excluídos, para manter o histórico.

## Segurança
- **Variáveis de ambiente:** as credenciais do banco ficam no `.env` (lido por `config/env.php`). O `.env` está no `.gitignore`; no repositório fica só o modelo `.env.example`.
- **Prepared Statements (PDO):** nenhuma query concatena dados do usuário (proteção contra SQL Injection).
- **Senhas:** `password_hash()` no cadastro e `password_verify()` no login. Nenhuma senha é salva em texto puro.
- **Controle de acesso no servidor:** `exigirLogin()` e `exigirAdmin()` em `includes/funcoes.php` protegem o checkout e o painel admin.
- **Extras:** proteção CSRF nos formulários, `htmlspecialchars()` contra XSS, `session_regenerate_id()` no login, upload de imagem validado pelo tipo real do arquivo e `.htaccess` bloqueando o acesso a `.env`, `config/`, `includes/` e `database/`.

## Banco de dados (`database/schema.sql`)
| Tabela | O que guarda |
|---|---|
| `usuarios` | clientes e administradores (`perfil`), e-mail **único**, senha criptografada |
| `categorias` | categorias dos terços (Títulos de Nossa Senhora e Santos de Devoção) |
| `produtos` | nome, descrição, preço `DECIMAL(10,2)`, estoque `INT`, imagem, FK para categoria |
| `pedidos` | cliente (FK), data, forma de pagamento e total |
| `itens_pedido` | tabela que resolve o N:N entre pedidos e produtos, com quantidade e preço unitário congelado |

O script cria todas as tabelas com PK e FK e faz uma carga inicial de teste (2 usuários, 2 categorias, 10 produtos e 1 pedido).

## Estrutura de pastas
```
loja-tercos/
├── .env.example        modelo das variáveis do banco
├── .gitignore
├── .htaccess
├── README.md
├── index.php           vitrine (busca + filtro)
├── produto.php         página do produto
├── carrinho.php        carrinho na sessão
├── login.php / cadastro.php / logout.php
├── checkout.php        pagamento simulado + finalização do pedido
├── pedido_confirmado.php
├── admin/              painel administrativo (CRUD + pedidos)
├── config/             env.php (lê o .env) e db.php (conexão PDO)
├── includes/           funções, cabeçalho e rodapé
├── database/schema.sql script do banco
└── assets/             css e imagens dos produtos
```
