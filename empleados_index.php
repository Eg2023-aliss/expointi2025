<?php
session_start();
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('fpdf/fpdf.php');
require_once 'conexion.php';

// 🔐 Verificar sesión


// 🔗 Obtener conexión activa (usa la nube por defecto)
try {
  $pdo = getPDO();
  $stmt = $pdo->query("SELECT id, nombre_completo, area, cargo FROM empleados ORDER BY id ASC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("❌ Error al obtener empleados: " . $e->getMessage());
}
?>
  <!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Áreas de la Empresa</title>
<link rel="icon" href="logss.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }

body {
  background: linear-gradient(135deg, #002b5c, #004080, #007bff);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  color: #fff;
  overflow-x: hidden;
  position: relative;
}

/* Ondas decorativas del fondo */
body::before, body::after {
  content: "";
  position: absolute;
  width: 700px;
  height: 700px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  animation: girar 25s linear infinite;
  pointer-events: none;
  z-index: 5;
}
body::before { top: -250px; left: -250px; }
body::after { bottom: -250px; right: -250px; }
@keyframes girar { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Navbar */
.navbar { position: fixed; top: 20px; left: 40px; display: flex; gap: 15px; z-index: 30; }
.navbar a {
  text-decoration: none;
  background: rgba(255,255,255,0.15);
  color: white;
  padding: 10px 20px;
  border-radius: 30px;
  font-weight: 500;
  transition: 0.3s;
  backdrop-filter: blur(10px);
}
.navbar a:hover { background: rgba(255,255,255,0.35); transform: scale(1.05); }

/* Logo */
.logo { position: fixed; top: 20px; right: 35px; width: 70px; opacity: 0.9; z-index: 30; }

/* Título */
header { text-align: center; margin-top: 120px; margin-bottom: 30px; text-shadow: 2px 2px 8px rgba(0,0,0,0.4); }
header h1 { font-size: 2.8rem; letter-spacing: 2px; }

/* Botones de áreas */
.area-container { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 40px; }
.area-btn {
  background: white;
  color: #003366;
  padding: 15px 25px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  transition: all 0.3s;
  position: relative;
  z-index: 40;
}
.area-btn:hover { background: #003366; color: white; transform: translateY(-4px); }

/* Tabla de empleados */
#empleadosArea {
width: 90%;
background: rgba(255,255,255,0.1);
border-radius: 10px;
padding: 20px;
backdrop-filter: blur(8px);
box-shadow: 0 4px 15px rgba(0,0,0,0.3);
margin-bottom: 40px;
position: relative;
z-index: 20;
}
#empleadosArea h2 { text-align: center; margin-bottom: 15px; }
table { width: 100%; border-collapse: collapse; color: #fff; }
table th, table td { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: left; }
table th { background: rgba(255,255,255,0.2); color: #fff; }
table tr:hover { background: rgba(255,255,255,0.1); }

/* Botones generales */
.btn {
  background: #ffffff;
  color: #003366;
  border: none;
  padding: 5px 15px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: 0.3s;
  position: relative;
  z-index: 40;
}
.btn:hover { background: #003366; color: white; }

/* Modal calendario */
#calendarModal {
display: none;
position: fixed;
top: 0; left: 0;
width: 100%; height: 100%;
background: rgba(0,0,0,0.8);
justify-content: center;
align-items: center;
z-index: 2000;
}

#calendarBox {
background: linear-gradient(135deg, #ffffff, #f0f4ff);
color: #002b5c;
padding: 25px;
border-radius: 15px;
width: 90%;
max-width: 900px;
box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

/* Botones dentro del modal */
#calendarBox .btn {
background: #004080;
color: white;
border: none;
padding: 8px 15px;
border-radius: 25px;
cursor: pointer;
font-weight: 500;
transition: 0.3s;
}
#calendarBox .btn:hover { background: #007bff; transform: scale(1.05); }

/* Selector de mes y año */
#calendarBox select {
padding: 6px 10px;
border-radius: 8px;
border: 1px solid #004080;
background: white;
color: #003366;
font-weight: 500;
margin: 0 5px;
}

/* Botón de descarga */
#btnDescargar {
background: #007bff;
color: white;
border: none;
padding: 10px 20px;
border-radius: 30px;
cursor: pointer;
transition: 0.3s;
font-weight: 600;
margin-bottom: 10px;
}
#btnDescargar:hover { background: #0056b3; }

/* Estilo calendario FullCalendar */
#calendar {
background: white;
border-radius: 10px;
padding: 10px;
margin-top: 15px;
box-shadow: 0 2px 8px rgba(0,0,0,0.2);
height: 350px; /* tamaño más compacto */
}

/* Cabecera (mes y controles) */
.fc-toolbar-title { color: #002b5c !important; font-weight: 700; text-transform: capitalize; }
.fc-button {
  background: #004080 !important;
  border: none !important;
  color: white !important;
  border-radius: 8px !important;
  transition: 0.3s;
}
.fc-button:hover { background: #007bff !important; }

/* Celdas de días */
.fc-daygrid-day { background: #f9fbff; border: 1px solid #e1e8ff; }
.fc-day-today { background: #e1edff !important; border: 2px solid #004080 !important; }
.fc-daygrid-day-number { color: #003366; font-weight: 600; }
.fc-event {
  border: none !important;
  color: white !important;
  border-radius: 5px;
  padding: 2px 4px;
  font-size: 0.8em;
}
/* Colores de eventos */
.fc-event[style*="background-color: #2ecc71"] { background-color: #2ecc71 !important; }
.fc-event[style*="background-color: #e74c3c"] { background-color: #e74c3c !important; }
.btn-small {
  background: #1f3b73;
  color: white;
  border-radius: 5px;
  padding: 4px 8px;
  font-size: 12px;
  margin-left: 10px;
  text-decoration: none;
  transition: 0.3s;
}
.btn-small:hover {
  background: #2c56b1;
}
</style>
</head>
<body>

<!-- Menú superior -->
<div class="navbar">
<a href="login.php">Login</a>
<a href="empleados_index.php">Empleados</a>

<a href="login.php">Salir</a>
</div>

<!-- Logo -->
<img src="logss.png" alt="Logo AX" class="logo">

<!-- Contenido principal -->
<header><h1>ÁREAS DE LA EMPRESA</h1></header>

<div class="area-container">
<button class="area-btn" onclick="mostrarArea('Dirección y Administración General')">1️⃣ Dirección y Administración General</button>
<button class="area-btn" onclick="mostrarArea('Finanzas y Administración')">2️⃣ Finanzas y Administración</button>
<button class="area-btn" onclick="mostrarArea('Recursos Humanos')">3️⃣ Recursos Humanos</button>
<button class="area-btn" onclick="mostrarArea('Ventas y Atención Comercial')">4️⃣ Ventas y Atención Comercial</button>
<button class="area-btn" onclick="mostrarArea('Infraestructura y Redes')">5️⃣ Infraestructura y Redes</button>
</div>

<div id="empleadosArea" style="display:none;">
<h2 id="tituloArea"></h2>
<table>
<thead>
<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Puesto</th><th>Fecha</th><th>Opciones</th></tr>
</thead>
<tbody id="tbodyArea"></tbody>
</table>
</div>

<!-- Modal calendario -->
<div id="calendarModal">
<div id="calendarBox">
<button class="btn" onclick="cerrarCalendario()">❌ Cerrar</button>
<div style="margin:10px 0;">
<label>Mes:</label>
<select id="selectMes"></select>
<label>Año:</label>
<select id="selectAnio"></select>
</div>
<button id="btnDescargar">⬇️ Descargar Asistencia</button>
<div id="calendar"></div>
</div>
</div>

<script>
let calendar, empleadoActual = 0;

async function mostrarArea(area){
  if(!area) return;
  document.getElementById('empleadosArea').style.display='block';
  // Aquí añadí solo la inserción del botón solicitado (para todas las áreas)
  tituloArea.innerHTML = 'Empleados del área: ' + area +
  ' <a href="formulario.php" class="btn-small">Registrar Encargado</a>';
  try {
    const res=await fetch('?action=list&area='+encodeURIComponent(area));
    const data=await res.json();
    if(!Array.isArray(data)) {
      document.getElementById('tbodyArea').innerHTML = '<tr><td colspan="6">No hay datos</td></tr>';
      return;
    }
    document.getElementById('tbodyArea').innerHTML=data.map(e=>`
    <tr>
    <td>${e.id}</td>
    <td>${e.nombre}</td>
    <td>${e.correo}</td>
    <td>${e.puesto}</td>
    <td>${e.fecha}</td>
    <td>
    <button class="btn" onclick="downloadCarnet(${e.id})">Carnet</button>
    <button class="btn" onclick="curriculum(${e.id})">Curriculum</button>
    <button class="btn" onclick="verAsistencia(${e.id})">Asistencia</button>

    </td>
    </tr>`).join('');
  } catch(err) {
    console.error(err);
    document.getElementById('tbodyArea').innerHTML = '<tr><td colspan="6">Error cargando empleados</td></tr>';
  }
}

function downloadCarnet(id){ if(!id) return; window.location='?action=carnet&id='+id; }

// función solicitada por tu HTML: descarga el CV
function curriculum(id){
  if(!id) return;
  window.location='?action=curriculum&id='+id;
}

function inicializarSelectores(){
  const selectMes=document.getElementById('selectMes'),
  selectAnio=document.getElementById('selectAnio');
  const meses=["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
  selectMes.innerHTML=meses.map((m,i)=>`<option value="${i+1}">${m}</option>`).join('');
  const anioAct=new Date().getFullYear();
  selectAnio.innerHTML=Array.from({length:6},(_,i)=>anioAct-3+i).map(a=>`<option value="${a}">${a}</option>`).join('');
  selectMes.value=new Date().getMonth()+1;
  selectAnio.value=anioAct;
}

async function verAsistencia(id){
  if(!id) return;
  empleadoActual=id;
  document.getElementById('calendarModal').style.display='flex';
  inicializarSelectores();
  try {
    const res=await fetch('?action=ver_asistencia&id='+id);
    const eventos=await res.json();
    const eventosCal=eventos.map(e=>({title:e.estado,start:e.fecha,color:(String(e.estado).toUpperCase()==='FALTA'?'#e74c3c':'#2ecc71')}));
    if(calendar) calendar.destroy();
    calendar=new FullCalendar.Calendar(document.getElementById('calendar'),{
      initialView:'dayGridMonth',
      height:350, // tamaño reducido
      locale:'es',
      events:eventosCal,
      editable:true,
      dateClick:async info=>{
        const estado=prompt("Ingrese estado (ASISTIÓ / FALTA / INCAPACIDAD):","ASISTIÓ");
        if(!estado) return;
        await fetch('?action=editar_asistencia',{
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body:JSON.stringify({id,estado,observacion:'Modificado manualmente'})
        });
        alert('Actualizado');
        verAsistencia(id);
      }
    });
    calendar.render();
  } catch(err) {
    console.error(err);
    alert('Error cargando asistencia');
  }
}

function cerrarCalendario(){ document.getElementById('calendarModal').style.display='none'; }

document.getElementById('btnDescargar').onclick=()=>{
  const m=document.getElementById('selectMes').value;
  const a=document.getElementById('selectAnio').value;
  if(!empleadoActual) { alert('Seleccione primero un empleado'); return; }
  window.location=`?action=descargar_asistencia&id=${empleadoActual}&mes=${m}&anio=${a}`;
};
</script>

</body>
</html>
