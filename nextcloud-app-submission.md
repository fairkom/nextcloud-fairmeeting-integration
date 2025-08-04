# Publishing an App to the Nextcloud App Store

This guide outlines the process for publishing an app to the Nextcloud App Store.

## Prerequisites

- A completed Nextcloud app
- OpenSSL installed on your system
- A Nextcloud App Store account

## App Requirements

Before submission, ensure your app meets the Nextcloud guidelines:

- Licensed under AGPLv3+ or compatible license
- Does not use 'Nextcloud' in the app name
- Uses only the public Nextcloud API
- Compatible with the latest Nextcloud release +1
- Follows design and HTML/CSS layout guidelines
- Cleans up properly on uninstall
- Handles up- and downgrades correctly
- Respects user privacy
- Provides contact means for the author

## Preparing Your App

1. Verify your `info.xml` file follows [the schema](https://apps.nextcloud.com/schema/apps/info.xsd)

   - Ensure the app ID, name, summary, and description are correctly set
   - Fix any broken links or typos
   - Ensure screenshot URLs are direct links, not repository paths

2. Run the code checker to identify issues:

   ```bash
   ./occ app:check-code <app_name>
   ```

3. Fix any issues identified by the code checker

## Creating Certificates

1. Create a directory for your certificates:

   ```bash
   mkdir -p ~/.nextcloud/certificates/
   ```

2. Generate a Certificate Signing Request (CSR):

   ```bash
   openssl req -nodes -newkey rsa:4096 -keyout ~/.nextcloud/certificates/APP_ID.key -out ~/.nextcloud/certificates/APP_ID.csr -subj "/CN=APP_ID"
   ```

   Replace `APP_ID` with your app's ID as defined in `info.xml`.

3. Generate a self-signed certificate:
   ```bash
   openssl x509 -req -days 365 -in ~/.nextcloud/certificates/APP_ID.csr -signkey ~/.nextcloud/certificates/APP_ID.key -out ~/.nextcloud/certificates/APP_ID.crt
   ```

## Packaging Your App

1. Create a tarball of your app:

   ```bash
   tar -czf APP_ID.tar.gz -C /path/to/parent/directory APP_FOLDER
   ```

   Where:

   - `/path/to/parent/directory` is the directory containing your app folder
   - `APP_FOLDER` is your app's folder name

   For example, if your app is in `/Users/developer/Git/myproject/my-app-name`:

   ```bash
   tar -czf my-app-name.tar.gz -C /Users/developer/Git/myproject my-app-name
   ```

2. Create a signature over your app's ID:

   ```bash
   echo -n "APP_ID" | openssl dgst -sha512 -sign ~/.nextcloud/certificates/APP_ID.key | openssl base64 > APP_ID-id.sig
   ```

3. Sign your app package:
   ```bash
   openssl dgst -sha512 -sign ~/.nextcloud/certificates/APP_ID.key APP_ID.tar.gz | openssl base64 > APP_ID.sig
   ```

## Registering and Uploading Your App

1. Visit the [Nextcloud App Store](https://apps.nextcloud.com/) and log in to your account

2. Register your app:

   - Click "Register app"
   - Upload your public certificate (`~/.nextcloud/certificates/APP_ID.crt`)
   - Provide the signature over your app's ID (content of `APP_ID-id.sig`)
   - Submit the form

3. Upload app release:
   - Navigate to your app's page after registration
   - Click "Upload app release"
   - Provide your app package (`APP_ID.tar.gz`) or upload link
   - Provide the signature file (`APP_ID.sig`)
   - Submit the form

## After Submission

- Your app will undergo review by the Nextcloud team
- Apps are evaluated against the guidelines mentioned in this document
- You may be contacted for clarification or requested to make changes
- Once approved, your app will be available in the Nextcloud App Store

## Updating Your App

To update your existing app in the store, follow these steps:

### 1. Prepare the Update

1. Merge all feature branches and update version in `info.xml`
2. Create and push a new git tag:
   ```bash
   git tag v0.22.3
   git push origin v0.22.3
   ```

### 2. Prepare Dependencies for Production

Before creating the archive, ensure you have production-ready dependencies:

```bash
cd fairmeeting

# Remove existing vendor to ensure clean state
rm -rf vendor/

# Install only production dependencies (no dev tools like phpstan, php-cs-fixer)
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm install
npm run build

# Go back to project root
cd ..
```

**Why this matters:**
- **Vendor directory**: Must be included in App Store packages (end users can't run `composer install`)
- **Production only**: Excludes development tools (phpstan, php-cs-fixer, testing frameworks)
- **Optimized autoloader**: Faster class loading in production
- **Built assets**: Compiled JS/CSS files ready for production

### 3. Create the Correct Archive Structure

The app store requires an archive with the app folder as the root directory:

```bash
# Clean any macOS artifacts first
find fairmeeting -name "._*" -type f -delete
find fairmeeting -name ".DS_Store" -type f -delete

# Create archive with correct structure (app folder as root)
# IMPORTANT: Use COPYFILE_DISABLE=1 on macOS to avoid metadata artifacts
COPYFILE_DISABLE=1 tar -czf fairmeeting_v0.22.3.tar.gz fairmeeting
```

**Important:** 
- The archive must contain a folder named exactly like your app ID (e.g., `fairmeeting/`), not loose files
- **On macOS:** Always use `COPYFILE_DISABLE=1` to prevent including hidden `._*` files that can break the app
- **Vendor included**: The archive should contain the `vendor/` directory with production dependencies

### 3. Sign the Archive

Create a new signature for the archive:

```bash
openssl dgst -sha512 -sign ~/.nextcloud/certificates/fairmeeting.key fairmeeting_v0.22.3_correct.tar.gz | openssl base64 > fairmeeting_v0.22.3_correct.sig
```

### 4. Upload to GitHub Release

1. Go to your GitHub repository releases
2. Edit the existing release or create a new one
3. Upload the `fairmeeting_v0.22.3_correct.tar.gz` file as a release asset
4. Get the download URL: `https://github.com/user/repo/releases/download/v0.22.3/fairmeeting_v0.22.3_correct.tar.gz`

### 5. Update in App Store

1. Visit the [Nextcloud App Store Developer Portal](https://apps.nextcloud.com/developer/)
2. Log in and select your app
3. Click **"Upload app release"**
4. Provide:
   - **Download link**: The GitHub release URL
   - **Signature**: Content of the `.sig` file you created
5. Submit the form

### Common Issues When Updating

- **"No possible app folder found"**: Archive must contain app folder (e.g., `fairmeeting/`) as root
- **"App folder must contain only lowercase ASCII characters"**: Ensure folder name matches app ID exactly
- **"Signature is invalid"**: Create new signature for the new archive - old signatures won't work
- **Archive structure**: Don't include the entire repository, only the app folder

## Common Issues

- **Incorrect app structure**: Ensure your tarball contains only the app folder
- **Certificate errors**: Make sure to use the exact app ID in your certificate
- **Signature validation failure**: Double-check your signing process
- **Guidelines violations**: Review and correct any violations before resubmitting

## Resources

- [Nextcloud App Store Developer Documentation](https://nextcloudappstore.readthedocs.io/en/latest/developer.html)
- [Nextcloud App Development Documentation](https://docs.nextcloud.com/server/latest/developer_manual/app_development/index.html)
- [Nextcloud App Store](https://apps.nextcloud.com/)
