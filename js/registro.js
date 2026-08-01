const form = document.getElementById("form-registro");
const tabla = document.querySelector("#tabla-historial tbody");

const cardGlucosa = document.getElementById("cardGlucosa");
const cardActividad = document.getElementById("cardActividad");
const cardRegistros = document.getElementById("cardRegistros");

const glucosaActual = document.getElementById("glucosaActual");
const glucosaEstado = document.getElementById("glucosaEstado");
const ultimaComida = document.getElementById("ultimaComida");
const recomendaciones = document.getElementById("recomendaciones");

const metaDiaria = document.getElementById("metaDiaria");
const barraProgreso = document.getElementById("barraProgreso");

const btnBorrarHistorial = document.getElementById("btnBorrarHistorial");


// VARIABLES
let registros = JSON.parse(localStorage.getItem("registroDiario")) || [];
let registrosHoy = registros.length;

// =======================
// RECETAS
// =======================

const recetas = {

    baja: {

        nombre: "Yogurt natural con avena y plátano",

        descripcion:
            "Ideal para elevar lentamente la glucosa y aportar energía."

    },

    normal: {

        nombre: "Sopa de verduras con pollo",

        descripcion:
            "Comida equilibrada para mantener niveles saludables."

    },

    alta: {

        nombre: "Ensalada de garbanzos con espinaca",

        descripcion:
            "Rica en fibra y baja en azúcares simples."

    }

};

// =======================
// CARGAR TABLA
// =======================

function cargarTabla() {

    tabla.innerHTML = "";

    registros.forEach(registro => {

        tabla.innerHTML += `

        <tr>

            <td>${registro.fecha}</td>

            <td>${registro.glucosa} mg/dL</td>

            <td>${registro.insulina} U</td>

            <td>${registro.comida}</td>

            <td>${registro.actividad}</td>

        </tr>

        `;

    });

}

// =======================
// ACTUALIZAR TARJETAS
// =======================

function actualizarTarjetas() {

    cardRegistros.textContent = registros.length;

    if (registros.length === 0) {

        cardGlucosa.textContent = "--";
        cardActividad.textContent = "--";
        ultimaComida.textContent = "--";

        glucosaActual.textContent = "--";
        glucosaEstado.textContent = "Sin registros";

        recomendaciones.className = "alert alert-light";

        recomendaciones.textContent =
            "Registra tu primera medición para recibir recomendaciones.";

        return;

    }

    const ultimo = registros[registros.length - 1];

    cardGlucosa.textContent = ultimo.glucosa + " mg/dL";
    cardActividad.textContent = ultimo.actividad;
    ultimaComida.textContent = ultimo.comida;

    glucosaActual.textContent = ultimo.glucosa + " mg/dL";

}

// =======================
// META DIARIA
// =======================

function actualizarProgreso() {

    registrosHoy = registros.length;

    let porcentaje = Math.min((registrosHoy / 3) * 100, 100);

    barraProgreso.style.width = porcentaje + "%";

    barraProgreso.textContent = Math.round(porcentaje) + "%";

    if (registrosHoy >= 3) {

        metaDiaria.innerHTML =
            "✅ ¡Meta cumplida! Excelente trabajo hoy.";

        barraProgreso.classList.remove("bg-warning");
        barraProgreso.classList.add("bg-success");

    } else {

        metaDiaria.innerHTML =
            `📊 Registros realizados: ${registrosHoy} de 3`;

        barraProgreso.classList.remove("bg-success");
        barraProgreso.classList.add("bg-warning");

    }

}

// =======================
// RECOMENDACIÓN DE RECETA
// =======================

function sugerirReceta(glucosa) {

    let receta;

    if (glucosa < 70) {

        receta = recetas.baja;

    }

    else if (glucosa <= 130) {

        receta = recetas.normal;

    }

    else {

        receta = recetas.alta;

    }

    Swal.fire({

        title: "🍲 Receta recomendada",

        html: `

        <h5>${receta.nombre}</h5>

        <p>${receta.descripcion}</p>

        `,

        icon: "info"

    });

}

// =======================
// RESUMEN DEL DÍA
// =======================

function mostrarResumenDia() {

    Swal.fire({

        title: "📅 Resumen del día",

        html: `

        <p><b>Registros:</b> ${registros.length}</p>

        <p><b>Última glucosa:</b> ${cardGlucosa.textContent}</p>

        <p><b>Última comida:</b> ${ultimaComida.textContent}</p>

        <p><b>Actividad:</b> ${cardActividad.textContent}</p>

        `,

        icon: "info"

    });

}

// =======================
// INICIALIZAR
// =======================

cargarTabla();
actualizarTarjetas();
actualizarProgreso();
/* ==========================================================
   PARTE 2B
   Guardar registros y actualizar interfaz
========================================================== */

if (form) {

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const datos = new FormData(form);

        const nuevoRegistro = {

            fecha: new Date().toLocaleDateString("es-PE"),

            glucosa: parseInt(datos.get("glucosa")),

            insulina: parseInt(datos.get("insulina")),

            comida: datos.get("comida"),

            actividad: datos.get("actividad"),

            observaciones: datos.get("observaciones") || ""

        };

        // =========================
        // GUARDAR EN LOCALSTORAGE
        // =========================

        registros.push(nuevoRegistro);

        localStorage.setItem(
            "registroDiario",
            JSON.stringify(registros)
        );

        // =========================
        // ACTUALIZAR TABLA
        // =========================

        tabla.innerHTML += `

        <tr>

            <td>${nuevoRegistro.fecha}</td>

            <td>${nuevoRegistro.glucosa} mg/dL</td>

            <td>${nuevoRegistro.insulina} U</td>

            <td>${nuevoRegistro.comida}</td>

            <td>${nuevoRegistro.actividad}</td>

        </tr>

        `;

        // =========================
        // ACTUALIZAR TARJETAS
        // =========================

        cardGlucosa.textContent =
            nuevoRegistro.glucosa + " mg/dL";

        cardActividad.textContent =
            nuevoRegistro.actividad;

        cardRegistros.textContent =
            registros.length;

        ultimaComida.textContent =
            nuevoRegistro.comida;

        glucosaActual.textContent =
            nuevoRegistro.glucosa + " mg/dL";

        // =========================
        // ESTADO DE LA GLUCOSA
        // =========================

        if (nuevoRegistro.glucosa < 70) {

            glucosaEstado.textContent =
                "⚠️ Glucosa baja";

            recomendaciones.className =
                "alert alert-warning";

            recomendaciones.innerHTML = `
                <strong>Recomendación:</strong><br>
                Consume una fuente rápida de carbohidratos
                como jugo, fruta o caramelos y vuelve
                a medir tu glucosa en 15 minutos.
            `;

        }

        else if (nuevoRegistro.glucosa <= 130) {

            glucosaEstado.textContent =
                "✅ Glucosa normal";

            recomendaciones.className =
                "alert alert-success";

            recomendaciones.innerHTML = `
                <strong>¡Excelente!</strong><br>
                Tus niveles son adecuados.
                Continúa con una alimentación saludable
                y mantén tu actividad física.
            `;

        }

        else {

            glucosaEstado.textContent =
                "⚠️ Glucosa alta";

            recomendaciones.className =
                "alert alert-danger";

            recomendaciones.innerHTML = `
                <strong>Atención:</strong><br>
                Evita alimentos con alto contenido de azúcar.
                Mantente hidratado y sigue las indicaciones
                de tu profesional de salud.
            `;

        }

        // =========================
        // ACTIVIDAD FÍSICA
        // =========================

        if (nuevoRegistro.actividad === "Ninguna") {

            recomendaciones.innerHTML += `
                <hr>
                🚶 Intenta realizar una caminata
                de al menos 20 minutos.
            `;

        }

        if (nuevoRegistro.actividad === "Caminata ligera") {

            recomendaciones.innerHTML += `
                <hr>
                👏 ¡Buen trabajo!
                Toda actividad física ayuda a controlar
                la glucosa.
            `;

        }

        if (nuevoRegistro.actividad === "Ejercicio moderado") {

            recomendaciones.innerHTML += `
                <hr>
                💪 Excelente elección.
                Mantén una buena hidratación.
            `;

        }

        if (nuevoRegistro.actividad === "Ejercicio intenso") {

            recomendaciones.innerHTML += `
                <hr>
                🏃 Muy bien.
                Recuerda monitorear tu glucosa
                después del ejercicio intenso.
            `;

        }

        // =========================
        // ACTUALIZAR META
        // =========================

        actualizarProgreso();

        // =========================
        // LIMPIAR FORMULARIO
        // =========================

        form.reset();

        // =========================
        // ALERTA
        // =========================

        Swal.fire({

            icon: "success",

            title: "¡Registro guardado!",

            text:
                "Tu información se registró correctamente.",

            timer: 1800,

            showConfirmButton: false

        });

        // =========================
        // RECETA
        // =========================

        sugerirReceta(
            nuevoRegistro.glucosa
        );

    });

}
/* ==========================================================
   PARTE 2C
   Extras: borrar historial, logros y funciones auxiliares
========================================================== */

// ===================================
// BOTÓN BORRAR HISTORIAL
// ===================================

if (btnBorrarHistorial) {

    btnBorrarHistorial.addEventListener("click", () => {

        Swal.fire({

            title: "¿Borrar historial?",

            text: "Se eliminarán todos los registros guardados.",

            icon: "warning",

            showCancelButton: true,

            confirmButtonText: "Sí, borrar",

            cancelButtonText: "Cancelar",

            confirmButtonColor: "#e0356d"

        }).then((result) => {

            if (!result.isConfirmed) return;

            localStorage.removeItem("registroDiario");

            registros = [];
            registrosHoy = 0;

            tabla.innerHTML = "";

            cardGlucosa.textContent = "--";
            cardActividad.textContent = "--";
            cardRegistros.textContent = "0";

            ultimaComida.textContent = "--";

            glucosaActual.textContent = "--";

            glucosaEstado.textContent =
                "Sin registros";

            recomendaciones.className =
                "alert alert-light";

            recomendaciones.textContent =
                "Registra tu primera medición para recibir recomendaciones.";

            actualizarProgreso();

            Swal.fire({

                icon: "success",

                title: "Historial eliminado",

                timer: 1500,

                showConfirmButton: false

            });

        });

    });

}

// ===================================
// SISTEMA DE LOGROS
// ===================================

function revisarLogros() {

    const cantidad = registros.length;

    if (cantidad === 1) {

        Swal.fire({

            icon: "success",

            title: "🏅 Primer registro",

            text: "¡Buen comienzo! Sigue monitoreando tu salud."

        });

    }

    else if (cantidad === 3) {

        Swal.fire({

            icon: "success",

            title: "🎯 Meta diaria",

            text: "Has alcanzado 3 registros."

        });

    }

    else if (cantidad === 7) {

        Swal.fire({

            icon: "success",

            title: "⭐ Constancia",

            text: "Ya realizaste 7 registros."

        });

    }

    else if (cantidad === 15) {

        Swal.fire({

            icon: "success",

            title: "🏆 Excelente",

            text: "¡15 registros completados!"

        });

    }

}

// ===================================
// TENDENCIA DE GLUCOSA
// ===================================

function revisarTendencia() {

    if (registros.length < 2) return;

    const ultimo = registros[registros.length - 1].glucosa;
    const anterior = registros[registros.length - 2].glucosa;

    if (ultimo > anterior) {

        console.log("📈 Tendencia: subiendo");

    }

    else if (ultimo < anterior) {

        console.log("📉 Tendencia: bajando");

    }

    else {

        console.log("➡ Tendencia: estable");

    }

}

// ===================================
// MENSAJES MOTIVACIONALES
// ===================================

function mensajeMotivacional() {

    const mensajes = [

        "💚 Cada registro te ayuda a conocer mejor tu salud.",

        "🌱 Los pequeños hábitos generan grandes cambios.",

        "💪 Sigue así, cuidar tu salud vale la pena.",

        "🥗 Alimentarte bien también es una forma de quererte.",

        "🚶 Un poco de actividad física cada día hace la diferencia."

    ];

    const mensaje =
        mensajes[Math.floor(Math.random() * mensajes.length)];

    setTimeout(() => {

        Swal.fire({

            icon: "info",

            title: "Mensaje del día",

            text: mensaje,

            confirmButtonColor: "#e0356d"

        });

    }, 800);

}

// ===================================
// ACTUALIZAR TODO
// ===================================

function actualizarTodo() {

    cargarTabla();

    actualizarTarjetas();

    actualizarProgreso();

    revisarTendencia();

    revisarLogros();

}

// ===================================
// INICIALIZACIÓN
// ===================================

document.addEventListener("DOMContentLoaded", () => {

    actualizarTodo();

});

// ===================================
// EXPORTAR FUNCIONES
// ===================================

window.mostrarResumenDia = mostrarResumenDia;
window.actualizarTodo = actualizarTodo;
