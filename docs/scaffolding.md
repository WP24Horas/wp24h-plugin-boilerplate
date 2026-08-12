# Criando um plugin com o scaffolder

O `wp24h-plugin-boilerplate` inclui um scaffolder local para transformar uma cópia da base em um plugin novo sem depender de uma sequência manual de `search/replace`.

## Uso

A partir de um clone do boilerplate:

```bash
composer install

composer scaffold -- \
  --name="Acme Orders" \
  --slug=acme-orders \
  --namespace="Acme\\Orders" \
  --vendor=acme \
  --author="Acme Inc." \
  --author-uri="https://example.com" \
  --plugin-uri="https://github.com/acme/acme-orders" \
  --target="../acme-orders"
```

Também é possível chamar o script diretamente:

```bash
php bin/wp24h-init --help
```

`--plugin-uri` é opcional. Quando não for informado, o scaffolder remove a linha `Plugin URI` do cabeçalho em vez de inventar um repositório sob a organização WP24Horas.

## O que é alterado

O scaffolder trabalha somente com identificadores conhecidos da base:

| Boilerplate | Plugin gerado |
|---|---|
| `WP24H Plugin Boilerplate` | valor de `--name` |
| `wp24h-plugin-boilerplate` | valor de `--slug` |
| `wp24h_plugin_boilerplate` | versão `snake_case` derivada do slug |
| `wp24h-boilerplate` | namespace REST derivado do slug |
| `WP24H\\PluginBoilerplate` | valor de `--namespace` |
| `WP24H_PLUGIN_BOILERPLATE` | prefixo derivado do slug |
| `wp24horas/wp24h-plugin-boilerplate` | `vendor/slug` |
| `wp24h-plugin-boilerplate.php` | `<slug>.php` |

Isso mantém alinhados o cabeçalho principal, text domain, namespace PSR-4, constantes, option keys, hooks públicos, namespace REST, referências internas, testes e metadados do Composer.

O `composer.lock` do boilerplate não é copiado, e comandos exclusivos do gerador (`scaffold` e seus smoke tests) são removidos do `composer.json` do novo plugin.

## Runtime identity vs. ownership metadata

O scaffolder trata essas duas categorias separadamente.

**Runtime identity** pode ser derivada deterministicamente do slug/namespace: text domain, hooks, option keys, constantes, REST namespace e package Composer.

**Ownership metadata** não deve ser inventada. Ela depende de quem realmente mantém o novo projeto. Por isso:

- `Plugin URI` só é incluída quando `--plugin-uri` é fornecido;
- `--author-uri` é opcional e pode funcionar como referência de contato do mantenedor;
- o `SECURITY.md` gerado é neutro e não direciona vulnerabilidades à WP24Horas por padrão;
- o README pode registrar a URL explícita do projeto quando ela existir;
- o plugin gerado deve revisar seus próprios canais de suporte e segurança antes da primeira release.

## Regras de segurança

O comando é deliberadamente conservador:

- o diretório informado em `--target` deve **não existir**;
- `.git`, `vendor`, `dist` e o `composer.lock` da base não são copiados;
- o próprio `bin/wp24h-init` e o smoke test do gerador são removidos do plugin gerado;
- se a geração falhar, o target parcial é removido;
- o slug precisa estar em `kebab-case` minúsculo;
- o namespace precisa ter pelo menos dois segmentos PSR-4;
- URLs opcionais precisam ser válidas;
- nenhum arquivo existente fora do target é alterado;
- nenhuma URL de repositório WP24Horas é inventada para o novo projeto.

## Validando o próprio scaffolder

A base possui um smoke test local que gera plugins temporários e valida:

- nome do arquivo principal;
- plugin name e text domain;
- namespace PSR-4;
- package Composer;
- prefixo de constantes;
- option key e hooks públicos;
- namespace REST;
- remoção das ferramentas exclusivas do boilerplate;
- ausência dos identificadores antigos no código/configuração gerados;
- ausência de `Plugin URI` quando nenhuma URL é fornecida;
- uso exato de `--plugin-uri` quando fornecido;
- política de segurança sem ownership implícito da WP24Horas.

Execute:

```bash
composer scaffold:smoke
```

Para a prova completa — incluindo instalação das dependências e execução do `composer check` **dentro do plugin gerado** — use:

```bash
composer scaffold:smoke:full
```

O modo completo exige Composer disponível no `PATH` e pode acessar a rede para baixar dependências.

O smoke estrutural faz parte do `composer check` da própria base e não depende de GitHub Actions.

## Depois de gerar

Entre no novo diretório e valide a base:

```bash
cd ../acme-orders
composer install
composer check
```

Depois revise pelo menos:

1. descrição e URLs do plugin;
2. módulos de exemplo que deseja manter;
3. README e `readme.txt` para refletir o produto real;
4. política de licença, autoria, suporte e canal de segurança;
5. namespace, slug, hooks e text domain com uma busca final;
6. ZIP de distribuição antes do primeiro release.

## Por que não fazer substituição cega

Boilerplates antigos frequentemente pedem ao desenvolvedor para renomear manualmente arquivos, classes, constantes e text domains. Esse processo funciona, mas é fácil deixar uma referência antiga escondida em testes, Composer, documentação ou código de fallback.

O scaffolder existe para automatizar apenas transformações determinísticas e deixar decisões de produto — descrição, ownership, URLs, módulos, branding e release — explícitas para o desenvolvedor.
