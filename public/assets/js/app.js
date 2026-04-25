'use strict';

const Validators = {
    required: (v) => v.trim() !== '' || 'Поле обязательно для заполнения.',

    fullname: (v) => {
        const t = v.trim();
        if (t.length < 2 || t.length > 100) return 'От 2 до 100 символов.';
        if (!/^[\p{L}\s\-']+$/u.test(t))   return 'Только буквы, пробелы, дефисы.';
        return true;
    },

    email: (v) => {
        const t = v.trim();
        if (t === '') return 'Поле обязательно для заполнения.';
        if (t.length > 254) return 'Слишком длинный email.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(t)) return 'Некорректный email.';
        return true;
    },

    phone: (v) => {
        const digits = v.replace(/\D+/g, '');
        let normalized = digits;
        if (normalized.length === 11 && normalized[0] === '8') normalized = '7' + normalized.slice(1);
        if (normalized.length !== 11 || normalized[0] !== '7') {
            return 'Введите номер в формате +7 XXX XXX-XX-XX.';
        }
        return true;
    },

    password: (v) => {
        if (v.length < 8)  return 'Минимум 8 символов.';
        if (v.length > 72) return 'Слишком длинный пароль.';
        if (!/[A-Za-zА-Яа-яЁё]/u.test(v) || !/\d/.test(v)) return 'Должен содержать буквы и цифры.';
        return true;
    },

    length: (v, el) => {
        const min = parseInt(el.getAttribute('minlength') || '0', 10);
        const max = parseInt(el.getAttribute('maxlength') || '0', 10);
        const len = v.trim().length;
        if (min && len < min) return `Минимум ${min} символов.`;
        if (max && len > max) return `Максимум ${max} символов.`;
        return true;
    },

    match: (v, el) => {
        const otherId = el.dataset.match;
        const other = document.getElementById(otherId);
        if (other && v !== other.value) return 'Значения не совпадают.';
        return true;
    },
};

function setError(el, message) {
    el.classList.add('input--err');
    let box = el.parentElement.querySelector('.err--js');
    if (!box) {
        box = document.createElement('div');
        box.className = 'err err--js';
        el.parentElement.appendChild(box);
    }
    box.textContent = message;
}

function clearError(el) {
    el.classList.remove('input--err');
    const box = el.parentElement.querySelector('.err--js');
    if (box) box.remove();
    const serverErr = el.parentElement.querySelector('.err:not(.err--js)');
    if (serverErr) serverErr.remove();
}

function validateField(el) {
    const rule = el.dataset.rule;
    if (!rule) return true;
    const value = el.value;
    if (!el.required && value === '') {
        clearError(el);
        return true;
    }
    const fn = Validators[rule];
    if (!fn) return true;
    const result = fn(value, el);
    if (result === true) {
        clearError(el);
        return true;
    }
    setError(el, result);
    return false;
}

function bindForm(form) {
    if (!form) return;
    const fields = form.querySelectorAll('[data-rule]');
    fields.forEach((el) => {
        el.addEventListener('blur',  () => validateField(el));
        el.addEventListener('input', () => {
            if (el.classList.contains('input--err')) validateField(el);
        });
    });
    form.addEventListener('submit', (e) => {
        let ok = true;
        fields.forEach((el) => { if (!validateField(el)) ok = false; });
        if (!ok) {
            e.preventDefault();
            const firstBad = form.querySelector('.input--err');
            if (firstBad) firstBad.focus();
        }
    });
}

async function refreshSlots(form) {
    const dateEl = form.querySelector('#date');
    const slotEl = form.querySelector('#time_slot');
    if (!dateEl || !slotEl) return;
    const date = dateEl.value;
    if (!date) return;

    const params = new URLSearchParams({ p: 'api_slots', date });
    const editId = form.dataset.mcId;
    if (editId) params.set('exclude', editId);

    let data;
    try {
        const resp = await fetch('index.php?' + params.toString(), { credentials: 'same-origin' });
        if (!resp.ok) return;
        data = await resp.json();
    } catch (err) {
        return;
    }
    const taken = new Set(data.taken || []);
    const previouslySelected = slotEl.value;
    Array.from(slotEl.options).forEach((opt) => {
        if (opt.value === '') return;
        const isTaken = taken.has(opt.value);
        opt.disabled = isTaken;
        opt.textContent = opt.textContent.replace(/\s+\(занято\)$/, '');
        if (isTaken) opt.textContent += ' (занято)';
    });
    if (previouslySelected && taken.has(previouslySelected)) {
        slotEl.value = '';
    }
}

function bindMcForm() {
    const form = document.getElementById('form-mc');
    if (!form) return;
    if (form.dataset.edit === '1') return;
    const dateEl = form.querySelector('#date');
    if (!dateEl) return;
    dateEl.addEventListener('change', () => refreshSlots(form));
    if (dateEl.value) refreshSlots(form);
}

function bindBurger() {
    const burger = document.querySelector('.menu-burger .burger');
    const menu   = document.querySelector('.main .menu');
    if (!burger || !menu) return;
    burger.addEventListener('click', () => {
        menu.style.display = menu.style.display === 'block' ? '' : 'block';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindForm(document.getElementById('form-register'));
    bindForm(document.getElementById('form-login'));
    bindForm(document.getElementById('form-mc'));
    bindMcForm();
    bindBurger();
});
