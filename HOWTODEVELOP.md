# fairmeeting integration for Nextcloud

- forked from https://github.com/nextcloud/jitsi
- fork on github: https://github.com/fairkom/nextcloud-fairmeeting-integration
- fork on gitlab: https://git.fairkom.net/hosting/fairkom/nextcloud_fairmeeting
- opimized for: https://www.fairkom.eu/en/fairmeeting
- faq: https://git.fairkom.net/hosting/fairmeeting/-/wikis/home

## Local Dev Instance:

uses the nextcloud developer docker: https://github.com/juliushaertl/nextcloud-docker-dev

### Basic Setup:

- Build the app:
  In the repo folder, run the following commands to install dependencies and build the app:

  1. `composer install` – Installs PHP dependencies.
  2. `npm install` – Installs Node.js dependencies.
  3. `npm run build` – Builds the app (e.g., compiles assets, prepares production-ready files).

```bash
docker run --rm -p 12345:80 -e SERVER_BRANCH=v28.0.6 \
  -v $(pwd):/var/www/html/apps-extra/fairmeeting \
  ghcr.io/juliusknorr/nextcloud-dev-php81:latest
```

- Then go to http://localhost:12345/index.php/settings/apps, login with u: admin pw: admin, and activate the 'fairmeeting Integration App'

### Calendar Integration Development:

For developing and testing the Calendar Integration feature, you need both the fairmeeting app and the Calendar app:

#### Method 1: Development Container with Calendar App

```bash
# Start development container
docker run -d --name nextcloud-dev \
  -p 4236:80 -e SERVER_BRANCH=v28.0.6 \
  -v $(pwd):/var/www/html/apps-extra/fairmeeting \
  ghcr.io/juliusknorr/nextcloud-dev-php81:latest
```

# If not existing than install Calendar app

```bash

docker exec nextcloud-dev curl -L -o /tmp/calendar.tar.gz \
  "https://github.com/nextcloud-releases/calendar/releases/download/v4.7.18/calendar-v4.7.18.tar.gz"
docker exec nextcloud-dev bash -c "cd /var/www/html/apps && tar -xzf /tmp/calendar.tar.gz && chown -R www-data:www-data calendar"
docker exec -u 33 nextcloud-dev php /var/www/html/occ app:enable calendar
```

#### Method 2: Standard Nextcloud Container

```bash
# Use a standard Nextcloud container that includes the Calendar app
docker run -d --name nextcloud-full \
  -p 4237:80 \
  -e NEXTCLOUD_ADMIN_USER=admin \
  -e NEXTCLOUD_ADMIN_PASSWORD=admin \
  nextcloud:28

# Copy fairmeeting app to container
docker cp $(pwd) nextcloud-full:/var/www/html/apps/fairmeeting
docker exec -u 33 nextcloud-full php /var/www/html/occ app:enable fairmeeting
```

## Test it on dev2.faircloud.eu:

- Build the app:
  In the repo folder, run the following commands to install dependencies and build the app:

  1. `composer install` – Installs PHP dependencies.
  2. `npm install` – Installs Node.js dependencies.
  3. `npm run build` – Builds the app (e.g., compiles assets, prepares production-ready files).

- Create a ZIP archive:
  After the build is complete, create a ZIP file containing all the necessary project files. `fairmeeting.zip`. You can use the following command to create the zip:

```
cd .. && zip -r fairmeeting.zip nextcloud-fairmeeting-integration
```

This will compress all files in the current directory.

- Transfer the ZIP file via SCP:
  Run the following command to send it to `dev2.faircloud.eu`, replace server or path if necessary

```
scp fairmeeting.zip nx-dev2:.
```

- Go to where you tranfered it on the server and than copy it into the container nextcloud/aio-nextcloud:latest:

```
docker cp fairmeeting.zip d5a93222a085:/var/www/html/custom_apps
```

- Go inside the container:

```
docker exec -it d5a93222a085 bash
```

- Go to /var/www/html/custom_apps and unzip:cd

```
unzip fairmeeting.zip
```

- rename the folder:

mv nextcloud-fairmeeting-integration/ fairmeeting/

## Architecture

### Core Components:

- **Backend (PHP)**:

  - `lib/Config/Config.php` - Configuration management and settings
  - `lib/Controller/` - HTTP request handlers (Room, Page, User, Assets)
  - `lib/Db/` - Database entities and operations (Room, RoomMapper)
  - `lib/Listener/CalendarEventListener.php` - Calendar integration event handler
  - `lib/Search/Provider.php` - Global search integration

- **Frontend (Vue.js 2)**:
  - `src/Index.vue` - Main room listing page
  - `src/Room.vue` - Individual room interface
  - `src/Admin.vue` - Admin settings panel
  - `src/components/` - Reusable components (RoomList, BrowserTest, etc.)

## Features

- 🎥 Easy online conferences in Nextcloud utilising fairmeeting
- 🔗 Sharable conference room links
- 🔎 Shows conference rooms in the global search
- ✅ System test before joining a conference
- 📅 **Calendar Integration**:
  - Automatic meeting link injection into calendar events
  - Smart location handling (doesn't overwrite existing locations)
  - Configurable description templates
  - Event-driven architecture using Nextcloud's CalDAV events

## Changelog

[See CHANGELOG.md](./CHANGELOG.md)

## Setup

⚠ It is highly recommended to set up a dedicated fairmeeting instance.
Further instructions can be found in the [fairmeeting setup doc](https://fairmeeting.github.io/handbook/docs/devops-guide/devops-guide-start).

🔒 In addition to that the fairmeeting instance should be secured via JSON Web Token.
Information about this can be found in the [fairmeeting authentication doc](https://fairmeeting.github.io/handbook/docs/devops-guide/devops-guide-docker#authentication).

Nextcloud setup and configuration:

- Install the Nextcloud fairmeeting app
- Go to _Settings_ → _fairmeeting_ and enter your server URL (and JWT secret)
- Start conferencing 🍻

## Issues

Report issues and feature requests [here](https://github.com/nextcloud/fairmeeting).

## Translations

```
wget https://github.com/nextcloud/docker-ci/raw/master/translations/translationtool/translationtool.phar
chmod u+x translationtool.phar
./translationtool.phar create-pot-files
./translationtool.phar convert-po-files
```

## Licence

See [LICENCE](./LICENCE)
