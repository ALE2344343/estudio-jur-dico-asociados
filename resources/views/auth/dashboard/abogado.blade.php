@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<!-- CALENDARIO DE AUDIENCIAS Y PLAZOS (AGENDA DEL ABOGADO) -->
<div class="table-card animate-in" style="margin-bottom:30px;padding:24px;border-radius:14px;background:white;box-shadow:0 4px 16px rgba(0,0,0,0.06);border:1px solid rgba(201,168,76,0.25);">
    
    <!-- Barra Superior del Calendario -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <button type="button" id="btnCalHoy" style="background:white;border:1px solid #D1D5DB;border-radius:20px;padding:6px 18px;font-size:0.85rem;font-weight:600;color:#374151;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='white'">
                Hoy
            </button>
            <div style="display:flex;align-items:center;gap:4px;">
                <button type="button" id="btnCalPrev" style="background:none;border:none;font-size:1.2rem;cursor:pointer;padding:6px 12px;border-radius:50%;color:#4B5563;transition:background 0.2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='none'">
                    &lt;
                </button>
                <button type="button" id="btnCalNext" style="background:none;border:none;font-size:1.2rem;cursor:pointer;padding:6px 12px;border-radius:50%;color:#4B5563;transition:background 0.2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='none'">
                    &gt;
                </button>
            </div>
            <div id="calTituloMes" style="font-size:1.45rem;font-weight:600;color:var(--navy);font-family:'Segoe UI',sans-serif;margin-left:8px;">
                <!-- Mes y Año dinámicos -->
            </div>
        </div>
<button type="button" onclick="abrirModalTelegramGmail()" 
        style="background: linear-gradient(145deg, #0D1525, #17233c); color: #C9A84C; border: 1px solid rgba(201, 168, 76, 0.6); border-radius: 24px; padding: 10px 18px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.25); transition: all 0.3s ease;"
        onmouseover="this.style.borderColor='#C9A84C'; this.style.boxShadow='0 6px 18px rgba(201, 168, 76, 0.25)'; this.style.transform='translateY(-1px)';"
        onmouseout="this.style.borderColor='rgba(201, 168, 76, 0.6)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.25)'; this.style.transform='translateY(0)';">
    
    <svg width="18" height="18" viewBox="0 0 24 24" fill="#C9A84C" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.67-.52.36-.99.53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.38-.49 1.07-.75 4.19-1.82 6.98-3.02 8.38-3.6 3.99-1.66 4.82-1.95 5.36-1.96.12 0 .38.03.55.17.14.12.18.28.2.43-.02.07-.02.21-.03.29z"/>
    </svg>
    
    <span>Configurar Telegram <span style="opacity:0.75; font-weight:400;">(@Bethg_bot)</span></span>
</button>
            <button type="button" onclick="abrirModalCrearAnotacion()" style="background:#0F766E;color:white;border:none;border-radius:20px;padding:8px 20px;font-weight:600;font-size:0.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background 0.2s;" onmouseover="this.style.background='#115E59'" onmouseout="this.style.background='#0F766E'">
                <span>+</span> Nueva Anotación
            </button>
            <button type="button" onclick="location.reload()" style="background:#DCEBFE;color:#1D4ED8;border:none;border-radius:20px;padding:8px 24px;font-weight:600;font-size:0.85rem;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#BFDBFE'" onmouseout="this.style.background='#DCEBFE'">
                Actualizar
            </button>
        </div>
    </div>

    <!-- Contenedor de la Cuadrícula del Calendario -->
    <div style="border:1px solid #E5E7EB;border-radius:10px;overflow:hidden;background:#F9FAFB;">
        
        <!-- Fila de nombres de días -->
        <div style="display:grid;grid-template-columns:repeat(7,1fr);background:#F9FAFB;border-bottom:1px solid #E5E7EB;text-align:center;">
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">DOM</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">LUN</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">MAR</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">MIÉ</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">JUE</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">VIE</div>
            <div style="padding:12px 0;font-size:0.75rem;font-weight:700;color:#6B7280;letter-spacing:0.5px;">SÁB</div>
        </div>

        <!-- Celdas de Días generadas por JS -->
        <div id="calGridDias" style="display:grid;grid-template-columns:repeat(7,1fr);background:#E5E7EB;gap:1px;">
            <!-- Celdas -->
        </div>

    </div>

</div>

<!-- Modal para ver detalle del evento al hacer clic -->
<div id="modalEventoCal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:90%;max-width:480px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid #0F766E;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 id="modalEventoTitulo" style="margin:0;font-size:1.1rem;color:var(--navy);font-family:'Cinzel',serif;">Detalle</h3>
            <button onclick="document.getElementById('modalEventoCal').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
        </div>
        <div id="modalEventoCuerpo" style="font-size:0.9rem;line-height:1.6;color:#374151;margin-bottom:20px;">
            <!-- Contenido dinámico -->
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;align-items:center;">
            <button type="button" id="btnEditarNotaModal" onclick="abrirModalEditarAnotacionActual()" class="btn-edit" title="Editar" style="display:none;align-items:center;justify-content:center;">
                <svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:currentColor;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </button>
            <form id="formEliminarNota" method="POST" action="" onsubmit="return confirm('¿Eliminar esta anotación personal?')" style="display:none;margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" title="Eliminar" style="display:inline-flex;align-items:center;justify-content:center;">
                    <svg style="width:14px;height:14px;fill:currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                </button>
            </form>
            <button type="button" onclick="document.getElementById('modalEventoCal').style.display='none'" class="btn-secondary" style="font-size:0.8rem;padding:6px 14px;">Cerrar</button>
            <a id="btnGoogleCalendarModal" href="#" target="_blank" rel="noopener noreferrer" class="btn-secondary" style="display:none;background:#4285F4;color:white;border:none;font-size:0.8rem;padding:6px 14px;align-items:center;gap:6px;text-decoration:none;font-weight:600;border-radius:4px;" title="Agendar en tu cuenta de Google Calendar para que suene en tu celular">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5v-5z"/></svg>
                Google Calendar
            </a>
            <a id="modalEventoLink" href="#" class="btn-primary" style="display:none;font-size:0.8rem;padding:6px 14px;">Ver Caso Completo</a>
        </div>
    </div>
</div>

<!-- Modal para crear nueva anotación personal -->
<div id="modalCrearAnotacion" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:90%;max-width:480px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid #2563EB;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;font-size:1.1rem;color:var(--navy);font-family:'Cinzel',serif;">Nueva Anotación Personal</h3>
            <button onclick="document.getElementById('modalCrearAnotacion').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
        </div>
        <form action="{{ route('calendario.anotaciones.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Título / Recordatorio *</label>
                <input type="text" name="titulo" required placeholder="Ej: Llamar al cliente, Revisar fojas" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Fecha *</label>
                    <input type="date" id="inputNotaFecha" name="fecha" required value="{{ date('Y-m-d') }}" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Hora (Opcional)</label>
                    <input type="time" name="hora" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Avisarme con anticipación</label>
                    <select name="recordatorio_minutos" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                        <option value="5" selected>5 minutos antes (Recomendado)</option>
                        <option value="10">10 minutos antes</option>
                        <option value="15">15 minutos antes</option>
                        <option value="30">30 minutos antes</option>
                        <option value="60">1 hora antes</option>
                        <option value="0">A la hora exacta</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Color de la Etiqueta</label>
                    <select name="color" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                        <option value="#2563EB">Azul Profesional</option>
                        <option value="#7C3AED">Morado / Judicial</option>
                        <option value="#D97706">Naranja / Urgente</option>
                        <option value="#059669">Verde / Cumplido</option>
                        <option value="#DC2626">Rojo / Crítico</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Detalle o Notas Privadas</label>
                <textarea name="descripcion" rows="3" placeholder="Detalles de la tarea o recordatorio..." style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
            </div>
            <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:6px;padding:8px 12px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;font-size:0.76rem;color:#475569;">
                <span>Se enviará alerta automática a tu Telegram (@Bethg_bot)</span>
                <button type="button" onclick="document.getElementById('modalCrearAnotacion').style.display='none'; abrirModalTelegramGmail();" style="background:none;border:none;color:#2563EB;font-weight:700;cursor:pointer;text-decoration:underline;font-size:0.75rem;">
                    Configurar Telegram
                </button>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('modalCrearAnotacion').style.display='none'" class="btn-secondary" style="font-size:0.8rem;padding:6px 14px;">Cancelar</button>
                <button type="submit" class="btn-primary" style="background:#2563EB;font-size:0.8rem;padding:6px 14px;">Guardar y Notificar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para editar anotación personal -->
<div id="modalEditarAnotacion" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;width:90%;max-width:480px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.3);border-top:5px solid #2563EB;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;font-size:1.1rem;color:var(--navy);font-family:'Cinzel',serif;">Editar Recordatorio / Anotación</h3>
            <button onclick="document.getElementById('modalEditarAnotacion').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
        </div>
        <form id="formEditarAnotacion" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Título / Recordatorio *</label>
                <input type="text" id="editNotaTitulo" name="titulo" required placeholder="Ej: Llamar al cliente, Revisar fojas" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Fecha *</label>
                    <input type="date" id="editNotaFecha" name="fecha" required style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Hora (Opcional)</label>
                    <input type="time" id="editNotaHora" name="hora" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Avisarme con anticipación</label>
                    <select id="editNotaRecordatorio" name="recordatorio_minutos" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                        <option value="5">5 minutos antes (Recomendado)</option>
                        <option value="10">10 minutos antes</option>
                        <option value="15">15 minutos antes</option>
                        <option value="30">30 minutos antes</option>
                        <option value="60">1 hora antes</option>
                        <option value="0">A la hora exacta</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Color de la Etiqueta</label>
                    <select id="editNotaColor" name="color" style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;background:white;">
                        <option value="#2563EB">Azul Profesional</option>
                        <option value="#7C3AED">Morado / Judicial</option>
                        <option value="#D97706">Naranja / Urgente</option>
                        <option value="#059669">Verde / Cumplido</option>
                        <option value="#DC2626">Rojo / Crítico</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:0.8rem;font-weight:bold;margin-bottom:4px;color:var(--navy);">Detalle o Notas Privadas</label>
                <textarea id="editNotaDescripcion" name="descripcion" rows="3" placeholder="Detalles de la tarea o recordatorio..." style="width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="document.getElementById('modalEditarAnotacion').style.display='none'" class="btn-secondary" style="font-size:0.8rem;padding:6px 14px;">Cancelar</button>
                <button type="submit" class="btn-primary" style="background:#2563EB;font-size:0.8rem;padding:6px 14px;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas -->
<div class="stats-grid animate-in">
    <div class="stat-card">
        <div class="stat-icon gold">
            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $totalClientes }}</div>
            <div class="stat-label">Total Clientes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy">
            <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.8.5 13 .5c-1.72 0-3.24.84-4.17 2.12L8 4 7.17 2.62C6.24 1.34 4.72.5 3 .5.2.5-2 2.54-2 4.66c0 .46.11.9.18 1.34H-4v2h2v12h16V8h2V6z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $totalCasos }}</div>
            <div class="stat-label">Total NUREJ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $casosActivos }}</div>
            <div class="stat-label">NUREJ Activos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-num">{{ $casosUrgentes }}</div>
            <div class="stat-label">NUREJ Urgentes</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
    <!-- Casos Recientes -->
    <div class="table-card animate-in" style="animation-delay:0.1s">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                CASOS RECIENTES
            </div>
            <a href="{{ route('casos.index') }}" class="btn-primary" style="font-size:0.7rem;padding:6px 14px;">Ver todos</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>NUREJ</th>
                    <th>Titulo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($casosRecientes as $caso)
                <tr>
                    <td><a href="{{ route('casos.show', $caso->id) }}" style="color:var(--gold-dark);text-decoration:none;font-family:'Cinzel',serif;font-size:0.78rem;">{{ $caso->numero_caso }}</a></td>
                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $caso->titulo }}</td>
                    <td><span class="badge badge-closed">{{ $caso->tipo }}</span></td>
                    <td>
                        @php $badge = $caso->badge_estado; @endphp
                        <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay NUREJ registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Clientes Recientes -->
    <div class="table-card animate-in" style="animation-delay:0.2s">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                CLIENTES RECIENTES
            </div>
            <a href="{{ route('clientes.index') }}" class="btn-primary" style="font-size:0.7rem;padding:6px 14px;">Ver todos</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Coddigo</th>
                    <th>Nombre</th>
                    <th>CI</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientesRecientes as $cliente)
                <tr>
                    <td><span style="font-family:'Cinzel',serif;font-size:0.78rem;color:var(--gold-dark);">{{ $cliente->codigo }}</span></td>
                    <td><a href="{{ route('clientes.show', $cliente->id) }}" style="color:var(--navy);text-decoration:none;">{{ $cliente->nombre_completo }}</a></td>
                    <td style="font-size:0.82rem;color:var(--text-gray);">{{ $cliente->ci }}</td>
                    <td><span class="badge {{ $cliente->estado === 'activo' ? 'badge-active' : 'badge-closed' }}">{{ ucfirst($cliente->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Accesos rapidos -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:25px;" class="animate-in">
    @php
        $accesos = [
            ['route'=>'clientes.index','label'=>'Mis Clientes','color'=>'#C9A84C'],
            ['route'=>'casos.index','label'=>'Mis NUREJ','color'=>'#1A2744'],
            ['route'=>'documentos.index','label'=>'Documentos','color'=>'#28a745'],
            ['route'=>'leyes.index','label'=>'Leyes Bolivia','color'=>'#dc3545'],
        ];
    @endphp
    @foreach($accesos as $acc)
    <a href="{{ route($acc['route']) }}" data-border-color="{{ $acc['color'] }}" class="acceso-rapido-card" style="background:white;border-radius:12px;padding:20px;text-align:center;text-decoration:none;border:1px solid rgba(201,168,76,.15);box-shadow:0 4px 12px rgba(0,0,0,.06);transition:all .3s ease;display:block;border-top:3px solid #C9A84C;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="font-family:'Cinzel',serif;font-size:0.9rem;font-weight:bold;color:var(--navy);letter-spacing:1px;text-transform:uppercase;padding:15px 0;">{{ $acc['label'] }}</div>
    </a>
    @endforeach
</div>


    <!-- Audiencias -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7v-5z"/></svg>
                PROXIMAS AUDIENCIAS
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>NUREJ</th>
                    <th>Abogado Asignado</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audiencias as $aud)
                <tr>
                    <td style="font-weight:600;color:var(--gold-dark);">{{ \Carbon\Carbon::parse($aud->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('casos.show', $aud->caso_id) }}" style="color:var(--navy);text-decoration:none;font-weight:bold;">{{ $aud->caso->numero_caso }}</a></td>
                    <td>
                        @if($aud->caso && $aud->caso->abogado)
                            <span style="font-weight:600;color:var(--navy);">{{ $aud->caso->abogado->nombre_completo }}</span>
                        @else
                            <span style="color:#e74c3c;font-style:italic;">Sin asignar</span>
                        @endif
                    </td>
                    <td style="font-size:0.85rem;color:var(--text-gray);">{{ $aud->titulo }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-gray);padding:25px;">No hay audiencias programadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@push('scripts')
<script id="eventos-calendario-data" type="application/json">{!! json_encode($eventosCalendario ?? []) !!}</script>
<script>
    document.querySelectorAll('.acceso-rapido-card[data-border-color]').forEach(el => {
        el.style.borderTopColor = el.getAttribute('data-border-color');
    });
    function cambiarColorTema(color) {
        document.documentElement.style.setProperty('--navy', color);
        document.documentElement.style.setProperty('--gradient-navy', `linear-gradient(135deg, ${color} 0%, #0D1525 100%)`);
        localStorage.setItem('temaColor', color);
    }

    // --- LÓGICA DEL CALENDARIO MENSUAL DE AUDIENCIAS Y PLAZOS ---
    const eventosCalendario = JSON.parse(document.getElementById('eventos-calendario-data')?.textContent || '[]');
    const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const nombresMesesCortos = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    let fechaCal = new Date();
    let mesCal = fechaCal.getMonth();
    let anioCal = fechaCal.getFullYear();

    function renderizarCalendario() {
        const primerDiaMes = new Date(anioCal, mesCal, 1);
        const ultimoDiaMes = new Date(anioCal, mesCal + 1, 0);
        const diasEnMes = ultimoDiaMes.getDate();
        const diaSemanaInicio = primerDiaMes.getDay(); // 0 = Domingo

        const ultimoDiaMesAnterior = new Date(anioCal, mesCal, 0).getDate();

        const titElem = document.getElementById('calTituloMes');
        if (titElem) {
            titElem.textContent = `${nombresMeses[mesCal]} ${anioCal}`;
        }

        const grid = document.getElementById('calGridDias');
        if (!grid) return;
        grid.innerHTML = '';

        const hoy = new Date();
        const hoyStr = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;

        // 1. Días del mes previo
        for (let i = diaSemanaInicio - 1; i >= 0; i--) {
            const diaNum = ultimoDiaMesAnterior - i;
            const mesPrev = mesCal === 0 ? 11 : mesCal - 1;
            const anioPrev = mesCal === 0 ? anioCal - 1 : anioCal;
            const fechaStr = `${anioPrev}-${String(mesPrev + 1).padStart(2, '0')}-${String(diaNum).padStart(2, '0')}`;
            const etiqueta = (diaNum === 1 || i === diaSemanaInicio - 1) ? nombresMesesCortos[mesPrev] : null;
            grid.appendChild(crearCeldaDia(diaNum, fechaStr, false, etiqueta, false));
        }

        // 2. Días del mes actual
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fechaStr = `${anioCal}-${String(mesCal + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const esHoy = (fechaStr === hoyStr);
            const etiqueta = (dia === 1) ? nombresMesesCortos[mesCal] : null;
            grid.appendChild(crearCeldaDia(dia, fechaStr, true, etiqueta, esHoy));
        }

        // 3. Días del mes siguiente
        const celdasTotales = diaSemanaInicio + diasEnMes;
        const celdasFaltantes = (celdasTotales % 7 === 0) ? 0 : 7 - (celdasTotales % 7);
        for (let dia = 1; dia <= celdasFaltantes; dia++) {
            const mesNext = mesCal === 11 ? 0 : mesCal + 1;
            const anioNext = mesCal === 11 ? anioCal + 1 : anioCal;
            const fechaStr = `${anioNext}-${String(mesNext + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const etiqueta = (dia === 1) ? nombresMesesCortos[mesNext] : null;
            grid.appendChild(crearCeldaDia(dia, fechaStr, false, etiqueta, false));
        }
    }

    function crearCeldaDia(numero, fechaStr, esMesActual, etiquetaMes, esHoy) {
        const celda = document.createElement('div');
        celda.style.background = esMesActual ? '#FFFFFF' : '#F9FAFB';
        celda.style.minHeight = '110px';
        celda.style.padding = '8px';
        celda.style.display = 'flex';
        celda.style.flexDirection = 'column';
        celda.style.position = 'relative';
        celda.style.cursor = 'pointer';
        celda.title = 'Clic para agregar recordatorio o anotación en este día';

        celda.onclick = () => {
            abrirModalCrearAnotacion(fechaStr);
        };

        if (esHoy) {
            celda.style.background = '#F0FDF4';
            celda.style.border = '1px solid #86EFAC';
        }

        const headerDia = document.createElement('div');
        headerDia.style.fontSize = '0.82rem';
        headerDia.style.fontWeight = esHoy ? '700' : '600';
        headerDia.style.color = esMesActual ? (esHoy ? '#166534' : '#374151') : '#9CA3AF';
        headerDia.style.marginBottom = '6px';
        headerDia.style.textAlign = 'right';

        if (etiquetaMes) {
            headerDia.textContent = `${numero} ${etiquetaMes}`;
        } else {
            headerDia.textContent = numero;
        }
        celda.appendChild(headerDia);

        const contenedorEventos = document.createElement('div');
        contenedorEventos.style.display = 'flex';
        contenedorEventos.style.flexDirection = 'column';
        contenedorEventos.style.gap = '4px';
        contenedorEventos.style.flex = '1';

        const eventosDia = eventosCalendario.filter(e => e.fecha === fechaStr);
        eventosDia.forEach(ev => {
            const pill = document.createElement('div');
            pill.textContent = ev.titulo;
            pill.title = ev.titulo;
            pill.style.background = ev.color || '#0F766E';
            pill.style.color = '#FFFFFF';
            pill.style.padding = '3px 8px';
            pill.style.borderRadius = '5px';
            pill.style.fontSize = '0.72rem';
            pill.style.fontWeight = '500';
            pill.style.whiteSpace = 'nowrap';
            pill.style.overflow = 'hidden';
            pill.style.textOverflow = 'ellipsis';
            pill.style.cursor = 'pointer';
            pill.style.boxShadow = '0 1px 2px rgba(0,0,0,0.1)';
            pill.style.transition = 'all 0.15s ease';

            pill.onmouseover = () => { pill.style.opacity = '0.9'; pill.style.transform = 'translateY(-1px)'; };
            pill.onmouseout = () => { pill.style.opacity = '1'; pill.style.transform = 'translateY(0)'; };

            pill.onclick = (e) => {
                e.stopPropagation();
                abrirDetalleEvento(ev);
            };
            contenedorEventos.appendChild(pill);
        });

        celda.appendChild(contenedorEventos);
        return celda;
    }

    let eventoActualSeleccionado = null;

    function abrirDetalleEvento(ev) {
        eventoActualSeleccionado = ev;
        let tituloModal = 'Audiencia Judicial';
        if (ev.tipo === 'plazo') tituloModal = 'Plazo Procesal';
        if (ev.tipo === 'anotacion') tituloModal = 'Anotación Personal';
        document.getElementById('modalEventoTitulo').textContent = tituloModal;
        
        let html = `
            <div style="margin-bottom:8px;"><strong>Fecha:</strong> ${ev.fecha} ${ev.hora ? '| <strong>Hora:</strong> ' + ev.hora : ''}</div>
            <div style="margin-bottom:8px;"><strong>Asunto:</strong> ${ev.titulo}</div>
        `;
        if (ev.tipo === 'anotacion') {
            const mins = ev.recordatorio_minutos !== undefined ? ev.recordatorio_minutos : 5;
            const txtRec = mins == 0 ? 'A la hora exacta' : `${mins} minutos antes`;
            html += `<div style="margin-bottom:8px;"><strong>Aviso de Recordatorio:</strong> <span style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;">🔔 ${txtRec}</span></div>`;
        }
        if (ev.caso) {
            html += `<div style="margin-bottom:8px;"><strong>Caso / Contexto:</strong> ${ev.caso}</div>`;
        }
        if (ev.lugar) {
            html += `<div style="margin-bottom:8px;"><strong>Lugar / Juzgado:</strong> ${ev.lugar}</div>`;
        }
        if (ev.detalle) {
            html += `<div style="margin-bottom:8px;padding:10px;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:6px;font-style:normal;color:#374151;"><strong>Notas:</strong> ${ev.detalle}</div>`;
        }
        document.getElementById('modalEventoCuerpo').innerHTML = html;

        const btnEditar = document.getElementById('btnEditarNotaModal');
        const formEliminar = document.getElementById('formEliminarNota');
        const baseUrlAnotaciones = "{{ url('calendario/anotaciones') }}";
        if (ev.tipo === 'anotacion' && ev.nota_id) {
            if (btnEditar) btnEditar.style.display = 'inline-block';
            formEliminar.action = `${baseUrlAnotaciones}/${ev.nota_id}`;
            formEliminar.style.display = 'inline-block';
        } else {
            if (btnEditar) btnEditar.style.display = 'none';
            formEliminar.style.display = 'none';
        }

        const linkBtn = document.getElementById('modalEventoLink');
        if (ev.link && ev.link !== '#') {
            linkBtn.href = ev.link;
            linkBtn.style.display = 'inline-block';
        } else {
            linkBtn.style.display = 'none';
        }

        const btnGCal = document.getElementById('btnGoogleCalendarModal');
        if (btnGCal) {
            if (ev.google_link) {
                btnGCal.href = ev.google_link;
                btnGCal.style.display = 'inline-flex';
            } else {
                btnGCal.style.display = 'none';
            }
        }

        document.getElementById('modalEventoCal').style.display = 'flex';
    }

    function abrirModalEditarAnotacionActual() {
        if (!eventoActualSeleccionado || !eventoActualSeleccionado.nota_id) return;
        
        document.getElementById('modalEventoCal').style.display = 'none';
        
        document.getElementById('formEditarAnotacion').action = `{{ url('calendario/anotaciones') }}/${eventoActualSeleccionado.nota_id}`;
        document.getElementById('editNotaTitulo').value = eventoActualSeleccionado.titulo_puro || eventoActualSeleccionado.titulo || '';
        document.getElementById('editNotaFecha').value = eventoActualSeleccionado.fecha || '';
        document.getElementById('editNotaHora').value = eventoActualSeleccionado.hora ? eventoActualSeleccionado.hora.substring(0, 5) : '';
        document.getElementById('editNotaRecordatorio').value = eventoActualSeleccionado.recordatorio_minutos !== undefined ? eventoActualSeleccionado.recordatorio_minutos : 5;
        document.getElementById('editNotaColor').value = eventoActualSeleccionado.color || '#2563EB';
        document.getElementById('editNotaDescripcion').value = eventoActualSeleccionado.detalle || '';
        
        document.getElementById('modalEditarAnotacion').style.display = 'flex';
    }

    function abrirModalCrearAnotacion(fechaDefecto = null) {
        if (fechaDefecto) {
            document.getElementById('inputNotaFecha').value = fechaDefecto;
        } else {
            const hoy = new Date();
            document.getElementById('inputNotaFecha').value = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
        }
        document.getElementById('modalCrearAnotacion').style.display = 'flex';
    }

    document.getElementById('btnCalHoy')?.addEventListener('click', () => {
        const h = new Date();
        mesCal = h.getMonth();
        anioCal = h.getFullYear();
        renderizarCalendario();
    });

    document.getElementById('btnCalPrev')?.addEventListener('click', () => {
        if (mesCal === 0) {
            mesCal = 11;
            anioCal--;
        } else {
            mesCal--;
        }
        renderizarCalendario();
    });

    document.getElementById('btnCalNext')?.addEventListener('click', () => {
        if (mesCal === 11) {
            mesCal = 0;
            anioCal++;
        } else {
            mesCal++;
        }
        renderizarCalendario();
    });

    renderizarCalendario();
</script>
@endpush
@endsection








