# Status de segurança do sistema (atualizado)

## Situação atual
Após as últimas correções, a segurança do sistema evoluiu de **básica/intermediária** para **intermediária**.

### Melhorias já implementadas
1. **Segredos de banco removidos do código-fonte principal** e substituídos por variáveis de ambiente (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).
2. **Consistência de autenticação** com uso da tabela `usuarios` no fluxo utilitário.
3. **Hardening de sessão** com flags de cookie (`httponly`, `secure`, `use_only_cookies`) e `session_regenerate_id(true)` após login bem-sucedido.
4. **Proteção CSRF aplicada** em formulários críticos já tratados (`login.php` e `registrar_usuario.php`).
5. **Caminho de log de erro configurado** (sem placeholder).

## Riscos que ainda permanecem
1. **Cobertura CSRF parcial**
   - Nem todos os endpoints de escrita foram validados nesta rodada.

2. **Autorização ainda distribuída em páginas**
   - Falta uma camada central obrigatória para todos os endpoints.

3. **Validação de entrada e escaping ainda não padronizados globalmente**
   - Existem pontos com sanitização, mas não um padrão único em toda aplicação.

4. **Ausência de suíte automatizada de segurança/regressão**
   - Falta teste recorrente para login, autorização e fluxos sensíveis.

## Avaliação objetiva (agora)
- **Confidencialidade:** média
- **Integridade:** média
- **Disponibilidade:** média
- **Maturidade geral:** **6.5/10** (antes ~5/10)

## Próximos passos recomendados
1. Expandir CSRF para todos os formulários e endpoints de mutação.
2. Centralizar autorização em middleware/função única obrigatória.
3. Padronizar validação de input e escaping de output.
4. Adicionar logs estruturados e trilha de auditoria ampliada.
5. Criar testes de fumaça automatizados para fluxos críticos.
