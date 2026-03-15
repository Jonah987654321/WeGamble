#!/usr/bin/env bash
set -euo pipefail

# Load .env into shell
set -a
source ./.env
set +a

profile="dev"
detach_flags=()

if [[ "${APP_ENV:-development}" == "production" ]]; then
  profile="prod"
  detach_flags=(-d)
fi

exec docker compose --profile "$profile" up --build "${detach_flags[@]}"