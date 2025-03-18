# fairmeeting integration for nextcloud

Integrate the **fairmeeting** video conferencing service seamlessly into your Nextcloud!  
**fairmeeting** is based on Jitsi, hosted in the EU, and fully **GDPR compliant**.

We set **[fairmeeting.net](https://fairmeeting.net)** as the default server. Unlike `meet.jit.si`, it requires no additional login/token and avoids the 5-minute limit on embedded conferences (introduced in 2023).

---

## ✨ Features

- 🎬 Full-featured online video conferences in nextcloud
- 🔗 Sharable conference room links
- 🔎 Integrated into global Nextcloud search
- ✅ Audio & video test before joining a conference
- 💯 Supports hundreds of users
- 🖼 Customizable background images
- 👏 Emoji reactions & animated GIF interactions
- 👩🏼‍🏫 Organiser is moderator and can assign moderation rights
- ➕ Option to open meetings **in a new browser tab** instead of embedded
- 🔒 Flexible JWT Authentication:
  - **Enter JWT Token directly** (pre-generated, no need to share secrets)
  - **Or provide JWT_APP_SECRET** to auto-generate tokens

---

## 📚 Sources & FAQs

- Forked & improved from: [nextcloud/jitsi](https://github.com/nextcloud/jitsi) (thanks to their great work!)
- Source code:
  - [GitHub](https://github.com/fairkom/nextcloud-fairmeeting-integration)
  - [GitLab](https://git.fairkom.net/hosting/fairkom/nextcloud_fairmeeting)
- Optimized for: [fairmeeting by fairkom](https://www.fairkom.eu/en/fairmeeting) (including desktop apps)
- FAQs: [fairmeeting wiki](https://git.fairkom.net/hosting/fairmeeting/-/wikis/home)

---

## 🌍 Usage of fairmeeting Servers

**Default server:** [fairmeeting.net](https://fairmeeting.net)  
Managed by [fairkom](https://fairkom.eu) and running on scalable Kubernetes infrastructure.

- **Fair Use:** Private, occasional use  
  ➔ Please consider a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=N8PYR9YWQHVE8&source=url) to support server costs.
- **Commercial/Regular Use:**  
  ➔ Please [order a pro plan](https://shop.fairkom.net/paketevergleich/).

---

## ⚙️ Setup with Your Own Jitsi Server

You can also integrate your **own Jitsi instance**!

📖 Setup guide: [Jitsi Setup Documentation](https://jitsi.github.io/handbook/docs/devops-guide/devops-guide-start)

**Requirements:**  
Familiarity with networks, TURN servers, and port management.

### 🔐 JWT Authentication Options:

1. **JWT Token Field:**  
   Enter a pre-generated **JWT Token** (recommended if you don't want to share your secret key).

2. **JWT_APP_SECRET Field:**  
   Enter your **JWT_APP_SECRET**, allowing the app to auto-generate tokens.

> **Note:** If both fields are filled, the pre-generated **JWT Token** will take priority.

---

## 🚀 Nextcloud Setup Instructions

1. Install the **Nextcloud fairmeeting app**
2. Navigate to:  
   _Settings_ → _fairmeeting_
3. Configure:
   - Your Jitsi server URL
   - Either **JWT Token** **OR** **JWT_APP_SECRET**
   - Option to open meetings in a **new browser tab** or embedded in Nextcloud
4. Start conferencing

---

## 🐛 Issues

Report issues and feature requests here:  
[Issue Tracker](https://git.fairkom.net/hosting/fairkom/nextcloud_fairmeeting/-/issues)

---

## 🛠️ Setting up a Dev Instance

- Use the Nextcloud developer Docker environment
- In your repo folder:

```bash
composer install
npm install
npm run build
```

```bash
docker run --rm -p 12345:80 -e SERVER_BRANCH=v28.0.6 \
  -v $(pwd):/var/www/html/apps-extra/fairmeeting \
  ghcr.io/juliusknorr/nextcloud-dev-php81:latest
```

Then go to http://localhost:12345/index.php/settings/apps, login (admin / admin), and activate the fairmeeting app.

## Changelog

[See CHANGELOG.md](./CHANGELOG.md)

## Translations

```
wget https://github.com/nextcloud/docker-ci/raw/master/translations/translationtool/translationtool.phar
chmod u+x translationtool.phar
./translationtool.phar create-pot-files
./translationtool.phar convert-po-files
```

## Licence

AGPL, see [LICENCE](./LICENCE).
