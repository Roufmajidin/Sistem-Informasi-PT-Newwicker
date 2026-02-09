$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$(document).ready(function () {
    $.fn.editable.defaults.mode = 'inline';

    // Aktifkan semua elemen yang memiliki class yang diawali dengan "editable-"
    $('[class^="editable-"], [class*=" editable-"]').editable({
        success: function (response, newValue) {
            if (response.status !== 'success') {
                return response.msg || 'Update failed.';
            }
        }
    });
});
