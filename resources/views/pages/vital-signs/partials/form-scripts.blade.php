<script>
(function() {
    const selectVitalSignPlaceholder = @json(localize('global.select_vital_sign_type'));
    const vitalSignLabel = @json(localize('global.vital_sign'));
    const deleteContainer = document.getElementById('delete-inputs-container');

    function getDropdownParent($select) {
        var $form = $('#multipleVitalSignsForm');
        if ($form.length) {
            return $form;
        }
        var $card = $select.closest('.card');
        if ($card.length) {
            return $card;
        }
        return $(document.body);
    }

    function destroySelect2InContainer(container) {
        if (!container || typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') return;
        $(container).find('select.vital-sign-type-select').each(function() {
            var $select = $(this);
            if (!$select.hasClass('select2-hidden-accessible')) {
                return;
            }
            try {
                $select.select2('close');
                $select.select2('destroy');
            } catch (e) {
                $select.removeClass('select2-hidden-accessible');
                $select.removeAttr('data-select2-id');
                $select.removeAttr('aria-hidden');
                $select.removeAttr('tabindex');
                $select.next('.select2-container').remove();
            }
        });
    }

    function stripSelect2FromElement(container) {
        destroySelect2InContainer(container);
        if (!container || typeof $ === 'undefined') return;
        $(container).find('select.vital-sign-type-select').each(function() {
            var $select = $(this);
            $select.next('.select2-container').remove();
            var $parent = $select.parent('.position-relative');
            if ($parent.length && $parent.children('select').length === 1) {
                $select.unwrap();
            }
        });
    }

    function initSelect2InContainer(container) {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') return;
        var $scope = container === document ? $('#multipleVitalSignsForm') : $(container);
        if (!$scope.length) {
            $scope = $(container);
        }
        $scope.find('select.vital-sign-type-select').each(function() {
            var $select = $(this);
            if (!$select.is(':visible') && $select.closest('.vital-sign-block').length) {
                return;
            }
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    $select.select2('destroy');
                } catch (e) {
                    destroySelect2InContainer($select.closest('.vital-sign-block')[0] || $select[0]);
                }
            }
            $select.select2({
                placeholder: selectVitalSignPlaceholder,
                allowClear: true,
                width: '100%',
                dropdownParent: getDropdownParent($select)
            });
        });
    }

    function isExistingBlock(block) {
        return block.getAttribute('data-is-existing') === '1' || block.classList.contains('existing-vital-sign-block');
    }

    function namePrefix(block, vsIndex) {
        return isExistingBlock(block) ? 'existing_vital_signs[' + vsIndex + ']' : 'vital_signs[' + vsIndex + ']';
    }

    function getNextIndex(containerSelector, blockSelector) {
        const blocks = document.querySelectorAll(containerSelector + ' ' + blockSelector);
        let max = -1;
        blocks.forEach(function(b) {
            const idx = parseInt(b.getAttribute('data-index'), 10);
            if (!isNaN(idx) && idx > max) max = idx;
        });
        return max + 1;
    }

    function getNextScheduleIndex(block) {
        const rows = block.querySelectorAll('.schedule-row');
        let max = -1;
        rows.forEach(function(r) {
            const inputEl = r.querySelector('input[name*="[date]"]') || r.querySelector('input[name*="[morning_time]"]');
            if (!inputEl || !inputEl.name) return;
            const m = inputEl.name.match(/schedules\]\[(\d+)\]/);
            if (m) { const n = parseInt(m[1], 10); if (n > max) max = n; }
        });
        return max + 1;
    }

    function renameBlockInputs(block, vsIndex) {
        const existing = isExistingBlock(block);
        block.setAttribute('data-index', vsIndex);
        block.querySelector('.vital-sign-label').textContent = vitalSignLabel + ' ' + (vsIndex + 1);
        const removeBtn = block.querySelector('.remove-vital-sign');
        if (removeBtn) {
            if (existing || vsIndex > 0) removeBtn.classList.remove('d-none');
            else removeBtn.classList.add('d-none');
        }
        const prefix = namePrefix(block, vsIndex);
        block.querySelectorAll('select.vital-sign-type-select').forEach(function(el) {
            el.name = prefix + '[vital_sign_type_id]';
        });
        block.querySelectorAll('.schedule-row').forEach(function(row, rowIdx) {
            const schedPrefix = prefix + '[schedules][' + rowIdx + ']';
            row.querySelectorAll('input').forEach(function(input) {
                if (!input.name) return;
                if (input.name.includes('[id]')) {
                    input.name = schedPrefix + '[id]';
                } else {
                    const match = input.name.match(/\[(date|morning_time|evening_time)\]$/);
                    if (match) input.name = schedPrefix + '[' + match[1] + ']';
                }
            });
        });
    }

    function reindexContainer(containerId, blockSelector) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll(blockSelector).forEach(function(block, i) {
            renameBlockInputs(block, i);
        });
    }

    function reindexAllBlocks() {
        reindexContainer('existing-vital-signs-container', '.existing-vital-sign-block');
        reindexContainer('vital-signs-container', '.new-vital-sign-block');
    }

    function addDeleteInput(fieldName, value) {
        if (!deleteContainer) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = fieldName;
        input.value = value;
        deleteContainer.appendChild(input);
    }

    function addVitalSignBlock() {
        const container = document.getElementById('vital-signs-container');
        const firstBlock = container && container.querySelector('.new-vital-sign-block');
        if (!container || !firstBlock) return;
        const idx = getNextIndex('#vital-signs-container', '.new-vital-sign-block');
        const clone = firstBlock.cloneNode(true);
        stripSelect2FromElement(clone);
        clone.setAttribute('data-index', idx);
        clone.setAttribute('data-is-existing', '0');
        clone.querySelector('.vital-sign-label').textContent = vitalSignLabel + ' ' + (idx + 1);
        const vsSelect = clone.querySelector('.vital-sign-type-select');
        if (vsSelect) vsSelect.value = '';
        clone.querySelectorAll('.schedule-row').forEach(function(row, i) {
            row.removeAttribute('data-schedule-id');
            row.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
            if (i > 0) row.remove();
        });
        const removeBtn = clone.querySelector('.remove-vital-sign');
        if (removeBtn) removeBtn.classList.remove('d-none');
        container.appendChild(clone);
        reindexAllBlocks();
        initSelect2InContainer(clone);
        initPersianDatepickerInContainer(clone);
    }

    function addScheduleRow(btn) {
        const block = btn.closest('.vital-sign-block');
        if (!block) return;
        const vsIndex = parseInt(block.getAttribute('data-index'), 10);
        const schedIndex = getNextScheduleIndex(block);
        const tbody = block.querySelector('tbody.schedule-rows');
        const firstRow = block.querySelector('tr.schedule-row');
        if (!tbody || !firstRow) return;
        const newRow = firstRow.cloneNode(true);
        newRow.removeAttribute('data-schedule-id');
        newRow.querySelectorAll('input').forEach(function(inp) {
            inp.value = '';
            if (inp.name && inp.name.includes('[id]')) inp.remove();
        });
        const dayCell = newRow.querySelector('td.align-middle');
        if (dayCell && !dayCell.querySelector('input[type="hidden"]')) {
            dayCell.innerHTML = '';
        }
        const prefix = namePrefix(block, vsIndex);
        newRow.querySelectorAll('input').forEach(function(el) {
            if (!el.name) return;
            if (el.name.includes('[id]')) return;
            const match = el.name.match(/\[(date|morning_time|evening_time)\]$/);
            if (match) el.name = prefix + '[schedules][' + schedIndex + '][' + match[1] + ']';
        });
        tbody.appendChild(newRow);
        reindexAllBlocks();
        initPersianDatepickerInContainer(newRow);
    }

    function initPersianDatepickerInContainer(container) {
        if (typeof $ === 'undefined' || typeof $.fn.persianDatepicker !== 'function') return;
        $(container).find('input.schedule-date, input.datepicker_dari').each(function() {
            var $el = $(this);
            if ($el.data('datepicker') || $el.hasClass('pdp-el')) return;
            $el.persianDatepicker({
                months: ["حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلو", "حوت"],
                dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
                shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
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
                alwaysShow: false
            });
        });
    }

    function attachListeners() {
        var addVsBtn = document.getElementById('add-vital-sign-btn');
        if (addVsBtn) {
            addVsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addVitalSignBlock();
            });
        }

        document.addEventListener('click', function(e) {
            var addScheduleBtn = e.target.closest('.add-schedule-row');
            if (addScheduleBtn) {
                e.preventDefault();
                addScheduleRow(addScheduleBtn);
                return;
            }

            var removeVsBtn = e.target.closest('.remove-vital-sign');
            if (removeVsBtn) {
                e.preventDefault();
                const block = removeVsBtn.closest('.vital-sign-block');
                if (!block) return;
                const isExisting = isExistingBlock(block);
                const container = block.parentElement;
                const selector = isExisting ? '.existing-vital-sign-block' : '.new-vital-sign-block';
                if (container.querySelectorAll(selector).length <= 1 && !isExisting) return;

                if (isExisting) {
                    const vitalSignId = block.getAttribute('data-vital-sign-id');
                    if (vitalSignId) addDeleteInput('delete_vital_sign_ids[]', vitalSignId);
                }
                destroySelect2InContainer(block);
                block.remove();
                reindexAllBlocks();
                return;
            }

            var removeScheduleBtn = e.target.closest('.remove-schedule-row');
            if (removeScheduleBtn) {
                e.preventDefault();
                const row = removeScheduleBtn.closest('tr.schedule-row');
                if (!row) return;
                const tbody = row.closest('tbody.schedule-rows');
                if (tbody && tbody.querySelectorAll('tr.schedule-row').length <= 1) return;
                const scheduleId = row.getAttribute('data-schedule-id');
                if (scheduleId) addDeleteInput('delete_schedule_ids[]', scheduleId);
                row.remove();
                reindexAllBlocks();
            }
        });
    }

    function initAll() {
        attachListeners();
        ['existing-vital-signs-container', 'vital-signs-container'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) initPersianDatepickerInContainer(el);
        });
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            setTimeout(function() {
                var form = document.getElementById('multipleVitalSignsForm');
                initSelect2InContainer(form || document);
            }, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        setTimeout(initAll, 100);
    }
})();
</script>
