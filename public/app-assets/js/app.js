$('#loadMoreNotificationBtn').on('click', function (e) {
    $.ajax({
        url: route('notifications.index'),
        data: {
            offset: $('.notification_item').length,
        },
        success: function (result, status, xhr) {
            if (result.success) {
                $('#notification_list').append(result.html);
            } else {
                toastr.error(null, result.error);
            }
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON?.message ?? null, error);
        }
    });
});

$('.dropdown-notification').on('click', function (e) {
    $.ajax({
        url: route('notifications.mark-all-as-read'),
        data: {},
        success: function (result, status, xhr) {
            if (result.success) {
                $('.notification_count_badge').hide();
            } else {
                toastr.error(null, result.error);
            }
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON?.message ?? null, error);
        }
    });
});

$(document).ajaxError(function (event, xhr) {
    let errors = {
        401: 'User is unauthenticated!',
        419: 'Login session expired!',
    };
    if (Object.keys(errors).some(status => status == xhr?.status)) {
        // if(xhr?.status == 401 && xhr?.responseJSON?.logout_cause) {
        //     window.location.href = route('login', {
        //         logout_cause: xhr?.responseJSON?.logout_cause ?? (errors?.[xhr?.status] ?? ''),
        //     });
        //     return;
        // }
        // window.location.href = route('login', {
        //     logout_cause: errors?.[xhr?.status] ?? '',
        // });
        if(xhr?.status == 401 && xhr?.responseJSON?.logout_cause) {
            window.location.href = route('login');
            return;
        }
        window.location.href = route('login');
    }
});

document
    .querySelector('#main-menu-navigation .nav-item-invoices-menu')
    .addEventListener('click', function (e) {
        localStorage.setItem('invoices.list.page-first', true)
    });
