<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de usuario</title>
</head>
<body>
  <h2>Registro de usuario</h2>
  <form id="registroForm">
    <input type="text" name="nombre" id="nombre" placeholder="Nombre completo" required>
    <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario" required>
    <input type="email" name="email" id="email" placeholder="Correo electrónico" required>
    <input type="password" name="password" id="password" placeholder="Contraseña" required>
    <input type="password" name="confirmar" id="confirmar" placeholder="Repetir contraseña" required>
    <button type="submit">Registrarse</button>
  </form>

  <script>
    document.getElementById('registroForm').onsubmit = function(e) {
      e.preventDefault();
      const nombre = document.getElementById('nombre').value.trim();
      const usuario = document.getElementById('usuario').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmar = document.getElementById('confirmar').value;

      if (password !== confirmar) {
        alert('Las contraseñas no coinciden');
        return;
      }

      const formData = new FormData();
      formData.append('nombre', nombre);
      formData.append('usuario', usuario);
      formData.append('email', email);
      formData.append('password', password);

      fetch('/Sumaqvida/api/registrar_usuario.php', {
        method: 'POST',
        body: formData
      })
      .then(async res => {
        const text = await res.text();
        try {
          return JSON.parse(text);
        } catch (error) {
          throw new Error(text || 'Respuesta inesperada del servidor.');
        }
      })
      .then(data => {
        if (data.success) {
          alert('¡Registro exitoso!');
          window.location.href = '/Sumaqvida/login.php';
        } else {
          alert('Error: ' + (data.error || 'No se pudo registrar'));
        }
      })
      .catch(err => {
        console.error(err);
        alert('No se pudo completar el registro: ' + err.message);
      });
    };
  </script>
</body>
</html>