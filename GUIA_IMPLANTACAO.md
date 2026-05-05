# Guia de Implantação do Sistema de Extintores

Este documento descreve os requisitos e o passo a passo para colocar o sistema de controle de extintores em funcionamento num servidor de produção.

## 1. Requisitos do Servidor

Para rodar este sistema, o seu servidor precisa ter a seguinte stack básica (LAMP/LEMP ou equivalente):

- **Servidor Web:** Apache ou Nginx.
- **PHP:** Versão recomendada 8.x (testado em 8.3), com as extensões `mysqli` e `session` habilitadas.
- **Banco de Dados:** MySQL (versão 5.7+ ou 8.0+) ou MariaDB equivalente.

## 2. Preparação do Servidor e Document Root

Por questões de segurança, o servidor web não deve apontar diretamente para a raiz do repositório.

- Configure o **Document Root** (no Apache via `VirtualHost` ou no Nginx no bloco `server`) para apontar para o diretório `/public` do projeto.
- Isso impede que arquivos sensíveis (como os arquivos em `/config`, ou `.env`, caso exista) sejam acessados diretamente pelo navegador.
- Certifique-se de que as permissões de leitura/escrita estejam corretas, especialmente se o sistema necessitar de salvar ficheiros. Existe um diretório `logs` onde as falhas de PHP serão gravadas. Certifique-se que o utilizador do servidor web (ex: `www-data`) tem permissão de escrita nesse diretório.

## 3. Configuração do Banco de Dados

1. **Importação:**
   - Acesse o seu gerenciador de banco de dados (ex: phpMyAdmin, DBeaver ou linha de comandos do MySQL).
   - Crie um novo banco de dados (ex: `controle_extintores`).
   - Importe o arquivo `eniltonbd.sql` que se encontra na raiz do projeto. Isso criará toda a estrutura de tabelas e alguns registros padrões essenciais.

2. **Usuários e Senhas iniciais:**
   - O banco `eniltonbd.sql` provavelmente contém utilizadores predefinidos. Tenha atenção às passwords guardadas (normalmente através de `password_hash`).
   - A gestão destes utilizadores encontra-se maioritariamente ligada à tabela `usuarios`.

## 4. Configuração das Variáveis de Ambiente (Conexão)

O sistema foi preparado para ler as credenciais da base de dados através de variáveis de ambiente. Isso garante maior segurança.
O arquivo `config/db_conexao.php` lê as seguintes variáveis:

- `DB_HOST` (Padrão: `localhost`)
- `DB_USER` (Padrão: `root`)
- `DB_PASS` (Padrão: `''`)
- `DB_NAME` (Padrão: `controle_extintores`)

**Como configurar (Exemplo no Apache):**
No arquivo de configuração do seu host virtual, adicione:
```apache
SetEnv DB_HOST "localhost"
SetEnv DB_USER "usuario_banco"
SetEnv DB_PASS "senha_segura"
SetEnv DB_NAME "nome_banco"
```

Se for usar Nginx com PHP-FPM, configure no seu arquivo `www.conf` (pool do php-fpm):
```ini
env[DB_HOST] = "localhost"
env[DB_USER] = "usuario_banco"
env[DB_PASS] = "senha_segura"
env[DB_NAME] = "nome_banco"
```

## 5. Segurança no Servidor (TLS / HTTPS)

**Obrigatório:** Instale um certificado SSL/TLS válido (pode utilizar o Let's Encrypt / Certbot, que é gratuito).

Como o sistema faz uso de sessões, as "flags" de segurança (ex: `cookie_secure`) estão ativadas no código em `public/index.php`. Se o servidor não operar via HTTPS (SSL), os cookies de sessão poderão não ser aceites pelo navegador ou trafegar em modo inseguro, resultando num login corrompido e graves quebras de segurança.

## 6. Fluxos Adicionais

- **Auditoria / Logs:** O diretório `logs` na raiz é fundamental. O código define `ini_set('error_log', __DIR__ . '/../logs/php_errors.log');`. O sistema não falhará imediatamente se faltar, mas eventuais erros de execução seriam perdidos.
- **CSRF e Autorização:** O sistema exige cuidados se for colocado em internet aberta, pois ainda possui pontos de melhoria apontados na documentação de análise do projeto (`PRONTIDAO_SEGURANCA.md`). Mantenha um ambiente isolado (VPN, firewall rígido) se o foco for segurança máxima.
