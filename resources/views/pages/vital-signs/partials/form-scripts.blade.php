<script>
(function () {
    'use strict';

    const form = document.getElementById('multipleVitalSignsForm');
    if (!form) {
        return;
    }

    const selectPlaceholder = @json(localize('global.select_vital_sign_type'));
    const vitalSignLabel = @json(localize('global.vital_sign'));
    const deleteContainer = document.getElementById('delete-inputs-container');
    const template = document.getElementById('vital-sign-block-template');

    function getDropdownParent($select) {
        return $('#multipleVitalSignsForm').length ? $('#multipleVitalSignsForm') : $(document.body);
    }

    function destroySelect2($select) {
        if (!$select || !$select.length || !$select.hasClass('select2-hidden-accessible')) {
            return;
        }
        try {
            $select.select2('close');
            $select.select2('destroy');
        } catch (e) {
            $select.removeClass('select2-hidden-accessible');
            $select.removeAttr('data-select2-id aria-hidden tabindex');
            $select.next('.select2-container').remove();
        }
    }

    function initSelect2(scope) {
        if (typeof $.fn.select2 === 'undefined') {
            return;
        }
        $(scope).find('select.vital-sign-type-select').each(function () {
            const $select = $(this);
            destroySelect2($select);
            $select.select2({
                placeholder: selectPlaceholder,
                allowClear: true,
                width: '100%',
                dropdownParent: getDropdownParent($select),
            });
        });
    }

    function isExistingBlock(block) {
        return block.classList.contains('existing-vital-sign-block');
    }

    function namePrefix(block, index) {
        return isExistingBlock(block)
            ? 'existing_vital_signs[' + index + ']'
            : 'vital_signs[' + index + ']';
    }

    function reindexBlocks(containerId, selector) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        container.querySelectorAll(selector).forEach(function (block, index) {
            block.setAttribute('data-index', index);
            const label = block.querySelector('.vital-sign-label');
            if (label) {
                label.textContent = vitalSignLabel + ' ' + (index + 1);
            }
            const removeBtn = block.querySelector('.remove-vital-sign');
            if (removeBtn) {
                removeBtn.classList.toggle('d-none', !isExistingBlock(block) && index === 0);
            }
            const prefix = namePrefix(block, index);
            const typeSelect = block.querySelector('select.vital-sign-type-select');
            if (typeSelect) {
                typeSelect.name = prefix + '[vital_sign_type_id]';
            }
            block.querySelectorAll('.schedule-row').forEach(function (row, rowIndex) {
                const schedPrefix = prefix + '[schedules][' + rowIndex + ']';
                row.querySelectorAll('input').forEach(function (input) {
                    if (!input.name) {
                        return;
                    }
                    if (input.name.includes('[id]')) {
                        input.name = schedPrefix + '[id]';
                        return;
                    }
                    const match = input.name.match(/\[(date|morning_time|evening_time)\]$/);
                    if (match) {
                        input.name = schedPrefix + '[' + match[1] + ']';
                    }
                });
            });
        });
    }

    function reindexAll() {
        reindexBlocks('existing-vital-signs-container', '.existing-vital-sign-block');
        reindexBlocks('vital-signs-container', '.new-vital-sign-block');
    }

    function addDeleteInput(name, value) {
        if (!deleteContainer) {
            return;
        }
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        deleteContainer.appendChild(input);
    }

    function initDatepickers(scope) {
        if (typeof $.fn.persianDatepicker !== 'function') {
            return;
        }
        $(scope).find('input.schedule-date').each(function () {
            const $el = $(this);
            if ($el.data('datepicker') || $el.hasClass('pdp-el')) {
                return;
            }
            $el.persianDatepicker({
                months: ['حمل', 'ثور', 'جوزا', 'سرطان', 'اسد', 'سنبله', 'میزان', 'عقرب', 'قوس', 'جدی', 'دلو', 'حوت'],
                dowTitle: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنج شنبه', 'جمعه'],
                shortDowTitle: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
                showGregorianDate: false,
                persianNumbers: true,
                formatDate: 'YYYY/MM/DD',
                selectedBefore: false,
                selectedDate: null,
                startDate: null,
                endDate: null,
                prevArrow: '\u25c4',
                nextArrow: '\u25ba',
                theme: 'default',
                alwaysShow: false,
            });
        });
    }

    function addVitalSignBlock() {
        const container = document.getElementById('vital-signs-container');
        if (!container || !template) {
            return;
        }
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        const block = container.lastElementChild;
        reindexAll();
        initSelect2(block);
        initDatepickers(block);
    }

    function addScheduleRow(button) {
        const block = button.closest('.vital-sign-block');
        const tbody = block && block.querySelector('tbody.schedule-rows');
        const templateRow = block && block.querySelector('tr.schedule-row');
        if (!block || !tbody || !templateRow) {
            return;
        }
        const row = templateRow.cloneNode(true);
        row.removeAttribute('data-schedule-id');
        row.querySelectorAll('input').forEach(function (input) {
            input.value = '';
            if (input.name && input.name.includes('[id]')) {
                input.remove();
            }
        });
        const dayCell = row.querySelector('td.align-middle');
        if (dayCell) {
            dayCell.innerHTML = '';
        }
        tbody.appendChild(row);
        reindexAll();
        initDatepickers(row);
    }

    function stripEmptyNewBlocks() {
        document.querySelectorAll('#vital-signs-container .new-vital-sign-block').forEach(function (block) {
            const select = block.querySelector('select.vital-sign-type-select');
            if (!select || select.value) {
                return;
            }
            destroySelect2($(select));
            block.remove();
        });
        reindexAll();
    }

    document.getElementById('add-vital-sign-btn')?.addEventListener('click', function (e) {
        e.preventDefault();
        addVitalSignBlock();
    });

    form.addEventListener('submit', function () {
        stripEmptyNewBlocks();
    });

    form.addEventListener('click', function (e) {
        const addScheduleBtn = e.target.closest('.add-schedule-row');
        if (addScheduleBtn) {
            e.preventDefault();
            addScheduleRow(addScheduleBtn);
            return;
        }

        const removeVsBtn = e.target.closest('.remove-vital-sign');
        if (removeVsBtn) {
            e.preventDefault();
            const block = removeVsBtn.closest('.vital-sign-block');
            if (!block) {
                return;
            }
            const container = block.parentElement;
            const selector = isExistingBlock(block) ? '.existing-vital-sign-block' : '.new-vital-sign-block';
            if (!isExistingBlock(block) && container.querySelectorAll(selector).length <= 1) {
                return;
            }
            if (isExistingBlock(block)) {
                const id = block.getAttribute('data-vital-sign-id');
                if (id) {
                    addDeleteInput('delete_vital_sign_ids[]', id);
                }
            }
            destroySelect2($(block).find('select.vital-sign-type-select'));
            block.remove();
            reindexAll();
            return;
        }

        const removeScheduleBtn = e.target.closest('.remove-schedule-row');
        if (removeScheduleBtn) {
            e.preventDefault();
            const row = removeScheduleBtn.closest('tr.schedule-row');
            const tbody = row && row.closest('tbody.schedule-rows');
            if (!row || !tbody || tbody.querySelectorAll('tr.schedule-row').length <= 1) {
                return;
            }
            const scheduleId = row.getAttribute('data-schedule-id');
            if (scheduleId) {
                addDeleteInput('delete_schedule_ids[]', scheduleId);
            }
            row.remove();
            reindexAll();
        }
    });

    initDatepickers(form);
    setTimeout(function () {
        initSelect2(form);
    }, 50);
})();
</script>
