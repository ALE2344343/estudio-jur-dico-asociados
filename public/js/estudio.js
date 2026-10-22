/**
 * ESTUDIO JURÍDICO & ASOCIADOS
 * Script JavaScript global del sistema
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-cerrar alertas después de 5 segundos ──
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });

    // ── Confirmar eliminaciones ──
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || '¿Está seguro?')) {
                e.preventDefault();
            }
        });
    });

    // ── Resaltar fila de tabla activa al hacer clic ──
    document.querySelectorAll('tbody tr').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' ||
                e.target.closest('form') || e.target.closest('a') || e.target.closest('button')) return;
            document.querySelectorAll('tbody tr.row-selected').forEach(r => r.classList.remove('row-selected'));
            this.classList.add('row-selected');
        });
    });

    // ── Tooltip simple ──
    document.querySelectorAll('[data-tooltip]').forEach(function (el) {
        el.style.position = 'relative';
        el.style.cursor = 'help';
        el.addEventListener('mouseenter', function () {
            const tip = document.createElement('div');
            tip.className = 'tooltip-box';
            tip.textContent = this.dataset.tooltip;
            tip.style.cssText = `
                position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);
                background:var(--navy-dark);color:white;font-size:0.75rem;padding:5px 10px;
                border-radius:5px;white-space:nowrap;z-index:9999;pointer-events:none;
                box-shadow:0 2px 8px rgba(0,0,0,0.3);
            `;
            this.appendChild(tip);
        });
        el.addEventListener('mouseleave', function () {
            this.querySelector('.tooltip-box')?.remove();
        });
    });

    // ── Formato automático de moneda boliviana ──
    document.querySelectorAll('input[data-bs]').forEach(function (input) {
        input.addEventListener('blur', function () {
            const val = parseFloat(this.value);
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });
    });


    // ── Búsqueda instantánea en tablas (local) ──
    const localSearch = document.getElementById('localSearch');
    if (localSearch) {
        localSearch.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    // ── Acordeón de leyes (delegado) ──
    document.addEventListener('click', function (e) {
        const header = e.target.closest('.ley-accordion-header');
        if (header) {
            const body = header.nextElementSibling;
            const arrow = header.querySelector('svg:last-child');
            if (!body) return;
            const isOpen = body.classList.contains('open');
            body.classList.toggle('open', !isOpen);
            header.classList.toggle('open', !isOpen);
            if (arrow) arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    });

    // Notificación de bienvenida 
    const lastLogin = localStorage.getItem('ej_last_login');
    const today = new Date().toDateString();
    if (lastLogin !== today && document.querySelector('.dashboard-layout')) {
        localStorage.setItem('ej_last_login', today);
        // Solo en el dashboard
        if (window.location.pathname === '/dashboard') {
            const hora = new Date().getHours();
            const saludo = hora < 12 ? 'Buenos días' : hora < 19 ? 'Buenas tardes' : 'Buenas noches';
            showToast(`${saludo}. Bienvenido al sistema. `, 'info');
        }
    }
});

// notifications 
function showToast(message, type = 'success') {
    const colors = {
        success: { bg: 'rgba(40,167,69,0.95)' },
        error:   { bg: 'rgba(220,53,69,0.95)' },
        info:    { bg: 'rgba(26,39,68,0.95)' },
        warning: { bg: 'rgba(255,193,7,0.95)' },
    };
    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed;bottom:25px;right:25px;z-index:9999;
        background:${c.bg};color:white;padding:14px 20px;
        border-radius:10px;font-size:0.88rem;
        box-shadow:0 8px 25px rgba(0,0,0,0.3);
        display:flex;align-items:center;gap:10px;
        max-width:350px;cursor:pointer;
        animation:slideIn 0.3s ease;
    `;
    toast.innerHTML = `${c.icon} ${message}`;
    toast.onclick = () => toast.remove();

    // Agregar keyframe de animación
    if (!document.getElementById('toastStyles')) {
        const style = document.createElement('style');
        style.id = 'toastStyles';
        style.textContent = `
            @keyframes slideIn { from { transform:translateX(110%);opacity:0; } to { transform:translateX(0);opacity:1; } }
            @keyframes slideOut { from { transform:translateX(0);opacity:1; } to { transform:translateX(110%);opacity:0; } }
            tbody tr.row-selected { background:rgba(201,168,76,0.08) !important; }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);
    setTimeout(function () {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Formatear número
function formatBs(amount) {
    return 'Bs. ' + parseFloat(amount).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
