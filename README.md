# Spes | Gratia Plena – E-commerce de terços católicos

Atividade Final do 4º Bimestre – Aplicação web de comércio eletrônico com fluxo completo de venda.

**Equipe:** Larah Landim

## Identidade visual
Marca **Spes | Gratia Plena** ("Esperança | Cheia de Graça"), com estilo *clean* inspirado nos títulos marianos e nos santos de devoção.

## Tecnologias
- HTML5, CSS3, Bootstrap 5.3 e JavaScript
- PHP 8 (Apache/XAMPP)
- MySQL/MariaDB com a extensão **PDO**

## Contas de teste
| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@spes.com | admin123 |
| Cliente | cliente@spes.com | cliente123 |


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
