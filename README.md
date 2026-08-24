# abrha/jig

A Docker-based development environment for PHP package tooling.

## Support

jig supports Linux, including WSL2, with Docker Compose v2. Native Windows and
Docker Desktop for macOS are not supported.

The default image uses PHP 8.4. Set `JIG_PHP_VERSION` to build another official
PHP CLI image, such as 8.2 or 8.3; only 8.4 is covered by this project's CI.

## Installation

With PHP and Composer installed on the host:

```bash
composer require --dev abrha/jig
./vendor/bin/jig stub
```

With Docker instead of host PHP and Composer:

```bash
docker run --rm \
    --user "$(id -u):$(id -g)" \
    -e COMPOSER_HOME=/tmp/composer \
    -v "$PWD:/app" \
    -w /app \
    composer:2 \
    composer require --dev abrha/jig
./vendor/bin/jig stub
```

Commit `jig` and `jig.env.example`, and add `jig.env` to `.gitignore`. On a
fresh clone, `./jig` uses Docker to install Composer dependencies before running
the requested command.

Install tagged releases. The `dev-main` branch is not a supported installation.

## Supported tools

jig supports one tool from each axis:

| Axis | Tools |
|---|---|
| Tests | Pest, PHPUnit |
| Style | Laravel Pint, PHP CS Fixer |
| Analysis | PHPStan, Psalm |

The tool selected in the shell environment or `jig.env` takes precedence.
Otherwise jig detects executable tools in `vendor/bin`. If none or more than one
tool exists for an axis, jig stops and asks for an explicit selection.

```dotenv
JIG_TEST_TOOL=phpunit
JIG_STYLE_TOOL=php-cs-fixer
JIG_ANALYSIS_TOOL=psalm
```

## Usage

```bash
./jig test
./jig quality
./jig coverage --min-method=80 --min-line=80 --html
```

| Command | Description |
|---|---|
| `jig test [args]` | Run the selected Pest or PHPUnit executable |
| `jig pest [args]` / `jig phpunit [args]` | Run a test tool explicitly |
| `jig pint [args]` / `jig php-cs-fixer [args]` | Run a style tool explicitly |
| `jig analyse [args]` | Run the selected PHPStan or Psalm executable |
| `jig coverage [--min-*] [--html]` | Run selected tests with coverage thresholds |
| `jig quality` | Run selected style, analysis, and test/coverage tools |
| `jig composer <args>` | Run Composer in the container |
| `jig shell` | Open an interactive shell |
| `jig doctor` | Diagnose the local setup |
| `jig help` | Show every command |

Unrecognised commands are executed from `vendor/bin` when present.

## Configuration

`jig stub` writes `jig.env.example`. Copy it to `jig.env` for local overrides.
Do not commit `jig.env`.

Precedence, highest first: shell environment, `jig.env`, built-in defaults.

| Variable | Default |
|---|---|
| `JIG_PHP_VERSION` | `8.4` |
| `JIG_IMAGE` | `abrha/jig:<php-version>` |
| `JIG_TEST_TOOL` / `JIG_STYLE_TOOL` / `JIG_ANALYSIS_TOOL` | auto-detect |
| `JIG_PHPSTAN_MEMORY` | `512M` |
| `JIG_MIN_LINE` / `JIG_MIN_METHOD` / `JIG_MIN_CLASS` | unset |
| `JIG_QUALITY_MIN_LINE` / `JIG_QUALITY_MIN_METHOD` / `JIG_QUALITY_MIN_CLASS` | `80` / `80` / unset |
| `JIG_COMPOSER_CACHE` | `~/.cache/abrha-jig/composer` |
| `JIG_SSH` | unset (SSH off) |
| `JIG_SSH_DIR` | unset |

## Image

The tooling image contains PHP CLI, Composer, Git, OpenSSH, PCOV, XML,
`mbstring`, `pdo_sqlite`, and `zip`. It is built locally as
`abrha/jig:<php-version>`; this name does not refer to a published Docker image.

The container user is created from the host UID/GID at build time. Run
`jig build` after a UID/GID change.

Packages needing services or extensions such as MySQL, Redis, GD, or Intl should
run `jig publish`, which copies the runtime files into `docker/jig/`, then
customise the copied Dockerfile.

## Private Composer repositories

SSH support is optional and disabled by default. jig never mounts or copies
`~/.ssh` automatically.

Set `JIG_SSH=1`, then choose one mode:

1. Load a key into an SSH agent. jig forwards its socket:

   ```bash
   eval "$(ssh-agent -s)"
   ssh-add ~/.ssh/id_ed25519
   ```

2. Set `JIG_SSH_DIR` to a dedicated key directory. jig mounts that directory
   read-only and copies it into the container. Do not point this at all of
   `~/.ssh`.

Both modes use `StrictHostKeyChecking=accept-new`. Verify host fingerprints
before first use when the repository is security-sensitive. Restart the jig
container after changing the agent socket.

Report security problems through GitHub's private vulnerability reporting,
not a public issue.

## Troubleshooting

| Symptom | Action |
|---|---|
| Unexpected state | `jig doctor` |
| More than one tool detected | Select the tool in `jig.env` |
| Permission errors on generated files | Run `jig build`, then `jig up` |
| SSH authentication fails after agent restart | Run `jig restart` |
| Stale environment | Run `jig reset` |
| Composer cache problems | Remove `~/.cache/abrha-jig/composer` |

## License

MIT
