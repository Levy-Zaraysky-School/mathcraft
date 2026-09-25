<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mathcraft access</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <main class="auth-shell">
      <section class="auth-panel">
        <p class="eyebrow">Mathcraft access</p>
        <h1>Request a launcher pass.</h1>
        <p class="auth-copy">This browser has a private device signature. Submit it once and an administrator can approve access.</p>
        <label class="auth-label" for="request-name">Name</label>
        <input class="auth-input" id="request-name" maxlength="40" autocomplete="name" placeholder="Your name" required />
        <div class="auth-status" id="auth-status" aria-live="polite">Checking this browser...</div>
        <button class="button-link auth-action" id="auth-action" type="button">Request access</button>
        <a class="auth-back" href="index.html">Back to launcher</a>
      </section>
    </main>
    <script>
      const statusElement = document.getElementById('auth-status');
      const actionButton = document.getElementById('auth-action');
      const nameInput = document.getElementById('request-name');
      const signatureKey = 'mathcraft-browser-signature';

      async function browserSignature() {
        let value = localStorage.getItem(signatureKey);
        if (!value) {
          value = crypto.randomUUID();
          localStorage.setItem(signatureKey, value);
        }
        const bytes = new TextEncoder().encode(value);
        const digest = await crypto.subtle.digest('SHA-256', bytes);
        return [...new Uint8Array(digest)].map((byte) => byte.toString(16).padStart(2, '0')).join('');
      }

      async function start() {
        const signature = await browserSignature();
        const response = await fetch(`auth-api.php?signature=${signature}`);
        const current = await response.json();
        const messages = {
          not_requested: 'No request has been submitted from this browser.',
          pending: 'Your request is waiting for administrator approval.',
          approved: 'Access approved. The launcher is ready.',
          denied: 'This request was denied. You can submit it again.'
        };
        const render = (status) => {
          statusElement.textContent = messages[status] || 'Unable to read the request status.';
          actionButton.hidden = status === 'approved';
          nameInput.disabled = status === 'approved' || status === 'pending';
          actionButton.textContent = status === 'denied' ? 'Request again' : 'Request access';
        };
        const authorize = async () => {
          await fetch('auth-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'authorize', signature })
          });
        };
        render(current.status);
        if (current.status === 'approved') await authorize();
        actionButton.addEventListener('click', async () => {
          actionButton.disabled = true;
          const request = await fetch('auth-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'request', signature, name: nameInput.value })
          });
          const result = await request.json();
          render(result.status);
          if (result.status === 'approved') await authorize();
          actionButton.disabled = false;
        });
      }

      start().catch(() => {
        statusElement.textContent = 'The access service is unavailable.';
        actionButton.disabled = true;
      });
    </script>
  </body>
</html>