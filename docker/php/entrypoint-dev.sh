#!/usr/bin/env sh
set -eu

cd /var/www/html

# The purpose of this script is for the dev mode:
# -> src dir is mounted into docker container
# -> this means we can't install the dependencies via the Docker file
# -> bc the mount would delete the vendor folder created in the image (because it isn't contained in the repo src)
# -> to fix this, the docker volume vendor_dev keeps the folder
# -> however on first startup the volume is empty, so this entry point script install the deps

# If vendor is missing OR lock file is newer than vendor, install deps
if [ ! -f "vendor/autoload.php" ]; then
  echo "[entrypoint-dev] vendor/autoload.php missing -> running composer install"
  composer install --prefer-dist --no-interaction --no-progress
else
  # Run composer install if composer.lock changed
  if [ -f "composer.lock" ] && [ -d "vendor" ]; then
    # If composer.lock is newer than vendor dir, re-install
    if [ "composer.lock" -nt "vendor" ]; then
      echo "[entrypoint-dev] composer.lock newer than vendor -> running composer install"
      composer install --prefer-dist --no-interaction --no-progress
    fi
  fi
fi

# Run the original container command (php-fpm or whatever was passed)
exec "$@"