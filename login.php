<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión</title>
</head>
<body>
  <h2>Iniciar sesión</h2>
  <form id="loginForm">
    <input type="text" name="usuario" id="usuario" placeholder="Usuario o correo" required>
    <input type="password" name="password" id="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar sesión</button>
  </form>

  <script>
    document.getElementById('loginForm').onsubmit = function(e) {
      e.preventDefault();
      const usuario = document.getElementById('usuario').value.trim();
      const password = document.getElementById('password').value;

      const formData = new FormData();
      formData.append('usuario', usuario);
      formData.append('password', password);

      fetch('/Sumaqvida/api/login_usuario.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('¡Bienvenido, ' + data.nombre + '!');
          window.location.href = '/Sumaqvida/index.html';
        } else {
          alert('Error: ' + (data.error || 'No se pudo iniciar sesión'));
        }
      });
    };
  </script>
</body>
</html>
