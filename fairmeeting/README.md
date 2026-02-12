# fairmeeting integration for Nextcloud

Integrate the **fairmeeting** video conferencing service seamlessly into your Nextcloud!  
**fairmeeting** is based on the Jitsi software, hosted by fairkom in the EU, thus fully **GDPR compliant**.

**[fairmeeting.net](https://fairmeeting.net)** is set as the default server. Unlike `meet.jit.si`, it requires no additional login/token and avoids the 5-minute limit on embedded conferences (which meet.jit.si introduced in 2023). Fair use conference duration is max one hour.

---

## ✨ fairmeeting features for Nextcloud

- 🎬 Full-featured online video conferences in nextcloud
- 🔗 Sharable conference room links
- 🔎 Integrated into global Nextcloud search
- ✅ Audio & video test before joining a conference (if opened in same tab)
- 💯 Supports hundreds of users
- 🖼 Customizable background images
- 👏 Emoji reactions & animated GIF interactions
- 👩🏼‍🏫 Organiser is moderator and can assign moderation rights
- 📺 play videos not only from Youtube, but also from any PeerTube instance (such as fair.tube)
- ➕ Option to open meetings **in a new browser tab** instead of embedded
- 🔒 Flexible optional JWT Authentication:
  - **Enter JWT Token directly** (pre-generated, no need to share secrets)
  - **Or provide JWT_APP_SECRET** to auto-generate tokens
- 📅 **Calendar Integration**:
  - Automatically adds fairmeeting links to new calendar events
  - Smart location handling (only adds to empty locations)
  - Keyword-based triggers: Use keywords like `#fm` to auto-generate meeting links
  - Flexible keyword replacement in location and/or description fields
  - Works with events that have attendees or meet minimum duration requirements

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

- **Fair Use:** Private, occasional use, max one hour  
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
4. **Optional - Calendar Integration:**
   - Enable "Automatically add fairmeeting links to calendar events"
   - Set minimum event duration (default: 15 minutes)
   - Enable "Use keyword-based triggers" for manual control
   - Configure trigger keyword (e.g., `#fm`, `fairmeeting`, `online`)
   - Choose where keywords should be replaced (location and/or description fields)
5. Start conferencing

---

## 📅 Calendar Integration

The fairmeeting app includes automatic calendar integration with keyword-based triggers.

### How it works:

#### Automatic Mode (default):
- fairmeeting links are automatically added to calendar events with attendees or that meet the minimum duration
- Links are added to empty location fields only

#### Keyword-Based Mode (optional):
- Enable "Use keyword-based triggers" in admin settings
- Set your trigger keyword (default: `#fm`)
- Users add the keyword to their calendar events
- The keyword is replaced with a fairmeeting link
- Example: "Team Meeting #fm" → "Team Meeting https://fairmeeting.net/xyz"

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
