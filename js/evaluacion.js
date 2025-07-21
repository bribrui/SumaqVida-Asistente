const simulador = document.getElementById('form-evaluador');

if (simulador) {
  simulador.addEventListener('submit', function (e) {
    e.preventDefault();
    const data = new FormData(simulador);
    const glucosa = parseInt(data.get('glucosa'));
    const comida = data.get('comida');
    const insulina = parseInt(data.get('insulina'));

    let icon = 'info';
    let mensaje = "";

    if (glucosa > 130 && insulina === 0) {
      mensaje = "Tu glucosa está alta y no planeas aplicar insulina. La comida puede empeorar tu estado.";
      icon = 'error';
    } else if (glucosa < 70 && insulina >= 4) {
      mensaje = "Tu glucosa está baja y planeas usar insulina. Riesgo de hipoglucemia.";
      icon = 'warning';
    } else if (glucosa >= 70 && glucosa <= 130) {
      mensaje = "Nivel adecuado. Puedes comer con precaución. Revisa la cantidad de carbohidratos.";
      icon = 'success';
    } else {
      mensaje = "Evalúa con tu médico si no estás seguro.";
      icon = 'info';
    }

    Swal.fire({
      icon: icon,
      title: 'Evaluación',
      text: mensaje
    });
  });
}
