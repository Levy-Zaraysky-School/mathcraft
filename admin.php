<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mathcraft approvals</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <main class="admin-shell">
      <header class="admin-header">
        <div><p class="eyebrow">Mathcraft access</p><h1>Approval desk</h1></div>
        <a class="auth-back" href="index.html">Back to launcher</a>
      </header>
      <form class="admin-login" id="admin-login">
        <label for="admin-key">Admin key</label>
        <input id="admin-key" type="password" autocomplete="current-password" required />
        <button class="button-link" type="submit">Load requests</button>
      </form>
      <p class="auth-status" id="admin-status" aria-live="polite"></p>
      <section class="request-list" id="request-list"></section>
    </main>
    <script>
      const login = document.getElementById('admin-login');
      const keyInput = document.getElementById('admin-key');
      const statusElement = document.getElementById('admin-status');
      const list = document.getElementById('request-list');

      async function loadRequests(key) {
        const response = await fetch('auth-requests.php', { headers: { 'X-Admin-Password': key } });
        if (!response.ok) throw new Error('Admin authentication failed.');
        const result = await response.json();
        list.replaceChildren();
        result.requests.forEach((request) => {
          const item = document.createElement('article');
          item.className = 'request-item';
          const details = document.createElement('div');
          const name = document.createElement('strong');
          name.textContent = request.name || 'Unnamed request';
          const status = document.createElement('span');
          status.textContent = `${request.status} | ${request.id}`;
          const userAgent = document.createElement('small');
          userAgent.textContent = request.user_agent || 'User agent unavailable';
          const created = document.createElement('small');
          created.textContent = request.created_at || 'Unknown time';
          details.append(name, status, userAgent, created);
          item.append(details);
          if (request.status === 'pending') {
            const actions = document.createElement('div');
            actions.className = 'request-actions';
            ['approve', 'deny'].forEach((action) => {
              const button = document.createElement('button');
              button.className = action === 'approve' ? 'button-link' : 'button-link danger';
              button.type = 'button';
              button.textContent = action;
              button.addEventListener('click', async () => {
                const update = await fetch('auth-api.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-Admin-Password': key },
                  body: JSON.stringify({ action, id: request.id })
                });
                if (!update.ok) throw new Error('The request could not be updated.');
                await loadRequests(key);
              });
              actions.append(button);
            });
            item.append(actions);
          }
          list.append(item);
        });
        statusElement.textContent = `${result.requests.length} request(s)`;
      }

      login.addEventListener('submit', (event) => {
        event.preventDefault();
        loadRequests(keyInput.value).catch((error) => {
          statusElement.textContent = error.message;
          list.replaceChildren();
        });
      });
    </script>
  </body>
</html>