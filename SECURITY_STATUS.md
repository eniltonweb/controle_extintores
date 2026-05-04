# Status de segurança do sistema

## Resumo executivo
A segurança do sistema está em **nível básico/intermediário**, com algumas melhorias já aplicadas, mas ainda com lacunas relevantes para ambiente de produção.

## O que está melhor
1. Credenciais de banco não estão mais hardcoded no código principal de conexão.
2. Há uso de prepared statement em pontos críticos.
3. Sessão possui flags de endurecimento (`httponly`, `secure`, `use_only_cookies`).
4. Logging de erro deixou de usar placeholder.

## Riscos ainda presentes
1. **Controle de sessão incompleto**
   - Falta regenerar ID de sessão após login (`session_regenerate_id(true)`).
   - Risco: session fixation.

2. **Ausência de proteção CSRF padronizada**
   - Formulários sensíveis não têm token global validado no backend.
   - Risco: execução de ações não autorizadas via navegador autenticado.

3. **Política de autorização distribuída**
   - Regras de permissão não parecem centralizadas em todos os endpoints.
   - Risco: bypass por acesso direto a páginas/rotas.

4. **Validação/sanitização inconsistente de entrada**
   - Há uso de `htmlspecialchars` em saída específica, mas não padronização completa de entrada.
   - Risco: XSS/entrada maliciosa em fluxos não cobertos.

5. **Dependência de configuração de ambiente**
   - Mesmo com variáveis de ambiente, sem gestão segura de secrets e rotação, ainda há risco operacional.

## Classificação atual (estimada)
- **Confidencialidade:** média
- **Integridade:** média-baixa
- **Disponibilidade:** média
- **Maturidade geral:** **5/10** para produção crítica

## Próximas ações prioritárias
1. Implementar `session_regenerate_id(true)` no login e timeout de sessão.
2. Adotar middleware/função única para autorização por perfil em todas as páginas.
3. Implementar CSRF token em formulários de escrita.
4. Criar camada de validação de entrada e escaping de saída por padrão.
5. Configurar gestão de segredo (Vault/Secrets Manager/.env fora de versionamento + rotação periódica).
6. Habilitar logs estruturados e trilha de auditoria para ações críticas.
