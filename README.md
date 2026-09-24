# Mathcraft

Mathcraft is a lightweight browser-based Minecraft-style project that serves a simple landing page and launches a bundled Eaglercraft / Resent 5.1 client from the web. It is designed to be easy to host on a static web server and to provide a quick way to open the game client in the browser.

## Overview

This repository contains the front-end files for a static web site hosted under the `htdocs` directory. The main page acts as a launcher, and the project includes a prepared client entry point for the Resent 5 client experience.

The project is intentionally simple:

- static HTML/CSS front-end
- no backend required
- easy deployment to any basic web host
- browser-friendly launch flow for the client

## Features

- Custom landing page for the project
- One-click link to the Resent 5.1 client
- Static hosting compatibility for free or low-cost web hosts
- Lightweight structure with no dependency installation required
- Portable assets that can be served directly from a web root

## Project Structure

```text
mathcraft/
├── README.md
├── htdocs/
│   ├── index.html
│   ├── index2.html
│   ├── style.css
│   ├── eagler12/
│   ├── eagler8/
│   └── mod/
│       ├── resent5.html
│       ├── resent5_part_00
│       ├── resent5_part_01
│       └── resent5_part_02
|
```

## Run Locally

Because this is a static site, you can run it locally with any simple web server:

```bash
cd /workspaces/mathcraft
python3 -m http.server 8000
```

Then open:

```text
http://localhost:8000/
```

If you are hosting it online, upload the contents of the `htdocs/` directory to your web root or hosting provider.

## Deployment

To deploy this project:

1. Upload the contents of `htdocs/` to your hosting service.
2. Make sure your host serves static `.html` files correctly.
3. Confirm that the browser client loads from the correct relative path, especially the `mod/resent5.html` link.
4. If needed, adjust the `index.html` navigation links or stylesheet paths for your hosting environment.

## Notes

- This project relies on client-side browser assets and may require a modern browser.
- The game client files are large and may be served best from a host with enough bandwidth and storage.
- Some assets may be third-party or community-provided; check licensing and usage rights before publishing publicly.
- If you are using this for a public site, consider updating the branding, landing page copy, and project description to match your own deployment.

## Customization

You can easily tailor the project by editing:

- `htdocs/index.html` for the main landing page
- `htdocs/style.css` for layout and visual design
- `htdocs/mod/resent5.html` for launch behavior and client configuration
