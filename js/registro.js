const form = document.getElementById('form-registro');
const tabla = document.querySelector('#tabla-historial tbody');
const metaDiaria = document.getElementById('metaDiaria');
let registrosHoy = 0;

const glucosaActual = document.getElementById('glucosaActual');
const glucosaEstado = document.getElementById('glucosaEstado');
const ultimaComida = document.getElementById('ultimaComida');
const recomendaciones = document.getElementById('recomendaciones');
const cardGlucosa = document.getElementById('cardGlucosa');
const cardActividad = document.getElementById('cardActividad');
const cardRegistros = document.getElementById('cardRegistros');

const recetas = {
  baja: {
    nombre: "Yogurt natural con avena y plátano",
    descripcion: "Ideal para elevar suavemente el nivel de glucosa.",
    link: "#"
  },
  normal: {
    nombre: "Sopa de verduras con pollo",
    descripcion: "Comida balanceada y segura para mantener niveles estables.",
    link: "#"
  },
  alta: {
    nombre: "Ensalada de garbanzos con espinaca",
    descripcion: "Ayuda a evitar picos de glucosa, rica en fibra.",
    link: "#"
  }
};

function sugerirRecetaPorGlucosa(glucosa) {
  let tipo = "";
  if (glucosa < 70) tipo = "baja";
  else if (glucosa <= 130) tipo = "normal";
  else tipo = "alta";

  const receta = recetas[tipo];
  Swal.fire({
    title: ' Receta sugerida',
    html: `<b>${receta.nombre}</b><br><small>${receta.descripcion}</small>`,
    icon: 'info',
  });
}

function actualizarProgreso() {
  if (!metaDiaria) return;
  if (registrosHoy >= 3) {
    metaDiaria.innerHTML = '✅ ¡Meta cumplida! Buen trabajo hoy.';
  } else {
    metaDiaria.innerHTML = `📊 Registros hasta ahora: ${registrosHoy}. Sigue registrando tus comidas.`;
  }
}

function mostrarResumenDia() {
  Swal.fire({
    title: '📅 Resumen del día',
    html: `
      <p><b>Registros de comidas:</b> ${registrosHoy}</p>
      <p><b>Último nivel de glucosa:</b> ${glucosaActual?.textContent || '--'}</p>
      <p><b>Estado:</b> ${glucosaEstado?.textContent || '--'}</p>
    `,
    icon: 'info'
  });
}

if (form && tabla) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const data = new FormData(form);
    const glucosa = parseInt(data.get('glucosa'));
    const insulina = parseInt(data.get('insulina'));
    const comida = data.get('comida');
    const actividad = data.get('actividad');
    const observaciones = data.get('observaciones') || '';
    const fecha = new Date().toISOString().slice(0, 10); // YYYY-MM-DD

    // Enviar al backend para guardar en la base de datos
    const formData = new FormData();
    formData.append('glucosa', glucosa);
    formData.append('insulina', insulina);
    formData.append('comida', comida);
    formData.append('actividad', actividad);
    formData.append('observaciones', observaciones);
    formData.append('fecha', fecha);
    fetch('/api/guardar_registro_diario.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(resp => {
      if (!resp.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: resp.error || 'No se pudo guardar en la base de datos.' });
        return;
      }
      tabla.innerHTML += `
        <tr>
          <td>${fecha}</td>
          <td>${glucosa} mg/dL</td>
          <td>${insulina} U</td>
          <td>${comida}</td>
          <td>${actividad}</td>
        </tr>`;
      if (glucosaActual) glucosaActual.textContent = glucosa + ' mg/dL';
      if (ultimaComida) ultimaComida.textContent = comida;
      registrosHoy++;
      if (cardGlucosa) cardGlucosa.textContent = `${glucosa} mg/dL`;
      if (cardActividad) cardActividad.textContent = actividad;
      if (cardRegistros) cardRegistros.textContent = registrosHoy;
      actualizarProgreso();
      if (glucosaEstado && recomendaciones) {
        if (actividad === "Sí") {
          recomendaciones.textContent += " Realizaste actividad física, eso puede ayudar a estabilizar tu glucosa.";
        } else if (actividad === "No") {
          recomendaciones.textContent += " No hubo actividad física, considera incorporar ejercicio moderado.";
        }
        if (glucosa < 70) {
          glucosaEstado.textContent = "⚠️ Muy baja";
          recomendaciones.className = 'alert alert-warning';
          recomendaciones.textContent = "Nivel bajo. Come algo con azúcar y monitorea tu estado.";
          if (insulina >= 4) recomendaciones.textContent += " ⚠️ Dosis alta de insulina con glucosa baja.";
        } else if (glucosa <= 130) {
          glucosaEstado.textContent = "✅ Normal";
          recomendaciones.className = 'alert alert-success';
          recomendaciones.textContent = "Todo en orden. Sigue tu rutina con normalidad.";
          if (insulina === 0) recomendaciones.textContent += " No se usó insulina, lo cual fue adecuado.";
        } else {
          glucosaEstado.textContent = "⚠️ Alta";
          recomendaciones.className = 'alert alert-danger';
          recomendaciones.textContent = "Nivel alto. Evita carbohidratos simples y considera aplicar insulina si es habitual.";
          if (insulina === 0) recomendaciones.textContent += " ⚠️ No se aplicó insulina.";
        }
      }
      form.reset();
      Swal.fire({
        icon: 'success',
        title: 'Registro exitoso',
        text: 'Tus datos se han guardado correctamente.',
        timer: 2000,
        showConfirmButton: false
      });
      sugerirRecetaPorGlucosa(glucosa);
    });
  });
}

// Exportar la función si se usa modularmente
window.mostrarResumenDia = mostrarResumenDia;
window.verTodasLasRecetas = function () {
  Swal.fire({
    title: ' Recetas para personas con diabetes',
    html: `
      <ul style="text-align:left">
        <li><b>Glucosa baja:</b> ${recetas.baja.nombre}</li>
        <li><b>Glucosa normal:</b> ${recetas.normal.nombre}</li>
        <li><b>Glucosa alta:</b> ${recetas.alta.nombre}</li>
      </ul>
    `,
    icon: 'info'
  });
};

