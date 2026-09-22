<style>
/* ===== Datepicker wrapper ===== */
.datepicker-wrapper { cursor: pointer; }
.datepicker-wrapper input[readonly] { background-color: #fff; cursor: pointer; }
.datepicker-wrapper .datepicker-icon { pointer-events: none; z-index: 2; line-height: 1; }

/* ===== Flatpickr custom ===== */
.flatpickr-calendar {
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.12) !important;
    border: 1px solid rgba(0,0,0,.06) !important;
    font-family: inherit !important;
    padding: 0 !important;
    overflow: hidden;
}
.flatpickr-months {
    background: linear-gradient(135deg, #487fff 0%, #3b6de0 100%);
    border-radius: 12px 12px 0 0;
    padding: 8px 4px;
}
.flatpickr-months .flatpickr-month { height: 40px; }
.flatpickr-current-month { color: #fff !important; font-weight: 600 !important; font-size: .95rem !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: transparent !important; color: #fff !important;
    font-weight: 600 !important; -webkit-appearance: none; appearance: none;
}
.flatpickr-current-month input.cur-year { color: #fff !important; font-weight: 600 !important; }
.flatpickr-weekdays { background: #f8fafc !important; padding: 4px 0; }
.flatpickr-weekday { color: #64748b !important; font-weight: 600 !important; font-size: .75rem !important; text-transform: uppercase; }
.flatpickr-day {
    border-radius: 8px !important; font-size: .8125rem !important;
    font-weight: 500 !important; color: #334155 !important; transition: all .15s ease !important;
}
.flatpickr-day:hover { background: #EFF6FF !important; border-color: #EFF6FF !important; color: #487fff !important; }
.flatpickr-day.selected { background: #487fff !important; border-color: #487fff !important; color: #fff !important; box-shadow: 0 2px 8px rgba(72,127,255,.35); }
.flatpickr-day.today { border-color: #487fff !important; color: #487fff !important; font-weight: 700 !important; }
.flatpickr-day.today.selected { color: #fff !important; }
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month { color: #fff !important; fill: #fff !important; }
.flatpickr-months .flatpickr-prev-month:hover svg,
.flatpickr-months .flatpickr-next-month:hover svg { fill: rgba(255,255,255,.7) !important; }

/* ===== Saldo info card ===== */
.saldo-info-card {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 8px;
    background: #EFF6FF; border: 1px solid #BFDBFE;
    font-size: .8125rem; color: #1d4ed8;
}
.saldo-info-card i { font-size: 15px; flex-shrink: 0; }
.saldo-info-text strong { font-weight: 700; }

/* Danger variant (overlap warning) */
.saldo-info-card--danger {
    background: #FEF2F2; border-color: #FECACA; color: #dc2626;
}

/* ===== Dark mode ===== */
[data-theme="dark"] .datepicker-wrapper input[readonly] { background-color: #1e293b; color: #e2e8f0; }
[data-theme="dark"] .saldo-info-card { background: rgba(72,127,255,.1); border-color: rgba(72,127,255,.3); color: #93c5fd; }
[data-theme="dark"] .saldo-info-card--danger { background: rgba(220,38,38,.1); border-color: rgba(220,38,38,.3); color: #fca5a5; }
</style>
