# Análise técnica do sistema de controle de extintores

## Visão geral
O projeto é uma aplicação web em **PHP procedural** com frontend em HTML/CSS/JS (Bootstrap 4 e Chart.js via CDN), organizada principalmente no diretório `public/`.

- Entrada principal com sessão e roteamento por perfil: `public/index.php`.
- Camada de autenticação utilitária: `public/includes/auth.php`.
- Conexão com banco MySQL: `config/db_conexao.php`.
- Módulos de operação: inspeção, manutenção, histórico, auditoria, exportação e dashboards.

## Arquitetura observada

### Backend
- Não há framework; cada página combina regra de negócio, acesso a dados e renderização.
- Acesso a dados via `mysqli` com uso misto de queries diretas e prepared statements.
- Controle de sessão central por `$_SESSION['user_id']` e `$_SESSION['user_level']`.

### Frontend
- Interface baseada em Bootstrap 4, com scripts CDN.
- Dashboard (`public/dashboard.php`) consome dados agregados do banco e renderiza gráficos em Chart.js.

### Banco de dados
- Script de estrutura e dados disponível em `eniltonbd.sql`.
- Conexão central em `config/db_conexao.php`.

## Pontos fortes
- Organização funcional por páginas facilita manutenção incremental.
- Há tentativa de proteção de sessão (cookies `httponly`, `secure`, `use_only_cookies`) em `public/index.php`.
- Existem recursos operacionais importantes (auditoria, exportações, históricos).

## Riscos e fragilidades prioritárias

1. **Credenciais sensíveis versionadas em repositório**
   - `config/db_conexao.php` contém host, usuário e senha em texto plano.
   - Impacto: alto (comprometimento de banco/ambiente).

2. **Inconsistência de modelo de usuários/tabelas**
   - `public/index.php` consulta `usuarios`.
   - `public/includes/auth.php` autentica em `users`.
   - Impacto: médio/alto (falhas funcionais de login/permissão, difícil rastreabilidade).

3. **Acoplamento alto (página = controller + service + view)**
   - Dificulta testes automatizados, reaproveitamento e governança de regras.

4. **Configuração de logs incompleta**
   - `error_log` aponta para `/path/to/error.log` (placeholder), o que pode causar perda de rastreabilidade em produção.

5. **Hardening parcial de segurança web**
   - Não foi observado mecanismo centralizado de CSRF, validação padronizada de entrada e política consistente de escaping em toda a aplicação.

## Recomendações objetivas

### Curto prazo (1–2 sprints)
1. **Remover segredos do código** e migrar para variáveis de ambiente (`.env` fora do versionamento) + rotação imediata de senha do banco.
2. **Padronizar entidade de usuário** (`users` ou `usuarios`) e atualizar autenticação/consultas para um único contrato.
3. Criar **bootstrap comum** para sessão, conexão, headers de segurança e tratamento de erro.
4. Ajustar `error_log` para caminho real por ambiente.

### Médio prazo
1. Introduzir camada mínima de serviços/repositórios para reduzir duplicação.
2. Adicionar validação e sanitização centralizadas de inputs.
3. Implementar testes de fumaça para fluxos críticos: login, cadastro, inspeção, manutenção e exportação.

## Conclusão
O sistema atende ao objetivo operacional de controle de extintores, porém possui riscos técnicos e de segurança que merecem correção prioritária antes de crescimento de uso. O maior risco atual é o gerenciamento de credenciais e a inconsistência do domínio de usuários.

### Bibliotecas de Terceiros
- A biblioteca `fpdf` (`public/cod/fpdf.php`) possui código legado de compatibilidade (ex: `// Fix parameter order` na função `Output`). Esse código deve ser mantido como está e não modificado, para preservar a retrocompatibilidade esperada de versões antigas e novas dessa biblioteca.
