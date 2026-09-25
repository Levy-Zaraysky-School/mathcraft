# Mathcraft

Mathcraft is a PHP-powered launcher for Resent 5.1 and several Eaglercraft client builds. The large client HTML files are split into upload-sized `.txt` chunks and streamed back together by small PHP loaders, which works around hosting file-size limits.

## Features

- Launcher page for 1.8.8 and 1.12.2 clients
- Official, PixelClient, and AstraClient builds
- Resent 5.1 launcher
- Chunks limited to 8 MB or less
- No package installation or build step required
- Browser-signature access requests with an admin approval panel

## Project Structure

```text
mathcraft/
├── index.html              # Main launcher
├── flamepvp.html           # Separate hacked-client launcher
├── style.css               # Launcher styling
├── resent5.php             # Resent 5.1 chunk loader
├── mod/                    # Resent 5.1 chunks
│   └── ...
├── eagler1.8/              # 1.8.8 launchers and chunks
│   ├── astra.php
│   ├── js.php
│   ├── pixel_js.php
│   ├── pixel_wasm.php
│   ├── wasm.php
│   ├── astra/              # AstraClient chunks
│   │   └── ...
│   ├── official/           # Official client chunks
│   │   └── ...
│   ├── flamepvp/           # Downloaded FlamePVP client chunks
│   │   └── ...
│   └── pixel/              # PixelClient chunks
│       └── ...
├── eagler1.12/             # 1.12.2 launchers and chunks
│   ├── js.php
│   ├── pixel_js.php
│   ├── pixel_wasm.php
│   ├── wasm.php
│   ├── official/
│   │   └── ...
│   └── pixel/
│       └── ...
└── ...
```

## Run Locally

The launchers require PHP. From the project directory, run:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/` in a browser.

Before uploading, generate a strong password hash and replace `AUTH_ADMIN_PASSWORD_HASH` in `auth-config.php`. Commit the hash, never the original password. Then open `/auth.php` for users or `/admin.php` for approvals. No environment variable is required:

```bash
php -r 'echo password_hash("your-private-password", PASSWORD_DEFAULT), PHP_EOL;'
```

Each request includes the user's name and browser user-agent along with the browser signature. The signature is a random value stored in local storage and hashed before it is sent to the server. It is a device label, not a secure identity: clearing browser storage or switching browsers creates a new request.

The admin password is checked with `password_verify()`, so the repository contains only a bcrypt hash. Use HTTPS in production because the password is sent to the admin endpoint during login. Keep the original password out of GitHub and change the hash immediately if the password is exposed.

For deployment, upload the project files to a PHP-enabled web host and open `index.html` through that host. Do not use a static-only server if you want the PHP loaders to launch the clients.

## Notes

- The client chunks are large and may require sufficient hosting storage and bandwidth.
- A modern browser is recommended.
- Check the licensing and usage rights for third-party client builds before publishing publicly.
- Edit `index.html` for launcher links and `style.css` for visual changes.
