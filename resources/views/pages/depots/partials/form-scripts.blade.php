@push('custom-js')
<script>
$(document).ready(function () {
    const select2Options = {
        placeholder: 'Select user',
        allowClear: true,
        width: '100%'
    };

    function initUserSelect($scope) {
        $scope.find('.depot-user-select').each(function () {
            const $select = $(this);
            if (!$select.hasClass('select2-hidden-accessible')) {
                $select.select2(select2Options);
            }
        });
    }

    function updateRemoveButtons() {
        const items = $('#depot-user-container .depot-user-item');
        items.find('.remove-depot-user').toggle(items.length > 1);
    }

    initUserSelect($('#depot-user-container'));
    updateRemoveButtons();

    $('#add-depot-user').on('click', function () {
        const container = $('#depot-user-container');
        const newItem = container.find('.depot-user-item:first').clone();

        newItem.find('.select2-container').remove();
        newItem.find('.depot-user-select')
            .removeClass('select2-hidden-accessible')
            .removeAttr('data-select2-id')
            .show()
            .val('');
        newItem.find('.depot-role-select').val('staff');

        container.append(newItem);
        initUserSelect(newItem);
        updateRemoveButtons();
    });

    $(document).on('click', '.remove-depot-user', function () {
        const container = $('#depot-user-container');
        if (container.find('.depot-user-item').length === 1) {
            return;
        }

        const item = $(this).closest('.depot-user-item');
        const select = item.find('.depot-user-select');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        item.remove();
        updateRemoveButtons();
    });

    $('.js-depot-form').on('submit', function (event) {
        event.preventDefault();

        const form = this;
        const errorBox = $('#depot-form-errors');
        const submitButton = $(form).find('button[type="submit"]');

        errorBox.addClass('d-none').empty();
        submitButton.prop('disabled', true);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw data;
                }
                window.location.href = data.redirect;
            })
            .catch(error => {
                const messages = error.errors
                    ? Object.values(error.errors).flat()
                    : [error.message || 'Unable to save depot.'];
                errorBox.html(messages.map(message => `<div>${message}</div>`).join('')).removeClass('d-none');
            })
            .finally(() => {
                submitButton.prop('disabled', false);
            });
    });
});
</script>
@endpush
