# WP24H Plugin Boilerplate

Uma base moderna, modular e configurável para plugins WordPress profissionais.

O projeto funciona imediatamente após ser copiado, mesmo sem `vendor/`, e oferece ferramentas de qualidade para quem utiliza Composer no desenvolvimento.

## O que vem pronto

- Arquitetura orientada a módulos com contrato público e registro via filtro.
- Tela de configurações usando a Settings API do WordPress.
- Ativação individual de recursos sem editar código.
- Exemplos funcionais de shortcode, REST público, REST protegido, widget do painel, aviso administrativo e diagnóstico via Site Health.
- Sanitização, escape, verificação de capacidade e callbacks REST explícitos.
- Internacionalização e carregamento de text domain.
- Compatibilidade com WordPress 6.5+ e PHP 8.1+.
- PHPCS com WordPress Coding Standards e PHPCompatibility.
- PHPStan com extensões para WordPress.
- PHPUnit com Brain Monkey.
- Matriz de compatibilidade para PHP 8.1, 8.2, 8.3 e 8.4 em workflow manual.
- Ambiente local opcional com `wp-env`.
- Política de desinstalação segura: dados são preservados por padrão.
- Build reproduzível e verificado de ZIP, com workflow de release opcional e manual-only.
- Scaffolder seguro para criar um plugin novo sem uma sequência manual de `search/replace`.
- Gerador de módulos que continua disponível dentro dos plugins criados pelo scaffolder.
- Smoke test local que valida o fluxo boilerplate → plugin → módulo e pode executar uma prova completa com Composer.
- Hardening tests para impedir que metadata de CLI quebre headers, DocBlocks ou o PHP gerado.

## Criar um plugin novo

Depois de clonar a base e instalar as dependências de desenvolvimento:

```bash
git clone https://github.com/WP24Horas/wp24h-plugin-boilerplate.git
cd wp24h-plugin-boilerplate
composer install
```

Gere um novo plugin em outro diretório:

```bash
composer scaffold -- \
  --name="Acme Orders" \
  --slug=acme-orders \
  --namespace="Acme\\Orders" \
  --vendor=acme \
  --author="Acme Inc." \
  --author-uri="https://example.com" \
  --target="../acme-orders"
```

O comando mantém alinhados nome, slug/text domain, namespace PSR-4, option keys, hooks públicos, namespace REST, prefixo de constantes, pacote Composer e nome do arquivo principal. Ele não sobrescreve targets existentes, não copia o `composer.lock` da base e remove as ferramentas exclusivas do scaffolder no plugin gerado.

Veja [docs/scaffolding.md](docs/scaffolding.md) para os detalhes e regras de segurança.

## Criar um módulo

Dentro da base ou de um plugin gerado:

```bash
composer make:module -- \
  --class=AuditLogModule \
  --id=audit_log \
  --label="Audit log" \
  --description="Registers audit-log hooks."
```

O gerador descobre o namespace PSR-4 e o text domain do próprio projeto, cria a classe em `src/Modules/`, cria o teste correspondente em `tests/Unit/` e se recusa a sobrescrever arquivos existentes.

Consulte [docs/module-api.md](docs/module-api.md).

## Status de release

A versão atual do plugin é **1.0.0**, mas ainda não existe tag ou GitHub Release publicada neste repositório.

O changelog permanece em `Unreleased` até os gates de qualidade, runtime e distribuição serem concluídos. A primeira release deve ser criada somente após validação local/runtime da versão atual. O processo completo está em [docs/releasing.md](docs/releasing.md).

## Instalação rápida da base

```bash
git clone https://github.com/WP24Horas/wp24h-plugin-boilerplate.git
cd wp24h-plugin-boilerplate
composer install
composer check
composer scaffold:smoke
```

Para experimentar no WordPress local:

```bash
npx @wordpress/env start
```

Depois, acesse **Configurações → WP24H Boilerplate**.

## Recursos configuráveis

| Módulo | Padrão | Resultado |
|---|---:|---|
| Shortcode | Ativo | Registra `[wp24h_boilerplate]` |
| REST API pública | Ativo | Expõe `GET /wp-json/wp24h-boilerplate/v1/message` |
| REST API protegida | Inativo | Expõe `POST /wp-json/wp24h-boilerplate/v1/protected-message` com `manage_options`, validação e sanitização |
| Dashboard widget | Inativo | Adiciona um card ao painel administrativo |
| Admin notice | Inativo | Mostra uma mensagem apenas a administradores |
| Site Health | Inativo | Adiciona um teste direto de baseline de WordPress/PHP em Ferramentas → Saúde do site |

Headline, mensagem, cor de destaque e namespace REST podem ser ajustados pela interface.

Os padrões REST estão explicados em [docs/rest-api.md](docs/rest-api.md), incluindo a diferença entre dados genuinamente públicos e operações que exigem capability checks.

## Transformando em seu plugin

O caminho recomendado é usar o scaffolder, em vez de fazer renomeações manuais.

Depois da geração:

1. Remova os módulos de exemplo que não fazem sentido.
2. Gere novos módulos com `composer make:module -- ...` quando for útil.
3. Registre os módulos no método `Plugin::build_modules()` ou pelo filtro público.
4. Atualize descrição, URLs, branding, documentação e licença quando necessário.
5. Faça uma busca final pelo slug/namespace originais como verificação de higiene.
6. Rode `composer check` antes de publicar.

O roteiro completo está em [docs/customization.md](docs/customization.md).

## Comandos

```bash
composer scaffold -- ...        # Gera um plugin novo a partir da base
composer make:module -- ...     # Gera classe + teste de um módulo
composer scaffold:smoke         # Valida boilerplate → plugin → módulo em diretório temporário
composer scaffold:smoke:full    # Também instala dependências e roda composer check no plugin temporário
composer lint                    # WordPress Coding Standards
composer lint:fix                # Correções automáticas seguras
composer analyse                 # PHPStan
composer test                    # PHPUnit
composer tooling:lint            # Sintaxe PHP das ferramentas CLI mantidas no projeto
composer tooling:hardening       # Regressões de segurança dos geradores
composer check                   # Lint + análise + testes + tooling lint + hardening
composer release:build           # Gera o ZIP de distribuição
composer release:verify          # Valida estrutura e exclusões do ZIP
composer release:package         # Build + verify em um único gate local
```

`composer check` é autocontido e continua funcionando no plugin gerado. O smoke do scaffolder é deliberadamente um comando separado porque existe apenas na base.

O processo completo de publicação está documentado em [docs/releasing.md](docs/releasing.md).

## CI e custo

Os workflows de qualidade e release estão atualmente em modo manual (`workflow_dispatch`). O loop normal de desenvolvimento usa validação local, preservando a matriz de compatibilidade e o fluxo de release para execuções deliberadas sem consumir GitHub Actions a cada push, pull request ou criação de tag.

## Ecossistema WordPress relacionado

Este boilerplate é a base de arquitetura do conjunto de ferramentas WordPress mantidas pela WP24Horas e pelo mantenedor.

- **[WP Plugin Readme Validator](https://github.com/asllanmaciel/wp-plugin-readme-validator)** — valida `readme.txt` e o cabeçalho principal do plugin antes de uma publicação.
- **[WP24H MD Importer](https://github.com/asllanmaciel/wp24h-md-importer)** — plugin real que demonstra importação de Markdown, REST autenticada, capabilities, mídia e automação editorial.

Os projetos têm objetivos diferentes: o Boilerplate ensina estrutura, o Validator protege metadados de distribuição e o MD Importer mostra padrões aplicados em um plugin funcional.

## Compatibilidade

- WordPress: 6.5 ou superior; testado até 7.0.
- PHP: 8.1 a 8.4 na matriz de compatibilidade.

## Segurança

Relate vulnerabilidades de forma privada seguindo [SECURITY.md](SECURITY.md). Não abra uma issue pública com detalhes exploráveis.

## Contribuindo

Issues e pull requests são bem-vindos. Leia [CONTRIBUTING.md](CONTRIBUTING.md) antes de enviar mudanças.

## Licença

GPL-2.0-or-later. Consulte [LICENSE.md](LICENSE.md).
