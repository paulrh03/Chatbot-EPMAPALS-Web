<?php
header('Content-Type: application/json; charset=utf-8');

/* ========================== CONFIGURACIÓN ========================== */
$DB_HOST = "localhost";
$DB_NAME = "u475379554_epmapals_db";
$DB_USER = "u475379554_admin_epmapals";
$DB_PASS = "\$mZH=ViS\$04";
define('API_KEY', ''); 
/* ========================== CONEXIÓN PDO ========================== */
try {
  $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch(Throwable $e){ ok("Error de conexión."); }
/* --- FUNCIÓN RECUPERADA: EL SABUESO (Obtener Datos Seguros) --- */
function obtener_datos_usuario_seguro($req, $pdo) {
    // 1. Buscamos en la Sesión Maestra (La más confiable)
    $ctx = ctx_get($req, 'ctx_sesion_maestra');
    
    // 2. Si no hay maestra, buscamos en activa
    if (!$ctx) { $ctx = ctx_get($req, 'ctx_sesion_activa'); }

    // 3. Extraemos datos
    $cedula = $ctx['parameters']['cedula'] ?? '';
    $nombre = $ctx['parameters']['nombre'] ?? '';

    // 4. Si falta el nombre pero hay cédula, hacemos una consulta rápida (Backup)
    if (!empty($cedula) && empty($nombre)) {
        try {
            $st = $pdo->prepare("SELECT nombre FROM medidores WHERE cedula = :c LIMIT 1");
            $st->execute([':c' => $cedula]);
            $row = $st->fetch();
            if ($row) { $nombre = $row['nombre']; }
        } catch (Exception $e) { /* Silencio si falla BD */ }
    }

    // 5. Si no hay nombre, ponemos uno genérico
    if (empty($nombre)) { $nombre = 'Vecino'; }

    return ['cedula' => $cedula, 'nombre' => $nombre];
}
/* ========================== LOG ========================== */
$LOG_DIR = __DIR__ . '/logs';
$LOG_FILE = $LOG_DIR . '/fulfillment.log';
if (!is_dir($LOG_DIR)) { @mkdir($LOG_DIR, 0775, true); }
function flog($m){ global $LOG_FILE; @file_put_contents($LOG_FILE, date('[Y-m-d H:i:s] ').$m.PHP_EOL, FILE_APPEND); }

/* ========================== UTILIDADES ========================== */
function ends_with($haystack,$needle){ return substr($haystack,-strlen($needle))===$needle; }
function ok($t){ echo json_encode(['fulfillmentText'=>$t], JSON_UNESCAPED_UNICODE); exit; }
function say($t){ ok($t); }
function frase_random($opciones) { return $opciones[array_rand($opciones)]; }

function formatear_nombre_corto($nombre_completo) {
    if (!$nombre_completo) return "Vecino";
    $partes = explode(' ', trim($nombre_completo));
    $nombre = ucfirst(strtolower($partes[0]));
    return (strlen($nombre) > 1) ? $nombre : "Vecino";
}

function registrar_auditoria($pdo, $cedula, $nombre, $accion, $detalle, $mensaje_usuario) { // <--- TIENE QUE TENER 6 COSAS AQUÍ
    try {
        $mensaje_usuario = substr($mensaje_usuario ?? '', 0, 500); 
        $sql = "INSERT INTO auditoria_bot (fecha, cedula, nombre, accion, detalle, mensaje_usuario) 
                VALUES (NOW(), :c, :n, :a, :d, :m)"; // <--- TIENE QUE TENER :m
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':c' => $cedula, 
            ':n' => $nombre, 
            ':a' => $accion, 
            ':d' => $detalle,
            ':m' => $mensaje_usuario // <--- TIENE QUE TENER ESTO
        ]);
    } catch (Exception $e) { flog("Error Auditoria: " . $e->getMessage()); }
}

function chips_menu_principal() {
  return [[['type'=>'chips','options'=>[
      ['text'=>'💵Consulta de saldo'],
      ['text'=>'🧾Requisitos de tramites'],
      ['text'=>'🕳️ solicitud de limpieza alcantarillado'],
      ['text'=>'💦Reporte de fugas'],
      ['text'=>'🤝 Otros servicios']
  ]]]];
}

/* --- FUNCIÓN DE SEGURIDAD: DETECTAR SALIDA --- */
function verificar_salida($texto, $req) {
    // 1. CORRECCIÓN IMPORTANTE: Si es la acción de rechazar, IGNORAMOS la salida global
    // para dejar que el bloque específico de rechazo haga su trabajo (bloquear).
    $accion_actual = $req['queryResult']['action'] ?? '';
    if ($accion_actual === 'login.rechazar') { return; }

    $t = mb_strtolower(trim($texto ?? ''), 'UTF-8');
    if (strpos($t, 'menú') !== false || strpos($t, 'menu') !== false || strpos($t, 'salir') !== false || strpos($t, 'cancelar') !== false) {
        responder_cancelacion($req);
    }
}

function responder_cancelacion($req) {
    // Intentamos recuperar nombre para personalizar (si existe)
    $ctx = ctx_get($req, 'ctx_sesion_maestra'); 
    $nombre = $ctx['parameters']['nombre'] ?? 'Vecino';
    $nombre_corto = formatear_nombre_corto($nombre);

    $richContent = [
        [
            'type' => 'description',
            'title' => '❌ Solicitud Cancelada',
            'text' => [
                "Entiendo **{$nombre_corto}**, tu solicitud NO fue generada.",
                "",
                "Si deseas consultar algo más, aquí tienes las opciones disponibles:"
            ]
        ],
        [
            'type' => 'chips',
            'options' => [
                ['text'=>'💵Consulta de saldo'],
                ['text'=>'🧾Requisitos de tramites'],
                ['text'=>'🕳️ solicitud de limpieza alcantarillado'],
                ['text'=>'💦Reporte de fugas'],
                ['text'=>'🤝 Otros servicios']
            ]
        ]
    ];

    echo json_encode([
        'fulfillmentText' => "Solicitud cancelada.",
        'fulfillmentMessages' => [[ 'payload' => [ 'richContent' => [$richContent] ] ]],
        'outputContexts' => [
            // MATAMOS TODOS LOS CONTEXTOS POSIBLES
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_cedula', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_telefono', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_referencia', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_descripcion', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_confirm', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_rep_fuga', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_cedula', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_telefono', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_referencia', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_descripcion', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_confirm', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_saldo_retry', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_login_espera', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_login_terminos', 'lifespanCount' => 0],
            // ACTIVAMOS EL MENÚ
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 5],
            // MANTENEMOS SESIÓN VIVA
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999, 'parameters' => $ctx['parameters'] ?? []]
        ]
    ], JSON_UNESCAPED_UNICODE); 
    exit;
}

function card($title, array $lines, array $chips = []) {
  $column = [[ 'type' => 'description', 'title' => $title, 'text' => $lines ]];
  if ($chips) { $column[] = [ 'type' => 'chips', 'options' => array_map(fn($x)=>['text'=>$x], $chips) ]; }
  echo json_encode([
    'fulfillmentText' => implode("\n", $lines),
    'fulfillmentMessages' => [['payload' => ['richContent' => [ $column ] ]]],
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function card_with_ctx($session, $ctxName, array $ctxParams, $title, array $lines, array $chips = [], $lifespan=50) {
  $column = [[ 'type' => 'description', 'title' => $title, 'text' => $lines ]];
  if ($chips) { $column[] = [ 'type' => 'chips', 'options' => array_map(fn($x)=>['text'=>$x], $chips) ]; }
  echo json_encode([
    'fulfillmentText' => implode("\n", $lines),
    'fulfillmentMessages' => [['payload' => ['richContent' => [ $column ] ]]],
    'outputContexts' => [[
      'name' => $session.'/contexts/'.$ctxName,
      'lifespanCount' => $lifespan,
      'parameters' => $ctxParams
    ]]], JSON_UNESCAPED_UNICODE);
  exit;
}

/* ========================== REQUEST ========================== */
$raw = file_get_contents('php://input');
$req = json_decode($raw, true);
if(!is_array($req)){ ok("Petición inválida."); }

// AGREGA ESTA LÍNEA AQUÍ 👇
$texto_usuario = mb_strtolower($req['queryResult']['queryText'] ?? '', 'UTF-8');

$action = trim($req['queryResult']['action'] ?? '');
$actionNorm = strtolower($action);
$intentNorm = strtolower($req['queryResult']['intent']['displayName'] ?? '');


/* ========================== HELPERS ========================== */
function ctx_get(array $req, $suffix){
  foreach (($req['queryResult']['outputContexts'] ?? []) as $c) {
    if (ends_with($c['name'], $suffix)) return $c;
  }
  return null;
}

function gather_params(array $req): array {
  $p = $req['queryResult']['parameters'] ?? [];
  foreach (($req['queryResult']['outputContexts'] ?? []) as $ctx) {
    if (!empty($ctx['parameters']) && is_array($ctx['parameters'])) {
      $p = array_merge($ctx['parameters'], $p);
    }
  }
  return $p;
}

function pick_raw(array $arr, array $keys) {
  foreach ($keys as $k) {
    if (array_key_exists($k, $arr) && $arr[$k] !== '' && $arr[$k] !== null) return $arr[$k];
  }
  return '';
}
function to_scalar($v): string {
  if (is_array($v)) {
    if (isset($v['name'])) return trim((string)$v['name']);
    return trim(($v['given-name'] ?? '') . ' ' . ($v['last-name'] ?? ''));
  }
  return trim((string)$v);
}

/* ============================================================
   💀 EL SILENCIADOR (AQUÍ ESTÁ LA MAGIA)
   Si el usuario tiene bloqueo, el script se detiene AQUÍ.
   ============================================================ */
$ctx_bloqueo = ctx_get($req, 'ctx_bloqueo_total');
if ($ctx_bloqueo) {
    // Silencio absoluto. No responde nada.
    echo json_encode([], JSON_UNESCAPED_UNICODE); 
    exit; 
}

/* ========================== SQL FUNCTIONS (CON AUDITORÍA) ========================== */
function guardar_reporte_fuga(PDO $pdo, array $d): string {
  $tipo_limpio = mb_strtoupper(str_replace('_', ' ', $d['tipo'] ?? 'CALLE'), 'UTF-8');
  $sql = "INSERT INTO reportes_fuga (codigo,tipo,estado,fecha,cedula,nombre,telefono,direccion,referencia,descripcion,numero_medidor) 
          VALUES ('',:tipo,'PENDIENTE',:fecha,:cedula,:nombre,:telefono,:direccion,:referencia,:descripcion,:numero_medidor)";
  $st = $pdo->prepare($sql);
  $st->execute([
    ':tipo' => $tipo_limpio,
    ':fecha' => date('Y-m-d H:i:s'),
    ':cedula' => $d['cedula'] ?? '',
    ':nombre' => $d['nombre'] ?? '',
    ':telefono' => $d['telefono'] ?? '',
    ':direccion' => $d['direccion'] ?? '',
    ':referencia' => $d['referencia'] ?? '',
    ':descripcion' => $d['descripcion'] ?? '',
    ':numero_medidor' => $d['numero_medidor'] ?? '',
  ]);
  $id = (int)$pdo->lastInsertId();
  $codigo = sprintf('RF-%s-%04d', date('Ymd'), $id);
  $pdo->prepare("UPDATE reportes_fuga SET codigo=:c WHERE id=:i")->execute([':c'=>$codigo, ':i'=>$id]);
  
  // REGISTRAR AUDITORÍA
  registrar_auditoria($pdo, $d['cedula'], $d['nombre'], 'REPORTE_FUGA', "Código: $codigo - Tipo: $tipo_limpio");

  return $codigo;
}

function guardar_limpieza(PDO $pdo, array $d): string {
  $sql = "INSERT INTO limpiezas (codigo,estado,fecha,cedula,nombre,telefono,direccion,referencia,descripcion)
          VALUES ('','PENDIENTE',:fecha,:cedula,:nombre,:telefono,:direccion,:referencia,:descripcion)";
  $st = $pdo->prepare($sql);
  $st->execute([
    ':fecha' => date('Y-m-d H:i:s'),
    ':cedula' => $d['cedula'] ?? '',
    ':nombre' => $d['nombre'] ?? '',
    ':telefono' => $d['telefono'] ?? '',
    ':direccion' => $d['direccion'] ?? '',
    ':referencia' => $d['referencia'] ?? '',
    ':descripcion' => $d['descripcion'] ?? ''
  ]);
  $id = (int)$pdo->lastInsertId();
  $codigo = sprintf('LP-%s-%04d', date('Ymd'), $id);
  $pdo->prepare("UPDATE limpiezas SET codigo=:c WHERE id=:i")->execute([':c'=>$codigo, ':i'=>$id]);
  
  // REGISTRAR AUDITORÍA
  registrar_auditoria($pdo, $d['cedula'], $d['nombre'], 'REPORTE_LIMPIEZA', "Código: $codigo");

  return $codigo;
}
/* ⬇️ EL SILENCIADOR (PÉGALO AQUÍ) ⬇️ */
$bloqueado = false;
foreach (($req['queryResult']['outputContexts'] ?? []) as $oc) {
    // Buscamos si existe el contexto de bloqueo
    if (substr($oc['name'], -strlen('ctx_bloqueo_total')) === 'ctx_bloqueo_total') { $bloqueado = true; break; }
}
if ($bloqueado) {
    // Si está bloqueado, enviamos respuesta vacía y MATAMOS el proceso.
    echo json_encode([], JSON_UNESCAPED_UNICODE); 
    exit;
}
/* ⬆️ FIN DEL SILENCIADOR ⬆️ */
/* ============================================================
   BLOQUE 0: SISTEMA DE LOGIN Y AUDITORÍA (TU CÓDIGO ORIGINAL)
   ============================================================ */

/* 1. BIENVENIDA (Se activa al decir 'Hola') */
if ($actionNorm === 'login.bienvenida' || $actionNorm === 'input.welcome') {
    echo json_encode([
        'fulfillmentText' => "Hola.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '👋 Asistente Virtual EPMAPALS', 'text' => [
                "Bienvenido. Para brindarte una mejor atención y seguridad, necesito validar tu identidad.",
                "",
                "Por favor, ingresa tu número de **cédula**:"
            ]]
        ]]]]],
        'outputContexts' => [[
            'name' => $req['session'].'/contexts/ctx_login_espera', 
            'lifespanCount' => 5
        ]]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* 2. VERIFICAR CÉDULA (TU LÓGICA DE VALIDACIÓN INTACTA) */
if ($actionNorm === 'login.verificar') {
    
    // 1. RECUPERAR EL CONTADOR DE INTENTOS
    $ctx = ctx_get($req, 'ctx_login_espera');
    $intentos = (int)($ctx['parameters']['intentos'] ?? 0);

    // 2. LIMPIEZA DE CÉDULA
    $cedula_raw = trim((string)($req['queryResult']['parameters']['cedula'] ?? ''));
    if ($cedula_raw === '') { $cedula_raw = $texto_usuario; }
    $cedula = preg_replace('/[^0-9]/', '', $cedula_raw); 

    // 3. VALIDACIÓN DE FORMATO (Tus mensajes originales)
    $error_msg = "";
    if ($cedula === "") {
        $error_msg = "Lo que escribiste no son números.";
    } elseif (strlen($cedula) !== 10) {
        $error_msg = "La cédula debe tener 10 dígitos exactos.";
    }

    // --- CASO A: ERROR DE FORMATO ---
    if ($error_msg !== "") {
        $intentos++;
        $restantes = 3 - $intentos;
        
        // BLOQUEO
        if ($restantes <= 0) {
            echo json_encode([
                'fulfillmentText' => "Acceso bloqueado.",
                'fulfillmentMessages' => [['payload' => ['richContent' => [[
                    ['type' => 'info', 'title' => '⛔ Acceso Bloqueado', 'subtitle' => "Has excedido el número de intentos permitidos. Por seguridad, el chat se ha cerrado."]
                ]]]]],
                'outputContexts' => [
                    ['name' => $req['session'].'/contexts/ctx_login_espera', 'lifespanCount' => 0],
                    ['name' => $req['session'].'/contexts/ctx_bloqueo_total', 'lifespanCount' => 999]
                ],
                'endInteraction' => true
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'fulfillmentText' => "Formato incorrecto.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'info', 'title' => '🔒 Acceso Restringido', 'subtitle' => $error_msg],
                ['type' => 'description', 'text' => [
                    "Este servicio es exclusivo para **usuarios activos de EPMAPALS**.",
                    "Por favor, ingresa correctamente tu número de cédula para comprobar tu identidad.",
                    "",
                    "⚠️ **Te quedan {$restantes} intentos.**"
                ]]
            ]]]]],
            'outputContexts' => [[
                'name' => $req['session'].'/contexts/ctx_login_espera', 
                'lifespanCount' => 5,
                'parameters' => ['intentos' => $intentos]
            ]]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. BÚSQUEDA EN BASE DE DATOS
    $st = $pdo->prepare("SELECT nombre FROM medidores WHERE cedula = :c LIMIT 1");
    $st->execute([':c' => $cedula]);
    $row = $st->fetch();

    // --- CASO B: CÉDULA NO ENCONTRADA ---
    if (!$row) {
        $intentos++;
        $restantes = 3 - $intentos;
        
        if ($restantes <= 0) {
            echo json_encode([
                'fulfillmentText' => "Acceso bloqueado.",
                'fulfillmentMessages' => [['payload' => ['richContent' => [[
                    ['type' => 'info', 'title' => '⛔ Acceso Bloqueado', 'subtitle' => "Has excedido los intentos. Por favor acércate a nuestras oficinas."]
                ]]]]],
                'outputContexts' => [
                    ['name' => $req['session'].'/contexts/ctx_login_espera', 'lifespanCount' => 0],
                    ['name' => $req['session'].'/contexts/ctx_bloqueo_total', 'lifespanCount' => 999]
                ],
                'endInteraction' => true
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        registrar_auditoria($pdo, $cedula, 'Desconocido', 'LOGIN_FALLIDO', 'Cédula no encontrada');
        
        echo json_encode([
            'fulfillmentText' => "No encontrado.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'info', 'title' => '🔒 Acceso Restringido', 'subtitle' => "Usuario no encontrado"],
                ['type' => 'description', 'text' => [
                    "La cédula **{$cedula}** no aparece en nuestros registros de medidores activos.",
                    "Si te equivocaste, escribe de nuevo tu cédula para comprobar quién eres.",
                    "",
                    "⚠️ **Te quedan {$restantes} intentos.**"
                ]]
            ]]]]],
            'outputContexts' => [[
                'name' => $req['session'].'/contexts/ctx_login_espera', 
                'lifespanCount' => 5,
                'parameters' => ['intentos' => $intentos]
            ]]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // --- CASO C: ÉXITO (AQUÍ ESTÁ EL BOTÓN QUE PEDISTE) ---
    if ($row) { 
        $nombre = $row['nombre'];
        $nombre_corto = formatear_nombre_corto($nombre);
        
        // AGREGAR ESTO 👇
        registrar_auditoria($pdo, $cedula, $nombre, 'LOGIN_EXITOSO', 'Usuario validado correctamente', $texto_usuario);
        
        $output_contexts = [
            [
                'name' => $req['session'].'/contexts/ctx_login_terminos', 
                'lifespanCount' => 5,
                'parameters' => ['cedula_login' => $cedula, 'nombre_login' => $nombre]
            ],
            [
                'name' => $req['session'].'/contexts/ctx_sesion_maestra', 
                'lifespanCount' => 999,
                'parameters' => ['cedula' => $cedula, 'nombre' => $nombre]
            ],
            ['name' => $req['session'].'/contexts/ctx_login_espera', 'lifespanCount' => 0]
        ];

        echo json_encode([
            'fulfillmentText' => "Identidad validada.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                [
                    'type' => 'info',
                    'title' => "¡Hola {$nombre_corto}! ✅",
                    'subtitle' => "Identidad validada."
                ],
                [
                    'type' => 'description',
                    'text' => [
                        "Para continuar, por favor acepte nuestras Políticas de Privacidad y Términos de Uso."
                    ]
                ],
                // TU ENLACE DE TÉRMINOS ORIGINAL
                [
                    'type' => 'button',
                    'icon' => ['type' => 'chevron_right', 'color' => '#FF9800'],
                    'text' => '📄 Leer Políticas de Privacidad y Términos de Uso',
                    'link' => 'https://epmapalstramites.com/services/',
                    'event' => ['name' => '', 'languageCode' => '', 'parameters' => []]
                ],
                // EL BOTÓN DE RECHAZAR QUE FALTABA
                [
                    'type' => 'chips',
                    'options' => [
                        ['text' => '✅ Aceptar y Continuar'], 
                        ['text' => '❌ Rechazar y Salir']
                    ]
                ]
            ]]]]],
            'outputContexts' => $output_contexts
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
/* ==========================================================================
   🔒 TRAMPA DE SEGURIDAD: TÉRMINOS Y CONDICIONES
   Si está en la pantalla de términos y escribe cualquier otra cosa, lo atrapamos.
   ========================================================================== */
// 1. Verificamos si el usuario está en el "limbo" de los términos
$ctx_trampa = ctx_get($req, 'ctx_login_terminos');

// 2. Si el contexto existe, PERO la acción no es ni aceptar ni rechazar...
if ($ctx_trampa && $actionNorm !== 'login.aceptar' && $actionNorm !== 'login.rechazar') {
    
    // Recuperamos datos para no perder el hilo
    $cedula = $ctx_trampa['parameters']['cedula_login'] ?? '';
    $nombre = $ctx_trampa['parameters']['nombre_login'] ?? 'Vecino';
    $nombre_corto = formatear_nombre_corto($nombre);

    // 3. RESPUESTA DE REGAÑO (LOOP INFINITO)
    echo json_encode([
        'fulfillmentText' => "Opción no válida.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'info',
                'title' => '⚠️ Opción no válida',
                'subtitle' => "Por favor {$nombre_corto}, no escribas texto."
            ],
            [
                'type' => 'description',
                'text' => [
                    "Debes seleccionar una de las opciones para continuar:",
                    "¿Aceptas o Rechazas los términos?"
                ]
            ],
            // VOLVEMOS A MOSTRAR LOS BOTONES
            [
                'type' => 'chips',
                'options' => [
                    ['text' => '✅ Aceptar y Continuar'],
                    ['text' => '❌ Rechazar y Salir']
                ]
            ]
        ]]]]],
        'outputContexts' => [
            // MANTENEMOS EL CONTEXTO VIVO (El "Loop")
            [
                'name' => $req['session'].'/contexts/ctx_login_terminos', 
                'lifespanCount' => 5, 
                'parameters' => ['cedula_login' => $cedula, 'nombre_login' => $nombre]
            ],
            // Aseguramos que el menú NO se active por error
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit; // DETENEMOS TODO AQUÍ
}
/* 2.5 RECHAZAR TÉRMINOS (CORREGIDO: SOLO ACTIVO EN LOGIN) */
// Primero verificamos si realmente estamos en la etapa de términos
$ctx_terminos = ctx_get($req, 'ctx_login_terminos');

if (
    $ctx_terminos && // <--- CONDICIÓN CLAVE: Solo si está en términos
    ($actionNorm === 'login.rechazar' || 
    stripos($texto_usuario, 'rechaz') !== false || 
    stripos($texto_usuario, 'no quiero') !== false || 
    stripos($texto_usuario, 'no acepto') !== false ||
    stripos($texto_usuario, 'cancelar') !== false ||
    trim($texto_usuario) === 'no')
) {
    
    // Recuperamos el nombre para despedirnos
    $cedula = $ctx_terminos['parameters']['cedula_login'] ?? '';
    $nombre = $ctx_terminos['parameters']['nombre_login'] ?? 'Vecino';
    $nombreCorto = formatear_nombre_corto($nombre);

    // Auditoría
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $cedula, $nombre, 'LOGIN_RECHAZADO', 'Usuario NO aceptó los términos', $texto_usuario);
    }

    echo json_encode([
        'fulfillmentText' => "Chat finalizado.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'info',
                'title' => '🔒 Chat Finalizado',
                'subtitle' => "Gracias por comunicarte con nosotros, {$nombreCorto}. Que tengas un excelente día."
            ],
            [
                'type' => 'description',
                'text' => [
                    "Has decidido no aceptar los términos.",
                    "Si deseas volver a intentar, por favor **recarga la página**."
                ]
            ]
        ]]]]],
        'outputContexts' => [
            ['name' => $req['session'].'/contexts/ctx_login_terminos', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_bloqueo_total', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* 4. ACEPTAR TÉRMINOS (CORREGIDO Y SIN ERRORES) */
if ($actionNorm === 'login.aceptar' || stripos($texto_usuario, 'aceptar') !== false) {
    
    $ctx = ctx_get($req, 'ctx_login_terminos');
    $cedula = $ctx['parameters']['cedula_login'] ?? '';
    // Recuperamos nombre, si falla usamos "Vecino"
    $nombre = $ctx['parameters']['nombre_logueado'] ?? $ctx['parameters']['nombre_login'] ?? 'Vecino';

    // Validación de seguridad por si acaso
    if (!$cedula) { say("La sesión expiró. Por favor ingresa tu cédula de nuevo."); }

    // --- AQUÍ ESTABA EL ERROR: AHORA ENVIAMOS LOS 6 DATOS ---
    if (function_exists('registrar_auditoria')) {
        // Agregamos $texto_usuario al final 👇
        registrar_auditoria($pdo, $cedula, $nombre, 'LOGIN_EXITOSO', 'Usuario aceptó términos', $texto_usuario);
    }

    // --- RESPUESTA: EL MENÚ PRINCIPAL ---
    echo json_encode([
        'fulfillmentText' => "Bienvenido al menú.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'description',
                'title' => "¡Hola {$nombre}! 👋",
                'text' => [
                    "Soy EMMAP tu asistente virtual.",
                    "¿En qué te puedo ayudar hoy? Selecciona una opción:"
                ]
            ],
            [
                'type' => 'chips',
                'options' => [
                    ['text' => '💵 Consulta de saldo'],
                    ['text' => '🧾 Requisitos de trámites'],
                    ['text' => '🕳️ solicitud de limpieza alcantarillado'],
                    ['text' => '💦 Reporte de fugas'],
                    ['text' => '🤝 Otros servicios']
                ]
            ]
        ]]]]],
        'outputContexts' => [
            // 1. CREAMOS LA SESIÓN ACTIVA
            [
                'name' => $req['session'].'/contexts/ctx_sesion_activa', 
                'lifespanCount' => 999, 
                'parameters' => ['cedula' => $cedula, 'nombre' => $nombre]
            ],
            // 2. RESPALDO EN SESIÓN MAESTRA
            [
                'name' => $req['session'].'/contexts/ctx_sesion_maestra', 
                'lifespanCount' => 999, 
                'parameters' => ['cedula' => $cedula, 'nombre' => $nombre]
            ],
            // 3. ACTIVAMOS EL MENÚ
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 5],
            
            // 4. BORRAMOS EL CONTEXTO DE TÉRMINOS
            ['name' => $req['session'].'/contexts/ctx_login_terminos', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ============================================================
   BLOQUE GLOBAL: MENÚ PRINCIPAL (CORREGIDO: MATA CONTEXTOS ZOMBIES)
   ============================================================ */
if (
    $actionNorm === 'menu.mostrar' || 
    strpos($texto_usuario, 'menú') !== false || 
    strpos($texto_usuario, 'menu') !== false || 
    strpos($texto_usuario, 'inicio') !== false
) {
    // 1. AUDITORÍA (Registramos que pidió menú)
    // Usamos 'obtener_datos...' para evitar errores si no hay sesión
    $u_temp = obtener_datos_usuario_seguro($req, $pdo);
    if (!empty($u_temp['cedula'])) {
        registrar_auditoria($pdo, $u_temp['cedula'], $u_temp['nombre'], 'MENU_PRINCIPAL', 'Usuario solicitó menú (Limpieza de contextos)', $texto_usuario);
    }

    // 2. VERIFICAR SI ESTÁ LOGUEADO
    $usuario = obtener_datos_usuario_seguro($req, $pdo);
    
    // Si NO tiene cédula, lo mandamos al inicio
    if (empty($usuario['cedula'])) { 
        echo json_encode(['followupEventInput' => ['name' => 'WELCOME']], JSON_UNESCAPED_UNICODE); 
        exit; 
    }

    // 3. SI ESTÁ LOGUEADO, MOSTRAMOS EL MENÚ
    $nombre_corto = formatear_nombre_corto($usuario['nombre']);

    echo json_encode([
        'fulfillmentText' => "Menú principal.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'description',
                'title' => "¡Hola {$nombre_corto}! 👋", // Tu saludo preferido
                'text' => [
                    "Soy EMMAP tu asistente virtual.",
                    "¿En qué te puedo ayudar hoy? Selecciona una opción:"
                ]
            ],
            [
                'type' => 'chips',
                'options' => [
                    ['text' => '💵 Consulta de saldo'],
                    ['text' => '🧾 Requisitos de trámites'],
                    ['text' => '🕳️ Solicitud de limpieza alcantarillado'],
                    ['text' => '💦 Reporte de fugas'],
                    ['text' => '🤝 Otros servicios']
                ]
            ]
        ]]]]],
        'outputContexts' => [
            // A. MANTENEMOS LA SESIÓN VIVA
            [
                'name' => $req['session'].'/contexts/ctx_sesion_maestra', 
                'lifespanCount' => 999, 
                'parameters' => ['cedula' => $usuario['cedula'], 'nombre' => $usuario['nombre']]
            ],
            [
                'name' => $req['session'].'/contexts/ctx_sesion_activa', 
                'lifespanCount' => 999, 
                'parameters' => ['cedula' => $usuario['cedula'], 'nombre' => $usuario['nombre']]
            ],
            // B. ACTIVAMOS EL MENÚ
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 5],

            // C. 💀 ZONA DE MUERTE: ELIMINAMOS TODOS LOS CONTEXTOS PENDIENTES 💀
            // Fugas
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_telefono', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_referencia', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_descripcion', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_confirm', 'lifespanCount' => 0],
            // Limpieza (Aquí estaba tu error)
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_telefono', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_referencia', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_descripcion', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_confirm', 'lifespanCount' => 0],
            // Saldos
            ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_saldo_retry', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ==========================================================================
   BLOQUE: REPORTE DE FUGAS (FLUJO COMPLETO, EMPÁTICO Y CON MEMORIA)
   ========================================================================== */

/* ========================== BLOQUE 3: REPORTE DE FUGAS (CORREGIDO: AUDITORÍA SIN NULL) ========================== */
if ($actionNorm === 'fuga.inicio') {
    // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);

    // 1. RECUPERAR USUARIO (ESTO DEBE IR PRIMERO)
    // Usamos la función segura para que nunca salga NULL
    $usuario = obtener_datos_usuario_seguro($req, $pdo); 
    
    // Si NO tiene cédula (no se ha logueado), lo mandamos al inicio
    if (empty($usuario['cedula'])) { 
        echo json_encode(['followupEventInput' => ['name' => 'WELCOME']], JSON_UNESCAPED_UNICODE); 
        exit; 
    }

    $nombre = $usuario['nombre'];
    $nombre_corto = formatear_nombre_corto($nombre);
    $cedula = $usuario['cedula'];

    // 2. DETERMINAR EL TIPO SOLICITADO
    $tipo_fuga_raw = $req['queryResult']['parameters']['tipo_fuga'] ?? $texto_usuario;
    $tipo_solicitado_db = 'calle'; // Valor por defecto

    if (stripos($tipo_fuga_raw, 'medidor') !== false) { 
        $tipo_solicitado_db = 'medidor'; 
        $tipo_display = "Fuga en Medidor";
    } elseif (stripos($tipo_fuga_raw, 'presión') !== false || stripos($tipo_fuga_raw, 'presion') !== false) { 
        $tipo_solicitado_db = 'baja_presion'; 
        $tipo_display = "Baja Presión";
    } else {
        $tipo_solicitado_db = 'calle';
        $tipo_display = "Fuga en Calle";
    }

    // 3. REGISTRAR AUDITORÍA (AHORA SÍ TENEMOS LOS DATOS)
    // Al ponerlo aquí, ya tenemos $cedula y $nombre cargados
    registrar_auditoria($pdo, $cedula, $nombre, 'FUGA_INICIO', "Inició reporte tipo: $tipo_display", $texto_usuario);

    // 4. VERIFICAR DUPLICADOS
    $st_check = $pdo->prepare("SELECT codigo, tipo FROM reportes_fuga WHERE cedula = :c AND tipo = :t AND estado IN ('PENDIENTE','EN_PROCESO') LIMIT 1");
    $st_check->execute([':c' => $cedula, ':t' => $tipo_solicitado_db]);
    $existe = $st_check->fetch();

    if ($existe) {
        $tipo_existente_display = ucfirst(str_replace('_', ' ', $existe['tipo']));
        card("⚠️ Reporte Duplicado", [
            "Hola **{$nombre_corto}**, ya tienes un reporte de **{$tipo_existente_display}** activo.",
            "Código: **{$existe['codigo']}**.",
            "Nuestro equipo ya está trabajando en este problema específico."
        ], ['🏠 Menú principal']);
        exit;
    }

    // 5. CONTINUAR FLUJO (Si no hay duplicado)
    // Recuperar dirección para tenerla lista
    $st = $pdo->prepare("SELECT direccion FROM medidores WHERE cedula = :c LIMIT 1");
    $st->execute([':c' => $cedula]);
    $row = $st->fetch();
    $direccion_guardada = $row['direccion'] ?? 'Sin dirección registrada';

    echo json_encode([
        'fulfillmentText' => "Reporte de fuga.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => "🚨 Reportando: {$tipo_display}", 'text' => [
                "Entendido **{$nombre_corto}**, vamos a registrar este problema de **{$tipo_display}**.",
                "",
                "Por favor, indícame un **número de celular** para contacto:"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_fuga_pedir_telefono', 
                'lifespanCount' => 5, 
                'parameters' => [
                    'cedula' => $cedula, 
                    'nombre' => $usuario['nombre'], 
                    'direccion' => $direccion_guardada,
                    'tipo_fuga_db' => $tipo_solicitado_db, 
                    'tipo_fuga_display' => $tipo_display,
                    'paso_fuga' => 'telefono'
                ]
            ],
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* PASO 2: RECIBIR TELÉFONO -> PEDIR REFERENCIA */
if ($actionNorm === 'fuga.paso2_telefono') {
     // 1. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $all = gather_params($req);
    
    // Capturamos el teléfono (lo que haya escrito)
    $telefono = to_scalar(pick_raw($all, ['telefono']));
    if (empty($telefono)) { $telefono = $texto_usuario; }
    
    verificar_salida($telefono, $req);

    echo json_encode([
        'fulfillmentText' => "Teléfono anotado.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '📞 Tengo tu Contacto', 'text' => [
                "Gracias. Ahora ayúdame con una **referencia visual** exacta del lugar de la fuga:",
                "(Ej: Frente a la tienda, vereda rota, esquina...)"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_fuga_pedir_referencia', 
                'lifespanCount' => 5, 
                'parameters' => array_merge($all, ['telefono' => $telefono, 'paso_fuga' => 'referencia'])
            ],
            // Cerramos paso anterior
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_telefono', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* PASO 3: RECIBIR REFERENCIA -> PEDIR DESCRIPCIÓN */
if ($actionNorm === 'fuga.paso3_referencia') {
    // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $all = gather_params($req);
    
    // Capturamos la referencia
    $referencia = to_scalar(pick_raw($all, ['referencia']));
    if (empty($referencia)) { $referencia = $texto_usuario; }
    
    verificar_salida($referencia, $req);

    echo json_encode([
        'fulfillmentText' => "Referencia anotada.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '📍 Tengo la Ubicación', 'text' => [
                "Entendido. Por último, añade algún **detalle adicional** sobre la fuga:",
                "(Ej: Sale mucha agua, lleva 2 días, es agua sucia)"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_fuga_pedir_descripcion', 
                'lifespanCount' => 5, 
                'parameters' => array_merge($all, ['referencia' => $referencia, 'paso_fuga' => 'descripcion'])
            ],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_referencia', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* PASO 4: CONFIRMACIÓN FINAL (CON MEMORIA REAL Y ADVERTENCIA) */
if ($actionNorm === 'fuga.paso4_descripcion') {
      // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $all = gather_params($req);
    
    // 1. Capturar Descripción
    $descripcion = to_scalar(pick_raw($all, ['descripcion']));
    if (empty($descripcion)) { $descripcion = $texto_usuario; }
    verificar_salida($descripcion, $req); 

    // 2. RECUPERAR DATOS SEGUROS DE LA BD (Para que nunca diga Vecino)
    $ctx_maestra = ctx_get($req, 'ctx_sesion_maestra');
    $cedula = $ctx_maestra['parameters']['cedula'] ?? $all['cedula'] ?? '';

    $nombre_real = 'Cliente';
    $direccion_real = 'Sin dirección';

    if (!empty($cedula)) {
        $st = $pdo->prepare("SELECT nombre, direccion FROM medidores WHERE cedula = :c LIMIT 1");
        $st->execute([':c' => $cedula]);
        $db_data = $st->fetch();
        if ($db_data) {
            $nombre_real = $db_data['nombre'];
            $direccion_real = $db_data['direccion'];
        }
    }
    $nombre_corto = formatear_nombre_corto($nombre_real);

    // 3. Recuperar datos del flujo
    $tipo_fuga = $all['tipo_fuga'] ?? 'Reporte General';
    $telefono = $all['telefono'] ?? '---';
    $referencia = $all['referencia'] ?? '---';

    // 4. RESPUESTA FINAL COMPACTA
    echo json_encode([
        'fulfillmentText' => "Confirma tu reporte.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [
            [
                // BLOQUE A: RESUMEN DE DATOS
                [
                    'type' => 'description',
                    'title' => '📝 Resumen del Reporte de Fuga',
                    'text' => [
                        "🚨 **Tipo:** {$tipo_fuga}",
                        "📞 **Tel:** {$telefono}",
                        "📍 **Dir:** {$direccion_real}",
                        "🏠 **Ref:** {$referencia}",
                        "💬 **Detalle:** {$descripcion}"
                    ]
                ],
                // BLOQUE B: ADVERTENCIA EMPÁTICA
                [
                    'type' => 'info',
                    'title' => "⚠️ Importante, {$nombre_corto}:",
                    'subtitle' => "Los datos deben ser verídicos para atender la emergencia. Si envías información falsa, el reporte será descartado. ¿Confirmas?"
                ],
                // BLOQUE C: BOTONES
                [
                    'type' => 'chips',
                    'options' => [
                        ['text' => '🚀 CONFIRMAR REPORTE'],
                        ['text' => '❌ CANCELAR REPORTE']
                    ]
                ]
            ]
        ]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_fuga_confirm', 
                'lifespanCount' => 5, 
                // Guardamos todo para el INSERT final
                'parameters' => [
                    'cedula' => $cedula,
                    'nombre' => $nombre_real,
                    'telefono' => $telefono,
                    'direccion' => $direccion_real,
                    'tipo_fuga' => $tipo_fuga,
                    'referencia' => $referencia,
                    'descripcion' => $descripcion,
                    'paso_fuga' => 'confirmacion'
                ]
            ],
            // Mantenemos sesión maestra
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* PASO 5: GUARDAR EN BASE DE DATOS (USANDO EL TIPO CORRECTO) */
if ($actionNorm === 'rep_fuga.confirmar') {
    verificar_salida($texto_usuario, $req, $pdo);
    $ctx = ctx_get($req, '/contexts/ctx_fuga_confirm');
    $p = $ctx['parameters'] ?? [];
    
    // Generar código...
    $fecha_codigo = date('Ymd');
    $sql_count = "SELECT COUNT(*) FROM reportes_fuga WHERE DATE(fecha) = CURDATE()"; 
    $stmt_c = $pdo->query($sql_count);
    $consecutivo = $stmt_c->fetchColumn() + 1;
    $codigo = "RF-" . $fecha_codigo . "-" . str_pad($consecutivo, 4, "0", STR_PAD_LEFT);
    $tipo_para_bd = $p['tipo_fuga_db'] ?? 'calle';

    try {
        $sql = "INSERT INTO reportes_fuga ... (tu sql de siempre) ...";
        // ... (tu execute normal) ...
        $stmt = $pdo->prepare("INSERT INTO reportes_fuga (codigo,tipo,estado,fecha,cedula,nombre,telefono,direccion,referencia,descripcion) VALUES (:cod,:tipo,'PENDIENTE',NOW(),:ced,:nom,:tel,:dir,:ref,:desc)");
        $stmt->execute([
            ':cod' => $codigo, ':tipo' => $tipo_para_bd, ':ced' => $p['cedula'], ':nom' => $p['nombre'],
            ':tel' => $p['telefono'], ':dir' => $p['direccion'], ':ref' => $p['referencia'], ':desc' => $p['descripcion']
        ]);

        // --- AUDITORÍA FINAL DETALLADA ---
        $detalle_log = "CONFIRMADO | Tipo: $tipo_para_bd | Tel: {$p['telefono']} | Ref: {$p['referencia']} | Prob: {$p['descripcion']}";
        registrar_auditoria($pdo, $p['cedula'], $p['nombre'], 'REPORTE_FUGA_FIN', $detalle_log, $texto_usuario);

        // ... (Tu respuesta JSON de éxito) ...
        echo json_encode([
            'fulfillmentText' => "Reporte creado. Código: {$codigo}",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'description', 'title' => '✅ Reporte Recibido', 'text' => [
                    "Tu reporte de **" . ucfirst(str_replace('_', ' ', $tipo_para_bd)) . "** ha sido registrado.",
                    "Código: **{$codigo}**."
                ]],
                ['type' => 'chips', 'options' => [['text' => '🏠 Menú principal']]]
            ]]]]],
            'outputContexts' => [
                ['name' => $req['session'].'/contexts/ctx_fuga_confirm', 'lifespanCount' => 0],
                ['name' => $req['session'].'/contexts/ctx_fuga_pedir_descripcion', 'lifespanCount' => 0],
                ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) { 
        flog($e->getMessage());
        card('⚠️ Error Técnico', ["No pudimos guardar el reporte."], ['🏠 Menú principal']); 
    }
    exit;
}
/* ACCIÓN: CANCELAR REPORTE */
if ($actionNorm === 'rep_fuga.cancelar') {
    // Usamos la misma lógica de cancelación personalizada que hicimos antes
    // (Asegúrate de que tu bloque global de cancelación maneje también 'rep_fuga.cancelar')
    // Si no, puedes redirigir aquí.
    
    // Recuperar nombre
    $usuario = obtener_datos_usuario_seguro($req, $pdo);
    $nombre_corto = formatear_nombre_corto($usuario['nombre']);
    
    echo json_encode([
        'fulfillmentText' => "Reporte cancelado.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'info', 'title' => '❌ Reporte Cancelado', 'subtitle' => "Entendido, no hemos registrado nada."],
            ['type' => 'description', 'text' => ["Si necesitas algo más, **{$nombre_corto}**, aquí tienes el menú:"]],
            ['type' => 'chips', 'options' => [['text' => '🏠 Menú principal']]]
        ]]]]],
        'outputContexts' => [
            ['name' => $req['session'].'/contexts/ctx_fuga_confirm', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_fuga_pedir_descripcion', 'lifespanCount' => 0],
            // Mantenemos sesión
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ========================== BLOQUE 2: LIMPIEZA INICIO (USANDO MEMORIA) ========================== */
/* ========================== INICIO LIMPIEZA (DETECTA SESIÓN REAL) ========================== */
if ($actionNorm === 'limpieza.inicio') {
    verificar_salida($texto_usuario, $req, $pdo);
    
    // 1. RECUPERAR USUARIO PRIMERO
    $usuario = obtener_datos_usuario_seguro($req, $pdo); // <--- ESTO EVITA EL NULL
    $cedula = $usuario['cedula'];
    $nombre = $usuario['nombre'];
    $nombre_corto = formatear_nombre_corto($nombre);

    if (empty($cedula)) { 
        echo json_encode(['followupEventInput' => ['name' => 'WELCOME']], JSON_UNESCAPED_UNICODE); 
        exit; 
    }
    // 2. AUDITORÍA
    registrar_auditoria($pdo, $cedula, $nombre, 'LIMPIEZA_INICIO', 'Inició solicitud de limpieza alcantarillado', $texto_usuario);
    
    // SI TENEMOS DATOS, LOS USAMOS
    $cedula = $usuario['cedula'];
    $nombre_corto = formatear_nombre_corto($usuario['nombre']);

    // 2. VERIFICAR DUPLICADOS
    $st_check = $pdo->prepare("SELECT codigo FROM limpiezas WHERE cedula = :c AND estado IN ('PENDIENTE','EN_PROCESO') LIMIT 1");
    $st_check->execute([':c' => $cedula]);
    if ($existe = $st_check->fetch()) {
        card("⚠️ Solicitud Activa", ["Hola {$nombre_corto}, ya tienes una solicitud activa ({$existe['codigo']}).", "Nuestro equipo está en ello."], ['🏠 Menú principal']);
    }

    // 3. RECUPERAR DIRECCIÓN
    $st = $pdo->prepare("SELECT direccion FROM medidores WHERE cedula = :c LIMIT 1");
    $st->execute([':c' => $cedula]);
    $row = $st->fetch();
    $direccion_guardada = $row['direccion'] ?? 'Sin dirección registrada';

    // 4. RESPUESTA EMPÁTICA (CON NOMBRE "LUIS")
    echo json_encode([
        'fulfillmentText' => "Limpieza de alcantarillado.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '🚛 Limpieza de Alcantarillado', 'text' => [
                "Hola **{$nombre_corto}**, entiendo perfectamente la situación.",
                "Lamento los inconvenientes. Vamos a coordinar la visita técnica.",
                "",
                "Por favor, indícame un **número de celular** para contacto:"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_limp_pedir_telefono', 
                'lifespanCount' => 5, 
                'parameters' => [
                    'cedula' => $cedula, 
                    'nombre' => $usuario['nombre'], 
                    'direccion' => $direccion_guardada,
                    'paso_limp' => 'telefono'
                ]
            ],
            // IMPORTANTE: REFRESCAMOS LA SESIÓN MAESTRA PARA QUE NO CADUQUE
            [
                'name' => $req['session'].'/contexts/ctx_sesion_maestra', 
                'lifespanCount' => 999, 
                'parameters' => ['cedula' => $cedula, 'nombre' => $usuario['nombre']]
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* (EL PASO 1 FUE ELIMINADO PORQUE YA TENEMOS LA CÉDULA) */

/* ==========================================================================
   FLUJO DE LIMPIEZA SIMPLIFICADO (CONFIRMACIÓN ROBUSTA AL FINAL)
   ========================================================================== */

/* PASO 2: RECIBIR TELÉFONO (CON AUDITORÍA DE LO QUE ESCRIBIÓ) */
if ($actionNorm === 'limpieza.paso2_telefono') {
    // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $all = gather_params($req);
    
    // Tomamos lo que el usuario escribió, sea lo que sea
    $telefono = to_scalar(pick_raw($all, ['telefono']));
    if (empty($telefono)) { $telefono = $texto_usuario; }
    
    verificar_salida($telefono, $req);

    // --- NUEVO: AUDITORÍA (EL ESPÍA) ---
    // Recuperamos quién es el usuario para no perder el nombre
    $u = obtener_datos_usuario_seguro($req, $pdo); 
    // Guardamos: Acción 'LIMPIEZA_DATO_TEL', y en el último campo va $texto_usuario (lo que escribió literal)
    registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'LIMPIEZA_DATO_TEL', 'Usuario ingresó teléfono', $texto_usuario);
    // -----------------------------------

    echo json_encode([
        'fulfillmentText' => "Teléfono anotado.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '📞Tengo tu Contacto', 'text' => [
                "Gracias. Ahora ayúdame con una **referencia visual** de tu domicilio (Color de casa, calle, frente a...):"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_limp_pedir_referencia', 
                'lifespanCount' => 5, 
                'parameters' => array_merge($all, ['telefono' => $telefono, 'paso_limp' => 'referencia'])
            ],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_telefono', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* PASO 3: RECIBIR REFERENCIA (CON AUDITORÍA DE LO QUE ESCRIBIÓ) */
if ($actionNorm === 'limpieza.paso3_referencia') {
     // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $all = gather_params($req);
    
    $referencia = to_scalar(pick_raw($all, ['referencia']));
    if (empty($referencia)) { $referencia = $texto_usuario; }
    
    verificar_salida($referencia, $req);

    // --- NUEVO: AUDITORÍA (EL ESPÍA) ---
    // 1. Recuperamos quién es el usuario
    $u = obtener_datos_usuario_seguro($req, $pdo); 
    // 2. Guardamos la acción y el TEXTO LITERAL ($texto_usuario) en la columna mensaje_usuario
    registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'LIMPIEZA_DATO_REF', 'Usuario ingresó referencia visual', $texto_usuario);
    // -----------------------------------

    echo json_encode([
        'fulfillmentText' => "Referencia anotada.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '📍 Tengo la Ubicación', 'text' => [
                "Entendido. Por último, descríbeme brevemente **cuál es el problema** (Ej: Malos olores, se rebosó la caja):"
            ]]
        ]]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_limp_pedir_descripcion', 
                'lifespanCount' => 5, 
                'parameters' => array_merge($all, ['referencia' => $referencia, 'paso_limp' => 'descripcion'])
            ],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_referencia', 'lifespanCount' => 0]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ACCIÓN FINAL: GUARDAR LIMPIEZA EN BD (EL INSERT) */
if ($actionNorm === 'limpieza.confirmar') {
    verificar_salida($texto_usuario, $req, $pdo);

    // 1. RECUPERAMOS LA "MOCHILA" CON TODOS LOS DATOS CONFIRMADOS
    $ctx = ctx_get($req, '/contexts/ctx_limp_confirm');
    $p = $ctx['parameters'] ?? [];

    // Validamos que existan datos (por seguridad)
    if (empty($p['cedula'])) {
        card("⚠️ Error de Sesión", ["Se perdió la conexión. Por favor intenta de nuevo."], ['🏠 Menú principal']);
        exit;
    }

    // 2. GENERAR CÓDIGO ÚNICO (LP-YYYYMMDD-XXXX)
    $fecha_codigo = date('Ymd');
    $sql_count = "SELECT COUNT(*) FROM limpiezas WHERE DATE(fecha) = CURDATE()"; 
    $stmt_c = $pdo->query($sql_count);
    $consecutivo = $stmt_c->fetchColumn() + 1;
    $codigo = "LP-" . $fecha_codigo . "-" . str_pad($consecutivo, 4, "0", STR_PAD_LEFT);

    // 3. INSERTAR EN BASE DE DATOS
    try {
        $sql = "INSERT INTO limpiezas 
                (codigo, estado, fecha, cedula, nombre, telefono, direccion, referencia, descripcion) 
                VALUES 
                (:cod, 'PENDIENTE', NOW(), :ced, :nom, :tel, :dir, :ref, :desc)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cod' => $codigo,
            ':ced' => $p['cedula'],
            ':nom' => $p['nombre'],
            ':tel' => $p['telefono'],
            ':dir' => $p['direccion'],
            ':ref' => $p['referencia'],
            ':desc' => $p['descripcion']
        ]);

        // 4. AUDITORÍA FINAL (EL ÉXITO)
        // Guardamos que confirmó y el código generado
        if (function_exists('registrar_auditoria')) {
            $detalle = "ORDEN CREADA: $codigo | Falla: {$p['descripcion']}";
            registrar_auditoria($pdo, $p['cedula'], $p['nombre'], 'LIMPIEZA_CONFIRM', $detalle, $texto_usuario);
        }

        // 5. RESPUESTA DE ÉXITO
        echo json_encode([
            'fulfillmentText' => "Solicitud creada. Código: {$codigo}",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                [
                    'type' => 'info',
                    'title' => '✅ Solicitud Exitosa',
                    'subtitle' => "Listo {$p['nombre']}, tu orden ha sido generada."
                ],
                [
                    'type' => 'description',
                    'text' => [
                        "📌 **Código:** {$codigo}",
                        "Nuestro equipo técnico acudirá a la dirección: **{$p['direccion']}**.",
                        "",
                        "Gracias por confiar en EPMAPALS."
                    ]
                ],
                ['type' => 'chips', 'options' => [['text' => '🏠 Menú principal']]]
            ]]]]],
            'outputContexts' => [
                // BORRAMOS TODO RASTRO DE LA LIMPIEZA
                ['name' => $req['session'].'/contexts/ctx_limp_confirm', 'lifespanCount' => 0],
                ['name' => $req['session'].'/contexts/ctx_limp_pedir_descripcion', 'lifespanCount' => 0],
                ['name' => $req['session'].'/contexts/ctx_limp', 'lifespanCount' => 0],
                // Mantenemos sesión maestra
                ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) { 
        flog("Error SQL Limpieza: " . $e->getMessage());
        card('⚠️ Error Técnico', ["Hubo un problema al guardar tu solicitud.", "Intenta más tarde."], ['🏠 Menú principal']); 
    }
    exit;
}
 
/* ACCIÓN: CANCELAR SOLICITUD DE LIMPIEZA */
bloque_cancelar_limpieza: // Etiqueta por si usamos goto
if ($actionNorm === 'limpieza.cancelar' || stripos($texto_usuario, 'cancelar orden') !== false) {
    
    $usuario = obtener_datos_usuario_seguro($req, $pdo);
    $nombre_corto = formatear_nombre_corto($usuario['nombre']);
    
    echo json_encode([
        'fulfillmentText' => "Solicitud cancelada.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'info',
                'title' => '❌ Solicitud Cancelada',
                'subtitle' => "Entendido {$nombre_corto}, no se generó ninguna orden."
            ],
            [
                'type' => 'chips',
                'options' => [['text' => '🏠 Menú principal']]
            ]
        ]]]]],
        'outputContexts' => [
            // MATAMOS TODO
            ['name' => $req['session'].'/contexts/ctx_limp_confirm', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp_pedir_descripcion', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_limp', 'lifespanCount' => 0],
            ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 5] // Activamos Menú
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* PASO 4: CONFIRMACIÓN FINAL (CON AUDITORÍA DE LA FALLA) */
if ($actionNorm === 'limpieza.paso4_descripcion') {
      // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);

    // 1. RECUPERACIÓN ROBUSTA DE DATOS (La corrección que hicimos antes)
    $ctx_incoming = ctx_get($req, 'ctx_limp_pedir_descripcion');
    $params_incoming = $ctx_incoming['parameters'] ?? [];
    $all_generic = gather_params($req);
    // Fusionamos todo para no perder nada
    $all = array_merge($all_generic, $params_incoming);

    // 2. CAPTURAR DESCRIPCIÓN
    $descripcion = to_scalar(pick_raw($all, ['descripcion']));
    if (empty($descripcion) || trim($descripcion) === '') {
        $descripcion = $texto_usuario;
    }
    $descripcion = substr($descripcion, 0, 200);

    verificar_salida($descripcion, $req);

    // 3. RECUPERAR DATOS DE USUARIO Y DIRECCIÓN
    $ctx_maestra = ctx_get($req, 'ctx_sesion_maestra');
    $ctx_activa = ctx_get($req, 'ctx_sesion_activa');

    $cedula = '';
    if (!empty($ctx_maestra['parameters']['cedula'])) { $cedula = $ctx_maestra['parameters']['cedula']; }
    elseif (!empty($ctx_activa['parameters']['cedula'])) { $cedula = $ctx_activa['parameters']['cedula']; }
    elseif (!empty($all['cedula'])) { $cedula = $all['cedula']; }

    $nombre_real = 'Cliente';
    $direccion_real = 'Dirección no disponible';

    if (!empty($cedula)) {
        try {
            $st = $pdo->prepare("SELECT nombre, direccion FROM medidores WHERE cedula = :c LIMIT 1");
            $st->execute([':c' => $cedula]);
            $datos_db = $st->fetch();
            if ($datos_db) {
                $nombre_real = $datos_db['nombre'] ?? 'Cliente';
                $dir_db = trim($datos_db['direccion'] ?? '');
                if (!empty($dir_db)) { $direccion_real = $dir_db; }
            }
        } catch (Exception $e) { flog("Error DB P4: ".$e->getMessage()); }
    }
    $nombre_corto = formatear_nombre_corto($nombre_real);
    $telefono = !empty($all['telefono']) ? $all['telefono'] : '---';
    $referencia = !empty($all['referencia']) ? $all['referencia'] : '---';

    // --- NUEVO: AUDITORÍA (EL ESPÍA) ---
    // Guardamos exactamente lo que escribió como problema
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $cedula, $nombre_real, 'LIMPIEZA_DATO_FALLA', 'Usuario describió el problema', $texto_usuario);
    }
    // -----------------------------------

    // 4. RESPUESTA (CUADRO DE CONFIRMACIÓN)
    echo json_encode([
        'fulfillmentText' => "Confirma tu solicitud.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [
            [
                [
                    'type' => 'description',
                    'title' => '📝 Resumen de Solicitud de Alcantarillado',
                    'text' => [
                        "📞 **Tel:** {$telefono}",
                        "📍 **Dir:** {$direccion_real}",
                        "🏠 **Ref:** {$referencia}",
                        "💬 **Falla:** {$descripcion}"
                    ]
                ],
                [
                    'type' => 'info',
                    'title' => "⚠️ Importante, {$nombre_corto}:",
                    'subtitle' => "Los datos deben ser reales y verificables. Si envías información falsa, no podremos procesar tu solicitud. ¿Confirmas la orden?"
                ],
                [
                    'type' => 'chips',
                    'options' => [
                        ['text' => '🚀 CONFIRMAR ORDEN'],
                        ['text' => '❌ CANCELAR ORDEN']
                    ]
                ]
            ]
        ]]]],
        'outputContexts' => [
            [
                'name' => $req['session'].'/contexts/ctx_limp_confirm',
                'lifespanCount' => 5,
                'parameters' => [
                    'cedula' => $cedula,
                    'nombre' => $nombre_real,
                    'direccion' => $direccion_real,
                    'telefono' => $telefono,
                    'referencia' => $referencia,
                    'descripcion' => $descripcion,
                    'paso_limp' => 'confirmacion'
                ]
            ],
            // Matamos los anteriores para evitar errores
            $ctx_maestra ? $ctx_maestra : ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ============================================================
   BLOQUE 3: CONSULTA DE SALDOS (CORREGIDO: FILTRO "CAMBIAR CÉDULA")
   ============================================================ */
/* PRIORIDAD 1: VER SALDO (AUDITORÍA RESUMIDA: CANTIDAD DE MESES) */
if ($actionNorm === 'saldo.ver_saldo') {
    verificar_salida($texto_usuario, $req, $pdo);
    $ctx = ctx_get($req,'/contexts/ctx_saldo');
    $medidor = (int)($ctx['parameters']['medidor_numero'] ?? 0);
    if (!$medidor) { say('No se detectó contrato activo.'); }

    // 1. Buscamos deuda
    $st = $pdo->prepare("SELECT mes, valor_pendiente FROM saldos WHERE numero_medidor=:m AND estado='pendiente' ORDER BY mes");
    $st->execute([':m'=>$medidor]);
    $rows = $st->fetchAll();

    // 2. Calculamos totales y CANTIDAD
    $total = 0;
    $cantidad_meses = count($rows); // <--- AQUÍ CONTAMOS (Ej: 2)
    
    foreach($rows as $r){ 
        $total += (float)$r['valor_pendiente']; 
    }
    $total_fmt = number_format($total, 2);

    // --- AUDITORÍA (RESUMEN) ---
    $u = obtener_datos_usuario_seguro($req, $pdo);
    if (function_exists('registrar_auditoria')) {
        // Detalle: "Total: $18.50 | Pendientes: 2 meses"
        $detalle = "Medidor: $medidor | Total: $$total_fmt | Pendientes: $cantidad_meses meses";
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'CONSULTA_SALDO', $detalle, $texto_usuario);
    }
    // ---------------------------

    if (!$rows) { 
        echo json_encode([
            'fulfillmentText' => "Estás al día.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'image', 'rawUrl' => 'https://cdn-icons-png.flaticon.com/512/190/190411.png', 'accessibilityText' => 'Al día'],
                ['type' => 'info', 'title' => '¡Excelente! 🎉', 'subtitle' => "No tienes valores pendientes en el medidor {$medidor}."],
                ['type' => 'chips', 'options' => [['text' => '📆 Ver historial pagos'], ['text' => '🏠 Menú principal']]]
            ]]]]]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $lines = [];
    foreach($rows as $r){ $lines[] = "❌ {$r['mes']}: $ {$r['valor_pendiente']}"; }
    
    echo json_encode([
        'fulfillmentText' => "Tienes saldos pendientes.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '💰 Valores Pendientes', 'text' => array_merge(
                ["Valor a pagar:"],
                $lines,
                ["-----------------", "💵 **TOTAL: $ {$total_fmt}**"]
            )],
            ['type' => 'chips', 'options' => [['text' => '📆 Ver historial pagos'], ['text' => '🏠 Menú principal']]]
        ]]]]]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* HISTORIAL DE PAGOS (CORREGIDO: DETECTA EL TEXTO DEL BOTÓN) */
// AHORA: Si la acción es la correcta O si el texto dice "historial" y estamos en saldo
if ($actionNorm === 'saldo.ver_pagados' || (ctx_get($req, 'ctx_saldo') && stripos($texto_usuario, 'historial') !== false)) {
    
    verificar_salida($texto_usuario, $req, $pdo);

    $ctx = ctx_get($req,'/contexts/ctx_saldo');
    $medidor = (int)($ctx['parameters']['medidor_numero'] ?? 0);
    if (!$medidor) { say('No se detectó contrato activo.'); }

    // Consulta SQL (La que ya arreglamos con hora_pago)
    $st = $pdo->prepare("SELECT mes, fecha_pago, hora_pago FROM saldos 
                         WHERE numero_medidor=:m 
                         AND estado='pagado' 
                         AND mes BETWEEN '2025-01' AND '2025-10' 
                         ORDER BY mes DESC");
    $st->execute([':m'=>$medidor]);
    $rows = $st->fetchAll();

    // --- AUDITORÍA (RESUMEN) ---
    $cantidad_pagados = count($rows);
    $u = obtener_datos_usuario_seguro($req, $pdo);
    if (function_exists('registrar_auditoria')) {
        $detalle = "Medidor: $medidor | Pagados: $cantidad_pagados meses";
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'HISTORIAL_SALDO', $detalle, $texto_usuario);
    }
    // ---------------------------

    if (!$rows) { 
        card('Historial 2025 vacío', ['No veo pagos registrados entre Enero y Octubre de 2025.'], ['💰 Ver saldo pendiente','🏠 Menú principal']); 
    }

    $lines = [];
    foreach($rows as $r){ 
        $hora = $r['hora_pago'] ?? '00:00:00';
        $lines[] = "✅ Pagó el {$r['fecha_pago']} {$hora}"; 
    }

    echo json_encode([
        'fulfillmentText' => "Historial de pagos.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '📆 Pagos 2025', 'text' => array_merge(
                ["Tus últimos pagos:"],
                $lines
            )],
            ['type' => 'chips', 'options' => [['text' => '💰 Ver saldo pendiente'], ['text' => '🏠 Menú principal']]]
        ]]]]]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ------------------------------------------------------------
   PRIORIDAD 2: CONFIRMACIÓN DEL CONTRATO
   ------------------------------------------------------------ */
$texto_usuario = mb_strtolower($req['queryResult']['queryText'] ?? '', 'UTF-8');

/* 4. CONFIRMAR MEDIDOR (CORREGIDO: Fuerza bruta al paso menu_saldo) */
if ($actionNorm === 'saldo.confirmar' || (strpos($texto_usuario, 'sí') !== false && strpos($texto_usuario, 'ver saldo') !== false)) {
      // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    $ctx = ctx_get($req,'/contexts/ctx_saldo');
    $ctx_maestra = ctx_get($req, 'ctx_sesion_maestra');
    
    // Recuperamos datos
    $params = $ctx['parameters'] ?? [];
    $medidor = (int)($params['medidor_numero'] ?? 0);
    
    // AQUÍ ESTÁ EL TRUCO: Sobrescribimos 'paso' explícitamente
    $nuevos_parametros = array_merge($params, ['paso' => 'menu_saldo']);

    echo json_encode([
        'fulfillmentText' => "Selección confirmada.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => "Medidor {$medidor} Conectado 📶", 'text' => ["¡Acceso correcto!", "¿Qué deseas revisar hoy?"]],
            ['type' => 'chips', 'options' => [['text' => '💰 Ver saldo pendiente'], ['text' => '📆 Ver historial pagos'], ['text' => '🏠 Menú principal']]]
        ]]]]],
        'outputContexts' => [
            // Enviamos los parámetros actualizados con vida larga (50 turnos)
            ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 50, 'parameters' => $nuevos_parametros],
            $ctx_maestra
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ------------------------------------------------------------
   PRIORIDAD 3: BÚSQUEDA DE CÉDULA (Y MANEJO DEL BOTÓN MENÚ/CAMBIAR)
/* 2. LÓGICA PRINCIPAL: MOSTRAR CONTRATOS (DIRECTO) */
if ($actionNorm === 'saldo.buscar' || 
    $actionNorm === 'saldo.cambiar' || 
    $intentNorm === 'saldo_si' || 
    $intentNorm === 'saldo - reintento cedula' ||
    (strpos($texto_usuario, 'si') !== false && strpos($texto_usuario, 'ver saldo') === false && ctx_get($req, 'ctx_saldo'))) {
    
    verificar_salida($texto_usuario, $req);

    // 1. OBTENCIÓN ROBUSTA DE CÉDULA (Prioridad: Sesión Activa)
    $ctx_sesion = ctx_get($req, 'ctx_sesion_activa');
    $cedula = $ctx_sesion['parameters']['cedula'] ?? '';
    
    // Si no está en sesión (raro), buscamos en params acumulados
    if (!$cedula) { 
        $all = gather_params($req);
        $cedula = $all['cedula'] ?? '';
    }

    // SI AÚN ASÍ NO HAY CÉDULA -> ERROR (Pero no pedimos de nuevo, asumimos error técnico si ya pasó login)
    if ($cedula === '') { 
        echo json_encode([
            'fulfillmentText' => "Error de sesión.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'info', 'title' => '⚠️ Sesión no detectada', 'subtitle' => "Por favor, di 'Hola' para reiniciar tu sesión."]
            ]]]]],
            'outputContexts' => [['name' => $req['session'].'/contexts/ctx_login_espera', 'lifespanCount' => 5]]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. BUSCAR EN BD DIRECTAMENTE
    $st = $pdo->prepare("SELECT numero_medidor, direccion, manzana, sector, nombre FROM medidores WHERE cedula = :c ORDER BY numero_medidor");
    $st->execute([':c'=>$cedula]);
    $rows = $st->fetchAll();

    // 3. NO ENCONTRADA (Raro si ya pasó login)
    if (!$rows) { 
        card_with_ctx($req['session'], 'ctx_saldo', [], "🤔 Sin Contratos", ["La cédula **{$cedula}** no tiene contratos activos."], ['🏠 Menú principal'], 5);
    }

    // 4. ÉXITO -> MOSTRAR LISTA DE INMEDIATO
    $nombre_primero = formatear_nombre_corto($rows[0]['nombre'] ?? '');
    $lista_texto = ["Hola {$nombre_primero}, aquí están tus contratos:"];
    $map = []; 
    $i = 1;
    foreach ($rows as $r) {
        $lista_texto[] = "🔹 **Opción {$i}:** Medidor {$r['numero_medidor']}";
        $map[$i] = (int)$r['numero_medidor'];
        $i++;
    }
    $lista_texto[] = "";
    $lista_texto[] = "👇 Escribe el número de la opción (ej: 1).";

    echo json_encode([
        'fulfillmentText' => "Contratos encontrados.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '🔎 Tus Contratos', 'text' => $lista_texto]
        ]]]]],
        // REFRESCAMOS LA MEMORIA: Guardamos cédula de nuevo en el contexto
        'outputContexts' => [
            ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 50, 'parameters' => ['cedula'=>$cedula, 'map'=>$map, 'paso' => 'seleccion']],
            ['name' => $req['session'].'/contexts/ctx_sesion_activa', 'lifespanCount' => 999, 'parameters' => ['cedula'=>$cedula]]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ============================================================
   SI DICE "NO" EN SALDO -> EJECUTAR CANCELACIÓN
   ============================================================ */
// Verificamos si está activo el contexto de saldo ('ctx_saldo')
// Y si el usuario dijo "no", "nel", "cancelar" o la acción es 'saldo.no'
if (ctx_get($req, 'ctx_saldo') && (stripos($texto_usuario, 'no') !== false || $actionNorm === 'saldo.no')) {
    
    // Llamamos a tu función de cancelar (la que muestra el mensaje de despedida y menú)
    responder_cancelacion($req); 
}
/* ------------------------------------------------------------
   PRIORIDAD 4: EL INICIO
   ------------------------------------------------------------ */
/* 1. INICIO: ¿DESEAS CONSULTAR SALDO? */
if ($actionNorm === 'saldo.inicio' || $intentNorm === 'saldo_start') {
      // 0. Verificar si quiere salir antes de hacer nada más
    verificar_salida($texto_usuario, $req, $pdo);
    // Recuperamos sesión y cédula
    $ctx_sesion = ctx_get($req, 'ctx_sesion_activa');
    $cedula = $ctx_sesion['parameters']['cedula'] ?? '';
    $nombre = $ctx_sesion['parameters']['nombre'] ?? 'Vecino';
    $nombre_corto = formatear_nombre_corto($nombre);

    // Si por alguna razón técnica no hay cédula, intentamos recuperarla o pedimos login
    if (!$cedula) { 
        $all = gather_params($req);
        $cedula = $all['cedula'] ?? '';
    }
    
    if (!$cedula) {
        say("⚠️ No detecto tu sesión activa. Por favor escribe 'Hola' para ingresar.");
    }

    echo json_encode([
        'fulfillmentText' => "¿Deseas consultar tu saldo?",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '💵 Consulta de Saldo', 'text' => [ 
                "¡Claro {$nombre_corto}! ¿Deseas consultar tu estado de cuenta de agua potable?" 
            ]],
            ['type' => 'chips', 'options' => [['text' => '✅ Sí'], ['text' => '❌ No']]]
        ]]]]],
        'outputContexts' => [
            // Guardamos paso 'inicio' para que el Basurero sepa validar Sí/No
            ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 5, 'parameters' => ['cedula' => $cedula, 'paso' => 'inicio']],
            // Renovamos la sesión maestra
            ['name' => $req['session'].'/contexts/ctx_sesion_activa', 'lifespanCount' => 999, 'parameters' => ['cedula' => $cedula, 'nombre' => $nombre]]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ------------------------------------------------------------
   PRIORIDAD 5: SELECCIONAR CONTRATO
   ------------------------------------------------------------ */
/* 3. SELECCIONAR MEDIDOR (CON AUDITORÍA RESTAURADA) */
if ($actionNorm === 'saldo.seleccionar') {
     // 0. Verificar salida
    verificar_salida($texto_usuario, $req, $pdo);

    // --- 1. AUDITORÍA (EL ESPÍA) ---
    // Esto debe ejecutarse ANTES de cualquier lógica
    $u = obtener_datos_usuario_seguro($req, $pdo);
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'SALDO_SELECCION', 'Usuario seleccionó contrato', $texto_usuario);
    }
    // -----------------------------------

    $ctx = ctx_get($req,'/contexts/ctx_saldo');
    $map = $ctx['parameters']['map'] ?? [];
    
    // Recuperamos la opción
    $op_raw = $req['queryResult']['parameters']['opcion'] ?? '';
    if ($op_raw === '') { 
        if (preg_match('/\d+/', $texto_usuario, $m)) { $op_raw = $m[0]; } 
    }
    $op_int = (int)$op_raw;

    // Validación
    if (!isset($map[$op_int])) {
        $msg = "El número **{$op_int}** no está en la lista. Elige una opción válida.";
        card_with_ctx($req['session'], 'ctx_saldo', 
            array_merge($ctx['parameters'], ['paso' => 'seleccion']), 
            '⚠️ Opción Incorrecta', [$msg], [], 5
        );
        exit;
    }

    // Selección válida
    $medidor = (int)$map[$op_int];
    
    // Consultamos datos
    $st = $pdo->prepare("SELECT numero_medidor, direccion FROM medidores WHERE numero_medidor=:m");
    $st->execute([':m'=>$medidor]);
    $m = $st->fetch();
    
    $info_medidor = $m['numero_medidor']; 
    $info_ubicacion = substr($m['direccion'], 0, 35) . "...";
  
    // Mensaje de éxito directo
    $lines = [
        "✅ **Conectado al medidor {$info_medidor}**",
        "📍 **Ubicación:** {$info_ubicacion}",
        "",
        "¿Qué deseas consultar?"
    ];
    
    $nuevos_params = array_merge($ctx['parameters'], [
        'info_m' => $info_medidor,
        'info_u' => $info_ubicacion,
        'medidor_numero' => $medidor,
        'paso' => 'menu_saldo' 
    ]);

    card_with_ctx($req['session'], 'ctx_saldo', 
        $nuevos_params, 
        '📶 Conexión Exitosa', 
        $lines, 
        ['💰 Ver saldo pendiente', '📆 Ver historial pagos', '🏠 Menú principal'], 
        50
    );
    exit;
}
/* MENÚ DE REQUISITOS (REQ_MENU_START) */
if ($actionNorm === 'req_menu_start' || strpos($texto_usuario, 'requisitos de tramites') !== false) {
    
    verificar_salida($texto_usuario, $req, $pdo);
    $u = obtener_datos_usuario_seguro($req, $pdo);

    // 1. AUDITORÍA
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'MENU_REQUISITOS', 'Usuario solicitó menú de requisitos', $texto_usuario);
    }

    // 2. RESPUESTA CON LOS BOTONES
    echo json_encode([
        'fulfillmentText' => "Menú de requisitos.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'description',
                'title' => '🧾 Requisitos de Trámites',
                'text' => [
                    "Aquí tienes la lista oficial de trámites.",
                    "Selecciona uno para ver qué documentos necesitas 👇"
                ]
            ],
            [
                'type' => 'chips',
                'options' => [
                    ['text' => '📄 Guía nueva con medidor'],
                    ['text' => '🚫 Requisitos para retirar un medidor'],
                    ['text' => '⚰️ Cambio de titular fallecido'],
                    ['text' => '👤 Cambio de titular'],
                    ['text' => '📜 Certificado de viabilidad'],
                    ['text' => '🔥 Exoneraciones y descuentos'],
                    ['text' => '🤝 Requisitos para realizar convenio'],
                    ['text' => '🏠 Menú principal']
                ]
            ]
        ]]]]],
        'outputContexts' => [
            // Mantenemos la sesión viva
            ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ==========================================================================
   BLOQUE CENTRALIZADO DE REQUISITOS (LINKS ESPECÍFICOS POR CADA UNO)
   ========================================================================== */
if (strpos($actionNorm, 'req.') === 0) {
    
    verificar_salida($texto_usuario, $req, $pdo);
    $u = obtener_datos_usuario_seguro($req, $pdo);

    // Configuración por defecto (Por si se nos olvida poner uno abajo)
    $titulo = "Requisitos";
    $info = [];
    $link_destino = 'https://epmapalstramites.com/services/'; 
    $texto_boton = "📄 Descargar Solicitud";

    switch ($actionNorm) {
        case 'req.nuevo_medidor':
            $titulo = "📄 Guía Nueva con Medidor";
            $info = [
                "1️⃣ Copia de Cédula y Papeleta de votación.",
                "2️⃣ Copia de las Escrituras.",
                "3️⃣ Certificado de no adeudar al municipio.",
                "4️⃣ Carpeta manila amarilla."
            ];
            // 👇 AQUÍ CAMBIAS EL LINK ESPECÍFICO DE ESTE TRÁMITE
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_nuevo_medidor.pdf";
            break;

        case 'req.retirar_medidor':
            $titulo = "🚫 Retiro de Medidor";
            $info = [
                "1️⃣ Solicitud dirigida al Gerente.",
                "2️⃣ Estar al día en los pagos.",
                "3️⃣ Copia de cédula del titular."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_retiro.pdf";
            break;

        case 'req.fallecido':
            $titulo = "⚰️ Cambio Titular (Fallecido)";
            $info = [
                "1️⃣ Partida de defunción.",
                "2️⃣ Escrituras de la propiedad.",
                "3️⃣ Copia de cédula del heredero/solicitante.",
                "4️⃣ Estar al día en pagos."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_fallecido.pdf";
            break;

        case 'req.cambio_titular':
            $titulo = "👤 Cambio de Titular (Compra/Venta)";
            $info = [
                "1️⃣ Copia de escritura registrada.",
                "2️⃣ Copia de cédula del nuevo dueño.",
                "3️⃣ Certificado de no adeudar.",
                "4️⃣ Solicitud de cambio."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_cambio_dueno.pdf";
            break;

        case 'req.viabilidad':
            $titulo = "📜 Certificado de Viabilidad";
            $info = [
                "1️⃣ Plano del predio.",
                "2️⃣ Copia de escrituras.",
                "3️⃣ Solicitud de inspección."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_viabilidad.pdf";
            break;
            
        case 'req.exoneracion':
            $titulo = "🔥 Exoneraciones (3ra Edad/Discapacidad)";
            $info = [
                "1️⃣ Copia de Cédula.",
                "2️⃣ Carnet de CONADIS (si aplica).",
                "3️⃣ El medidor debe ser de uso residencial."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/solicitud_exoneracion.pdf";
            break;

        case 'req.convenio':
            $titulo = "🤝 Convenio de Pago";
            $info = [
                "1️⃣ Cédula del titular.",
                "2️⃣ Pago inicial del 30% de la deuda.",
                "3️⃣ Llenar formulario de garantía.",
                "4️⃣ Acercarse a ventanilla de cobros."
            ];
            // 👇 LINK ESPECÍFICO
            $link_destino = "https://epmapalstramites.com/descargas/formulario_convenio.pdf";
            break;

        default:
            $titulo = "Información";
            $info = ["Por favor acércate a ventanilla para más información."];
            break;
    }

    // 2. AUDITORÍA
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'INFO_REQUISITOS', "Consultó: $titulo", $texto_usuario);
    }

    // 3. RESPUESTA AL USUARIO
    echo json_encode([
        'fulfillmentText' => implode("\n", $info),
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'description',
                'title' => $titulo,
                'text' => $info
            ],
            // EL BOTÓN MÁGICO QUE CAMBIA DE LINK SEGÚN EL CASO
            [
                'type' => 'button',
                'icon' => ['type' => 'description', 'color' => '#FF9800'],
                'text' => $texto_boton,
                'link' => $link_destino,
                'event' => ['name' => '', 'languageCode' => '', 'parameters' => []]
            ],
            [
                'type' => 'chips',
                'options' => [
                    ['text' => '🧾 Ver otros trámites'],
                    ['text' => '🏠 Menú principal']
                ]
            ]
        ]]]]],
        'outputContexts' => [
             ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
/* ============================================================
   BLOQUES 4 y 5: ESTADOS Y QUEJAS (MÁS EMPÁTICO)
   ============================================================ */
/* CONSULTAR ESTADO LIMPIEZA (AUTOMÁTICO POR CÉDULA) */
if ($actionNorm === 'svc.limpieza.buscar') {
    verificar_salida($texto_usuario, $req, $pdo);
    
    // 1. RECUPERAR USUARIO (Identidad)
    $u = obtener_datos_usuario_seguro($req, $pdo);
    if (empty($u['cedula'])) { 
        say("Por favor, inicia sesión diciendo 'Hola' para ver tus trámites."); 
    }
    $nombre_corto = formatear_nombre_corto($u['nombre']);

    // 2. AUDITORÍA
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'CONSULTA_ESTADO_LIMP', 'Usuario revisó estado limpieza', $texto_usuario);
    }

    // 3. BUSCAR EL TRÁMITE MÁS RECIENTE
    // Buscamos el último reporte registrado por esa cédula
    $st = $pdo->prepare("SELECT codigo, estado, fecha, direccion FROM limpiezas 
                         WHERE cedula = :c 
                         ORDER BY id DESC LIMIT 1");
    $st->execute([':c' => $u['cedula']]);
    $row = $st->fetch();

    // CASO A: NO TIENE REPORTES
    if (!$row) {
        card("Sin reportes activos", [
            "Hola **{$nombre_corto}**, revisé el sistema y no tienes ninguna solicitud de limpieza registrada con tu cédula.",
            "Si deseas solicitar una, ve al menú principal."
        ], ['🕳️ Solicitud de limpieza', '🏠 Menú principal']);
    }

    // CASO B: SÍ TIENE REPORTE (Mostrar estado directo)
    $estado = ucfirst(strtolower($row['estado']));
    $fecha_fmt = date('d/m/Y', strtotime($row['fecha']));
    
    // Mensaje personalizado según estado
    $msg_estado = "Tu solicitud está en cola de espera.";
    if ($row['estado'] == 'EN_PROCESO') { $msg_estado = "¡La cuadrilla ya está atendiendo tu sector!"; }
    if ($row['estado'] == 'FINALIZADO') { $msg_estado = "El trabajo ha sido completado con éxito."; }

    echo json_encode([
        'fulfillmentText' => "Estado de limpieza.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'info',
                'title' => '🚛 Estado de Limpieza',
                'subtitle' => "Reporte encontrado para: {$u['nombre']}"
            ],
            [
                'type' => 'description',
                'text' => [
                    "📌 **Código:** {$row['codigo']}",
                    "📅 **Fecha:** {$fecha_fmt}",
                    "📍 **Dirección:** " . substr($row['direccion'], 0, 40) . "...",
                    "",
                    "📶 **ESTADO: {$estado}**",
                    "💬 {$msg_estado}"
                ]
            ],
            ['type' => 'chips', 'options' => [['text' => '🏠 Menú principal']]]
        ]]]]]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* CONSULTAR ESTADO FUGAS (AUTOMÁTICO E INTELIGENTE) */
if ($actionNorm === 'svc.fuga.buscar') {
    verificar_salida($texto_usuario, $req, $pdo);

    // 1. RECUPERAR USUARIO
    $u = obtener_datos_usuario_seguro($req, $pdo);
    if (empty($u['cedula'])) { 
        say("Por favor, inicia sesión para consultar tus reportes."); 
    }
    $nombre_corto = formatear_nombre_corto($u['nombre']);

    // 2. AUDITORÍA
    if (function_exists('registrar_auditoria')) {
        registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'CONSULTA_ESTADO_FUGA', 'Usuario revisó estado fugas', $texto_usuario);
    }

    // 3. BUSCAR TODOS LOS REPORTES ACTIVOS (Pendientes o En Proceso)
    // Mostramos los 3 más recientes
    $st = $pdo->prepare("SELECT codigo, tipo, estado, fecha FROM reportes_fuga 
                         WHERE cedula = :c 
                         ORDER BY id DESC LIMIT 3");
    $st->execute([':c' => $u['cedula']]);
    $rows = $st->fetchAll();

    // CASO A: NO TIENE REPORTES
    if (!$rows) {
        card("Sin reportes de fuga", [
            "Hola **{$nombre_corto}**, no encontré ningún reporte de fuga registrado a tu nombre recientemente.",
            "¿Deseas reportar una ahora?"
        ], ['💦 Reporte de fugas', '🏠 Menú principal']);
    }

    // CASO B: TIENE REPORTES (Construimos la lista)
    $lista_reportes = [];
    foreach ($rows as $r) {
        $tipo_bonito = ucfirst(str_replace('_', ' ', $r['tipo'])); // Ej: "Baja presion"
        $estado = ucfirst(strtolower($r['estado']));
        $icono = ($r['estado'] == 'PENDIENTE') ? '⏳' : (($r['estado'] == 'EN_PROCESO') ? 'ben 🛠️' : '✅');
        
        $lista_reportes[] = "🔹 **{$tipo_bonito}:** {$estado} {$icono}";
        $lista_reportes[] = "   (Cód: {$r['codigo']} - " . date('d/m', strtotime($r['fecha'])) . ")";
    }

    echo json_encode([
        'fulfillmentText' => "Estado de fugas.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            [
                'type' => 'info',
                'title' => '🚰 Reportes Encontrados',
                'subtitle' => "Aquí tienes el estado de tus solicitudes, {$nombre_corto}:"
            ],
            [
                'type' => 'description',
                'text' => array_merge(
                    $lista_reportes,
                    ["", "Estamos trabajando para solucionarlo."]
                )
            ],
            ['type' => 'chips', 'options' => [['text' => '🏠 Menú principal']]]
        ]]]]]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


// QUEJAS
if ($actionNorm === 'general.quejas') {
    $texto_usuario = strtolower($req['queryResult']['queryText'] ?? '');
    $titulo = "Entiendo la espera ⏳";
    $mensaje_principal = "Nuestro equipo trabaja arduamente por zonas. A veces la alta demanda nos retrasa un poco.";
    if (strpos($texto_usuario, 'lento') !== false) { $titulo = "La seguridad es primero 👷"; $mensaje_principal = "Las cuadrillas priorizan riesgos mayores, pero tu caso está en lista."; }
    elseif (strpos($texto_usuario, 'nadie') !== false) { $titulo = "No te hemos olvidado 🤝"; $mensaje_principal = "Tu orden existe en el sistema y será atendida lo antes posible."; }
    elseif (strpos($texto_usuario, 'tiempo') !== false) { $titulo = "Lamento la demora 🗓️"; $mensaje_principal = "Agradecemos mucho tu paciencia, estamos haciendo lo posible."; }
    card($titulo, [$mensaje_principal, "", "📞 Si es una emergencia crítica, llámanos: (04) 2XXX-XXX"], ['🔎 Consultar Estado', '🏠 Menú principal']);
}

// REQUISITOS
if ($actionNorm === 'global.requisitos' || $intentNorm === 'req_guia_nueva_medidor') {
   // AGREGAR ESTO 👇
    registrar_auditoria($pdo, $u['cedula'], $u['nombre'], 'INFO_REQUISITOS', 'Consultó requisitos nuevo medidor', $texto_usuario);
    card("📄 Requisitos Nuevo Medidor", [
        "¡Nos encantaría que seas parte de nuestra empresa! 💧", "",
        "Para solicitar un nuevo medidor, por favor acércate a ventanilla con:",
        "1️⃣ Copia de Cédula y Papeleta de votación.", "2️⃣ Copia de las Escrituras.",
        "3️⃣ Certificado de no adeudar al municipio.", "4️⃣ Una carpeta manila amarilla.", "",
        "🕒 Te atendemos de Lunes a Viernes de 8:00 AM a 5:00 PM."
    ], ['🏠 Menú principal']);
}
/* ========================== DEFAULT MAESTRO (CON TRAMPAS DE ERROR) ========================== */
if ($actionNorm === 'input.unknown' || $intentNorm === 'default fallback intent') {
    
    $texto = mb_strtolower($req['queryResult']['queryText'] ?? '', 'UTF-8');
    
    // 1. RECUPERAR DATOS DE SESIÓN (SABUESO INTEGRADO)
    $ctx_maestra = ctx_get($req, 'ctx_sesion_maestra');
    $ctx_activa = ctx_get($req, 'ctx_sesion_activa');
    
    $nombre = 'Vecino';
    if ($ctx_maestra && !empty($ctx_maestra['parameters']['nombre'])) {
        $nombre = $ctx_maestra['parameters']['nombre'];
    } elseif ($ctx_activa && !empty($ctx_activa['parameters']['nombre'])) {
        $nombre = $ctx_activa['parameters']['nombre'];
    }
    
    $nombre_corto = formatear_nombre_corto($nombre);
    $cedula = $ctx_maestra['parameters']['cedula'] ?? $ctx_activa['parameters']['cedula'] ?? '';

    // Contextos de salida base
    $output_contexts = [];
    if (!empty($cedula)) {
        // Mantenemos viva la sesión maestra
        $output_contexts[] = ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999, 'parameters' => ['cedula' => $cedula, 'nombre' => $nombre]];
    }
    
    // --- A. SALIDA DE EMERGENCIA ---
    if (strpos($texto, 'menú') !== false || strpos($texto, 'menu') !== false || strpos($texto, 'salir') !== false) {
        if (!empty($cedula)) {
            echo json_encode(['followupEventInput' => ['name' => 'MENU_EVENT']], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['followupEventInput' => ['name' => 'WELCOME']], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // --- B. DETECCIÓN DE ERRORES DENTRO DE FLUJOS ---
    
    // ----------------------------------------------------------------------
    // 🔴 NUEVA TRAMPA: ERROR EN CONFIRMACIÓN DE LIMPIEZA
    // ----------------------------------------------------------------------
    $ctx_limp = ctx_get($req, 'ctx_limp_confirm');
    if ($ctx_limp) {
        // Recuperamos los datos de la memoria para volver a mostrarlos
        $p = $ctx_limp['parameters'] ?? [];
        $tel = $p['telefono'] ?? '---';
        $dir = $p['direccion'] ?? '---';
        $ref = $p['referencia'] ?? '---';
        $desc = $p['descripcion'] ?? '---';
        
        // Mensaje de Regaño + Resumen
        echo json_encode([
            'fulfillmentText' => "Opción incorrecta.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                [
                    'type' => 'info',
                    'title' => '⚠️ Opción Incorrecta',
                    'subtitle' => "{$nombre_corto}, debes seleccionar CONFIRMAR o CANCELAR orden."
                ],
                [
                    'type' => 'description',
                    'title' => '📝 Resumen de Solicitud de Alcantarillado',
                    'text' => [
                        "📞 **Tel:** {$tel}",
                        "📍 **Dir:** {$dir}", 
                        "🏠 **Ref:** {$ref}",
                        "💬 **Falla:** {$desc}"
                    ]
                ],
                [
                    'type' => 'info',
                    'title' => "⚠️ Recordatorio:",
                    'subtitle' => "Los datos deben ser reales y verificables. ¿Confirmas la orden?"
                ],
                [
                    'type' => 'chips',
                    'options' => [
                        ['text' => '🚀 CONFIRMAR ORDEN'],
                        ['text' => '❌ CANCELAR ORDEN']
                    ]
                ]
            ]]]]],
            'outputContexts' => [
                // Mantenemos vivo el contexto para que pueda intentar de nuevo
                ['name' => $req['session'].'/contexts/ctx_limp_confirm', 'lifespanCount' => 5, 'parameters' => $p],
                ['name' => $req['session'].'/contexts/ctx_sesion_maestra', 'lifespanCount' => 999]
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // ----------------------------------------------------------------------

    // 2. ERROR EN SALDO (TU CÓDIGO ORIGINAL)
    $ctx_saldo = ctx_get($req, 'ctx_saldo');
    if ($ctx_saldo) {
        $paso = $ctx_saldo['parameters']['paso'] ?? '';
        $msg_titulo = "⚠️ Opción Incorrecta";
        $msg_texto = [];
        $chips = [];

        if ($paso === 'inicio') {
            $msg_texto = ["Hola **{$nombre_corto}**, opción inválida.", "Si deseas consultar saldo, elige:", "✅ **Sí** para continuar", "❌ **No** para salir"];
            $chips = [['text'=>'✅ Sí'], ['text'=>'❌ No']];
        } 
        elseif ($paso === 'seleccion') {
            $map = $ctx_saldo['parameters']['map'] ?? [];
            $lista_contratos = [];
            if (!empty($map)) {
                foreach ($map as $k => $medidor_num) {
                    $lista_contratos[] = "🔹 **Opción {$k}:** Medidor {$medidor_num}";
                }
            }
            $msg_texto = array_merge(
                ["Hola **{$nombre_corto}**, opción incorrecta.", "Escribe la **opción correcta** de la siguiente lista:"],
                $lista_contratos,
                ["", "Ejemplo: Escribe **1**"]
            );
            $chips = [['text'=>'🏠 Menú principal']];
        } 
        elseif ($paso === 'confirmacion') {
            $msg_texto = ["Hola **{$nombre_corto}**, opción incorrecta.", "¿Es este tu contrato?", "Responde **Sí** o cambia de contrato."];
            $chips = [['text'=>'✅ Sí, ver saldo'], ['text'=>'🔄 Cambiar contrato']];
        } 
        elseif ($paso === 'menu_saldo') {
            $medidor = $ctx_saldo['parameters']['medidor_numero'] ?? '';
            $msg_texto = ["Hola **{$nombre_corto}**, opción incorrecta.", "Estoy conectado al medidor **{$medidor}**. ¿Qué deseas ver?"];
            $chips = [['text' => '💰 Ver saldo pendiente'], ['text' => '📆 Ver historial pagos'], ['text' => '🏠 Menú principal']];
        }

        $output_contexts[] = ['name' => $req['session'].'/contexts/ctx_saldo', 'lifespanCount' => 5, 'parameters' => $ctx_saldo['parameters']];

        echo json_encode([
            'fulfillmentText' => "Error saldo.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'description', 'title' => $msg_titulo, 'text' => $msg_texto],
                ['type' => 'chips', 'options' => $chips]
            ]]]]],
            'outputContexts' => $output_contexts
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // --- C. ERROR EN MENÚ PRINCIPAL ---
    if (!empty($cedula)) {
        $msg = ["Hola **{$nombre_corto}**, opción no válida.", "Por favor usa el menú 👇"];
        $output_contexts[] = ['name' => $req['session'].'/contexts/ctx_menu', 'lifespanCount' => 50];

        echo json_encode([
            'fulfillmentText' => "Opción no válida.",
            'fulfillmentMessages' => [['payload' => ['richContent' => [[
                ['type' => 'description', 'title' => '🤔 Opción no válida', 'text' => $msg],
                ['type' => 'chips', 'options' => [
                    ['text'=>'💵Consulta de saldo'],
                    ['text'=>'🧾Requisitos de tramites'],
                    ['text'=>'🕳️ solicitud de limpieza alcantarillado'],
                    ['text'=>'💦Reporte de fugas'],
                    ['text'=>'🤝 Otros servicios']
                ]]
            ]]]]],
            'outputContexts' => $output_contexts
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // --- D. NO LOGUEADO ---
    echo json_encode([
        'fulfillmentText' => "Hola.",
        'fulfillmentMessages' => [['payload' => ['richContent' => [[
            ['type' => 'description', 'title' => '👋 Hola', 'text' => ["No estoy seguro de qué necesitas.", "Intenta escribir 'Hola' para comenzar."]]
        ]]]]],
        'outputContexts' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fallback final
flog("Sin manejador: {$action}");
say("No estoy seguro de qué necesitas. Intenta con el menú principal.");
?>