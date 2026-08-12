# WP24H Plugin Boilerplate

Uma base moderna, modular e configurável para plugins WordPress profissionais.

O projeto funciona imediatamente após ser copiado, mesmo sem `vendor/`, e oferece ferramentas de qualidade para quem utiliza Composer no desenvolvimento.

## O que vem pronto

- Arquitetura orientada a módulos com contrato público e registro via filtro.
- Tela de configurações usando a Settings API do WordPress.
- Ativação individual de recursos sem editar código.
- Exemplos funcionais de shortcode, endpoint REST, widget do painel, aviso administrativo e diagnóstico via Site Health.
- Sanitização, escape, verificação de capacidade e callbacks REST explícitos.
- Internacionalização e carregamento de text domain.
- Compatibilidade com WordPress 6.5+ e PHP 8.1+.
- PHPCS com WordPress Coding Standards e PHPCompatibility.
- PHPStan com extensões para WordPress.
- PHPUnit com Brain Monkey.
- Matriz de compatibilidade para PHP 8.1, 8.2, 8.3 e 8.4 em workflow manual.
- Ambiente local opcional com `wp-env`.
- Política de desinstalação segura: dados são preservados por padrão.
- Build reproduzível de ZIP e workflow de release preparado para uso quando uma tag for publicada.
- Scaffolder seguro para criar um plugin novo sem uma sequência manual de `search/replace`.
- Smoke test local que valida o plugin gerado e pode executar uma prova completa com Composer.

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

## Status de release

A versão atual do plugin é **1.0.0**, mas ainda não existe tag ou GitHub Release publicada neste repositório.

A primeira release deve ser criada somente após validação local/runtime da versão atual. O processo completo está em [docs/releasing.md](docs/releasing.md).

## Instalação rápida da base

```bash
git clone https://github.com/WP24Horas/wp24h-plugin-boilerplate.git
cd wp24h-plugin-boilerplate
composer install
composer check
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
| REST API | Ativo | Expõe `GET /wp-json/wp24h-boilerplate/v1/message` |
| Dashboard widget | Inativo | Adiciona um card ao painel administrativo |
| Admin notice | Inativo | Mostra uma mensagem apenas a administradores |
| Site Health | Inativo | Adiciona um teste direto de baseline de WordPress/PHP em Ferramentas → Saúde do site |

Headline, mensagem, cor de destaque e namespace REST podem ser ajustados pela interface.

## Criando um módulo

Implemente `WP24H\PluginBoilerplate\Contracts\Module` e registre a instância:

```php
add_filter(
	'wp24h_plugin_boilerplate_modules',
	static function ( array $modules ): array {
		$modules[] = new MinhaEmpresa\MeuPlugin\MeuModulo();
		return $modules;
	}
);
```

O módulo passa a aparecer automaticamente na tela de configurações. Consulte [docs/module-api.md](docs/module-api.md).

## Transformando em seu plugin

O caminho recomendado agora é usar o scaffolder, em vez de fazer renomeações manuais.

Depois da geração:

1. Remova os módulos de exemplo que não fazem sentido.
2. Defina seus módulos no método `Plugin::build_modules()` ou pelo filtro público.
3. Atualize descrição, URLs, branding, documentação e licença quando necessário.
4. Faça uma busca final pelo slug/namespace originais como verificação de higiene.
5. Rode `composer check` antes de publicar.

O roteiro completo está em [docs/customization.md](docs/customization.md).

## Comandos

```bash
composer scaffold -- ...        # Gera um plugin novo a partir da base
composer scaffold:smoke         # Valida estruturalmente um scaffold temporário
composer scaffold:smoke:full    # Gera, instala dependências e roda composer check no plugin temporário
composer lint                    # WordPress Coding Standards
composer lint:fix                # Correções automáticas seguras
composer analyse                 # PHPStan
composer test                    # PHPUnit
composer check                   # Lint + análise + testes + smoke estrutural do scaffolder
bash scripts/build-release.sh    # Gera dist/wp24h-plugin-boilerplate.zip
```

O processo completo de publicação está documentado em [docs/releasing.md](docs/releasing.md).

## CI e custo

O workflow de qualidade está atualmente em modo manual (`workflow_dispatch`). O loop normal de desenvolvimento usa `composer check` local, preservando a matriz de compatibilidade para execuções deliberadas sem consumir GitHub Actions a cada push.

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
