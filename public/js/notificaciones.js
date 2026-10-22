/**
 * Sistema de Notificaciones de Escritorio y Recordatorios en Tiempo Real
 * Estudio Jurídico & Asociados
 */

(function () {
    'use strict';

    // Generador de sonido de alerta jurídica (Web Audio API)
    function reproducirSonidoAlerta() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            // Primer tono (587.33 Hz - Re 5)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, ctx.currentTime);
            gain1.gain.setValueAtTime(0.2, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.3);

            // Segundo tono armonioso (880 Hz - La 5)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
            gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.12);
            osc2.stop(ctx.currentTime + 0.6);
        } catch (e) {
            console.warn('Audio no disponible:', e);
        }
    }

    // Solicitar permiso para Notificaciones de Escritorio en Windows
    window.solicitarPermisoNotificaciones = function () {
        if (!('Notification' in window)) {
            alert('Su navegador no soporta notificaciones de escritorio.');
            return;
        }

        Notification.requestPermission().then(function (permission) {
            actualizarEstadoBotonPermiso();
            if (permission === 'granted') {
                reproducirSonidoAlerta();
                const n = new Notification('Notificaciones de Escritorio Activadas', {
                    body: 'El sistema le avisará automáticamente de sus audiencias, citas y plazos procesales directamente en su pantalla.',
                    icon: '/img/logo.png',
                    requireInteraction: false
                });
                n.onclick = () => window.focus();
                mostrarToastAlerta('Notificaciones de escritorio activadas correctamente.', 'success');
            } else if (permission === 'denied') {
                alert('Las notificaciones fueron bloqueadas en la configuración de su navegador. Por favor habilítelas en los permisos del sitio (icono de candado en la barra de direcciones).');
            }
        });
    };

    // Disparar una notificación de prueba para que el usuario verifique
    window.probarNotificacionEscritorio = function () {
        if (!('Notification' in window)) {
            alert('Su navegador no soporta notificaciones de escritorio.');
            return;
        }

        if (Notification.permission !== 'granted') {
            solicitarPermisoNotificaciones();
            return;
        }

        reproducirSonidoAlerta();
        const n = new Notification('PRUEBA DE RECORDATORIO', {
            body: '¡Excelente! Las notificaciones de escritorio están funcionando correctamente en su pantalla.',
            icon: '/img/logo.png',
            requireInteraction: true
        });
        n.onclick = function () {
            window.focus();
            n.close();
        };
        mostrarToastAlerta('Se envió una notificación de prueba a su escritorio.', 'info');
    };

    // Enviar notificación a escritorio (Windows Action Center)
    function enviarNotificacionEscritorio(alerta) {
        reproducirSonidoAlerta();

        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const n = new Notification(`${alerta.badge || alerta.tipo}: ${alerta.titulo}`, {
                    body: `Hora: ${alerta.hora}\n${alerta.detalle || alerta.lugar || ''}\nHaga clic para abrir en el sistema.`,
                    icon: '/img/logo.png',
                    tag: 'alerta_' + alerta.id,
                    requireInteraction: true
                });

                n.onclick = function () {
                    window.focus();
                    if (alerta.link && alerta.link !== '#') {
                        window.location.href = alerta.link;
                    }
                    n.close();
                };
            } catch (e) {
                console.error('Error al emitir notificación:', e);
            }
        }

        // Mostrar también alerta visual en la interfaz web
        mostrarToastAlerta(`<strong>${alerta.badge || alerta.tipo}:</strong> ${alerta.titulo} (${alerta.hora})`, 'warning', alerta.link);

        // Enviar también la alerta en vivo al celular Telegram 
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/alertas/externas/despachar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                titulo: alerta.titulo,
                hora: alerta.hora,
                detalle: alerta.detalle || alerta.lugar || '',
                caso: alerta.caso || ''
            })
        }).catch(err => console.debug('Aviso externo Telegram:', err));
    }

    // Funciones del Modal de Configuración y Prueba de Telegram (@Bethg_bot)
    window.abrirModalTelegramGmail = function () {
        const modal = document.getElementById('modalTelegramGmail');
        const dropdown = document.getElementById('dropdownCampanaNotificaciones');
        if (dropdown) dropdown.style.display = 'none';
        if (!modal) return;
        modal.style.display = 'flex';

        fetch('/alertas/externas/config', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            const elTgId  = document.getElementById('cfgMiTelegramId');
            const elToken = document.getElementById('cfgTelegramToken');

            if (elTgId)  elTgId.value  = data.mi_telegram_id || '';
            if (elToken && data.telegram_bot_token) elToken.value = data.telegram_bot_token;
        })
        .catch(() => {});
    };

    window.cerrarModalTelegramGmail = function () {
        const modal = document.getElementById('modalTelegramGmail');
        if (modal) modal.style.display = 'none';
    };

    function mostrarEstadoModalExterno(html, exito = true) {
        const box = document.getElementById('estadoConfigExterna');
        if (!box) return;
        box.style.display = 'block';
        box.style.background = exito ? '#dcfce7' : '#fee2e2';
        box.style.color = exito ? '#166534' : '#991b1b';
        box.style.borderLeft = exito ? '4px solid #16a34a' : '4px solid #dc2626';
        box.innerHTML = html;
    }

    window.guardarConfigTelegramGmail = function (callback) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const payload = {
            mi_telegram_id: document.getElementById('cfgMiTelegramId')?.value || '',
            telegram_bot_token: document.getElementById('cfgTelegramToken')?.value || ''
        };

        mostrarEstadoModalExterno('Guardando configuración de Telegram...', true);

        fetch('/alertas/externas/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (data.mi_telegram_id) {
                    const elTgId = document.getElementById('cfgMiTelegramId');
                    if (elTgId) elTgId.value = data.mi_telegram_id;
                }
                mostrarEstadoModalExterno(' ' + data.mensaje, true);
                if (typeof callback === 'function') callback();
            } else {
                mostrarEstadoModalExterno('' + (data.mensaje || 'Error al guardar'), false);
            }
        })
        .catch(() => mostrarEstadoModalExterno('Error de red al guardar la configuración.', false));
    };

    window.detectarMiChatIdTelegram = function () {
        guardarConfigTelegramGmail(function () {
            const boxLista = document.getElementById('listaChatsDetectados');
            if (boxLista) {
                boxLista.style.display = 'block';
                boxLista.innerHTML = 'Buscando mensajes recientes enviados a @Bethg_bot en Telegram...';
            }

            fetch('/alertas/externas/detectar-telegram', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    if (boxLista) boxLista.innerHTML = `<span style="color:#dc2626;"> ${data.mensaje}</span>`;
                    mostrarEstadoModalExterno('' + data.mensaje, false);
                    return;
                }
                const chats = data.chats || [];
                if (chats.length === 0) {
                    const msg = 'Aún no se detectaron mensajes en <strong>@Bethg_bot</strong>. Primero haz clic en <strong>"✈️ Abrir t.me/Bethg_bot en Telegram"</strong> arriba, presiona <strong>Iniciar (Start)</strong> o envíale "Hola", y luego vuelve a pulsar <strong>🔍 Detectar mi Chat ID</strong>.';
                    if (boxLista) boxLista.innerHTML = msg;
                    mostrarEstadoModalExterno(msg, false);
                    return;
                }
                const ultimo = chats[chats.length - 1];
                const inputId = document.getElementById('cfgMiTelegramId');
                if (inputId) inputId.value = String(ultimo.chat_id);

                let html = '<div style="margin-bottom:4px;font-weight:600;color:var(--navy);">Tu cuenta de Telegram fue vinculada automáticamente:</div>';
                chats.forEach(c => {
                    html += `<button type="button" onclick="document.getElementById('cfgMiTelegramId').value='${c.chat_id}'; guardarConfigTelegramGmail();" style="margin:2px 4px 2px 0;padding:4px 8px;border-radius:4px;border:1px solid var(--gold);background:#fefce8;cursor:pointer;font-size:0.75rem;">
                        <strong>${c.nombre}</strong> ${c.username} &rarr; ID: <code>${c.chat_id}</code>
                    </button>`;
                });
                if (boxLista) boxLista.innerHTML = html;
                mostrarEstadoModalExterno(`Chat ID <strong>${ultimo.chat_id}</strong> (${ultimo.nombre}) detectado y guardado. ¡Ahora pulsa "Probar Envío a mi Telegram"!`, true);
            });
        });
    };

    window.probarEnvioExterno = function (canal) {
        guardarConfigTelegramGmail(function () {
            mostrarEstadoModalExterno('Enviando alerta de prueba a tu Telegram...', true);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/alertas/externas/probar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ canal: 'telegram' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.mi_telegram_id) {
                    const elTgId = document.getElementById('cfgMiTelegramId');
                    if (elTgId) elTgId.value = data.mi_telegram_id;
                }
                const res = data.resultados || {};
                if (res.telegram) {
                    mostrarEstadoModalExterno(
                        (res.telegram.ok ? ' <strong>Telegram:</strong> ' : ' <strong>Telegram:</strong> ') + res.telegram.mensaje,
                        !!res.telegram.ok
                    );
                } else {
                    mostrarEstadoModalExterno('Respuesta inesperada del servidor.', false);
                }
            })
            .catch(() => mostrarEstadoModalExterno('Error al ejecutar la prueba de envío.', false));
        });
    };

    // Toast interactivo flotante en pantalla
    function mostrarToastAlerta(mensajeHtml, tipo = 'info', link = null) {
        const id = 'toast_alerta_' + Date.now();
        const toast = document.createElement('div');
        toast.id = id;
        toast.style.cssText = `
            position: fixed; top: 75px; right: 25px; z-index: 99999;
            background: #0D1525; color: #fff; border: 1px solid #C9A84C;
            border-left: 5px solid ${tipo === 'warning' ? '#e02424' : '#C9A84C'};
            padding: 16px 20px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            font-size: 0.88rem; max-width: 380px; display: flex; flex-direction: column; gap: 8px;
            animation: slideInRight 0.3s ease;
        `;

        toast.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                <div style="line-height:1.4;">${mensajeHtml}</div>
                <button type="button" onclick="document.getElementById('${id}').remove()" style="background:none; border:none; color:#C9A84C; font-size:1.1rem; cursor:pointer; padding:0; line-height:1;">&times;</button>
            </div>
            ${link ? `<div style="text-align:right;"><a href="${link}" style="color:#C9A84C; font-weight:600; text-decoration:underline; font-size:0.8rem;">Ver en el sistema &rarr;</a></div>` : ''}
        `;

        document.body.appendChild(toast);
        setTimeout(() => {
            if (document.getElementById(id)) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }
        }, 12000);
    }

    // Cambiar la anticipación global de recordatorios (5 min, 10 min, etc.)
    window.cambiarAnticipacionGlobal = function (minutos) {
        localStorage.setItem('anticipacion_notificaciones', minutos);
        const texto = minutos === '0' ? 'a la hora exacta' : `${minutos} minutos antes`;
        mostrarToastAlerta(`Anticipación configurada: le avisaremos ${texto}.`, 'success');
        consultarAlertasPendientes();
    };

    // Actualizar botón de permiso en el menú superior
    function actualizarEstadoBotonPermiso() {
        const btn = document.getElementById('btnPermisoNotificaciones');
        const badgePermiso = document.getElementById('badgeEstadoNotificaciones');
        const selectAnticipacion = document.getElementById('selectAnticipacionGlobal');

        if (selectAnticipacion) {
            const guardado = localStorage.getItem('anticipacion_notificaciones') || '5';
            selectAnticipacion.value = guardado;
        }

        if (!('Notification' in window)) {
            if (btn) btn.style.display = 'none';
            return;
        }

        if (Notification.permission === 'granted') {
            if (badgePermiso) {
                badgePermiso.innerHTML = '<span style="color:#22c55e;">● Notificaciones de Escritorio Activas</span>';
            }
            if (btn) {
                btn.innerHTML = 'Probar Sonido/Aviso';
                btn.onclick = window.probarNotificacionEscritorio;
            }
        } else {
            if (badgePermiso) {
                badgePermiso.innerHTML = '<span style="color:#f59e0b;">● Notificaciones Desactivadas</span>';
            }
            if (btn) {
                btn.innerHTML = 'Activar Notificaciones';
                btn.onclick = window.solicitarPermisoNotificaciones;
            }
        }
    }

    // Consulta periódica al servidor para verificar eventos de hoy
    function consultarAlertasPendientes() {
        fetch('/alertas/pendientes', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            const alertas = data.alertas || [];
            actualizarCampanaAlertas(alertas);

            // Registro en localStorage para no repetir la misma alerta hoy
            const hoyKey = 'notif_registradas_' + (data.fecha_servidor || new Date().toISOString().slice(0, 10));
            let notificados = JSON.parse(localStorage.getItem(hoyKey) || '[]');

            alertas.forEach(alerta => {
                // Anticipación individual del evento, o la global elegida por el usuario (por defecto 5 min)
                const anticipacion = (alerta.recordatorio_minutos !== undefined && alerta.recordatorio_minutos !== null)
                    ? parseInt(alerta.recordatorio_minutos)
                    : parseInt(localStorage.getItem('anticipacion_notificaciones') || '5');

                const keyPrevio = 'previo_' + alerta.id + '_' + alerta.hora + '_' + anticipacion;
                const keyExacto = 'exacto_' + alerta.id + '_' + alerta.hora;

                if (alerta.minutos !== null && alerta.minutos !== undefined) {
                    // 1. RECORDATORIO CON ANTICIPACIÓN (ej. 5 minutos antes):
                    if (anticipacion > 0 && alerta.minutos <= anticipacion && alerta.minutos > 0) {
                        if (!notificados.includes(keyPrevio)) {
                            enviarNotificacionEscritorio({
                                ...alerta,
                                titulo: `En ${alerta.minutos} min: ${alerta.titulo}`,
                                badge: `Recordatorio (${alerta.minutos} min antes)`
                            });
                            notificados.push(keyPrevio);
                            localStorage.setItem(hoyKey, JSON.stringify(notificados));
                        }
                    }

                    // 2. ALERTA A LA HORA EXACTA (inicia ahora o en los últimos 5 minutos):
                    if (alerta.minutos <= 0 && alerta.minutos >= -5) {
                        if (!notificados.includes(keyExacto)) {
                            enviarNotificacionEscritorio({
                                ...alerta,
                                titulo: `ES LA HORA: ${alerta.titulo}`,
                                badge: `Iniciando Ahora`
                            });
                            notificados.push(keyExacto);
                            localStorage.setItem(hoyKey, JSON.stringify(notificados));
                        }
                    }
                }
            });
        })
        .catch(err => console.debug('Verificación de alertas:', err));
    }

    // Actualizar campana en la barra superior
    function actualizarCampanaAlertas(alertas) {
        const badge = document.getElementById('campanaNotificacionesBadge');
        const listaContenedor = document.getElementById('listaAlertasDropdown');

        if (badge) {
            if (alertas.length > 0) {
                badge.innerText = alertas.length;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        }

        if (listaContenedor) {
            if (alertas.length === 0) {
                listaContenedor.innerHTML = `
                    <div style="padding:15px; text-align:center; color:#888; font-size:0.85rem;">
                        No tiene audiencias ni citas programadas para hoy.
                    </div>
                `;
            } else {
                let html = '';
                alertas.forEach(a => {
                    html += `
                        <div style="padding:10px 14px; border-bottom:1px solid rgba(201,168,76,0.15); display:flex; gap:10px; align-items:center;">
                            <span style="font-size:1.1rem;"></span>
                            <div style="flex:1;">
                                <div style="font-weight:600; color:var(--navy); font-size:0.85rem;">${a.titulo}</div>
                                <div style="font-size:0.75rem; color:#666;">
                                    <span style="color:#C9A84C; font-weight:600;"> ${a.hora}</span> | ${a.detalle || a.lugar || ''}
                                </div>
                            </div>
                            ${a.link ? `<a href="${a.link}" style="font-size:0.75rem; color:#C9A84C; text-decoration:none; font-weight:600;">Ver</a>` : ''}
                        </div>
                    `;
                });
                listaContenedor.innerHTML = html;
            }
        }
    }

    // Toggle dropdown de campana
    window.toggleCampanaNotificaciones = function (e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('dropdownCampanaNotificaciones');
        if (!dropdown) return;
        const visible = dropdown.style.display === 'block';
        dropdown.style.display = visible ? 'none' : 'block';
    };

    // Cerrar al hacer clic afuera
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('dropdownCampanaNotificaciones');
        const contenedor = document.getElementById('contenedorCampanaNotificaciones');
        if (dropdown && dropdown.style.display === 'block') {
            if (contenedor && !contenedor.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        }
    });

    // Iniciar al cargar el DOM
    document.addEventListener('DOMContentLoaded', function () {
        actualizarEstadoBotonPermiso();
        // Consultar de inmediato al abrir cualquier página
        consultarAlertasPendientes();
        // Y consultar periódicamente cada 10 segundos para precisión exacta al minuto
        setInterval(consultarAlertasPendientes, 10000);
    });
})();
